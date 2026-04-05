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
			'dashboard_remove_wp_mail_smtp_reports_widget_lite' => 0,
			'dashboard_remove_elementor_overview' => 0,
			'dashboard_remove_elementor_ally' => 0,
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
	$input = is_array( $input ) ? $input : [];

	// Identify which tab was submitted so we only update fields from that tab.
	$active_tab = isset( $input['bws_active_tab'] ) ? sanitize_key( (string) $input['bws_active_tab'] ) : 'dashboard';

	// Load existing saved settings so saving one tab doesn't wipe other tabs.
	$existing = get_option( BWS_OPTION_KEY, [] );
	$existing = is_array( $existing ) ? $existing : [];

	// Never persist this helper value.
	unset( $input['bws_active_tab'] );

	$tab_fields = [
		'dashboard' => [
			'checkbox' => [
				'dashboard_remove_quick_draft',
				'dashboard_remove_wordpress_events_news',
				'dashboard_remove_activity',
				'dashboard_remove_at_a_glance',
				'dashboard_remove_site_health',
				'dashboard_remove_welcome_panel',
				'dashboard_remove_wp_mail_smtp_reports_widget_lite',
				'dashboard_remove_elementor_overview',
				'dashboard_remove_elementor_ally',
			],
			'textareas' => [
				'dashboard_remove_custom_widget_ids',
				'admin_notice_hide_selectors',
			],
		],
		'updates' => [
			'checkbox' => [
				'updates_hide_update_nag',
				'updates_hide_plugin_update_rows',
				'updates_hide_update_badges_counts',
				'updates_hide_plugin_auto_update_column_links',
				'updates_hide_plugins_update_available_tab',
				'updates_hide_redirect_updates_screen',
			],
		],
		'restrictions' => [
			'checkbox' => [
				'restrictions_hide_plugin_editor',
				'restrictions_hide_theme_editor',
				'restrictions_hide_plugin_add_new_upload',
				'restrictions_hide_plugin_delete_links',
				'restrictions_hide_theme_add_new_upload',
				'restrictions_hide_theme_switching_theme_pages',
				'restrictions_hide_updates_screen',
				'restrictions_hide_tools_menu',
				'restrictions_hide_comments_menu',
				'restrictions_hide_settings_menu',
				'restrictions_hide_users_menu',
				'restrictions_hide_plugins_menu',
				'restrictions_hide_appearance_menu',
			],
			'textareas' => [
				'restrictions_hide_top_level_menu_slugs',
				'restrictions_hide_submenu_slugs',
			],
		],
		'labels' => [
			'texts' => [
				'labels_rename_posts',
				'labels_rename_pages',
				'labels_rename_media',
			],
			'textareas' => [
				'labels_cpt_menu_overrides',
			],
		],
		'branding' => [
			'checkbox' => [
				'branding_enable_admin_footer_text',
			],
			'texts' => [
				'branding_admin_footer_text',
				'branding_admin_footer_version_text',
				'branding_support_logo_url',
				'branding_support_widget_intro_text',
				'branding_support_page_intro_text',
			],
		],
		'support' => [
			'checkbox' => [
				'support_enable_dashboard_widget',
				'support_enable_support_sidebar_page',
				'support_include_diagnostics',
			],
			'texts' => [
				'support_sidebar_label',
				'support_email',
				'support_success_message',
			],
			'textareas' => [
				'support_topics',
				'support_instructions',
			],
		],
		'login' => [
			'checkbox' => [
				'login_enable_custom_login_branding',
			],
			'texts' => [
				'login_logo_url',
				'login_logo_link_url',
				'login_logo_title_text',
				'login_background_color',
				'login_button_color',
			],
			'textareas' => [
				'login_help_text',
			],
		],
		'white-label' => [
			'checkbox' => [
				'whitelabel_enable_whitelabel_features',
				'whitelabel_hide_settings_page',
				'whitelabel_hide_plugin_from_plugins_list',
				'whitelabel_hide_update_ui',
				'whitelabel_hide_support_page',
			],
		],
	];

	$fields = isset( $tab_fields[ $active_tab ] ) ? $tab_fields[ $active_tab ] : $tab_fields['dashboard'];

	$output = $existing;

	$sanitize_text = static function( $value ) {
		return sanitize_text_field( (string) $value );
	};
	$sanitize_textarea = static function( $value ) {
		return sanitize_textarea_field( (string) $value );
	};

	// Checkboxes: explicitly set 1/0 for keys in this tab.
	if ( ! empty( $fields['checkbox'] ) ) {
		foreach ( $fields['checkbox'] as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? 1 : 0;
		}
	}

	// Text inputs.
	if ( ! empty( $fields['texts'] ) ) {
		foreach ( $fields['texts'] as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$output[ $key ] = $sanitize_text( $input[ $key ] );
			}
		}
	}

	// Textareas.
	if ( ! empty( $fields['textareas'] ) ) {
		foreach ( $fields['textareas'] as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$output[ $key ] = $sanitize_textarea( $input[ $key ] );
			}
		}
	}

	return $output;
}

	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	public function register_settings_page() {
		if ( ! $this->can_manage() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

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


public function enqueue_admin_assets( $hook_suffix ) {
	// Only load on our settings screen.
	if ( 'settings_page_' . BWS_SETTINGS_PAGE_SLUG !== $hook_suffix ) {
		return;
	}

	$css = BWS_PLUGIN_URL . 'assets/admin-settings.css';
	wp_enqueue_style( 'bws-admin-settings', $css, [], BWS_VERSION );

	$js = BWS_PLUGIN_URL . 'assets/admin-settings.js';
	wp_enqueue_script( 'bws-admin-settings', $js, [ 'jquery' ], BWS_VERSION, true );
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
		$base_url = admin_url( 'options-general.php?page=' . BWS_SETTINGS_PAGE_SLUG );

		?>
		<div class="wrap bws-settings-wrap" data-bws-active-tab="<?php echo esc_attr( $active_tab ); ?>">
			<h1><?php echo esc_html__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ); ?></h1>
			<p><?php echo esc_html__( 'Client admin cleanup, branding, login customization, and support tools for managed WordPress sites.', BWS_TEXT_DOMAIN ); ?></p>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=' . BWS_SUPPORT_PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Open Website Support Page', BWS_TEXT_DOMAIN ); ?></a>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'options-general.php?page=' . BWS_SETTINGS_PAGE_SLUG ) ); ?>"><?php echo esc_html__( 'Refresh', BWS_TEXT_DOMAIN ); ?></a>
			</p>

			<h2 class="nav-tab-wrapper bws-settings-tabs" role="tablist">
				<?php foreach ( $tabs as $tab_key => $label ) :
					$url   = add_query_arg( 'tab', $tab_key, $base_url );
					$class = 'nav-tab' . ( $tab_key === $active_tab ? ' nav-tab-active' : '' );
					?>
					<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>" role="tab" aria-selected="<?php echo $tab_key === $active_tab ? 'true' : 'false'; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php">
				<?php settings_fields( 'bws_settings_group' ); ?>
				<div class="bws-tab-panel" data-bws-tab="<?php echo esc_attr( $active_tab ); ?>">
					<?php
					switch ( $active_tab ) {
						case 'updates':
							?>
							<h2><?php esc_html_e( 'Update UI Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->checkbox( 'updates_hide_nag', 'Hide update nag' ); ?></p>
							<p><?php $this->checkbox( 'updates_hide_plugin_rows', 'Hide plugin update rows/messages' ); ?></p>
							<p><?php $this->checkbox( 'updates_hide_badges', 'Hide update badges/counts' ); ?></p>
							<p><?php $this->checkbox( 'updates_hide_auto_update_column', 'Hide plugin auto-update column/links' ); ?></p>
							<p><?php $this->checkbox( 'updates_hide_plugin_update_tab', 'Hide Plugins “Update Available” tab' ); ?></p>
							<p><?php $this->checkbox( 'restrict_updates_page', 'Hide/redirect Updates screen' ); ?></p>
							<p><?php $this->textarea( 'admin_notice_hide_selectors', 'Admin Notice CSS Selectors to Hide (one per line)', 4 ); ?></p>
							<?php
							break;

						case 'restrictions':
							?>
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
							<?php
							break;

						case 'labels':
							?>
							<h2><?php esc_html_e( 'Label Renaming', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->text( 'label_posts', 'Rename “Posts” (optional)' ); ?></p>
							<p><?php $this->text( 'label_pages', 'Rename “Pages” (optional)' ); ?></p>
							<p><?php $this->text( 'label_media', 'Rename “Media” (optional)' ); ?></p>
							<p><?php $this->textarea( 'label_cpt_map', 'CPT Menu Label Overrides (post_type|Menu Label|Add New Label)', 6 ); ?></p>
							<?php
							break;

						case 'branding':
							?>
							<h2><?php esc_html_e( 'Branding', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->checkbox( 'branding_footer_enabled', 'Replace admin footer text' ); ?></p>
							<p><?php $this->text( 'branding_footer_text', 'Footer Text' ); ?></p>
							<p><?php $this->text( 'branding_footer_version_text', 'Footer Version Text (optional)' ); ?></p>
							<p><?php $this->text( 'branding_support_logo_url', 'Support Logo URL (optional)' ); ?></p>
							<p><?php $this->text( 'branding_support_widget_intro', 'Support Widget Intro Text' ); ?></p>
							<p><?php $this->text( 'branding_support_page_intro', 'Support Page Intro Text' ); ?></p>
							<?php
							break;

						case 'support':
							?>
							<h2><?php esc_html_e( 'Website Support', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->checkbox( 'support_widget_enabled', 'Enable Dashboard Widget' ); ?></p>
							<p><?php $this->checkbox( 'support_page_enabled', 'Enable Support Sidebar Page' ); ?></p>
							<p><?php $this->text( 'support_page_label', 'Support Sidebar Label', 'Website Support' ); ?></p>
							<p><?php $this->text( 'support_email', 'Support Email', 'support@bestwebsite.com' ); ?></p>
							<p><?php $this->textarea( 'support_topic_options', 'Topics (one per line)', 6 ); ?></p>
							<p><?php $this->text( 'support_success_message', 'Success Message' ); ?></p>
							<p><?php $this->textarea( 'support_instructions_text', 'Instructions', 4 ); ?></p>
							<p><?php $this->checkbox( 'support_include_diagnostics', 'Include diagnostics metadata in emails' ); ?></p>
							<?php
							break;

						case 'login':
							?>
							<h2><?php esc_html_e( 'Login Branding', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->checkbox( 'login_branding_enabled', 'Enable custom login branding' ); ?></p>
							<p><?php $this->text( 'login_logo_url', 'Login Logo URL (optional)' ); ?></p>
							<p><?php $this->text( 'login_logo_link_url', 'Login Logo Link URL' ); ?></p>
							<p><?php $this->text( 'login_logo_title', 'Login Logo Title Text' ); ?></p>
							<p><?php $this->color( 'login_bg_color', 'Login Background Color' ); ?></p>
							<p><?php $this->color( 'login_button_color', 'Login Button Color' ); ?></p>
							<p><?php $this->textarea( 'login_help_text', 'Login Help Text (shown below form)', 3 ); ?></p>
							<?php
							break;

						case 'whitelabel':
							?>
							<h2><?php esc_html_e( 'Plugin Visibility / White-Label', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->checkbox( 'plugin_whitelabel_enabled', 'Enable white-label behavior' ); ?></p>
							<p><?php $this->checkbox( 'plugin_show_settings_menu', 'Hide settings page in admin menu (direct URL only)' ); ?></p>
							<p><?php $this->checkbox( 'plugin_hide_from_plugins_list', 'Hide this plugin from Plugins list (advanced; test carefully)' ); ?></p>
							<p><?php $this->checkbox( 'plugin_hide_plugin_ui_badges', 'Hide this plugin’s update row/badges when possible' ); ?></p>
							<p><?php $this->checkbox( 'plugin_hide_support_menu_from_adminbar', 'Hide support page from admin bar shortcuts (future-safe)' ); ?></p>
							<?php
							break;

						case 'dashboard':
						default:
							?>
							<h2><?php esc_html_e( 'Dashboard Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
							<p><?php $this->checkbox( 'dashboard_remove_quick_draft', 'Remove Quick Draft' ); ?></p>
							<p><?php $this->checkbox( 'dashboard_remove_events_news', 'Remove WordPress Events and News' ); ?></p>
							<p><?php $this->checkbox( 'dashboard_remove_activity', 'Remove Activity' ); ?></p>
							<p><?php $this->checkbox( 'dashboard_remove_at_a_glance', 'Remove At a Glance' ); ?></p>
							<p><?php $this->checkbox( 'dashboard_remove_site_health', 'Remove Site Health' ); ?></p>
							<p><?php $this->checkbox( 'dashboard_remove_welcome_panel', 'Remove Welcome Panel' ); ?></p>

							<p><?php $this->checkbox( 'dashboard_remove_wp_mail_smtp_reports_widget_lite', 'Remove WP Mail SMTP dashboard widget' ); ?></p>
							<?php if ( class_exists( '\\Elementor\\Plugin' ) ) : ?>
								<p><?php $this->checkbox( 'dashboard_remove_elementor_overview', 'Remove Elementor Overview dashboard widget' ); ?></p>
								<p><?php $this->checkbox( 'dashboard_remove_elementor_ally', 'Remove Elementor Accessibility dashboard widget' ); ?></p>
							<?php endif; ?>

							<?php $this->checkbox( 'dashboard_remove_wp_mail_smtp_widget', __( 'Remove WP Mail SMTP dashboard widget', BWS_TEXT_DOMAIN ) ); ?>
							<?php if ( class_exists( '\\Elementor\\Plugin' ) ) : ?>
								<?php $this->checkbox( 'dashboard_remove_elementor_overview_widget', __( 'Remove Elementor Overview dashboard widget', BWS_TEXT_DOMAIN ) ); ?>
								<?php $this->checkbox( 'dashboard_remove_elementor_accessibility_widget', __( 'Remove Elementor Accessibility dashboard widget', BWS_TEXT_DOMAIN ) ); ?>
							<?php endif; ?>

							<p><?php $this->textarea( 'dashboard_remove_custom_widget_ids', 'Custom Dashboard Widget IDs to Remove (one per line)', 4 ); ?></p>
							<p><?php $this->textarea( 'admin_notice_hide_selectors', 'Admin Notice CSS Selectors to Hide (one per line)' ); ?></p>

							<?php
							break;
					}
					?>
				</div>

				<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
			</form>
		</div>
		<?php
	}
}
