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
			foreach ( get_post_types( [], 'names' ) as $post_type ) {
				if ( post_type_supports( $post_type, 'comments' ) ) {
					remove_post_type_support( $post_type, 'comments' );
				}
				if ( post_type_supports( $post_type, 'trackbacks' ) ) {
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}

			if ( $this->settings->get( 'comments_disable_feeds', 0 ) ) {
				add_action( 'do_feed_rss2_comments', [ $this, 'disable_comment_feed' ], 1 );
				add_action( 'do_feed_atom_comments', [ $this, 'disable_comment_feed' ], 1 );
				add_action( 'do_feed_rss_comments', [ $this, 'disable_comment_feed' ], 1 );
			}
		}
	}

	public function disable_comment_feed() {
		wp_die( esc_html__( 'Comment feeds are disabled on this site.', BWS_TEXT_DOMAIN ), '', [ 'response' => 403 ] );
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
		return $this->settings->get( 'security_disable_application_passwords', 1 ) ? false : $available;
	}

	public function filter_generator( $generator ) {
		return $this->settings->get( 'security_remove_generator', 1 ) ? '' : $generator;
	}

	public function force_comments_closed( $open, $post_id ) {
		return $this->settings->get( 'comments_disable_sitewide', 1 ) ? false : $open;
	}

	public function filter_comments_array( $comments, $post_id ) {
		return $this->settings->get( 'comments_disable_sitewide', 1 ) ? [] : $comments;
	}

	public function filter_revisions_to_keep( $num, $post ) {
		if ( ! $this->settings->get( 'perf_limit_revisions_enabled', 1 ) ) {
			return $num;
		}

		$limit = (int) $this->settings->get( 'perf_limit_revisions_count', 10 );
		if ( $limit < 0 ) {
			return $num;
		}

		return 0 === $limit ? 0 : max( 1, $limit );
	}

	public function maybe_force_ssl_admin_redirect() {
		if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}
		if ( ! $this->settings->get( 'security_force_ssl_admin', 1 ) ) {
			return;
		}
		if ( 0 !== strpos( home_url(), 'https://' ) || is_ssl() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/wp-admin/';
		$target      = set_url_scheme( home_url( $request_uri ), 'https' );

		if ( empty( $target ) ) {
			return;
		}

		wp_safe_redirect( $target, 302 );
		exit;
	}

	public function maybe_block_author_enumeration() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( ! $this->settings->get( 'security_block_author_enum', 1 ) ) {
			return;
		}
		if ( isset( $_GET['author'] ) && is_numeric( wp_unslash( $_GET['author'] ) ) ) {
			wp_safe_redirect( home_url( '/' ), 302 );
			exit;
		}
	}

	public function maybe_redirect_attachment_pages() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}
		if ( ! $this->settings->get( 'seo_disable_attachment_pages', 1 ) || ! is_attachment() ) {
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
