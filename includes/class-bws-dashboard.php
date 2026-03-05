<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Dashboard {
	/** @var BWS_Settings */
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		$this->hooks();
	}

	private function hooks() {
		add_action( 'wp_dashboard_setup', [ $this, 'cleanup_dashboard_widgets' ], 99 );
		add_action( 'admin_init', [ $this, 'maybe_remove_welcome_panel' ] );
	}

	public function cleanup_dashboard_widgets() {
		if ( $this->settings->get( 'dashboard_remove_quick_draft', 1 ) ) {
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		}

		if ( $this->settings->get( 'dashboard_remove_events_news', 1 ) ) {
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
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

		$custom_ids = (string) $this->settings->get( 'dashboard_remove_custom_widget_ids', '' );
		if ( '' !== trim( $custom_ids ) ) {
			$ids = preg_split( '/\r\n|\r|\n/', $custom_ids );
			$ids = array_filter( array_map( 'trim', (array) $ids ) );

			$contexts = [ 'normal', 'side', 'column3', 'column4' ];
			foreach ( $ids as $id ) {
				foreach ( $contexts as $context ) {
					remove_meta_box( $id, 'dashboard', $context );
				}
			}
		}
	}

	public function maybe_remove_welcome_panel() {
		if ( is_admin() && $this->settings->get( 'dashboard_remove_welcome_panel', 0 ) ) {
			remove_action( 'welcome_panel', 'wp_welcome_panel' );
		}
	}
}
