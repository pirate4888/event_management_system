<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Statistic_Controller {
	public static function get_event_statistic() {
		$events = Ems_Event::get_events();
		if ( ! empty( $events ) ) {
			$startdate_oldest_event = null;
			$startdate_latest_event = null;
			//TODO Maybe faster if we first start at the beginning of the array and search the oldest event, then start again the end and search the latest event
			for ( $i = 0; $i < count( $events ); $i ++ ) {
				$start_date_time = $events[ $i ]->get_start_date_time();
				if ( ! $start_date_time instanceof DateTime ) {
					continue;
				}
				/** $start_date_time DateTime */
				//Set first event with start date as "oldest event"
				if ( null == $startdate_oldest_event ) {
					$startdate_oldest_event = $events[0]->get_start_date_time();
				}
				//Set last event with start date as "latest event"
				if ( null === $startdate_latest_event || $start_date_time->getTimestamp() > $startdate_latest_event->getTimestamp() ) {
					/** $startdate_latest_event DateTime */
					$startdate_latest_event = $start_date_time;
				}
			}
			error_log( print_r( $startdate_latest_event, true ) );
			error_log( print_r( $startdate_oldest_event, true ) );

			if ( null !== $startdate_oldest_event && null !== $startdate_latest_event ) {
				$start_year = date( "Y", $startdate_oldest_event->getTimestamp() );
				$end_year   = date( "Y", $startdate_latest_event->getTimestamp() );
				error_log( "start: " . $start_year );
				error_log( "End: " . $end_year );

				for ( ; $start_year <= $end_year; $start_year ++ ) {
					echo "<h3>" . $start_year . "</h3>";
					$start = new DateTime();
					$start->setTimestamp( strtotime( "1-1-" . $start_year ) );
					$end = new DateTime();
					$end->setTimestamp( strtotime( "31-12-" . $end_year ) );
					$current_year_events = Ems_Event::get_events_by_start_date( new Ems_Date_Period( $start, $end ) );
					echo "<h4>Teilnehmerzahlen</h4>";
					$users = array();
					foreach ( $current_year_events as $event ) {
						$registrations = Ems_Event_Registration::get_registrations_of_event( $event->ID );
						foreach ( $registrations as $registration ) {
							$users[ $registration->get_user_id() ] = true;
						}
						echo $event->post_title . ": " . count( $registrations ) . "<br>";
					}
					echo "<h4>Teilnehmer im Jahr " . $start_year . "</h4>";
					echo count( $users );

				}
			}

			return;
		}
		echo "Couldn't create statistic. No Events found.";
	}
}