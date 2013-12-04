<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Fum_User extends Fum_Observable implements Fum_Observer {
	public function update( Fum_Observable $o ) {
		//Check if input_field contains the data of a default wordpress user field
		if ( in_array( $input_field->get_name(), Fum_Conf::$fum_wordpress_fields ) ) {
			$user_data[$input_field->get_name()] = $_REQUEST[$input_field->get_name()];
		}
		else {
			update_user_meta( get_current_user_id(), $input_field->get_name(), $_REQUEST[$input_field->get_name()] );
		}

		if ( ! empty( $user_data ) ) {
			wp_update_user( $user_data );
		}
	}
} 