<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Support {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_action( 'admin_menu', [ $this, 'register_support_page' ], 50 );
		add_action( 'wp_dashboard_setup', [ $this, 'register_dashboard_widget' ], 20 );
		add_action( 'admin_post_bws_submit_support_request', [ $this, 'handle_support_submission' ] );
		add_action( 'admin_head', [ $this, 'admin_support_styles' ] );
	}

	public function admin_support_styles() {
		if ( ! is_admin() ) {
			return;
		}
		$screen           = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$screen_id        = $screen && isset( $screen->id ) ? (string) $screen->id : '';
		$is_support_screen = in_array( $screen_id, [ 'dashboard', 'toplevel_page_' . BWS_SUPPORT_PAGE_SLUG ], true );
		if ( ! $is_support_screen ) {
			return;
		}
		echo '<style>
		#bws_website_support_widget .inside{padding:0;margin:0;background:#fff}
		.bws-support-shell{border:1px solid #dcdcde;border-radius:10px;background:#fff;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04)}
		.bws-support-header{padding:14px 16px;background:linear-gradient(180deg,#ffffff,#f6f7f7);border-bottom:1px solid #dcdcde}
		.bws-support-brand{display:flex;gap:12px;align-items:center}
		.bws-support-brand img{max-height:42px;width:auto;display:block}
		.bws-support-kicker{margin:0;font-size:12px;color:#646970;text-transform:uppercase;letter-spacing:.04em}
		.bws-support-title{margin:2px 0 0;font-size:14px;font-weight:600;color:#1d2327}
		.bws-support-email{margin:6px 0 0;font-size:13px;color:#50575e}
		.bws-support-email a{text-decoration:none}
		.bws-support-body{padding:16px}
		.bws-support-help{margin:0 0 14px;color:#50575e;line-height:1.45}
		.bws-support-grid{display:grid;grid-template-columns:110px minmax(0,1fr);gap:10px 12px;align-items:start}
		.bws-support-grid label{font-weight:600;color:#1d2327;padding-top:8px}
		.bws-support-grid input[type=text],.bws-support-grid input[type=email],.bws-support-grid select,.bws-support-grid textarea{width:100%;max-width:100%}
		.bws-support-grid textarea{min-height:110px}
		.bws-support-actions{margin-top:12px;padding-top:10px;border-top:1px solid #f0f0f1}
		#bws_website_support_widget .bws-support-grid{grid-template-columns:100px minmax(0,1fr)}
		@media (max-width:782px){.bws-support-grid{grid-template-columns:1fr}.bws-support-grid label{padding-top:0}}
		</style>';
	}

	public function register_support_page() {
		if ( ! is_admin() || ! current_user_can( 'read' ) || ! $this->settings->get( 'support_page_enabled', 1 ) ) {
			return;
		}
		$label = (string) $this->settings->get( 'support_page_label', 'Website Support' );
		add_menu_page(
			__( 'Website Support', BWS_TEXT_DOMAIN ),
			$label,
			'read',
			BWS_SUPPORT_PAGE_SLUG,
			[ $this, 'render_support_page' ],
			'dashicons-sos',
			3
		);
	}

	public function register_dashboard_widget() {
		if ( ! current_user_can( 'read' ) || ! $this->settings->get( 'support_widget_enabled', 1 ) ) {
			return;
		}
		wp_add_dashboard_widget( 'bws_website_support_widget', __( 'Website Support', BWS_TEXT_DOMAIN ), [ $this, 'render_dashboard_widget' ] );
	}

	public function render_dashboard_widget() {
		$this->render_support_ui( 'dashboard' );
	}

	public function render_support_page() {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', BWS_TEXT_DOMAIN ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'Website Support', BWS_TEXT_DOMAIN ) . '</h1>';
		$this->render_support_ui( 'page' );
		echo '</div>';
	}

	private function get_topics() {
		$raw    = (string) $this->settings->get( 'support_topic_options', '' );
		$topics = preg_split( '/\r\n|\r|\n/', $raw );
		$topics = array_values( array_filter( array_map( 'trim', (array) $topics ) ) );
		return ! empty( $topics ) ? $topics : [ 'Technical Support', 'Content Update Request', 'Other' ];
	}

	private function render_branding_block() {
		$logo_url = (string) $this->settings->get( 'branding_support_logo_url', '' );
		$intro    = (string) $this->settings->get( 'branding_support_widget_intro', 'Managed Website Support by Best Website' );
		$email    = (string) $this->settings->get( 'support_email', 'support@bestwebsite.com' );

		echo '<div class="bws-support-shell">';
		echo '<div class="bws-support-header"><div class="bws-support-brand">';
		if ( $logo_url ) {
			echo '<img src="' . esc_url( $logo_url ) . '" alt="">';
		}
		echo '<div class="bws-support-brandtext">';
		echo '<p class="bws-support-kicker">' . esc_html__( 'Best Website Support', BWS_TEXT_DOMAIN ) . '</p>';
		echo '<p class="bws-support-title">' . esc_html( $intro ) . '</p>';
		echo '<p class="bws-support-email">' . esc_html__( 'Email:', BWS_TEXT_DOMAIN ) . ' <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
		echo '</div></div></div>';
	}

	private function render_support_ui( $context = 'dashboard' ) {
		$status = isset( $_GET['bws_support_status'] ) ? sanitize_key( wp_unslash( $_GET['bws_support_status'] ) ) : '';
		if ( 'success' === $status ) {
			echo '<div class="notice notice-success inline"><p>' . esc_html( (string) $this->settings->get( 'support_success_message', 'Your message has been sent.' ) ) . '</p></div>';
		} elseif ( 'error' === $status ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'There was a problem sending your message. Please try again or email support directly.', BWS_TEXT_DOMAIN ) . '</p></div>';
		}

		$this->render_branding_block();

		if ( 'page' === $context ) {
			$page_intro = (string) $this->settings->get( 'branding_support_page_intro', '' );
			if ( '' !== trim( $page_intro ) ) {
				echo '<p class="bws-support-help">' . esc_html( $page_intro ) . '</p>';
			}
		}

		$instructions = (string) $this->settings->get( 'support_instructions_text', '' );
		if ( '' !== trim( $instructions ) ) {
			echo '<p class="bws-support-help">' . esc_html( $instructions ) . '</p>';
		}

		$current_user = wp_get_current_user();
		echo '<div class="bws-support-body">';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'bws_submit_support_request', 'bws_support_nonce' );
		echo '<input type="hidden" name="action" value="bws_submit_support_request">';
		echo '<div class="bws-support-grid">';

		echo '<label for="bws_support_topic">' . esc_html__( 'Topic', BWS_TEXT_DOMAIN ) . '</label><div><select id="bws_support_topic" name="bws_support_topic" required>';
		foreach ( $this->get_topics() as $topic ) {
			echo '<option value="' . esc_attr( $topic ) . '">' . esc_html( $topic ) . '</option>';
		}
		echo '</select></div>';

		echo '<label for="bws_support_message">' . esc_html__( 'Message', BWS_TEXT_DOMAIN ) . '</label><div><textarea id="bws_support_message" name="bws_support_message" rows="6" class="large-text" required></textarea></div>';

		echo '<label for="bws_support_name">' . esc_html__( 'Your Name', BWS_TEXT_DOMAIN ) . '</label><div><input id="bws_support_name" type="text" name="bws_support_name" class="regular-text" value="' . esc_attr( $current_user->display_name ) . '"></div>';

		echo '<label for="bws_support_email">' . esc_html__( 'Your Email', BWS_TEXT_DOMAIN ) . '</label><div><input id="bws_support_email" type="email" name="bws_support_email" class="regular-text" value="' . esc_attr( $current_user->user_email ) . '"></div>';

		echo '</div>';
		echo '<div class="bws-support-actions">';
		submit_button( __( 'Send Support Request', BWS_TEXT_DOMAIN ), 'primary', 'submit', false );
		echo '</div>';
		echo '</form></div></div>';
	}

	public function handle_support_submission() {
		if ( ! is_admin() || ! current_user_can( 'read' ) ) {
			wp_die( esc_html__( 'You do not have permission to submit this form.', BWS_TEXT_DOMAIN ) );
		}
		check_admin_referer( 'bws_submit_support_request', 'bws_support_nonce' );

		$topic   = isset( $_POST['bws_support_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['bws_support_topic'] ) ) : 'Other';
		$message = isset( $_POST['bws_support_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bws_support_message'] ) ) : '';
		$name    = isset( $_POST['bws_support_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bws_support_name'] ) ) : '';
		$email   = isset( $_POST['bws_support_email'] ) ? sanitize_email( wp_unslash( $_POST['bws_support_email'] ) ) : '';

		$redirect = wp_get_referer() ?: admin_url( 'admin.php?page=' . BWS_SUPPORT_PAGE_SLUG );
		if ( '' === trim( $message ) ) {
			wp_safe_redirect( add_query_arg( 'bws_support_status', 'error', $redirect ) );
			exit;
		}

		$to = (string) $this->settings->get( 'support_email', 'support@bestwebsite.com' );
		if ( ! is_email( $to ) ) {
			wp_safe_redirect( add_query_arg( 'bws_support_status', 'error', $redirect ) );
			exit;
		}

		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$site_url  = home_url();

		// Subject format: [Selected Topic] New Message from Name
		$subject_name = trim( (string) $name );
		if ( '' === $subject_name ) {
			$subject_name = 'Website User';
		}

		$subject = sprintf(
			'[%s] New Message from %s',
			$topic,
			$subject_name
		);

		$lines   = [];
		$lines[] = 'Topic: ' . $topic;
		$lines[] = 'Message:';
		$lines[] = $message;
		$lines[] = '';
		$lines[] = 'Submitted by: ' . ( $name ?: '(not provided)' );
		$lines[] = 'Email: ' . ( $email ?: '(not provided)' );

		if ( $this->settings->get( 'support_include_diagnostics', 1 ) ) {
			$current_user = wp_get_current_user();
			$theme        = wp_get_theme();
			$screen       = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			$lines[] = '';
			$lines[] = '--- Diagnostics ---';
			$lines[] = 'Site Name: ' . $site_name;
			$lines[] = 'Site URL: ' . $site_url;
			$lines[] = 'Admin URL: ' . admin_url();
			$lines[] = 'Current User: ' . $current_user->display_name . ' (' . $current_user->user_email . ')';
			$lines[] = 'Roles: ' . implode( ', ', (array) $current_user->roles );
			$lines[] = 'Timestamp (Site TZ): ' . wp_date( 'Y-m-d H:i:s T' );
			$lines[] = 'Timestamp (UTC): ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC';
			$lines[] = 'WP Version: ' . get_bloginfo( 'version' );
			$lines[] = 'PHP Version: ' . PHP_VERSION;
			$lines[] = 'Theme: ' . $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ')';
			$lines[] = 'Locale: ' . get_locale();
			$lines[] = 'Memory Limit: ' . ( defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'n/a' );

			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$lines[] = 'Admin Page URL: ' . home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			}

			if ( $screen && isset( $screen->id ) ) {
				$lines[] = 'Screen ID: ' . $screen->id;
			}

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( function_exists( 'get_plugins' ) ) {
				$lines[] = 'Installed Plugins Count: ' . count( (array) get_plugins() );
			}
		}

		$headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
		if ( $email && is_email( $email ) ) {
			$headers[] = 'Reply-To: ' . ( $name ? $name : 'Website User' ) . ' <' . $email . '>';
		}

		$sent = wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
		wp_safe_redirect( add_query_arg( 'bws_support_status', $sent ? 'success' : 'error', $redirect ) );
		exit;
	}
}
