<?php
namespace WPGraphQL\Extensions\Fieldmanager;

use WPGraphQL\Extensions\Fieldmanager\Type\FieldGroup\FieldGroupType;
use WPGraphQL\Extensions\Fieldmanager\Type\FieldType\FieldType;

class Types {

	private static $field_type;
	private static $field_group_type;

	/**
	 * @param $type
	 *
	 * @return mixed|null
	 */
	public static function field_type( $field, $fm_context ) {

		if ( null === self::$field_type ) {
			self::$field_type = [];
		}

		if ( is_object( $field ) && ! empty( $field->name ) && empty( self::$field_type[ Utils::_graphql_label( $field->name ) ] ) ) {
			self::$field_type[ Utils::_graphql_label( $field->name ) ] = new FieldType( $field, $fm_context );
		}

		return ! empty( self::$field_type[ Utils::_graphql_label( $field->name ) ] ) ? self::$field_type[ Utils::_graphql_label( $field->name ) ] : null;

	}

	/**
	 * @return FieldGroupType
	 */
	public static function field_group_type( $field, $fm_context ) {

		if ( null === self::$field_group_type ) {
			self::$field_group_type = [];
		}

		if ( is_object( $field ) && ! empty( $field->name ) && empty( self::$field_group_type[ Utils::_graphql_label( $field->name ) ] ) ) {
			self::$field_group_type[ Utils::_graphql_label( $field->name ) ] = new FieldGroupType( $field, $fm_context );
		}

		return ! empty( self::$field_group_type[ Utils::_graphql_label( $field->name ) ] ) ? self::$field_group_type[ Utils::_graphql_label( $field->name ) ] : null;

	}

}
