<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Admin_Menu {

	public static function init() {
		add_action( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( __CLASS__, 'menu_reorder' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	public static function menu_reorder( $menu_order ) {
		global $submenu;

		$menu_slugs = array( 'se-scheduler');

		if ( ! empty( $submenu ) && ! empty( $menu_slugs ) && is_array( $menu_slugs ) ) {
			foreach( $menu_slugs as $slug ) {
				if ( ! empty( $submenu[ $slug ] ) ) {
					usort( $submenu[ $slug ], array( __CLASS__, 'sort_alphabet' ) );
				}
			}
		}

		return $menu_order;
	}

	/**
	 * Compare alphabetically
	 */
	public static function sort_alphabet( $a, $b ) {
		return strnatcmp( $a[0], $b[0] );
	}

	/**
	 * Registers admin menu wrapper
	 */
	public static function admin_menu() {
		$scheduleTitle = __( 'Events', 'se-scheduler' );
		if (is_super_admin()) {
			$scheduleTitle = __( 'Users events', 'se-scheduler' );
		}

		add_menu_page( __( 'SE Scheduler', 'se-scheduler' ), __( 'SE Scheduler', 'se-scheduler' ), 'edit_posts', 'se-scheduler', null, SE_SCHEDULER_URL . 'assets/img/date.png', '100' );
		add_submenu_page( 'se-scheduler', $scheduleTitle, $scheduleTitle, 'edit_posts', 'se_scheduler_events', array( __CLASS__, 'render_events') );
		remove_submenu_page( 'se-scheduler', 'se-scheduler' );
	}
	
	public static function render_events() {
		echo SE_Scheduler_Utilities::load( 'admin/users-events', null );
	}
}

SE_Scheduler_Admin_Menu::init();