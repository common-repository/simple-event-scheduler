<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Cache {

	static $dataCache = array();

	public static function add_events_2cache($user_id, $events) {
		if (!isset(self::$dataCache[$user_id]))
			self::$dataCache[$user_id] = array();
		self::$dataCache[$user_id]['events'] = $events;
	}
	
	public static function get_events($user_id, $dt) {
		if (!isset(self::$dataCache[$user_id]) || !isset(self::$dataCache[$user_id]['events']) || !isset(self::$dataCache[$user_id]['events'][$dt]))
			return array();
		else
			return self::$dataCache[$user_id]['events'][$dt];
	}
}