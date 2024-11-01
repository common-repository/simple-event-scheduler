<?php

class SE_Scheduler_Admin_Updates {

	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check_update'));
	}

	public static function check_update( $transient ) {
		$version = SE_Scheduler::get_version();
		if (empty(SE_Scheduler::HOME_SITE_URL)) {
			return $transient;
		}

		$response = wp_remote_get( SE_Scheduler::HOME_SITE_URL . '?action=get_updates&version=' . $version );

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			return $transient;
		}

		$plugins = json_decode( $response['body'] );
		if ( is_array( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				$plugin_name = sprintf( '%s/%s.php', $plugin->slug, $plugin->slug );
				$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_name;

				if ( ! file_exists( $plugin_path ) ) {
					continue;
				}

				if ( empty( $plugin->current_version ) ) {
					continue;
				}

				$plugin_data = get_plugin_data( $plugin_path );
				$version     = version_compare( $plugin_data['Version'], $plugin->current_version, '<' );

				if ( $version ) {
					$obj					= new stdClass();
					$obj->id				= 0;
					$obj->slug				= $plugin->slug;
					$obj->plugin			= $plugin_name;
					$obj->new_version		= $plugin->current_version;
					$obj->url				= $plugin->url;
					$obj->package			= $plugin->package;
					$obj->upgrade_notice	= $plugin->notice;

					$transient->response[ $plugin_name ] = $obj;
				}
			}
		}

		return $transient;
	}
}

SE_Scheduler_Admin_Updates::init();