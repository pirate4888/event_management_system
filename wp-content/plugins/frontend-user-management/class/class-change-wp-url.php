<?php
/**
 * Inspired and copied from http://wordpress.stackexchange.com/questions/78257/changing-wp-login-url-without-htaccess
 * http://en.bainternet.info/2012/wordpress-easy-login-url-with-no-htaccess
 * @author  Christoph Bessei
 * @version 0.01
 */

class Change_Wp_Url {
	private $fum_login_url = '';
	private $fum_register_url = '';

	public function __construct() {
		$this->fum_register_url = get_permalink( get_option( Fum_Conf::get_fum_register_form_name() ) );


		//register url fix
		add_filter( 'register', array( $this, 'change_wp_register_url' ) );

		//login url fix
		add_filter( 'login_url', array( $this, 'change_wp_login_url' ) );
		add_action( 'wp_login_failed', array( $this, 'login_failed_redirect' ) );
		add_filter( 'authenticate', array( $this, 'check_if_username_or_password_empty' ), 10, 3 );

		add_filter( 'logout_url', array( $this, 'my_logout_url' ) );
		add_action( 'pre_get_posts', array( $this, 'search_logout_action' ) );
		/*	//forgot password url fix
			add_filter( 'lostpassword_url', 'fix_lostpass_url' );
			//Site URL hack to overwrite register url
			add_filter( 'site_url', 'fix_urls', 10, 3 );*/
	}

	public function check_if_username_or_password_empty( $user, $username, $password ) {
		if ( 0 === strlen( trim( $username ) ) || 0 === strlen( trim( $password ) ) ) {
			return new WP_Error( 'At least one field was not filled' );
		}
		return $user;
	}

	public function login_failed_redirect() {
		wp_redirect( add_query_arg( array( Fum_Conf::get_fum_login_arg_name() => Fum_Conf::get_fum_login_failed_arg_value() ), wp_login_url() ) );
	}

	/**
	 * Checks if there is action=logout set in the URL, calls wp_logout() and redirects the user to home_url()
	 */
	public function search_logout_action() {
		if ( isset( $_GET[Fum_Conf::get_fum_logout_arg_name()] ) && $_GET[Fum_Conf::get_fum_logout_arg_name()] == Fum_Conf::get_fum_logout_arg_value() ) {
			wp_logout();
			wp_redirect( home_url() );
			exit();
		}
	}

	public function my_logout_url( $link ) {
		return add_query_arg( array( Fum_Conf::get_fum_logout_arg_name() => Fum_Conf::get_fum_logout_arg_value() ), home_url() );
	}

	public function change_wp_login_url( $link ) {
		$permalink = get_permalink( get_option( Fum_Conf::get_fum_login_form_name() ) );

		//Do not show our own login page if we are on a admin page
		if ( is_admin() || $permalink == '' ) {
			return $link;
		}
		return add_query_arg( array( 'previous' => get_permalink() ), $permalink );
	}


	public function change_wp_register_url( $link ) {
		if ( $this->fum_register_url == '' ) {
			return $link;
		}
		return $this->fum_register_url;

	}

	//Site URL hack to overwrite register url
	public function fix_urls( $url, $path, $orig_scheme ) {
		if ( $orig_scheme !== 'login' )
			return $url;
		if ( $path == 'wp-login.php?action=register' )
			return site_url( 'register', 'login' );

		return $url;
	}

	public function fix_lostpass_url( $link ) {
		return str_replace( '?action=lostpassword', '', str_replace( network_site_url( 'wp-login.php', 'login' ), site_url( 'forgot', 'login' ), $link ) );
	}
}