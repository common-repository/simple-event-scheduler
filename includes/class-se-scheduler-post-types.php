<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SE_Scheduler_Post_Types {
    /**
     * Initialize listing types
     */
    public static function init() {
        self::includes();
    }

    /**
     * Loads listing types
     */
    public static function includes() {
		require_once SE_SCHEDULER_DIR . 'includes/post-types/class-se-scheduler-post-type-event.php';
    }
}

SE_Scheduler_Post_Types::init();