<?php
/**
 * Templator
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_Admin' ) ) :

	/**
	 * Templator_Admin
	 *
	 * @since 0.1.0
	 */
	class Templator_Admin {

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
			add_action( 'add_meta_boxes', array( $this, 'meta_box_settings' ) );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_scripts' );
			add_action( 'admin_footer', array( $this, 'meta_box_templates' ) );
			add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 3 );
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		}

		/**
		 * Loads textdomain for the plugin.
		 *
		 * @since 1.0.3.1
		 */
		function load_textdomain() {
			load_plugin_textdomain( 'wp-templator' );
		}

		/**
		 * Meta box templates.
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		function meta_box_templates() {
			?>
			<script type="text/template" id="tmpl-templator-set-media">
				<p class="hide-if-no-js">
					<a href="#" class="templator-set-media"><?php esc_html_e( 'Set Screenshot Image', 'wp-templator' ); ?></a>
				</p>
			</script>
			<script type="text/template" id="tmpl-templator-remove-media">
				<# if( data ) { #>
					<p class="hide-if-no-js">
						<img src="{{data}}" class="templator-set-media" />
					</p>
					<p class="hide-if-no-js"><a href="#" class="templator-remove-media"><?php _e( 'Remove Screenshot Image', 'wp-templator' ); ?></a></p>
				<# } #>
			</script>
			<?php
		}

		/**
		 * Admin Scripts
		 *
		 * @since 0.1.0
		 *
		 * @param  string $hook Current page hook.
		 * @return void
		 */
		static public function admin_scripts( $hook = '' ) {
			if (
				'edit-page' === get_current_screen()->id ||
				'edit-post' === get_current_screen()->id ||
				'edit-elementor_library' === get_current_screen()->id ||
				'elementor_library' === get_current_screen()->id ||
				'page' === get_current_screen()->id ||
				'post' === get_current_screen()->id
			) {
				wp_enqueue_media();
				wp_enqueue_script( 'templator-post', TEMPLATOR_URI . 'assets/js/post.js', array( 'wp-util', 'jquery' ), TEMPLATOR_VER, true );
				wp_enqueue_style( 'templator-post', TEMPLATOR_URI . 'assets/css/post.css', null, TEMPLATOR_VER, 'all' );
			}

			if ( 'toplevel_page_templator' == $hook ) {
				wp_enqueue_style( 'templator-settings-page', TEMPLATOR_URI . 'assets/css/settings-page.css', null, TEMPLATOR_VER, 'all' );
			}
		}

		/**
		 * Register meta box(es).
		 *
		 * @since 0.1.0
		 */
		function meta_box_settings() {

			if ( ! Templator_Misc::get_instance()->is_supported_post( get_post_type() ) ) {
				return;
			}

			add_meta_box( 'templator-page', __( 'Template Screenshot', 'wp-templator' ), array( $this, 'meta_boxe_callback' ), null, 'side' );
		}

		/**
		 * Save meta boxes
		 *
		 * @since 0.1.0
		 *
		 * @param  int    $post_id     Post ID.
		 * @param  object $post     (WP_Post) Post .
		 * @param  bool   $update     Whether this is an existing post being updated or not.
		 * @return void
		 */
		function save_meta_boxes( $post_id = 0, $post = '', $update = '' ) {
			if ( isset( $_POST['templator-screenshot-id'] ) ) {
				update_post_meta( $post_id, 'templator-screenshot-id', absint( $_POST['templator-screenshot-id'] ) );
			}
		}

		/**
		 * Meta box display callback.
		 *
		 * @since 0.1.0
		 *
		 * @param WP_Post $post Current post object.
		 * @return void
		 */
		function meta_boxe_callback( $post ) {

			$screenshot_id  = get_post_meta( $post->ID, 'templator-screenshot-id', true );
			$screenshot_url = self::get_portfolio_image_url( $screenshot_id );
			?>
			<div class="templator-screenshot">
				<?php self::image_markup( $screenshot_url ); ?>
				<input type="hidden" name="templator-screenshot-id" class="image-id" value="<?php echo esc_attr( $screenshot_id ); ?>">
				<input type="hidden" name="templator-screenshot-url" class="image-url" value="<?php echo esc_attr( $screenshot_url ); ?>">
			</div>
			<?php
		}

		/**
		 * Image field markup
		 *
		 * @since 0.1.0
		 *
		 * @param  string $image_url Image URL.
		 * @return void
		 */
		public static function image_markup( $image_url = '' ) {
			?>
			<div class="templator-screenshot-inner">
				<?php if ( ! empty( $image_url ) ) : ?>
					<p class="hide-if-no-js">
						<img src="<?php echo esc_attr( $image_url ); ?>" class="templator-set-media" />
					</p>
					<p class="hide-if-no-js"><a href="#" class="templator-remove-media"><?php _e( 'Remove Screenshot Image', 'wp-templator' ); ?></a></p>
				<?php else : ?>
					<p class="hide-if-no-js">
						<a href="#" class="templator-set-media"><?php esc_html_e( 'Set Screenshot Image', 'wp-templator' ); ?></a>
					</p>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Get portfolio image URL.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $image_id Attachment image ID.
		 * @return string           Image URL.
		 */
		public static function get_portfolio_image_url( $image_id = '' ) {

			if ( empty( $image_id ) ) {
				return;
			}

			$image_attributes = wp_get_attachment_image_src( $image_id, 'medium' );
			if ( $image_attributes ) {
				return $image_attributes[0];

			}

			$image_attributes = wp_get_attachment_image_src( $image_id, 'full' );
			return $image_attributes[0];
		}

		/**
		 * Get page settings.
		 *
		 * @since 0.1.0
		 *
		 * @return array Page Settings.
		 */
		public static function get_page_settings() {
			$settings_defaults = array(
				'license_key' => '',
			);

			// Stored Settings.
			$settings = get_option( 'templator-settings', $settings_defaults );
			$settings = wp_parse_args( $settings, $settings_defaults );

			return $settings;
		}

		/**
		 * Get page settings.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $key     Meta key.
		 * @param  string $default Default value.
		 * @return array Page Settings.
		 */
		public static function get_page_setting( $key = '', $default = '' ) {
			$settings = self::get_page_settings();
			if ( isset( $settings[ $key ] ) ) {
				return $settings[ $key ];
			}

			return $default;
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_Admin::get_instance();

endif;
