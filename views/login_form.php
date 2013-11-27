<form name="loginform" id="loginform" action="<?php echo get_permalink(); ?>" method="post">
	<p>
		<label for="user_login"><?php _e( 'Username' ) ?><br />
			<input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr( $user_login ); ?>" size="20" /></label>
	</p>

	<p>
		<label for="user_pass"><?php _e( 'Password' ) ?><br />
			<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" /></label>
	</p>
	<?php
	/**
	 * Fires following the 'Password' field in the login form.
	 *
	 * @since 2.1.0
	 */
	do_action( 'login_form' );
	?>
	<p class="forgetmenot">
		<label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked( $rememberme ); ?> /> <?php esc_attr_e( 'Remember Me' ); ?>
		</label></p>

	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Log In' ); ?>" />
		<?php if ( $interim_login ) { ?>
			<input type="hidden" name="interim-login" value="1" />
		<?php
		}
		else {
			?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
		<?php } ?>
		<?php if ( $customize_login ) : ?>
			<input type="hidden" name="customize-login" value="1" />
		<?php endif; ?>
		<input type="hidden" name="testcookie" value="1" />
	</p>
</form>