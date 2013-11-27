<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Front_End_Form {
	public function add_form_posts() {
		$fum_post = new Fum_Post();
		$fum_post->fum_register_post_type();
		$register_id = $fum_post->add_post( Fum_Conf::get_fum_register_form_name(), 'Register', '[' . Fum_Conf::get_fum_register_form_name() . ']' );
		$login_id    = $fum_post->add_post( Fum_Conf::get_fum_login_form_name(), 'Login', '[' . Fum_Conf::get_fum_login_form_name() . ']' );
		$edit_id     = $fum_post->add_post( Fum_Conf::get_fum_edit_form_name(), 'Edit', '[' . Fum_Conf::get_fum_edit_form_name() . ']' );
		return array(
			Fum_Conf::get_fum_register_form_name() => $register_id,
			Fum_Conf::get_fum_login_form_name()    => $login_id,
			Fum_Conf::get_fum_edit_form_name()     => $edit_id,
		);
	}

	public function add_shortcode_of_register_form( $shortcode ) {
		add_shortcode( $shortcode, array( $this, 'get_register_form' ) );
	}

	public function add_shortcode_of_login_form( $shortcode ) {
		add_shortcode( $shortcode, array( $this, 'get_login_form' ) );
	}

	public function add_shortcode_of_edit_form( $shortcode ) {
		add_shortcode( $shortcode, array( $this, 'get_edit_form' ) );
	}

	public function add_shortcode_of_logout_form( $shortcode ) {
		add_shortcode( $shortcode, array( $this, 'get_edit_form' ) );
	}


	/**
	 * Buffer the content if the post is a front end form
	 * This is necessary because we may use wp_redirect()
	 */
	public function buffer_content_if_front_end_form() {
		ob_start();
	}


	public function get_login_form() {

		if ( isset( $_GET[Fum_Conf::get_fum_login_arg_name()] ) && Fum_Conf::get_fum_login_failed_arg_value() == $_GET[Fum_Conf::get_fum_login_arg_name()] ) {
			echo '<p><strong>' . __( 'Ist der Benutzer aktiviert(E-Mails checken?) <br/>Benutzername oder Passwort falsch' ) . '</strong></p>';
		}

		if ( is_user_logged_in() ) {
			echo __( 'Already logged in.' );
		}
		else {
			//TODO Check if 'previous' is a url from our domain..better for security
			if ( isset( $_GET['previous'] ) ) {
				$redirect = $_GET['previous'];
			}
			else {
				if ( wp_get_referer() ) {
					$redirect = wp_get_referer();
				}
				else {
					$redirect = home_url();
				}
			}

		}
	}

	public function get_edit_form( $user_id = null ) {

		if ( ! is_user_logged_in() ) {
			echo "You have to be logged in to edit your profile";
			return;
		}

		global $userdata, $wp_http_referer;
		get_currentuserinfo();

		if ( ! ( function_exists( 'get_user_to_edit' ) ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}

		if ( ! ( function_exists( '_wp_get_user_contactmethods' ) ) ) {
			require_once( ABSPATH . '/wp-includes/registration.php' );
		}

		if ( ! $user_id ) {
			$current_user = wp_get_current_user();
			$user_id      = $user_ID = $current_user->ID;
		}
		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'update-profile_' . $user_id );
			$errors = edit_user( $user_id );
			if ( is_wp_error( $errors ) ) {
				$message = $errors->get_error_message();
				$style   = 'error';
			}
			else {
				$message = '<strong>Profil erfolgreich gespeichert</strong>';
				$style   = 'success';
				do_action( 'personal_options_update', $user_id );
			}
		}

		$profileuser = get_user_to_edit( $user_id );

		if ( isset( $message ) ) {
			echo '<div class="' . $style . '">' . $message . '</div>';
		}
		$dhv_jugend_form = new Dhv_Jugend_Form();
		echo $dhv_jugend_form->get_form( $user_id );
	}

	private function getCustomFields() {

	}

	public function use_ssl_on_front_end_form( $force_ssl ) {
		global $post;

		$custom_post_type = Fum_Conf::get_fum_post_type();

		$post_type = get_post_type( $post );


		if ( force_ssl_login() && $post_type === $custom_post_type ) {
			return true;
		}
		return $force_ssl;
	}
}