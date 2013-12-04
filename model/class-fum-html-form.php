<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Html_Form extends Fum_Observable implements Fum_Observer {
	private static $option_name_forms = 'fum_forms';

	private $unique_name;
	private $name;
	private $action;
	private $method;
	private $id;
	private $classes;
	/** @var  Fum_Html_Input_Field[] $input_fields */
	private $input_fields;
	private $callback;
	private $validation_return = false;

	function __construct( $unique_name, $name, $action, Html_Method_Type_Enum $method = NULL, $id = '', $classes = '', $input_fields = '', $callback = NULL ) {
		$this->unique_name = $unique_name;
		$this->name        = $name;
		$this->action      = $action;
		if ( ! $method instanceof Html_Method_Type_Enum ) {
			$method = new Html_Method_Type_Enum( Html_Method_Type_Enum::POST );
		}
		$this->method       = $method;
		$this->id           = $id;
		$this->classes      = $classes;
		$this->input_fields = $input_fields;
		if ( is_array( $this->input_fields ) ) {

			foreach ( $this->input_fields as $input_field ) {
				/** @var  Fum_Html_Input_Field $input_field */
				$input_field->addObserver( $this );
			}
		}

		$this->callback = $callback;
	}

	public function update( Fum_Observable $observable ) {
		//Got notification from an input field, notify observers
		if ( in_array( $observable, $this->input_fields ) ) {
			$this->setChanged();
			$this->notifyObservers();
		}
	}

	public function validate( $force_new_validation = false ) {
		//Check if validation is forced or if no validation_return value is set, else return the validation_return value
		if ( $force_new_validation || false === $this->validation_return ) {
			$errors = new WP_Error();
			foreach ( $this->get_input_fields() as $input_field ) {
				error_log( "Validate: " . $input_field->get_unique_name() );
				$error = $input_field->validate();
				if ( is_wp_error( $error ) ) {
					$errors->add( $input_field->get_unique_name(), $error->get_error_message() );
				}
			}
			$error_codes = $errors->get_error_codes();
			if ( empty( $error_codes ) ) {
				$this->validation_return = true;
				return $this->validation_return;
			}

			$this->validation_return = $errors;
			return $errors;
		}
		else {
			return $this->validation_return;
		}
	}

	/**
	 * Adds an HTML form to the database
	 *
	 * @param Fum_Html_Form $form HTML form which should be stored in the database
	 *
	 * @return bool  return value of update_option(), true if add was successful, otherwise false
	 * @throws Exception The exception is thrown if $unique_name is already used for another form
	 */
	public
	static function add_form( Fum_Html_Form $form ) {
		$forms = self::get_forms();
		foreach ( $forms as $cur_form ) {
			if ( $cur_form->get_unique_name() === $form->get_unique_name() ) {
				throw new Exception( '$unique_name "' . $form->get_unique_name() . '" is already used' );
			}
		}
		$forms[] = $form;
		return self::update_forms( $forms );
	}

	/**
	 * Stores new forms in the database, OVERWRITES! the previously stored forms
	 *
	 * @param Fum_Html_Form[] $forms array of Fum_Html_Form forms
	 *
	 * @return bool return value of update_option(), true if add was successful, otherwise false
	 */
	public static function set_forms( array $forms ) {
		return self::update_forms( $forms );
	}

	/**
	 * Get form by object or $unique_name
	 *
	 * @param $form Fum_Html_Form|string  Html_form object of the form or $unique_name
	 *
	 * @return Fum_Html_Form|bool returns the searched Fum_Html_Form or false if it's not found
	 */
	public static function get_form( $form ) {
		$unique_name = $form;
		if ( $form instanceof Fum_Html_Form ) {
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
	 * @return Fum_Html_Form[]
	 */
	public static function get_all_forms() {
		return self::get_forms();
	}

	/**
	 * Deletes a form from the database
	 *
	 * @param $form Fum_Html_Form|string The Fum_Html_Form object or the unique name
	 *
	 * @return bool true if delete was successful, otherwise false
	 */
	public static function delete_form( $form ) {
		$unique_name = $form;
		if ( $form instanceof Fum_Html_Form ) {
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
	 * @return Fum_Html_Form[]
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


	/**
	 * @param mixed $action
	 */
	public function set_action( $action ) {
		$this->action = $action;
	}

	/**
	 * @return mixed
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * @param mixed $classes
	 */
	public function set_classes( $classes ) {
		$this->classes = $classes;
	}

	/**
	 * @return mixed
	 */
	public function get_classes() {
		return $this->classes;
	}

	/**
	 * @param mixed $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	public function add_input_field( Fum_Html_Input_Field $input_field ) {
		$input_field->addObserver( $this );
		$this->input_fields[] = $input_field;
	}

	public function insert_input_field_after_unique_name( Fum_Html_Input_Field $input_field, $unique_name ) {
		$found = false;

		foreach ( $this->get_input_fields() as $key => $cur_input_field ) {
			if ( $found ) {
				$input_fields = $this->get_input_fields();
				//Insert element into array: http://www.php.net/manual/de/function.array-splice.php#19084
				array_splice( $input_fields, $key, 0, array( $input_field ) );
				$this->set_input_fields( $input_fields );
				break;
			}
			if ( $cur_input_field->get_unique_name() == $unique_name ) {
				$found = true;
			}
		}
	}

	public function insert_input_field_before_unique_name( Fum_Html_Input_Field $input_field, $unique_name ) {
		foreach ( $this->get_input_fields() as $key => $cur_input_field ) {
			if ( $cur_input_field->get_unique_name() == $unique_name ) {
				$input_fields = $this->get_input_fields();
				//Insert element into array: http://www.php.net/manual/de/function.array-splice.php#19084
				array_splice( $input_fields, $key, 0, array( $input_field ) );
				$this->set_input_fields( $input_fields );
				break;
			}
		}
	}

	/**
	 * @param Fum_Html_Input_Field[] $input_fields
	 */
	public
	function set_input_fields( array $input_fields ) {
		foreach ( $input_fields as $input_field ) {
			$input_field->addObserver( $this );
		}
		$this->input_fields = $input_fields;
	}

	/**
	 * @return Fum_Html_Input_Field[]
	 */
	public
	function get_input_fields() {
		return $this->input_fields;
	}

	/**
	 * @param Html_Method_Type_Enum $method
	 */
	public
	function set_method( Html_Method_Type_Enum $method ) {
		$this->method = $method;
	}

	/**
	 * @return Html_Method_Type_Enum
	 */
	public
	function get_method() {
		return $this->method;
	}

	/**
	 * @param mixed $name
	 */
	public
	function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public
	function get_name() {
		return $this->name;
	}

	/**
	 * @param mixed $unique_name
	 */
	public
	function set_unique_name( $unique_name ) {
		$this->unique_name = $unique_name;
	}

	/**
	 * @return mixed
	 */
	public
	function get_unique_name() {
		return $this->unique_name;
	}

	/**
	 * @param null $callback
	 */
	public function set_callback( $callback ) {
		$this->callback = $callback;
	}

	/**
	 * @return null
	 */
	public function get_callback() {
		return $this->callback;
	}

}