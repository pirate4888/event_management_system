<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_List_Controller {

	public static function get_event_list() {

		$events = Ems_Event::get_events();

		foreach ( $events as $event ) {
			/** @var DateTime $start_date */
			$start_date = get_post_meta( $event->ID, 'ems_start_date', true );
			$start_date = date( 'd.m.y', $start_date->getTimestamp() );
			/** @var DateTime $end_date */
			$end_date = get_post_meta( $event->ID, 'ems_end_date', true );

			$end_date = date( 'd.m.y', $end_date->getTimestamp() );

			?>
			<p><a href="<?php echo get_permalink( $event->ID ); ?>"><?php echo $event->post_title; ?></a>
				<i><?php echo $start_date . ' - ' . $end_date; ?> </i></p>

		<?php
		}
	}

} 