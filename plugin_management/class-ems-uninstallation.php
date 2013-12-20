<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Ems_Uninstallation {
	public static function uninstall_plugin() {
		$role = get_role( 'administrator' );
		$caps = array( 'edit_event'          => true,
									 'read_event'          => true,
									 'edit_events'         => true,
									 'edit_others_events'  => true,
									 'publish_events'      => true,
									 'read_private_events' => true,
		);
		foreach ( $caps as $key => $value ) {
			$role->remove_cap( $key );
		}
		remove_role( 'eventleiter' );
	}
} 