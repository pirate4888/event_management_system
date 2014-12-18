<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Report_Controller {
	public static function process_event_report_list() {
		$list_view = new Ems_Event_Report_List_View();

		return $list_view->print_event_report_list( Ems_Event_Report::get_event_reports() );
	}

} 