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

			if ( null !== $startdate_oldest_event && null !== $startdate_latest_event ) {
				$year = date( "Y", $startdate_oldest_event->getTimestamp() );
				for ( $current_year = date( "Y" ); $year <= $current_year; $year ++ ) {
					echo "<h3>" . $year . "</h3>";
//					$start = new DateTime();
//					$start->setTimestamp()
//					$current_year_events = Ems_Event::get_events_by_start_date(new Ems_Date_Period(DateTime::c))
				}
			}

			return true;
		}
		echo "Couldn't create statistic. No Events found.";
	}
}