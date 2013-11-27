<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Action_Hooks {
	public static function add_action_hooks( $user_hooks = true, $admin_hooks = true ) {
		if ( $user_hooks ) {
			add_action( 'wp_before_admin_bar_render', array( 'Admin_Bar', 'create_admin_bar' ) );
			add_action( 'init', array( 'Admin_Bar', 'show_admin_bar' ) );
			add_action( 'init', array( new Fum_Post(), 'fum_register_post_type' ) );
			add_action( 'init', array( new Front_End_Form(), 'buffer_content_if_front_end_form' ) );
		}

		if ( $admin_hooks ) {
			add_action( 'admin_init', array( 'Option_Page_Controller', 'register_settings' ) );
			add_action( 'admin_menu', array( 'Option_Page_Controller', 'create_menu' ) );
		}
	}
} 