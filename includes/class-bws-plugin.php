<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Plugin {
	private static $instance = null;

	/** @var BWS_Settings */
	public $settings;

	/** @var BWS_Login_Branding */
	public $login_branding;

	// Admin-only components (initialized only in wp-admin).
	public $dashboard;
	public $admin_cleanup;
	public $branding;
	public $support;
	public $menu_labels;
	public $whitelabel;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->settings = new BWS_Settings();

		// Login branding can apply on wp-login.php which is not always "admin".
		$this->login_branding = new BWS_Login_Branding( $this->settings );

		if ( is_admin() ) {
			$this->boot_admin();
		}
	}

	private function boot_admin() {
		$this->dashboard     = new BWS_Dashboard( $this->settings );
		$this->admin_cleanup = new BWS_Admin_Cleanup( $this->settings );
		$this->branding      = new BWS_Branding( $this->settings );
		$this->support       = new BWS_Support( $this->settings );
		$this->menu_labels   = new BWS_Menu_Labels( $this->settings );
		$this->whitelabel    = new BWS_Whitelabel( $this->settings );

		add_action( 'admin_init', [ $this->settings, 'register_settings' ] );
		add_action( 'admin_menu', [ $this->settings, 'register_settings_page' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this->settings, 'enqueue_admin_assets' ] );
	}

	/** Convenience accessor for other classes. */
	public function settings() {
		return $this->settings;
	}
}
