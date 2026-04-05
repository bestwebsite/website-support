<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Hardening {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;

		add_action( 'init', [ $this, 'apply_init_hardening' ], 1 );
		add_action( 'template_redirect', [ $this, 'maybe_redirect_attachment_pages' ], 1 );

		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_disable_dashicons_for_visitors' ], 20 );
		add_filter( 'xmlrpc_methods', [ $this, 'filter_xmlrpc_methods' ] );
		add_filter( 'wp_is_application_passwords_available', [ $this, 'maybe_disable_application_passwords' ] );

		add_filter( 'the_generator', [ $this, 'filter_generator' ] );

		add_filter( 'comments_open', [ $this, 'force_comments_closed' ], 20, 2 );
		add_filter( 'pings_open', [ $this, 'force_comments_closed' ], 20, 2 );
		add_filter( 'comments_array', [ $this, 'filter_comments_array' ], 20, 2 );

		add_filter( 'wp_revisions_to_keep', [ $this, 'filter_revisions_to_keep' ], 10, 2 );

		add_action( 'admin_init', [ $this, 'maybe_force_ssl_admin_redirect' ], 1 );
		add_filter( 'wp_mail_from', [ $this, 'filter_wp_mail_from' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'filter_wp_mail_from_name' ] );
		add_action( 'init', [ $this, 'maybe_block_author_enumeration' ], 1 );
	}

	public function apply_init_hardening() {
		if ( $this->settings->get( 'perf_disable_emojis', 1 ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		}

		if ( $this->settings->get( 'perf_disable_oembed', 1 ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
			remove_action( 'rest_api_init', 'wp_oembed_register_route' );
			remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10 );
		}

		if ( $this->settings->get( 'security_remove_generator', 1 ) ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		if ( $this->settings->get( 'comments_disable_sitewide', 1 ) ) {
			// Remove comment support from post types.
			foreach ( get_post_types( [], 'names' ) as $pt ) {
				if ( post_type_supports( $pt, 'comments' ) ) {
					remove_post_type_support( $pt, 'comments' );
				}
				if ( post_type_supports( $pt, 'trackbacks' ) ) {
					remove_post_type_support( $pt, 'trackbacks' );
				}
			}
			// Optional: disable comment feed endpoints.
			if ( $this->settings->get( 'comments_disable_feeds', 0 ) ) {
				add_action( 'do_feed_rss2_comments', [ $this, 'disable_comment_feed' ], 1 );
				add_action( 'do_feed_atom_comments', [ $this, 'disable_comment_feed' ], 1 );
				add_action( 'do_feed_rss_comments', [ $this, 'disable_comment_feed' ], 1 );
			}
		}
	}

	public function maybe_disable_dashicons_for_visitors() {
		if ( is_user_logged_in() ) {
			return;
		}
		if ( $this->settings->get( 'perf_disable_dashicons_visitors', 1 ) ) {
			wp_deregister_style( 'dashicons' );
		}
	}

	public function filter_xmlrpc_methods( $methods ) {
		if ( $this->settings->get( 'security_disable_xmlrpc_pingbacks', 1 ) ) {
			unset( $methods['pingback.ping'] );
			unset( $methods['pingback.extensions.getPingbacks'] );
		}
		return $methods;
	}

	public function maybe_disable_application_passwords( $available ) {
		if ( $this->settings->get( 'security_disable_application_passwords', 1 ) ) {
			return false;
		}
		return $available;
	}

	public function filter_generator( $gen ) {
		if ( $this->settings->get( 'security_remove_generator', 1 ) ) {
			return '';
		}
		return $gen;
	}

	public function force_comments_closed( $open, $post_id ) {
		if ( $this->settings->get( 'comments_disable_sitewide', 1 ) ) {
			return false;
		}
		return $open;
	}

	public function filter_comments_array( $comments, $post_id ) {
		if ( $this->settings->get( 'comments_disable_sitewide', 1 ) ) {
			return [];
		}
		return $comments;
	}

	public function filter_revisions_to_keep( $num, $post ) {
		if ( ! $this->settings->get( 'perf_limit_revisions_enabled', 1 ) ) {
			return $num;
		}
		$limit = (int) $this->settings->get( 'perf_limit_revisions_count', 10 );
		if ( $limit < 0 ) {
			return $num;
		}
		if ( 0 === $limit ) {
			return 0;
		}
		return max( 1, $limit );
	}

	public function maybe_force_ssl_admin_redirect() {
		if ( ! is_admin() ) {
			return;
		}
		if ( ! $this->settings->get( 'security_force_ssl_admin', 1 ) ) {
			return;
		}
		// Only enforce if the site home URL is HTTPS (meaning HTTPS is expected/working).
		$home = home_url();
		if ( 0 !== strpos( $home, 'https://' ) ) {
			return;
		}
		if ( is_ssl() ) {
			return;
		}
		// Redirect to HTTPS equivalent.
		$target = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		wp_safe_redirect( $target, 301 );
		exit;
	}

	public function maybe_block_author_enumeration() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( ! $this->settings->get( 'security_block_author_enum', 1 ) ) {
			return;
		}
		if ( isset( $_GET['author'] ) && is_numeric( $_GET['author'] ) ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}


	private function is_wp_mail_smtp_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return function_exists( 'is_plugin_active' ) && ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) || is_plugin_active( 'wp-mail-smtp-pro/wp_mail_smtp.php' ) );
	}

	public function filter_wp_mail_from( $from_email ) {
		if ( ! $this->settings->get( 'support_force_from_domain', 0 ) ) {
			return $from_email;
		}
		// If WP Mail SMTP is active, do not override.
		if ( is_admin() && $this->is_wp_mail_smtp_active() ) {
			return $from_email;
		}
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! $host ) {
			return $from_email;
		}
		return 'wordpress@' . $host;
	}

	public function filter_wp_mail_from_name( $from_name ) {
		if ( ! $this->settings->get( 'support_force_from_domain', 0 ) ) {
			return $from_name;
		}
		if ( is_admin() && $this->is_wp_mail_smtp_active() ) {
			return $from_name;
		}
		$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		return $site ? $site : $from_name;
	}

	public function maybe_redirect_attachment_pages() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( ! $this->settings->get( 'seo_disable_attachment_pages', 1 ) ) {
			return;
		}
		if ( ! is_attachment() ) {
			return;
		}
		$url = wp_get_attachment_url( get_queried_object_id() );
		if ( ! $url ) {
			$url = home_url( '/' );
		}
		wp_safe_redirect( $url, 301 );
		exit;
	}
}
