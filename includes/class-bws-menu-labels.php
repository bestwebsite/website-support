<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BWS_Menu_Labels {
	private $settings;

	public function __construct( BWS_Settings $settings ) {
		$this->settings = $settings;
		add_action( 'admin_menu', [ $this, 'rename_admin_menus' ], 1000 );
	}

	public function rename_admin_menus() {
		global $menu, $submenu;

		$built_in_map = [];
		if ( $this->settings->get( 'label_posts', '' ) ) {
			$built_in_map['edit.php'] = [
				'menu'    => (string) $this->settings->get( 'label_posts', '' ),
				'add_new' => sprintf( __( 'Add New %s', BWS_TEXT_DOMAIN ), (string) $this->settings->get( 'label_posts', '' ) ),
			];
		}
		if ( $this->settings->get( 'label_pages', '' ) ) {
			$built_in_map['edit.php?post_type=page'] = [
				'menu'    => (string) $this->settings->get( 'label_pages', '' ),
				'add_new' => sprintf( __( 'Add New %s', BWS_TEXT_DOMAIN ), rtrim( (string) $this->settings->get( 'label_pages', '' ), 's' ) ),
			];
		}
		if ( $this->settings->get( 'label_media', '' ) ) {
			$built_in_map['upload.php'] = [
				'menu'    => (string) $this->settings->get( 'label_media', '' ),
				'add_new' => __( 'Add New', BWS_TEXT_DOMAIN ),
			];
		}

		foreach ( $built_in_map as $slug => $labels ) {
			$this->rename_menu_slug( $menu, $submenu, $slug, $labels['menu'], $labels['add_new'] );
		}

		foreach ( $this->parse_cpt_map() as $post_type => $labels ) {
			$slug = 'edit.php?post_type=' . $post_type;
			$this->rename_menu_slug( $menu, $submenu, $slug, $labels['menu'], $labels['add_new'] );
		}
	}

	private function rename_menu_slug( &$menu, &$submenu, $slug, $menu_label, $add_new_label ) {
		if ( is_array( $menu ) ) {
			foreach ( $menu as $k => $item ) {
				if ( isset( $item[2] ) && $item[2] === $slug ) {
					$menu[ $k ][0] = $menu_label;
				}
			}
		}

		if ( isset( $submenu[ $slug ] ) && is_array( $submenu[ $slug ] ) ) {
			foreach ( $submenu[ $slug ] as $i => $subitem ) {
				if ( empty( $subitem[2] ) ) {
					continue;
				}
				if ( $subitem[2] === $slug ) {
					$submenu[ $slug ][ $i ][0] = $menu_label;
				}
				if ( 0 === strpos( $subitem[2], 'post-new.php' ) ) {
					$submenu[ $slug ][ $i ][0] = $add_new_label;
				}
			}
		}
	}

	private function parse_cpt_map() {
		$map = [];
		$raw = (string) $this->settings->get( 'label_cpt_map', '' );
		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		foreach ( (array) $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || '#' === substr( $line, 0, 1 ) ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			if ( count( $parts ) < 2 ) {
				continue;
			}
			$post_type = sanitize_key( $parts[0] );
			$menu_lbl  = sanitize_text_field( $parts[1] );
			$add_lbl   = isset( $parts[2] ) && '' !== $parts[2] ? sanitize_text_field( $parts[2] ) : sprintf( __( 'Add New %s', BWS_TEXT_DOMAIN ), rtrim( $menu_lbl, 's' ) );
			if ( $post_type && $menu_lbl ) {
				$map[ $post_type ] = [ 'menu' => $menu_lbl, 'add_new' => $add_lbl ];
			}
		}
		return $map;
	}
}
