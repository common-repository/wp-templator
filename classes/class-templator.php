<?php
/**
 * Templator
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator' ) ) :

	/**
	 * Templator
	 *
	 * @since 0.1.0
	 */
	class Templator {

		/**
		 * Instance
		 *
		 * @since 0.1.0
		 * @access private
		 * @var object Class object.
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

			// Core Helpers - Image.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Helper.
			require_once TEMPLATOR_DIR . 'classes/helper/class-templator-import-core.php';
			require_once TEMPLATOR_DIR . 'classes/helper/class-templator-misc.php';

			// Import.
			require_once TEMPLATOR_DIR . 'classes/import/class-templator-import-elementor.php';
			require_once TEMPLATOR_DIR . 'classes/import/class-templator-import-beaver-builder.php';
			require_once TEMPLATOR_DIR . 'classes/import/class-templator-import.php';

			// Export.
			require_once TEMPLATOR_DIR . 'classes/export/class-templator-export.php';

			// Core.
			require_once TEMPLATOR_DIR . 'classes/class-templator-admin.php';
			require_once TEMPLATOR_DIR . 'classes/class-templator-api.php';
			require_once TEMPLATOR_DIR . 'classes/class-templator-remote-api.php';
			require_once TEMPLATOR_DIR . 'classes/class-templator-license.php';
			require_once TEMPLATOR_DIR . 'classes/class-templator-rest-api.php';

		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator::get_instance();

endif;
