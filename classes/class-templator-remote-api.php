<?php
/**
 * Templator Remote API initial setup
 *
 * => Use 'Templator_Remote_API::store_request()' to send the request instead of wp_remote_post() / wp_remote_get().
 *
 * => Example
 *
 *      $response = Templator_Remote_API::store_request( 'user', array(
 *          'body' => array(
 *              'license_info' => '',
 *          ),
 *          'timeout'   => '30',
 *          'sslverify' => false
 *          // 'method' => 'POST',                      // Optional. Change method. Default 'GET'.
 *      ) );
 *
 *      // @DEBUG
 *      // var_dump( $response );
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_Remote_API' ) ) :

	/**
	 * Templator Remote API
	 *
	 * @since 0.1.0
	 */
	final class Templator_Remote_API {

		/**
		 * Instance
		 *
		 * @access private
		 * @since 0.1.0
		 * @var $instance instance of Class.
		 */
		private static $instance;

		/**
		 * The API URL of the server.
		 *
		 * @since 0.1.0
		 * @access public
		 * @var string $_updates_api_url
		 */
		static public $_updates_api_url;

		/**
		 * Initiator
		 *
		 * @since 0.1.0
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
		 * @since 0.1.0
		 */
		public function __construct() {
		}

		/**
		 * Get site URL.
		 *
		 * @since 0.1.0
		 *
		 * @param string $api_base base of api request.
		 * @return string API site URL.
		 */
		public static function get_api_site_url( $api_base = '' ) {
			// Check activation limit.
			$license_info = array(
				'key' => Templator_Admin::get_instance()->get_page_setting( 'license_key' ),
			);
			$response     = self::store_request(
				'user/', array(
					'body'      => array(
						'meta'    => 'blog_info',
						'license' => $license_info,
					),
					'timeout'   => '30',
					'sslverify' => false,
				)
			);

			if ( $response['success'] ) {
				$site_url = ( isset( $response['data']->siteurl ) ) ? esc_url( $response['data']->siteurl ) : '';
				if ( ! empty( $site_url ) ) {
					return rtrim( $site_url, '/' ) . '/';
				}
			}

			return $response;
		}

		/**
		 * Validate license key.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $license_key License key.
		 * @return array               License validation data.
		 */
		public static function activate_license( $license_key = '' ) {
			if ( empty( $license_key ) ) {
				return array(
					'success' => false,
					'message' => __( 'You didn\'t enter the API key. Please try again.', 'wp-templator' ),
					'data'    => null,
				);
			}

			$api_site_url = '';

			// Check activation limit.
			$license_info = array(
				'key' => $license_key,
			);
			$response     = self::store_request(
				'user/', array(
					'body'      => array(
						'meta'    => 'blog_info',
						'license' => $license_info,
					),
					'timeout'   => '30',
					'sslverify' => false,
				)
			);

			if ( $response['success'] ) {
				return array(
					'success' => true,
					'message' => $response['message'],
					'data'    => $response['data'],
				);
			}

			return array(
				'success' => false,
				'message' => $response['message'],
				'data'    => $response['data'],
			);
		}

		/**
		 * Get API request URL.
		 *
		 * @since 0.1.0
		 *
		 * @param string $api_base base of api request.
		 * @return string API site URL.
		 */
		public static function get_request_api_url( $api_base = '' ) {
			return TEMPLATOR_STORE_URL . 'wp-json/templator/v1/' . $api_base;
		}

		/**
		 * API Request
		 *
		 * Handle the API request and return the result.
		 *
		 * @param  boolean $api_base Target server API URL.
		 * @param  array   $args    Array of arguments for the API request.
		 * @return mixed           Return the API request result.
		 */
		public static function store_request( $api_base = '', $args = array() ) {
			$api_url = self::get_request_api_url( $api_base );

			$response = array(
				'success' => false,
				'message' => '',
				'data'    => '',
			);

			$request = wp_remote_post( $api_url, $args );

			// Is WP Error?
			if ( is_wp_error( $request ) ) {
				return array(
					'success' => false,
					'message' => $request->get_error_message(),
					'data'    => $request,
				);
			}

			// Invalid response code.
			if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
				return array(
					'success' => false,
					'message' => $request['response'],
					'data'    => $request,
				);
			}

			// Get body data.
			$body = wp_remote_retrieve_body( $request );

			// Is WP Error?
			if ( is_wp_error( $body ) ) {
				return array(
					'success' => false,
					'message' => $body->get_error_message(),
					'data'    => $body,
				);
			}

			// Decode body content.
			$body_decoded = json_decode( $body );

			return (array) $body_decoded;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Templator_Remote_API::get_instance();

endif;
