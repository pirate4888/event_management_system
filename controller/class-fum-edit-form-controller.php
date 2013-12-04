<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Edit_Form_Controller {
	public static function  create_edit_form() {
		$error = NULL;

		if ( isset( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] ) ) {
			$request_form_name = $_REQUEST[Fum_Conf::$fum_unique_name_field_name];
		}
		else {
			$request_form_name = false;
		}

		if ( $request_form_name !== false ) {
			switch ( $request_form_name ) {
				case Fum_Conf::$fum_edit_form_unique_name:
					$error = self::validate_edit_form();
					break;
				case Fum_Conf::$fum_change_password_form_unique_name:
					$error = self::validate_new_password_form();
					break;
			}
		}


		if ( is_user_logged_in() ) {
			if ( $request_form_name !== false && $request_form_name == Fum_Conf::$fum_edit_form_unique_name && ! is_wp_error( $error ) ) {
				echo '<p><strong>Profil wurde erfolgreich aktualisiert!</strong></p>';
			}
			else if ( $request_form_name !== false && $request_form_name == Fum_Conf::$fum_change_password_form_unique_name && ! is_wp_error( $error ) ) {

				echo '<p><strong>Passwort erfolgreich geändert!</strong></p>';


				/** @var WP_User $error */
				$credentials[Fum_Conf::$fum_input_field_username] = $error->data->user_login;
				//Set new password as password for the relog
				$credentials[Fum_Conf::$fum_input_field_password] = $_REQUEST[Fum_Conf::$fum_input_field_new_password];
				//User gets automatically logged out after password change, log him in again
				$user = wp_signon( $credentials, true );

				if ( is_wp_error( $user ) ) {
					$error = $user;
				}
				else {
					wp_set_auth_cookie( $user->ID );
				}
			}

			if ( is_wp_error( $error ) ) {
				echo '<p><strong>' . $error->get_error_message() . '</strong></p>';
			}

			if ( $error instanceof Fum_Html_Form ) {
				$form = $error;
			}
			else {
				$form = Fum_Html_Form::get_form( Fum_Conf::$fum_edit_form_unique_name );

				foreach ( $form->get_input_fields() as $input_field ) {
					if ( $input_field->get_type() == Html_Input_Type_Enum::SUBMIT ) {
						continue;
					}
					$input_field->set_value( get_user_meta( get_current_user_id(), $input_field->get_name(), true ) );
				}
			}
			//Change password form
			Fum_Form_View::output( Fum_Html_Form::get_form( Fum_Conf::$fum_change_password_form_unique_name ) );

			//Edit profile form
			Fum_Form_View::output( $form );
		}
		else {
			echo '<p></p><strong>Du musst dich einloggen, bevor du dein Profil bearbeiten kannst</strong></p>';
			echo '<p>' . wp_loginout() . '</p>';
		}
	}

	private static function validate_edit_form() {
		$form = Fum_Html_Form::get_form( $_REQUEST[Fum_Conf::$fum_unique_name_field_name] );
		//Set form values
		foreach ( $form->get_input_fields() as $input_field ) {
			if ( isset( $_REQUEST[$input_field->get_name()] ) ) {
				$input_field->set_value( $_REQUEST[$input_field->get_name()] );
			}
		}
		$error = $form->validate( true );
		if ( is_wp_error( $error ) ) {
			return $form;
		}
		$user      = new WP_User( get_current_user_id() );
		$user_data = array();
		foreach ( $form->get_input_fields() as $input_field ) {
			if ( isset( $_REQUEST[$input_field->get_name()] ) ) {
				//Check if input_field contains the data of a default wordpress user field
				if ( in_array( $input_field->get_name(), Fum_Conf::$fum_wordpress_fields ) ) {
					$user_data[$input_field->get_name()] = $_REQUEST[$input_field->get_name()];
				}
				else {
					update_user_meta( get_current_user_id(), $input_field->get_name(), $_REQUEST[$input_field->get_name()] );
				}
			}
		}
		if ( ! empty( $user_data ) ) {
			wp_update_user( $user_data );
		}
		return NULL;
	}

	private static function validate_new_password_form() {
		$user = get_user_by( 'id', get_current_user_id() );
		$pass = $_REQUEST[Fum_Conf::$fum_input_field_password];

		if ( $user && wp_check_password( $pass, $user->data->user_pass, $user->ID ) ) {
			$new_pass       = $_REQUEST[Fum_Conf::$fum_input_field_new_password];
			$new_pass_check = $_REQUEST[Fum_Conf::$fum_input_field_new_password_check];
			if ( $new_pass == $new_pass_check ) {
				if ( empty( $new_pass ) ) {
					return new WP_Error( 'Das Passwortfeld war leer - leere Passwörter sind leider nicht möglich.', 'Das Passwortfeld war leer - leere Passwörter sind leider nicht möglich.' );
				}
				reset_password( $user, $new_pass );
				return $user;
			}
			else {
				return new WP_Error( 'Die Passwörter stimmen nicht überein', 'Die Passwörter stimmen nicht überein' );
			}
		}
		else {

			return new WP_Error( 'Das aktuelle Passwort stimmt nicht', 'Das aktuelle Passwort stimmt nicht' );
		}
	}
}
