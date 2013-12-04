<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Redirect {
	public static function redirect_wp_login_php() {

		if ( isset( $_GET['action'] ) ) {
			$action = $_GET['action'];
		}
		else {
			$action = '';
		}
		switch ( $action ) {
			case '':
			case 'register':
				$link = add_query_arg( array_merge( array( 'action' => $action ), $_GET ), get_permalink( get_option( Fum_Conf::$fum_register_login_page_name ) ) );
				wp_safe_redirect( $link );
				exit();
				break;
			case 'lostpassword':
				//TODO Implement it
				break;
		}
	}

	public static function redirect_own_profile_edit() {
		wp_safe_redirect( get_permalink( get_option( Fum_Conf::$fum_edit_page_name ) ) );
		exit();


	}

	public static function redirect_to_home_if_user_cannot_manage_options() {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_event' ) && $_SERVER['PHP_SELF'] != '/wp-admin/profile.php' ) {
			wp_safe_redirect( home_url() );
		}
	}
} 