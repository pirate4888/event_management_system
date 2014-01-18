<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Ems_Option_Page_Controller {

	public static $parent_slug = 'ems';
	/** @var  Ems_Option_Page[] $pages */
	public static $pages;

	public static function create_menu() {

		/** @var Ems_Option_Page[] $pages */
		$pages = array();

		//Add General Settings Page
		$page = new Ems_Option_Page( 'ems_general_settings_page', 'Allgemeine Einstellungen' );

		//Add General Settings Ems_Option Group
		$option_group = new Ems_Option_Group( 'ems_option_group' );
		$options      = array();

		//Create hide wordpress login and register page checkbox
		$name  = Ems_Conf::$ems_general_option_show_events_in_menu;
		$title = 'Zeige Events automatisch unter folgendem Menüpunkt';

		$description = 'Fügt alle Events automatisch zum angegebenen Menüpunkt' . "\n" . ' ( "Angezeigter Name" des Menüpunkt) hinzu. Der Menüpunkt muss bereits existieren!';
		$description = esc_attr( $description );

		//Add option to option_group
		$options[] = new Ems_Option( $name, $title, $description, get_option( Ems_Conf::$ems_general_option_show_events_in_menu ), $option_group, 'text' );

		//Create hide wordpress login and register page checkbox
		$name  = 'ems_git_branch';
		$title = 'Welche Version soll verwendet werden (Experimental beinhaltet nicht geteste Versionen!)';

		$description = 'Welche Version soll verwendet werden (Experimental beinhaltet nicht geteste Versionen!), im Zweifelsfall immer stable benutzen';
		$description = esc_attr( $description );

		//Add option to option_group
		$option = new Ems_Option( $name, $title, $description, get_option( Ems_Conf::$ems_general_option_show_events_in_menu ), $option_group, 'select' );
		$option->set_possible_values( array( 'stable', 'experimental' ) );
		$option->set_value( get_option( 'ems_git_branch' ) );
		$options[] = $option;


		//Add created options to $option_group and register $option_group
		$option_group->set_options( $options );

		//Add all option groups to page
		$page->add_option_group( $option_group );


		//Add page to page array
		$pages[] = $page;


		self::$pages = $pages;

		//Add main menu
		add_menu_page( 'Event Management System', 'Event Management System', 'manage_options', self::$parent_slug, array( 'Ems_Option_Page_View', 'print_option_page' ) );
		//Add first submenu to avoid duplicate entries: http://wordpress.org/support/topic/top-level-menu-duplicated-as-submenu-in-admin-section-plugin
		add_submenu_page( self::$parent_slug, $pages[0]->get_title(), self::$pages[0]->get_title(), 'manage_options', self::$parent_slug );
		//remove first submenu because we used this already
		unset( $pages[0] );

		foreach ( $pages as $page ) {

			add_submenu_page( self::$parent_slug, $page->get_title(), $page->get_title(), 'manage_options', $page->get_name(), array( 'Ems_Option_Page_View', 'print_option_page' ) );
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