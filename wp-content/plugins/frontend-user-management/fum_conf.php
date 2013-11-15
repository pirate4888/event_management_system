<?php
/**
 * @author  Christoph Bessei
 * @version 0.01
 */

class Fum_Conf {
	private static $fum_register_form_name = 'fum_register_form';
	private static $fum_register_form_shortcode = 'fum_register_form';
	private static $fum_login_form_name = 'fum_login_form';
	private static $fum_login_form_shortcode = 'fum_login_form';
	private static $fum_edit_form_name = 'fum_edit_form';
	private static $fum_edit_form_shortcode = 'fum_edit_form';

	private static $fum_post_type = 'fum_post';
	private static $fum_post_type_label = 'fum_posts';

	private static $fum_logout_arg_name = 'fum_action';
	private static $fum_logout_arg_value = 'logout';

	private static $fum_login_arg_name = 'fum_login';
	private static $fum_login_failed_arg_value = 'failed';

	/**
	 * @param string $fum_login_arg_name
	 */
	public static function set_fum_login_arg_name( $fum_login_arg_name ) {
		self::$fum_login_arg_name = $fum_login_arg_name;
	}

	/**
	 * @return string
	 */
	public static function get_fum_login_arg_name() {
		return self::$fum_login_arg_name;
	}

	/**
	 * @param string $fum_login_failed_arg_value
	 */
	public static function set_fum_login_failed_arg_value( $fum_login_failed_arg_value ) {
		self::$fum_login_failed_arg_value = $fum_login_failed_arg_value;
	}

	/**
	 * @return string
	 */
	public static function get_fum_login_failed_arg_value() {
		return self::$fum_login_failed_arg_value;
	}


	/**
	 * @param string $fum_logout_arg_name
	 */
	public static function set_fum_logout_arg_name( $fum_logout_arg_name ) {
		self::$fum_logout_arg_name = $fum_logout_arg_name;
	}

	/**
	 * @return string
	 */
	public static function get_fum_logout_arg_name() {
		return self::$fum_logout_arg_name;
	}

	/**
	 * @param string $fum_logout_arg_value
	 */
	public static function set_fum_logout_arg_value( $fum_logout_arg_value ) {
		self::$fum_logout_arg_value = $fum_logout_arg_value;
	}

	/**
	 * @return string
	 */
	public static function get_fum_logout_arg_value() {
		return self::$fum_logout_arg_value;
	}

	/**
	 * @param string $fum_post_type
	 */
	public static function set_fum_post_type( $fum_post_type ) {
		self::$fum_post_type = $fum_post_type;
	}

	/**
	 * @return string
	 */
	public static function get_fum_post_type() {
		return self::$fum_post_type;
	}

	/**
	 * @param string $fum_post_type_label
	 */
	public static function set_fum_post_type_label( $fum_post_type_label ) {
		self::$fum_post_type_label = $fum_post_type_label;
	}

	/**
	 * @return string
	 */
	public static function get_fum_post_type_label() {
		return self::$fum_post_type_label;
	}

	/**
	 * @param string $edit_form_shortcode
	 */
	public static function set_fum_edit_form_shortcode( $edit_form_shortcode ) {
		self::$fum_edit_form_shortcode = $edit_form_shortcode;
	}

	/**
	 * @return string
	 */
	public static function get_fum_edit_form_shortcode() {
		return self::$fum_edit_form_shortcode;
	}

	/**
	 * @param string $login_form_shortcode
	 */
	public static function set_fum_login_form_shortcode( $login_form_shortcode ) {
		self::$fum_login_form_shortcode = $login_form_shortcode;
	}

	/**
	 * @return string
	 */
	public static function get_fum_login_form_shortcode() {
		return self::$fum_login_form_shortcode;
	}

	/**
	 * @param string $register_form_shortcode
	 */
	public static function set_fum_register_form_shortcode( $register_form_shortcode ) {
		self::$fum_register_form_shortcode = $register_form_shortcode;
	}

	/**
	 * @return string
	 */
	public static function get_fum_register_form_shortcode() {
		return self::$fum_register_form_shortcode;
	}

	/**
	 * @param string $edit_form_name
	 */
	public static function set_fum_edit_form_name( $edit_form_name ) {
		self::$fum_edit_form_name = $edit_form_name;
	}

	/**
	 * @return string
	 */
	public static function get_fum_edit_form_name() {
		return self::$fum_edit_form_name;
	}

	/**
	 * @param string $login_form_name
	 */
	public static function set_fum_login_form_name( $login_form_name ) {
		self::$fum_login_form_name = $login_form_name;
	}

	/**
	 * @return string
	 */
	public static function get_fum_login_form_name() {
		return self::$fum_login_form_name;
	}

	/**
	 * @param string $register_form_name
	 */
	public static function set_fum_register_form_name( $register_form_name ) {
		self::$fum_register_form_name = $register_form_name;
	}

	/**
	 * @return string
	 */
	public static function get_fum_register_form_name() {
		return self::$fum_register_form_name;
	}


}