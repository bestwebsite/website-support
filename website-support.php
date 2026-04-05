<?php
/**
 * Plugin Name: Best Website Support
 * Plugin URI: https://bestwebsite.com
 * Description: Best Website client admin cleanup, branding, login customization, performance hardening, and built-in support tools for managed WordPress sites.
 * Author: Best Website
 * Author URI: https://bestwebsite.com
 * Text Domain: bw-support
 * Domain Path: /languages
 * Version: 0.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BWS_VERSION', '0.0.0' ); // Patched by release workflow from tag vX.Y.Z.
define( 'BWS_GITHUB_OWNER', 'bestwebsite' );
define( 'BWS_GITHUB_REPO', 'website-support' );

define( 'BWS_PLUGIN_FILE', __FILE__ );
define( 'BWS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BWS_TEXT_DOMAIN', 'bw-support' );

define( 'BWS_OPTION_KEY', 'bw_support_settings' );
define( 'BWS_SETTINGS_PAGE_SLUG', 'bw-settings' );
define( 'BWS_SUPPORT_PAGE_SLUG', 'bw-support' );

define( 'BWS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once BWS_PLUGIN_DIR . 'includes/class-bws-settings.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-github-updater.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-plugin.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-dashboard.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-admin-cleanup.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-branding.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-support.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-login-branding.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-menu-labels.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-whitelabel.php';
require_once BWS_PLUGIN_DIR . 'includes/class-bws-hardening.php';

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( BWS_TEXT_DOMAIN, false, dirname( BWS_PLUGIN_BASENAME ) . '/languages' );

	BWS_Plugin::instance();

	// Enable GitHub-based updates (public repo releases).
	new BWS_GitHub_Updater( BWS_GITHUB_OWNER, BWS_GITHUB_REPO, BWS_PLUGIN_FILE );
} );
