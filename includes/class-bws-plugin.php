<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Plugin {
	private static $instance = null;

	public $settings;
	public $dashboard;
	public $admin_cleanup;
	public $branding;
	public $support;
	public $login_branding;
	public $menu_labels;
	public $whitelabel;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->settings       = new BWS_Settings();
		$this->dashboard      = new BWS_Dashboard( $this->settings );
		$this->admin_cleanup  = new BWS_Admin_Cleanup( $this->settings );
		$this->branding       = new BWS_Branding( $this->settings );
		$this->support        = new BWS_Support( $this->settings );
		$this->login_branding = new BWS_Login_Branding( $this->settings );
		$this->menu_labels    = new BWS_Menu_Labels( $this->settings );
		$this->whitelabel     = new BWS_Whitelabel( $this->settings );

		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_init', [ $this->settings, 'register_settings' ] );
		add_action( 'admin_menu', [ $this->settings, 'register_settings_page' ], 999 );
	}
}
