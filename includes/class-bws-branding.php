<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Branding {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_filter( 'admin_footer_text', [ $this, 'filter_admin_footer_text' ] );
		add_filter( 'update_footer', [ $this, 'filter_update_footer_text' ], 999 );
		add_action( 'admin_head', [ $this, 'output_admin_notice_hide_css' ], 20 );
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

	/**
	 * Outputs admin-side CSS to hide arbitrary notices by selector.
	 * Selector list is managed via settings: admin_notice_hide_selectors (one per line).
	 */
	public function output_admin_notice_hide_css() {
		if ( ! is_admin() ) {
			return;
		}
		$raw = (string) $this->settings->get( 'admin_notice_hide_selectors', '' );
		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		$selectors = array_values( array_filter( array_map( 'trim', (array) $lines ) ) );
		if ( empty( $selectors ) ) {
			return;
		}

		// Safety limits: keep output small and avoid accidental broad selectors.
		$max = 30;
		$clean = [];
		foreach ( $selectors as $sel ) {
			if ( count( $clean ) >= $max ) {
				break;
			}
			// Strip tags and control chars.
			$sel = wp_strip_all_tags( $sel );
			$sel = preg_replace( '/[\x00-\x1F\x7F]/', '', $sel );
			$sel = trim( $sel );
			if ( '' === $sel ) {
				continue;
			}

			// Allow only common CSS selector characters.
			if ( ! preg_match( '/^[a-zA-Z0-9\s\#\._\-\[\]=\"\'\:\>\+\~\(\)\*\,]+$/', $sel ) ) {
				continue;
			}

			// Reject overly broad selectors that can hide core UI accidentally.
			$lower = strtolower( $sel );
			if ( in_array( $lower, [ 'body', 'html', '*', '#wpwrap', '#wpcontent' ], true ) ) {
				continue;
			}

			$clean[] = $sel;
		}

		if ( empty( $clean ) ) {
			return;
		}

		echo "\n<style id=\"bws-admin-notice-hide\">\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo implode( ",\n", array_map( 'esc_html', $clean ) ) . "{display:none !important;}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}
