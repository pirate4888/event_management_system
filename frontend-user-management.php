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

		register_activation_hook( __FILE__, array( 'Fum_Activation', 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( 'Fum_Deactivation', 'deactivate_plugin' ) );
		register_uninstall_hook( __FILE__, array( 'Fum_Uninstallation', 'uninstall_plugin' ) );

		Fum_Initialisation::initiate_plugin();
		add_filter( 'tc_post_metas', array( $this, 'remove_meta' ) );
	}

	function remove_meta( $html ) {
		if ( get_post()->post_type == Fum_Conf::$fum_post_type ) {
			return '';
		}
		return $html;
	}

	public
	function autoload( $class_name ) {

		if ( 'Fum_Conf' === $class_name ) {
			require_once( $this->plugin_path . 'fum_conf.php' );
		}

		if ( 'Options' === $class_name ) {
			require_once( $this->plugin_path . 'options.php' );
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
		elseif ( file_exists( $this->plugin_path . 'view/' . $class_name ) ) {
			require_once( $this->plugin_path . 'view/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'view/fum_option_pages/' . $class_name ) ) {
			require_once( $this->plugin_path . 'view/fum_option_pages/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'utility/' . $class_name ) ) {
			require_once( $this->plugin_path . 'utility/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'plugin_management/' . $class_name ) ) {
			require_once( $this->plugin_path . 'plugin_management/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'interface/' . $class_name ) ) {
			require_once( $this->plugin_path . 'interface/' . $class_name );
		}
	}
}

new Frontend_User_Management(); //start plugin

