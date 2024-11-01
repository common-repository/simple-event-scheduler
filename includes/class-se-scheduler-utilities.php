<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SE_Scheduler_Utilities
 */
class SE_Scheduler_Utilities {

	/**
	 * Get user id for shortcode
	 */
	public static function get_user_id($attr) {
		if (isset($attr['for_user']) && !empty($attr['for_user'])) {
			if (ctype_digit($attr['for_user']))
				return intval($attr['for_user']);
			else {
				$for_user = mb_strtolower($attr['for_user']);
				if ($for_user == 'current') {
					$uid = get_current_user_id();
					return $uid == 0 ? null : $uid;
				}
				else if ($for_user == 'post_author') {
					$uid = get_the_author_meta('ID');
					return empty($uid) ? null : intval($uid);
				}
				else
					return 0;
			}
		}
		else
			return 0;
	}

	/**
	 * Gets user timezone
	 */
	public static function get_user_timezone($user_id) {
		$user_timezone = null;
		if (!empty($user_id) && SE_Scheduler_Settings::instance()->se_timezone_avail)
			$user_timezone = get_user_meta( $user_id, SE_Scheduler::USER_TIMEZONE_META_NAME, true );
		if (empty($user_timezone))
			$user_timezone = self::wp_get_timezone_string();
		return $user_timezone;
	}

	/**
	 * Returns the timezone string for a site, even if it’s set to a UTC offset
	 */
	public static function wp_get_timezone_string() {
		if ( $timezone = get_option( 'timezone_string' ) )
			return $timezone;
		// get UTC offset, if it isn’t set then return UTC
		if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
			return 'UTC';
		// adjust UTC offset from hours to seconds
		$utc_offset *= 3600;
		// attempt to guess the timezone string from the UTC offset
		if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
			return $timezone;
		}
		// last try, guess timezone string manually
		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
					return $city['timezone_id'];
			}
		}
		// fallback to UTC
		return 'UTC';
	}

	/**
	 * Gets template path
	 */
	public static function locate( $name, $plugin_dir = SE_SCHEDULER_DIR ) {
		$template = '';

		// Current theme base dir
		if ( ! empty( $name ) ) {
			$template = locate_template( "{$name}.php" );
		}

		// Child theme
		if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/templates/{$name}.php" ) ) {
			$template = get_stylesheet_directory() . "/templates/{$name}.php";
		}

		// Original theme
		if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/templates/{$name}.php" ) ) {
			$template = get_template_directory() . "/templates/{$name}.php";
		}

		// Current Plugin
		if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "/templates/{$name}.php" ) ) {
			$template = $plugin_dir . "/templates/{$name}.php";
		}

		// Nothing found
		if ( empty( $template ) ) {
			throw new Exception( "Template /templates/{$name}.php in plugin dir {$plugin_dir} not found." );
		}

		return $template;
	}

	/**
	 * Loads template content
	 */
	public static function load( $name, $args = array(), $plugin_dir = SE_SCHEDULER_DIR ) {
        if ( is_array( $args ) && count( $args ) > 0 ) {
			extract( $args, EXTR_SKIP );
		}

		$path = self::locate( $name, $plugin_dir );
		ob_start();
		include $path;
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	/**
	 * Gets header for event calendar
	 */
	public static function get_eventcal_header($date) {
		$frmt = SE_Scheduler_Settings::instance()->se_date_format;
		$days_count = SE_Scheduler_Settings::instance()->se_days_count - 1;
		$nextDT = clone $date;
		$nextDT->modify('+' . $days_count . ' days');
		return $date->format($frmt) . ' - ' . $nextDT->format($frmt);
	}
	
	/**
	 * Returns timezone list
	 */
	public static function timezone_list() {
		static $timezones = null;

		if ($timezones === null) {
			$timezones = [];
			$offsets = [];
			$now = new DateTime('today', new DateTimeZone('UTC'));

			foreach (DateTimeZone::listIdentifiers() as $timezone) {
				$now->setTimezone(new DateTimeZone($timezone));
				$offsets[] = $offset = $now->getOffset();
				$timezones[$timezone] = '(' . self::format_GMT_offset($offset) . ') ' . self::format_timezone_name($timezone);
			}

			array_multisort($offsets, $timezones);
		}

		return $timezones;
	}

	private static function format_GMT_offset($offset) {
		$hours = intval($offset / 3600);
		$minutes = abs(intval($offset % 3600 / 60));
		return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
	}

	private static function format_timezone_name($name) {
		$name = str_replace('/', ', ', $name);
		$name = str_replace('_', ' ', $name);
		$name = str_replace('St ', 'St. ', $name);
		return $name;
	}
	
	/**
	 * Get week day short name by number: 0 - Monday
	 */
	private static function get_week_day_shortname($weekDay) {
		static $weekDayShortNames = null;
		if ($weekDayShortNames === null) {
			$weekDayShortNames = array( 0 => __( 'Mo', 'se-scheduler' ), 1 => __( 'Tu', 'se-scheduler' ), 2 => __( 'We', 'se-scheduler' ), 3 => __( 'Th', 'se-scheduler' ), 4 => __( 'Fr', 'se-scheduler' ), 5 => __( 'Sa', 'se-scheduler' ), 6 => __( 'Su', 'se-scheduler' ));
		}
		return $weekDayShortNames[$weekDay];
	}

	/**
	 * Gets user date and time in specified format
	 */
	public static function get_user_datetime($user_id, $format, $time = 'today') {
		$user_timezone = self::get_user_timezone($user_id);
		$date = new DateTime($time, new DateTimeZone($user_timezone));
		return $date->format($format);
	}

	/**
	 * Get event time frame text
	 */
	public static function get_event_timeframe_text($event, $timeFormat) {
		$res = '<div class="se-ev-item-start" data-raw="' . $event->Start->format('H:i') . '">' . $event->Start->format($timeFormat) . '</div>';
		if (!empty($event->Duration))
		{
			$res .= '<div class="se-ev-item-dur">'.$event->Duration.'</div>';
		}
		return $res;
	}

	/**
	 * Check if string is valid date YYYY-MM-DD
	 */
	public static function check_date($str) {
		return (!empty($str) && is_string($str) && date_create_from_format('Y-m-d', $str) !== FALSE);
	}

	/**
	 * Check if string is valid time in minutes
	 */
	public static function check_time($str) {
		return (!empty($str) && is_string($str) && date_create_from_format('H:i', $str) !== FALSE);
	}

	/**
	 * Render day header
	 */
	public static function get_day_header( $date ) {
		return sprintf('<span>%s</span><br><span>%s</span>', SE_Scheduler_Utilities::get_week_day_shortname($date->format('N') - 1), $date->format('j'));
	}

	/**
	 * Render fields
	 */
	public static function get_fields() {
		$fields = SE_Scheduler_Settings::instance()->get_fields();
		$html = '';
		foreach($fields as $name => $field) {
			$html .= self::get_field( 'se-' . $name, $field['title'], $field['elem'], $field['with_err'] );
		}
		return $html;
	}

	public static function get_field( $name, $header, $input_elem, $with_err_elem ) {
		$err_elem = $with_err_elem ? '<i>' . __( 'Please, fill this field with correct value', 'se-scheduler' ) . '</i>' : '';
		return sprintf('<div><label for="%s">%s</label><br/>%s%s</div>', $name, $header, $input_elem, $err_elem);
	}

	/**
	 * Get date pattern for picker
	 */
	public static function get_picker_pattern() {
		$dateFormat = SE_Scheduler_Settings::instance()->se_date_format;
		$dtPat = '';
		for ($i = 0; $i < strlen($dateFormat); $i++){
			if (strpos($dtPat, 'D') === false && strpos($dtPat, 'd') === false) {
				if ($dateFormat[$i] == 'd')
					$dtPat .= 'D';
				elseif ($dateFormat[$i] == 'j' && strpos($dtPat, 'D') === false && strpos($dtPat, 'd') === false)
					$dtPat .= 'd';
			}
			if (strpos($dtPat, 'M') === false && strpos($dtPat, 'm') === false && strpos($dtPat, 'F') === false && strpos($dtPat, 'n') === false) {
				if (strpos('FmMn', $dateFormat[$i]) !== false)
					$dtPat .= $dateFormat[$i];
			}
			if (strpos($dtPat, 'Y') === false) {
				if (strpos('oYy', $dateFormat[$i]) !== false)
					$dtPat .= 'Y';
			}
		}
		if (strpos($dtPat, 'D') === false && strpos($dtPat, 'd') === false) {
			$dtPat = 'D' . $dtPat;
		}
		if (strpos($dtPat, 'M') === false && strpos($dtPat, 'm') === false && strpos($dtPat, 'F') === false && strpos($dtPat, 'n') === false) {
			$dtPat = 'M' . $dtPat;
		}
		if (strpos($dtPat, 'Y') === false) {
			$dtPat .= 'Y';
		}
		return $dtPat;
	}

	/**
	 * Get date pattern for picker
	 */
	public static function get_month_names($format) {
		$res = array();
		for ($i = 1; $i <= 12; $i++) {
			$dateObj = DateTime::createFromFormat('!m', $i);
			$res[] = $dateObj->format($format);
		}
		return $res;
	}

	/**
	 * Check if user is in visitors array
	 */
	public static function user_in_array($uid, $array) {
		if (!empty($array))
			foreach($array as $user)
				if (isset($user['ID']) && is_numeric($user['ID']) && $uid == intval($user['ID']))
					return true;
		return false;
	}

	/**
	 * Check if user is super user and can edit others events
	 */
	public static function is_super_mode($user_id) {
		return is_super_admin() && $user_id !== null && $user_id > 0;
	}
}