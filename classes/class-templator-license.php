<?php
/**
 * Templator License
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_License' ) ) :

	/**
	 * Templator License
	 *
	 * @since 0.1.0
	 */
	class Templator_License {

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
			add_action( 'plugin_action_links_' . TEMPLATOR_BASE, array( $this, 'license_popup_link' ) );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_scripts' );
			add_action( 'wp_ajax_templator_activate_license', __CLASS__ . '::activate_license' );
			add_action( 'wp_ajax_templator_deactivate_license', __CLASS__ . '::deactivate_license' );
			add_action( 'admin_footer', __CLASS__ . '::export_popup' );
		}

		/**
		 * Export popup.
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		public static function export_popup() {
			$license_key = Templator_Admin::get_instance()->get_page_setting( 'license_key' );
			?>
			<div id="templator-license-popup-overlay" style="display:none;"></div>
			<div id="templator-license-popup" style="display:none;" data-license-key="<?php echo esc_attr( $license_key ); ?>">
				<div class="inner">
					<div class="heading">
						<span><?php _e( 'Activate Templator API', 'wp-templator' ); ?></span>
						<span class="templator-close-popup-button tb-close-icon"></span>
					</div>
					<div class="contents">
					</div>
				</div>
			</div>
			<script type="text/template" id="tmpl-templator-activate-license">
				<table class="widefat">
					<tr class="templator-row">
						<td class="templator-heading"><?php _e( 'API Key', 'wp-templator' ); ?></td>
						<td class="templator-content">
							<input type="text" placeholder="<?php _e( 'Enter your API key', 'wp-templator' ); ?>" class="regular-text license_key" name="license_key" value="" autocomplete="off">
							<?php ;/* translators: %1$s is site url. */ ?>
							<p class="description"><?php printf( __( 'If you don\'t have API key, you can get it from <a target="_blank" href="%1$s">here</a>.', 'wp-templator' ), 'https://wptemplator.com/' ); ?> </p>
						</td>
					</tr>
					<tr class="templator-row">
						<td colspan="2" class="submit-button-td">
							<span class="button button-primary templator-activate-license"><i class="templator-processing dashicons dashicons-update"></i><span class="text"><?php _e( 'Activate', 'wp-templator' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>
			<script type="text/template" id="tmpl-templator-deactivate-license">
				<table class="widefat">
					<tr class="templator-row">
						<td class="templator-heading"><?php _e( 'API Key', 'wp-templator' ); ?></td>
						<td class="templator-content">
							<input type="password" placeholder="<?php _e( '******************', 'wp-templator' ); ?>" class="regular-text license_key" value="" autocomplete="off" text="" readonly="readonly">
						</td>
					</tr>
					<tr class="templator-row">
						<td colspan="2" class="submit-button-td">
							<span class="button button-primary templator-deactivate-license"><i class="templator-processing dashicons dashicons-update"></i><span class="text"><?php _e( 'Deactivate', 'wp-templator' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>
			<?php
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function license_popup_link( $links ) {

			$license_key = Templator_Admin::get_instance()->get_page_setting( 'license_key' );
			if ( $license_key ) {
				$links['license_key'] = '<a href="#"" class="templator-license-popup-open-button inactive" aria-label="' . esc_attr__( 'Settings', 'wp-templator' ) . '">' . esc_html__( 'Deactivate API', 'wp-templator' ) . '</a>';
			} else {
				$links['license_key'] = '<a href="#"" class="templator-license-popup-open-button active" aria-label="' . esc_attr__( 'Settings', 'wp-templator' ) . '">' . esc_html__( 'Activate API', 'wp-templator' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Enqueues the needed CSS/JS for Backend.
		 *
		 * @param  string $hook Current hook.
		 *
		 * @since 0.1.0
		 */
		static public function admin_scripts( $hook = '' ) {

			if ( 'plugins.php' == $hook ) {
				wp_enqueue_style( 'templator-license', TEMPLATOR_URI . 'assets/css/license-popup.css', null, TEMPLATOR_VER, 'all' );
				wp_enqueue_script( 'templator-license', TEMPLATOR_URI . 'assets/js/license-popup.js', array( 'wp-util', 'jquery' ), TEMPLATOR_VER, true );
			}
		}

		/**
		 * Deactivate license.
		 *
		 * @since 0.1.0
		 *
		 * @return void.
		 */
		static public function deactivate_license() {
			// Stored Settings.
			$stored_data = Templator_Admin::get_instance()->get_page_settings();

			// New settings.
			$new_data = array(
				'license_key' => '',
				'site_url'    => '',
			);

			// Merge settings.
			$data = wp_parse_args( $new_data, $stored_data );

			// Update settings.
			update_option( 'templator-settings', $data );

			wp_send_json_success();
		}

		/**
		 * Save All admin settings here
		 */
		static public function activate_license() {

			$license_key = ( isset( $_REQUEST['license_key'] ) ) ? sanitize_text_field( $_REQUEST['license_key'] ) : '';

			$license_status = Templator_Remote_API::get_instance()->activate_license( $license_key );

			if ( $license_status['success'] ) {

				// Stored Settings.
				$stored_data = Templator_Admin::get_instance()->get_page_settings();
				$site_url    = isset( $license_status['data']->siteurl ) ? $license_status['data']->siteurl : '';

				if ( ! empty( $site_url ) ) {
					// New settings.
					$new_data = array(
						'license_key' => $license_key,
						'site_url'    => $site_url,
					);

					// Merge settings.
					$data = wp_parse_args( $new_data, $stored_data );

					// Update settings.
					update_option( 'templator-settings', $data );

					wp_send_json_success( $license_status );
				}
			}

			wp_send_json_error( $license_status );
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_License::get_instance();

endif;
