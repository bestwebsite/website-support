<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Dashboard {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_action( 'wp_dashboard_setup', [ $this, 'cleanup_dashboard_widgets' ], 99 );
		// Some plugins add dashboard widgets late (e.g., Elementor). Run a second removal pass on the Dashboard screen.
		add_action( 'admin_head-index.php', [ $this, 'cleanup_dashboard_widgets' ], 999 );
		add_action( 'admin_init', [ $this, 'maybe_remove_welcome_panel' ] );
	}

	private function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
	}

	public function cleanup_dashboard_widgets() {
		if ( $this->settings->get( 'dashboard_remove_quick_draft', 1 ) ) {
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		}
		if ( $this->settings->get( 'dashboard_remove_events_news', 1 ) ) {
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
		}
		if ( $this->settings->get( 'dashboard_remove_activity', 1 ) ) {
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		}
		if ( $this->settings->get( 'dashboard_remove_at_a_glance', 1 ) ) {
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		}
		if ( $this->settings->get( 'dashboard_remove_site_health', 1 ) ) {
			remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
		}

		// Common third-party widgets.
		if ( $this->settings->get( 'dashboard_remove_wp_mail_smtp_widget', 1 ) ) {
			$this->remove_dashboard_metabox_everywhere( 'wp_mail_smtp_reports_widget_lite' );
		}

		// Elementor-specific widgets (only shown if Elementor is active).
		if ( $this->is_elementor_active() ) {
			if ( $this->settings->get( 'dashboard_remove_elementor_overview', 1 ) ) {
				$this->remove_dashboard_metabox_everywhere( 'e-dashboard-overview' );
			}
			if ( $this->settings->get( 'dashboard_remove_elementor_ally', 1 ) ) {
				$this->remove_dashboard_metabox_everywhere( 'e-dashboard-ally' );
			}
		}

		// Custom IDs (one per line)
		$custom_ids = preg_split( '/\r\n|\r|\n/', (string) $this->settings->get( 'dashboard_remove_custom_widget_ids', '' ) );
		$custom_ids = array_filter( array_map( 'trim', (array) $custom_ids ) );
		foreach ( $custom_ids as $id ) {
			$this->remove_dashboard_metabox_everywhere( $id );
		}
	}

	private function remove_dashboard_metabox_everywhere( $id ) {
		foreach ( [ 'normal', 'side', 'advanced', 'column3', 'column4' ] as $ctx ) {
			remove_meta_box( $id, 'dashboard', $ctx );
		}
	}

	public function maybe_remove_welcome_panel() {
		if ( is_admin() && $this->settings->get( 'dashboard_remove_welcome_panel', 0 ) ) {
			remove_action( 'welcome_panel', 'wp_welcome_panel' );
		}
	}
}
