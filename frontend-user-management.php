<?php
/**
 * Plugin Name: Front End User Management
 * Plugin URI: https://www.schwarzwald-falke.de
 * Description: A brief description of the Plugin.
 * Version: 0.01
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL2
 */
 
  // [ufm_create_user]
function ufm_create_user_func( $atts ) {
	if(isset($_POST["register_form_sent"])) {
	  $return = wp_insert_user($_POST);
	  
	  if ( is_wp_error($return) ) {
	    echo $return->get_error_message();
	    } else {
	    wp_new_user_notification($return);
	    }
	    
	  } else {
	
	/*Field names from http://codex.wordpress.org/Function_Reference/wp_insert_user*/
	?>
	
	<form action="<?php echo get_permalink(); ?>" method="POST">
	  Username: <input type="text" name="user_login" /><br/>
	  Passwort: <input type="password" name="user_pass" /><br/>
	  E-Mail:   <input type="text" name="user_email" /><br/>
	  Scheinnummer: <input type="text" name="licensenumber" />
		    <input type="submit" value="Registrieren" name="register_form_sent" /><br/>
	</form>
	<?php
	}
}
add_shortcode( 'ufm_create_user', 'ufm_create_user_func' );
 
 // [ufm_edit_user]
function ufm_edit_user_func( $atts ) {
	
      echo "<pre>";
      print_r(get_user_meta(get_current_user_id()));
      echo "</pre>";
}
add_shortcode( 'ufm_edit_user', 'ufm_edit_user_func' );
