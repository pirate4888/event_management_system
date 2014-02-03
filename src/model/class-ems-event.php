<?php
/**
 * @author Christoph Bessei
 * @version
 */

/**
 * Class Ems_Event quasi(!) extends WP_Post, because WP_Post is final it fake the extends via __get and __set method
 * e.g you can access the WP_Post variable $post_title via $event->post_title
 */
class Ems_Event extends Fum_Observable implements Fum_Observer {

	//TODO Should be ems_event
	private static $post_type = 'event';

	private static $object = NULL;

	private $start_date_time;
	private $end_date_time;
	private $location;
	private $leader;
	private $post;

	public function __construct( WP_Post $post = NULL ) {
		if ( NULL !== $post ) {
			$this->post            = $post;
			$this->start_date_time = get_post_meta( $post->ID, 'ems_start_date', true );
			if ( empty( $this->start_date_time ) ) {
				$this->start_date_time = new DateTime();
				$this->start_date_time->setTimestamp( 0 );
			}
			$this->end_date_time = get_post_meta( $post->ID, 'ems_end_date', true );
			if ( empty( $this->end_date_time ) ) {
				$this->end_date_time = new DateTime();
				$this->end_date_time->setTimestamp( 0 );
			}

			$this->leader = get_userdata( get_post_meta( $post->ID, 'ems_event_leader', true ) );
			if ( false === $this->leader ) {
				$this->leader = get_post_meta( $post->ID, 'ems_event_leader', true );
			}
		}
	}

	/**
	 * With __get you can access Ems_Event like an WP_Post e.g. $event->ID returns the post ID
	 * For consistency also the event member variables are accessible like this e.g. $event->end_date_time
	 *
	 * Be careful: If WP_Post and Ems_Event have a variable with the same name, Ems_Event variable is used
	 *
	 * @param string $var name of the class variable
	 *
	 * @return array|mixed
	 */
	public function __get( $var ) {
		if ( property_exists( $this, $var ) ) {
			return $this->$var;
		}

		if ( NULL !== $this->post ) {
			return $this->post->$var;
		}
		throw new Exception( "Property " . $var . " does not exist in Ems_Event and WP_Post property is NULL" );
	}

	/**
	 * Calls the underlying WP_Post functions
	 *
	 * @param $method
	 * @param $args
	 */
	public function __call( $method, $args ) {
		//__call is not called if the function exists in Ems_Event, so we just have to check if the function exists in WP_Post
		if ( is_callable( array( $this->post, $method ) ) ) {
			return call_user_func_array( array( $this->post, $method ), $args );
		}
		throw new Exception( "Tried to call function: " . print_r( $method, true ) . " which does not exist in WP_Post and Ems_Event" );
	}

	/**
	 * @param DateTime $end_date_time
	 */
	public function set_end_date_time( $end_date_time ) {
		$this->end_date_time = $end_date_time;
	}

	/**
	 * @return DateTime
	 */
	public function get_end_date_time() {
		return $this->end_date_time;
	}

	/**
	 * @param DateTime $start_date_time
	 */
	public function set_start_date_time( $start_date_time ) {
		$this->start_date_time = $start_date_time;
	}

	/**
	 * @return DateTime
	 */
	public function get_start_date_time() {
		return $this->start_date_time;
	}

	/**
	 * @param bool|WP_User $leader
	 */
	public function set_leader( $leader ) {
		$this->leader = $leader;
	}

	/**
	 * @return bool|WP_User
	 */
	public function get_leader() {
		return $this->leader;
	}

	/**
	 * @param WP_Post $post
	 */
	public function set_post( $post ) {
		$this->post = $post;
	}

	/**
	 * @return WP_Post
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * @param mixed $location
	 */
	public function set_location( $location ) {
		$this->location = $location;
	}

	/**
	 * @return mixed
	 */
	public function get_location() {
		return $this->location;
	}


	public function update( Fum_Observable $observable ) {
		if ( $observable instanceof Fum_Html_Form ) {
			switch ( $observable->get_unique_name() ) {
				case Fum_Conf::$fum_event_register_form_unique_name:

					//Value of the input field is ID_<id_of_event> the preg_replace below is not safe for use with floating points numbers, but  ID should be an integer anyway
					$post_id = preg_replace( "/[^0-9]/", "", $observable->get_input_field( Fum_Conf::$fum_input_field_select_event )->get_value() );
					self::register_user_to_event( $post_id, get_current_user_id(), $observable );
					break;
			}
		}
	}

	/**
	 * Orders event ascending by start date and end date (if start dates are equal)
	 *
	 * @param Ems_Event $a
	 * @param Ems_Event $b
	 *
	 * @return int
	 */
	public static function compare( Ems_Event $a, Ems_Event $b ) {

		$a_start = 0;
		if ( $a->get_start_date_time() instanceof DateTime ) {
			$a_start = $a->start_date_time->getTimestamp();
		}

		$b_start = 0;
		if ( $b->get_start_date_time() instanceof DateTime ) {
			$b_start = $b->get_start_date_time()->getTimestamp();
		}

		$start_diff = $a_start - $b_start;
		if ( $start_diff != 0 ) {
			return $start_diff;
		}

		$a_end = 0;
		if ( $a->get_end_date_time() instanceof DateTime ) {
			$a_end = $a->get_end_date_time()->getTimestamp();
		}

		$b_end = 0;
		if ( $b->get_end_date_time() instanceof DateTime ) {
			$b_end = $b->get_end_date_time()->getTimestamp();
		}

		return ( $a_end - $b_end );
	}

	public static function observe_object( Fum_Observable $observable ) {
		if ( self::$object === NULL ) {
			self::$object = new Ems_Event();
		}
		$observable->addObserver( self::$object );
	}

	/**
	 * Returns an array of events (posts with post_type Ems_Event::$post_type)
	 *
	 * @param int      $limit              limits the returned events. If $sort the events gets sorted first and then the first $limit events will be returned
	 *                                     without sort
	 * @param bool     $sort               sort events (true) or not (false)
	 * @param bool     $reverse_order      reverse the order after sort, has no affect if $sort=false
	 * @param callable $user_sort_callback function which compares two Ems_Event objects, used with usort(). Default is Ems_Event->compare
	 * @param array    $user_args          array of arguments to use in wordpress get_posts. 'post_type','posts_per_page' and 'order_by' are ignored! Use $sort and $limit instead
	 *
	 * @return Ems_Event[]    returns an array of Ems_Event
	 * @throws Exception      throws exception if there occurs an error during sort
	 */
	public static function get_events( $limit = -1, $sort = true, $reverse_order = false, callable $user_sort_callback = NULL, array $user_args = array(), Ems_Date_Period $start_period = NULL, Ems_Date_Period $end_period = NULL ) {

		//return empty array if limit is 0
		if ( 0 === $limit ) {
			return array();
		}
		//unset post_type,posts_per_page and order_by from $user_args because we do this on our own
		unset( $user_args['post_type'] );
		unset( $user_args['posts_per_page'] );
		unset( $user_args['order_by'] );

		$posts_per_page = $limit;
		if ( $sort ) {
			//Because we have to order the events later in the function, we need all events
			$posts_per_page = - 1;
		}
		$args  = array(
				'post_type'      => self::get_event_post_type(),
				'posts_per_page' => $posts_per_page,
		);
		$posts = get_posts( array_merge( $user_args, $args ) );

		//Use all events if $start_period and $end_period are undefined
		$events = $posts;

		/* @var DatePeriod $start_period */
		$events = array();
		/** @var WP_Post[] $posts */
		foreach ( $posts as $post ) {
			$event = new Ems_Event( $post );
			/** @var DateTime $date_time */
			$timestamp = $event->get_start_date_time()->getTimestamp();
			$year      = date( 'Y', $timestamp );
			//TODO Use $start_period and $end_period
			if ( $year == 2014 ) {
				//$events[] = array( 'title' => $post->post_title, 'value' => 'ID_' . $post->ID, 'ID' => $post->ID );
				$events[] = $event;
			}
		}


		if ( $sort ) {
			if ( NULL === $user_sort_callback ) {
				$user_sort_callback = array( __CLASS__, 'compare' );
			}
			if ( false === usort( $events, $user_sort_callback ) ) {
				throw new Exception( "Couldn't sort events with " . print_r( $user_sort_callback, true ) . " as callback function" );
			}
			if ( $reverse_order ) {
				$events = array_reverse( $events );
			}
		}

		//Take the first $limit elements if array is sorted, if not we have done this via posts_per_page
		if ( $sort && $limit > - 1 ) {
			$events = array_splice( $events, 0, $limit );
		}
		return $events;
	}


	public static function get_event_post_type() {
		return self::$post_type;
	}

	private static function register_user_to_event( $event_post_id, $user_id, Fum_Html_Form $form = NULL ) {
		$event_registration = new Ems_Event_Registration( $event_post_id, $user_id );
		$data               = array();

		$used_input_fields = Fum_Activation::get_event_input_fields();
		if ( NULL !== $form ) {
			foreach ( $form->get_input_fields() as $input_field ) {
				//Skip select_event field (contains ID) because we already have $event_post_id
				if ( $input_field->get_unique_name() == 'select_event' || $input_field->get_unique_name() == Fum_Conf::$fum_input_field_accept_agb ) {
					continue;
				}
				if ( in_array( $input_field->get_unique_name(), $used_input_fields ) ) {
					$value = $input_field->get_value();
					if ( empty( $value ) ) {
						$value = 0;
					}
					$data[$input_field->get_unique_name()] = $value;
				}
			}
		}
		$event_registration->set_data( $data );
		Ems_Event_Registration::add_event_registration( $event_registration );
	}

	private static function delete_user_from_event( Fum_Html_Form $form ) {

	}
}