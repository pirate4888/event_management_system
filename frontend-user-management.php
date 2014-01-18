<?php
/**
 * Plugin Name: Frontend User Management
 * Plugin URI: https://github.com/SchwarzwaldFalke/frontend-user-management
 * Description: Plugin which allows user to register, login and edit their user profile in frontend. It also adds activation mails during user registration
 * Version: 0.03
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */

class Frontend_User_Management {
	private $option_fum_category_id = 'fum_category';
	private static $plugin_path = NULL;

	public function __construct() {


		spl_autoload_register( array( $this, 'autoload' ) );
		//Set path to plugin dir
		Frontend_User_Management::$plugin_path = plugin_dir_path( __FILE__ );

		//Add Github Updater
		if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin
			$config = array(
				'slug'               => plugin_basename( __FILE__ ), // this is the slug of your plugin
				'proper_folder_name' => 'frontend-user-management', // this is the name of the folder your plugin lives in
				'api_url'            => 'https://api.github.com/repos/SchwarzwaldFalke/frontend_user_management', // the github API url of your github repo
				'raw_url'            => 'https://raw.github.com/SchwarzwaldFalke/frontend_user_management/master', // the github raw url of your github repo
				'github_url'         => 'https://github.com/SchwarzwaldFalke/frontend_user_management', // the github url of your github repo
				'zip_url'            => 'https://github.com/SchwarzwaldFalke/frontend_user_management/archive/master.zip', // the zip url of the github repo
				'sslverify'          => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
				'requires'           => '3.7', // which version of WordPress does your plugin require?
				'tested'             => '3.8', // which version of WordPress is your plugin tested up to?
				'readme'             => 'README.MD' // which file to use as the readme for the version number
			);
			new WP_GitHub_Updater( $config );
		}


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

	public function autoload( $class_name ) {

		if ( 'Fum_Conf' === $class_name ) {
			require_once( Frontend_User_Management::$plugin_path . 'fum_conf.php' );
		}

		if ( 'Options' === $class_name ) {
			require_once( Frontend_User_Management::$plugin_path . 'options.php' );
		}

		if ( 'WP_GitHub_Updater' === $class_name ) {
			require_once( Frontend_User_Management::$plugin_path . 'updater.php' );
		}

		//Because of sucking wordpress name conventions class name != file name, convert it manually
		$class_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) . '.php' );
		if ( file_exists( Frontend_User_Management::$plugin_path . 'class/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'class/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'controller/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'controller/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'model/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'model/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'view/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'view/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'view/fum_option_pages/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'view/fum_option_pages/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'utility/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'utility/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'plugin_management/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'plugin_management/' . $class_name );
		}
		elseif ( file_exists( Frontend_User_Management::$plugin_path . 'interface/' . $class_name ) ) {
			require_once( Frontend_User_Management::$plugin_path . 'interface/' . $class_name );
		}
	}

	/**
	 * @return null|string
	 */
	public static function get_plugin_path() {
		return self::$plugin_path;
	}


}

new Frontend_User_Management(); //start plugin

