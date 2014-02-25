<?php

/**
 * @author  Christoph Bessei
 * @version 0.04
 */
abstract class Ems_Post extends Fum_Observable implements Fum_Observer {
	protected static $post_type;
	protected static $object = NULL;
	protected $post;

	/**
	 * With __get you can access Ems_Post like an WP_Post e.g. $event->ID returns the post ID
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
} 