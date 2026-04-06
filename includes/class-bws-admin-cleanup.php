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
		add_action( 'admin_init', [ $this, 'maybe_disable_update_ui' ], 1 );
		add_filter( 'all_plugins', [ $this, 'filter_plugins_list_actions' ] );
		add_filter( 'plugin_action_links', [ $this, 'filter_plugin_action_links' ], 10, 4 );
		add_filter( 'theme_action_links', [ $this, 'filter_theme_action_links' ], 10, 2 );

		if ( $this->settings->get( 'updates_hide_auto_update_column', 1 ) ) {
			add_filter( 'plugins_auto_update_enabled', '__return_false' );
			add_filter( 'themes_auto_update_enabled', '__return_false' );
		}
	}

	private function can_restrict_admin() {
		return is_admin() && current_user_can( 'manage_options' );
	}

	public function maybe_disable_update_ui() {
		if ( ! $this->can_restrict_admin() ) {
			return;
		}

		if ( $this->settings->get( 'updates_hide_nag', 1 ) ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
		}

		if ( $this->settings->get( 'updates_hide_badges', 1 ) ) {
			add_action( 'admin_bar_menu', [ $this, 'remove_updates_admin_bar_node' ], 999 );
		}

		if ( $this->settings->get( 'updates_hide_plugin_update_tab', 1 ) ) {
			add_filter( 'views_plugins', [ $this, 'filter_plugin_views' ] );
		}
	}

	public function remove_updates_admin_bar_node( $wp_admin_bar ) {
		if ( ! is_object( $wp_admin_bar ) ) {
			return;
		}

		$wp_admin_bar->remove_node( 'updates' );
	}

	public function filter_plugin_views( $views ) {
		if ( isset( $views['upgrade'] ) ) {
			unset( $views['upgrade'] );
		}

		return $views;
	}

	public function admin_head_cleanup() {
		if ( ! $this->can_restrict_admin() ) {
			return;
		}

		$selectors = [];

		if ( $this->settings->get( 'updates_hide_plugin_rows', 1 ) ) {
			$selectors[] = '.plugins .plugin-update-tr';
			$selectors[] = '.plugins .update-message';
		}

		if ( $this->settings->get( 'updates_hide_badges', 1 ) ) {
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

		if ( $this->settings->get( 'restrict_plugin_install', 1 ) ) {
			$selectors[] = 'a.page-title-action[href*="plugin-install.php"]';
		}

		if ( $this->settings->get( 'restrict_theme_install', 1 ) ) {
			$selectors[] = 'a.page-title-action[href*="theme-install.php"]';
			$selectors[] = '.theme-browser .page-title-action';
		}

		echo BWS_Utils::build_hide_css( array_unique( $selectors ) );
	}

	private function normalize_top_level_menu_slug( $slug ) {
		$slug = trim( (string) $slug );
		if ( '' === $slug ) {
			return '';
		}

		if ( 0 === strpos( $slug, 'menu-posts-' ) ) {
			$post_type = sanitize_key( substr( $slug, strlen( 'menu-posts-' ) ) );
			return '' !== $post_type ? 'edit.php?post_type=' . $post_type : '';
		}

		if ( 0 === strpos( $slug, 'toplevel_page_' ) ) {
			$slug = trim( substr( $slug, strlen( 'toplevel_page_' ) ) );
		}

		switch ( $slug ) {
			case 'menu-media':
				return 'upload.php';
			case 'menu-pages':
				return 'edit.php?post_type=page';
			case 'menu-posts':
				return 'edit.php';
			case 'menu-comments':
				return 'edit-comments.php';
			case 'menu-appearance':
				return 'themes.php';
			case 'menu-plugins':
				return 'plugins.php';
			case 'menu-users':
				return 'users.php';
			case 'menu-tools':
				return 'tools.php';
			case 'menu-settings':
				return 'options-general.php';
			default:
				return $slug;
		}
	}

	public function cleanup_admin_menus() {
		if ( ! $this->can_restrict_admin() ) {
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

		if ( $this->settings->get( 'restrict_updates_page', 1 ) ) {
			remove_submenu_page( 'index.php', 'update-core.php' );
		}
		if ( $this->settings->get( 'restrict_plugin_install', 1 ) ) {
			remove_submenu_page( 'plugins.php', 'plugin-install.php' );
		}
		if ( $this->settings->get( 'restrict_theme_switch', 1 ) ) {
			remove_submenu_page( 'themes.php', 'themes.php' );
			remove_submenu_page( 'themes.php', 'customize.php' );
		}
		if ( $this->settings->get( 'restrict_theme_install', 1 ) ) {
			remove_submenu_page( 'themes.php', 'theme-install.php' );
		}

		$custom_top = preg_split( '/\r\n|\r|\n/', (string) $this->settings->get( 'menu_hide_custom_slugs', '' ) );
		foreach ( array_filter( array_map( 'trim', (array) $custom_top ) ) as $slug ) {
			$slug = $this->normalize_top_level_menu_slug( $slug );
			if ( '' !== $slug ) {
				remove_menu_page( $slug );
			}
		}

		$custom_sub = preg_split( '/\r\n|\r|\n/', (string) $this->settings->get( 'submenu_hide_custom_slugs', '' ) );
		foreach ( array_filter( array_map( 'trim', (array) $custom_sub ) ) as $line ) {
			$parts = array_map( 'trim', explode( '|', $line ) );
			if ( count( $parts ) >= 2 && '' !== $parts[0] && '' !== $parts[1] ) {
				remove_submenu_page( $parts[0], $parts[1] );
			}
		}
	}

	public function restrict_admin_pages() {
		if ( ! $this->can_restrict_admin() ) {
			return;
		}

		global $pagenow;

		if ( $this->settings->get( 'restrict_plugin_editor', 1 ) && 'plugin-editor.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
		if ( $this->settings->get( 'restrict_theme_editor', 1 ) && 'theme-editor.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
		if ( $this->settings->get( 'restrict_plugin_install', 1 ) && in_array( $pagenow, [ 'plugin-install.php', 'update.php' ], true ) ) {
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( in_array( $action, [ 'upload-plugin', 'install-plugin' ], true ) || 'plugin-install.php' === $pagenow ) {
				wp_safe_redirect( admin_url( 'plugins.php' ) );
				exit;
			}
		}
		if ( $this->settings->get( 'restrict_theme_install', 1 ) && in_array( $pagenow, [ 'theme-install.php', 'update.php' ], true ) ) {
			$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
			if ( in_array( $action, [ 'upload-theme', 'install-theme' ], true ) || 'theme-install.php' === $pagenow ) {
				wp_safe_redirect( admin_url( 'themes.php' ) );
				exit;
			}
		}
		if ( $this->settings->get( 'restrict_theme_switch', 1 ) && in_array( $pagenow, [ 'themes.php', 'customize.php' ], true ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
		if ( $this->settings->get( 'restrict_updates_page', 1 ) && 'update-core.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}

	public function filter_plugins_list_actions( $all_plugins ) {
		return $all_plugins;
	}

	public function filter_plugin_action_links( $actions, $plugin_file ) {
		if ( $this->settings->get( 'restrict_plugin_delete', 1 ) && isset( $actions['delete'] ) ) {
			unset( $actions['delete'] );
		}

		return $actions;
	}

	public function filter_theme_action_links( $actions, $theme ) {
		if ( $this->settings->get( 'restrict_theme_switch', 1 ) ) {
			foreach ( [ 'activate', 'live-preview' ] as $key ) {
				if ( isset( $actions[ $key ] ) ) {
					unset( $actions[ $key ] );
				}
			}
		}

		return $actions;
	}
}
