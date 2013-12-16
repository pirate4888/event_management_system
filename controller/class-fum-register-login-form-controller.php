<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Register_Login_Form_Controller {

	public static function create_register_login_form() {
		$error = NULL;
		if ( isset( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] ) ) {
			if ( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] === Fum_Conf::$fum_login_form_unique_name ) {
				$error = self::validate_login_form();
			}
			else if ( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] === Fum_Conf::$fum_register_form_unique_name ) {
				$error = self::validate_register_form();
			}
		}

		//Print if unique_name_Field was not found OR $error != NULL
		if ( ! isset( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] ) || $error != NULL ) {
			if ( is_wp_error( $error ) ) {
				echo $error->get_error_message();
			}

			if ( isset( $_GET['action'] ) ) {
				$action = $_GET['action'];
			}
			else {
				$action = '';
			}
			switch ( $action ) {
				case 'register':
					if ( is_user_logged_in() ) {
						echo '<strong>Da du eingeloggt bist, kannst du dich nicht registrieren</strong>';
					}
					else {
						$form = Fum_Html_Form::get_form( Fum_Conf::$fum_register_form_unique_name );
						if ( get_option( Fum_Conf::$fum_register_form_generate_password_option ) ) {
							Fum_Form_View::output( $form );
						}
						else {
							$password_field = Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_password );
							$form->insert_input_field_after_unique_name( $password_field, Fum_Conf::$fum_input_field_username );
							Fum_Form_View::output( $form );

						}
					}
					break;
				case 'lostpassword':
					echo 'lostpassword';
					break;
				case 'logout':
					wp_logout();
					break;
				default:
					if ( is_user_logged_in() ) {
						echo '<strong> Du bist bereits einloggt</strong>';
					}
					else {
						Fum_Form_View::output( Fum_Html_Form::get_form( Fum_Conf::$fum_login_form_unique_name ) );
						if ( isset( $_REQUEST['interim-login'] ) && $_REQUEST['interim-login'] == 1 ):
							?>
							<script type="text/javascript">
								function wp_attempt_focus() {
									setTimeout(function () {
										try {
											d = document.getElementById('password');
											d.value = '';
											d.focus();
											d.select();
										} catch (e) {
										}
									}, 200);
								}

								if (typeof wpOnload == 'function')wpOnload();
								(function () {
									try {
										var i, links = document.getElementsByTagName('a');
										for (i in links) {
											if (links[i].href)
												links[i].target = '_blank';
										}
									} catch (e) {
									}
								}());
							</script><?php

						endif;
					}

			}
		}
		else {
			echo '<p><strong>Registrierung erfolgreich</strong></p>';
			echo 'Wir haben dir eine E-Mail mit deinem Passwort geschickt. <br/>';
			echo 'Sollte diese in den nächsten Minuten nicht ankommen, übperüfe bitte deinen SPAM-Ordner.';
		}
	}


	private static function validate_login_form() {
		$error = NULL;
		$user  = wp_signon( $_REQUEST, true );
		if ( is_wp_error( $user ) ) {
			$error = $user;
		}
		else {
			wp_set_auth_cookie( $user->ID );
			wp_safe_redirect( admin_url( 'profile.php' ) );
		}
		return $error;
	}

	private static function validate_register_form() {
		$error = NULL;

		if ( get_option( Fum_Conf::$fum_register_form_generate_password_option ) ) {
			//Wordpress uses sometimes user_pass (wp_insert_usert) and sometimes user_password, this is a workaround for this problem
			$_REQUEST['user_pass']                         = wp_generate_password();
			$_REQUEST[Fum_Conf::$fum_input_field_password] = $_REQUEST['user_pass'];

		}

		$ID   = wp_insert_user( $_REQUEST );
		$form = Fum_Html_Form::get_form( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] );
		if ( is_wp_error( $ID ) ) {
			$error = $ID;
			//Set values so the formular is filled

			foreach ( $form->get_input_fields() as $input_field ) {
				$input_field->set_value( $_REQUEST[$input_field->get_name()] );
			}
		}
		else {
			//Registration was successful, write special user fields to database
			foreach ( $form->get_input_fields() as $input_field ) {
				if ( isset( $_REQUEST[$input_field->get_name()] ) ) {
					update_user_meta( $ID, $input_field->get_name(), $_REQUEST[$input_field->get_name()] );
				}
			}
		}
		return $error;
	}
}