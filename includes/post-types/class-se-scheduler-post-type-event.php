<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SE_Scheduler_Post_Type_Event {
    /**
     * Initialize custom post type
     */
    public static function init() {        
        add_action( 'init', array( __CLASS__, 'definition' ), 11 );
    }

    /**
     * Custom post type definition
     */
    public static function definition() {
		$labels = array(
			'name'                  => __( 'Events', 'se-scheduler' ),
			'singular_name'         => __( 'Event', 'se-scheduler' ),
			'add_new'               => __( 'Add New Event', 'se-scheduler' ),
			'add_new_item'          => __( 'Add New Event', 'se-scheduler' ),
			'edit_item'             => __( 'Edit Event', 'se-scheduler' ),
			'new_item'              => __( 'New Event', 'se-scheduler' ),
			'all_items'             => __( 'Events', 'se-scheduler' ),
			'view_item'             => __( 'View Event', 'se-scheduler' ),
			'search_items'          => __( 'Search Event', 'se-scheduler' ),
			'not_found'             => __( 'No Events found', 'se-scheduler' ),
			'not_found_in_trash'    => __( 'No Events Found in Trash', 'se-scheduler' ),
			'parent_item_colon'     => '',
			'menu_name'             => __( 'Events', 'se-scheduler' ),
		);

		register_post_type( SE_Scheduler::EVENT_POST_TYPE,
			array(
				'labels'            => $labels,
				'show_in_menu'	    => false,
				'supports'          => array( null ),
				'public'            => false,
				'has_archive'       => false,
				'show_ui'           => false,
				'categories'        => array(),
				'capabilities'		=> array(
										'create_posts'	=> false,
									),
				'map_meta_cap'		=> true
			)
		);
	}
}

SE_Scheduler_Post_Type_Event::init();