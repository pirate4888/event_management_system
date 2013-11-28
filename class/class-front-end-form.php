<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Front_End_Form {
	public function add_form_posts() {
		$fum_post = new Fum_Post();
		$fum_post->fum_register_post_type();
		$register_id = $fum_post->add_post( Fum_Conf::$fum_register_form_name, 'Register', '[' . Fum_Conf::$fum_register_form_name . ']' );
		$login_id    = $fum_post->add_post( Fum_Conf::$fum_login_form_name, 'Login', '[' . Fum_Conf::$fum_login_form_name . ']' );
		$edit_id     = $fum_post->add_post( Fum_Conf::$fum_edit_form_name, 'Edit', '[' . Fum_Conf::$fum_edit_form_name . ']' );
		return array(
			Fum_Conf::$fum_register_form_name => $register_id,
			Fum_Conf::$fum_login_form_name    => $login_id,
			Fum_Conf::$fum_edit_form_name     => $edit_id,
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


	public function get_register_form() {

		echo '<pre>';
		$user = get_userdata( 1 );
		$user->data;
		print_r( $user->data );
		echo '</pre>';
		echo '<pre>';
		print_r( wp_get_user_contact_methods( 1 ) );
		echo '</pre>';
		echo '<pre>';
		print_r( get_user_option( 1 ) );
		echo '</pre>';
	}

	public function get_login_form() {

		if ( isset( $_POST['wp-submit'] ) ) {
			wp_signon();
		}
		if ( isset( $_GET[Fum_Conf::$fum_login_arg_name] ) && Fum_Conf::$fum_login_failed_arg_value == $_GET[Fum_Conf::$fum_login_arg_name] ) {
			echo '<p><strong>' . __( 'Ist der Benutzer aktiviert(E-Mails checken?) <br/>Benutzername oder Passwort falsch' ) . '</strong></p>';
		}

		if ( is_user_logged_in() ) {
			echo __( 'Already logged in.' );
			echo '<pre>';
			print_r( $_GET );
			echo '</pre>';
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
			$form = '
			<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( get_permalink() ) . '" method="post">
					' . apply_filters( 'login_form_top', '', $args ) . '
			<p class="login-username">
				<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
				<input type="text" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" size="20" />
			</p>
			<p class="login-password">
				<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
				<input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" size="20" />
			</p>
			' . apply_filters( 'login_form_middle', '', $args ) . '
			' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
			<p class="login-submit">
				<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="button-primary" value="' . esc_attr( $args['label_log_in'] ) . '" />
				<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
			</p>
			' . apply_filters( 'login_form_bottom', '', $args ) . '
			</form>';
			echo $form;
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
				/*@var $errors WP_Error */
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

		$custom_post_type = Fum_Conf::$fum_post_type;

		$post_type = get_post_type( $post );


		if ( force_ssl_login() && $post_type === $custom_post_type ) {
			return true;
		}
		return $force_ssl;
	}
}