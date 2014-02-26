<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Report extends Ems_Post {
	protected static $post_type = 'ems_event_report';
	/**
	 * The event which the report is connected to
	 * @var Ems_Event
	 */
	private $event;


	public function update( Fum_Observable $o ) {

	}
} 