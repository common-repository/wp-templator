<?php
/**
 * Templator API
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_API' ) ) :

	/**
	 * Templator API
	 *
	 * @since 0.1.0
	 */
	class Templator_API {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 0.1.0
		 */
		private static $instance;

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
		 * @return string Site URL.
		 */
		public static function get_site_url() {
			return trailingslashit( Templator_Admin::get_instance()->get_page_setting( 'site_url' ) ) . '/';
		}

		/**
		 * Get Client Site Templates Rest API URL.
		 *
		 * @since 0.1.0
		 *
		 * @return string API site URL.
		 */
		public static function get_template_endpoint_url() {
			return self::get_site_url() . 'wp-json/wp/v2/templator/';
		}

		/**
		 * Get Client Site Category Rest API URL.
		 *
		 * @since 0.1.0
		 *
		 * @return string API site URL.
		 */
		public static function get_category_endpoint_url() {
			return self::get_site_url() . 'wp-json/wp/v2/templator-category/';
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
			return self::get_site_url() . 'wp-json/templator/v1/' . $api_base;
		}

		/**
		 * Get single demo.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $site_id  Template ID of the site.
		 * @return array            Template data.
		 */
		public static function get_template( $site_id ) {

			$request_params = array(
				'license' => Templator_Admin::get_instance()->get_page_setting( 'license_key' ),
			);

			$url = add_query_arg( $request_params, self::get_template_endpoint_url() . $site_id );

			$api_args = array(
				'timeout' => 15,
			);

			$response = self::remote_get( $url, $api_args );
			$data     = array();
			if ( $response['success'] ) {
				$template = $response['data'];
				return array(
					'title'         => ( isset( $template['title']->rendered ) ) ? $template['title']->rendered : '',
					'template_data' => ( isset( $template['template_data'] ) ) ? $template['template_data'] : '',
					'data'          => $template,
					'message'       => __( 'Your API Key is not valid. Please add valid API Key.', 'wp-templator' ),
				);
			}

			return array(
				'title'         => '',
				'template_data' => array(),
				'message'       => $response['message'],
				'data'          => $response['data'],
			);
		}

		/**
		 * Get Cloud Templates
		 *
		 * @since 0.1.0
		 *
		 * @param  array $args For selecting the demos (Search terms, pagination etc).
		 * @return array        Templator list.
		 */
		public static function get_templates( $args = array() ) {

			$request_params = wp_parse_args(
				$args, array(
					'page'     => '1',
					'per_page' => '100',
				)
			);

			$url = add_query_arg( $request_params, self::get_template_endpoint_url() );

			$api_args = array(
				'timeout' => 15,
			);

			$response = self::remote_get( $url, $api_args );

			if ( $response['success'] ) {
				$templates_data = $response['data'];
				$templates      = array();
				foreach ( $templates_data as $key => $template ) {

					if ( ! isset( $template->id ) ) {
						continue;
					}

					$templates[ $key ]['id']                 = isset( $template->id ) ? esc_attr( $template->id ) : '';
					$templates[ $key ]['slug']               = isset( $template->slug ) ? esc_attr( $template->slug ) : '';
					$templates[ $key ]['link']               = isset( $template->link ) ? esc_url( $template->link ) : '';
					$templates[ $key ]['date']               = isset( $template->date ) ? esc_attr( $template->date ) : '';
					$templates[ $key ]['title']              = isset( $template->title->rendered ) ? esc_attr( $template->title->rendered ) : '';
					$templates[ $key ]['featured_image_url'] = isset( $template->featured_image_url ) ? esc_url( $template->featured_image_url ) : '';
					$templates[ $key ]['content']            = isset( $template->content->rendered ) ? strip_tags( $template->content->rendered ) : '';
					$templates[ $key ]['template_data']      = isset( $template->template_data ) ? $template->template_data : '';
				}

				return array(
					'templates'       => $templates,
					'templates_count' => $response['count'],
					'data'            => $response,
				);
			}

			return array(
				'templates'       => array(),
				'templates_count' => 0,
				'data'            => $response,
			);

		}

		/**
		 * Get categories.
		 *
		 * @since 0.1.0
		 * @param  array $args Arguments.
		 * @return array        Category data.
		 */
		public static function get_categories( $args = array() ) {

			$request_params = apply_filters(
				'templator_categories_api_params',
				wp_parse_args(
					$args, array(
						'page'     => '1',
						'per_page' => '100',
					)
				)
			);

			$url = add_query_arg( $request_params, self::get_category_endpoint_url() );

			$api_args = apply_filters(
				'templator_api_args', array(
					'timeout' => 15,
				)
			);

			$response = self::remote_get( $url, $api_args );

			if ( $response['success'] ) {
				$categories_data = $response['data'];
				$categories      = array();

				foreach ( $categories_data as $key => $category ) {
					if ( isset( $category->count ) && ! empty( $category->count ) ) {
						$categories[] = array(
							'id'          => isset( $category->id ) ? absint( $category->id ) : 0,
							'count'       => isset( $category->count ) ? absint( $category->count ) : 0,
							'description' => isset( $category->description ) ? $category->description : '',
							'link'        => isset( $category->link ) ? esc_url( $category->link ) : '',
							'name'        => isset( $category->name ) ? $category->name : '',
							'slug'        => isset( $category->slug ) ? sanitize_text_field( $category->slug ) : '',
							'taxonomy'    => isset( $category->taxonomy ) ? $category->taxonomy : '',
							'parent'      => isset( $category->parent ) ? $category->parent : '',
						);
					}
				}

				return array(
					'categories'       => $categories,
					'categories_count' => $response['count'],
					'data'             => $response,
				);
			}

			return array(
				'categories'       => array(),
				'categories_count' => 0,
				'data'             => $response,
			);
		}

		/**
		 * Remote GET API Request
		 *
		 * @since 0.1.0
		 *
		 * @param  string $url      Target server API URL.
		 * @param  array  $args    Array of arguments for the API request.
		 * @return mixed            Return the API request result.
		 */
		public static function remote_get( $url = '', $args = array() ) {
			$request = wp_remote_get( $url, $args );
			return self::request( $request );
		}

		/**
		 * Remote POST API Request
		 *
		 * @since 0.1.0
		 *
		 * @param  string $url      Target server API URL.
		 * @param  array  $args    Array of arguments for the API request.
		 * @return mixed            Return the API request result.
		 */
		public static function remote_post( $url = '', $args = array() ) {
			$request = wp_remote_post( $url, $args );

			return self::request( $request );
		}

		/**
		 * Site API Request
		 *
		 * @since 0.1.0
		 *
		 * @param  boolean $api_base Target server API URL.
		 * @param  array   $args    Array of arguments for the API request.
		 * @return mixed           Return the API request result.
		 */
		public static function site_request( $api_base = '', $args = array() ) {

			$api_url = self::get_request_api_url( $api_base );

			return self::remote_post( $api_url, $args );
		}

		/**
		 * API Request
		 *
		 * Handle the API request and return the result.
		 *
		 * @since 0.1.0
		 *
		 * @param  array $request    Array of arguments for the API request.
		 * @return mixed           Return the API request result.
		 */
		public static function request( $request ) {

			// Is WP Error?
			if ( is_wp_error( $request ) ) {
				return array(
					'success' => false,
					'message' => $request->get_error_message(),
					'data'    => $request,
					'count'   => 0,
				);
			}

			// Invalid response code.
			if ( wp_remote_retrieve_response_code( $request ) != 200 ) {
				return array(
					'success' => false,
					'message' => $request['response'],
					'data'    => $request,
					'count'   => 0,
				);
			}

			// Get body data.
			$body = wp_remote_retrieve_body( $request );

			// Is WP Error?
			if ( is_wp_error( $body ) ) {
				return array(
					'success' => false,
					'message' => $body->get_error_message(),
					'data'    => $request,
					'count'   => 0,
				);
			}

			// Decode body content.
			$body_decoded = json_decode( $body );

			return array(
				'success' => true,
				'message' => __( 'Request successfully processed!', 'wp-templator' ),
				'data'    => (array) $body_decoded,
				'count'   => wp_remote_retrieve_header( $request, 'x-wp-total' ),
			);
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_API::get_instance();

endif;
