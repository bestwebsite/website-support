<?php
/**
 * Plugin Name: Best Website Support
 * Plugin URI: https://bestwebsite.com
 * Description: Best Website client admin cleanup, branding, and support tools for managed WordPress sites.
 * Version: 0.1.0
 * Author: Best Website
 * Author URI: https://bestwebsite.com
 * Text Domain: bw-support
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BWS_VERSION', '0.1.0' );
define( 'BWS_PLUGIN_FILE', __FILE__ );
define( 'BWS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BWS_TEXT_DOMAIN', 'bw-support' );
define( 'BWS_OPTION_KEY', 'bw_support_settings' );
define( 'BWS_SETTINGS_PAGE_SLUG', 'bw-settings' );
define( 'BWS_SUPPORT_PAGE_SLUG', 'bw-support' );

require_once BWS_PLUGIN_DIR . 'includes/class-bws-settings.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-dashboard.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-admin-cleanup.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-branding.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-support.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-plugin.php';

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( BWS_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	BWS_Plugin::instance();
} );
