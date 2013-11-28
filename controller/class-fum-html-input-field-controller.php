<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Html_Input_Field_Controller {
	private static $option_name_input_fields = 'fum_input_fields';

	/**
	 * Adds an Html_Input_Field to the database
	 *
	 * @param Html_Input_Field $input_field Html_Input_Field which should be stored in the database
	 *
	 * @return bool  return value of update_option(), true if add was successful, otherwise false
	 * @throws Exception The exception is thrown if $unique_name is already used for another form
	 */
	public static function add_form( Html_Input_Field $input_field ) {
		$input_fields = self::get_input_fields();
		foreach ( $input_fields as $cur_input_field ) {
			if ( $cur_input_field->get_unique_name() === $input_field->get_unique_name() ) {
				throw new Exception( '$unique_name is already used' );
			}
		}
		$input_fields[] = $input_field;
		return self::update_input_fields( $input_fields );
	}

	public static function add_input_field( Html_Input_Field $input_field ) {

	}

	/**
	 * Stores new forms in the database, OVERWRITES! the previously stored forms
	 *
	 * @param array $forms array of Html_Input_Field forms
	 *
	 * @return bool return value of update_option(), true if add was successful, otherwise false
	 */
	public static function set_input_fields( array $input_fields ) {
		return self::update_input_fields( $input_fields );
	}

	/**
	 * Get form by object or $unique_name
	 *
	 * @param $input_field Html_Input_Field|string  Html_Input_Field object of the form or $unique_name
	 *
	 * @return Html_Form|bool returns the searched Html_Form or false if it's not found
	 */
	public static function get_form( $input_field ) {
		$unique_name = $input_field;
		if ( $input_field instanceof Html_Input_Field ) {
			$unique_name = $input_field->get_unique_name();
		}
		$input_fields = self::get_input_fields();
		foreach ( $input_fields as $input_field ) {
			if ( $input_field->get_unique_name() === $unique_name ) {
				return $input_field;
			}
		}
		return false;
	}

	/**
	 * Get all stored forms from the database
	 * @return Html_Input_Field[]
	 */
	public static function get_all_input_fields() {
		return self::get_input_fields();
	}

	/**
	 * Deletes a form from the database
	 *
	 * @param $input_field Html_Input_Field|string The Html_Input_Field object or the unique name
	 *
	 * @return bool true if delete was successful, otherwise false
	 */
	public static function delete_input_field( $input_field ) {
		$unique_name = $input_field;
		if ( $input_field instanceof Html_Input_Field ) {
			$unique_name = $input_field->get_unique_name();
		}
		$input_fields = self::get_input_fields();
		foreach ( $input_fields as $key => $input_field ) {
			if ( $input_field->get_unique_name() === $unique_name ) {
				unset( $input_fields[$key] );
				return self::update_input_fields( $input_fields );
			}
		}
	}

	/**
	 * Checks if an $unique_name is already used for another input field
	 *
	 * @param $unique_name string
	 *
	 * @return bool returns true if $unique_name is already used, false if not
	 */
	public static function is_unique_name_already_used( $unique_name ) {
		$input_fields = self::get_input_fields();
		foreach ( $input_fields as $input_field ) {
			if ( $input_field->get_unique_name() === $unique_name ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * @return string
	 */
	public static function get_option_name_input_fields() {
		return self::$option_name_input_fields;
	}

	/**
	 * @return Html_Input_Field[]
	 */
	private static function get_input_fields() {
		$return = get_option( self::get_option_name_input_fields() );
		if ( is_array( $return ) ) {
			return $return;
		}
		return array();
	}

	private static function update_input_fields( $input_fields ) {
		return update_option( self::$option_name_input_fields, $input_fields );
	}
} 