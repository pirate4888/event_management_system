<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Deactivation {
	public static function deactivate_plugin() {
		Fum_Deactivation::deactivate_plugin();
		error_log( 'ems_deactivate_plugin' );
	}

} 