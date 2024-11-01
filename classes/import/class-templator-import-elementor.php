<?php
/**
 * Elementor Images Batch Processing
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// If plugin - 'Elementor' not exist then return.
if ( ! class_exists( '\Elementor\Plugin' ) ) {
	return;
}

/**
 * Elementor Demo Import
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_Import_Elementor' ) ) :

	/**
	 * Templator_Import_Elementor
	 *
	 * @since 0.1.0
	 */
	class Templator_Import_Elementor {

		/**
		 * Instance
		 *
		 * @since 0.1.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 0.1.0
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
		 * @since 0.1.0
		 */
		public function __construct() {
		}

		/**
		 * Replace Elements Ids
		 *
		 * @param  string $content Context.
		 * @return array    Element.
		 */
		public function replace_elements_ids( $content ) {
			return \Elementor\Plugin::$instance->db->iterate_data(
				$content, function( $element ) {
					$element['id'] = \Elementor\Utils::generate_random_string();
					return $element;
				}
			);
		}

		/**
		 * Process Import Content.
		 *
		 * @param array  $content a set of elements.
		 * @param string $method  (on_export|on_import).
		 *
		 * @return mixed
		 */
		public function process_export_import_content( $content, $method ) {
			return \Elementor\Plugin::$instance->db->iterate_data(
				$content, function( $element_data ) use ( $method ) {
					$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_data );

					// If the widget/element isn't exist, like a plugin that creates a widget but deactivated.
					if ( ! $element ) {
						return null;
					}

					return Templator_Import_Elementor::get_instance()->process_element_export_import_content( $element, $method );
				}
			);
		}

		/**
		 * Process Element/Export Import Content.
		 *
		 * @param \Elementor\Controls_Stack $element Element.
		 * @param string                    $method Method.
		 *
		 * @return array
		 */
		public function process_element_export_import_content( $element, $method ) {
			$element_data = $element->get_data();

			if ( method_exists( $element, $method ) ) {
				// TODO: Use the internal element data without parameters.
				$element_data = $element->{$method}( $element_data );
			}

			foreach ( $element->get_controls() as $control ) {

				if ( 'media' === $control['type'] ) {
					$element_data['settings'][ $control['name'] ] = self::on_import_media( $element->get_settings( $control['name'] ) );
				}

				if ( 'gallery' === $control['type'] ) {
					$element_data['settings'][ $control['name'] ] = self::on_import_gallery( $element->get_settings( $control['name'] ) );
				}

				// @codingStandardsIgnoreStart
				// TODO: Add support for repeater type.
				// if( 'repeater' === $control['type'] ) {
				// vl( $control['type'] );
				// $element_data['settings'][ $control['name'] ] = self::on_import_repeater( $element->get_settings( $control['name'] ) );
				// }
				// @codingStandardsIgnoreEnd
			}

			return $element_data;
		}

		/**
		 * Import gallery images.
		 *
		 * Used to import gallery control files from external sites while importing
		 * Elementor template JSON file, and replacing the old data.
		 *
		 * @since 0.1.0
		 * @access public
		 *
		 * @param array $settings Control settings.
		 * @param array $control_data Control data.
		 *
		 * @return array Control settings.
		 */
		public function on_import_repeater( $settings, $control_data = array() ) {
			if ( empty( $settings ) || empty( $control_data['fields'] ) ) {
				return $settings;
			}

			$method = 'on_import';

			foreach ( $settings as &$item ) {
				foreach ( $control_data['fields'] as $field ) {
					if ( empty( $field['name'] ) || empty( $item[ $field['name'] ] ) ) {
						continue;
					}

					// @codingStandardsIgnoreStart
					// TODO: Add support for repeater type.
					// $control_obj = Plugin::$instance->controls_manager->get_control( $field['type'] );
					// if ( ! $control_obj ) {
					// continue;
					// }
					// if ( method_exists( $control_obj, $method ) ) {
					// $item[ $field['name'] ] = $control_obj->{$method}( $item[ $field['name'] ], $field );
					// }
					// @codingStandardsIgnoreEnd
				}
			}

			return $settings;
		}

		/**
		 * Import gallery images.
		 *
		 * Used to import gallery control files from external sites while importing
		 * Elementor template JSON file, and replacing the old data.
		 *
		 * @since 0.1.0
		 * @access public
		 *
		 * @param array $settings Control settings.
		 *
		 * @return array Control settings.
		 */
		public static function on_import_media( $settings ) {
			if ( empty( $settings['url'] ) ) {
				return $settings;
			}

			$settings = Templator_Import_Core::get_instance()->import( $settings );

			if ( ! $settings ) {
				$settings = array(
					'id'  => '',
					'url' => '',
				);
			}

			return $settings;
		}

		/**
		 * Import gallery images.
		 *
		 * Used to import gallery control files from external sites while importing
		 * Elementor template JSON file, and replacing the old data.
		 *
		 * @since 0.1.0
		 * @access public
		 *
		 * @param array $settings Control settings.
		 *
		 * @return array Control settings.
		 */
		public static function on_import_gallery( $settings ) {
			foreach ( $settings as &$attachment ) {
				if ( empty( $attachment['url'] ) ) {
					continue;
				}

				$attachment = Templator_Import_Core::get_instance()->import( $attachment );
			}

			// Filter out attachments that don't exist.
			$settings = array_filter( $settings );

			return $settings;
		}

		/**
		 * Import single post.
		 *
		 * @since 0.1.0
		 *
		 * @param  integer $post_id  Post id.
		 * @param  array   $raw_data  Post meta data.
		 * @return array             Post meta data.
		 */
		public function import_single_post( $post_id = 0, $raw_data = array() ) {
			Templator_Import_Core::log( '---- Processing WordPress Posts / Pages - for Elementor ----' );
			Templator_Import_Core::log( 'Post ID: ' . $post_id );

			if ( ! empty( $post_id ) ) {

				$data = $this->replace_elements_ids( $raw_data );
				$data = $this->process_export_import_content( $data, 'on_import' );

				// Update processed meta.
				update_post_meta( $post_id, '_elementor_data', $data );

				// !important, Clear the cache after images import.
				\Elementor\Plugin::$instance->posts_css_manager->clear_cache();

				return $data;
			}

			return;
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_Import_Elementor::get_instance();

endif;
