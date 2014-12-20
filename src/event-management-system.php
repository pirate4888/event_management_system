<?php

class Event_Management_System {

	private static $plugin_path = null;
	private static $plugin_url = null;
	private static $src_directories = array(
		'controller',
		'../lib',
		'model',
		'view',
		'utility',
		'plugin_management',
		'../../../../wp-includes',
		'abstract',
		'interface',
	);

	public function __construct( $plugin_path = null, $plugin_url = null ) {

		//add_filter( 'fum_option_page_entries', array( $this, 'remove_fum_option_page' ) );

		spl_autoload_register( array( $this, 'autoload' ) );


		Event_Management_System::$plugin_path = plugin_dir_path( __FILE__ );
		Event_Management_System::$plugin_url  = plugin_dir_url( __FILE__ );

		Ems_Initialisation::initiate_plugin();


		//Add Github Updater
		if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin
			$branch = 'master';
			if ( false !== get_option( 'ems_git_branch' ) && get_option( 'ems_git_branch' ) == 'experimental' ) {
				$branch = 'experimental';
			}
			$config = array(
				'slug' => $plugin_path,
				// this is the slug of your plugin
				'proper_folder_name' => 'event-management-system',
				// this is the name of the folder your plugin lives in
				'api_url'            => 'https://api.github.com/repos/SchwarzwaldFalke/event_management_system',
				// the github API url of your github repo
				'raw_url'            => 'https://raw.github.com/SchwarzwaldFalke/event_management_system/' . $branch,
				// the github raw url of your github repo
				'github_url'         => 'https://github.com/SchwarzwaldFalke/event_management_system',
				// the github url of your github repo
				'zip_url'            => 'https://github.com/SchwarzwaldFalke/event_management_system/archive/' . $branch . '.zip',
				// the zip url of the github repo
				'sslverify'          => true,
				// wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
				'requires'           => '3.7',
				// which version of WordPress does your plugin require?
				'tested'             => '4.1',
				// which version of WordPress is your plugin tested up to?
				'readme'             => 'README.MD'
				// which file to use as the readme for the version number
			);
			new WP_GitHub_Updater( $config );
			echo "Called WP_GitHub_Updater";
		}


	}

//	public function remove_fum_option_page( $options ) {
//		return array();
//	}


	public function autoload( $class_name ) {

		//Because of sucking wordpress name conventions class name != file name, convert it manually
		$class_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) . '.php' );
		if ( file_exists( Event_Management_System::$plugin_path . $class_name ) ) {
			require_once( Event_Management_System::$plugin_path . $class_name );

			return;
		}

		foreach ( self::$src_directories as $dir ) {
			$dir  = trailingslashit( $dir );
			$path = Event_Management_System::$plugin_path . $dir . $class_name;
			if ( file_exists( $path ) ) {
				require_once( $path );

				return;
			}
		}
	}

	public
	static function get_plugin_path() {
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

