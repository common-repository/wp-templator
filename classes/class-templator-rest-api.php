<?php
/**
 * Templator Server Rest API update
 *
 * @package Templator Server
 * @since 0.1.1
 */

if ( ! class_exists( 'Templator_Rest_API' ) ) :

	/**
	 * Templator_Rest_API
	 *
	 * @since 0.1.1
	 */
	class Templator_Rest_API {

		/**
		 * Instance
		 *
		 * @since 0.1.1
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 0.1.1
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 0.1.1
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'api_actions' ) );
		}

		/**
		 * Initiate api actions
		 *
		 * @since 0.1.1
		 * @return void
		 */
		function api_actions() {
			register_rest_route(
				TEMPLATOR_API_NAMESPACE, '/update',
				array(
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'update_template' ),
					),
				)
			);
		}

		/**
		 * Retrieves all products.
		 *
		 * E.g. <site-url>/wp-json/templator/v1/update?license_key=<license-key>&post_id=<post_id>&export_status=<status>
		 *
		 * @since 0.1.1
		 * @access public
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
		 */
		public function update_template( $request ) {
			$args = $request->get_params();

			$post_id             = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
			$export_status       = isset( $args['export_status'] ) ? sanitize_key( $args['export_status'] ) : '';
			$request_license_key = isset( $args['license_key'] ) ? sanitize_key( $args['license_key'] ) : '';
			$stored_license_key  = Templator_Admin::get_instance()->get_page_setting( 'license_key' );

			if ( empty( $post_id ) || empty( $request_license_key ) || empty( $export_status ) ) {
				return new WP_Error( 'insufficient_inputs', __( 'To process the request please add \'post_id\', \'license_key\' and \'export_status\' details.', 'wp-templator' ) );
			}

			if ( empty( $stored_license_key ) ) {
				return new WP_Error( 'api_key_not_found', __( 'API Key not found on your site. Please validate API Key.', 'wp-templator' ) );
			}

			if ( $request_license_key !== $stored_license_key ) {
				return new WP_Error( 'api_key_invalid', __( 'Invalid API Key.', 'wp-templator' ) );
			}

			update_post_meta( $post_id, 'templator-export-status', $export_status );

			/* translators: %1$d is post id and %2$s is the exported template status. */
			return sprintf( __( 'Updated export post %1$d status with \'%2$s\'.', 'wp-templator' ), $post_id, $export_status );
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_Rest_API::get_instance();

endif;
