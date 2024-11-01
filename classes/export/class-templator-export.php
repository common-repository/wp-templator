<?php
/**
 * Templator Admin
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_Export' ) ) :

	/**
	 * Templator Import
	 *
	 * @since 0.1.0
	 */
	class Templator_Export {

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
			add_filter( 'post_row_actions', array( $this, 'add_export_link' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'add_export_link' ), 10, 2 );
			add_filter( 'admin_footer', array( $this, 'export_popup' ) );
			add_action( 'wp_ajax_templator_export', array( $this, 'export' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}

		/**
		 * Export popup.
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		function export_popup() {
			?>
			<div id="templator-export-popup-overlay" style="display:none;"></div>
			<div id="templator-export-popup" style="display:none;">
				<div class="inner">
					<div class="heading">
						<span><?php _e( 'Export Template', 'wp-templator' ); ?></span>
						<span class="templator-close-popup-button tb-close-icon"></span>
					</div>
					<div class="contents">
					</div>
				</div>
			</div>

			<script type="text/template" id="tmpl-templator-table-export">
				<table class="widefat templator-table-export">
					<tr class="templator-row">
						<td class="templator-heading"><?php _e( 'Enter Categories', 'wp-templator' ); ?></td>
						<td class="templator-content">
							<input type="text" class="categories" placeholder="<?php _e( 'About, Contact', 'wp-templator' ); ?>">
						</td>
					</tr>
					<tr class="templator-row">
						<td class="templator-heading"><?php _e( 'Screenshot', 'wp-templator' ); ?></td>
						<td class="templator-content templator-screenshot">
							<div class="templator-screenshot-inner">
								<# if ( data.screenshot_url ) { #>
									<p class="hide-if-no-js">
										<img src="{{{ data.screenshot_url }}}" class="templator-set-media" />
									</p>
									<p class="hide-if-no-js"><a href="#" class="templator-remove-media"><?php _e( 'Remove Screenshot Image', 'wp-templator' ); ?></a></p>
								<# } else { #>
									<p class="hide-if-no-js">
										<a href="#" class="templator-set-media"><?php esc_html_e( 'Set Screenshot Image', 'wp-templator' ); ?></a>
									</p>
								<# } #>
							</div>
							<input type="hidden" name="templator-screenshot-id" class="image-id" value="{{data.screenshot_id }}">
							<input type="hidden" name="templator-screenshot-url" class="image-url" value="{{data.screenshot_url }}">
						</td>
					</tr>
					<tr class="templator-row">
						<td></td>
						<td class="submit-button-td">
							<span class="button button-primary templator-export"><i class="templator-processing dashicons dashicons-update"></i><span class="text"><?php _e( 'Export', 'wp-templator' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>

			<script type="text/template" id="tmpl-templator-license-not-active">
				<div class="activate-license">
					<p class="description">
						<?php
						/* translators: %1$d activate api link, %2$s get api link. */
						printf( __( 'API key is not active on your website. Please <a href="%1$s">activate API key</a> to export template on cloud server.<br/>If you don\'t have API key, you can get it from <a target="_blank" href="%2$s">here</a>.', 'wp-templator' ), admin_url( 'plugins.php?templator-license-popup' ), 'https://wptemplator.com/' );
						?>
					</p>
				</div>
			</script>
			<script type="text/template" id="tmpl-templator-export-success">
				<div class="templator-export-status notice notice-warning templator-export-success">
					<p><?php _e( 'This template was already exported. Exporting again?', 'wp-templator' ); ?></p>
				</div>
			</script>
			<script type="text/template" id="tmpl-templator-export-in-process">
				<div class="templator-export-status notice notice-info templator-export-in-process">
					<p><?php _e( 'Template export is in process. Please refresh your page in some time to know the export status.', 'wp-templator' ); ?></p>
				</div>
			</script>
			<?php
		}

		/**
		 * Add Export Link to Posts
		 *
		 * @since 0.1.0
		 *
		 * @hook page_row_actions
		 * @param array  $actions Post action links.
		 * @param object $post   Post object.
		 */
		function add_export_link( $actions, $post ) {

			if ( ! get_post_meta( $post->ID, '_elementor_edit_mode', true ) ) {
				return $actions;
			}

			if ( ! Templator_Misc::get_instance()->is_supported_post( $post->post_type ) ) {
				return $actions;
			}

			$screenshot_id  = get_post_meta( $post->ID, 'templator-screenshot-id', true );
			$screenshot_url = Templator_Admin::get_instance()->get_portfolio_image_url( $screenshot_id );

			$status      = get_post_meta( $post->ID, 'templator-export-status', true );
			$license_key = Templator_Admin::get_instance()->get_page_setting( 'license_key' );
			$title       = __( 'Export to Templator', 'wp-templator' );

			if ( 'success' === $status ) {
				$title = __( 'Exported', 'wp-templator' );
			} elseif ( 'in-process' === $status ) {
				$title = __( 'Export In Process', 'wp-templator' );
			}

			$markup  = '<a href="#" title="' . __( 'Export Template', 'wp-templator' ) . '" data-license-key="' . esc_attr( $license_key ) . '" data-export-status="' . esc_attr( $status ) . '" class="templator-open-popup-button templator-export-status-' . esc_attr( $status ) . '" data-post-id="' . esc_attr( $post->ID ) . '" data-screenshot-id="' . esc_attr( $screenshot_id ) . '" data-screenshot-url="' . esc_attr( $screenshot_url ) . '" style="box-shadow: none;">';
			$markup .= '<span class="spinner" style="display: none; float: none;margin: 0 5px 0 0;"></span>';
			$markup .= '<span class="text">';
			$markup .= $title;
			$markup .= '</span>';
			$markup .= '</a>';

			$actions['export_to_templator'] = $markup;

			return $actions;
		}

		/**
		 * Enqueue scripts
		 *
		 * @since 0.1.0
		 *
		 * @hook admin_enqueue_scripts
		 *
		 * @param  string $hook Current page hook name.
		 */
		function scripts( $hook = '' ) {
			wp_enqueue_script( 'templator-export', TEMPLATOR_URI . 'assets/js/export.js', array( 'jquery', 'wp-util' ), TEMPLATOR_VER, true );
			wp_enqueue_style( 'templator-export', TEMPLATOR_URI . 'assets/css/export.css', null, TEMPLATOR_VER, 'all' );
		}

		/**
		 * Export
		 *
		 * @since 0.1.0
		 *
		 * @hook wp_ajax_templator_export
		 * @return void
		 */
		function export() {
			$site_url  = site_url();
			$site_root = parse_url( $site_url );
			$site_host = ( isset( $site_root['host'] ) ) ? $site_root['host'] : '';
			$args      = ( isset( $_POST['args'] ) ) ? $_POST['args'] : array();

			$post_id    = ( ! empty( $args['post_id'] ) ) ? absint( $args['post_id'] ) : '';
			$categories = ( ! empty( $args['categories'] ) ) ? $args['categories'] : array();

			$screenshot_id  = ( ! empty( $args['screenshot_id'] ) ) ? absint( $args['screenshot_id'] ) : '';
			$screenshot_url = ( ! empty( $args['screenshot_url'] ) ) ? $args['screenshot_url'] : '';

			if ( $screenshot_id ) {
				update_post_meta( $post_id, 'templator-screenshot-id', $screenshot_id );
			}

			$_thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
			$thumbnail_url = Templator_Misc::get_instance()->get_image_url_by_id( $_thumbnail_id, 'full' );

			if ( empty( $post_id ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'data'    => $post_id,
						/* translators: %1$s Post id. */
						'message' => sprintf( __( 'Invalid template ID %1$s.', 'wp-templator' ), $post_id ),
					)
				);
			}

			$data = Templator_Admin::get_instance()->get_page_settings();

			$elementor = get_post_meta( $post_id, '_elementor_data', true );

			if ( empty( $elementor ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'data'    => $elementor,
						'message' => __( 'Not a elementor template. Now we support only elementor page builder.', 'wp-templator' ),
					)
				);
			}

			// @todo Change this static value when we have a support
			// for another page builder.
			$page_builder = 'elementor';

			$page_builder_meta = array();

			if ( 'elementor' === $page_builder ) {

				$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
				if ( is_array( $elementor_data ) ) {
					$elementor_data = json_encode( $elementor_data, true );
				}

				$page_builder_meta['_elementor_data']      = $elementor_data;
				$page_builder_meta['_elementor_css']       = get_post_meta( $post_id, '_elementor_css', true );
				$page_builder_meta['_elementor_edit_mode'] = get_post_meta( $post_id, '_elementor_edit_mode', true );
				$page_builder_meta['_elementor_version']   = get_post_meta( $post_id, '_elementor_version', true );
			}

			$body = array(
				'post_id'           => $post_id,
				'post_permalink'    => get_permalink( $post_id ),
				'title'             => get_the_title( $post_id ),
				'site_url'          => $site_url,
				'thumbnail_url'     => $thumbnail_url,
				'screenshot_url'    => $screenshot_url,
				'site_root'         => $site_root,
				'site_host'         => $site_host,
				'categories'        => $categories,
				'license_key'       => Templator_Admin::get_instance()->get_page_setting( 'license_key' ),
				'page_builder'      => $page_builder,
				'page_builder_meta' => $page_builder_meta,
			);

			$response = Templator_API::get_instance()->site_request(
				'import', array(
					'body'      => $body,
					'timeout'   => '30',
					'sslverify' => false,
				)
			);

			// Send Import Template.
			if ( $response['success'] ) {

				update_post_meta( $post_id, 'templator-export-status', 'in-process' );

				wp_send_json_success( $response['data'] );
			} else {
				wp_send_json_error( $response );
			}

			exit();
		}


	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_Export::get_instance();

endif;
