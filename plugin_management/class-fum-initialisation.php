<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Initialisation {

	public static function initiate_plugin() {
		self::add_action_hooks();
		self::add_filter_hooks();
		add_shortcode( Fum_Conf::$fum_register_login_page_name, array( 'Fum_Register_Login_Form_Controller', 'create_register_login_form' ) );
		add_shortcode( Fum_Conf::$fum_edit_page_name, array( 'Fum_Edit_Form_Controller', 'create_edit_form' ) );
	}

	private static function add_action_hooks() {

		/*class-fum-option-page-controller.php*/

//Register plugin settings
		add_action( 'admin_init', array( 'Fum_Option_Page_Controller', 'register_settings' ) );
//Create plugin admin menu page
		add_action( 'admin_menu', array( 'Fum_Option_Page_Controller', 'create_menu' ) );


		/*			add_action( 'wp_before_admin_bar_render', array( 'Admin_Bar', 'create_admin_bar' ) );
			add_action( 'init', array( 'Admin_Bar', 'show_admin_bar' ) );*/
		add_action( 'init', array( 'Fum_Post', 'fum_register_post_type' ) );
		add_action( 'init', array( new Fum_Front_End_Form(), 'buffer_content_if_front_end_form' ) );


		/*class-activation-email.php*/

//Create activation code on user_register and add it to the user meta
		add_action( 'user_register', array( 'Fum_Activation_Email', 'new_user_registered' ) );

//Check if url contains activation key and if yes, prepend "You have successfully your account etc.."
		add_filter( 'the_content', array( 'Fum_Activation_Email', 'activate_user' ) );

		if ( get_option( Fum_Conf::$fum_register_form_use_activation_mail_option ) ) {
			//Check on login  if user is activated
			add_filter( 'wp_authenticate_user', array( 'Fum_Activation_Email', 'authenticate' ), 10, 1 );
		}

//Delete not activated users, if the home url changes, because the activation link may returns a 404 thenwp_dashboard_setup
		add_action( 'update_option_home', array( 'Fum_Activation_Email', 'delete_not_activated_users' ) );
		add_action( 'update_option_siteurl', array( 'Fum_Activation_Email', 'delete_not_activated_users' ) );


//Redirect wp-admin/profile.php (Only redirect if the user edits his OWN profile!)
		add_action( 'show_user_profile', array( 'Fum_Redirect', 'redirect_own_profile_edit' ) );

		if ( get_option( Fum_Conf::$fum_general_option_group_hide_wp_login_php ) ) {
			//Redirect wp-login.php
			add_action( 'login_init', array( 'Fum_Redirect', 'redirect_wp_login_php' ) );
			//Redirect to home url after logout
			add_action( 'wp_logout', create_function( '', 'wp_redirect(home_url());exit();' ) );
		}

		if ( get_option( Fum_Conf::$fum_general_option_group_hide_dashboard_from_non_admin ) ) {
			add_action( 'wp_dashboard_setup', array( 'Fum_Redirect', 'redirect_to_home_if_user_cannot_manage_options' ) );
		}


	}

	private static function add_filter_hooks() {
		add_filter( 'force_ssl', array( new Fum_Front_End_Form(), 'use_ssl_on_front_end_form' ), 1, 3 );
	}
}