<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Html_Form {
	private $unique_name;
	private $name;
	private $action;
	private $method;
	private $id;
	private $classes;
	private $input_fields;

	function __construct( $unique_name, $name, $action, Html_Method_Type_Enum $method = NULL, $id = '', $classes = '', $input_fields = '' ) {
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

	public function add_input_field( Html_Input_Field $input_field ) {
		$this->input_fields[] = $input_field;

	}

	/**
	 * @param Html_Input_Field[] $input_fields
	 */
	public function set_input_fields( array $input_fields ) {
		$this->input_fields = $input_fields;
	}

	/**
	 * @return Html_Input_Field[]
	 */
	public function get_input_fields() {
		return $this->input_fields;
	}

	/**
	 * @param Html_Method_Type_Enum $method
	 */
	public function set_method( Html_Method_Type_Enum $method ) {
		$this->method = $method;
	}

	/**
	 * @return Html_Method_Type_Enum
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * @param mixed $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @param mixed $unique_name
	 */
	public function set_unique_name( $unique_name ) {
		$this->unique_name = $unique_name;
	}

	/**
	 * @return mixed
	 */
	public function get_unique_name() {
		return $this->unique_name;
	}

	public function get_html( $table = false, $th = true, $return = true ) {
		if ( $return ) {
			ob_start();
		}
		?>
		<form id="<?php echo $this->get_id(); ?>" name="<?php echo $this->get_name(); ?>" class="<?php echo $this->get_classes(); ?>" action="<?php echo $this->get_action(); ?>" method="<?php echo $this->get_method(); ?>">
			<?php if ($table): //TODO make class="form-table" dynamic ? ?>
			<table class="form-table">
				<?php endif;

				foreach ( $this->get_input_fields() as $input_field ) {
					//$return is always false, because we started already an output buffer
					$input_field->get_html( $table, $th, false );
				}

				if ($table): ?>
			</table>
		<?php endif; ?>

		</form>
		<?php

		if ( $return ) {
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}
	}


}