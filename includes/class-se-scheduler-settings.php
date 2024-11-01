<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SE_Scheduler_Settings {

	private static $instance = null;
	
	public static function instance() {
		if (self::$instance == null) {
			$init = array(
							'se_date_format' => get_option('date_format'),
							'se_time_format' =>  get_option('time_format'),
							'se_days_count' => 7,
							'se_event_avail' => true,
							'se_timezone_avail' => true,
							'se_style_eventcalendar' => '',
							'se_style_modal' => '',
							'se_text_eventaddheader' => __( 'Create an Event', 'se-scheduler' ),
							'se_text_eventeditheader' => __( 'Edit an Event', 'se-scheduler' ),
							'se_text_eventsubmit' => __( 'Save', 'se-scheduler' ),
					);
			$init_fields = array(
							'name'		=> array('title' => __( 'Name', 'se-scheduler' ), 'elem' => '<input id="se-name" name="se-name" type="text" maxlength="200" required="required" />', 'with_err' => true),
							'duration'	=> array('title' => __( 'Duration', 'se-scheduler' ), 'elem' => '<input id="se-duration" name="se-duration" type="text" maxlength="100" />', 'with_err' => false),
						);
			$init_fields = apply_filters( 'se_fields', $init_fields );
			self::$instance = new SE_Scheduler_Settings($init, $init_fields);
		}
		return self::$instance;
	}

	private $defVals = array();
	
	private $fields = array();
	
	private function __construct($init, $init_fields) {
		$this->defVals = $init;
		$this->fields = $init_fields;
	}

	protected function __clone() {}

	public function __get($name) {
		if (array_key_exists($name, $this->defVals)) {
			return apply_filters( 'se_settings', $this->defVals[$name], $name );
		}
        return null;
    }
	
	public function get_default($name) {
		if (array_key_exists($name, $this->defVals))
			return $this->defVals[$name];
		return '';
	}
	
	public function get_fields() {
		return $this->fields;
	}
}