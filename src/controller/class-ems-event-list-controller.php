<?php

/**
 * @author  Christoph Bessei
 * @version 0.04
 */
class Ems_Event_List_Controller {

	public static function get_event_list() {

		$events = Ems_Event::get_events();

		foreach ( $events as $event ) {
			/** @var DateTime $start_date_object */
			$start_date_object = $event->get_start_date_time();
			$start_date        = "";
			if ( NULL !== $start_date_object ) {
				$start_date = date_i18n( get_option( 'date_format' ), $start_date_object->getTimestamp() );
				echo get_option( "ems_start_date_period" );
				echo Ems_Date_Helper::get_timestamp( get_option( "date_format" ), get_option( "ems_start_date_period" ) );
			}
			/** @var DateTime $end_date_object */
			$end_date_object = $event->get_end_date_time();
			$end_date        = "";
			if ( NULL !== $end_date_object ) {
				$end_date = date_i18n( get_option( 'date_format' ), $end_date_object->getTimestamp() );
			}
			$date_string = "";
			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$date_string = $start_date . ' - ' . $end_date;
			}
			?>
			<p><a href="<?php echo get_permalink( $event->ID ); ?>"><?php echo $event->post_title; ?></a>
				<i><?php echo $date_string; ?> </i></p>

		<?php
		}
	}

} 