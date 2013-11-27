<?php
/**
 * @author  Christoph Bessei
 * @version 0.01
 */

class Activation_Email {
	private $activation_key_field = 'fum_user_activation_key';
	private $active_user_value = 'active';


	public function __construct() {

		//since 0.01
		//Create activation code on user_register and add it to the user meta
		add_action( 'user_register', array( $this, 'new_user_registered' ) );

		//Check if url contains activation key and if yes, prepend "You have successfully your account etc.."
		add_filter( 'the_content', array( $this, 'activate_user' ) );

		//Check on login  if user is activated
		add_filter( 'wp_authenticate_user', array( $this, 'authenticate' ), 10, 1 );

		//Add action if permalink structure, site url or home url changes, delete not activated users then
		//because the sent activation link is invalid now (it points to the home url)

		//https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/misc.php
		add_action( 'update_option_home', array( $this, 'delete_not_activated_users' ) );
		add_action( 'update_option_siteurl', array( $this, 'delete_not_activated_users' ) );
	}

	public function plugin_activated() {
		//Get all user IDs and do not sort them, we do not need a sorted result and it just costs time
		$options  = array( 'fields' => 'ID' );
		$user_IDs = get_users( $options );

		//add the activation key field to all current users and mark them as activated
		foreach ( $user_IDs as $user_ID ) {

			//Check if meta key already exists, this is possible if the plugin was only deactivated and now gets reactivated
			$activation_key_field_exists = get_user_meta( $user_ID, $this->activation_key_field, true );
			if ( ! empty( $activation_key_field_exists ) ) {
				break;
			}
			add_user_meta( $user_ID, $this->activation_key_field, $this->active_user_value );
		}
	}

	public function plugin_deactivated() {
		$this->delete_not_activated_users();

		//Get all user IDs and do not sort them, we do not need a sorted result and it just costs time
		$options  = array( 'fields' => 'ID' );
		$user_IDs = get_users( $options );
		// remove activation key field from all users
		foreach ( $user_IDs as $user_ID ) {
			delete_user_meta( $user_ID, $this->activation_key_field );
		}
	}

	/**
	 * Deletes users who are not activated
	 */
	private function delete_not_activated_users() {
		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		$options = array( 'fields'       => 'ID',
											'meta_key'     => $this->activation_key_field,
											'meta_value'   => $this->active_user_value,
											'meta_compare' => '!=',
		);

		$user_IDs = get_users( $options );
		foreach ( $user_IDs as $user_ID ) {
			wp_delete_user( $user_ID );
		}
	}

	public function new_user_registered( $user_id ) {
		//Create new activation key and add it to the user meta
		$activation_key = $this->create_activation_key();
		update_user_meta( $user_id, $this->activation_key_field, $activation_key );

		$this->fum_new_user_notification( $user_id, $_POST['user_pass'] );
	}

	private function create_activation_key() {
		//Generate random activation key, without special characters (has to be url friendly)
		return wp_generate_password( 15, false );
	}

	//TODO Make activation link more secure with an extra field which contains the username, so we do not check every activation code
	public function activate_user( $content ) {
		if ( isset( $_GET[$this->activation_key_field] ) ) {
			$url_activation_key = $_GET[$this->activation_key_field];
			//Get all user IDs
			$options  = array( 'fields' => 'ID' );
			$user_ids = get_users( $options );
			foreach ( $user_ids as $user_id ) {
				if ( update_user_meta( $user_id, $this->activation_key_field, $this->active_user_value, $url_activation_key ) ) {
					return '<strong>' . __( 'Activation of user was successful' ) . '</strong>' . $content;
				}
			}
			return '<strong>' . __( 'Invalid activation key' ) . '</strong>' . $content;
		}
		return $content;
	}

	public function authenticate( $user ) {
		//Authentication already failed, just return $user
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		if ( get_user_meta( $user->ID, $this->activation_key_field, true ) === $this->active_user_value ) {
			return $user;
		}
		return new WP_Error( 'not activated', __( 'User was not activated, have you check your mails for the activation link?' ) );
	}

	private function fum_new_user_notification( $user_id, $password = false ) {
		$user            = new WP_User( $user_id );
		$activation_code = get_user_meta( $user->ID, $this->get_activation_key_field(), true );

		//Send activation link only if the admin wants to use activation links, otherwise activate user directly
		if ( get_option( Fum_Conf::get_fum_register_form_use_activation_mail_option() ) ) {
			$send_activation_link = true;
		}
		else {
			update_user_meta( $user_id, $this->get_activation_key_field(), $this->get_active_user_value() );
			$send_activation_link = false;
		}

		//Send password only if it was generated randomly
		if ( ! get_option( Fum_Conf::get_fum_register_form_generate_password_option() ) ) {
			$password = false;
		}
		//Send the welcome mail only if it contains useful informations (activation link and/or password)
		if ( false === $password && false === $send_activation_link ) {
			return;
		}

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		$message = sprintf( __( 'New user registration on your blog %s:', 'frontend-user-management' ), get_option( 'blogname' ) ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'frontend-user-management' ), $user_login ) . "\r\n";
		$message .= sprintf( __( 'E-mail: %s', 'frontend-user-management' ), $user_email ) . "\r\n";
		if ( false !== $password ) {
			$message .= sprintf( __( 'Password: %s', 'frontend-user-management' ), $password ) . "\r\n";
		}
		if ( false !== $send_activation_link ) {
			$message .= sprintf( __( 'Activation Link: %s', 'frontend-user-management' ), get_home_url() . "?" . $this->get_activation_key_field() . "=" . $activation_code ) . "\r\n";
		}

		wp_mail( $user_email, sprintf( __( 'Welcome to %s', 'frontend-user-management' ), get_option( 'blogname' ) ), $message );
	}

	/**
	 * Returns the field name where the activation key is stored in the user meta
	 * @return string
	 */
	public function get_activation_key_field() {
		return $this->activation_key_field;
	}

	/**
	 * @return string
	 */
	public function get_active_user_value() {
		return $this->active_user_value;
	}
}

if ( ! function_exists( 'wp_new_user_notification' ) ) :
//Overwrite wp_new_user_notification from pluggable.php
//TODO else: throw exception that there is a plugin conflict because two plugins overwrite wp_new_user_notification
endif;