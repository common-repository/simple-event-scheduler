<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Query {

	public static function get_user_events( $user_id, $dtFrom, $dtTo, $timezone ) {
		global $wpdb;
		$UTC = new DateTimeZone('UTC');
		$dtFrom->setTimezone($UTC);
		$dtTo->setTimezone($UTC);
		$dtTo->modify('+1439 minutes');
		$from = $dtFrom->format('Y-m-d H:i');
		$to = $dtTo->format('Y-m-d H:i');
		$date_filter = "pm.meta_value between '$from' and '$to'";

		$query = self::get_events_query( $user_id, $date_filter );
		$events = $wpdb->get_results($query);
		return self::prepare_events($events, $timezone);
	}

	public static function get_user_events_4dates( $user_id, $dates ) {
		global $wpdb;
		$date_filters = array();
		$timezone = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($user_id));
		$UTC = new DateTimeZone('UTC');
		foreach($dates as $date) {
			$dt = date_create_from_format('Y-m-d H:i', $date . ' 00:00', $timezone);
			$dt->setTimezone($UTC);
			$from = $dt->format('Y-m-d H:i');
			$dt->modify('+1439 minutes');
			$to = $dt->format('Y-m-d H:i');
			$date_filters[] = "pm.meta_value between '$from' and '$to'";
		}
		$date_filter = '(' . implode(' or ', $date_filters) . ')';
		$query = self::get_events_query( $user_id, $date_filter );
		$events = $wpdb->get_results($query);

		return self::prepare_events($events, $timezone);
	}

	public static function get_events_query( $user_id, $date_filter ) {
		global $wpdb;
		$where = 'p.post_type = \''.SE_Scheduler::EVENT_POST_TYPE.'\' and p.post_status = \'publish\'';
		if (!empty($user_id))
			$where .= " and p.post_author = $user_id";
		if (!empty($date_filter))
			$where .= " and $date_filter";
		return "SELECT p.ID, p.post_title as `Name`, pm.meta_value as `Start`, pmdur.meta_value as `Duration`, p.post_content as `Visitors`, p.post_author as `UID`, IFNULL(u.display_name, IFNULL(u.user_nicename, u.user_login)) as `UserName` FROM $wpdb->posts p LEFT JOIN $wpdb->users u ON p.post_author = u.ID LEFT JOIN $wpdb->postmeta pm on pm.post_id = p.ID and pm.meta_key = '".SE_Scheduler::EVENT_START_META_NAME."' LEFT JOIN $wpdb->postmeta pmdur on pmdur.post_id = p.ID and pmdur.meta_key = '".SE_Scheduler::EVENT_FIELD_META_NAME."duration' WHERE $where ORDER BY pm.meta_value";
	}

	public static function prepare_events( $events, $timezone ) {
		global $wpdb;
		if (!empty($events) && count($events) > 0) {
			$res = array();

			foreach($events as $event){
				$event->Visitors = maybe_unserialize($event->Visitors);
				$event->VisitorsNames = array();
				$users_ids = array();
				if (!empty($event->Visitors) && is_array($event->Visitors)) {
					foreach($event->Visitors as $user) {
						if (isset($user['ID']) && is_numeric($user['ID']))
							$users_ids[] = intval($user['ID']);
						else if (isset($user['Name']))
							$event->VisitorsNames[] = strval($user['Name']);
					}
				}

				if (!empty($users_ids)) {
					$ids = implode($users_ids, ',');
					$query = "SELECT IFNULL(display_name, IFNULL(user_nicename, user_login)) as `Name` FROM $wpdb->users WHERE ID in ($ids)";
					$users = $wpdb->get_col($query);
					$event->VisitorsNames = array_merge($event->VisitorsNames, $users);
				}

				natcasesort($event->VisitorsNames);
				
				$event->Start = date_create_from_format('Y-m-d H:i', $event->Start, new DateTimeZone('UTC'));
				$event->Start->setTimezone($timezone);

				$dt = $event->Start->format('Y-m-d');
				if (!isset($res[$dt]))
					$res[$dt] = array();
				$res[$dt][] = $event;
			}

			return $res;
		}
		else
			return null;
	}

	public static function set_user_event( $user_id, $date, $time, $save_info ) {
		$super_mode = SE_Scheduler_Utilities::is_super_mode($user_id);
		if (SE_Scheduler_Settings::instance()->se_event_avail || $super_mode) {
			$uid = $super_mode ? $user_id : get_current_user_id();
			if (self::check_user($uid, $user_id))
			{
				$timezone = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($uid));
				$start = date_create_from_format('Y-m-d H:i', $date.' '.$time, $timezone);
				$start->setTimezone(new DateTimeZone('UTC'));
				
				$meta_input = array(SE_Scheduler::EVENT_START_META_NAME => $start->format('Y-m-d H:i'));
				$name_title = '';
				foreach($save_info as $name => $value) {
					if (mb_strtolower($name) == 'name')
						$name_title = $value;
					else
						$meta_input[SE_Scheduler::EVENT_FIELD_META_NAME . mb_strtolower($name)] = $value;
				}

				$user_event = array(
						'post_title'	=> $name_title,
						'post_author'	=> $uid,
						'post_status'	=> 'publish',
						'post_type'		=> SE_Scheduler::EVENT_POST_TYPE
					);
				$event_id = wp_insert_post( $user_event, true );
				if ($event_id > 0) {
					foreach($meta_input as $meta_key => $meta_value) {
						add_post_meta($event_id, $meta_key, $meta_value, true);
					}
					do_action( 'se_event_set', $event_id, $uid, $date, $time, $save_info, array() );
				}
				return $event_id;
			}
		}
		return -1;
	}

	public static function update_user_event( $user_id, $eid, $date, $time, $save_info, &$dates ) {
		$super_mode = SE_Scheduler_Utilities::is_super_mode($user_id);
		if (SE_Scheduler_Settings::instance()->se_event_avail || $super_mode) {
			$uid = $super_mode ? $user_id : get_current_user_id();
			$visitors = array();
			$name = '';
			if (self::check_user($uid, $user_id) && self::check_user_event($uid, $eid, $dates, $name, $visitors))
			{
				$timezone = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($uid));
				$start = date_create_from_format('Y-m-d H:i', $date.' '.$time, $timezone);
				$start->setTimezone(new DateTimeZone('UTC'));
				
				$meta_input = array(SE_Scheduler::EVENT_START_META_NAME => $start->format('Y-m-d H:i'));
				$name_title = '';
				foreach($save_info as $name => $value) {
					if (mb_strtolower($name) == 'name')
						$name_title = $value;
					else
						$meta_input[SE_Scheduler::EVENT_FIELD_META_NAME . mb_strtolower($name)] = $value;
				}

				$user_event = array(
						'ID'			=> $eid,
						'post_title'	=> $name_title
					);
				$event_id = wp_update_post( $user_event );
				if ($event_id > 0) {
					foreach($meta_input as $meta_key => $meta_value) {
						update_post_meta($event_id, $meta_key, $meta_value);
					}
					do_action( 'se_event_update', $event_id, $uid, $date, $time, $save_info, $visitors );
				}
				return $event_id;
			}
		}
		return -1;
	}

	public static function delete_user_event( $user_id, $eid, &$dates ) {
		$super_mode = SE_Scheduler_Utilities::is_super_mode($user_id);
		if (SE_Scheduler_Settings::instance()->se_event_avail || $super_mode) {
			$uid = $super_mode ? $user_id : get_current_user_id();
			$visitors = array();
			$event_name = '';
			if (self::check_user($uid, $user_id) && self::check_user_event($uid, $eid, $dates, $event_name, $visitors))
			{
				$post_meta = get_post_meta($eid);

				$dt = '';
				$date = '';
				$time = '';
				if (isset($post_meta[SE_Scheduler::EVENT_START_META_NAME])) {
					$dt = reset($post_meta[SE_Scheduler::EVENT_START_META_NAME]);
					unset($post_meta[SE_Scheduler::EVENT_START_META_NAME]);
				}
				if (!empty($dt)) {
					$timezone = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($uid));
					$dt = date_create_from_format('Y-m-d H:i', $dt, new DateTimeZone('UTC'));
					$dt->setTimezone($timezone);
					$date = $dt->format('Y-m-d');
					$time = $dt->format('H:i');
				}

				$fields = SE_Scheduler_Settings::instance()->get_fields();
				$save_info = array();
				foreach($fields as $name => $val) {
					if (mb_strtolower($name) == 'name')
						$save_info[$name] = $event_name;
					else {
						$key = SE_Scheduler::EVENT_FIELD_META_NAME . mb_strtolower($name);
						$save_info[$name] = isset($post_meta[$key]) ? reset($post_meta[$key]) : '';
					}
				}
				if ( wp_delete_post( $eid, true ) !== false ) {
					do_action( 'se_event_delete', $eid, $uid, $date, $time, $save_info, $visitors );
					return $eid;
				}
				return 0;
			}
		}
		return -1;
	}

	public static function visit_user_event( $user_id, $eid, &$dates ) {
		if (SE_Scheduler_Settings::instance()->se_event_avail) {
			global $wpdb;
			$query = self::get_events_query( null, 'p.ID='.$eid );
			$events = $wpdb->get_results($query);
			if (!empty($events) && count($events) > 0) {
				$event = reset($events);
				$event->Visitors = maybe_unserialize($event->Visitors);
				if (!is_array ($event->Visitors)) $event->Visitors = array();
				$uid = get_current_user_id();
				$found = false;
				for($i = 0; $i < count($event->Visitors); $i++) {
					if (isset($event->Visitors[$i]['ID']) && is_numeric($event->Visitors[$i]['ID']) && $uid == intval($event->Visitors[$i]['ID'])) {
						array_splice($event->Visitors, $i, 1);
						$found = true;
						break;
					}
				}
				if (!$found)
					$event->Visitors[] = array('ID' => $uid);

				$user_event = array(
						'ID'			=> $eid,
						'post_content'	=> maybe_serialize($event->Visitors)
					);
				$event_id = wp_update_post( $user_event );
				if ($event_id > 0) {
					$date = '';
					$time = '';
					if (!empty($event->Start)) {
						$timezone = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($event->UID));
						$dt = date_create_from_format('Y-m-d H:i', $event->Start, new DateTimeZone('UTC'));
						$dt->setTimezone($timezone);
						$date = $dt->format('Y-m-d');
						$time = $dt->format('H:i');
					}
					if ($found)
						do_action( 'se_event_cancel_visit', $event_id, $event->UID, $uid, $date, $time, $event->Name, $event->Visitors );
					else
						do_action( 'se_event_visit', $event_id, $event->UID, $uid, $date, $time, $event->Name, $event->Visitors );
				}
				return $event_id;
			}
		}
		return -1;
	}

	public static function update_user_timezone( $user_id, $timezone ) {
		if (SE_Scheduler_Settings::instance()->se_timezone_avail) {
			$uid = SE_Scheduler_Utilities::is_super_mode($user_id) ? $user_id : get_current_user_id();
			if (self::check_user($uid, $user_id))
			{
				if ( update_user_meta( $uid, SE_Scheduler::USER_TIMEZONE_META_NAME, $timezone ) == true ) {
					do_action( 'se_timezone_update', $uid, $timezone );
					return true;
				}
			}
		}
		return false;
	}

	public static function check_user($uid, $user_id){
		if (empty($uid))
			return false;
		return $user_id == 0 || $user_id == $uid;
	}

	public static function check_user_event($uid, $eid, &$dates, &$name, &$visitors){
		global $wpdb;
		$query = "SELECT p.ID, p.post_title as `Name`, p.post_content as `Visitors`, pm.meta_value as `Start` FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm on pm.post_id = p.ID and pm.meta_key = '".SE_Scheduler::EVENT_START_META_NAME."' WHERE p.ID = $eid AND p.post_author = $uid";
		$event = $wpdb->get_row($query);
		$timezone = new DateTimeZone(SE_Scheduler_Utilities::get_user_timezone($uid));
		if (!empty($event)) {
			if (!empty($event->Start)) {
				$dt = date_create_from_format('Y-m-d H:i', $event->Start, new DateTimeZone('UTC'));
				$dt->setTimezone($timezone);
				$date = $dt->format('Y-m-d');
				if (!in_array($date, $dates))
					$dates[] = $date;
			}
			if (!empty($event->Visitors)) {
				$visitors = maybe_unserialize($event->Visitors);
			}
			$name = $event->Name;
			return true;
		}
		return false;
	}
}