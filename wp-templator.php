<?php
/**
 * Plugin Name: Templator
 * Plugin URI: https://wptemplator.com/
 * Description: Save your templates in the cloud and access them on any other site.
 * Version: 1.0.3.2
 * Author: Brainstorm Force
 * Author URI: http://www.brainstormforce.com
 * Text Domain: wp-templator
 *
 * @package Templator
 */

/**
 * Set constants.
 */
define( 'TEMPLATOR_API_NAMESPACE', 'templator/v1' );
define( 'TEMPLATOR_STORE_HOST', 'wptemplator.com' );
define( 'TEMPLATOR_STORE_URL', apply_filters( 'templator_store_url', 'https://' . TEMPLATOR_STORE_HOST . '/' ) );
define( 'TEMPLATOR_VER', '1.0.3.2' );
define( 'TEMPLATOR_FILE', __FILE__ );
define( 'TEMPLATOR_BASE', plugin_basename( TEMPLATOR_FILE ) );
define( 'TEMPLATOR_DIR', plugin_dir_path( TEMPLATOR_FILE ) );
define( 'TEMPLATOR_URI', plugins_url( '/', TEMPLATOR_FILE ) );

require_once TEMPLATOR_DIR . 'classes/class-templator.php';
