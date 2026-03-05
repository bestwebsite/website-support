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
		$raw = (string) $this->settings->get( 'support_topic_options', '' );
		$topics = preg_split( '/\r\n|\r|\n/', $raw );
		$topics = array_values( array_filter( array_map( 'trim', (array) $topics ) ) );
		return ! empty( $topics ) ? $topics : [ 'Technical Support', 'Content Update Request', 'Other' ];
	}

	private function render_branding_block() {
		$logo_url = (string) $this->settings->get( 'branding_support_logo_url', '' );
		$intro    = (string) $this->settings->get( 'branding_support_widget_intro', 'Managed Website Support by Best Website' );
		$email    = (string) $this->settings->get( 'support_email', 'support@bestwebsite.com' );

		echo '<div style="margin-bottom:12px;padding:12px;border:1px solid #dcdcde;border-radius:8px;background:#fff;">';
		if ( $logo_url ) {
			echo '<p style="margin:0 0 8px 0;"><img src="' . esc_url( $logo_url ) . '" alt="" style="max-height:44px;width:auto;"></p>';
		}
		echo '<p style="margin:0 0 6px 0;font-weight:600;">' . esc_html( $intro ) . '</p>';
		echo '<p style="margin:0;color:#50575e;">' . esc_html__( 'Email:', BWS_TEXT_DOMAIN ) . ' <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
		echo '</div>';
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
				echo '<p>' . esc_html( $page_intro ) . '</p>';
			}
		}

		$instructions = (string) $this->settings->get( 'support_instructions_text', '' );
		if ( '' !== trim( $instructions ) ) {
			echo '<p>' . esc_html( $instructions ) . '</p>';
		}

		$current_user = wp_get_current_user();
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'bws_submit_support_request', 'bws_support_nonce' );
		echo '<input type="hidden" name="action" value="bws_submit_support_request">';
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row"><label for="bws_support_topic">' . esc_html__( 'Topic', BWS_TEXT_DOMAIN ) . '</label></th><td><select id="bws_support_topic" name="bws_support_topic" required>';
		foreach ( $this->get_topics() as $topic ) {
			echo '<option value="' . esc_attr( $topic ) . '">' . esc_html( $topic ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th scope="row"><label for="bws_support_message">' . esc_html__( 'Message', BWS_TEXT_DOMAIN ) . '</label></th><td><textarea id="bws_support_message" name="bws_support_message" rows="6" class="large-text" required></textarea></td></tr>';
		echo '<tr><th scope="row"><label for="bws_support_name">' . esc_html__( 'Your Name', BWS_TEXT_DOMAIN ) . '</label></th><td><input id="bws_support_name" type="text" name="bws_support_name" class="regular-text" value="' . esc_attr( $current_user->display_name ) . '"></td></tr>';
		echo '<tr><th scope="row"><label for="bws_support_email">' . esc_html__( 'Your Email', BWS_TEXT_DOMAIN ) . '</label></th><td><input id="bws_support_email" type="email" name="bws_support_email" class="regular-text" value="' . esc_attr( $current_user->user_email ) . '"></td></tr>';
		echo '</tbody></table>';
		submit_button( __( 'Send Support Request', BWS_TEXT_DOMAIN ) );
		echo '</form>';
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
		$subject   = sprintf( '[Website Support] %s — %s', $topic, $site_name ?: $site_url );

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
