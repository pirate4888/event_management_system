<?php
/**
 * Plugin Name: Frontend User Management
 * Plugin URI: https://github.com/SchwarzwaldFalke/frontend-user-management
 * Description: Plugin which allows user to register, login and edit their user profile. It also adds activation mails during user registration
 * Version: 0.01
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */

class Frontend_User_Management {
	private $plugin_path = NULL;
	private $plugin_url = NULL;
	private $activation_email = NULL;
	private $dhv_jugend_form = NULL;

	public function __construct() {
		require_once( $this->plugin_path . "class/class-dhv-jugend-form.php" );
		$this->dhv_jugend_form =  new DHV_Jugend_Form();
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		require_once( $this->plugin_path . "class/class-activation-email.php" );
		$this->activation_email = new Activation_Email();

		//Add user activation key field to all current users and mark them as activated
		register_activation_hook( __FILE__, array( $this, 'plugin_activated' ) );

		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivated' ) );
		/*
				register_uninstall_hook( __FILE__, 'plugin_uninstall' );*/
		//since 0.01
		add_shortcode( "fum_create_user", array( $this, "fum_create_user_func" ) );
		add_shortcode( "fum_edit_user", array( $this, "fum_edit_user_func" ) );
		add_shortcode( "fum_login_user", array( $this, "fum_login_user_func" ) );

		//TODO Currently the Login/Logout etc can only be at the admin bar, make it more flexible
		show_admin_bar( true );

		add_action( 'wp_before_admin_bar_render', array( $this, 'create_admin_bar' ) );

		add_action('admin_menu', array($this,'redirect_to_home_if_not_admin'));

		add_action( 'personal_options_update', array($this, 'post_lock_update') );
		add_action( 'edit_user_profile_update', array($this, 'post_lock_update') );

	}


	public function redirect_to_home_if_not_admin() {
		if ( is_super_admin() ) {
			return;
		} else {
			wp_redirect(get_home_url());
		}
	}
	public function create_admin_bar() {

		if ( is_super_admin() ) {
			return;
		}


		if ( is_user_logged_in() ) {
			$loginout_link  = wp_logout_url( get_permalink() );
			$loginout_title = "Ausloggen";
		}
		else {
			$loginout_link  = wp_login_url( get_permalink() );
			$loginout_title = "Einloggen";
		}


		global $wp_admin_bar;
		$this->clear_admin_bar( $wp_admin_bar );
		$wp_admin_bar->add_menu( array(
			'id'    => 1,
			'href'  => $loginout_link,
			'title' => $loginout_title,
		) );


	}

	private function clear_admin_bar( $admin_bar ) {

		$nodes = $admin_bar->get_nodes();
		foreach ( $nodes as $node ) {
			$admin_bar->remove_node( $node->id );
		}
	}

	// [fum_create_user]
	function fum_create_user_func() {
		if ( isset( $_POST["register_form_sent"] ) ) {
			$return = wp_insert_user( $_POST );

			if ( is_wp_error( $return ) ) {
				echo $return->get_error_message();
			}
			else {
				wp_new_user_notification( $return );
			}

		}
		else {
			/*Field names from http://codex.wordpress.org/Function_Reference/wp_insert_user*/
			?>
			<form action="<?php echo get_permalink(); ?>" method="POST">
				<label>Username:</label> <input type="text" name="user_login" /><br />
				<label>Passwort:</label> <input type="password" name="user_pass" /><br />
				<label>E-Mail:</label> <input type="text" name="user_email" /><br />
				<label>Scheinnummer:</label> <input type="text" name="licensenumber" /><br />
				<input type="submit" value="Registrieren" name="register_form_sent" /><br />
			</form>
		<?php
		}
	}


	// [ufm_edit_user]
	function fum_edit_user_func($user_id = null ) {

		global $userdata, $wp_http_referer;
		get_currentuserinfo();

		if ( !(function_exists( 'get_user_to_edit' )) ) {
			require_once(ABSPATH . '/wp-admin/includes/user.php');
		}

		if ( !(function_exists( '_wp_get_user_contactmethods' )) ) {
			require_once(ABSPATH . '/wp-includes/registration.php');
		}

		if ( !$user_id ) {
			$current_user = wp_get_current_user();
			$user_id = $user_ID = $current_user->ID;
		}
		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'update-profile_' . $user_id );
			$errors = edit_user( $user_id );
			if ( is_wp_error( $errors ) ) {
				$message = $errors->get_error_message();
				$style = 'error';
			} else {
				$message = '<strong>Profil erfolgreich gespeichert</strong>';
				$style = 'success';
				do_action( 'personal_options_update', $user_id );
			}
		}

		$profileuser = get_user_to_edit( $user_id );

		if ( isset( $message ) ) {
			echo '<div class="' . $style . '">' . $message . '</div>';
		}

		echo $this->dhv_jugend_form->get_form($user_id);
	}

	function post_lock_update( $user_id ) {
		if ( is_admin() && current_user_can( 'edit_users' ) ) {
			update_user_meta( $user_id, 'wpuf_postlock', $_POST['wpuf_postlock'] );
			update_user_meta( $user_id, 'wpuf_lock_cause', $_POST['wpuf_lock_cause'] );
			update_user_meta( $user_id, 'wpuf_sub_validity', $_POST['wpuf_sub_validity'] );
			update_user_meta( $user_id, 'wpuf_sub_pcount', $_POST['wpuf_sub_pcount'] );
		}
	}

	// [fum_login_user]
	public function fum_login_user_func() {

		if ( isset( $_POST["wp-submit"] ) ) {
			$return = wp_signon();

			if ( is_wp_error( $return ) ) {
				echo $return->get_error_message();
			}
			else {
				echo "Eingeloggt";
			}

		}
		else if ( is_user_logged_in() ) {
			echo "Bereits eingeloggt";
			return;
		}
		else {
			wp_login_form();
		}
	}

	public function plugin_activated() {
		$this->activation_email->plugin_activated();
	}

	public function plugin_deactivated() {
		$this->activation_email->plugin_deactivated();
	}

	public function plugin_uninstall() {
		$this->activation_email->plugin_uninstall();
	}
}


new Frontend_User_Management(); //start plugin

