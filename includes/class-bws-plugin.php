<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Plugin {
	/** @var BWS_Plugin|null */
	private static $instance = null;

	/** @var BWS_Settings */
	public $settings;

	/** @var BWS_Dashboard */
	public $dashboard;

	/** @var BWS_Admin_Cleanup */
	public $admin_cleanup;

	/** @var BWS_Branding */
	public $branding;

	/** @var BWS_Support */
	public $support;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->settings      = new BWS_Settings();
		$this->dashboard     = new BWS_Dashboard( $this->settings );
		$this->admin_cleanup = new BWS_Admin_Cleanup( $this->settings );
		$this->branding      = new BWS_Branding( $this->settings );
		$this->support       = new BWS_Support( $this->settings );

		$this->hooks();
	}

	private function hooks() {
		add_action( 'admin_init', [ $this->settings, 'register_settings' ] );
		add_action( 'admin_menu', [ $this->settings, 'register_settings_page' ], 999 );
	}
}
