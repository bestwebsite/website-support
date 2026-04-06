<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Settings {
	private $settings_cache = null;

	public function get_defaults() {
		return [
			// Dashboard
			'dashboard_remove_quick_draft'           => 1,
			'dashboard_remove_events_news'           => 1,
			'dashboard_remove_activity'              => 1,
			'dashboard_remove_at_a_glance'           => 1,
			'dashboard_remove_site_health'           => 1,
			'dashboard_remove_welcome_panel'         => 0,
			'dashboard_remove_wp_mail_smtp_widget'   => 1,
			'dashboard_remove_elementor_overview'    => 1,
			'dashboard_remove_elementor_ally'        => 1,
			'dashboard_remove_custom_widget_ids'     => '',
			'admin_notice_hide_selectors'            => '',

			// Updates
			'updates_hide_nag'                       => 1,
			'updates_hide_plugin_rows'               => 1,
			'updates_hide_badges'                    => 1,
			'updates_hide_auto_update_column'        => 1,
			'updates_hide_plugin_update_tab'         => 1,
			'restrict_updates_page'                  => 1,

			// Restrictions / menus
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

			// Labels
			'label_posts'                            => '',
			'label_pages'                            => '',
			'label_media'                            => '',
			'label_cpt_map'                          => "# Format: post_type|Menu Label|Add New Label
# Example: event-item|Events|Add New Event",

			// Branding
			'branding_footer_enabled'                => 1,
			'branding_footer_text'                   => 'Managed by Best Website • support@bestwebsite.com',
			'branding_footer_version_text'           => '',
			'branding_support_logo_url'              => '',
			'branding_support_widget_intro'          => 'Managed Website Support by Best Website',
			'branding_support_page_intro'            => 'Use this form to contact Best Website for support, changes, or questions about your website.',

			// Support
			'support_widget_enabled'                 => 1,
			'support_page_enabled'                   => 1,
			'support_page_label'                     => 'Website Support',
			'support_email'                          => 'support@bestwebsite.com',
			'support_topic_options'                  => "Technical Support
Content Update Request
SEO / Marketing Question
Website Change Request
Other",
			'support_success_message'                => 'Thanks! Your message has been sent to Best Website Support.',
			'support_instructions_text'              => 'Please share as much detail as possible, including page URLs and what you expected to happen.',
			'support_include_diagnostics'            => 1,
			'support_force_from_domain'              => 0,

			// Login
			'login_branding_enabled'                 => 1,
			'login_logo_url'                         => '',
			'login_logo_link_url'                    => home_url( '/' ),
			'login_logo_title'                       => get_bloginfo( 'name' ),
			'login_bg_color'                         => '#f6f7fb',
			'login_button_color'                     => '#2271b1',
			'login_help_text'                        => 'Website managed by Best Website • support@bestwebsite.com',

			// White-label
			'plugin_whitelabel_enabled'              => 1,
			'plugin_hide_settings_menu'              => 0,
			'plugin_hide_from_plugins_list'          => 0,
			'plugin_hide_plugin_ui_badges'           => 0,
			'plugin_hide_support_menu_from_adminbar' => 0,

			// Hardening & Performance
			'comments_disable_sitewide'              => 1,
			'comments_disable_feeds'                 => 0,
			'security_disable_xmlrpc_pingbacks'      => 1,
			'security_disable_application_passwords' => 1,
			'security_block_author_enum'             => 1,
			'security_remove_generator'              => 1,
			'security_force_ssl_admin'               => 1,
			'perf_disable_emojis'                    => 1,
			'perf_disable_oembed'                    => 1,
			'perf_disable_dashicons_visitors'        => 1,
			'perf_limit_revisions_enabled'           => 1,
			'perf_limit_revisions_count'             => 10,
			'seo_disable_attachment_pages'           => 1,
		];
	}

	public function get_all() {
		if ( null !== $this->settings_cache ) {
			return $this->settings_cache;
		}

		$saved = get_option( BWS_OPTION_KEY, [] );
		if ( ! is_array( $saved ) ) {
			$saved = [];
		}

		$this->settings_cache = wp_parse_args( $saved, $this->get_defaults() );
		return $this->settings_cache;
	}

	public function get( $key, $default = null ) {
		$all = $this->get_all();
		return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	public function clear_cache() {
		$this->settings_cache = null;
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

	private function sanitize_plain_multiline( $value, $max_lines = 100, $max_line_length = 250 ) {
		return BWS_Utils::sanitize_plain_multiline( $value, $max_lines, $max_line_length );
	}

	private function sanitize_topics( $value ) {
		return $this->sanitize_plain_multiline( $value, 25, 120 );
	}

	private function sanitize_cpt_map( $value ) {
		$lines   = explode( "
", BWS_Utils::normalize_newlines( wp_unslash( (string) $value ) ) );
		$output  = [];
		$counter = 0;

		foreach ( $lines as $line ) {
			if ( $counter >= 100 ) {
				break;
			}

			$line = trim( (string) $line );
			if ( '' === $line ) {
				continue;
			}

			if ( '#' === substr( $line, 0, 1 ) ) {
				$output[] = $line;
				$counter++;
				continue;
			}

			$parts = array_map( 'trim', explode( '|', wp_strip_all_tags( $line, true ) ) );
			if ( count( $parts ) < 2 ) {
				continue;
			}

			$key = (string) $parts[0];
			if ( 0 === strpos( $key, 'menu-posts-' ) ) {
				$key = substr( $key, strlen( 'menu-posts-' ) );
			}
			if ( 0 === strpos( $key, 'edit.php?post_type=' ) ) {
				$key = substr( $key, strlen( 'edit.php?post_type=' ) );
			}

			$key      = sanitize_key( $key );
			$menu     = sanitize_text_field( $parts[1] );
			$add_new  = isset( $parts[2] ) ? sanitize_text_field( $parts[2] ) : '';

			if ( '' === $key || '' === $menu ) {
				continue;
			}

			$output[] = $key . '|' . $menu . '|' . ( '' !== $add_new ? $add_new : sprintf( __( 'Add New %s', BWS_TEXT_DOMAIN ), rtrim( $menu, 's' ) ) );
			$counter++;
		}

		return implode( "
", $output );
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
			'dashboard_remove_wp_mail_smtp_widget',
			'dashboard_remove_elementor_overview',
			'dashboard_remove_elementor_ally',
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
			'support_force_from_domain',
			'login_branding_enabled',
			'plugin_whitelabel_enabled',
			'plugin_hide_settings_menu',
			'plugin_hide_from_plugins_list',
			'plugin_hide_plugin_ui_badges',
			'plugin_hide_support_menu_from_adminbar',
			'comments_disable_sitewide',
			'comments_disable_feeds',
			'security_disable_xmlrpc_pingbacks',
			'security_disable_application_passwords',
			'security_block_author_enum',
			'security_remove_generator',
			'security_force_ssl_admin',
			'perf_disable_emojis',
			'perf_disable_oembed',
			'perf_disable_dashicons_visitors',
			'perf_limit_revisions_enabled',
			'seo_disable_attachment_pages',
		];

		foreach ( $checkbox_keys as $key ) {
			$output[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
		}

		$output['menu_hide_custom_slugs']             = BWS_Utils::sanitize_menu_slug_list( $input['menu_hide_custom_slugs'] ?? $defaults['menu_hide_custom_slugs'] );
		$output['submenu_hide_custom_slugs']          = BWS_Utils::sanitize_submenu_map( $input['submenu_hide_custom_slugs'] ?? $defaults['submenu_hide_custom_slugs'] );
		$output['label_posts']                        = sanitize_text_field( $input['label_posts'] ?? $defaults['label_posts'] );
		$output['label_pages']                        = sanitize_text_field( $input['label_pages'] ?? $defaults['label_pages'] );
		$output['label_media']                        = sanitize_text_field( $input['label_media'] ?? $defaults['label_media'] );
		$output['label_cpt_map']                      = $this->sanitize_cpt_map( $input['label_cpt_map'] ?? $defaults['label_cpt_map'] );
		$output['branding_footer_text']               = sanitize_text_field( $input['branding_footer_text'] ?? $defaults['branding_footer_text'] );
		$output['branding_footer_version_text']       = sanitize_text_field( $input['branding_footer_version_text'] ?? $defaults['branding_footer_version_text'] );
		$output['branding_support_logo_url']          = esc_url_raw( $input['branding_support_logo_url'] ?? $defaults['branding_support_logo_url'] );
		$output['branding_support_widget_intro']      = sanitize_text_field( $input['branding_support_widget_intro'] ?? $defaults['branding_support_widget_intro'] );
		$output['branding_support_page_intro']        = sanitize_text_field( $input['branding_support_page_intro'] ?? $defaults['branding_support_page_intro'] );
		$output['support_page_label']                 = sanitize_text_field( $input['support_page_label'] ?? $defaults['support_page_label'] );
		$output['support_email']                      = sanitize_email( $input['support_email'] ?? $defaults['support_email'] );
		$output['support_topic_options']              = $this->sanitize_topics( $input['support_topic_options'] ?? $defaults['support_topic_options'] );
		$output['support_success_message']            = sanitize_text_field( $input['support_success_message'] ?? $defaults['support_success_message'] );
		$output['support_instructions_text']          = $this->sanitize_plain_multiline( $input['support_instructions_text'] ?? $defaults['support_instructions_text'], 10, 200 );
		$output['login_logo_url']                     = esc_url_raw( $input['login_logo_url'] ?? $defaults['login_logo_url'] );
		$output['login_logo_link_url']                = esc_url_raw( $input['login_logo_link_url'] ?? $defaults['login_logo_link_url'] );
		$output['login_logo_title']                   = sanitize_text_field( $input['login_logo_title'] ?? $defaults['login_logo_title'] );
		$output['login_bg_color']                     = sanitize_hex_color( $input['login_bg_color'] ?? '' );
		$output['login_button_color']                 = sanitize_hex_color( $input['login_button_color'] ?? '' );
		$output['login_help_text']                    = $this->sanitize_plain_multiline( $input['login_help_text'] ?? $defaults['login_help_text'], 5, 200 );
		$output['dashboard_remove_custom_widget_ids'] = BWS_Utils::sanitize_dashboard_widget_ids( $input['dashboard_remove_custom_widget_ids'] ?? $defaults['dashboard_remove_custom_widget_ids'] );
		$output['admin_notice_hide_selectors']        = BWS_Utils::sanitize_css_selector_list( $input['admin_notice_hide_selectors'] ?? $defaults['admin_notice_hide_selectors'] );
		$output['perf_limit_revisions_count']         = isset( $input['perf_limit_revisions_count'] ) ? max( 0, (int) $input['perf_limit_revisions_count'] ) : (int) $defaults['perf_limit_revisions_count'];

		if ( empty( $output['support_email'] ) || ! is_email( $output['support_email'] ) ) {
			$output['support_email'] = $defaults['support_email'];
		}

		if ( empty( $output['login_bg_color'] ) ) {
			$output['login_bg_color'] = $defaults['login_bg_color'];
		}

		if ( empty( $output['login_button_color'] ) ) {
			$output['login_button_color'] = $defaults['login_button_color'];
		}

		$this->settings_cache = wp_parse_args( $output, $defaults );
		return $this->settings_cache;
	}

	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	public function register_settings_page() {
		if ( ! $this->can_manage() ) {
			return;
		}

		// Hide settings menu when requested; page remains accessible via direct URL.
		if ( ! $this->get( 'plugin_hide_settings_menu', 0 ) ) {
			add_options_page(
				__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ),
				__( 'Website Support', BWS_TEXT_DOMAIN ),
				'manage_options',
				BWS_SETTINGS_PAGE_SLUG,
				[ $this, 'render_settings_page' ]
			);
		}

		// Always register page for direct access (even if menu hidden).
		add_submenu_page(
			null,
			__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ),
			__( 'Website Support', BWS_TEXT_DOMAIN ),
			'manage_options',
			BWS_SETTINGS_PAGE_SLUG,
			[ $this, 'render_settings_page' ]
		);
	}

	private function checkbox( $key, $label, $desc = '' ) {
		printf(
			'<label class="bws-field"><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> <span class="bws-field-label">%4$s</span>%5$s</label>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			checked( 1, (int) $this->get( $key, 0 ), false ),
			esc_html( $label ),
			$desc ? '<span class="bws-field-desc">' . esc_html( $desc ) . '</span>' : ''
		);
	}

	private function text( $key, $label, $placeholder = '' ) {
		printf(
			'<label class="bws-field" for="%1$s_%2$s"><span class="bws-field-label">%3$s</span><input type="text" class="regular-text" id="%1$s_%2$s" name="%1$s[%2$s]" value="%4$s" placeholder="%5$s"></label>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) $this->get( $key, '' ) ),
			esc_attr( $placeholder )
		);
	}

	private function textarea( $key, $label, $rows = 4, $desc = '' ) {
		printf(
			'<label class="bws-field" for="%1$s_%2$s"><span class="bws-field-label">%3$s</span><textarea class="large-text code" rows="%5$d" id="%1$s_%2$s" name="%1$s[%2$s]">%4$s</textarea>%6$s</label>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_textarea( (string) $this->get( $key, '' ) ),
			(int) $rows,
			$desc ? '<span class="bws-field-desc">' . esc_html( $desc ) . '</span>' : ''
		);
	}

	private function number( $key, $label, $min = 0, $max = 999 ) {
		printf(
			'<label class="bws-field" for="%1$s_%2$s"><span class="bws-field-label">%3$s</span><input type="number" min="%6$d" max="%7$d" class="small-text" id="%1$s_%2$s" name="%1$s[%2$s]" value="%4$s"></label>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) $this->get( $key, '' ) ),
			esc_attr( (string) $this->get( $key, '' ) ),
			(int) $min,
			(int) $max
		);
	}

	private function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
	}

	public function render_settings_page() {
		if ( ! $this->can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', BWS_TEXT_DOMAIN ) );
		}

		$tabs = [
			'dashboard'    => __( 'Dashboard', BWS_TEXT_DOMAIN ),
			'updates'      => __( 'Updates', BWS_TEXT_DOMAIN ),
			'restrictions' => __( 'Restrictions', BWS_TEXT_DOMAIN ),
			'labels'       => __( 'Labels', BWS_TEXT_DOMAIN ),
			'branding'     => __( 'Branding', BWS_TEXT_DOMAIN ),
			'support'      => __( 'Support', BWS_TEXT_DOMAIN ),
			'login'        => __( 'Login', BWS_TEXT_DOMAIN ),
			'hardening'    => __( 'Hardening & Performance', BWS_TEXT_DOMAIN ),
			'whitelabel'   => __( 'White-label', BWS_TEXT_DOMAIN ),
		];
		?>
		<div class="wrap bws-settings-wrap">
			<h1><?php echo esc_html__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ); ?></h1>
			<p><?php echo esc_html__( 'Client admin cleanup, branding, login customization, performance hardening, and support tools for managed WordPress sites.', BWS_TEXT_DOMAIN ); ?></p>

			<nav class="bws-tabs" aria-label="<?php echo esc_attr__( 'Settings sections', BWS_TEXT_DOMAIN ); ?>">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a href="#<?php echo esc_attr( $slug ); ?>" class="bws-tab" data-bws-tab="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="options.php">
				<?php settings_fields( 'bws_settings_group' ); ?>

				<div class="bws-panels">

					<section class="bws-panel" data-bws-panel="dashboard">
						<h2><?php esc_html_e( 'Dashboard Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'dashboard_remove_quick_draft', 'Remove Quick Draft' ); ?>
							<?php $this->checkbox( 'dashboard_remove_events_news', 'Remove WordPress Events and News' ); ?>
							<?php $this->checkbox( 'dashboard_remove_activity', 'Remove Activity' ); ?>
							<?php $this->checkbox( 'dashboard_remove_at_a_glance', 'Remove At a Glance' ); ?>
							<?php $this->checkbox( 'dashboard_remove_site_health', 'Remove Site Health' ); ?>
							<?php $this->checkbox( 'dashboard_remove_welcome_panel', 'Remove Welcome Panel' ); ?>
							<?php $this->checkbox( 'dashboard_remove_wp_mail_smtp_widget', 'Remove WP Mail SMTP dashboard widget', 'Hides WP Mail SMTP Reports widget on the Dashboard.' ); ?>

							<?php if ( $this->is_elementor_active() ) : ?>
								<hr />
								<h3><?php esc_html_e( 'Elementor', BWS_TEXT_DOMAIN ); ?></h3>
								<?php $this->checkbox( 'dashboard_remove_elementor_overview', 'Remove Elementor Overview widget', 'Dashboard widget ID: e-dashboard-overview' ); ?>
								<?php $this->checkbox( 'dashboard_remove_elementor_ally', 'Remove Elementor Accessibility widget', 'Dashboard widget ID: e-dashboard-ally' ); ?>
							<?php endif; ?>

							<hr />
							<?php $this->textarea( 'dashboard_remove_custom_widget_ids', 'Custom Dashboard Widget IDs to Remove (one per line)', 4, 'Tip: paste widget IDs like e-dashboard-ally, qi_addons_for_elementor_dashboard_widget, etc.' ); ?>

							<?php $this->textarea( 'admin_notice_hide_selectors', 'Admin Notice CSS Selectors to Hide (one per line)', 4, 'You can paste a CSS selector (e.g. .notice.notice-info) OR a space-delimited class list (e.g. venture_admin_notice notice notice-info).' ); ?>
						</div>

						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="updates">
						<h2><?php esc_html_e( 'Update UI Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'updates_hide_nag', 'Hide update nag' ); ?>
							<?php $this->checkbox( 'updates_hide_plugin_rows', 'Hide plugin update rows/messages' ); ?>
							<?php $this->checkbox( 'updates_hide_badges', 'Hide update badges/counts' ); ?>
							<?php $this->checkbox( 'updates_hide_auto_update_column', 'Hide auto-update UI + disable auto-updates', 'Recommended when managing updates via WP Remote.' ); ?>
							<?php $this->checkbox( 'updates_hide_plugin_update_tab', 'Hide Plugins “Update Available” tab' ); ?>
							<?php $this->checkbox( 'restrict_updates_page', 'Hide/redirect Updates screen' ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="restrictions">
						<h2><?php esc_html_e( 'Admin Restrictions & Menu Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'restrict_plugin_editor', 'Hide Plugin File Editor' ); ?>
							<?php $this->checkbox( 'restrict_theme_editor', 'Hide Theme File Editor' ); ?>
							<?php $this->checkbox( 'restrict_plugin_install', 'Hide Plugin Add New / Upload' ); ?>
							<?php $this->checkbox( 'restrict_plugin_delete', 'Hide Plugin Delete links' ); ?>
							<?php $this->checkbox( 'restrict_theme_install', 'Hide Theme Add New / Upload' ); ?>
							<?php $this->checkbox( 'restrict_theme_switch', 'Hide Theme switching / Theme pages' ); ?>
							<hr />
							<?php $this->checkbox( 'menu_hide_tools', 'Hide Tools menu' ); ?>
							<?php $this->checkbox( 'menu_hide_comments', 'Hide Comments menu' ); ?>
							<?php $this->checkbox( 'menu_hide_settings', 'Hide Settings menu' ); ?>
							<?php $this->checkbox( 'menu_hide_users', 'Hide Users menu' ); ?>
							<?php $this->checkbox( 'menu_hide_plugins', 'Hide Plugins menu' ); ?>
							<?php $this->checkbox( 'menu_hide_appearance', 'Hide Appearance menu' ); ?>
							<hr />
							<?php $this->textarea( 'menu_hide_custom_slugs', 'Hide Top-Level Menu Slugs (one per line)', 4 ); ?>
							<?php $this->textarea( 'submenu_hide_custom_slugs', 'Hide Submenu Slugs (format: parent_slug|submenu_slug, one per line)', 4 ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="labels">
						<h2><?php esc_html_e( 'Label Renaming', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->text( 'label_posts', 'Rename “Posts” (optional)' ); ?>
							<?php $this->text( 'label_pages', 'Rename “Pages” (optional)' ); ?>
							<?php $this->text( 'label_media', 'Rename “Media” (optional)' ); ?>
							<?php $this->textarea( 'label_cpt_map', 'CPT Menu Label Overrides (post_type|Menu Label|Add New Label)', 6 ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="branding">
						<h2><?php esc_html_e( 'Branding', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'branding_footer_enabled', 'Replace admin footer text' ); ?>
							<?php $this->text( 'branding_footer_text', 'Footer Text' ); ?>
							<?php $this->text( 'branding_footer_version_text', 'Footer Version Text (optional)' ); ?>
							<hr />
							<?php $this->text( 'branding_support_logo_url', 'Support Logo URL (optional)' ); ?>
							<?php $this->text( 'branding_support_widget_intro', 'Support Widget Intro Text' ); ?>
							<?php $this->text( 'branding_support_page_intro', 'Support Page Intro Text' ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="support">
						<h2><?php esc_html_e( 'Website Support', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'support_widget_enabled', 'Enable Dashboard Widget' ); ?>
							<?php $this->checkbox( 'support_page_enabled', 'Enable Support Sidebar Page' ); ?>
							<?php $this->text( 'support_page_label', 'Support Sidebar Label', 'Website Support' ); ?>
							<?php $this->text( 'support_email', 'Support Email', 'support@bestwebsite.com' ); ?>
							<?php $this->textarea( 'support_topic_options', 'Topics (one per line)', 6 ); ?>
							<?php $this->text( 'support_success_message', 'Success Message' ); ?>
							<?php $this->textarea( 'support_instructions_text', 'Instructions', 4 ); ?>
							<?php $this->checkbox( 'support_include_diagnostics', 'Include diagnostics metadata in emails' ); ?>
							<hr />
							<?php $this->checkbox( 'support_force_from_domain', 'Force outgoing “From” email to site domain (optional)', 'If WP Mail SMTP is active, this will be skipped.' ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="login">
						<h2><?php esc_html_e( 'Login Branding', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'login_branding_enabled', 'Enable custom login branding' ); ?>
							<?php $this->text( 'login_logo_url', 'Login Logo URL (optional)' ); ?>
							<?php $this->text( 'login_logo_link_url', 'Login Logo Link URL' ); ?>
							<?php $this->text( 'login_logo_title', 'Login Logo Title Text' ); ?>
							<?php $this->text( 'login_bg_color', 'Login Background Color' ); ?>
							<?php $this->text( 'login_button_color', 'Login Button Color' ); ?>
							<?php $this->textarea( 'login_help_text', 'Login Help Text (shown below form)', 3 ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="hardening">
						<h2><?php esc_html_e( 'Hardening & Performance', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<h3><?php esc_html_e( 'Comments', BWS_TEXT_DOMAIN ); ?></h3>
							<?php $this->checkbox( 'comments_disable_sitewide', 'Disable comments site-wide', 'Closes comments/pingbacks and removes comment support from post types.' ); ?>
							<?php $this->checkbox( 'comments_disable_feeds', 'Disable comment feeds (optional)' ); ?>

							<hr />
							<h3><?php esc_html_e( 'Security', BWS_TEXT_DOMAIN ); ?></h3>
							<?php $this->checkbox( 'security_force_ssl_admin', 'Force SSL for wp-admin when site uses HTTPS', 'Auto-enforces only if home_url() begins with https://.' ); ?>
							<?php $this->checkbox( 'security_disable_xmlrpc_pingbacks', 'Disable XML-RPC pingbacks' ); ?>
							<?php $this->checkbox( 'security_disable_application_passwords', 'Disable Application Passwords' ); ?>
							<?php $this->checkbox( 'security_block_author_enum', 'Block author enumeration (?author=)' ); ?>
							<?php $this->checkbox( 'security_remove_generator', 'Remove WordPress generator/version output' ); ?>

							<hr />
							<h3><?php esc_html_e( 'Performance', BWS_TEXT_DOMAIN ); ?></h3>
							<?php $this->checkbox( 'perf_disable_emojis', 'Disable emoji scripts/styles' ); ?>
							<?php $this->checkbox( 'perf_disable_oembed', 'Disable oEmbed discovery + wp-embed.js' ); ?>
							<?php $this->checkbox( 'perf_disable_dashicons_visitors', 'Disable Dashicons for non-logged-in visitors' ); ?>
							<?php $this->checkbox( 'perf_limit_revisions_enabled', 'Limit post revisions' ); ?>
							<?php $this->number( 'perf_limit_revisions_count', 'Max revisions to keep', 0, 100 ); ?>

							<hr />
							<h3><?php esc_html_e( 'SEO', BWS_TEXT_DOMAIN ); ?></h3>
							<?php $this->checkbox( 'seo_disable_attachment_pages', 'Disable attachment pages (redirect to media file)', 'Recommended on most sites to avoid thin attachment pages.' ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

					<section class="bws-panel" data-bws-panel="whitelabel">
						<h2><?php esc_html_e( 'Plugin Visibility / White-label', BWS_TEXT_DOMAIN ); ?></h2>
						<div class="bws-card">
							<?php $this->checkbox( 'plugin_whitelabel_enabled', 'Enable white-label behavior' ); ?>
							<?php $this->checkbox( 'plugin_hide_settings_menu', 'Hide settings page in admin menu (direct URL only)' ); ?>
							<?php $this->checkbox( 'plugin_hide_from_plugins_list', 'Hide this plugin from Plugins list (advanced)' ); ?>
							<?php $this->checkbox( 'plugin_hide_plugin_ui_badges', 'Hide this plugin’s update row/badges when possible' ); ?>
							<?php $this->checkbox( 'plugin_hide_support_menu_from_adminbar', 'Hide support page from admin bar shortcuts' ); ?>
						</div>
						<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
					</section>

				</div>
			</form>
		</div>
		<?php
	}
}
