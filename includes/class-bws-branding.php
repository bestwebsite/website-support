<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Branding {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;

		add_filter( 'admin_footer_text', [ $this, 'filter_admin_footer_text' ], 1000 );
		add_filter( 'update_footer', [ $this, 'filter_update_footer_text' ], 1000 );

		// Hide arbitrary admin notices via selectors (optional).
		add_action( 'admin_head', [ $this, 'output_admin_notice_hide_css' ], 1000 );
	}

	public function filter_admin_footer_text( $text ) {
		if ( ! is_admin() || ! $this->settings->get( 'branding_footer_enabled', 1 ) ) {
			return $text;
		}
		$custom = (string) $this->settings->get( 'branding_footer_text', '' );
		return '' !== trim( $custom ) ? esc_html( $custom ) : $text;
	}

	public function filter_update_footer_text( $text ) {
		if ( ! is_admin() || ! $this->settings->get( 'branding_footer_enabled', 1 ) ) {
			return $text;
		}
		$custom = (string) $this->settings->get( 'branding_footer_version_text', '' );
		return '' !== trim( $custom ) ? esc_html( $custom ) : $text;
	}

	public function output_admin_notice_hide_css() {
		if ( ! is_admin() ) {
			return;
		}

		$raw = (string) $this->settings->get( 'admin_notice_hide_selectors', '' );
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return;
		}

		$selectors = preg_split( '/\r\n|\r|\n/', $raw );
		$selectors = is_array( $selectors ) ? $selectors : [];
		echo str_replace( 'bws-inline-hide-css', 'bws-admin-notice-hide-css', BWS_Utils::build_hide_css( $selectors, 'display:none !important;visibility:hidden !important;' ) );
	}
}
