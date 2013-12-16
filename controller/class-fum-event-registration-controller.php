<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Event_Registration_Controller {
	public static function create_event_registration_form() {

		//Check if user is logged in and show register/login link if not
		if ( ! is_user_logged_in() ) {
			?>
			Du musst dich einloggen, bevor du dich für ein Event anmelden kannst:<br />
			<?php
			wp_loginout();
			?>
			<br />Du hast noch keinen Account? Registriere dich:<br />
			<?php
			wp_register( '', '' );
			return;
		}

		$form = Fum_Html_Form::get_form( Fum_Conf::$fum_event_register_form_unique_name );
		$form = Fum_User::fill_form( $form );

		if ( isset( $_GET['event'] ) ) {
			$form->get_input_field( Fum_Conf::$fum_input_field_select_event )->set_value( $_GET['event'] );
		}
		$posts  = get_posts( array( 'post_type' => 'event' ) );
		$events = array();
		/** @var WP_Post[] $posts */
		foreach ( $posts as $post ) {
			/** @var DateTime $date_time */
			$date_time = get_post_meta( $post->ID, 'ems_start_date', true );
			$timestamp = $date_time->getTimestamp();
			$year      = date( 'Y', $timestamp );
			if ( $year == 2014 ) {
				$events[] = array( 'title' => $post->post_title, 'value' => 'ID_' . $post->ID, 'ID' => $post->ID );
			}
		}

		/* Start sort Events by date */
		$dates = array();
		foreach ( $events as $key => $event ) {
			$date_time = get_post_meta( $event['ID'], 'ems_start_date', true );
			if ( $date_time instanceof DateTime ) {
				$dates[$key] = $date_time->getTimestamp();
			}
			else {
				$dates = array();
				break;
			}

		}
		asort( $dates );
		$ordered_events = array();
		foreach ( $dates as $key => $timestamp ) {
			$ordered_events[] = $events[$key];
		}

		if ( ! empty( $ordered_events ) ) {
			$events = $ordered_events;
		}
		/* End sort events by date */

		foreach ( $form->get_input_fields() as $input_field ) {
			if ( $input_field->get_unique_name() == Fum_Conf::$fum_input_field_search_ride || $input_field->get_unique_name() == Fum_Conf::$fum_input_field_offer_ride ) {
				continue;
			}
			$input_field->set_required( true );
		}
		if ( isset( $_REQUEST[Fum_Conf::$fum_input_field_submit] ) ) {
			$form->set_callback( array( 'Fum_Event_Registration_Controller', 'validate_event_registration_form' ) );
			//Check if event select field contains and valid event
			$form->get_input_field( Fum_Conf::$fum_input_field_select_event )->set_validate_callback( array( 'Fum_Event_Registration_Controller', 'validate_event_select_field' ) );
			$form->set_values_from_array( $_REQUEST );
			$form->validate( true );
			Fum_User::observe_object( $form );
			Ems_Event::observe_object( $form );
			$form->save();
			if ( true === $form->get_validation_result() ) {
				echo '<p><strong>Du hast dich erfolgreich für "' . get_post( preg_replace( "/[^0-9]/", "", $form->get_input_field( Fum_Conf::$fum_input_field_select_event )->get_value() ) )->post_title . '" registriert </strong></p >';
			}
		}

		$form->get_input_field( Fum_Conf::$fum_input_field_select_event )->set_possible_values( $events );

		Fum_Form_View::output( $form );
	}


	public static function validate_event_select_field( Fum_Html_Input_Field $input_field ) {
		$posts          = get_posts( array( 'post_type' => 'event' ) );
		$is_valid_event = false;
		$ID             = NULL;
		/** @var WP_Post[] $posts */
		foreach ( $posts as $post ) {
			if ( 'ID_' . $post->ID == $input_field->get_value() ) {
				$ID             = $post->ID;
				$is_valid_event = true;
				break;
			}
		}
		if ( $is_valid_event ) {
			if ( Ems_Event_Registration::is_already_registered( new Ems_Event_Registration( $ID, get_current_user_id() ) ) ) {
				return new WP_Error( $input_field->get_unique_name(), 'Du bist bereits für dieses Event registriert' );
			}
			else {
				return true;
			}
		}
		return new WP_Error( $input_field->get_unique_name(), 'Das ausgewählte Event existiert nicht' );
	}

	public static function validate_event_registration_form( Fum_Html_Form $form, array $params = NULL ) {
		if ( isset( $params['error_on_input_field'] ) && true === $params['error_on_input_field'] ) {
			return new WP_Error( $form->get_unique_name(), 'Das Registrierungsformular ist nicht vollständig' );
		}
		return true;
	}
} 