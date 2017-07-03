<?php
/**
 * Plugin Name:     WPGraphQL Fieldmanager
 * Plugin URI:      https://github.com/wp-graphql/wp-graphql
 * Description:     GraphQL Bindings for Fieldmanager
 * Author:          WPGraphQL, Jason Bahl
 * Author URI:      https://www.wpgraphql.com
 * Text Domain:     wp-graphql-fieldmanager
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WPGraphQL_Fieldmanager
 */

namespace WPGraphQL\Extensions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WPGraphQL\Extensions\Fieldmanager' ) ) :

	final class Fieldmanager {

		/**
		 * Stores the instance of the WPGraphQL\Extensions\Fieldmanager class
		 *
		 * @var Fieldmanager The one true WPGraphQL\Extensions\Fieldmanager
		 * @access private
		 */
		private static $instance;

		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Fieldmanager ) ) {
				self::$instance = new Fieldmanager;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->actions();
				self::$instance->filters();
			}

			/**
			 * Fire off init action
			 *
			 * @param Fieldmanager $instance The instance of the WPGraphQL\Extensions\Fieldmanager class
			 */
			do_action( 'graphql_fieldmanager_init', self::$instance );

			/**
			 * Return the WPGraphQL Instance
			 */
			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @access public
		 * @return void
		 */
		public function __clone() {

			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'The \WPGraphQL\Extensions\Fieldmanager class should not be cloned.', 'wp-graphql-fieldmanager' ), '0.0.1' );

		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {

			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the \WPGraphQL\Extensions\Fieldmanager class is not allowed', 'wp-graphql-fieldmanager' ), '0.0.1' );

		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'WPGRAPHQL_FIELDMANAGER_VERSION' ) ) {
				define( 'WPGRAPHQL_FIELDMANAGER_VERSION', '0.1.0' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'WPGRAPHQL_FIELDMANAGER_PLUGIN_DIR' ) ) {
				define( 'WPGRAPHQL_FIELDMANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'WPGRAPHQL_FIELDMANAGER_PLUGIN_URL' ) ) {
				define( 'WPGRAPHQL_FIELDMANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'WPGRAPHQL_FIELDMANAGER_PLUGIN_FILE' ) ) {
				define( 'WPGRAPHQL_FIELDMANAGER_PLUGIN_FILE', __FILE__ );
			}

		}

		/**
		 * Include required files.
		 * Uses composer's autoload
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {

			// Autoload Required Classes
			require_once( WPGRAPHQL_FIELDMANAGER_PLUGIN_DIR . 'vendor/autoload.php' );

		}

		/**
		 * Sets up actions to run at certain spots throughout WordPress and the WPGraphQL execution cycle
		 */
		private function actions() {

			/**
			 * Fieldmanager fields are associated with specific Admin contexts to ensure the classes, etc aren't unnecessarily exposed
			 * to the WP Admin when they shouldn't be. This sets up the contexts to allow the Fieldmanager Fields
			 * to be exposed to the WPGraphQL Schema
			 *
			 * The priority must be lower than the priority for adding fields to types to ensure that the fields are registered
			 * before attempting to add them to the types
			 *
			 * @see: https://github.com/alleyinteractive/wordpress-fieldmanager/compare/rest_api?expand=1#diff-e3440d8ed6d5c6c9afc90e2261f4e9a7R461
			 */
			add_action( 'graphql_generate_schema', [ '\WPGraphQL\Extensions\Fieldmanager\Actions', 'setup_fm_context' ], 5 );

			/**
			 * Hook into WPGraphQL when the schema is being generated and add the Fieldmanager fields to the GraphQL Schema
			 */
			add_action( 'graphql_generate_schema', [ '\WPGraphQL\Extensions\Fieldmanager\Actions', 'add_fields_to_types' ], 99 );

		}

		/**
		 * Setup filters
		 */
		private function filters() {
			// Placeholder for future filter needs
		}

		/**
		 * This is a static method to be used for adding Fieldmanager config to the GraphQL Schema.
		 *
		 * To add a Fieldmanager config to the GraphQL Schema, simply call:
		 *
		 * WPGraphQL\Extensions\Fieldmanager::add_fields_to_graphql( $fm_config );
		 *
		 * For example:
		 *
		 * add_action( 'fm_post_post', function() {
		 *   $fm = new Fieldmanager_TextField([
		 *     'name' => 'demo_field',
		 *     'description' => 'Demo Field',
		 *   ]);
		 *
		 *   $fm_config = $fm->add_meta_box( 'Single Field', ['post'] );
		 *   \WPGraphQL\Extensions\Fieldmanager::add_fields_to_graphql( $fm_config );
		 * }
		 *
		 * @param object $fieldmanager_config The Fieldmanager context object that is returned when associating Fieldmanager to WordPress.
		 */
		public static function add_fields( $fieldmanager_config ) {

			if ( ! empty( $fieldmanager_config ) ) {
				add_filter( 'graphql_fieldmanager_schema', function( $fields ) use ( $fieldmanager_config ) {
					$fields[] = $fieldmanager_config;
					return $fields;
				} );
			}
		}

	}

endif;

function fieldmanager_init() {
	return FIELDMANAGER::instance();
}

add_action( 'graphql_init', '\WPGraphQL\Extensions\fieldmanager_init' );
