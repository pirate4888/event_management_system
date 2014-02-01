<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Initialisation {
	public static function initiate_plugin() {
		add_shortcode( 'ems_teilnehmerlisten', array( 'Ems_Participant_List_Controller', 'get_participant_lists' ) );
		add_shortcode( 'ems_event_list', array( 'Ems_Event_List_Controller', 'get_event_list' ) );


		self::add_action();
		self::add_filter();
	}


	private static function add_filter() {
		if ( ! is_admin() ) {
			add_filter( 'wp_get_nav_menu_items', array( new Ems_Menu(), 'add_children_to_menu' ) );
		}

	}

	private static function add_action() {
		//Register plugin settings
		add_action( 'admin_init', array( 'Ems_Option_Page_Controller', 'register_settings' ) );
		//Create plugin admin menu page
		add_action( 'admin_menu', array( 'Ems_Option_Page_Controller', 'create_menu' ) );

//		//Register plugin settings
//		add_action( 'admin_init', array( 'Ems_Participant_List_Controller', 'register_settings' ) );
//		//Create plugin admin menu page
//		add_action( 'admin_menu', array( 'Ems_Participant_List_Controller', 'create_menu' ) );

		add_action( 'add_meta_boxes', array( 'Ems_Dhv_Jugend', 'add_meta_box_to_event' ), 10, 2 );
		add_action( 'init', array( 'Ems_Initialisation', 'add_event_post_type' ) );
		add_action( 'do_meta_boxes', array( 'Ems_Dhv_Jugend', 'remove_metabox_layout' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget("Ems_Dhv_Jugend_Widget");' ) );
		add_action( 'admin_enqueue_scripts', array( 'Ems_Dhv_Jugend', 'enqueue_datepicker_jquery' ) );
		add_action( 'save_post', array( 'Ems_Dhv_Jugend', 'save_meta_data_of_event' ) );
	}

	public static function add_event_post_type() {

		register_post_type( Ems_Conf::$ems_custom_event_post_type,
				array(
						'labels'             => array( 'name' => __( 'Events' ) ),
						'public'             => true,
						'publicly_queryable' => true,
						'show_ui'            => true,
						'post_type'          => Ems_Conf::$ems_custom_event_post_type,
						'show_in_menu'       => true,
						'query_var'          => true,
						'rewrite'            => true,
						'capability_type'    => array( 'event', 'events' ),
						'capabilities'       => array(),
						'has_archive'        => false,
						'hierarchical'       => false,
						'supports'           => array( 'title', 'editor', 'custom_fields' ),
				)
		);
	}

	/**
	 * Calls shortcode callback_header function in wp_head, useful for  add styles,scripts, change title, etc.
	 *
	 *
	 * <p>Checks if the current post (it checks the complete WP_Post object) contains a shortcode with the FUM_NAME_PREFIX
	 * If a shortcode is found it takes the callback functionname adds _header and calls it (if it's callable)</p>
	 *
	 * <p><b>Example:</b></p>
	 * <code>add_shortcode('FUM_NAME_PREFIX_test',array('Classname','functionname'));</code>
	 * then the following is called:<br>
	 * <code>call_user_func(array('Classname','functionname_header'));</code>
	 *
	 * <code>add_shortcode('FUM_NAME_PREFIX_test','functionname');</code>
	 * then the following is called:
	 * <code>call_user_func('functionname_header');</code>
	 */
	public static function check_shortcode() {
		global $shortcode_tags;
		foreach ( $shortcode_tags as $shortcode_tag => $callback ) {
			if ( 0 === stripos( $shortcode_tag, Ems_Conf::EMS_NAME_PREFIX ) && has_shortcode( implode( ' ', get_object_vars( get_post() ) ), $shortcode_tag ) ) {
				switch ( count( $callback ) ) {
					case 1:
						$function = (string) $callback;
						$function = $function . '_header';
						if ( is_callable( $function ) ) {
							call_user_func( $function );
						}
						break;
					case 2:
						$class    = $callback[0];
						$function = $callback[1] . '_header';
						$callback = array( $class, $function );
						if ( is_callable( $callback ) ) {
							call_user_func( $callback );
						}
				}
			}
		}
	}
} 