<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function test_get_events_limit_with_sort() {
		$events = Ems_Event::get_events( true, 5 );
		$this->assertEquals( 5, count( $events ) );
	}

	public function test_get_events_limit_without_sort() {
		$events = Ems_Event::get_events( false, 5 );
		$this->assertEquals( 5, count( $events ) );
	}

	public function test_get_events_limit_zero_with_sort() {
		$events = Ems_Event::get_events( true, 0 );
		$this->assertEquals( 5, count( $events ) );
	}

	public function test_get_events_limit_zero_without_sort() {
		$events = Ems_Event::get_events( false, 0 );
		$this->assertEquals( 5, count( $events ) );
	}

	/**
	 * Tests if the returned event array is really sorted ('next' event first)
	 */
	public function test_get_events_sorted() {
		$events = Ems_Event::get_events( true );

		$this->assertGreaterThan( 1, count( $events ), 'At least two elements are needed to check order' );

		for ( $i = 1; $i < count( $events ); $i ++ ) {
			$first_start_date  = $events[$i - 1]->get_start_date_time();
			$second_start_date = $events[$i]->get_start_date_time();

			$this->assertFalse( $first_start_date > $second_start_date, "start date of first event shouldn't be greater then start date of second event" );

			$first_end_date  = $events[$i - 1]->get_end_date_time();
			$second_end_date = $events[$i]->get_end_date_time();

			//if start date is equal and end date differs, check if the elements are ordered by end date
			if ( $first_start_date == $first_start_date && $first_end_date != $second_end_date ) {
				$this->assertTrue( $first_end_date < $second_end_date, 'start dates are equal and end dates differ but end date of first element is great then end date of second element' );
			}
		}
	}
}
 