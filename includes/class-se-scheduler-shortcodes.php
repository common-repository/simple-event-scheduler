<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Shortcodes {
	/**
	 * Initialize shortcodes
	 */
	public static function init() {
		add_shortcode( 'se_scheduler_eventcalendar', array( __CLASS__, 'eventcalendar' ) );
	}

	/**
	 * Event Calendar
	 */
	public static function eventcalendar( $atts = array() ) {
		$a = shortcode_atts( array('for_user' => null), $atts );
		$user_id = SE_Scheduler_Utilities::get_user_id($a);
		
		$utz = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($user_id));
		self::prepare_calendar_data($user_id, $utz);

		$attrs = array(
			'user_id' => $user_id,
			'utz' => $utz
		);
		
		SE_Scheduler_Logic::$addEventCalendar = true;

		return SE_Scheduler_Utilities::load( 'content-eventcalendar', $attrs );
	}

	private static function prepare_calendar_data($user_id, $utz) {
		$days_count = SE_Scheduler_Settings::instance()->se_days_count;
		$dtFrom = new DateTime('today', $utz);
		$dtTo = new DateTime('today', $utz);
		$dtTo->modify('+' . $days_count . ' days');
		
		$events = SE_Scheduler_Query::get_user_events($user_id, $dtFrom, $dtTo, $utz);

		SE_Scheduler_Cache::add_events_2cache($user_id, $events);
	}
}

SE_Scheduler_Shortcodes::init();
