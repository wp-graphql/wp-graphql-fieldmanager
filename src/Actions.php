<?php
namespace WPGraphQL\Extensions\Fieldmanager;

use WPGraphQL\Extensions\Fieldmanager\Data\Post;

class Actions {

	public function add_fields_to_types() {

		$fm_context_config = apply_filters( 'graphql_fieldmanager_schema', [] );

		if ( ! empty( $fm_context_config ) && is_array( $fm_context_config ) ) {
			foreach ( $fm_context_config as $fm_context ) {

				switch ( true ) {

					/**
					 * Add Fieldmanager fields that are attached to posts to their appropriate GraphQL Schema
					 */
					case $fm_context instanceof \Fieldmanager_Context_Post:

						if ( ! empty( $fm_context->post_types ) && is_array( $fm_context->post_types ) ) {
							foreach ( $fm_context->post_types as $post_type ) {
								$post_type_object = get_post_type_object( $post_type );
								$type             = $post_type_object->graphql_single_name;
								if ( ! empty( $type ) ) {

									/**
									 * Filter the GraphQL Fields for the post_type's GraphQL Type
									 */
									add_filter( "graphql_{$type}_fields", function( $fields ) use ( $fm_context ) {

										/**
										 * Map the fields to GraphQL and return a new list of fields
										 */
										$new_fields = Actions::map_to_graphql( $fields, $fm_context );

										/**
										 * If there are $new_fields, return them, otherwise return the default $fields
										 */
										return ! empty( $new_fields ) ? $new_fields : $fields;

									}, 10, 1 );
								} // End if().
							} // End foreach().
						} // End if().

						break;
					case $fm_context instanceof \Fieldmanager_Context_Submenu:
						break;
					case $fm_context instanceof \Fieldmanager_Context_Term:
						break;
					case $fm_context instanceof \Fieldmanager_Context_User:
						break;
					default:
						break;

				} // End switch().
			} // End foreach().
		} // End if().

	}

	/**
	 * This takes an array of existing fields and an instance of $fm_context and maps the Fieldmanager fields to the
	 * GraphQL fields
	 *
	 * @param array $fields Existing fields on a GraphQL Type
	 * @param object $fm_context An instance of Fieldmanager Context
	 *
	 * @return mixed
	 */
	public static function map_to_graphql( $fields, $fm_context ) {

		/**
		 * Ensure the $fm_context is an object
		 */
		if ( ! empty( $fm_context ) && is_object( $fm_context ) ) {

			/**
			 * Determine which context
			 */
			switch ( true ) {
				case $fm_context instanceof \Fieldmanager_Context_Post:

					if ( ! empty( $fm_context->fm ) ) {
						$fields = Actions::add_field( $fields, $fm_context->fm, $fm_context );
					}

					break;
				default:
					break;
			} // End switch().
		} // End if().

		return $fields;
	}

	/**
	 * This takes an array of GraphQL fields, a Fieldmanager Field and the Fieldmanager Context and adds the Fieldmanager
	 * field to the GraphQL schema
	 *
	 * @param array $fields An array of GraphQL Fields on a Type
	 * @param object $fm_field An instance of a Fieldmanager field
	 * @param object $fm_context An instance of Fieldmanager context
	 *
	 * @return mixed
	 */
	public static function add_field( $fields, $fm_field, $fm_context ) {

		/**
		 * If the fieldmanager field is an object
		 */
		if ( ! empty( $fm_field ) && is_object( $fm_field ) ) {

			/**
			 * Determine if the field is a Group or not, and add to the Schema
			 */
			switch ( true ) {

				/**
				 * Add "field_group" to the Schema
				 */
				case $fm_field instanceof \Fieldmanager_Group:

					$fields[ Utils::_graphql_label( $fm_field->name ) ] = [
						'type' => Types::field_group_type( $fm_field, $fm_context ),
						'resolve' => function( $root ) use ( $fm_field, $fm_context ) {
							$field = self::resolve_field( $root, $fm_field, $fm_context );
							return $field;
						},
					];

					break;

				/**
				 * Otherwise, add a "field" to the schema
				 */
				default:
					$fields[ Utils::_graphql_label( $fm_field->name ) ] = [
						'type' => Types::field_type( $fm_field, $fm_context ),
						'resolve' => function( $root ) use ( $fm_field, $fm_context ) {
							$field = self::resolve_field( $root, $fm_field, $fm_context );
							return $field;

						},
					];
					break;
			} // End switch().
		} // End if().

		return $fields;
	}

	/**
	 * This takes in the resolving root object, the fieldmanager field, and the fieldmanager context and resolves the
	 * field
	 *
	 * @param $root
	 * @param $fm_field
	 * @param $fm_context
	 *
	 * @return mixed
	 */
	public static function resolve_field( $root, $fm_field, $fm_context ) {

		$field['field'] = $fm_field;
		$field['context'] = $fm_context;

		/**
		 * Determine the object being resolved so we can get the field value from the proper context
		 */
		switch ( true ) {

			case $root instanceof \WP_Post:
				$post = new Post( 'graphql', $fm_context->post_types, $fm_context->context, $fm_context->priority, $fm_context->fm );
				$post->fm->data_id = $root->ID;
				$post->fm->data_type = 'post';
				$value = $post->resolve();
				$field['value'] = $value;
				break;

			case $root instanceof \WP_Term:
				$field['object_id'] = $root->term_id;
				$field['object_type'] = 'term';
				break;

			case $root instanceof \WP_User:
				$field['object_id'] = $root->ID;
				$field['object_type'] = 'user';
				break;

			/**
			 * If there's already a field value defined with the field's name as a key, use that as the value
			 */
			default:
				$field['value'] = ( is_array( $root['value'] ) && ! empty( $root['value'][ $fm_field->name ] ) ) ? $root['value'][ $fm_field->name ] : null;
		} // End switch().

		return $field;
	}

	/**
	 * This sets up the Fieldmanager Contexts to be available in the GraphQL Schema
	 */
	public function setup_fm_context() {

		$post_types = \WPGraphQL::get_allowed_post_types();
		$taxonomies = \WPGraphQL::get_allowed_taxonomies();

		/**
		 * Allow fields connected to post_types to be exposed to GraphQL
		 */
		if ( ! empty( $post_types ) && is_array( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				do_action( "fm_post_{$post_type}" );
			}
		}

		/**
		 * Allow fields connected to taxonomies to be exposed to GraphQL
		 */
		if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				do_action( "fm_term_{$taxonomy}" );
			}
		}

		/**
		 * Allow fields connected to users to be exposed to GraphQL
		 */
		do_action( 'fm_user' );

	}

}
