<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Logic {
	const nonceAjaxName = 'se-scheduler';
	
	static $addEventCalendar = false; 
	
	/**
	* Initialize functionality
	*/
	public static function init() {
		add_action( 'wp_ajax_save_event', array( __CLASS__, 'save_event_callback') );

		add_action( 'wp_ajax_del_event', array( __class__, 'del_event_callback') );
		
		add_action( 'wp_ajax_visit_event', array( __class__, 'visit_event_callback') );
		
		add_action( 'wp_ajax_save_timezone', array( __CLASS__, 'save_timezone_callback') );
		
		add_action( 'wp_ajax_get_sesdata', array( __CLASS__, 'get_sesdata_callback') );
		add_action( 'wp_ajax_nopriv_get_sesdata', array( __CLASS__, 'get_sesdata_callback') );

		add_action( 'wp_footer', array( __CLASS__, 'footer_callback') );
		add_action( 'admin_footer', array( __CLASS__, 'footer_callback') );
	}

	/**
	* Adds scripts, hidden elements and modal forms
	*/
	public static function footer_callback() {
		$output = '';

		if (SE_Scheduler_Logic::$addEventCalendar) {
			if (SE_Scheduler_Settings::instance()->se_event_avail || is_super_admin()) {
				$dtPat = SE_Scheduler_Utilities::get_picker_pattern();
				$monthsAr = array();
				if (strpos($dtPat, 'M') !== false)
					$monthsAr = SE_Scheduler_Utilities::get_month_names('M');
				elseif (strpos($dtPat, 'm') !== false)
					$monthsAr = SE_Scheduler_Utilities::get_month_names('m');
				elseif (strpos($dtPat, 'F') !== false)
					$monthsAr = SE_Scheduler_Utilities::get_month_names('F');
				elseif (strpos($dtPat, 'n') !== false)
					$monthsAr = SE_Scheduler_Utilities::get_month_names('n');
				$output .= 'ses_months = [\''.implode('\',\'', $monthsAr).'\'];';
				echo SE_Scheduler_Utilities::load( 'content-modal' );
			}
			else
				$output .= 'ses_months = [];';

			$sec = wp_create_nonce(SE_Scheduler_Logic::nonceAjaxName);
			$output .= 'ses_ajax_url = \'' . admin_url('admin-ajax.php') . '\';ses_sec = \'' . $sec . '\';jQuery(function() { window.SESHED.init(); });';
		}
		if (!empty($output))
			echo '<script>' . $output . '</script>';
	}
	
	public static function del_event_callback() {
		$result = false;
		$resAr = array('status'=>'error');

		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : -1;
		$eid = isset($_GET['eid']) && ctype_digit($_GET['eid']) ? intval($_GET['eid']) : -1;
		$sec = isset($_GET['sec']) ? sanitize_text_field($_GET['sec']) : '';

		if ( $eid > 0 && $uid >= 0 && SE_Scheduler_Utilities::check_date($dt) && wp_verify_nonce($sec, SE_Scheduler_Logic::nonceAjaxName)) {
			$dates = array($dt);
			$eventid = SE_Scheduler_Query::delete_user_event($uid, $eid, $dates);
			if ($eventid <= 0)
				wp_die('', '', array('response' => 500));

			self::fill_events_blocks($uid, $dates, false, $resAr);

			$resAr['status'] = 'ok';
			$result = true;
		}

		echo(json_encode( $resAr ));
		wp_die();
	}

	public static function visit_event_callback() {
		$result = false;
		$resAr = array('status'=>'error');

		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : -1;
		$eid = isset($_GET['eid']) && ctype_digit($_GET['eid']) ? intval($_GET['eid']) : -1;
		$sec = isset($_GET['sec']) ? sanitize_text_field($_GET['sec']) : '';

		if ( $eid > 0 && $uid >= 0 && SE_Scheduler_Utilities::check_date($dt) && wp_verify_nonce($sec, SE_Scheduler_Logic::nonceAjaxName)) {
			$dates = array($dt);
			$eventid = SE_Scheduler_Query::visit_user_event($uid, $eid, $dates);
			if ($eventid <= 0)
				wp_die('', '', array('response' => 500));

			self::fill_events_blocks($uid, $dates, false, $resAr);

			$resAr['status'] = 'ok';
			$result = true;
		}

		echo(json_encode( $resAr ));
		wp_die();
	}

	public static function save_event_callback() {
		$result = false;
		$resAr = array('status'=>'error');

		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$tm = isset($_GET['tm']) ? sanitize_text_field($_GET['tm']) : '';
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : -1;
		$eid = isset($_GET['eid']) && ctype_digit($_GET['eid']) ? intval($_GET['eid']) : -1;
		$prefix = isset($_GET['prefix']) ? sanitize_text_field($_GET['prefix']) : '';
		$captcha = isset($_GET['captcha']) ? sanitize_text_field($_GET['captcha']) : '';
		$sec = isset($_GET['sec']) ? sanitize_text_field($_GET['sec']) : '';

		$fields = SE_Scheduler_Settings::instance()->get_fields();
		$save_info = array();
		foreach($fields as $name => $field) {
			if (isset($_GET[$name])) {
				$save_info[$name] = sanitize_text_field($_GET[$name]);
			}
		}

		$c = new SE_Scheduler_Captcha();
		$captchaOK = $c->check( $prefix, $captcha );
		$c->remove( $prefix );
		
		if ($captchaOK && $uid >= 0 && SE_Scheduler_Utilities::check_date($dt) && SE_Scheduler_Utilities::check_time($tm) && !empty($sec) && wp_verify_nonce($sec, SE_Scheduler_Logic::nonceAjaxName)) {
			$dates = array($dt);
			$eventid = $eid > 0 ? SE_Scheduler_Query::update_user_event($uid, $eid, $dt, $tm, $save_info, $dates) : SE_Scheduler_Query::set_user_event($uid, $dt, $tm, $save_info);
			if ($eventid <= 0)
				wp_die('', '', array('response' => 500));

			self::fill_events_blocks($uid, $dates, true, $resAr);

			$resAr['status'] = 'ok';
			$result = true;
		}

		if (!$result && !$captchaOK) {
			self::add_captcha($resAr);
		}
		echo(json_encode( $resAr ));
		wp_die();
	}
	
	public static function save_timezone_callback() {
		$result = false;
		$resAr = array('status'=>'error');

		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : -1;
		$tz = isset($_GET['tz']) ? sanitize_text_field($_GET['tz']) : '';
		$sec = isset($_GET['sec']) ? sanitize_text_field($_GET['sec']) : '';

		if ($uid >= 0 && !empty($sec) && wp_verify_nonce($sec, SE_Scheduler_Logic::nonceAjaxName)) {
			if (!SE_Scheduler_Query::update_user_timezone($uid, $tz))
				wp_die('', '', array('response' => 500));

			$resAr['status'] = 'ok';
			$result = true;
		}

		echo(json_encode( $resAr ));
		wp_die();
	}

	public static function get_sesdata_callback() {
		$uid = isset($_GET['uid']) && ctype_digit($_GET['uid']) ? intval($_GET['uid']) : -1;
		$dt = isset($_GET['dt']) ? sanitize_text_field($_GET['dt']) : '';
		$dc = isset($_GET['dc']) && ctype_digit($_GET['dc']) ? intval($_GET['dc']) : SE_Scheduler_Settings::instance()->se_days_count;
		$dts = isset($_GET['dts']) ? sanitize_text_field($_GET['dts']) : '';
		$tp = isset($_GET['tp']) && ctype_digit($_GET['tp']) ? intval($_GET['tp']) : 0; // 2 - get events, 4 - get week name, 8 - get captcha
		$resAr = array('status'=>'ok');
		if ($tp > 0) {
			$uidOK = $uid >= 0;
			$dtOK = SE_Scheduler_Utilities::check_date($dt);
			$dtsOK = !empty($dts);
			$dates = null;
			if ($dtsOK) {
				$dates = explode(',', $dts);
				foreach($dates as $date) {
					if (!SE_Scheduler_Utilities::check_date($date)){
						$dtsOK = false;
						break;
					}
				}
			}

			if (($tp & 2) != 0) {
				if ($uidOK && $dtsOK && $dtOK) {
					self::fill_events_blocks($uid, $dates, true, $resAr);
				}
				else
					$resAr['status'] = 'error';
			}

			if (($tp & 4) != 0) {
				if ($dtOK)
					$resAr['wn'] = SE_Scheduler_Utilities::get_eventcal_header( date_create_from_format('Y-m-d H:i', $dt . ' 00:00') );
				else
					$resAr['status'] = 'error';
			}

			if (($tp & 8) != 0) {
				self::add_captcha($resAr);
			}
		}
		else
			$resAr['status'] = 'error';
		
		echo(json_encode( $resAr ));
		wp_die();
	}

	public static function fill_events_blocks($uid, $dates, $addDn, &$resAr) {
		$allevents = SE_Scheduler_Query::get_user_events_4dates($uid, $dates);
		$resAr['cal'] = array();
		if ($addDn)
			$resAr['dn'] = array();
		$timeFormat = SE_Scheduler_Settings::instance()->se_time_format;
		$current_user_id = get_current_user_id();
		$super_mode = SE_Scheduler_Utilities::is_super_mode($uid);
		$is_avail = SE_Scheduler_Settings::instance()->se_event_avail;
		foreach($dates as $dt) {
			$events = isset($allevents[$dt]) ? $allevents[$dt] : array();
			$attrs = array('current_user_id' => $current_user_id, 'is_avail' => $is_avail, 'super_mode' => $super_mode, 'timeFormat' => $timeFormat, 'events' => $events);
			$resAr['cal'][$dt] = SE_Scheduler_Utilities::load( 'content-events', $attrs );
			if ($addDn)
				$resAr['dn'][$dt] = SE_Scheduler_Utilities::get_day_header( date_create_from_format('Y-m-d H:i', $dt . ' 00:00') );
		}
	}

	public static function add_captcha(&$ar) {
		$captcha = new SE_Scheduler_Captcha();
		$word = $captcha->generate_random_word();
		$prefix = mt_rand();
		$filename = $captcha->generate_image( $prefix, $word );
		$filePath = substr( trailingslashit( $captcha->tmp_dir ) . $filename, strlen( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
		$ar['path'] = $filePath;
		$ar['prefix'] = $prefix;
	}
}

SE_Scheduler_Logic::init();
