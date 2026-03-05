<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Whitelabel {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_filter( 'all_plugins', [ $this, 'maybe_hide_plugin_from_list' ] );
		add_action( 'admin_bar_menu', [ $this, 'maybe_cleanup_admin_bar' ], 999 );
	}

	private function enabled() {
		return (bool) $this->settings->get( 'plugin_whitelabel_enabled', 1 );
	}

	public function maybe_hide_plugin_from_list( $plugins ) {
		if ( ! $this->enabled() || ! $this->settings->get( 'plugin_hide_from_plugins_list', 0 ) ) {
			return $plugins;
		}
		if ( isset( $plugins[ BWS_PLUGIN_BASENAME ] ) ) {
			unset( $plugins[ BWS_PLUGIN_BASENAME ] );
		}
		return $plugins;
	}

	public function maybe_cleanup_admin_bar( $wp_admin_bar ) {
		if ( ! $this->enabled() ) {
			return;
		}
		if ( $this->settings->get( 'plugin_hide_support_menu_from_adminbar', 0 ) ) {
			$wp_admin_bar->remove_node( 'bws-support' );
		}
	}
}
