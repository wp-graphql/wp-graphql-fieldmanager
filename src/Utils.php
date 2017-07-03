<?php
namespace WPGraphQL\Extensions\Fieldmanager;

use phpDocumentor\Reflection\DocBlockFactory;
use \WPGraphQL\Types;

class Utils {

	/**
	 * Utility function for formatting a string to be compatible with GraphQL labels (camelCase with lowercase first letter)
	 *
	 * @param $input
	 *
	 * @return mixed|string
	 */
	public static function _graphql_label( $input ) {

		$graphql_label = str_ireplace( '_', ' ', $input );
		$graphql_label = ucwords( $graphql_label );
		$graphql_label = str_ireplace( ' ', '', $graphql_label );
		$graphql_label = lcfirst( $graphql_label );

		return $graphql_label;

	}

	public static function map_class_properties_to_fields( $fields, $class ) {

		/**
		 * Set a default array of class_properties
		 */
		$class_properties = [];

		/**
		 * Let's do some introspection into the Fieldmanager_Group class so we can build out the GraphQL Schema
		 * dynamically based on the defined properties of the Fieldmanager_Group
		 */
		$field_group_fields = new \ReflectionClass( $class );

		/**
		 * Get the properties for the Fieldmanager_Group class
		 */
		$properties = $field_group_fields->getProperties();

		/**
		 * Instantiate php_documentor so we can parse docblocks for use in the Schema
		 */
		$php_documentor = DocBlockFactory::createInstance();

		/**
		 * Loop through the properties of Fieldmanager_Group
		 */
		foreach ( $properties as $property ) {

			/**
			 * @todo: The docblock for editor_settings was throwing all sorts of errors, so this was a quick way to just ignore parsing that field. Should look at actually addressing this.
			 */
			if ( $property->getName() === 'editor_settings' ) {
				return;
			}

			/**
			 * Get the docbloc comment
			 */
			$comment = $property->getDocComment();

			/**
			 * If there's no comment, we're not going to be able to properly parse and add to the schema
			 */
			if ( empty( $comment ) ) {
				return $fields;
			}

			/**
			 * Create the docblock for parsing by php_documentor
			 */
			$docbloc = $php_documentor->create( $comment );

			/**
			 * Get the property type from the "@var" paramater in the docbloc
			 */
			if ( preg_match( '/@var\s+([^\s]+)/', $comment, $matches ) ) {
				list(, $type) = $matches;
			}

			/**
			 * Build an array of properties that will be used to map the fields to the GraphQL Schema
			 */
			$class_properties[] = [
				'name' => $property->getName(),
				'comment' => $docbloc->getDescription(),
				'summary' => $docbloc->getSummary(),
				'tags' => $docbloc->getTags(),
				'context' => $docbloc->getContext(),
				'location' => $docbloc->getLocation(),
				'type' => ! empty( $type ) ? $type : null,
			];
		} // End foreach().

		/**
		 * If there are $class_properties, loop through them and add the appropriate fields to the Schema
		 */
		if ( ! empty( $class_properties ) && is_array( $class_properties ) ) {

			/**
			 * Loop through the properties
			 */
			foreach ( $class_properties as $property ) {

				if ( empty( $property['type'] ) || empty( $property['name'] ) ) {
					return;
				}

				/**
				 * Determine the type of property so we can add the appropriate field Type to the Schema
				 */
				switch ( $property['type'] ) {

					/**
					 * If the property is a string, let's add it to the Schema as a string Type and resolve for a string
					 */
					case 'string':
						$fields[ Utils::_graphql_label( $property['name'] ) ] = [
							'type'        => Types::string(),
							'description' => ! empty( $property['summary'] ) ? $property['summary'] : null,
							'resolve'     => function( $field ) use ( $property ) {
								$group = self::get_field( $field );
								return ( ! empty( $group ) &&  is_object( $group ) && ! empty( $group->{$property['name']} ) ) ? $group->{$property['name']} : null;
							},
						];
						break;

					/**
					 * If the property is a Boolean, let's add it to the schema as a Boolean Type and resolve for Boolean
					 */
					case 'boolean':
						$fields[ Utils::_graphql_label( $property['name'] ) ] = [
							'type'        => Types::boolean(),
							'description' => ! empty( $property['summary'] ) ? $property['summary'] : null,
							'resolve'     => function( $field ) use ( $property ) {
								$group = self::get_field( $field );
								return ( ! empty( $group ) && is_object( $group ) && true === $group->{$property['name']} ) ? true : false;
							},
						];
						break;

					/**
					 * If the property is an Integer, let's add it to the Schema as an Integer Type and resolve for an Integer
					 */
					case 'integer':
						$fields[ Utils::_graphql_label( $property['name'] ) ] = [
							'type'        => Types::int(),
							'description' => ! empty( $property['summary'] ) ? $property['summary'] : null,
							'resolve'     => function( $field ) use ( $property ) {
								$group = self::get_field( $field );
								return ( ! empty( $group ) &&  is_object( $group ) && ! empty( $group->{$property['name']} ) ) ? absint( $group->{$property['name']} ) : null;
							},
						];
						break;
					default:
						break;
				} // End switch().
			} // End foreach().
		} // End if().

		return $fields;

	}

	/**
	 * Takes a Fieldmanager field (either raw or nested) and returns the field_object to be used in resolving the property values
	 *
	 * @param $field
	 *
	 * @return mixed|null
	 */
	private static function get_field( $field ) {

		/**
		 * If the field is an object, just pass it through
		 */
		if ( is_object( $field ) ) {
			$field_to_return = $field;

		/**
		 * If the $field is an array, it means it's formatted with our nested array, so there should be
		 * a "field" key which has the object we're looking for
		 */
		} elseif ( is_array( $field ) && ! empty( $field['field'] ) && is_object( $field['field'] )  ) {
			$field_to_return = $field['field'];
		} else {
			$field_to_return = null;
		}

		/**
		 * Return the field
		 */
		return $field_to_return;
	}

}
