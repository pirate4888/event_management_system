<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Name_Conversion {

	public static function convert_post_type_to_class_name( $post_type ) {
		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $post_type ) ) );
	}

} 