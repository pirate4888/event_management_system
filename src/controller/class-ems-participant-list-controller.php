<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Participant_List_Controller {
	public static $parent_slug = 'ems_participant_list';
	/** @var  Fum_Option_Page[] $pages */
	public static $pages;

	public static function get_participant_lists() {
		if ( ! current_user_can( Ems_Event::get_edit_capability() ) ) {
			global $wp;
			?>
			<p><strong>Du hast keinen Zugriff auf diese Seite.</strong></p>
			<?php
			if ( ! is_user_logged_in() ) {
				$redirect_url = add_query_arg( array( 'select_event' => $_REQUEST['select_event'] ), get_permalink() );
				?>
				<p>
					<a href="<?php echo wp_login_url( $redirect_url ); ?>">Anmelden</a>
				</p>
			<?php
			}

			return;
		}
		$events = Ems_Event::get_active_events();

		$form = new Fum_Html_Form( 'fum_parctipant_list_form', 'fum_participant_list_form', '#' );
		$form->add_input_field( new Fum_Html_Input_Field( 'select_event', 'select_event', new Html_Input_Type_Enum( Html_Input_Type_Enum::SELECT ), 'Eventauswahl', 'select_event', false ) );


		foreach ( $events as $event ) {

			$date_time = $event->get_start_date_time();
			$year      = '';
			if ( null !== $date_time ) {
				$timestamp = $date_time->getTimestamp();
				$year      = date( 'Y', $timestamp );
			}

			$title             = $event->post_title . ' ' . $year . ' (' . count( Ems_Event_Registration::get_registrations_of_event( $event->ID ) ) . ')';
			$value             = 'ID_' . $event->ID;
			$possible_values   = $form->get_input_field( 'select_event' )->get_possible_values();
			$possible_values[] = array( 'title' => $title, 'value' => $value, 'ID' => $event->ID );
			$form->get_input_field( 'select_event' )->set_possible_values( $possible_values );
		}
		if ( isset( $_REQUEST[ Fum_Conf::$fum_input_field_select_event ] ) ) {
			$form->get_input_field( Fum_Conf::$fum_input_field_select_event )->set_value( $_REQUEST[ Fum_Conf::$fum_input_field_select_event ] );
		}
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );
		Fum_Form_View::output( $form );

		//print particpant list if event selected
		if ( isset( $_REQUEST[ Fum_Conf::$fum_input_field_select_event ] ) ) {
			$id       = preg_replace( "/[^0-9]/", "", $_REQUEST[ Fum_Conf::$fum_input_field_select_event ] );
			$registrations = Ems_Event_Registration::get_registrations_of_event( $id );
			if ( empty( $registrations ) ) {
				echo '<p><strong>Bisher gibt es keine Anmeldungen für dieses Event</strong></p>';

				return;
			}

			//Create array with all relevant data
			$participant_list = array();

			foreach ( $registrations as $registration ) {

				$user_data = array_intersect_key( Fum_User::get_user_data( $registration->get_user_id() ), Fum_Html_Form::get_form( Fum_Conf::$fum_event_register_form_unique_name )->get_unique_names_of_input_fields() );
				if ( empty( $user_data ) ) {
					continue;
				}
				unset( $user_data[ Fum_Conf::$fum_input_field_submit ] );
				unset( $user_data[ Fum_Conf::$fum_input_field_accept_agb ] );
				$merged_array = array_merge( $user_data, $registration->get_data() );
				$participant_list[] = $merged_array;
			}

			$excel_array_private = array();
			$excel_array_public  = array();

			$public_fields = array(
				"Vorname",
				"Nachname",
				"E-Mail",
				"Stadt",
				"Postleitzahl",
				"Bundesland",
				"Telefonnummer",
				"Handynummer",
				"Suche Mitfahrgelegenheit",
				"Biete Mitfahrgelgenheit"
			);


			$order = $participant_list[0];

			//Generate title row
			foreach ( $order as $title => $value ) {
				$excel_array_private[0][] = Fum_Html_Input_Field::get_input_field( $title )->get_title();
				if ( in_array( Fum_Html_Input_Field::get_input_field( $title )->get_title(), $public_fields ) ) {
					$excel_array_public[0][] = Fum_Html_Input_Field::get_input_field( $title )->get_title();
				}
			}

			//Generate entry rows
			foreach ( $participant_list as $index => $participant ) {
				foreach ( $order as $title => $unused ) {
					//$index+1 because $index=0 is the title row
					$excel_array_private[ $index + 1 ][] = ( 0 === $participant[ $title ] ? 'Nein' : ( "1" === $participant[ $title ] ? 'Ja' : $participant[ $title ] ) );
					if ( in_array( Fum_Html_Input_Field::get_input_field( $title )->get_title(), $public_fields ) ) {
						$excel_array_public[ $index + 1 ][] = ( 0 === $participant[ $title ] ? 'Nein' : ( "1" === $participant[ $title ] ? 'Ja' : $participant[ $title ] ) );
					}
				}
			}

			//TODO Should be in view
			//Print html table
			?>
			<div style="overflow:auto;">
				<table>
					<thead>
					<tr>
						<?php foreach ( $order as $title => $value ): ?>
							<th><?php echo Fum_Html_Input_Field::get_input_field( $title )->get_title(); ?></th>
						<?php endforeach; ?>
					</tr>

					</thead>
					<tbody>
					<?php foreach ( $participant_list as $participant ): ?>
						<tr>
							<?php foreach ( $order as $title => $unused ): ?>
								<td><?php echo( 0 === $participant[ $title ] ? 'Nein' : ( "1" === $participant[ $title ] ? 'Ja' : $participant[ $title ] ) ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php
			//Create excel table
			$objPHPExcel = new PHPExcel();

			$myWorkSheet = new PHPExcel_Worksheet( $objPHPExcel, 'Teilnehmerliste' );

			//Use customized value binder so phone numbers with leading zeros are preserved
			PHPExcel_Cell::setValueBinder( new PHPExcel_Value_Binder() );

			//Remove default worksheet named "Worksheet"
			$objPHPExcel->removeSheetByIndex( 0 );

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$objPHPExcel->addSheet( $myWorkSheet, 0 );
			$objPHPExcel->setActiveSheetIndex( 0 );
			$objPHPExcel->getActiveSheet()->fromArray( $excel_array_private );

			$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
			$filename  = $id . '.xlsx';
			$objWriter->save( Event_Management_System::get_plugin_path() . $filename );
			echo '<p><a href="' . Event_Management_System::get_plugin_url() . $filename . '">Teilnehmerliste für Eventleiter als Excelfile downloaden</a></p>';

			//Public participant list excel table
			$objPHPExcel = new PHPExcel();

			$myWorkSheet = new PHPExcel_Worksheet( $objPHPExcel, 'Teilnehmerliste' );

			//Use customized value binder so phone numbers with leading zeros are preserved
			PHPExcel_Cell::setValueBinder( new PHPExcel_Value_Binder() );

			//Remove default worksheet named "Worksheet"
			$objPHPExcel->removeSheetByIndex( 0 );

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$objPHPExcel->addSheet( $myWorkSheet, 0 );
			$objPHPExcel->setActiveSheetIndex( 0 );
			$objPHPExcel->getActiveSheet()->fromArray( $excel_array_public );

			$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
			$filename = $id . "_public" . '.xlsx';
			$objWriter->save( Event_Management_System::get_plugin_path() . $filename );
			echo '<p><a href="' . Event_Management_System::get_plugin_url() . $filename . '">Teilnehmerliste für Teilnehmer als Excelfile downloaden</a></p>';
		}
	}
}