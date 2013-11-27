<?php
/**
 * @author  Christoph Bessei
 * @version 0.01
 */

class Registration_Form {
	public function get_register_form() {

		$errors    = new WP_Error();
		$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
		if ( is_multisite() ) {
			$sign_up_url = network_site_url( 'wp-signup.php' );
			/**
			 * Filter the Multisite sign up URL.
			 *
			 * @since 3.0.0
			 *
			 * @param string $sign_up_url The sign up URL.
			 */
			wp_redirect( apply_filters( 'wp_signup_location', $sign_up_url ) );
			exit;
		}

		if ( ! get_option( 'users_can_register' ) ) {
			wp_redirect( site_url( 'wp-login.php?registration=disabled' ) );
			exit();
		}

		if ( isset( $_POST["register_form_sent"] ) ) {
			//Check if we should generate a password or if the user should enter one
			if ( get_option( Fum_Conf::get_fum_register_form_generate_password_option() ) ) {
				$_POST['user_pass'] = wp_generate_password();
			}

			error_log( "USER_PASS: " . $_POST['user_pass'] );
			$return = wp_insert_user( $_POST );

			if ( is_wp_error( $return ) ) {
				echo $return->get_error_message();
			}
			else {
				echo __( 'Registrierung war erfolgreich' ) . '<br/>';
				echo __( 'Bevor du dich einloggen kannst musst deinen Account aktivieren. Dafür wurde dir eine E-Mail mit einem Aktivierungslink geschickt' );
			}
		}
		else {
			$user_login = '';
			$user_email = '';
			if ( $http_post ) {
				$user_login = $_POST['user_login'];
				$user_email = $_POST['user_email'];
			}
			$registration_redirect = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
			/**
			 * Filter the registration redirect URL.
			 *
			 * @since 3.0.0
			 *
			 * @param string $registration_redirect The redirect destination URL.
			 */
			$redirect_to = apply_filters( 'registration_redirect', $registration_redirect );
			?>


		<?php
		}
		//Flush output, needed because we buffer the formulas so we can sent headers
		ob_flush();
	}
} 