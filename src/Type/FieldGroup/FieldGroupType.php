<?php
namespace WPGraphQL\Extensions\Fieldmanager\Type\FieldGroup;

use WPGraphQL\Extensions\Fieldmanager\Actions;
use WPGraphQL\Extensions\Fieldmanager\Utils;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Types;

class FieldGroupType extends WPObjectType {

	private static $fields;

	public function __construct( \Fieldmanager_Group $field_group, $fm_context ) {

		$config = [
			'name' => ! empty( $field_group->name ) ? Utils::_graphql_label( $field_group->name ) : 'fmField',
			'fields' => self::fields( $field_group, $fm_context ),
			'description' => ! empty( $field_group->description ) ? $field_group->description : __( 'Fieldmanager group', 'wp-graphql-fieldmanager' ),
			'fm_context' => $fm_context,
			'field_group' => $field_group,
		];

		parent::__construct( $config );
	}

	private function fields( \Fieldmanager_Group $field_group, $fm_context ) {

		if ( null === self::$fields ) {
			self::$fields = [];
		}

		if ( empty( self::$fields[ Utils::_graphql_label( $field_group->name ) ] ) ) {

			// $field_group->add_more_position

			$fields = [
				'attributes' => [
					'type' => Types::string(),
					'description' => '',
					'resolve' => function( \Fieldmanager_Group $group ) {
						return ! empty( $group->attributes ) ? $group->attributes : null;
					},
				],
				'description' => [
					'type' => Types::string(),
					'description' => __( 'Description of the field group', 'wp-graphql' ),
					'resolve' => function( \Fieldmanager_Group $group ) {
						return $group->description;
					},
				],
			];

			if ( ! empty( $field_group->children ) && is_array( $field_group->children ) ) {
				foreach ( $field_group->children as $child_field ) {
					$fields = Actions::add_field( $fields, $child_field, $fm_context );
				}
			}

			return self::prepare_fields( $fields, Utils::_graphql_label( $field_group->name ) );

		}

		return ! empty( self::$fields[ $field_group->name ] ) ? self::$fields[ $field_group->name ] : null;

	}

}
