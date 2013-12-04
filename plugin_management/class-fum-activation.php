<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Activation {

	public static function activate_plugin() {
		$front_end_form = new Fum_Front_End_Form();
		$post_ids       = $front_end_form->add_form_posts();
		add_option( Fum_Conf::$fum_register_login_page_name, $post_ids[Fum_Conf::$fum_register_login_page_name] );
		add_option( Fum_Conf::$fum_edit_page_name, $post_ids[Fum_Conf::$fum_edit_page_name] );

		Fum_Activation_Email::plugin_activated();

		/*Set default value of options*/

		//Disable activation emails by default
		if ( false === get_option( Fum_Conf::$fum_register_form_use_activation_mail_option ) ) {
			update_option( Fum_Conf::$fum_register_form_use_activation_mail_option, 0 );
		}

		//Let wordpress generate the password of new user
		if ( false === get_option( Fum_Conf::$fum_register_form_generate_password_option ) ) {
			update_option( Fum_Conf::$fum_register_form_generate_password_option, 1 );
		}

		//Do NOT hide wp-login.php by default
		if ( false === get_option( Fum_Conf::$fum_general_option_group_hide_wp_login_php ) ) {
			update_option( Fum_Conf::$fum_general_option_group_hide_wp_login_php, 0 );
		}

		self::create_default_input_fields();
		/*		echo '<pre>';
				print_r( Fum_Html_Input_Field::get_all_input_fields() );
				echo '</pre>';*/


		self::create_login_form();
		self::create_register_form();
		self::create_change_password_form();
		self::create_edit_form();
		//self::create_event_register_form();
	}


	private static function create_event_register_form() {
		$form         = Fum_Html_Form::get_form( Fum_Conf::$fum_event_register_form_unique_name );
		$input_fields = $form->get_input_fields();
		if ( Fum_Html_Form::is_unique_name_already_used( $form->get_unique_name() ) ) {
			Fum_Html_Form::delete_form( $form );
		}
		Fum_Html_Form::add_form( $form );
	}


	private static function create_default_input_fields() {

		//Text input fields
		$all_input_fields = array(
			Html_Input_Type_Enum::TEXT     => array(
				//Default input field names
				'fum_input_field_username'                   => array( 'Username', true ),
				'fum_input_field_email'                      => array( 'E-Mail', true, array( 'Fum_Html_Input_Field', 'mail_address_callback' ) ),
				'fum_input_field_last_name'                  => array( 'Nachname', false ),
				'fum_input_field_first_name'                 => array( 'Vorname', false ),
				'fum_input_field_website'                    => array( 'Website', false ),
				'fum_input_field_display_name'               => array( 'Öffentlicher Name', false ),

				//DHV-Jugend input field names
				'fum_input_field_birthday'                   => array( 'Geburtstag', false ),
				'fum_input_field_street'                     => array( 'Straße', false ),
				'fum_input_field_city'                       => array( 'Stadt', false ),
				'fum_input_field_postcode'                   => array( 'Postleitzahl', false ),
				'fum_input_field_state'                      => array( 'Bundesland', false ),
				'fum_input_field_phone_number'               => array( 'Telefonnummer', false ),
				'fum_input_field_mobile_number'              => array( 'Handynummer', false ),
				'fum_input_field_dhv_member_number'          => array( 'DHV MitgliedsNr.', false ),
				'fum_input_field_license_number'             => array( 'Lizenznummer', false ),
				'fum_input_field_aircraft'                   => array( 'Fluggerät', false ),

				'fum_input_field_emergency_contact_surname'  => array( 'Notfallkontakt Nachname', false ),
				'fum_input_field_emergency_contact_forename' => array( 'Notfallkontakt Vorname', false ),
				'fum_input_field_emergency_phone_number'     => array( 'Notfallkontakt Telefonnummer', false ),
			),
			Html_Input_Type_Enum::PASSWORD => array(
				'fum_input_field_password'           => array( 'Password', true ),
				'fum_input_field_new_password'       => array( 'New password', true ),
				'fum_input_field_new_password_check' => array( 'Confirm new password', true ),
			),
			Html_Input_Type_Enum::CHECKBOX => array(),
			Html_Input_Type_Enum::SUBMIT   => array(
				'fum_input_field_submit' => array( 'Abschicken', false ),
			)
		);

		foreach ( $all_input_fields as $type => $input_fields ) {
			foreach ( $input_fields as $name => $input_field ) {
				if ( Fum_Html_Input_Field::is_unique_name_used( Fum_Conf::$$name ) ) {
					Fum_Html_Input_Field::delete_input_field( Fum_Conf::$$name );
				}
				$field = new Fum_Html_Input_Field( Fum_Conf::$$name, Fum_Conf::$$name, new Html_Input_Type_Enum( $type ), $input_field[0], Fum_Conf::$$name, $input_field[1] );
				if ( isset( $input_field[2] ) ) {
					$field->set_validate_callback( $input_field[2] );
				}
				Fum_Html_Input_Field::add_input_field( $field );


			}
		}
	}


	private
	static function create_login_form() {

		$form = new Fum_Html_Form( Fum_Conf::$fum_login_form_unique_name, 'Login', '#' );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_username ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_password ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );

		if ( Fum_Html_Form::is_unique_name_already_used( $form->get_unique_name() ) ) {
			Fum_Html_Form::delete_form( $form );
		}
		Fum_Html_Form::add_form( $form );
	}

	private static function create_register_form() {

		$form = new Fum_Html_Form( Fum_Conf::$fum_register_form_unique_name, 'Registration Form', '#' );

		//Required fields
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_username ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_email ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );

		//DHV-Jugend input field names
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_birthday ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_street ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_city ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_postcode ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_state ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_phone_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_mobile_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_dhv_member_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_license_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_aircraft ) );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_emergency_contact_surname ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_emergency_contact_forename ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_emergency_phone_number ) );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );


		if ( Fum_Html_Form::is_unique_name_already_used( $form->get_unique_name() ) ) {
			Fum_Html_Form::delete_form( $form );
		}
		Fum_Html_Form::add_form( $form );
	}

	private static function create_change_password_form() {
		//Create change password fom
		$form = new Fum_Html_Form( Fum_Conf::$fum_change_password_form_unique_name, 'Passwort ändern', '#' );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_password ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_new_password ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_new_password_check ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );

		if ( Fum_Html_Form::is_unique_name_already_used( $form->get_unique_name() ) ) {
			Fum_Html_Form::delete_form( $form );
		}
		Fum_Html_Form::add_form( $form );
	}

	private static function create_edit_form() {

		$form = new Fum_Html_Form( Fum_Conf::$fum_edit_form_unique_name, 'Profil editieren', '#' );


		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_email ) );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_last_name ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_first_name ) );


		//DHV-Jugend input field names
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_birthday ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_street ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_city ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_postcode ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_state ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_phone_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_mobile_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_dhv_member_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_license_number ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_aircraft ) );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_emergency_contact_surname ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_emergency_contact_forename ) );
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_emergency_phone_number ) );

		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );


		if ( Fum_Html_Form::is_unique_name_already_used( $form->get_unique_name() ) ) {
			Fum_Html_Form::delete_form( $form );
		}
		Fum_Html_Form::add_form( $form );
	}
} 