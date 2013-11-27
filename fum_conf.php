<?php
/**
 * @author  Christoph Bessei
 * @version 0.01
 */

class Fum_Conf {
	public static $fum_register_form_name = 'fum_register_form';
	public static $fum_register_form_shortcode = 'fum_register_form';
	public static $fum_login_form_name = 'fum_login_form';
	public static $fum_login_form_shortcode = 'fum_login_form';
	public static $fum_edit_form_name = 'fum_edit_form';
	public static $fum_edit_form_shortcode = 'fum_edit_form';

	public static $fum_post_type = 'fum_post';
	public static $fum_post_type_label = 'fum_posts';

	public static $fum_logout_arg_name = 'fum_action';
	public static $fum_logout_arg_value = 'logout';

	public static $fum_login_arg_name = 'fum_login';
	public static $fum_login_failed_arg_value = 'failed';

	public static $fum_login_form_option_group = 'fum_login_form_options';

	public static $fum_register_form_option_group = 'fum_register_form_options';
	public static $fum_register_form_generate_password_option = 'fum_register_form_generate_password_option';
	public static $fum_register_form_use_activation_mail_option = 'fum_register_form_use_activation_mail_option';

	/*option_name of the available html forms and input fields in wp_option*/
	public static $option_name_forms = 'fum_forms';
	public static $option_name_input_fields = 'fum_input_fields';
}