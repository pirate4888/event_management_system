<?php

/**
 * @author Christoph Bessei
 * @version
 */
interface Ems_Post_Interface {
	public static function register_post_type();

	public static function get_post_type();

	/**
	 * Returns the needed capabilities to create, edit, delete and read private posts of this type
	 * @return array  array of needed caps which fits the format of add_role()
	 */
	public static function get_admin_capabilities();

	/**
	 * If you assign a 'capability_type' and then take a look into the $GLOBALS['wp_post_types']['your_cpt_name'] array, then you'll see the following:
	 *<p>
	 * [cap] => stdClass Object<br/>
	 * (<br/>
	 * [edit_post]     => "edit_{$capability_type}"<br/>
	 * [read_post]     => "read_{$capability_type}"<br/>
	 * [delete_post]     => "delete_{$capability_type}"<br/>
	 * [edit_posts]     => "edit_{$capability_type}s"<br/>
	 * [edit_others_posts]   => "edit_others_{$capability_type}s"<br/>
	 * [publish_posts]     => "publish_{$capability_type}s"<br/>
	 * [read_private_posts]   => "read_private_{$capability_type}s"<br/>
	 * [delete_posts]           => "delete_{$capability_type}s"<br/>
	 * [delete_private_posts]   => "delete_private_{$capability_type}s"<br/>
	 * [delete_published_posts] => "delete_published_{$capability_type}s"<br/>
	 * [delete_others_posts]    => "delete_others_{$capability_type}s"<br/>
	 * [edit_private_posts]     => "edit_private_{$capability_type}s"<br/>
	 * [edit_published_posts]   => "edit_published_{$capability_type}s"<br/>
	 * )<br/>
	 *</p>
	 * Note the "s" at the end of plural capabilities.
	 *
	 * @return string|array  string or array which can be used as capability_type in register_post_type
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type register_post_type (WordPress Codex)
	 */
	public static function get_capability_type();

	public static function get_edit_capability();

	//TODO the model should only know all meta fields, but not which one are printed. Move this to ???
	public static function get_custom_columns();

	public function save_post();

	/**
	 * @param WP_Post|int $post ID of a post or a WP_Post object
	 */
	public function __construct( $post );

	public function get_meta_value( $name );

	/**
	 * Returns a meta value in a "nice" format. e.g. not the post ID but the post title, not an array but a string etc.
	 *
	 * @param string $name name of the meta value
	 *
	 * @return string print friendly string
	 */
	public function get_meta_value_printable( $name );
} 