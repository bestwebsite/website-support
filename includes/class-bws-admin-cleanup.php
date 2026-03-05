<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Admin_Cleanup {
	/** @var BWS_Settings */
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_head', [ $this, 'admin_head_cleanup' ], 1 );

		if ( $this->settings->get( 'updates_hide_auto_update_column', 1 ) ) {
			add_filter( 'plugins_auto_update_enabled', '__return_false' );
			add_filter( 'themes_auto_update_enabled', '__return_false' );
		}
	}

	public function admin_head_cleanup() {
		if ( ! is_admin() ) {
			return;
		}

		if ( $this->settings->get( 'updates_hide_nag', 1 ) ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
		}

		$selectors = [];

		if ( $this->settings->get( 'updates_hide_plugin_rows', 1 ) ) {
			$selectors[] = '.plugins .plugin-update-tr';
			$selectors[] = '.plugins .update-message';
		}

		if ( $this->settings->get( 'updates_hide_badges', 1 ) ) {
			$selectors[] = '#wp-admin-bar-updates';
			$selectors[] = '.wp-menu-name .update-plugins';
			$selectors[] = '.update-plugins';
			$selectors[] = '.plugin-count';
			$selectors[] = '.update-count';
		}

		if ( $this->settings->get( 'updates_hide_auto_update_column', 1 ) ) {
			$selectors[] = '.plugins .column-auto-updates';
			$selectors[] = 'th#auto-updates';
			$selectors[] = 'td.column-auto-updates';
		}

		if ( $this->settings->get( 'updates_hide_plugin_update_tab', 1 ) ) {
			$selectors[] = '.subsubsub a[href*="plugin_status=upgrade"]';
		}

		$selectors = array_unique( array_filter( $selectors ) );

		if ( ! empty( $selectors ) ) {
			echo '<style>' . implode( ',', array_map( 'trim', $selectors ) ) . '{display:none!important;}</style>';
		}
	}
}
