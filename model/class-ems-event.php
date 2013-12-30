<?php
/**
 * @author Christoph Bessei
 * @version
 */

/**
 * Class Ems_Event quasi(!) extends WP_Post, because WP_Post is final it fake the extends via __get and __set method
 * e.g you can access the WP_Post variable $post_title via $event->post_title
 * TODO: Make WP_Post cunctions callable
 */
class Ems_Event extends Fum_Observable implements Fum_Observer {

	//TODO Should be ems_event
	private static $post_type = 'event';

	private static $object = NULL;
	private static $event_fields = NULL;

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
		else {
			if ( property_exists( $this->post, $var ) ) {
				return $this->post->$var;
			}
		}
	}

	public function __set( $var, $value ) {
		if ( property_exists( $this, $var ) ) {
			$this->$var = $value;
		}
		else {
			if ( property_exists( $this->post, $var ) ) {
				$this->post->$var = $value;
			}
		}
	}

	public function __call( $func, $param ) {

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

	public static function compare( Ems_Event $a, Ems_Event $b ) {
		//If DateTime is not set, it's later ("bigger")
		if ( ! $a->get_start_date_time() instanceof DateTime || ! $a->get_end_date_time() instanceof DateTime ) {
			return - 1;
		}

		if ( ! $b->get_start_date_time() instanceof DateTime || ! $b->get_end_date_time() instanceof DateTime ) {
			return 1;
		}

		if ( $a->get_start_date_time() > $b->get_start_date_time() ) {
			return 1;
		}
		else if ( $a->get_start_date_time() < $b->get_start_date_time() ) {
			return - 1;
		}
		else {
			if ( $a->get_end_date_time() > $b->get_end_date_time() ) {
				return 1;
			}
			else {
				return - 1;
			}
		}
	}

	public static function observe_object( Fum_Observable $observable ) {
		if ( self::$object === NULL ) {
			self::$object = new Ems_Event();
		}
		$observable->addObserver( self::$object );
	}

	/**
	 * Returns an array of events (post with post_type Ems_Event::$post_type)
	 *
	 * @param bool  $sorted    true(default) = sort array by start date (and if start date is not unique by end date)
	 * @param int   $limit     number of events which should be returned, if $sorted is true the 'next' $limit events will be returned
	 *                         is $sorted false $limit random events will be returned
	 * @param array $user_args additional arguments for get_posts, order_by and post_per_page is ignored if $sorted/$limit is set!
	 *
	 * @return Fum_Event[] Sorted (if $sorted=true)
	 */
	public static function get_events( $sorted = true, $limit = -1, array $user_args = array() ) {

		$args = array(
			'post_type'      => self::get_event_post_type(),
			'posts_per_page' => $limit,
		);
		//Order is important because $args should overwrite $user_args if keys are duplicate
		//We do this because order_by will be overwritten by php sort, so it's more consistent to do this also for $limit
		array_merge( $user_args, $args );
		$posts = get_posts( $args );

		$events = array();
		/** @var WP_Post[] $posts */
		foreach ( $posts as $post ) {
			//TODO Add date_range with option if start or/and end date should be in the range, delete hardcoded 2014
			$event = new Ems_Event( $post );
			/** @var DateTime $date_time */
			$timestamp = $event->get_start_date_time()->getTimestamp();
			$year      = date( 'Y', $timestamp );
			if ( $year == 2014 ) {
				//$events[] = array( 'title' => $post->post_title, 'value' => 'ID_' . $post->ID, 'ID' => $post->ID );
				$events[] = $event;
			}
		}
		uasort( $events, array( 'Ems_Event', 'compare' ) );

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