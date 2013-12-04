<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_Form_View {

	public static function output( Fum_Html_Form $form ) {
		echo self::get_html_of_form( $form );
	}


	private static function get_html_of_form( Fum_Html_Form $form, $table = false, $th = true, $return = true ) {
		if ( $return ) {
			ob_start();
		}
		?>
		<form id="<?php echo $form->get_id(); ?>" name="<?php echo $form->get_name(); ?>" class="<?php echo $form->get_classes(); ?>" action="<?php echo $form->get_action(); ?>" method="<?php echo $form->get_method(); ?>">
			<?php if ($table): //TODO make class="form-table" dynamic ? ?>
			<table class="form-table">
				<?php endif;

				foreach ( $form->get_input_fields() as $input_field ) {
					//$return is always false, because we started already an output buffer
					self::get_html_of_input_field( $input_field, $table, $th, false );
				}

				if ($table): ?>
			</table>
		<?php endif; ?>
			<input type="hidden" name="<?php echo Fum_Conf::$fum_unique_name_field_name ?>" value="<?php echo $form->get_unique_name(); ?>">
		</form>
		<?php

		if ( $return ) {
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}
	}

	private static function get_html_of_input_field( Fum_Html_Input_Field $input_field, $table = false, $th = true, $return = true ) {
		if ( $return ) {
			ob_start();
		}

		//If type is submit, we don't need a label or description
		if ( $input_field->get_type() == Html_Input_Type_Enum::SUBMIT ) {
			if ( $input_field->get_value() != '' ):
				?>
				<p>
					<input type="<?php echo $input_field->get_type(); ?>" value="<?php echo $input_field->get_value(); ?>" size="<?php echo $input_field->get_size(); ?>" id="<?php echo $input_field->get_id(); ?>" name="<?php echo $input_field->get_name(); ?>" />
				</p>
			<?php else: ?>
				<p>
					<input type="<?php echo $input_field->get_type(); ?>" value="<?php echo $input_field->get_title(); ?>" size="<?php echo $input_field->get_size(); ?>" id="<?php echo $input_field->get_id(); ?>" name="<?php echo $input_field->get_name(); ?>" />
				</p>
			<?php
			endif;


		}
		else {


			//title/label is independent of input type, so create it before distinction of cases
			if ( $table ): //Use table to format form
				?>
				<tr id="<?php echo $input_field->get_type(); ?>">
				<?php if ( $th ): ?>
				<th>
			<?php else: ?>
				<td>
			<?php endif; ?>
				<label for="<?php echo $input_field->get_id(); ?>"> <?php _e( $input_field->get_title() );
					if ( $input_field->get_required() ) {
						echo '*';
					} ?></label>
				<?php if ( $th ): ?>
				</th>
			<?php else: ?>
				</td>
			<?php
			endif; ?>
				<td>
			<?php else: //Use label only to format form?>
				<p><label for="<?php echo $input_field->get_id(); ?>"> <?php _e( $input_field->get_title() );
				if ( $input_field->get_required() ) {
					echo '*';
				} ?><br />
			<?php endif;

			switch ( $input_field->get_type() ) {

				case Html_Input_Type_Enum::TEXT:
				case Html_Input_Type_Enum::PASSWORD:
					?>
					<input type="<?php echo $input_field->get_type(); ?>" value="<?php echo $input_field->get_value(); ?>" size="<?php echo $input_field->get_size(); ?>" id="<?php echo $input_field->get_id(); ?>" name="<?php echo $input_field->get_name(); ?>" />
					<?php
					break;
				case Html_Input_Type_Enum::RADIO:
					foreach ( $input_field->get_possible_values() as $value ): ?>
						<input type="radio" name="<?php echo( $input_field->get_name() ); ?>" value="<?php echo $value; ?>" <?php checked( $input_field->get_value(), $value ); ?>/> <?php echo $value; ?>
						<br />
					<?php
					endforeach;
					?>
					<?php
					break;
				case Html_Input_Type_Enum::CHECKBOX:
					?>
					<input type="checkbox" name="<?php echo( $input_field->get_name() ); ?>" value="1" <?php checked( $input_field->get_value(), 1 ); ?>/>
					<?php
					break;
				case Html_Input_Type_Enum::SELECT:
					?>
					<select name="<?php echo $input_field->get_name(); ?>">
						<?php foreach ( $input_field->get_possible_values() as $value ): ?>
							<option value="<?php echo $value ?>" <?php selected( $input_field->get_value(), $value ); ?>><?php echo $value ?></option>
						<?php endforeach; ?>
					</select>
					<?php
					break;
				case Html_Input_Type_Enum::TEXTAREA:
					?>
					<textarea name="<?php echo( $input_field->get_name() ); ?>"><?php echo( $input_field->get_value() ); ?></textarea>
					<?php
					break;
			}
			if ( true !== $input_field->validate() ) {
				?>
				<br />
				<strong><?php _e( $input_field->validate()->get_error_message( $input_field->get_unique_name() ) ); ?></strong>
			<?php
			}
			if ( $table ): ?>
				</td>
				</tr>
			<?php else: ?>
				</label>
				</p>
			<?php endif;
			if ( $input_field->get_do_action() != NULL ) {
				do_action( $input_field->get_do_action() );
			}
		}
		if ( $return ) {
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}
	}

} 