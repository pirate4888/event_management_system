<form name="registerform" id="registerform" action="<?php echo get_permalink(); ?>" method="post">
	<p>
		<label for="user_login"><?php _e( 'Username' ) ?><br />
			<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr( wp_unslash( $user_login ) ); ?>" size="25" /></label>
	</p>

	<p>
		<label for="user_email"><?php _e( 'E-mail' ) ?><br />
			<input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr( wp_unslash( $user_email ) ); ?>" size="25" /></label>
	</p>
	<?php
	//Check if we should generate a password or if the user should enter one
	if ( get_option( Fum_Conf::$fum_register_form_generate_password_option ) ) {
		?>
		<p id="reg_passmail"><?php _e( 'A password will be e-mailed to you.' ) ?></p>
	<?php
	}
	else {
		?>
		<p>
			<label for="user_pass"><?php _e( 'Password' ) ?><br />
				<input type="password" name="user_pass" id="user_pass" class="input" value='' size="25" /></label>
		</p>
	<?php
	}

	/**
	 * Fires following the 'E-mail' field in the user registration form.
	 *
	 * @since 2.1.0
	 */
	do_action( 'register_form' );
	?>
	<br class="clear" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
	<input type="hidden" name="register_form_sent" id="register_form_sent" />

	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Register' ); ?>" />
	</p>
</form>

<p id="nav">
	<a href="<?php echo esc_url( get_permalink( Fum_Conf::$fum_login_form_name ) ); ?>"><?php _e( 'Log in' ); ?></a> |
	<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Password Lost and Found' ) ?>"><?php _e( 'Lost your password?' ); ?></a>
</p>