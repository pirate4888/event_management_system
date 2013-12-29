<?php
/**
 * @author Christoph Bessei
 * @version
 */

class Ems_Participant_List_Controller {
	public static $parent_slug = 'ems_participant_list';
	/** @var  Ems_Option_Page[] $pages */
	public static $pages;

	public static function get_participant_lists() {
		if ( ! current_user_can( 'edit_event' ) ) {
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
		$posts = Ems_Event::get_events();

		$form = new Fum_Html_Form( 'fum_parctipant_list_form', 'fum_participant_list_form', '#' );
		$form->add_input_field( new Fum_Html_Input_Field( 'select_event', 'select_event', new Html_Input_Type_Enum( Html_Input_Type_Enum::SELECT ), 'Eventauswahl', 'select_event', false ) );

		/** @var WP_Post[] $posts */
		foreach ( $posts as $post ) {
			/** @var DateTime $date_time */
			$date_time = get_post_meta( $post->ID, 'ems_start_date', true );

			$timestamp = $date_time->getTimestamp();
			$year      = date( 'Y', $timestamp );

			$title             = $post->post_title . ' ' . $year . ' (' . count( Ems_Event_Registration::get_registrations_of_event( $post->ID ) ) . ')';
			$value             = 'ID_' . $post->ID;
			$possible_values   = $form->get_input_field( 'select_event' )->get_possible_values();
			$possible_values[] = array( 'title' => $title, 'value' => $value, 'ID' => $post->ID );
			$form->get_input_field( 'select_event' )->set_possible_values( $possible_values );
		}
		if ( isset( $_REQUEST[Fum_Conf::$fum_input_field_select_event] ) ) {
			$form->get_input_field( Fum_Conf::$fum_input_field_select_event )->set_value( $_REQUEST[Fum_Conf::$fum_input_field_select_event] );
		}
		$form->add_input_field( Fum_Html_Input_Field::get_input_field( Fum_Conf::$fum_input_field_submit ) );
		Fum_Form_View::output( $form );

		//print particpant list if event selected
		if ( isset( $_REQUEST[Fum_Conf::$fum_input_field_select_event] ) ) {
			$id            = preg_replace( "/[^0-9]/", "", $_REQUEST[Fum_Conf::$fum_input_field_select_event] );
			$registrations = Ems_Event_Registration::get_registrations_of_event( $id );
			if ( empty( $registrations ) ) {
				echo '<p><strong>Bisher gibt es keine Anmeldungen für dieses Event</strong></p>';
				return;
			}

			//Create array with all relevant data
			$participant_list = array();

			foreach ( $registrations as $registration ) {

				$user_data = array_intersect_key( Fum_User::get_user_data( $registration->get_user_id() ), Fum_Html_Form::get_form( Fum_Conf::$fum_event_register_form_unique_name )->get_unique_names_of_input_fields() );
				unset( $user_data[Fum_Conf::$fum_input_field_submit] );
				unset( $user_data[Fum_Conf::$fum_input_field_accept_agb] );
				$merged_array = array_merge( $user_data, $registration->get_data() );
//				ksort( $merged_array );
				$participant_list[] = $merged_array;
//				echo '<pre>';
//				print_r( $participant_list );
//				echo '</pre>';
			}

			$order = $participant_list[0];
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
								<td><?php echo( 0 === $participant[$title] ? 'Nein' : ( "1" === $participant[$title] ? 'Ja' : $participant[$title] ) ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php
			//Create excel table
			$objPHPExcel = new PHPExcel();

			//TODO Sort Array titles and values (maybe participant 1 has another order then participant 2 ), maybe check also html table order
			//Add title row on top of array
			foreach ( $participant_list as $key => $participant ) {
				ksort( $participant );
				$participant_list[$key] = $participant;

			}

			$titles = array();

			foreach ( $participant_list[0] as $title => $value ) {
				$titles[] = $title;
			}
			sort( $titles );
			$participant_list = array_merge( array( $titles ), $participant_list );
			// Create a new worksheet called "My Data"
			$myWorkSheet = new PHPExcel_Worksheet( $objPHPExcel, 'Teilnehmerliste' );

			// Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
			$objPHPExcel->addSheet( $myWorkSheet, 0 );
			$objPHPExcel->getActiveSheet()->fromArray( $participant_list );

			$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
			$filename  = $id . '.xlsx';
			$objWriter->save( Event_Management_System::get_plugin_path() . $filename );
			echo '<p><a href="' . Event_Management_System::get_plugin_url() . $filename . '">Teilnehmerliste als Excelfile downloaden</a></p>';
		}
	}
}