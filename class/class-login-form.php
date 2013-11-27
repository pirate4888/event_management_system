<?php
/**
 * @author  Christoph Bessei
 * @version 0.01
 */

class Login_Form {
	/**
	 * Wrapper function for wp_login_form, just to make code more consistent
	 *
	 */
	public function get_login_form( $echo = false, $redirect_to = NULL ) {
		wp_login_form( array( 'echo' => $echo, 'redirect_to' => $redirect_to ) );
	}
} 