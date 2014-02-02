<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Activation {
	public static function activate_plugin() {

		//Setup frontend user management first (must be called here, because a second 'register_activation_hook' didn't work
		Fum_Activation::activate_plugin();
		error_log( 'ems_activate_plugin' );
		$caps = array( 'edit_event'          => true,
									 'read_event'          => true,
									 'delete_event'        => true,
									 'edit_events'         => true,
									 'edit_others_events'  => true,
									 'publish_events'      => true,
									 'read_private_events' => true,
									 'read'                => true,

		);

		remove_role( 'eventleiter' );
		add_role( 'eventleiter', 'Eventleiter', $caps );


		$admin_role = get_role( 'administrator' );
		//Add Caps to Administrator and super admin
		foreach ( $caps as $key => $value ) {
			$admin_role->add_cap( $key );
		}

		$post_id = Fum_Post::add_post( 'Eventverwaltung', 'Eventverwaltung', '[ems_eventverwaltung]' );
		update_option( 'ems_eventmanagement_page', $post_id );
		$post_id = Fum_Post::add_post( 'Teilnehmerlisten', 'Teilnehmerlisten', '[ems_teilnehmerlisten]' );
		update_option( 'ems_partcipant_list_page', $post_id );

	}

} 