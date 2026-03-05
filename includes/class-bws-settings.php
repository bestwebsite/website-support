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
			'menu_hide_comments'                     => 1, // changed default to checked
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
			'plugin_hide_settings_menu'              => 0, // unchecked by default = visible in menu
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

		// Backward compatibility: older versions used plugin_show_settings_menu (inverse meaning)
		if ( ! isset( $saved['plugin_hide_settings_menu'] ) && isset( $saved['plugin_show_settings_menu'] ) ) {
			$saved['plugin_hide_settings_menu'] = ! empty( $saved['plugin_show_settings_menu'] ) ? 0 : 1;
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
			'plugin_hide_settings_menu',
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

	public function register_settings_page() {
		if ( ! $this->can_manage() ) {
			return;
		}

		// New behavior: unchecked = visible in Settings menu, checked = direct URL only.
		$parent_slug = $this->get( 'plugin_hide_settings_menu', 0 ) ? null : 'options-general.php';

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
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ); ?></h1>
			<p><?php echo esc_html__( 'Client admin cleanup, branding, login customization, and support tools for managed WordPress sites.', BWS_TEXT_DOMAIN ); ?></p>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . BWS_SUPPORT_PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Open Website Support Page', BWS_TEXT_DOMAIN ); ?></a>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . BWS_SETTINGS_PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Refresh', BWS_TEXT_DOMAIN ); ?></a>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'bws_settings_group' ); ?>

				<h2><?php esc_html_e( 'Dashboard Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'dashboard_remove_quick_draft', 'Remove Quick Draft' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_events_news', 'Remove WordPress Events and News' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_activity', 'Remove Activity' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_at_a_glance', 'Remove At a Glance' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_site_health', 'Remove Site Health' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_welcome_panel', 'Remove Welcome Panel' ); ?></p>
				<p><?php $this->textarea( 'dashboard_remove_custom_widget_ids', 'Custom Dashboard Widget IDs to Remove (one per line)', 4 ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Update UI Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'updates_hide_nag', 'Hide update nag' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_plugin_rows', 'Hide plugin update rows/messages' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_badges', 'Hide update badges/counts' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_auto_update_column', 'Hide plugin auto-update column/links' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_plugin_update_tab', 'Hide Plugins “Update Available” tab' ); ?></p>
				<p><?php $this->checkbox( 'restrict_updates_page', 'Hide/redirect Updates screen' ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Admin Restrictions & Menu Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'restrict_plugin_editor', 'Hide Plugin File Editor' ); ?></p>
				<p><?php $this->checkbox( 'restrict_theme_editor', 'Hide Theme File Editor' ); ?></p>
				<p><?php $this->checkbox( 'restrict_plugin_install', 'Hide Plugin Add New / Upload' ); ?></p>
				<p><?php $this->checkbox( 'restrict_plugin_delete', 'Hide Plugin Delete links' ); ?></p>
				<p><?php $this->checkbox( 'restrict_theme_install', 'Hide Theme Add New / Upload' ); ?></p>
				<p><?php $this->checkbox( 'restrict_theme_switch', 'Hide Theme switching / Theme pages' ); ?></p>
				<p><?php $this->checkbox( 'menu_hide_tools', 'Hide Tools menu' ); ?></p>
				<p><?php $this->checkbox( 'menu_hide_comments', 'Hide Comments menu' ); ?></p>
				<p><?php $this->checkbox( 'menu_hide_settings', 'Hide Settings menu' ); ?></p>
				<p><?php $this->checkbox( 'menu_hide_users', 'Hide Users menu' ); ?></p>
				<p><?php $this->checkbox( 'menu_hide_plugins', 'Hide Plugins menu' ); ?></p>
				<p><?php $this->checkbox( 'menu_hide_appearance', 'Hide Appearance menu' ); ?></p>
				<p><?php $this->textarea( 'menu_hide_custom_slugs', 'Hide Top-Level Menu Slugs (one per line)', 4 ); ?></p>
				<p><?php $this->textarea( 'submenu_hide_custom_slugs', 'Hide Submenu Slugs (format: parent_slug|submenu_slug, one per line)', 4 ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Label Renaming', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->text( 'label_posts', 'Rename “Posts” (optional)' ); ?></p>
				<p><?php $this->text( 'label_pages', 'Rename “Pages” (optional)' ); ?></p>
				<p><?php $this->text( 'label_media', 'Rename “Media” (optional)' ); ?></p>
				<p><?php $this->textarea( 'label_cpt_map', 'CPT Menu Label Overrides (post_type|Menu Label|Add New Label)', 6 ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Branding', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'branding_footer_enabled', 'Replace admin footer text' ); ?></p>
				<p><?php $this->text( 'branding_footer_text', 'Footer Text' ); ?></p>
				<p><?php $this->text( 'branding_footer_version_text', 'Footer Version Text (optional)' ); ?></p>
				<p><?php $this->text( 'branding_support_logo_url', 'Support Logo URL (optional)' ); ?></p>
				<p><?php $this->text( 'branding_support_widget_intro', 'Support Widget Intro Text' ); ?></p>
				<p><?php $this->text( 'branding_support_page_intro', 'Support Page Intro Text' ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Website Support', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'support_widget_enabled', 'Enable Dashboard Widget' ); ?></p>
				<p><?php $this->checkbox( 'support_page_enabled', 'Enable Support Sidebar Page' ); ?></p>
				<p><?php $this->text( 'support_page_label', 'Support Sidebar Label', 'Website Support' ); ?></p>
				<p><?php $this->text( 'support_email', 'Support Email', 'support@bestwebsite.com' ); ?></p>
				<p><?php $this->textarea( 'support_topic_options', 'Topics (one per line)', 6 ); ?></p>
				<p><?php $this->text( 'support_success_message', 'Success Message' ); ?></p>
				<p><?php $this->textarea( 'support_instructions_text', 'Instructions', 4 ); ?></p>
				<p><?php $this->checkbox( 'support_include_diagnostics', 'Include diagnostics metadata in emails' ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Login Branding', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'login_branding_enabled', 'Enable custom login branding' ); ?></p>
				<p><?php $this->text( 'login_logo_url', 'Login Logo URL (optional)' ); ?></p>
				<p><?php $this->text( 'login_logo_link_url', 'Login Logo Link URL' ); ?></p>
				<p><?php $this->text( 'login_logo_title', 'Login Logo Title Text' ); ?></p>
				<p><?php $this->color( 'login_bg_color', 'Login Background Color' ); ?></p>
				<p><?php $this->color( 'login_button_color', 'Login Button Color' ); ?></p>
				<p><?php $this->textarea( 'login_help_text', 'Login Help Text (shown below form)', 3 ); ?></p>

				<hr>
				<h2><?php esc_html_e( 'Plugin Visibility / White-Label', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'plugin_whitelabel_enabled', 'Enable white-label behavior' ); ?></p>
				<p><?php $this->checkbox( 'plugin_hide_settings_menu', 'Hide settings page in admin menu (direct URL only)' ); ?></p>
				<p><?php $this->checkbox( 'plugin_hide_from_plugins_list', 'Hide this plugin from Plugins list (advanced)' ); ?></p>
				<p><?php $this->checkbox( 'plugin_hide_plugin_ui_badges', 'Hide this plugin’s update row/badges when possible' ); ?></p>
				<p><?php $this->checkbox( 'plugin_hide_support_menu_from_adminbar', 'Hide support page from admin bar shortcuts (future-safe)' ); ?></p>

				<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
			</form>
		</div>
		<?php
	}
}
