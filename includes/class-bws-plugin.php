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
	public $hardening;

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
		$this->hardening      = new BWS_Hardening( $this->settings );

		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_init', [ $this->settings, 'register_settings' ] );
		add_action( 'admin_menu', [ $this->settings, 'register_settings_page' ], 999 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	public function enqueue_admin_assets( $hook_suffix ) {
		// Only load assets on our settings screen.
		if ( 'settings_page_' . BWS_SETTINGS_PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'bws-admin-settings', BWS_PLUGIN_URL . 'assets/admin-settings.css', [], BWS_VERSION );
		wp_enqueue_script( 'bws-admin-settings', BWS_PLUGIN_URL . 'assets/admin-settings.js', [], BWS_VERSION, true );
	}
}
