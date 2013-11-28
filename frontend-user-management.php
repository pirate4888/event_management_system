<?php
/**
 * Plugin Name: Frontend User Management
 * Plugin URI: https://github.com/SchwarzwaldFalke/frontend-user-management
 * Description: Plugin which allows user to register, login and edit their user profile. It also adds activation mails during user registration
 * Version: 0.01
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */

class Frontend_User_Management {
	private $option_fum_category_id = 'fum_category';
	private $plugin_path = NULL;

	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );
		//Set path to plugin dir
		$this->plugin_path = plugin_dir_path( __FILE__ );

		register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' ) );

		$this->init_plugin();

		Action_Hooks::add_action_hooks();
		Filter_Hooks::add_filter_hooks();
	}

	private function init_plugin() {

		//Add ShortCodes of user forms(register,login,edit)
		$front_end_form = new Front_End_Form();
		$front_end_form->add_shortcode_of_register_form( Fum_Conf::$fum_register_form_shortcode );
		$front_end_form->add_shortcode_of_login_form( Fum_Conf::$fum_login_form_shortcode );
		$front_end_form->add_shortcode_of_edit_form( Fum_Conf::$fum_edit_form_shortcode );
	}

	public function plugin_activate() {
		$front_end_form = new Front_End_Form();
		$post_ids       = $front_end_form->add_form_posts();
		add_option( Fum_Conf::$fum_register_form_name, $post_ids[Fum_Conf::$fum_register_form_name] );
		add_option( Fum_Conf::$fum_login_form_name, $post_ids[Fum_Conf::$fum_login_form_name] );
		add_option( Fum_Conf::$fum_edit_form_name, $post_ids[Fum_Conf::$fum_edit_form_name] );

		$activation_email = new Activation_Email();
		$activation_email->plugin_activated();
	}

	public function plugin_deactivate() {
		$fum_post = new Fum_Post();
		$fum_post->remove_all_fum_posts();

		delete_option( Fum_Conf::$fum_register_form_name );
		delete_option( Fum_Conf::$fum_login_form_name );
		delete_option( Fum_Conf::$fum_edit_form_name );
		$activation_email = new Activation_Email();
		$activation_email->plugin_deactivated();
	}

	public function autoload( $class_name ) {
		if ( 'Fum_Conf' === $class_name ) {
			require_once( $this->plugin_path . 'fum_conf.php' );
		}

		if ( 'Options' === $class_name ) {
			require_once( $this->plugin_path . 'options.php' );
		}
		if ( 'Option_Page_Controller' === $class_name ) {
			require_once( $this->plugin_path . 'controller/class-option-page-controller.php' );
		}
		//Because of sucking wordpress name conventions class name != file name, convert it manually
		$class_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) . '.php' );

		if ( file_exists( $this->plugin_path . 'class/' . $class_name ) ) {
			require_once( $this->plugin_path . 'class/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'controller/' . $class_name ) ) {
			require_once( $this->plugin_path . 'controller/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'model/' . $class_name ) ) {
			require_once( $this->plugin_path . 'model/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'views/' . $class_name ) ) {
			require_once( $this->plugin_path . 'views/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'views/fum_option_pages/' . $class_name ) ) {
			require_once( $this->plugin_path . 'views/fum_option_pages/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'utility/' . $class_name ) ) {
			require_once( $this->plugin_path . 'utility/' . $class_name );
		}
	}
}


new Frontend_User_Management(); //start plugin

