<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Form_Controller {
	private static $option_name_forms = 'fum_forms';

	/**
	 * Adds an HTML form to the database
	 *
	 * @param Html_Form $form HTML form which should be stored in the database
	 *
	 * @return bool  return value of update_option(), true if add was successful, otherwise false
	 * @throws Exception The exception is thrown if $unique_name is already used for another form
	 */
	public static function add_form( Html_Form $form ) {
		$forms = self::get_forms();
		foreach ( $forms as $cur_form ) {
			if ( $cur_form->get_unique_name() === $form->get_unique_name() ) {
				throw new Exception( '$unique_name is already used' );
			}
		}
		$forms[] = $form;
		return self::update_forms( $forms );
	}

	/**
	 * Stores new forms in the database, OVERWRITES! the previously stored forms
	 *
	 * @param array $forms array of Html_Form forms
	 *
	 * @return bool return value of update_option(), true if add was successful, otherwise false
	 */
	public static function set_forms( array $forms ) {
		return self::update_forms( $forms );
	}

	/**
	 * Get form by object or $unique_name
	 *
	 * @param $form Html_Form|string  Html_form object of the form or $unique_name
	 *
	 * @return Html_Form|bool returns the searched Html_Form or false if it's not found
	 */
	public static function get_form( $form ) {
		$unique_name = $form;
		if ( $form instanceof Html_Form ) {
			$unique_name = $form->get_unique_name();
		}
		$forms = self::get_forms();
		foreach ( $forms as $form ) {
			if ( $form->get_unique_name() === $unique_name ) {
				return $form;
			}
		}
		return false;
	}

	/**
	 * Get all stored forms from the database
	 * @return Html_Form[]
	 */
	public static function get_all_forms() {
		return self::get_forms();
	}

	/**
	 * Deletes a form from the database
	 *
	 * @param $form Html_Form|string The Html_Form object or the unique name
	 *
	 * @return bool true if delete was successful, otherwise false
	 */
	public static function delete_form( $form ) {
		$unique_name = $form;
		if ( $form instanceof Html_Form ) {
			$unique_name = $form->get_unique_name();
		}
		$forms = self::get_forms();
		foreach ( $forms as $key => $form ) {
			if ( $form->get_unique_name() === $unique_name ) {
				unset( $forms[$key] );
				return self::update_forms( $forms );
			}
		}
	}

	/**
	 * Checks if an $unique_name is already used in another form
	 *
	 * @param $unique_name string
	 *
	 * @return bool returns true if $unique_name is already used, false if not
	 */
	public static function is_unique_name_already_used( $unique_name ) {
		$forms = self::get_forms();
		foreach ( $forms as $form ) {
			if ( $form->get_unique_name() === $unique_name ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * @return string option name of the stored forms
	 */
	public static function get_option_name_forms() {
		return self::$option_name_forms;
	}

	/**
	 * @return Html_Form[]
	 */
	private static function get_forms() {
		$return = get_option( self::get_option_name_forms() );
		if ( is_array( $return ) ) {
			return $return;
		}
		return array();

	}

	private static function update_forms( $forms ) {
		return update_option( self::$option_name_forms, $forms );
	}
} 