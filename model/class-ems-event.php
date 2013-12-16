<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Ems_Event extends Fum_Observable implements Fum_Observer {

	private static $object = NULL;
	private static $event_fields = NULL;


	private $name;
	private $start_date_time;
	private $end_date_time;
	private $location;
	private $leader;


	public function __construct() {
		$date = new DateTime();

	}

	public static function observe_object( Fum_Observable $observable ) {
		if ( self::$object === NULL ) {
			self::$object = new Ems_Event();
		}
		$observable->addObserver( self::$object );
	}

	public function update( Fum_Observable $observable ) {
		if ( $observable instanceof Fum_Html_Form ) {
			switch ( $observable->get_unique_name() ) {
				case Fum_Conf::$fum_event_register_form_unique_name:
//					echo 'VALUE: ' . $observable->get_input_field( Fum_Conf::$fum_input_field_select_event )->get_value();
//					echo 'Value after REGEXP: ' . preg_replace( "/[^0-9]/", "", $observable->get_input_field( Fum_Conf::$fum_input_field_select_event )->get_value() );

					//Value of the input field is ID_<id_of_event> the preg_replace below is not safe for use with floating points numbers, but and ID should be an integer anyway
					$post_id = preg_replace( "/[^0-9]/", "", $observable->get_input_field( Fum_Conf::$fum_input_field_select_event )->get_value() );
					self::register_user_to_event( $post_id, get_current_user_id() );
//					echo '<pre>';
//					print_r( Ems_Event_Registration::get_registrations_of_user( get_current_user_id() ) );
//					echo '</pre>';
					break;
			}
		}
	}

	private static function register_user_to_event( $event_post_id, $user_id ) {
		$event_registration = new Ems_Event_Registration( $event_post_id, $user_id );
		Ems_Event_Registration::add_event_registration( $event_registration );
	}

	private static function delete_user_from_event( Fum_Html_Form $form ) {

	}
} 