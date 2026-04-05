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

		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		$lines = is_array( $lines ) ? $lines : [];

		$selectors = [];
		foreach ( $lines as $line ) {
			$sel = trim( (string) $line );
			if ( '' === $sel ) {
				continue;
			}

			// Basic hardening: strip characters that can break CSS/style tag.
			$sel = str_replace( [ '{', '}', ';', '<', '>' ], '', $sel );
			$sel = preg_replace( '/[\x00-\x1F\x7F]/u', '', $sel );
			$sel = trim( $sel );
			if ( '' === $sel ) {
				continue;
			}

			// Shorthand: if user pasted a space-delimited class list, convert to .a.b.c
			if ( false === strpos( $sel, '.' ) && false === strpos( $sel, '#' ) && preg_match( '/^[A-Za-z0-9_\- ]+$/', $sel ) ) {
				$parts = array_values( array_filter( array_map( 'trim', preg_split( '/\s+/', $sel ) ) ) );
				if ( count( $parts ) >= 2 ) {
					$sel = '.' . implode( '.', $parts );
				}
			}

			if ( strlen( $sel ) > 200 ) {
				$sel = substr( $sel, 0, 200 );
			}

			$selectors[] = $sel;
			if ( count( $selectors ) >= 75 ) {
				break;
			}
		}

		if ( empty( $selectors ) ) {
			return;
		}

		$css_rules = [];
		foreach ( $selectors as $sel ) {
			$css_rules[] = $sel . '{display:none !important;visibility:hidden !important;}';
		}

		echo "\n" . '<style id="bws-admin-notice-hide-css">' . "\n";
		echo implode( "\n", $css_rules ) . "\n";
		echo "</style>\n";
	}
}
