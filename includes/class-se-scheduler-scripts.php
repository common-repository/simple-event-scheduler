<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Scripts {
	/**
	 * Initialize scripts
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_frontend' ) );
	}

	/**
	 * Loads frontend files
	 */
	public static function enqueue_frontend() {
		$l10n = array(
			'ConfirmEventDel' => __('Do you really want to delete this event?', 'se-scheduler'),
		);

		wp_register_script( 'se-scheduler', SE_SCHEDULER_URL . 'assets/js/se-scheduler.min.js', array( 'jquery' ), false, false );
		wp_localize_script( 'se-scheduler', 'ses_l10n', $l10n );
		wp_enqueue_script( 'se-scheduler' );

		wp_register_style( 'se-scheduler', SE_SCHEDULER_URL . 'assets/css/se-scheduler.min.css' );
		wp_enqueue_style( 'se-scheduler' );
	}
}

SE_Scheduler_Scripts::init();
