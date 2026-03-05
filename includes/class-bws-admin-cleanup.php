<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Admin_Cleanup {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_action( 'admin_head', [ $this, 'admin_head_cleanup' ], 1 );
		add_action( 'admin_menu', [ $this, 'cleanup_admin_menus' ], 999 );
		add_action( 'admin_init', [ $this, 'restrict_admin_pages' ] );
		add_filter( 'all_plugins', [ $this, 'filter_plugins_list_actions' ] );
		add_filter( 'plugin_action_links', [ $this, 'filter_plugin_action_links' ], 10, 4 );
		add_filter( 'theme_action_links', [ $this, 'filter_theme_action_links' ], 10, 2 );

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

		if ( $this->settings->get( 'restrict_plugin_delete', 1 ) ) {
			$selectors[] = '.plugins .delete';
		}

		if ( ! empty( $selectors ) ) {
			echo '<style>' . esc_html( implode( ',', array_unique( $selectors ) ) ) . '{display:none!important;}</style>';
		}
	}

	public function cleanup_admin_menus() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $this->settings->get( 'menu_hide_tools', 1 ) ) {
			remove_menu_page( 'tools.php' );
		}
		if ( $this->settings->get( 'menu_hide_comments', 0 ) ) {
			remove_menu_page( 'edit-comments.php' );
		}
		if ( $this->settings->get( 'menu_hide_settings', 0 ) ) {
			remove_menu_page( 'options-general.php' );
		}
		if ( $this->settings->get( 'menu_hide_users', 0 ) ) {
			remove_menu_page( 'users.php' );
		}
		if ( $this->settings->get( 'menu_hide_plugins', 0 ) ) {
			remove_menu_page( 'plugins.php' );
		}
		if ( $this->settings->get( 'menu_hide_appearance', 0 ) ) {
			remove_menu_page( 'themes.php' );
		}

		$custom_top = preg_split( '/\r\n|\r|\n/', (string) $this->settings->get( 'menu_hide_custom_slugs', '' ) );
		$custom_top = array_filter( array_map( 'trim', (array) $custom_top ) );
		foreach ( $custom_top as $slug ) {
			remove_menu_page( $slug );
		}

		$custom_sub = preg_split( '/\r\n|\r|\n/', (string) $this->settings->get( 'submenu_hide_custom_slugs', '' ) );
		$custom_sub = array_filter( array_map( 'trim', (array) $custom_sub ) );
		foreach ( $custom_sub as $line ) {
			$parts = array_map( 'trim', explode( '|', $line ) );
			if ( count( $parts ) >= 2 && $parts[0] && $parts[1] ) {
				remove_submenu_page( $parts[0], $parts[1] );
			}
		}
	}

	public function restrict_admin_pages() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $pagenow;

		if ( $this->settings->get( 'restrict_plugin_editor', 1 ) && 'plugin-editor.php' === $pagenow ) {
			wp_safe_redirect( admin_url() ); exit;
		}
		if ( $this->settings->get( 'restrict_theme_editor', 1 ) && 'theme-editor.php' === $pagenow ) {
			wp_safe_redirect( admin_url() ); exit;
		}
		if ( $this->settings->get( 'restrict_plugin_install', 1 ) && in_array( $pagenow, [ 'plugin-install.php', 'update.php' ], true ) ) {
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( in_array( $action, [ 'upload-plugin', 'install-plugin' ], true ) || 'plugin-install.php' === $pagenow ) {
				wp_safe_redirect( admin_url( 'plugins.php' ) ); exit;
			}
		}
		if ( $this->settings->get( 'restrict_theme_install', 1 ) && in_array( $pagenow, [ 'theme-install.php', 'update.php' ], true ) ) {
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( in_array( $action, [ 'upload-theme', 'install-theme' ], true ) || 'theme-install.php' === $pagenow ) {
				wp_safe_redirect( admin_url( 'themes.php' ) ); exit;
			}
		}
		if ( $this->settings->get( 'restrict_theme_switch', 1 ) && in_array( $pagenow, [ 'themes.php', 'customize.php' ], true ) ) {
			wp_safe_redirect( admin_url() ); exit;
		}
		if ( $this->settings->get( 'restrict_updates_page', 1 ) && 'update-core.php' === $pagenow ) {
			wp_safe_redirect( admin_url() ); exit;
		}
	}

	public function filter_plugins_list_actions( $all_plugins ) {
		// Placeholder hook kept to support future per-plugin cleanup; no mutation here.
		return $all_plugins;
	}

	public function filter_plugin_action_links( $actions, $plugin_file ) {
		if ( $this->settings->get( 'restrict_plugin_delete', 1 ) && isset( $actions['delete'] ) ) {
			unset( $actions['delete'] );
		}
		if ( $this->settings->get( 'restrict_plugin_install', 1 ) && isset( $actions['activate'] ) ) {
			// leave activate; no-op.
		}
		return $actions;
	}

	public function filter_theme_action_links( $actions, $theme ) {
		if ( $this->settings->get( 'restrict_theme_switch', 1 ) ) {
			foreach ( [ 'activate', 'live-preview' ] as $k ) {
				if ( isset( $actions[ $k ] ) ) {
					unset( $actions[ $k ] );
				}
			}
		}
		return $actions;
	}
}
