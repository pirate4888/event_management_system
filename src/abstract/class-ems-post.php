<?php

/**
 * @author  Christoph Bessei
 * @version 0.04
 */
abstract class Ems_Post extends Fum_Observable implements Fum_Observer, Ems_Post_Interface {
	protected $post;
	protected static $post_type;
	protected static $capability_type;

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
			error_log( "var: " . $var );
			return $this->$var;
		}

		if ( null !== $this->get_post() ) {
			return $this->get_post()->$var;
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
		if ( is_callable( array( $this->get_post(), $method ) ) ) {
			return call_user_func_array( array( $this->get_post(), $method ), $args );
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

	public static function get_admin_capabilities() {
		$cap_type = static::get_capability_type();
		$single   = reset( $cap_type );;
		$plural = ( reset( $cap_type ) ) . 's';
		if ( is_array( $cap_type ) && count( $cap_type ) > 1 ) {
			$single = $cap_type[0];
			$plural = $cap_type[1];
		}
		$caps = array(
			'edit_' . $single         => true,
			'read_' . $single         => true,
			'delete_' . $single       => true,
			'edit_' . $plural         => true,
			'edit_others_' . $plural  => true,
			'publish_' . $plural      => true,
			'read_private_' . $plural => true,
			'read'                    => true,
		);

		return $caps;
	}

	/**
	 * Get the capability which allows edit of the post
	 * @return string name of the capability
	 */
	public static function get_edit_capability() {
		$cap_type = static::get_capability_type();
		$single   = reset( $cap_type );
		if ( is_array( $cap_type ) && count( $cap_type ) > 1 ) {
			$single = $cap_type[0];
		} else {

		}

		return 'edit_' . $single;
	}

	public static function get_post_type() {
		return static::$post_type;
	}

	public static function get_capability_type() {
		return static::$capability_type;
	}
}