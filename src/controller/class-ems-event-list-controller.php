<?php

/**
 * @author  Christoph Bessei
 * @version 0.04
 */
class Ems_Event_List_Controller {

	public static function get_event_list() {
		//If user has no access, redirect to home
		if ( ! current_user_can( Ems_Event::get_edit_capability() ) ) {
			wp_redirect( home_url() );
			exit;
		}

		$allowed_event_time_start = new DateTime();
		$allowed_event_time_start->setTimestamp( Ems_Date_Helper::get_timestamp( get_option( "date_format" ), get_option( "ems_start_date_period" ) ) );
		$allowed_event_time_end = new DateTime();
		$allowed_event_time_end->setTimestamp( Ems_Date_Helper::get_timestamp( get_option( "date_format" ), get_option( "ems_end_date_period" ) ) );
		$allowed_event_time_period = new Ems_Date_Period( $allowed_event_time_start, $allowed_event_time_end );
		$events                    = Ems_Event::get_events( - 1, true, false, null, array(), $allowed_event_time_period );

		foreach ( $events as $event ) {
			/** @var DateTime $start_date_object */
			$start_date_object = $event->get_start_date_time();
			$start_date        = "";
			if ( null !== $start_date_object ) {
				$start_date = date_i18n( get_option( 'date_format' ), $start_date_object->getTimestamp() );
			}
			/** @var DateTime $end_date_object */
			$end_date_object = $event->get_end_date_time();
			$end_date        = "";
			if ( null !== $end_date_object ) {
				$end_date = date_i18n( get_option( 'date_format' ), $end_date_object->getTimestamp() );
			}
			$date_string = "";
			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$date_string = $start_date . ' - ' . $end_date;
			}
			?>
			<div style="margin-bottom: 5px;">
				<div style="width:60%; min-width:300px;float:left;">
					<a href="<?php echo get_permalink( $event->ID ); ?>"><?php echo $event->post_title; ?></a></div>
				<div style="text-align: right;"><i><?php echo $date_string; ?> </i></div>
			</div>
		<?php
		}
		echo "<p></p>";
	}

} 