<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Option_Page_Controller {

	public static $parent_slug = 'fum';
	public static $pages;

	public static function register_option_pages() {

	}

	public static function create_menu() {

		$pages = array();

		//Add General Settings Page
		$page = new Option_Page( 'general_settings_page', 'Allgemeine Einstellungen' );

		//Add General Settings Option Group
		$option_group = new Option_Group( 'general_settings_group' );
		$options      = array();

		//Create hide wordpress login and register page checkbox
		$name        = 'hide_wp_login_register';
		$title       = 'Verstecke das Wordpress Login - und Registrierungsformular';
		$description = 'Versteckt alle Wordpress Login und Registrierungsformulare, Login ist dann nur noch über die Frontend User Management Formulare mögliche';
		//Add option to option_group
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'checkbox' );

		//Create option
		$name      = 'test_field';
		$title     = __( 'Versuche ein Textfeld', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'password' );

		//Create option
		$name      = 'test_field1';
		$title     = __( 'Versuche ein Textfeld1', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'text' );

		//Create option
		$name      = 'test_field2';
		$title     = __( 'Versuche ein Textfeld2', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'textarea' );

		//Create option
		$name      = 'test_field3';
		$title     = __( 'Versuche ein Textfeld2', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'radio', array( 'Mastercard', 'Visa', 'Dieter' ) );
		//Create option
		$name      = 'test_field4';
		$title     = __( 'Versuche ein Textfeld2', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'select', array( 'Mastercard', 'Visa', 'Dieter' ) );

		//Add created options to $option_group and register $option_group
		$option_group->set_options( $options );

		//Add all option groups to page
		$page->add_option_group( $option_group );


		//Add page to page array
		$pages[] = $page;

		//Create admin menu page
		$page = new Option_Page( 'specific_settings_page', 'Spezielle Einstellungen' );

		//Create option group
		$option_group = new Option_Group( 'specific_settings_group' );
		$options      = array();

		//Create option
		$name  = 'hide_wp_login_register';
		$title = 'Verstecke das Wordpress Login - und Registrierungsformular';
		//Add option to option_group
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'checkbox' );

		//Create option
		$name      = 'test_field';
		$title     = __( 'Versuche ein Textfeld', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'password' );

		//Create option
		$name      = 'test_field1';
		$title     = __( 'Versuche ein Textfeld1', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'text' );

		//Create option
		$name      = 'test_field2';
		$title     = __( 'Versuche ein Textfeld2', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'textarea' );

		//Create option
		$name      = 'test_field3';
		$title     = __( 'Versuche ein Textfeld2', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'radio', array( 'Mastercard', 'Visa', 'Dieter' ) );
		//Create option
		$name      = 'test_field4';
		$title     = __( 'Versuche ein Textfeld2', 'fum_text_domain' );
		$options[] = new Option( $name, $title, $description, get_option( $name ), $option_group, 'select', array( 'Mastercard', 'Visa', 'Dieter' ) );

		//Add created options to $option_group and register $option_group
		$option_group->set_options( $options );

		//Add all option groups to page
		$page->add_option_group( $option_group );


		//Add page to page array
		$pages[] = $page;


		self::$pages = $pages;

		//Add main menu
		add_menu_page( 'Frontend User Management', 'Frontend User Management', 'manage_options', self::$parent_slug, array( 'Option_Page_View', 'print_option_page' ) );
		//Add first submenu to avoid duplicate entries: http://wordpress.org/support/topic/top-level-menu-duplicated-as-submenu-in-admin-section-plugin
		add_submenu_page( self::$parent_slug, $pages[0]->get_title(), $pages[0]->get_title(), 'manage_options', self::$parent_slug );
		//remove first submenu because we used this already
		unset( $pages[0] );

		foreach ( $pages as $page ) {
			/*@var $page Option_Page */
			add_submenu_page( self::$parent_slug, $page->get_title(), $page->get_title(), 'manage_options', $page->get_name(), array( 'Option_Page_View', 'print_option_page' ) );
		}
	}


	public static function register_settings() {
		$pages = self::$pages;
		for ( $i = 0; $i < count( $pages ); $i ++ ) {
			$page = $pages[$i];
			foreach ( $page->get_option_groups() as $option_group ) {
				foreach ( $option_group->get_options() as $option ) {
					register_setting( $option_group->get_name(), $option->get_name() );
				}
			}
		}
	}
}