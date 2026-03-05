<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Branding {
	/** @var BWS_Settings */
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		$this->hooks();
	}

	private function hooks() {
		add_filter( 'admin_footer_text', [ $this, 'filter_admin_footer_text' ] );
		add_filter( 'update_footer', [ $this, 'filter_update_footer_text' ], 999 );
	}

	public function filter_admin_footer_text( $text ) {
		if ( ! is_admin() || ! $this->settings->get( 'branding_footer_enabled', 1 ) ) {
			return $text;
		}

		$custom = (string) $this->settings->get( 'branding_footer_text', '' );
		if ( '' === trim( $custom ) ) {
			return $text;
		}

		return esc_html( $custom );
	}

	public function filter_update_footer_text( $text ) {
		if ( ! is_admin() || ! $this->settings->get( 'branding_footer_enabled', 1 ) ) {
			return $text;
		}

		$custom = (string) $this->settings->get( 'branding_footer_version_text', '' );
		if ( '' === trim( $custom ) ) {
			return $text;
		}

		return esc_html( $custom );
	}
}
