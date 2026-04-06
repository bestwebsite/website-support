<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Utils {
	public static function normalize_newlines( $value ) {
		$value = (string) $value;
		return preg_replace( "/\r\n?|\n/u", "\n", $value );
	}

	public static function sanitize_plain_multiline( $value, $max_lines = 100, $max_line_length = 250 ) {
		$lines   = explode( "\n", self::normalize_newlines( wp_unslash( (string) $value ) ) );
		$output  = [];
		$counter = 0;

		foreach ( $lines as $line ) {
			if ( $counter >= $max_lines ) {
				break;
			}

			$line = wp_strip_all_tags( (string) $line, true );
			$line = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $line );
			$line = trim( $line );

			if ( '' === $line ) {
				continue;
			}

			if ( function_exists( 'mb_substr' ) ) {
				$line = mb_substr( $line, 0, $max_line_length );
			} else {
				$line = substr( $line, 0, $max_line_length );
			}

			$output[] = $line;
			$counter++;
		}

		return implode( "\n", $output );
	}

	public static function sanitize_menu_slug_list( $value ) {
		$lines   = explode( "\n", self::normalize_newlines( wp_unslash( (string) $value ) ) );
		$output  = [];
		$counter = 0;

		foreach ( $lines as $line ) {
			if ( $counter >= 100 ) {
				break;
			}

			$line = wp_strip_all_tags( (string) $line, true );
			$line = preg_replace( '/[^A-Za-z0-9_\-\?&=:\/.%]/', '', trim( $line ) );

			if ( '' === $line ) {
				continue;
			}

			$output[] = $line;
			$counter++;
		}

		return implode( "\n", array_values( array_unique( $output ) ) );
	}

	public static function sanitize_submenu_map( $value ) {
		$lines   = explode( "\n", self::normalize_newlines( wp_unslash( (string) $value ) ) );
		$output  = [];
		$counter = 0;

		foreach ( $lines as $line ) {
			if ( $counter >= 100 ) {
				break;
			}

			$parts = array_map( 'trim', explode( '|', wp_strip_all_tags( (string) $line, true ) ) );
			if ( count( $parts ) < 2 ) {
				continue;
			}

			$parent = preg_replace( '/[^A-Za-z0-9_\-\?&=:\/.%]/', '', (string) $parts[0] );
			$child  = preg_replace( '/[^A-Za-z0-9_\-\?&=:\/.%]/', '', (string) $parts[1] );

			if ( '' === $parent || '' === $child ) {
				continue;
			}

			$output[] = $parent . '|' . $child;
			$counter++;
		}

		return implode( "\n", array_values( array_unique( $output ) ) );
	}

	public static function sanitize_dashboard_widget_ids( $value ) {
		$lines   = explode( "\n", self::normalize_newlines( wp_unslash( (string) $value ) ) );
		$output  = [];
		$counter = 0;

		foreach ( $lines as $line ) {
			if ( $counter >= 100 ) {
				break;
			}

			$line = preg_replace( '/[^A-Za-z0-9_\-]/', '', trim( wp_strip_all_tags( (string) $line, true ) ) );
			if ( '' === $line ) {
				continue;
			}

			$output[] = $line;
			$counter++;
		}

		return implode( "\n", array_values( array_unique( $output ) ) );
	}

	public static function sanitize_css_selector_line( $selector ) {
		$selector = trim( wp_strip_all_tags( (string) $selector, true ) );
		$selector = str_replace( array( '{', '}', ';', '<', '>' ), '', $selector );
		$selector = preg_replace( '/[\x00-\x1F\x7F]/u', '', $selector );
		$selector = trim( $selector );

		if ( '' === $selector ) {
			return '';
		}

		if ( false === strpos( $selector, '.' ) && false === strpos( $selector, '#' ) && preg_match( '/^[A-Za-z0-9_\- ]+$/', $selector ) ) {
			$parts = array_values( array_filter( array_map( 'trim', preg_split( '/\s+/', $selector ) ) ) );
			if ( count( $parts ) >= 2 ) {
				$selector = '.' . implode( '.', $parts );
			}
		}

		if ( function_exists( 'mb_substr' ) ) {
			$selector = mb_substr( $selector, 0, 200 );
		} else {
			$selector = substr( $selector, 0, 200 );
		}

		return trim( $selector );
	}

	public static function sanitize_css_selector_list( $value ) {
		$lines   = explode( "\n", self::normalize_newlines( wp_unslash( (string) $value ) ) );
		$output  = [];
		$counter = 0;

		foreach ( $lines as $line ) {
			if ( $counter >= 75 ) {
				break;
			}

			$selector = self::sanitize_css_selector_line( $line );
			if ( '' === $selector ) {
				continue;
			}

			$output[] = $selector;
			$counter++;
		}

		return implode( "\n", array_values( array_unique( $output ) ) );
	}

	public static function build_hide_css( $selectors, $declarations = 'display:none !important;' ) {
		$selectors = is_array( $selectors ) ? $selectors : array();
		$rules     = array();

		foreach ( $selectors as $selector ) {
			$selector = self::sanitize_css_selector_line( $selector );
			if ( '' === $selector ) {
				continue;
			}

			$rules[] = $selector . '{' . trim( (string) $declarations ) . '}';
		}

		if ( empty( $rules ) ) {
			return '';
		}

		return "\n<style id=\"bws-inline-hide-css\">\n" . implode( "\n", array_unique( $rules ) ) . "\n</style>\n";
	}
}
