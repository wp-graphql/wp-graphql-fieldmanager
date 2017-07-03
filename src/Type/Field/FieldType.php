<?php

namespace WPGraphQL\Extensions\Fieldmanager\Type\FieldType;

use WPGraphQL\Extensions\Fieldmanager\Utils;
use WPGraphQL\Type\WPObjectType;

/**
 * Class FieldType
 *
 * @package WPGraphQL\Extensions\Fieldmanager\Type\FieldType
 */
class FieldType extends WPObjectType {

	/**
	 * Holds the definition of the fields for the FieldGroupType
	 *
	 * @var array
	 */
	private static $fields;

	/**
	 * FieldType constructor.
	 *
	 * @param \Fieldmanager_Field $field      The instance of the Field being queried
	 * @param object              $fm_context The Context the Field is connected to
	 */
	public function __construct( \Fieldmanager_Field $field, $fm_context ) {

		$config = [
			'name'        => ! empty( $field->name ) ? Utils::_graphql_label( 'fm_' . $field->name ) : 'fmField',
			'fields'      => self::fields( $field, $fm_context ),
			'description' => ! empty( $field->description ) ? $field->description : __( 'Fieldmanager field', 'wp-graphql-fieldmanager' ),
		];

		parent::__construct( $config );
	}

	/**
	 * Defines the Fields for the FieldType schema
	 *
	 * @param \Fieldmanager_Field $field      The instance of the Field being queried
	 * @param object              $fm_context The Context the Field is connected to
	 *
	 * @return mixed|null
	 */
	private function fields( \Fieldmanager_Field $field, $fm_context ) {

		if ( null === self::$fields ) {
			self::$fields = [];
		}

		if ( empty( self::$fields[ Utils::_graphql_label( $field->name ) ] ) ) {

			/**
			 * Map the $field class properties to GraphQL fields for all the statically
			 * defined field properties
			 */
			$fields = Utils::map_class_properties_to_fields( [], $field );

			/**
			 * @todo: Need to add any other properties that are not "string", "bool" or "int"
			 */

			/**
			 * Add a "value" field to the schema that resolves the actual field value
			 * stored in the database
			 */
			$fields['value'] = [
				'type'    => \WPGraphQL\Types::string(),
				'resolve' => function( $root ) use ( $fm_context, $field ) {
					$value = self::resolve_field_value( $root, $field, $fm_context );

					return ! empty( $value ) ? $value : null;
				},
			];

			/**
			 * Return the prepared fields (filtered/sorted)
			 */
			return self::prepare_fields( $fields, Utils::_graphql_label( $field->name ) );

		} // End if().

		return ! empty( self::$fields[ $field->name ] ) ? self::$fields[ $field->name ] : null;

	}

	/**
	 * This resolves the value of a particular field.
	 *
	 * @param array                      $root       The field array passed down the resolve tree
	 * @param object                     $field      The field object being resolved
	 * @param \Fieldmanager_Context_Post $fm_context The context for the field being resolved
	 *
	 * @return mixed|null
	 */
	private function resolve_field_value( $root, $field, \Fieldmanager_Context_Post $fm_context ) {

		if ( is_array( $root['value'] ) ) {
			$value = ! empty( $root['value'][ $field->name ] ) ? $root['value'][ $field->name ] : null;
		} else {
			$value = $root['value'];
		}

		return $value;
	}

}
