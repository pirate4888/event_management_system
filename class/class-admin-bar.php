<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Admin_Bar {
	public function __construct() {
		//TODO Currently the Login/Logout etc can only be at the admin bar, make it more flexible
		show_admin_bar( true );
	}

	public function create_admin_bar() {

		if ( is_super_admin() ) {
			return;
		}

		if ( is_user_logged_in() ) {
			$loginout_link  = wp_logout_url( get_permalink() );
			$loginout_title = "Ausloggen";
		}
		else {
			$loginout_link  = wp_login_url( site_url( get_permalink() ) );
			$loginout_title = "Einloggen";
		}


		global $wp_admin_bar;
		$this->clear_admin_bar( $wp_admin_bar );
		$wp_admin_bar->add_menu( array(
			'id'    => 1,
			'href'  => $loginout_link,
			'title' => $loginout_title,
		) );
		if ( ! is_user_logged_in() ) {
			$wp_admin_bar->add_menu( array(
				'id'    => 2,
				'href'  => wp_register( '', '', false ),
				'title' => __( 'Registrieren' ),
			) );
		}
		else {
			$wp_admin_bar->add_menu( array(
				'id'    => 2,
				'href'  => get_permalink( get_option( Fum_Conf::get_fum_edit_form_name() ) ),
				'title' => __( 'Profil editieren' ),
			) );
		}
	}

	private function clear_admin_bar( $admin_bar ) {

		$nodes = $admin_bar->get_nodes();
		foreach ( $nodes as $node ) {
			$admin_bar->remove_node( $node->id );
		}
	}
}
