<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Login_Branding {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_action( 'login_enqueue_scripts', [ $this, 'output_login_styles' ] );
		add_filter( 'login_headerurl', [ $this, 'filter_login_header_url' ] );
		add_filter( 'login_headertext', [ $this, 'filter_login_header_text' ] );
		add_action( 'login_footer', [ $this, 'render_login_help_text' ] );
	}

	private function enabled() {
		return (bool) $this->settings->get( 'login_branding_enabled', 1 );
	}

	public function output_login_styles() {
		if ( ! $this->enabled() ) {
			return;
		}
		$bg     = (string) $this->settings->get( 'login_bg_color', '#f6f7fb' );
		$button = (string) $this->settings->get( 'login_button_color', '#2271b1' );
		$logo   = (string) $this->settings->get( 'login_logo_url', '' );
		echo '<style>';
		echo 'body.login{background:' . esc_html( $bg ) . ';}';
		echo 'body.login div#login h1 a{' . ( $logo ? 'background-image:url(' . esc_url( $logo ) . ');background-size:contain;width:100%;max-width:320px;' : '' ) . 'height:80px;}';
		echo 'body.login .button-primary{background:' . esc_html( $button ) . ';border-color:' . esc_html( $button ) . ';box-shadow:none;text-shadow:none;}';
		echo 'body.login .button-primary:hover{filter:brightness(.95);}';
		echo '#bws-login-help{margin:16px auto 0;max-width:320px;padding:10px 12px;border:1px solid #dcdcde;border-radius:8px;background:#fff;color:#3c434a;font-size:12px;line-height:1.5;text-align:center;}';
		echo '</style>';
	}

	public function filter_login_header_url( $url ) {
		if ( ! $this->enabled() ) {
			return $url;
		}
		$custom = (string) $this->settings->get( 'login_logo_link_url', '' );
		return $custom ? esc_url( $custom ) : $url;
	}

	public function filter_login_header_text( $text ) {
		if ( ! $this->enabled() ) {
			return $text;
		}
		$custom = (string) $this->settings->get( 'login_logo_title', '' );
		return $custom ? $custom : $text;
	}

	public function render_login_help_text() {
		if ( ! $this->enabled() ) {
			return;
		}
		$help = (string) $this->settings->get( 'login_help_text', '' );
		if ( '' === trim( $help ) ) {
			return;
		}
		echo '<div id="bws-login-help">' . esc_html( $help ) . '</div>';
	}
}
