<?php

namespace WPGraphQL\Extensions\Fieldmanager\Type\FieldGroup;

use WPGraphQL\Extensions\Fieldmanager\Actions;
use WPGraphQL\Extensions\Fieldmanager\Utils;
use WPGraphQL\Type\WPObjectType;

/**
 * Class FieldGroupType
 *
 * @package WPGraphQL\Extensions\Fieldmanager\Type\FieldGroup
 */
class FieldGroupType extends WPObjectType {

	/**
	 * Holds the definition of the fields for the FieldGroupType
	 *
	 * @var array
	 */
	private static $fields;

	/**
	 * FieldGroupType constructor.
	 *
	 * @param \Fieldmanager_Group $field_group The instance of the Field_Group being queried
	 * @param object              $fm_context  The Context the Fieldmanager_Group is connected to
	 */
	public function __construct( \Fieldmanager_Group $field_group, $fm_context ) {

		$config = [
			'name'        => ! empty( $field_group->name ) ? Utils::_graphql_label( 'fm_' . $field_group->name . '_field_group' ) : 'fmFieldGroup',
			'fields'      => self::fields( $field_group, $fm_context ),
			'description' => ! empty( $field_group->description ) ? $field_group->description : __( 'Fieldmanager group', 'wp-graphql-fieldmanager' ),
			'fm_context'  => $fm_context,
			'field_group' => $field_group,
		];

		parent::__construct( $config );
	}

	/**
	 * Defines the Fields for the FieldmanagerGroupType schema
	 *
	 * @param \Fieldmanager_Group $field_group The instance of the Fieldmanager_Group being queried
	 * @param object              $fm_context  The Context the Fieldmanager_Group is connected to
	 *
	 * @return mixed|null
	 */
	private function fields( \Fieldmanager_Group $field_group, $fm_context ) {

		if ( null === self::$fields ) {
			self::$fields = [];
		}

		if ( empty( self::$fields[ Utils::_graphql_label( $field_group->name ) ] ) ) {

			/**
			 * Map the properties of the $field_group class in Fieldmanager to be properties
			 * of the WPGraphQL Schema
			 */
			$fields = Utils::map_class_properties_to_fields( [], $field_group );

			/**
			 * @todo: Need to add any other properties that are not "string", "bool" or "int"
			 */

			/**
			 * Add children fields to the Field Group schema
			 */
			if ( ! empty( $field_group->children ) && is_array( $field_group->children ) ) {
				foreach ( $field_group->children as $child_field ) {
					$fields = Actions::add_field( $fields, $child_field, $fm_context );
				}
			}

			/**
			 * Return the prepared fields (filtered/sorted)
			 */
			return self::prepare_fields( $fields, Utils::_graphql_label( $field_group->name ) );

		} // End if().

		return ! empty( self::$fields[ $field_group->name ] ) ? self::$fields[ $field_group->name ] : null;

	}

}

