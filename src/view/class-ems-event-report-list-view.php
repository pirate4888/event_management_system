<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Report_List_View {
	/**
	 * @param Ems_Event_Report[] $list
	 *
	 * @return array|mixed|string
	 */
	public function print_event_report_list( array $list ) {
		foreach ( $list as $report ) {
			return $report->get_connected_event_title();
		}
	}
}