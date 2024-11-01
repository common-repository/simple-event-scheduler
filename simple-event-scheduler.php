<?php

/**
 *	Plugin Name: Simple Event Scheduler
 *	Plugin URI: http://se-scheduler.website.tk
 *	Description: Create and manage events scheduling for registered site users and presenting it as a calendar.
 *	Text Domain: se-scheduler
 *	Version: 1.0.0
 *	Author: Andrey Denisov <seschedulerplugin@gmail.com>
 *	License: GPL2
 */
 
 /*  Copyright 2018 Andrey Denisov (email:andreyvdenisov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'SE_Scheduler' ) ) {

	final class SE_Scheduler {
		const HOME_SITE_URL = '';

		const USER_TIMEZONE_META_NAME = 'se_scheduler_timezone';

		const EVENT_POST_TYPE = 'se_scheduler_event';
		const EVENT_START_META_NAME = 'se_scheduler_evst';
		const EVENT_FIELD_META_NAME = 'se_scheduler_ev';

		/**
		 * Initialize plugin
		 */
		public function __construct() {
			$this->constants();
			$this->includes();

			add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );
		}

		/**
		 * Defines constants
		 */
		public function constants() {
			define( 'SE_SCHEDULER_DIR', plugin_dir_path( __FILE__ ) );
			define( 'SE_SCHEDULER_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Include classes
		 */
		public function includes() {
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-settings.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-utilities.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-cache.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-scripts.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-captcha.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-post-types.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-query.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-shortcodes.php';
			require_once SE_SCHEDULER_DIR . 'includes/class-se-scheduler-logic.php';

			// Admin
			if ( is_admin() ) {
				require_once SE_SCHEDULER_DIR . 'includes/admin/class-se-scheduler-admin-menu.php';
				require_once SE_SCHEDULER_DIR . 'includes/admin/class-se-scheduler-admin-updates.php';
			}
		}

		/**
		 * Loads localization files
		 */
		public static function load_plugin_textdomain() {
			$path = plugin_basename( dirname( __FILE__ ) ) . '/languages';
			load_plugin_textdomain( 'se-scheduler', false, $path );
		}
		
		/**
		 * Gets the plugin version
		 */
		private static $version = '';
		public static function get_version() {
			if (empty(self::$version)) {
				$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
				self::$version = $plugin_data['Version'];
			}
			return self::$version;
		}
	}

	new SE_Scheduler();
}
