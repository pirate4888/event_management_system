<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Action_Hooks {
	public static function add_action_hooks( $user_hooks = true, $admin_hooks = true ) {
		if ( $user_hooks ) {
		}

		if ( $admin_hooks ) {
			/*class-option-page-controller.php*/

			//Register plugin settings
			add_action( 'admin_init', array( 'Option_Page_Controller', 'register_settings' ) );
			//Create plugin admin menu page
			add_action( 'admin_menu', array( 'Option_Page_Controller', 'create_menu' ) );
		}

		/*			add_action( 'wp_before_admin_bar_render', array( 'Admin_Bar', 'create_admin_bar' ) );
			add_action( 'init', array( 'Admin_Bar', 'show_admin_bar' ) );*/
		add_action( 'init', array( new Fum_Post(), 'fum_register_post_type' ) );
		add_action( 'init', array( new Front_End_Form(), 'buffer_content_if_front_end_form' ) );

		/*class-activation-email.php*/

		//Create activation code on user_register and add it to the user meta
		add_action( 'user_register', array( 'Activation_Email', 'new_user_registered' ) );

		//Check if url contains activation key and if yes, prepend "You have successfully your account etc.."
		add_filter( 'the_content', array( 'Activation_Email', 'activate_user' ) );

		//Check on login  if user is activated
		add_filter( 'wp_authenticate_user', array( 'Activation_Email', 'authenticate' ), 10, 1 );

		//Delete not activated users, if the home url changes, because the activation link may returns a 404 then
		add_action( 'update_option_home', array( 'Activation_Email', 'delete_not_activated_users' ) );
		add_action( 'update_option_siteurl', array( 'Activation_Email', 'delete_not_activated_users' ) );

		//TODO Flush is not working correctly
		if ( get_option( 'hide_wp_login_register' ) == 1 ) {
			add_action( 'init', array( 'Fum_Redirect', 'redirect_wp_login' ) );
		}
		else {
			add_action( 'init', 'flush_rewrite_rules' );
		}
	}
} 