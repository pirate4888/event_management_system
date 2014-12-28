<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Menu {

	private static $added = array();

	/**
	 * Loop through all nav menu items checking whether the functionality has been enabled or not for them.
	 * If enabled, add in submenu items for all of their descendants
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post[] $items Array of nav menu items.
	 *
	 * @return array Potentially modified array of nav menu items.
	 */
	public static function add_children_to_menu( $items ) {


		$menu_order   = count( $items ) + 1000;
		$filter_added = false;

		foreach ( $items as $item ) {

			if ( $item->title == get_option( Ems_Conf::$ems_general_option_show_events_in_menu ) ) {
				$events = Ems_Event::get_active_events();

				$parent_field = 'post_parent';
				$parent_ID    = $item->ID;

			} else {
				$parent_ID = - 1;
				$events = new WP_Error( "Bla" );
			}

			if ( empty( $events ) || is_wp_error( $events ) ) {
				continue;
			}

			/** @var Ems_Event[] $events */
			// Menu items are being added, so later fix the "current" values for highlighting
			if ( ! $filter_added ) {
				add_filter( 'wp_nav_menu_objects', array( 'Ems_Menu', 'fix_menu_current_item' ) );
			}

			//Add Eventregistration as child
			$events[] = new Ems_Event( get_post( get_option( Fum_Conf::$fum_event_registration_page ) ) );

			// Add each child to the menu
			foreach ( $events as $event ) {
				//Set Title for menu
				$date = $event->get_formatted_date();
				if ( null !== $date ) {
					$event->post_title = $event->post_title . "(" . $date . ")";
				}

				//Check if $child is already an item in the menu
				if ( self::is_child_already_in_menu( $items, $event ) ) {
					continue;
				}
				$event->post_parent = $parent_ID;

				$child = wp_setup_nav_menu_item( $event );
				$child->db_id = $child->ID;

				self::$added[ $child->ID ] = true; // We'll need this later

				// Set the parent menu item.
				// When adding items as children of existing menu items, their IDs won't match up
				// which means that the parent value can't always be used.
				if ( $child->$parent_field == $item->object_id ) {
					$child->menu_item_parent = $item->ID; // Children
				} else {
					$child->menu_item_parent = $child->$parent_field; // Grandchildren, etc.
				}

//				// The menu_order has to be unique, so make up new ones
//				// The items are already sorted due to the get_pages()
				$menu_order ++;
				$child->menu_order = $menu_order;

				$items[] = $child;
			}
		}

		return $items;
	}

	/**
	 * Avoids duplicates posts in the nav_menu
	 * object_id of the nav_menu_item is the ID of the post to which it points
	 *
	 * @param WP_Post[] $items all menu items
	 * @param WP_Post   $child post which should be added to the menu
	 *
	 * @return bool
	 */
	private static function is_child_already_in_menu( $items, $child ) {

		foreach ( $items as $item ) {
			if ( $item->object_id == $child->ID ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Fixes the attributes of all ancestors of all menu items added by this plugin.
	 * This is to ensure that the selected functionality works.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Array of nav menu items.
	 *
	 * @return array Potentially modified array of nav menu items.
	 */
	public static function fix_menu_current_item( $items ) {
		$queried_object    = get_queried_object();
		$queried_object_id = (int) get_queried_object_id();

		// Only need to fix items added by this plugin
		if ( empty( $queried_object_id ) || empty( self::$added[ $queried_object_id ] ) ) {
			return $items;
		}

		// Get ancestors of currently displayed item
		if ( isset( $queried_object->term_id ) ) {
			$ancestors    = get_ancestors( $queried_object->term_id, $queried_object->taxonomy );
			$parent_field = 'parent';
			$type         = 'taxonomy';
		} elseif ( is_singular() ) {
			$ancestors    = get_post_ancestors( $queried_object_id );
			$parent_field = 'post_parent';
			$type         = 'post_type';
		} else {
			return $items;
		}

		$ancestors[] = $queried_object_id; // Needed to potentially add "current_page_item"

		foreach ( $items as $item ) {
			if ( ! in_array( $item->object_id, $ancestors ) ) {
				continue;
			}

			// Only highlight things of the same type because IDs can collide
			if ( $item->type !== $type ) {
				continue;
			}

			// See http://core.trac.wordpress.org/ticket/18643
			if ( $item->object_id == $queried_object_id ) {
				if ( ! in_array( 'current_page_item', $item->classes ) ) {
					$item->classes[] = 'current_page_item';
				}

				continue;
			}

			$item->current_item_ancestor = true;
			$item->classes[]             = 'current-menu-ancestor';
			$item->classes[]             = 'current_page_ancestor'; // See http://core.trac.wordpress.org/ticket/18643

			// If menu item is direct parent of current page
			if ( $item->object_id == $queried_object->$parent_field ) {
				$item->current_item_parent = true;
				$item->classes[]           = 'current-menu-parent';
				$item->classes[]           = 'current_page_parent'; // See http://core.trac.wordpress.org/ticket/18643
			}
		}

		return $items;
	}

} 