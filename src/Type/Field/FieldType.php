<?php
namespace WPGraphQL\Extensions\Fieldmanager\Type\FieldType;

use WPGraphQL\Extensions\Fieldmanager\Utils;
use WPGraphQL\Type\WPObjectType;

class FieldType extends WPObjectType {

	private static $fields;

	public function __construct( \Fieldmanager_Field $field, $fm_context ) {

		$config = [
			'name' => ! empty( $field->name ) ? Utils::_graphql_label( $field->name ) : 'fmField',
			'fields' => self::fields( $field, $fm_context ),
			'description' => ! empty( $field->description ) ? $field->description : __( 'Fieldmanager field', 'wp-graphql-fieldmanager' ),
		];

		parent::__construct( $config );
	}

	private function fields( \Fieldmanager_Field $field, $fm_context ) {

		if ( null === self::$fields ) {
			self::$fields = [];
		}

		if ( empty( self::$fields[ Utils::_graphql_label( $field->name ) ] ) ) {

			$fields = [
				'value' => [
					'type' => \WPGraphQL\Types::string(),
					'resolve' => function( $root ) use ( $fm_context, $field ) {
						$value = self::resolve_field_value( $root, $field, $fm_context );
						return ! empty( $value ) ? $value : null;
					},
				],
			];

			return self::prepare_fields( $fields, Utils::_graphql_label( $field->name ) );

		}

		return ! empty( self::$fields[ $field->name ] ) ? self::$fields[ $field->name ] : null;

	}

	private function resolve_field_value( $root, $field, \Fieldmanager_Context_Post $fm_context ) {

		if ( is_array( $root['value'] ) ) {
			$value = ! empty( $root['value'][ $field->name ] ) ? $root['value'][ $field->name ] : null;
		} else {
			$value = $root['value'];
		}

		return $value;
	}

}
