<?php
/**
 * Templator Admin
 *
 * @package Templator
 * @since 0.1.0
 */

if ( ! class_exists( 'Templator_Import' ) ) :

	/**
	 * Templator Import
	 *
	 * @since 0.1.0
	 */
	class Templator_Import {

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
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'media_buttons', array( $this, 'import_template_button' ) );
			add_action( 'wp_ajax_templator_import', array( $this, 'import' ) );
			add_action( 'wp_ajax_templator_load_categories', array( $this, 'load_categories' ) );
			add_action( 'wp_ajax_templator_load_templates', array( $this, 'load_templates' ) );
			add_action( 'wp_ajax_templator_add_to_library', array( $this, 'add_to_library' ) );
			add_action( 'admin_footer', array( $this, 'js_templates' ) );
			add_action( 'admin_footer', array( $this, 'html_markup' ) );
		}

		/**
		 * JS Templates
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		function js_templates() {

			// Loading Templates.
			?>
			<script type="text/template" id="tmpl-templator-loading-templates">
				<div class="template-message-block templator-loading-templates">
					<h2>
						<span class="spinner is-active"></span>
						<?php _e( 'Loading Templates', 'wp-templator' ); ?>
					</h2>
					<p class="description"><?php _e( 'Getting templates from the cloud. Please wait for the moment.', 'wp-templator' ); ?></p>
				</div>
			</script>

			<?php
			// Search Templates.
			?>
			<script type="text/template" id="tmpl-templator-searching-templates">
				<div class="template-message-block templator-searching-templates">
					<h2>
						<span class="spinner is-active"></span>
						<?php _e( 'Searching Template..', 'wp-templator' ); ?>
					</h2>
					<p class="description"><?php _e( 'Getting templates from the cloud. Please wait for the moment.', 'wp-templator' ); ?></p>
				</div>
			</script>

			<?php
			// Templator Importing Template.
			?>
			<script type="text/template" id="tmpl-templator-importing">
				<div class="template-message-block templator-importing">
					<h2><span class="spinner is-active"></span> <?php _e( 'Importing..', 'wp-templator' ); ?></h2>
				</div>
			</script>

			<?php
			// Templator Imported.
			?>
			<script type="text/template" id="tmpl-templator-imported">
				<div class="template-message-block templator-imported">
					<h2><span class="dashicons dashicons-yes"></span> <?php _e( 'Imported', 'wp-templator' ); ?></h2>
					<p class="description"><?php _e( 'Thanks for patience', 'wp-templator' ); ?> <span class="dashicons dashicons-smiley"></span><br/><br/><?php _e( 'Closing the window.', 'wp-templator' ); ?> </p></div>
			</script>

			<?php
			// No templates.
			?>
			<script type="text/template" id="tmpl-templator-no-templates">
				<div class="templator-no-templates">
					<div class="template-message-block">
						<h2><?php _e( 'You don\'t have templates in the cloud yet.', 'wp-templator' ); ?></h2>
						<p class="description"><?php _e( 'Please export any pages as template and get started..', 'wp-templator' ); ?></p>
					</div>
				</div>
			</script>

			<?php
			// Error handling.
			?>
			<script type="text/template" id="tmpl-templator-error">
				<div class="notice notice-error"><p>{{ data }}</p></div>
			</script>

			<?php
			// Redirect to Elementor.
			?>
			<script type="text/template" id="tmpl-templator-redirect-to-elementor">
				<div class="template-message-block templator-redirect-to-elementor">
					<h2><span class="dashicons dashicons-yes"></span> <?php _e( 'Imported', 'wp-templator' ); ?></h2>
					<p class="description"><?php _e( 'Thanks for patience', 'wp-templator' ); ?> <span class="dashicons dashicons-smiley"></span><br/><br/><?php _e( 'Redirecting to the Elementor edit window.', 'wp-templator' ); ?> </p></div>
			</script>

			<?php
			// Templates data.
			?>
			<script type="text/template" id="tmpl-templator-list">
				<# if ( data.templates_count ) { #>
					<# for ( key in data.templates ) { #>
						<div class="theme template">
							<div class="theme-screenshot">
								<img src="{{ data.templates[ key ].featured_image_url }}" />
							</div>
							<div class="theme-id-container">
								<h3 class="theme-name">{{ data.templates[ key ].title }}</h3>
								<div class="theme-actions">
									<?php if ( Templator_Admin::get_instance()->get_page_setting( 'license_key' ) ) { ?>
										<a href="#" class="button button-primary templator-import" data-template-id="{{ data.templates[ key ].id }}"><?php _e( 'Import', 'wp-templator' ); ?></a>
									<?php } else { ?>
										<a href="<?php echo esc_url( admin_url( 'plugins.php?templator-license-popup' ) ); ?>" class="button button-primary"><?php _e( 'Activate API Key', 'wp-templator' ); ?></a>
									<?php } ?>
								</div>
							</div>
						</div>
					<# } #>
				<# } #>
			</script>
			<?php
			// Templates data.
			?>
			<script type="text/template" id="tmpl-templator-list-popup">
				<div id="templator-list" class="templator-list-popup">
					<# if ( data.categories_count ) { #>
						<div class="wp-filter hide-if-no-js">
							<div class="filter-count">
								<span class="count theme-count">0</span>
							</div>
							<ul class="filter-links">
								<li><a href="#" data-id="all" class="current" ><?php echo esc_attr_e( 'All', 'wp-templator' ); ?></a></li>
								<# for ( key in data.categories ) { #>
									<li><a href="#" data-id="{{ data.categories[ key ].id }}" >{{ data.categories[ key ].name }}</a></li>
								<# } #>
							</ul>
							<div class="search-form">
								<label class="screen-reader-text" for="templator-search"><?php _e( 'Search Sites', 'wp-templator' ); ?> </label>
								<input placeholder="<?php _e( 'Search Template...', 'wp-templator' ); ?>" type="search" aria-describedby="live-search-desc" id="templator-search" class="wp-filter-search">
							</div>
						</div>
					<# } #>
					<div class="theme-browser content-filterable rendered" style="margin-top: 2em;">
						<div class="themes wp-clearfix">
						</div>
					</div>
				</div>				
			</script>

			<?php
			// Add to library button.
			?>
			<script type="text/template" id="tmpl-templator-add-to-library">
				<a class="templator-add-to-library page-title-action templator-load-templates-library"><i class="dashicons dashicons-cloud"></i><?php esc_attr_e( 'Import from Cloud', 'wp-templator' ); ?></a>
			</script>
			<?php
		}

		/**
		 * Enqueue scripts
		 *
		 * @since 0.1.0
		 *
		 * @hook admin_enqueue_scripts
		 * @param  string $hook Current page hook.
		 */
		function scripts( $hook = '' ) {
			if ( ! Templator_Misc::get_instance()->is_supported_post( get_current_screen()->post_type ) ) {
				return;
			}

			wp_enqueue_style( 'templator-import', TEMPLATOR_URI . 'assets/css/import.css', null, TEMPLATOR_VER, 'all' );
			wp_enqueue_script( 'templator-import', TEMPLATOR_URI . 'assets/js/import.js', array( 'jquery', 'wp-util' ), TEMPLATOR_VER, true );
		}

		/**
		 * Choose Template for Import.
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		function html_markup() {
			?>
			<div id="templator-templates" class="templator-templates-popup" style="display:none;">
				<div class="heading" style="text-align: right;"><span class="close"><span class="dashicons dashicons-no"></span></span></div>
				<div class="inner"></div>
			</div>
			<?php
		}

		/**
		 * Import Template Button
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		function import_template_button() {
			if ( ! Templator_Misc::get_instance()->is_supported_post( get_current_screen()->post_type ) ) {
				return;
			}

			if ( Templator_Admin::get_instance()->get_page_setting( 'license_key' ) ) {
				$elementor_link = add_query_arg(
					array(
						'post'   => get_the_id(),
						'action' => 'elementor',
					), admin_url( 'post.php' )
				);
				?>
				<span data-elementor-link="<?php echo esc_attr( $elementor_link ); ?>" class="templator-load-templates button button-primary"><i class="dashicons dashicons-cloud"></i> <?php _e( 'Import from Cloud', 'wp-templator' ); ?></span>
			<?php } else { ?>
				<a href="<?php echo admin_url( 'plugins.php?templator-license-popup' ); ?>" class="button button-primary"><i class="dashicons dashicons-cloud"></i> <?php _e( 'Activate Key', 'wp-templator' ); ?></a>
				<?php
}
		}

		/**
		 * Load all templates
		 *
		 * @since 0.1.0
		 *
		 * @hook templator_load_categories Hook name.
		 * @return void
		 */
		function load_categories() {
			$args             = ( isset( $_POST['args'] ) ) ? $_POST['args'] : array();
			$categories       = Templator_API::get_instance()->get_categories();
			$categories_count = isset( $categories['categories_count'] ) ? $categories['categories_count'] : 0;

			wp_send_json_success( $categories );
		}

		/**
		 * Load Template
		 *
		 * @since 0.1.0
		 *
		 * @hook templator_load_templates
		 * @return void
		 */
		function load_templates() {
			$args      = ( isset( $_POST['args'] ) ) ? $_POST['args'] : array();
			$templates = Templator_API::get_instance()->get_templates( $args );

			if ( $templates['templates_count'] ) {
				wp_send_json_success( $templates );
			} else {
				wp_send_json_error( $templates );
			}
			wp_die();
		}

		/**
		 * Import.
		 *
		 * @since 0.1.0
		 *
		 * @hook wp_ajax_templator_add_to_library
		 * @return void
		 */
		function add_to_library() {
			$template_id   = ( isset( $_POST['template_id'] ) ) ? absint( $_POST['template_id'] ) : '';
			$template_type = ( isset( $_POST['template_type'] ) ) ? sanitize_text_field( $_POST['template_type'] ) : '';

			if ( empty( $template_id ) || empty( $template_type ) ) {
				wp_send_json_error(
					/* translators: %1$s is template id and %2$s is template type. */
					sprintf( __( 'Error! Template ID %1$s and type %2$s.', 'wp-templator' ), $template_id, $template_type )
				);
			}

			$response = Templator_API::get_instance()->get_template( $template_id );

			$post_type = '';
			switch ( $template_type ) {
				case 'elementor':
					if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
						/* translators: %1$s is plugin installation link. */
						wp_send_json_error( sprintf( __( 'Elementor plugin required to import this template. <a href="%1$s">Get it</a>.', 'wp-templator' ), admin_url( '/plugin-install.php?s=elementor&tab=search&type=term' ) ) );
					}

						$post_type = 'elementor_library';
					break;

				case 'fl_builder':
					if (
							! is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) &&
							! is_plugin_active( 'bb-plugin/fl-builder.php' )
						) {
						/* translators: %1$s is plugin installation link. */
						wp_send_json_error( sprintf( __( 'Beaver Builder plugin required to import this template. <a href="%1$s">Get it</a>.', 'wp-templator' ), admin_url( '/plugin-install.php?s=beaver+builder&tab=search&type=term' ) ) );
					}

						$post_type = 'fl-builder-template';
					break;
			}

			if ( empty( $post_type ) ) {
				wp_send_json_error(
					new WP_Error(
						/* translators: %1$s post type. */
						'unknown_post_type', sprintf( __( 'Post type of template %1$s has no support.', 'wp-templator' ), $post_type )
					)
				);
			}

			if ( $response['title'] ) {

				$postarr = array(
					'post_type'   => $post_type,
					'post_title'  => $response['title'],
					'post_status' => 'publish',
				);

				$post_id = wp_insert_post( $postarr );
				if ( is_wp_error( $post_id ) ) {
					wp_send_json_error(
						new WP_Error(
							/* translators: %1$s dynamic error message. */
							'failed_insert_post', sprintf( __( 'Not able to create post due to "%1$s".', 'wp-templator' ), $post_id->get_error_message() )
						)
					);
				}

				// Elementor.
				if ( 'elementor' === $template_type ) {
					self::import_template_elementor( $post_id, $response );
				}

				// Beaver Builder.
				if ( 'fl_builder' === $template_type ) {
					self::import_template_beaver_builder( $post_id, $response );
				}

				wp_send_json_success(
					get_post_permalink( $post_id )
				);
			}

			wp_send_json_error();

			exit();
		}

		/**
		 * Import.
		 *
		 * @since 0.1.0
		 *
		 * @hook wp_ajax_templator_import
		 * @return void
		 */
		function import() {

			$template_id = ( isset( $_POST['template_id'] ) ) ? absint( $_POST['template_id'] ) : '';
			$post_id     = ( isset( $_POST['post_id'] ) ) ? absint( $_POST['post_id'] ) : '';

			if ( empty( $template_id ) || empty( $post_id ) ) {
				wp_send_json_error();
			}

			$response = Templator_API::get_instance()->get_template( $template_id );

			if ( ! empty( $response['template_data'] ) ) {

				// Elementor.
				self::import_template_elementor( $post_id, $response );

				// Beaver Builder.
				self::import_template_beaver_builder( $post_id, $response );

				wp_send_json_success( $response );
			}

			wp_send_json_error( $response );

			exit();
		}

		/**
		 * Import Template for Elementor
		 *
		 * @since 0.1.0
		 *
		 * @param  integer $post_id  Post ID.
		 * @param  array   $response  Post meta.
		 * @return void
		 */
		public static function import_template_elementor( $post_id, $response ) {
			if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
				return;
			}

			$meta_keys = array(
				'_elementor_edit_mode',
				'_elementor_version',
				'_elementor_css',
			);

			$data = (array) $response['template_data'];

			foreach ( $meta_keys as $key => $meta_key ) {
				if ( isset( $data[ $meta_key ] ) ) {
					update_post_meta( $post_id, $meta_key, $data[ $meta_key ] );
				}
			}

			if ( isset( $data['_elementor_data'] ) ) {

				if ( is_serialized( $data['_elementor_data'][0], true ) ) {
					$raw_data = maybe_unserialize( stripcslashes( $data['_elementor_data'][0] ) );
				} else {
					$raw_data = json_decode( $data['_elementor_data'][0], true );
				}

				$stored_data = Templator_Import_Elementor::get_instance()->import_single_post( $post_id, $raw_data );

				self::add_images_import_status( $post_id, $stored_data );

			}
		}

		/**
		 * Import Template for Beaver Builder
		 *
		 * @since 0.1.0
		 *
		 * @param  integer $post_id  Post ID.
		 * @param  array   $response  Post meta.
		 * @param  string  $template_type  Post type.
		 * @return void
		 */
		public static function import_template_beaver_builder( $post_id, $response, $template_type = 'layout' ) {
			if (
				! is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) &&
				! is_plugin_active( 'bb-plugin/fl-builder.php' )
			) {
				return;
			}

			$meta_keys = array(
				'_fl_builder_draft_settings',
				'_fl_builder_enabled',
				'_fl_builder_data_settings',
			);

			// It should be 'layout', 'row' or 'module'.
			wp_set_post_terms( $post_id, $template_type, 'fl-builder-template-type' );

			foreach ( $meta_keys as $key => $meta_key ) {
				if ( isset( $response['template_data'][ $meta_key ] ) ) {
					update_post_meta( $post_id, $meta_key, $response['template_data'][ $meta_key ] );
				}
			}

			if ( isset( $response['template_data']['_fl_builder_data'] ) ) {
				$data = $response['template_data']['_fl_builder_data'][0];
				$data = maybe_unserialize( $data );

				$stored_data = Templator_Import_Beaver_Builder::get_instance()->import_single_post( $post_id, $data );
				self::add_images_import_status( $post_id, $stored_data );
			}
		}

		/**
		 * Add all image import status.
		 *
		 * @since 0.1.1
		 *
		 * @param  integer $post_id     Post ID.
		 * @param  array   $stored_data Stored data.
		 * @return void
		 */
		public static function add_images_import_status( $post_id = 0, $stored_data = array() ) {
			$import_all_images = 'no';

			// Set flag to know all images are imported or not.
			if ( $stored_data ) {
				$stored_data = json_encode( $stored_data, true );
				if ( strpos( $stored_data, TEMPLATOR_STORE_HOST ) === false ) {
					$import_all_images = 'yes';
				}
			}

			update_post_meta( $post_id, 'template_imported_all_images', $import_all_images );
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Templator_Import::get_instance();

endif;
