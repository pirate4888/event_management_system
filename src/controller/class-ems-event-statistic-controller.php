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
			for ( $i = 0; ! $startdate_oldest_event instanceof DateTime && count( $events ) > $i; $i ++ ) {
				$startdate_oldest_event = $events[0]->get_start_date_time();
			}

			if ( $startdate_oldest_event instanceof DateTime ) {
				$year = date( "Y", $startdate_oldest_event->getTimestamp() );
				for ( $current_year = date( "Y" ); $year <= $current_year; $year ++ ) {
					echo "<h3>" . $year . "</h3>";
//					$start = new DateTime();
//					$start->setTimestamp()
//					$current_year_events = Ems_Event::get_events_by_start_date(new Ems_Date_Period(DateTime::c))
				}
			}

		}
		echo "Couldn't create statistic. No Events found.";
	}
}