<?php
namespace WPGraphQL\Extensions\Fieldmanager;

use WPGraphQL\Extensions\Fieldmanager\Data\Post;

class Actions {

	public function add_fields_to_types() {

		$fm_context_config = apply_filters( 'graphql_fieldmanager_schema', [] );
		if ( ! empty( $fm_context_config ) && is_array( $fm_context_config ) ) {
			foreach ( $fm_context_config as $fm_context ) {

				switch ( true ) {

					case $fm_context instanceof \Fieldmanager_Context_Post:

						if ( ! empty( $fm_context->post_types ) && is_array( $fm_context->post_types ) ) {
							foreach ( $fm_context->post_types as $post_type ) {
								$post_type_object = get_post_type_object( $post_type );
								$type             = $post_type_object->graphql_single_name;
								if ( ! empty( $type ) ) {
									add_filter( "graphql_{$type}_fields", function( $fields ) use ( $fm_context ) {
										return Actions::map_to_graphql( $fields, $fm_context );
									}, 10, 1 );
								}
							}
						}

						break;
					case $fm_context instanceof \Fieldmanager_Context_Page:
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

	public static function map_to_graphql( $fields, $fm_context ) {

		if ( ! empty( $fm_context ) && is_object( $fm_context ) ) {

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

	public static function add_field( $fields, $fm_field, $fm_context ) {

		if ( ! empty( $fm_field ) && is_object( $fm_field ) ) {

			switch ( true ) {
				case $fm_field instanceof \Fieldmanager_Group:
					$fields[ Utils::_graphql_label( $fm_field->name ) ] = [
						'type' => Types::field_group_type( $fm_field, $fm_context ),
						'resolve' => function( $root ) use ( $fm_field, $fm_context ) {
							$field = self::resolve_field( $root, $fm_field, $fm_context );
							return $field;
						},
					];
					break;
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

	public static function resolve_field( $root, $fm_field, $fm_context ) {

		$field['field'] = $fm_field;
		$field['context'] = $fm_context;

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
			default:
				$field['value'] = ! empty( $root['value'][ $fm_field->name ] ) ? $root['value'][ $fm_field->name ] : null;
		}

		return $field;
	}

}
