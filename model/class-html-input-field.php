<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Html_Input_Field {
	private $unique_name;
	private $name;
	private $title;
	private $type;
	private $id;
	private $classes;
	private $size;
	private $value;
	private $possible_values;

	function __construct( $unique_name, $name, Html_Input_Type_Enum $type, $title = '', $id = '', $classes = '', $size = '', $value = '', $possible_values = array() ) {
		$this->unique_name     = $unique_name;
		$this->classes         = $classes;
		$this->id              = $id;
		$this->name            = $name;
		$this->title           = $title;
		$this->size            = $size;
		$this->type            = $type;
		$this->value           = $value;
		$this->possible_values = $possible_values;
	}

	/**
	 * @param string $classes
	 */
	public function set_classes( $classes ) {
		$this->classes = $classes;
	}

	/**
	 * @return string
	 */
	public function get_classes() {
		return $this->classes;
	}

	/**
	 * @param string $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @param string $size
	 */
	public function set_size( $size ) {
		$this->size = $size;
	}

	/**
	 * @return string
	 */
	public function get_size() {
		return $this->size;
	}

	/**
	 * @param Html_Input_Type_Enum $type
	 */
	public function set_type( Html_Input_Type_Enum $type ) {
		$this->type = $type;
	}

	/**
	 * @return Html_Input_Type_Enum
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * @param string $value
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * @param array $possible_values
	 */
	public function set_possible_values( $possible_values ) {
		$this->possible_values = $possible_values;
	}

	/**
	 * @return array
	 */
	public function get_possible_values() {
		return $this->possible_values;
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

		//If type is submit, we don't need a label or description
		if ( $this->get_type() === Html_Input_Type_Enum::SUBMIT ) {
			?>
			<input type="<?php echo $this->get_type(); ?>" value="<?php echo $this->get_value(); ?>" size="<?php echo $this->get_size(); ?>" id="<?php echo $this->get_id(); ?>" name="<?php echo $this->get_name(); ?>" />
		<?php
		}
		else {


			//title/label is independent of input type, so create it before distinction of cases
			if ( $table ): //Use table to format form
				?>
				<tr id="<?php echo $this->get_type(); ?>">
				<?php if ( $th ): ?>
				<th>
			<?php else: ?>
				<td>
			<?php endif; ?>
				<label for="<?php echo $this->get_id(); ?>"> <?php echo $this->get_title(); ?></label>
				<?php if ( $th ): ?>
				</th>
			<?php else: ?>
				</td>
			<?php endif; ?>
				<td>
			<?php else: //Use label only to format form?>
				<label for="<?php echo $this->get_id(); ?>" > <?php echo $this->get_title(); ?><br />
			<?php endif;

			switch ( $this->get_type() ) {

				case Html_Input_Type_Enum::TEXT:
				case Html_Input_Type_Enum::PASSWORD:
					?>
					<input type="<?php echo $this->get_type(); ?>" value="<?php echo $this->get_value(); ?>" size="<?php echo $this->get_size(); ?>" id="<?php echo $this->get_id(); ?>" name="<?php echo $this->get_name(); ?>" />
					<?php
					break;
				case Html_Input_Type_Enum::RADIO:
					foreach ( $this->get_possible_values() as $value ): ?>
						<input type="radio" name="<?php echo( $this->get_name() ); ?>" value="<?php echo $value; ?>" <?php checked( $this->get_value(), $value ); ?>/> <?php echo $value; ?>
						<br />
					<?php
					endforeach;
					?>
					<?php
					break;
				case Html_Input_Type_Enum::CHECKBOX:
					?>
					<input type="checkbox" name="<?php echo( $this->get_name() ); ?>" value="1" <?php checked( $this->get_value(), 1 ); ?>/>
					<?php
					break;
				case Html_Input_Type_Enum::SELECT:
					?>
					<select name="<?php echo $this->get_name(); ?>" <?php echo ( $this->get_multiple_selection() ) ? 'multiple="multiple"' : '' ?>>
						<?php foreach ( $this->get_possible_values() as $value ): ?>
							<option value="<?php echo $value ?>" <?php selected( $this->get_value(), $value ); ?>><?php echo $value ?></option>
						<?php endforeach; ?>
					</select>
					<?php
					break;
				case Html_Input_Type_Enum::TEXTAREA:
					?>
					<textarea name="<?php echo( $this->get_name() ); ?>"><?php echo( $this->get_value() ); ?></textarea>
					<?php
					break;


			}
			if ( $table ): ?>
				</td>
				</tr>
			<?php else: ?>
				</label>
			<?php endif;
		}
		if ( $return ) {
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}
	}


}