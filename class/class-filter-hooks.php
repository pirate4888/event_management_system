<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Filter_Hooks {


	public static function add_filter_hooks() {
		add_filter( 'force_ssl', array( new Front_End_Form(), 'use_ssl_on_front_end_form' ), 1, 3 );
	}

} 