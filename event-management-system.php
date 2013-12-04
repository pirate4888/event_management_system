<?php
/**
 * Plugin Name: Event Management System
 * Plugin URI: https://github.com/SchwarzwaldFalke/event_management_system
 * Description: Plugin which allows management (create, edit, delete, event registration, participant list, Task reminder, automated news, etc.
 * Version: 0.01
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */

class Event_Management_System {

	private static $plugin_path = NULL;
	private static $plugin_url = NULL;

	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );
		//Set path to plugin dir
		Event_Management_System::$plugin_path = plugin_dir_path( __FILE__ );
		Event_Management_System::$plugin_url  = plugin_dir_url( __FILE__ );


		Ems_Dhv_Jugend::init_plugin();
		register_activation_hook( __FILE__, array( 'Ems_Dhv_Jugend', 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( 'Ems_Dhv_Jugend', 'deactivate_plugin' ) );
		register_uninstall_hook( __FILE__, array( 'Ems_Dhv_Jugend', 'uninstall_plugin' ) );

	}


	public function autoload( $class_name ) {


		//Because of sucking wordpress name conventions class name != file name, convert it manually
		$class_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) . '.php' );
		if ( file_exists( Event_Management_System::$plugin_path . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . 'controller/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . 'controller/' . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . 'model/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . 'model/' . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . 'view/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . 'view/' . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . 'utility/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . 'utility/' . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . 'plugin_management/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . 'plugin_management/' . $class_name );
		}
	}

	public static function get_plugin_path() {
		return self::$plugin_path;
	}

	/**
	 * @return null
	 */
	public static function get_plugin_url() {
		return self::$plugin_url;
	}

}

new Event_Management_System(); //start plugin

