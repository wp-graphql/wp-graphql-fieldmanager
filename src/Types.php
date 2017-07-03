<?php
namespace WPGraphQL\Extensions\Fieldmanager;

use WPGraphQL\Extensions\Fieldmanager\Type\Enum\AddMorePositionEnum;
use WPGraphQL\Extensions\Fieldmanager\Type\FieldGroup\FieldGroupType;
use WPGraphQL\Extensions\Fieldmanager\Type\FieldType\FieldType;

class Types {

	private static $add_more_position_enum;
	private static $field_type;
	private static $field_group_type;

	/**
	 * @return AddMorePositionEnum
	 */
	public static function add_more_position_enum() {
		return self::$add_more_position_enum ? : ( self::$add_more_position_enum = new AddMorePositionEnum() );
	}

	/**
	 * @param  $field
	 * @param  $fm_context
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
