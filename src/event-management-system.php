<?php

/**
 * Plugin Name: Event Management System
 * Plugin URI: https://github.com/SchwarzwaldFalke/event_management_system
 * Description: Plugin which allows management (create, edit, delete, event registration, participant list, Task reminder, automated news, etc.
 * Version: 0.03
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */
class Event_Management_System {

	private static $plugin_path = NULL;
	private static $plugin_url = NULL;

	public function __construct() {

		//Load frontend-user-management
		require_once( "frontend-user-management/frontend-user-management.php" );
		new Frontend_User_Management();
		add_filter( 'fum_option_page_entries', array( $this, 'remove_fum_option_page' ) );


		spl_autoload_register( array( $this, 'autoload' ) );
		//Set path to plugin dir
		Event_Management_System::$plugin_path = plugin_dir_path( __FILE__ );
		Event_Management_System::$plugin_url  = plugin_dir_url( __FILE__ );

		Ems_Initialisation::initiate_plugin();

		register_activation_hook( __FILE__, array( 'Ems_Activation', 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( 'Ems_Deactivation', 'deactivate_plugin' ) );
		register_uninstall_hook( __FILE__, array( 'Ems_Uninstallation', 'uninstall_plugin' ) );

		//Add Github Updater
		if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin
			$branch = 'master';
			if ( false !== get_option( 'ems_git_branch' ) && get_option( 'ems_git_branch' ) == 'experimental' ) {
				$branch = 'experimental';
			}
			$config = array(
					'slug'               => plugin_basename( __FILE__ ), // this is the slug of your plugin
					'proper_folder_name' => 'event-management-system', // this is the name of the folder your plugin lives in
					'api_url'            => 'https://api.github.com/repos/SchwarzwaldFalke/event_management_system', // the github API url of your github repo
					'raw_url'            => 'https://raw.github.com/SchwarzwaldFalke/event_management_system/' . $branch, // the github raw url of your github repo
					'github_url'         => 'https://github.com/SchwarzwaldFalke/event_management_system', // the github url of your github repo
					'zip_url'            => 'https://github.com/SchwarzwaldFalke/event_management_system/archive/' . $branch . '.zip', // the zip url of the github repo
					'sslverify'          => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
					'requires'           => '3.7', // which version of WordPress does your plugin require?
					'tested'             => '3.8', // which version of WordPress is your plugin tested up to?
					'readme'             => 'README.MD' // which file to use as the readme for the version number
			);
			new WP_GitHub_Updater( $config );
		}


	}

	public function remove_fum_option_page( $options ) {
		return array();
	}


	public function autoload( $class_name ) {

		if ( 'WP_GitHub_Updater' === $class_name ) {
			require_once( '../lib/updater.php' );
		}

		//Because of sucking wordpress name conventions class name != file name, convert it manually
		$class_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) . '.php' );
		if ( file_exists( Event_Management_System::$plugin_path . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . 'controller/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . 'controller/' . $class_name );
		}
		elseif ( file_exists( Event_Management_System::$plugin_path . '../lib/' . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . '../lib/' . $class_name );
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

