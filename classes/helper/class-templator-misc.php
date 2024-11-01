<?php
/**
 * Image Importer
 *
 * => How to use?
 *
 *  $image = array(
 *      'url' => '<image-url>',
 *      'id'  => '<image-id>',
 *  );
 *
 *  $downloaded_image = Templator_Misc::get_instance()->import( $image );
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_Misc' ) ) :

	/**
	 * Templator Importer
	 *
	 * @since 0.1.0
	 */
	class Templator_Misc {

		/**
		 * Instance
		 *
		 * @since 0.1.0
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Images IDs
		 *
		 * @var array   The Array of already image IDs.
		 * @since 0.1.0
		 */
		private $already_imported_ids = array();

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
		 * Get portfolio image URL.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $image_id Attachment image ID.
		 * @param  string $size     Attachment image size.
		 * @return string           Image URL.
		 */
		public static function get_image_url_by_id( $image_id = '', $size = 'medium' ) {
			if ( empty( $image_id ) ) {
				return null;
			}

			$image_attributes = wp_get_attachment_image_src( $image_id, $size );
			if ( $image_attributes ) {
				return $image_attributes[0];

			}

			$image_attributes = wp_get_attachment_image_src( $image_id, 'full' );
			return $image_attributes[0];
		}

		/**
		 * Supported post types
		 *
		 * @since 0.1.0
		 *
		 * @return array Supported post types.
		 */
		public static function supported_post_types() {
			return apply_filters(
				'templator_supported_post_types', array(
					'post',
					'page',
					'elementor_library',
				)
			);
		}

		/**
		 * Check supported post type
		 *
		 * @since 0.1.0
		 *
		 * @param  string $post_type Post type.
		 * @return boolean Supported post type status.
		 */
		public static function is_supported_post( $post_type = '' ) {
			if ( in_array( $post_type, self::supported_post_types() ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_Misc::get_instance();

endif;
