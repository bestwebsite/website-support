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
	 * Output CSS in wp-admin to hide admin notices by user-provided selectors.
	 *
	 * This MUST exist because it's registered as an admin_head callback.
	 * If this method is missing, WordPress will fatal during admin_head.
	 */
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

			// Allow comments, but do NOT treat CSS ID selectors (#foo) as comments.
			// We only skip lines that start with "# " (hash + space) as a comment marker.
			if ( 0 === strpos( $sel, '# ' ) ) {
				continue;
			}

			$sel = $this->normalize_notice_selector( $sel );

			// Strip characters that could break out of CSS and inject markup.
			$sel = str_replace( [ '{', '}', ';', '<', '>' ], '', $sel );
			$sel = preg_replace( '/[\x00-\x1F\x7F]/u', '', $sel ); // control chars
			$sel = trim( $sel );

			if ( '' === $sel ) {
				continue;
			}
			if ( strlen( $sel ) > 200 ) {
				$sel = substr( $sel, 0, 200 );
			}

			$selectors[] = $sel;

			// Prevent runaway output if someone pastes a huge blob.
			if ( count( $selectors ) >= 75 ) {
				break;
			}
		}

		if ( empty( $selectors ) ) {
			return;
		}

		$rules = [];
		foreach ( $selectors as $sel ) {
			$rules[] = $sel . '{display:none !important;visibility:hidden !important;}';
		}

		echo "\n" . '<style id="bws-admin-notice-hide-css">' . "\n";
		echo implode( "\n", $rules ) . "\n";
		echo "</style>\n";
	}

	/**
	 * Normalize a selector entered by a human.
	 *
	 * Supports the common case where a user copies a space-delimited class list
	 * (e.g. "notice notice-info is-dismissible") by turning it into
	 * ".notice.notice-info.is-dismissible".
	 */
	private function normalize_notice_selector( $sel ) {
		$sel = trim( (string) $sel );

		$looks_like_class_list = ( false !== strpos( $sel, ' ' ) )
			&& false === strpbrk( $sel, '.#[:>+~' );

		if ( $looks_like_class_list ) {
			$parts = preg_split( '/\s+/', $sel );
			$parts = is_array( $parts ) ? array_filter( array_map( 'trim', $parts ) ) : [];
			if ( ! empty( $parts ) ) {
				$sel = '.' . implode( '.', $parts );
			}
		}

		return $sel;
	}
}
