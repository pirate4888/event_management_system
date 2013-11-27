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
	private $option_fum_category_id = 'fum_category';
	private $plugin_path = NULL;

	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );
		//Set path to plugin dir
		$this->plugin_path = plugin_dir_path( __FILE__ );


		$this->register_hooks();
		$this->init_plugin();
		Action_Hooks::add_action_hooks();
		$this->add_filter();

		add_action( 'admin_print_footer_scripts', array( $this, 'my_admin_print_footer_scripts' ) );

	}


	/*	function my_admin_enqueue_scripts() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );

		}*/

	function my_admin_print_footer_scripts() {
		$pointer_content = '<h3>iShift | Notice</h3>';
		$pointer_content .= '<p>Added new functions to Edit Post section and few more options for users (authors and subscribers only).</p>';
		?>
		<script type="text/javascript">
			$(document).ready(function () {
				// Tooltip only Text
				$('#icon_fum').hover(function () {
					// Hover over code
					var title = $(this).attr('title');
					$(this).data('tipText', title).removeAttr('title');
					$('<p class="tooltip"></p>')
							.text(title)
							.appendTo('body')
							.fadeIn('slow');
				},function () {
					// Hover out code
					$(this).attr('title', $(this).data('tipText'));
					$('.tooltip').remove();
				}).mousemove(function (e) {
							var mousex = e.pageX + 20; //Get X coordinates
							var mousey = e.pageY + 10; //Get Y coordinates
							$('.tooltip')
									.css({ top: mousey, left: mousex })
						});
			});
		</script>
	<?php
	}


	private function add_filter() {
		add_filter( 'force_ssl', array( new Front_End_Form(), 'use_ssl_on_front_end_form' ), 1, 3 );
	}

	private function init_plugin() {
		new Dhv_Jugend_Form();
		new Change_Wp_Url();
		new Activation_Email();

		//Add ShortCodes of user forms(register,login,edit)
		$front_end_form = new Front_End_Form();
		$front_end_form->add_shortcode_of_register_form( Fum_Conf::$fum_register_form_shortcode );
		$front_end_form->add_shortcode_of_login_form( Fum_Conf::$fum_login_form_shortcode );
		$front_end_form->add_shortcode_of_edit_form( Fum_Conf::$fum_edit_form_shortcode );
	}

	private function register_hooks() {
		//Add user activation key field to all current users and mark them as activated
		register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' ) );
	}

	public function plugin_activate() {
		$front_end_form = new Front_End_Form();
		$post_ids       = $front_end_form->add_form_posts();
		add_option( Fum_Conf::$fum_register_form_name, $post_ids[Fum_Conf::$fum_register_form_name] );
		add_option( Fum_Conf::$fum_login_form_name, $post_ids[Fum_Conf::$fum_login_form_name] );
		add_option( Fum_Conf::$fum_edit_form_name, $post_ids[Fum_Conf::$fum_edit_form_name] );

		$activation_email = new Activation_Email();
		$activation_email->plugin_activated();
	}

	public function plugin_deactivate() {
		$fum_post = new Fum_Post();
		$fum_post->remove_all_fum_posts();

		delete_option( Fum_Conf::$fum_register_form_name );
		delete_option( Fum_Conf::$fum_login_form_name );
		delete_option( Fum_Conf::$fum_edit_form_name );
		$activation_email = new Activation_Email();
		$activation_email->plugin_deactivated();
	}

	public function autoload( $class_name ) {
		if ( 'Fum_Conf' === $class_name ) {
			require_once( $this->plugin_path . 'fum_conf.php' );
		}

		if ( 'Options' === $class_name ) {
			require_once( $this->plugin_path . 'options.php' );
		}
		if ( 'Option_Page_Controller' === $class_name ) {
			require_once( $this->plugin_path . 'controller/class-option-page-controller.php' );
		}
		//Because of sucking wordpress name conventions class name != file name, convert it manually
		$class_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) . '.php' );

		if ( file_exists( $this->plugin_path . 'class/' . $class_name ) ) {
			require_once( $this->plugin_path . 'class/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'controller/' . $class_name ) ) {
			require_once( $this->plugin_path . 'controller/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'model/' . $class_name ) ) {
			require_once( $this->plugin_path . 'model/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'views/' . $class_name ) ) {
			require_once( $this->plugin_path . 'views/' . $class_name );
		}
		elseif ( file_exists( $this->plugin_path . 'views/fum_option_pages/' . $class_name ) ) {
			require_once( $this->plugin_path . 'views/fum_option_pages/' . $class_name );
		}
	}
}


new Frontend_User_Management(); //start plugin

