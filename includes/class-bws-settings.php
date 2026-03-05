<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Settings {
	public function get_defaults() {
		return [
			'dashboard_remove_quick_draft'   => 1,
			'dashboard_remove_events_news'   => 1,
			'dashboard_remove_activity'      => 1,
			'dashboard_remove_at_a_glance'   => 1,
			'dashboard_remove_site_health'   => 1,
			'dashboard_remove_welcome_panel' => 0,
			'dashboard_remove_custom_widget_ids' => '',

			'updates_hide_nag'                => 1,
			'updates_hide_plugin_rows'        => 1,
			'updates_hide_badges'             => 1,
			'updates_hide_auto_update_column' => 1,
			'updates_hide_plugin_update_tab'  => 1,

			'branding_footer_enabled'         => 1,
			'branding_footer_text'            => 'Managed by Best Website • support@bestwebsite.com',
			'branding_footer_version_text'    => '',
			'branding_support_logo_url'       => '',
			'branding_support_widget_intro'   => 'Managed Website Support by Best Website',
			'branding_support_page_intro'     => 'Use this form to contact Best Website for support, changes, or questions about your website.',

			'support_widget_enabled'          => 1,
			'support_page_enabled'            => 1,
			'support_page_label'              => 'Website Support',
			'support_email'                   => 'support@bestwebsite.com',
			'support_topic_options'           => "Technical Support\nContent Update Request\nSEO / Marketing Question\nWebsite Change Request\nOther",
			'support_success_message'         => 'Thanks! Your message has been sent to Best Website Support.',
			'support_instructions_text'       => 'Please share as much detail as possible, including page URLs and what you expected to happen.',
			'support_include_diagnostics'     => 1,

			'plugin_show_settings_menu'       => 0,
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
		$settings = $this->get_all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
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
		$output   = [];
		$input    = is_array( $input ) ? $input : [];

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
			'branding_footer_enabled',
			'support_widget_enabled',
			'support_page_enabled',
			'support_include_diagnostics',
			'plugin_show_settings_menu',
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
			'support_instructions_text',
		];

		foreach ( $text_keys as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : ( $defaults[ $key ] ?? '' );
		}

		$output['branding_support_logo_url'] = isset( $input['branding_support_logo_url'] ) ? esc_url_raw( $input['branding_support_logo_url'] ) : '';
		$output['support_email']             = isset( $input['support_email'] ) ? sanitize_email( $input['support_email'] ) : $defaults['support_email'];

		$textarea_keys = [
			'dashboard_remove_custom_widget_ids',
			'support_topic_options',
		];

		foreach ( $textarea_keys as $key ) {
			$output[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : ( $defaults[ $key ] ?? '' );
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

		if ( $this->get( 'plugin_show_settings_menu', 0 ) ) {
			add_submenu_page(
				'options-general.php',
				__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ),
				__( 'Best Website Support', BWS_TEXT_DOMAIN ),
				'manage_options',
				BWS_SETTINGS_PAGE_SLUG,
				[ $this, 'render_settings_page' ]
			);
		} else {
			add_submenu_page(
				null,
				__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ),
				__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ),
				'manage_options',
				BWS_SETTINGS_PAGE_SLUG,
				[ $this, 'render_settings_page' ]
			);
		}
	}

	private function checkbox( $key, $label ) {
		$checked = ! empty( $this->get( $key ) ) ? 'checked' : '';
		printf(
			'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			$checked,
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

	private function textarea( $key, $label, $rows = 5 ) {
		printf(
			'<label for="%1$s_%2$s"><strong>%3$s</strong></label><br><textarea class="large-text" rows="%6$d" id="%1$s_%2$s" name="%1$s[%2$s]">%4$s</textarea>',
			esc_attr( BWS_OPTION_KEY ),
			esc_attr( $key ),
			esc_html( $label ),
			esc_textarea( (string) $this->get( $key, '' ) ),
			'',
			(int) $rows
		);
	}

	public function render_settings_page() {
		if ( ! $this->can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', BWS_TEXT_DOMAIN ) );
		}

		$settings_url = admin_url( 'admin.php?page=' . BWS_SETTINGS_PAGE_SLUG );
		$support_url  = admin_url( 'admin.php?page=' . BWS_SUPPORT_PAGE_SLUG );
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Best Website Support Settings', BWS_TEXT_DOMAIN ); ?></h1>
			<p><?php echo esc_html__( 'Client admin cleanup, branding, and support tools for managed WordPress sites.', BWS_TEXT_DOMAIN ); ?></p>

			<p>
				<a class="button" href="<?php echo esc_url( $support_url ); ?>"><?php echo esc_html__( 'Open Website Support Page', BWS_TEXT_DOMAIN ); ?></a>
				<a class="button button-secondary" href="<?php echo esc_url( $settings_url ); ?>"><?php echo esc_html__( 'Refresh Settings', BWS_TEXT_DOMAIN ); ?></a>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'bws_settings_group' ); ?>

				<h2><?php echo esc_html__( 'Dashboard Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'dashboard_remove_quick_draft', 'Remove Quick Draft' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_events_news', 'Remove WordPress Events and News' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_activity', 'Remove Activity' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_at_a_glance', 'Remove At a Glance' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_site_health', 'Remove Site Health widget' ); ?></p>
				<p><?php $this->checkbox( 'dashboard_remove_welcome_panel', 'Remove Welcome Panel' ); ?></p>
				<p><?php $this->textarea( 'dashboard_remove_custom_widget_ids', 'Custom Dashboard Widget IDs to Remove (one per line)', 4 ); ?></p>

				<hr>

				<h2><?php echo esc_html__( 'Update UI Cleanup', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'updates_hide_nag', 'Hide update nag' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_plugin_rows', 'Hide plugin update rows/messages' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_badges', 'Hide update badges/counts' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_auto_update_column', 'Hide plugin auto-update column/links' ); ?></p>
				<p><?php $this->checkbox( 'updates_hide_plugin_update_tab', 'Hide Plugins "Update Available" tab' ); ?></p>

				<hr>

				<h2><?php echo esc_html__( 'Branding', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'branding_footer_enabled', 'Replace admin footer text' ); ?></p>
				<p><?php $this->text( 'branding_footer_text', 'Footer Text' ); ?></p>
				<p><?php $this->text( 'branding_footer_version_text', 'Footer Version Text (optional)' ); ?></p>
				<p><?php $this->text( 'branding_support_logo_url', 'Support Logo URL (optional)' ); ?></p>
				<p><?php $this->text( 'branding_support_widget_intro', 'Support Widget Intro Text' ); ?></p>
				<p><?php $this->text( 'branding_support_page_intro', 'Support Page Intro Text' ); ?></p>

				<hr>

				<h2><?php echo esc_html__( 'Support', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'support_widget_enabled', 'Enable dashboard support widget' ); ?></p>
				<p><?php $this->checkbox( 'support_page_enabled', 'Enable Website Support sidebar page' ); ?></p>
				<p><?php $this->text( 'support_page_label', 'Sidebar Page Label', 'Website Support' ); ?></p>
				<p><?php $this->text( 'support_email', 'Support Email', 'support@bestwebsite.com' ); ?></p>
				<p><?php $this->textarea( 'support_topic_options', 'Support Topics (one per line)', 6 ); ?></p>
				<p><?php $this->text( 'support_success_message', 'Success Message' ); ?></p>
				<p><?php $this->textarea( 'support_instructions_text', 'Support Instructions Text', 4 ); ?></p>
				<p><?php $this->checkbox( 'support_include_diagnostics', 'Include diagnostics metadata in support emails' ); ?></p>

				<hr>

				<h2><?php echo esc_html__( 'Plugin Visibility', BWS_TEXT_DOMAIN ); ?></h2>
				<p><?php $this->checkbox( 'plugin_show_settings_menu', 'Show settings page in admin menu (otherwise direct URL only)' ); ?></p>

				<?php submit_button( __( 'Save Settings', BWS_TEXT_DOMAIN ) ); ?>
			</form>
		</div>
		<?php
	}
}
