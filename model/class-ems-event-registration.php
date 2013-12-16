<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Ems_Event_Registration {
	private static $option_name = 'ems_event_registration';

	private $event_post_id;
	private $user_id;

	public function __construct( $event_post_id, $user_id ) {
		$this->event_post_id = $event_post_id;
		$this->user_id       = $user_id;
	}

	/**
	 * @return mixed
	 */
	public function get_event_post_id() {
		return $this->event_post_id;
	}

	/**
	 * @return mixed
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	public function equals( self $otherObject ) {
		if ( $otherObject->get_event_post_id() == $this->get_event_post_id() && $otherObject->get_user_id() == $this->get_user_id() ) {
			return true;
		}
		return false;
	}

	public static function add_event_registration( Ems_Event_Registration $registration ) {
		$registrations = self::get_event_registrations();
		if ( self::is_already_registered( $registration ) ) {
			throw new Exception( "User is alredy registered for this event" );
		}
		$registrations[] = $registration;
		$title = get_post( $registration->get_event_post_id() )->post_title;
		$user = get_userdata( $registration->get_user_id() );
		$subject = 'Erfolgreich für "' . $title . '" registriert';
		$message =
				'Liebe/r ' . $user->user_firstname . "\n" .
				'du hast dich erfolgreich für das Event "' . $title . '" registriert.' . "\n" .
				'Du bekommst spätestens 14 Tage vor dem Event weitere Informationen vom Eventleiter zugeschickt.' . "\n" .
				'Viele Grüße,' . "\n" .
				'Das DHV-Jugendteam';
		wp_mail( $user->user_email, $subject, $message );
		update_option( self::$option_name, $registrations );
	}


	public static function delete_event_registration( Ems_Event_Registration $registration ) {
		$registrations = self::get_event_registrations();
		foreach ( $registrations as $key => $cur_registration ) {
			if ( $registration->equals( $cur_registration ) ) {
				unset( $registrations[$key] );
			}
		}
		update_option( self::$option_name, $registrations );
	}

	/**
	 * @return Ems_Event_Registration[]
	 */
	private static function get_event_registrations() {
		$registrations = get_option( self::$option_name );
		if ( is_array( $registrations ) ) {
			return $registrations;
		}
		return array();
	}

	public static function get_registrations_of_event( $event_post_id ) {
		$registrations       = self::get_event_registrations();
		$event_registrations = array();
		foreach ( $registrations as $registration ) {
			if ( $registration->get_event_post_id() == $event_post_id ) {
				$event_registrations[] = $registration;
			}
		}
		return $event_registrations;
	}

	/**
	 * @param $user_id
	 *
	 * @return Ems_Event_Registration[]
	 */
	public static function get_registrations_of_user( $user_id ) {
		$registrations       = self::get_event_registrations();
		$event_registrations = array();
		foreach ( $registrations as $registration ) {
			if ( $registration->get_user_id() == $user_id ) {
				$event_registrations[] = $registration;
			}
		}
		return $event_registrations;
	}

	public static function is_already_registered( Ems_Event_Registration $registration ) {
		$registrations = self::get_event_registrations();
		$used          = false;
		foreach ( $registrations as $cur_registration ) {
			if ( $registration->get_event_post_id() == $cur_registration->get_event_post_id() && $registration->get_user_id() == $cur_registration->get_user_id() ) {
				$used = true;
				break;
			}
		}
		return $used;
	}

	/**
	 * @return string
	 */
	public static function get_option_name() {
		return self::$option_name;
	}


}