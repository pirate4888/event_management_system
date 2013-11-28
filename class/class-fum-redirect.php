<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Redirect {
	public static function redirect_wp_login() {


		//First check if action=register or action= password and if not, redirect to login form
		add_rewrite_rule( '^wp-login\.php.*$', get_permalink( get_option( Fum_Conf::$fum_login_form_name ) ) . '?%{QUERY_STRING}', 'top' );

		flush_rewrite_rules();
	}

} 