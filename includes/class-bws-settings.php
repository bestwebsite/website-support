<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Settings {
	public function get_defaults() {
		return [
			'dashboard_remove_quick_draft'           => 1,
			'dashboard_remove_events_news'           => 1,
			'dashboard_remove_activity'              => 1,
			'dashboard_remove_at_a_glance'           => 1,
			'dashboard_remove_site_health'           => 1,
			'dashboard_remove_welcome_panel'         => 0,
			'dashboard_remove_custom_widget_ids'     => '',

			'admin_notice_hide_selectors'           => '',

			'updates_hide_nag'                       => 1,
			'updates_hide_plugin_rows'               => 1,
			'updates_hide_badges'                    => 1,
			'updates_hide_auto_update_column'        => 1,
			'updates_hide_plugin_update_tab'         => 1,
			'restrict_updates_page'                  => 1,

			'restrict_plugin_editor'                 => 1,
			'restrict_theme_editor'                  => 1,
			'restrict_plugin_install'                => 1,
			'restrict_plugin_delete'                 => 1,
			'restrict_theme_install'                 => 1,
			'restrict_theme_switch'                  => 1,
			'menu_hide_tools'                        => 1,
			'menu_hide_comments'                     => 1,
			'menu_hide_settings'                     => 0,
			'menu_hide_users'                        => 0,
			'menu_hide_plugins'                      => 0,
			'menu_hide_appearance'                   => 0,

			'menu_hide_custom_slugs'                 => '',
			'submenu_hide_custom_slugs'              => '',

			'label_posts'                            => '',
			'label_pages'                            => '',
			'label_media'                            => '',
			'label_cpt_map'                          => "# Format: post_type|Menu Label|Add New Label\n# Example: event-item|Events|Add New Event",

			'branding_footer_enabled'                => 1,
			'branding_footer_text'                   => 'Managed by Best Website • support@bestwebsite.com',
			'branding_footer_version_text'           => '',
			'branding_support_logo_url'              => '',
			'branding_support_widget_intro'          => 'Managed Website Support by Best Website',
			'branding_support_page_intro'            => 'Use this form to contact Best Website for support, changes, or questions about your website.',

			'support_widget_enabled'                 => 1,
			'support_page_enabled'                   => 1,
			'support_page_label'                     => 'Website Support',
			'support_email'                          => 'support@bestwebsite.com',
			'support_topic_options'                  => "Technical Support\nContent Update Request\nSEO / Marketing Question\nWebsite Change Request\nOther",
			'support_success_message'                => 'Thanks! Your message has been sent to Best Website Support.',
			'support_instructions_text'              => 'Please share as much detail as possible, including page URLs and what you expected to happen.',
			'support_include_diagnostics'            => 1,

			'login_branding_enabled'                 => 1,
			'login_logo_url'                         => '',
			'login_logo_link_url'                    => home_url( '/' ),
			'login_logo_title'                       => get_bloginfo( 'name' ),
			'login_bg_color'                         => '#f6f7fb',
			'login_button_color'                     => '#2271b1',
			'login_help_text'                        => 'Website managed by Best Website • support@bestwebsite.com',

			'plugin_whitelabel_enabled'              => 1,
			'plugin_show_settings_menu'              => 0,
			'plugin_hide_from_plugins_list'          => 0,
			'plugin_hide_plugin_ui_badges'           => 0,
			'plugin_hide_support_menu_from_adminbar' => 0,
		];
	}

	public function get_all() {
		$saved = get_option( BWS_OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}
		return wp_parse_args( $saved, $this->get_defaults() );
	}

	public function get( $key, $default = null ) {
		$all = $this->get_all();
		return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	public function register_settings() {
		register_setting(
			'bws_settings_group',
			BWS_OPTION_KEY,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'default'           => $this->get_defaults(),
			]
		);
	}

	public function sanitize_settings( $input ) {
		$defaults = $this->get_defaults();
		$input    = is_array( $input ) ? $input : [];
		$output   = [];

		$checkbox_keys = [
			'dashboard_remove_quick_draft',
			'dashboard_remove_events_news',
			'dashboard_remove_activity',
			'dashboard_remove_at_a_glance',
			'dashboard_remove_site_health',
			'dashboard_remove_welcome_panel',
			'updates_hide_nag',
			'updates_hide_plugin_rows',
			'updates_hide_badges',
			'updates_hide_auto_update_column',
			'updates_hide_plugin_update_tab',
			'restrict_updates_page',
			'restrict_plugin_editor',
			'restrict_theme_editor',
			'restrict_plugin_install',
			'restrict_plugin_delete',
			'restrict_theme_install',
			'restrict_theme_switch',
			'menu_hide_tools',
			'menu_hide_comments',
			'menu_hide_settings',
			'menu_hide_users',
			'menu_hide_plugins',
			'menu_hide_appearance',
			'branding_footer_enabled',
			'support_widget_enabled',
			'support_page_enabled',
			'support_include_diagnostics',
			'login_branding_enabled',
			'plugin_whitelabel_enabled',
			'plugin_show_settings_menu',
			'plugin_hide_from_plugins_list',
			'plugin_hide_plugin_ui_badges',
			'plugin_hide_support_menu_from_adminbar',
		];

		foreach ( $checkbox_keys as $key ) {
			$output[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
		}

		$text_keys = [
			'branding_footer_text',
			'branding_footer_version_text',
			'branding_support_widget_intro',
			'branding_support_page_intro',
			'support_page_label',
			'support_success_message',
			'login_logo_title',
			'login_bg_color',
			'login_button_color',
		];

		foreach ( $text_keys as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : ( $defaults[ $key ] ?? '' );
		}

		$output['branding_support_logo_url']  = isset( $input['branding_support_logo_url'] ) ? esc_url_raw( $input['branding_support_logo_url'] ) : '';
		$output['support_email']              = isset( $input['support_email'] ) ? sanitize_email( $input['support_email'] ) : $defaults['support_email'];
		$output['login_logo_url']             = isset( $input['login_logo_url'] ) ? esc_url_raw( $input['login_logo_url'] ) : '';
		$output['login_logo_link_url']        = isset( $input['login_logo_link_url'] ) ? esc_url_raw( $input['login_logo_link_url'] ) : home_url( '/' );

		$textarea_keys = [
			'dashboard_remove_custom_widget_ids',
			'admin_notice_hide_selectors',
			'menu_hide_custom_slugs',
			'submenu_hide_custom_slugs',
			'label_cpt_map',
			'support_topic_options',
			'support_instructions_text',
			'login_help_text',
		];
		foreach ( $textarea_keys as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : ( $defaults[ $key ] ?? '' );
		}

		foreach ( [ 'label_posts', 'label_pages', 'label_media' ] as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		}

		return wp_parse_args( $output, $defaults );
	}

	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	public function enqueue_admin_assets( $hook_suffix ) {
		// Only load on our settings screen.
		if ( ! $this->can_manage() ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( BWS_SETTINGS_PAGE_SLUG !== $page ) {
			return;
		}
		$css = BWS_PLUGIN_URL . 'assets/admin-settings.css';
		wp_enqueue_style( 'bws-admin-settings', $css, [], BWS_VERSION );
	}


	public function register_settings_page() {
		if ( ! $this->can_manage() ) {
			return;
		}

		$parent_slug = $this->get( 'plugin_show_settings_menu', 0 ) ? null : 'options-general.php';

		add_submenu_page(
			$parent_slug,
			__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ),
			__( 'Website Support', BWS_TEXT_DOMAIN ),
			'manage_options',
			BWS_SETTINGS_PAGE_SLUG,
			[ $this, 'render_settings_page' ]
		);
	}

	private function checkbox( $key, $label ) {
		printf(
			'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			checked( 1, (int) $this->get( $key, 0 ), false ),
			esc_html( $label )
		);
	}

	private function text( $key, $label, $placeholder = '' ) {
		printf(
			'<label for="%1$s_%2$s"><strong>%3$s</strong></label><br><input type="text" class="regular-text" id="%1$s_%2$s" name="%1$s[%2$s]" value="%4$s" placeholder="%5$s">',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) $this->get( $key, '' ) ),
			esc_attr( $placeholder )
		);
	}

	private function textarea( $key, $label, $rows = 4 ) {
		printf(
			'<label for="%1$s_%2$s"><strong>%3$s</strong></label><br><textarea class="large-text code" rows="%5$d" id="%1$s_%2$s" name="%1$s[%2$s]">%4$s</textarea>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_textarea( (string) $this->get( $key, '' ) ),
			(int) $rows
		);
	}

	private function color( $key, $label ) {
		printf(
			'<label for="%1$s_%2$s"><strong>%3$s</strong></label><br><input type="text" class="regular-text" id="%1$s_%2$s" name="%1$s[%2$s]" value="%4$s" placeholder="#2271b1">',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) $this->get( $key, '' ) )
		);
	}

	
	public function render_settings_page() {
		if ( ! $this->can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', BWS_TEXT_DOMAIN ) );
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
		$tabs       = [
			'dashboard'    => __( 'Dashboard', BWS_TEXT_DOMAIN ),
			'updates'      => __( 'Updates', BWS_TEXT_DOMAIN ),
			'restrictions' => __( 'Restrictions', BWS_TEXT_DOMAIN ),
			'labels'       => __( 'Labels', BWS_TEXT_DOMAIN ),
			'branding'     => __( 'Branding', BWS_TEXT_DOMAIN ),
			'support'      => __( 'Support', BWS_TEXT_DOMAIN ),
			'login'        => __( 'Login', BWS_TEXT_DOMAIN ),
			'whitelabel'   => __( 'White-Label', BWS_TEXT_DOMAIN ),
		];
		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = 'dashboard';
		}

		echo '<div class="wrap bws-settings-wrap" data-bws-active-tab="' . esc_attr( $active_tab ) . '">';

		echo '<div class="bws-settings-head">';
		echo '<h1>' . esc_html__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ) . '</h1>';
		echo '<p class="bws-settings-subtitle">' . esc_html__( 'Client admin cleanup, branding, login customization, and support tools for managed WordPress sites.', BWS_TEXT_DOMAIN ) . '</p>';
		echo '<div class="bws-settings-actions">';
		echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=' . BWS_SUPPORT_PAGE_SLUG ) ) . '">' . esc_html__( 'Open Website Support Page', BWS_TEXT_DOMAIN ) . '</a> ';
		echo '<a class="button button-secondary" href="' . esc_url( admin_url( 'admin.php?page=' . BWS_SETTINGS_PAGE_SLUG ) ) . '">' . esc_html__( 'Refresh', BWS_TEXT_DOMAIN ) . '</a>';
		echo '</div>';
		echo '</div>';

		// Tabs.
		echo '<nav class="nav-tab-wrapper bws-tabs" aria-label="' . esc_attr__( 'Settings Sections', BWS_TEXT_DOMAIN ) . '">';
		foreach ( $tabs as $tab_key => $tab_label ) {
			$url   = add_query_arg(
				[
					'page' => BWS_SETTINGS_PAGE_SLUG,
					'tab'  => $tab_key,
				],
				admin_url( 'options-general.php' )
			);
			$class = 'nav-tab';
			if ( $tab_key === $active_tab ) {
				$class .= ' nav-tab-active';
			}
			echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $tab_label ) . '</a>';
		}
		echo '</nav>';

		echo '<form method="post" action="options.php" class="bws-settings-form">';
		settings_fields( 'bws_settings_group' );

		// Sticky save bar.
		echo '<div class="bws-sticky-save">';
		echo '<div class="bws-sticky-save-inner">';
		echo '<span class="bws-sticky-save-title">' . esc_html( $tabs[ $active_tab ] ) . '</span>';
		submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ), 'primary', 'submit', false );
		echo '</div></div>';

		// Panels (all rendered so saving does not reset hidden sections).
		$this->render_panel_dashboard();
		$this->render_panel_updates();
		$this->render_panel_restrictions();
		$this->render_panel_labels();
		$this->render_panel_branding();
		$this->render_panel_support();
		$this->render_panel_login();
		$this->render_panel_whitelabel();

		echo '</form>';
		echo '</div>';
	}

	private function card_open( $title, $desc = '' ) {
		echo '<div class="bws-card">';
		echo '<div class="bws-card-header">';
		echo '<h2 class="bws-card-title">' . esc_html( $title ) . '</h2>';
		if ( '' !== trim( (string) $desc ) ) {
			echo '<p class="bws-card-desc">' . esc_html( $desc ) . '</p>';
		}
		echo '</div>';
		echo '<div class="bws-card-body">';
	}

	private function card_close() {
		echo '</div></div>';
	}

	private function panel_open( $tab_key ) {
		echo '<section class="bws-tab-panel" data-bws-tab="' . esc_attr( $tab_key ) . '">';
	}

	private function panel_close() {
		echo '</section>';
	}

	private function render_panel_dashboard() {
		$this->panel_open( 'dashboard' );

		$this->card_open( __( 'Dashboard Cleanup', BWS_TEXT_DOMAIN ), __( 'Remove clutter from the main WordPress dashboard screen.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'dashboard_remove_quick_draft', __( 'Remove Quick Draft', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'dashboard_remove_events_news', __( 'Remove WordPress Events and News', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'dashboard_remove_activity', __( 'Remove Activity', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'dashboard_remove_at_a_glance', __( 'Remove At a Glance', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'dashboard_remove_site_health', __( 'Remove Site Health', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'dashboard_remove_welcome_panel', __( 'Remove Welcome Panel', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->textarea( 'dashboard_remove_custom_widget_ids', __( 'Custom Dashboard Widget IDs to Remove (one per line)', BWS_TEXT_DOMAIN ), 4 ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_updates() {
		$this->panel_open( 'updates' );

		$this->card_open( __( 'Update UI Cleanup', BWS_TEXT_DOMAIN ), __( 'Hide WordPress update nags, badges, and plugin update rows where appropriate.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'updates_hide_nag', __( 'Hide update nag', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'updates_hide_plugin_rows', __( 'Hide plugin update rows/messages', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'updates_hide_badges', __( 'Hide update badges/counts', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'updates_hide_auto_update_column', __( 'Hide plugin auto-update column/links', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'updates_hide_plugin_update_tab', __( 'Hide Plugins “Update Available” tab', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'restrict_updates_page', __( 'Hide/redirect Updates screen', BWS_TEXT_DOMAIN ) ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_restrictions() {
		$this->panel_open( 'restrictions' );

		$this->card_open( __( 'Admin Restrictions & Menu Cleanup', BWS_TEXT_DOMAIN ), __( 'Limit risky admin functionality and remove unused menus.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'restrict_plugin_editor', __( 'Hide Plugin File Editor', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'restrict_theme_editor', __( 'Hide Theme File Editor', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'restrict_plugin_install', __( 'Hide Plugin Add New / Upload', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'restrict_plugin_delete', __( 'Hide Plugin Delete links', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'restrict_theme_install', __( 'Hide Theme Add New / Upload', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'restrict_theme_switch', __( 'Hide Theme switching / Theme pages', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<hr class="bws-hr">';
		echo '<p>'; $this->checkbox( 'menu_hide_tools', __( 'Hide Tools menu', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'menu_hide_comments', __( 'Hide Comments menu', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'menu_hide_settings', __( 'Hide Settings menu', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'menu_hide_users', __( 'Hide Users menu', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'menu_hide_plugins', __( 'Hide Plugins menu', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'menu_hide_appearance', __( 'Hide Appearance menu', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->textarea( 'menu_hide_custom_slugs', __( 'Hide Top-Level Menu Slugs (one per line)', BWS_TEXT_DOMAIN ), 4 ); echo '</p>';
		echo '<p>'; $this->textarea( 'submenu_hide_custom_slugs', __( 'Hide Submenu Slugs (format: parent_slug|submenu_slug, one per line)', BWS_TEXT_DOMAIN ), 4 ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_labels() {
		$this->panel_open( 'labels' );

		$this->card_open( __( 'Label Renaming', BWS_TEXT_DOMAIN ), __( 'Rename default WordPress labels and override CPT menu labels.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->text( 'label_posts', __( 'Rename “Posts” (optional)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'label_pages', __( 'Rename “Pages” (optional)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'label_media', __( 'Rename “Media” (optional)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->textarea( 'label_cpt_map', __( 'CPT Menu Label Overrides (post_type|Menu Label|Add New Label)', BWS_TEXT_DOMAIN ), 6 ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_branding() {
		$this->panel_open( 'branding' );

		$this->card_open( __( 'Branding', BWS_TEXT_DOMAIN ), __( 'Add Best Website branding and optional client-facing copy.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'branding_footer_enabled', __( 'Replace admin footer text', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'branding_footer_text', __( 'Footer Text', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'branding_footer_version_text', __( 'Footer Version Text (optional)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'branding_support_logo_url', __( 'Support Logo URL (optional)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'branding_support_widget_intro', __( 'Support Widget Intro Text', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'branding_support_page_intro', __( 'Support Page Intro Text', BWS_TEXT_DOMAIN ) ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_support() {
		$this->panel_open( 'support' );

		$this->card_open( __( 'Website Support', BWS_TEXT_DOMAIN ), __( 'Dashboard widget and a sidebar page that sends support requests to Best Website.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'support_widget_enabled', __( 'Enable Dashboard Widget', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'support_page_enabled', __( 'Enable Support Sidebar Page', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'support_page_label', __( 'Support Sidebar Label', BWS_TEXT_DOMAIN ), 'Website Support' ); echo '</p>';
		echo '<p>'; $this->text( 'support_email', __( 'Support Email', BWS_TEXT_DOMAIN ), 'support@bestwebsite.com' ); echo '</p>';
		echo '<p>'; $this->textarea( 'support_topic_options', __( 'Topics (one per line)', BWS_TEXT_DOMAIN ), 6 ); echo '</p>';
		echo '<p>'; $this->text( 'support_success_message', __( 'Success Message', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->textarea( 'support_instructions_text', __( 'Instructions', BWS_TEXT_DOMAIN ), 4 ); echo '</p>';
		echo '<p>'; $this->checkbox( 'support_include_diagnostics', __( 'Include diagnostics metadata in emails', BWS_TEXT_DOMAIN ) ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_login() {
		$this->panel_open( 'login' );

		$this->card_open( __( 'Login Branding', BWS_TEXT_DOMAIN ), __( 'Customize the WordPress login screen for managed sites.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'login_branding_enabled', __( 'Enable custom login branding', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'login_logo_url', __( 'Login Logo URL (optional)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'login_logo_link_url', __( 'Login Logo Link URL', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->text( 'login_logo_title', __( 'Login Logo Title Text', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->color( 'login_bg_color', __( 'Login Background Color', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->color( 'login_button_color', __( 'Login Button Color', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->textarea( 'login_help_text', __( 'Login Help Text (shown below form)', BWS_TEXT_DOMAIN ), 3 ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

	private function render_panel_whitelabel() {
		$this->panel_open( 'whitelabel' );

		$this->card_open( __( 'Plugin Visibility / White-Label', BWS_TEXT_DOMAIN ), __( 'Control plugin visibility and optional admin UI hiding behavior.', BWS_TEXT_DOMAIN ) );
		echo '<p>'; $this->checkbox( 'plugin_whitelabel_enabled', __( 'Enable white-label behavior', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'plugin_show_settings_menu', __( 'Hide settings page in admin menu (direct URL only)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'plugin_hide_from_plugins_list', __( 'Hide this plugin from Plugins list (advanced)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'plugin_hide_plugin_ui_badges', __( 'Hide this plugin’s update row/badges when possible', BWS_TEXT_DOMAIN ) ); echo '</p>';
		echo '<p>'; $this->checkbox( 'plugin_hide_support_menu_from_adminbar', __( 'Hide support page from admin bar shortcuts (future-safe)', BWS_TEXT_DOMAIN ) ); echo '</p>';
		$this->card_close();

		$this->panel_close();
	}

}
