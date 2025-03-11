<?php

use Mpdf\Tag\Tr;

	$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';

	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';
	
	if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());$result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());pg_free_result($result);}
	
	if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
	if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}

	function ambulance_show_request_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';

		$query='SELECT visits.id_request, visits.id, brigades.name AS brigade, ';
		$query.='visits.start_dttm, ';
		$query.='pet_owners.is_veteran_infosoc, pet_owners.is_disabled_infosoc, pet_owners.is_blind_infosoc, pet_owners.is_family_disabled_children_infosoc,';

		$query.='string_agg(distinct users.fullname::character varying, \', \') AS specialists, ';
		$query.='visits.status, visits.description, visits.call_reason, visits.time_range, visits.id_owner, visits.status, visits.created_at, visits.visit_to_address, ';
		$query.='organizations.short_name AS org_name, addresses.name AS org_address, visits.ticket_number, pet_owners.fullname AS owner_name, ';
		$query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,';
		$query.='(UPPER(VISITS.TIME_RANGE)::time) AS end_time,';
		$query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, ';

		$query.='start_request_date, end_request_date, ';
		$query.='fact_start_dttm, fact_end_dttm, ';

		$query.='string_agg(contacts.id::character varying, \',\') AS contacts ';
		$query.='FROM visits ';
		$query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
		$query.='LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
		$query.='LEFT JOIN organizations ON visits.id_organization=organizations.id ';
		
		$query.='LEFT JOIN brigades ON brigades.id=visits.id_brigade ';
		$query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade ';
		$query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
		$query.='LEFT JOIN users ON users.id=specialists.id_user ';
		$query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
		$query.='WHERE visits.id_request='.string_formating_for_sql($id).' ';

		$query.='GROUP BY pet_owners.id, visits.id, visits.id_request, organizations.short_name, brigades.name, addresses.name ';
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id_request>'.$row['id_request'].'</rec_id_request>';
			echo '<rec_status>'.$row['status'].'</rec_status>';	
			echo '<rec_call_reason>'.$row['call_reason'].'</rec_call_reason>';
			echo '<rec_owner_name>'.$row['owner_name'].'</rec_owner_name>';
			echo '<rec_brigade>'.$row['brigade'].'</rec_brigade>';
			echo '<rec_specialists>'.$row['specialists'].'</rec_specialists>';
			echo '<rec_description>'.$row['description'].'</rec_description>';			
			echo '<rec_address>'.$row['visit_to_address'].'</rec_address>';
			echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
			echo '<rec_start_date>'.$row['start_date'].'</rec_start_date>';
			echo '<rec_start_time>'.$row['start_time'].'</rec_start_time>';
			echo '<rec_end_time>'.$row['end_time'].'</rec_end_time>';

			echo '<rec_start_dttm>'.$row['start_dttm'].'</rec_start_dttm>';

			echo '<rec_start_request_date>'.$row['start_request_date'].'</rec_start_request_date>';
			echo '<rec_end_request_date>'.$row['end_request_date'].'</rec_end_request_date>';
			echo '<rec_fact_start_dttm>'.$row['fact_start_dttm'].'</rec_fact_start_dttm>';
			echo '<rec_fact_end_dttm>'.$row['fact_end_dttm'].'</rec_fact_end_dttm>';

			echo '<rec_org_name>'.$row['org_name'].'</rec_org_name>';

			echo '<rec_is_veteran>'.$row['is_veteran_infosoc'].'</rec_is_veteran>';
			echo '<rec_is_disabled>'.$row['is_disabled_infosoc'].'</rec_is_disabled>';
			echo '<rec_is_blind>'.$row['is_blind_infosoc'].'</rec_is_blind>';
			echo '<rec_is_family_disabled_children>'.$row['is_family_disabled_children_infosoc'].'</rec_is_family_disabled_children>';
			// echo '<rec_is_large_family>'.$row['is_large_family'].'</rec_is_large_family>';
			// echo '<rec_is_veteran_of_labour>'.$row['is_veteran_of_labour'].'</rec_is_veteran_of_labour>';

			$query_='SELECT pets.*,breeds.name AS breed_name,species.name AS species_name FROM visit_pets ';
			$query_.='LEFT JOIN pets ON visit_pets.id_pet=pets.id ';
			$query_.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';
            $query_.='LEFT JOIN species ON pets.id_species = species.id ';
			$query_.='WHERE visit_pets.id_visit='.$row['id'].'';
			$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
			echo '<rec_pets>';
			while ($row_ = pg_fetch_assoc($result_)) {
				echo '<pet>';
				echo '<name>'.$row_['name'].'</name>';
				echo '<specie>'.$row_['species_name'].'</specie>';
				echo '<breed>'.$row_['breed_name'].'</breed>';
				echo '<sex>'.$row_['sex'].'</sex>';

				echo '<age>'.calculate_age($row_['birthday']).'</age>';
				echo '</pet>';
			}
			pg_free_result($result_);
			echo '</rec_pets>';

			$contacts = ($row['contacts'] != '')?explode(",",$row['contacts']):NULL;
			if(count($contacts) > 0){
				#контакты
				echo '<rec_contacts>';
				$q='SELECT contacts.name,contacts.main_flag,contacts.confirmed,contact_types.name AS contact_type_title, contacts.id_contact_type AS contact_type_id FROM contacts ';
				$q.='LEFT JOIN contact_types ON contacts.id_contact_type = contact_types.id ';
				$q.='WHERE ';

				for($i=0; $i<count($contacts);$i++){
					if($i>0){$q.=' OR ';}
					$q.='contacts.id='.$contacts[$i].'';
				}

				$result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
				while ($row_c = pg_fetch_assoc($result_c)) {
					echo '<contact>';
					echo '<name>'.$row_c['name'].'</name>';
					echo '<main>'.$row_c['main_flag'].'</main>';
					echo '<confirmed>'.$row_c['confirmed'].'</confirmed>';
					echo '<type_title>'.$row_c['contact_type_title'].'</type_title>';
					echo '<type_id>'.$row_c['contact_type_id'].'</type_id>';
					echo '</contact>';
				}
				pg_free_result($result_c);
				echo '</rec_contacts>';
				#контакты
			}
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function ambulance_show_requests_xml($config){
		$xls=$_GET["xls"];

		require('library/PHPExcel.php');

		$objPHPExcel = new PHPExcel();
		
		//Стиль XLS
		$font_style = ['font' => ['size' => 16]];
		$border_style = array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000')),'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000')),'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000')),'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN,'color' => array('argb' => '000'))));

		$cell_style = array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'rotation'   => 0,
			'wrap'       => TRUE
		);

		if($xls){
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(false);
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(false);
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(false);
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(false);
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(false);
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Отчёт по заявкам');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
			$objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($font_style);
			
			$objPHPExcel->getActiveSheet()->setCellValue('A2', "Номер заявки");
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);
			
			$objPHPExcel->getActiveSheet()->setCellValue('B2', "Состояние заявки");
			$objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->setCellValue('C2', "Дата/время передачи вызова бригаде");
			$objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C2')->setWidth(20);

			$objPHPExcel->getActiveSheet()->setCellValue('D2', "Дата/время планового начала приёма");
			$objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->setCellValue('E2', "Бригада/специалисты");
			$objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->setCellValue('F2', "Адрес вызова");
			$objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(50);
			$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(50);
		}

		if($xls){
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="your_name.xlsx"');
			header('Cache-Control: max-age=0');
		}else{
			header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<xml>';
		}
		
		$date_from=string_formating_for_sql($_GET["date_from"]);
		$date_to=string_formating_for_sql($_GET["date_to"]);
		$species=string_formating_for_sql($_GET["species"]);
		$brigade=string_formating_for_sql($_GET["brigade"]);
		$owner=string_formating_for_sql($_GET["owner"]);
		$nick=string_formating_for_sql($_GET["nick"]);
		$status=string_formating_for_sql($_GET["status"]);
		$specialist=string_formating_for_sql($_GET["doc"]);

		// $params=get_params();

		// $owner='';
    	// if(count($params["owner"]) > 0){
		// 	for ($i=0; $i<count($params["owner"]); $i++) {
		// 		if($i>0){
		// 			$owner.=',';
		// 		}
		// 		$area.=$params["owner"][$i];
		// 	}
		// 	$owner='\''.$owner.'\'';
		// }
		
		if(!$xls){
			echo '<recs>';
		}

		$org_user_id='';
		if($_COOKIE['organization']){
			if($_COOKIE['organization'] == 1){
				$org_user_id=666;
			}else{
				$query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$org_user_id=$row[0];
				pg_free_result($result);
			}
		}

		$query='SELECT visits.id_request, brigades.name AS brigade, brigades.id AS brigade_id, ';
		//string_agg(distinct users.fullname::character varying, \', \') AS specialists, ';
		$query.='visits.status, visits.time_range, visits.id_owner, visits.created_at, visits.visit_to_address, ';
		$query.='organizations.short_name AS org_name, addresses.name AS org_address, visits.ticket_number, pet_owners.fullname AS owner_name, ';
		$query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,';
		$query.='(UPPER(VISITS.TIME_RANGE)::time) AS end_time,';
		$query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, ';
		$query.='visits.start_dttm, ';

		$query.='start_request_date, end_request_date, ';
		$query.='fact_start_dttm, fact_end_dttm, ';
		$query.='string_agg(contacts.id::character varying, \',\') AS contacts ';
		$query.='FROM visits ';
		$query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';

		$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
		$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
		$query.='LEFT JOIN public.species ON species.id=pets.id_species ';

		$query.='LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
		$query.='LEFT JOIN organizations ON visits.id_organization=organizations.id ';
		
		$query.='LEFT JOIN brigades ON brigades.id=visits.id_brigade ';
		$query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade ';
		$query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
		$query.='LEFT JOIN users ON users.id=specialists.id_user ';

		// $query.='LEFT JOIN visits_specialists ON visits.id=visits_specialists.id_visit ';
		$query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
		$query.='WHERE visits.channel=10 ';
		$query.=' AND visits.id_organization='.$org_user_id.' ';
		
		$query.='AND (';
		$query.='(LOWER(VISITS.TIME_RANGE)::date) >= \''.$date_from.'\' AND (LOWER(VISITS.TIME_RANGE)::date) <= \''.$date_to.'\'';
		$query.=')';

		//$query.='subscription.log.log_time BETWEEN \''.$date_from.' 00:00:00\' AND \''.$date_to.' 00:00:00\'::DATE + INTERVAL \'1 DAY\' ';
		//echo $query;

		if($status){
			$query.="AND visits.status='".$status."' ";
		}

		if($brigade){
			$query.="AND visits.id_brigade='".$brigade."' ";
		}

		if($nick){
			$query.="AND pets.name ILIKE '%".$nick."%' ";
		}

		if($owner){
			$query.="AND pet_owners.fullname ILIKE '%".$owner."%' ";
		}

		if($specialist){
			$query.="AND specialists.id = '".$specialist."' ";
		}

		// if($status){
		// 	$query.="AND visits.status='".$status."' ";
		// }

		if($species){
			if($species == 'OTHER'){
				$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
			}else{
				$query.="AND public.pets.id_species='".$species."' ";
			}
		}else{
			$query.="AND public.pets.id_species IS NOT NULL ";
		}

		$query.='GROUP BY pet_owners.id, visits.id, visits.id_request, organizations.short_name, brigades.name, brigades.id, addresses.name ';
    	$query.='ORDER BY visits.time_range ';

		$n=3;

		$count=0;
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			$specialists='';
			$specialists_='';

			if($row['brigade_id']){
				$query_='SELECT users.fullname,brigades_specialists.id_specialist AS id FROM brigades_specialists ';
				$query_.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
				$query_.='LEFT JOIN users ON users.id=specialists.id_user ';
				$query_.='WHERE brigades_specialists.id_brigade='.$row['brigade_id'].'';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				while ($row_ = pg_fetch_assoc($result_)) {
					$specialists_.='<specialist>';
					$specialists_.='<id>'.$row_['id'].'</id>';
					$specialists_.='<name>'.$row_['fullname'].'</name>';
					$specialists_.='</specialist>';

					if($specialists){$specialists.=', ';}
					$specialists.=''.$row_['fullname'].'';
				}
				pg_free_result($result_);
			}

			if(!$xls){
				echo '<rec>';
				echo '<rec_id_request>'.$row['id_request'].'</rec_id_request>';
				echo '<rec_status>'.$row['status'].'</rec_status>';
				echo '<rec_brigade>'.$row['brigade'].'</rec_brigade>';
				//echo '<rec_specialists>'.$row['specialists'].'</rec_specialists>';
				echo '<rec_address>'.$row['visit_to_address'].'</rec_address>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';

				echo '<rec_start_dttm>'.$row['start_dttm'].'</rec_start_dttm>';
				
				echo '<rec_start_date>'.$row['start_date'].'</rec_start_date>';
				echo '<rec_start_time>'.$row['start_time'].'</rec_start_time>';

				echo '<rec_end_time>'.$row['end_time'].'</rec_end_time>';

				echo '<rec_start_request_date>'.$row['start_request_date'].'</rec_start_request_date>';
				echo '<rec_end_request_date>'.$row['end_request_date'].'</rec_end_request_date>';
				echo '<rec_fact_start_dttm>'.$row['fact_start_dttm'].'</rec_fact_start_dttm>';
				echo '<rec_fact_end_dttm>'.$row['fact_end_dttm'].'</rec_fact_end_dttm>';

				echo '<rec_specialists>'.$specialists_.'</rec_specialists>';

				//
				$contacts = ($row['contacts'] != '')?explode(",",$row['contacts']):NULL;
				if(count($contacts) > 0){
					#контакты
					echo '<rec_contacts>';
					$q='SELECT contacts.name,contacts.main_flag,contacts.confirmed,contact_types.name AS contact_type_title, contacts.id_contact_type AS contact_type_id FROM contacts ';
					$q.='LEFT JOIN contact_types ON contacts.id_contact_type = contact_types.id ';
					$q.='WHERE ';

					for($i=0; $i<count($contacts);$i++){
						if($i>0){$q.=' OR ';}
						$q.='contacts.id='.$contacts[$i].'';
					}

					$result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_c = pg_fetch_assoc($result_c)) {
						echo '<contact>';
						echo '<name>'.$row_c['name'].'</name>';
						echo '<main>'.$row_c['main_flag'].'</main>';
						echo '<confirmed>'.$row_c['confirmed'].'</confirmed>';
						echo '<type_title>'.$row_c['contact_type_title'].'</type_title>';
						echo '<type_id>'.$row_c['contact_type_id'].'</type_id>';
						echo '</contact>';
					}
					pg_free_result($result_c);
					echo '</rec_contacts>';
					#контакты
				}
				//

				echo '</rec>';
			}

			if($xls){
				$count++;
				$status='';
				if($row['status'] == 'N'){$status='Новая';}
				if($row['status'] == 'W'){$status='В работе';}
				if($row['status'] == 'A'){$status='Отменена';}
				if($row['status'] == 'F'){$status='Завершена';}

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$n, $row['id_request'])
				->setCellValue('B'.$n, $status)
				->setCellValue('C'.$n, $row['created_at'])
				->setCellValue('D'.$n, $row['start_date']." ".$row['start_time'])
				->setCellValue('E'.$n, $row['brigade']." (".$specialists.")")
				->setCellValue('F'.$n, $row['visit_to_address']);

				$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("D".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("D".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("E".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("E".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("F".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("F".$n)->getAlignment()->applyFromArray($cell_style);
			}
			$n++;
		}
		pg_free_result($result);

		if(!$xls){
			echo '</recs>';
			echo '</xml>';
		}

		if($xls){
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$n, "Итого:")
				->setCellValue('B'.$n, $count);

			$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

			$objWriter->save('php://output');
		}
	}

	function ambulance_show_brigades_xml($config){
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		echo '<recs>';
		
		$query='SELECT brigades.*, ';
		$query.='string_agg(users.fullname::character varying, \', \') AS specialists ';
		$query.='FROM brigades ';
		$query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade ';
		$query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
		$query.='LEFT JOIN users ON users.id=specialists.id_user ';
		//$query.='WHERE visits.status=\'N\' AND visits.channel=3 ';
		
		$query.='GROUP BY brigades.id ';
		$query.='ORDER BY brigades.id DESC';

		//$query.='subscription.log.log_time BETWEEN \''.$date_from.' 00:00:00\' AND \''.$date_to.' 00:00:00\'::DATE + INTERVAL \'1 DAY\' ';
		//echo $query;
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_specialists>'.$row['specialists'].'</rec_specialists>';

			echo '<rec_schedule>';
			$query_='SELECT ';
			$query_.='(LOWER(date)::date) AS start_date, ';
			$query_.='(LOWER(date)::time) AS start_time,';
			$query_.='(UPPER(date)::time) AS end_time ';
			$query_.='FROM brigades_timesheets ';
        	$query_.='WHERE brigades_timesheets.id_brigade='.$row['id'].'';
        	$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
	        while ($row_ = pg_fetch_assoc($result_)) {
    	        echo '<slot>';
				echo '<time_from>'.$row_['start_time'].'</time_from>';
				echo '<time_to>'.$row_['end_time'].'</time_to>';
				echo '<date>'.$row_['start_date'].'</date>';
				echo '</slot>';
        	}
        	pg_free_result($result_);
			echo '</rec_schedule>';

			echo '<rec_car_model>'.$row['car_model'].'</rec_car_model>';
			echo '<rec_car_driver>'.$row['car_driver'].'</rec_car_driver>';
			echo '<rec_car_number>'.$row['car_number'].'</rec_car_number>';
			echo '<rec_pass_number>'.$row['pass_number'].'</rec_pass_number>';

			echo '</rec>';
		
		}
		pg_free_result($result);
		
		echo '</recs>';
		echo '</xml>';
	}

	function ambulance_show_brigades_map_xml($config){
		$coordinates=$_GET["coordinates"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		//выводим текущие бригады
		//

		echo '<recs>';

		$query='SELECT brigades.id AS brigade_id, visits.id AS visit_id, visits.visit_to_address, visits.status, brigades.name AS brigade, brigades.car_model AS car_model, ';

		if($coordinates){
			$coordinatesArray = explode(",", $coordinates);
			$query.='ST_DistanceSphere(brigades.coordinates::geometry, ST_GeomFromText(\'POINT('.$coordinatesArray[1].' '.$coordinatesArray[0].')\')) AS distance, ';
		}

		$query.='brigades.car_driver AS car_driver, brigades.car_number AS car_number, brigades.pass_number AS pass_number, ';
		$query.='string_agg(users.fullname::character varying, \', \') AS specialists, ';
		$query.='ST_X(brigades.coordinates::geometry) AS lon, ST_Y(brigades.coordinates::geometry) AS lat, ';
		$query.='visits.status, visits.time_range, visits.id_owner, visits.created_at, visits.visit_to_address, ';
		$query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,(UPPER(VISITS.TIME_RANGE)::time) AS end_time, ';
		$query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, start_request_date, end_request_date, fact_start_dttm, fact_end_dttm ';
		$query.='FROM visits ';

		$query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
		$query.='LEFT JOIN brigades ON brigades.id=visits.id_brigade ';
		$query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade ';
		$query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
		$query.='LEFT JOIN users ON users.id=specialists.id_user ';

		$query.='WHERE visits.channel=10 ';
		
		$query.='AND ((LOWER(VISITS.TIME_RANGE)::date) + (LOWER(VISITS.TIME_RANGE)::time)) >= NOW()::date ';

		$query.="AND (visits.status='P' OR visits.status='W') ";
		//$query.="AND (brigades.id IN (".$brigades.")) ";
		$query.='GROUP BY pet_owners.id, visits.id, visits.id_request, brigades.name, brigades.id ';
		if($coordinates){
			$query.='ORDER BY ST_DistanceSphere(brigades.coordinates::geometry, ST_GeomFromText(\'POINT('.$coordinatesArray[1].' '.$coordinatesArray[0].')\')) ';
		}else{
			$query.='ORDER BY visits.time_range ';
		}

		//echo $query;

		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$brigades_='';
		$brigades_array = array();

		while ($row = pg_fetch_assoc($result)) {
			if (!in_array($row['brigade_id'], $brigades_array) || $coordinates == '') {
				echo '<rec>';
				echo '<rec_id>'.$row['brigade_id'].'</rec_id>';
				echo '<rec_visit_id>'.$row['visit_id'].'</rec_visit_id>';
				echo '<rec_name>'.$row['brigade'].'</rec_name>';
				echo '<rec_specialists>'.$row['specialists'].'</rec_specialists>';
				echo '<rec_car_model>'.$row['car_model'].'</rec_car_model>';
				echo '<rec_car_driver>'.$row['car_driver'].'</rec_car_driver>';
				echo '<rec_car_number>'.$row['car_number'].'</rec_car_number>';
				echo '<rec_pass_number>'.$row['pass_number'].'</rec_pass_number>';
				echo '<rec_lat>'.$row['lat'].'</rec_lat>';
				echo '<rec_lon>'.$row['lon'].'</rec_lon>';

				if($row['distance'] != 0){
					echo '<rec_distance>'.sprintf("%01.2f", $row['distance']/1000).'</rec_distance>';
				}else{
					echo '<rec_distance>'.sprintf("%01.2f", 0).'</rec_distance>';
				}

				echo '<rec_status>'.$row['status'].'</rec_status>';
				if($row['status'] == 'P'){
					echo '<rec_address>'.$row['visit_to_address'].'</rec_address>';//адрес выезда
				}

				echo '<rec_schedule>';
				$query_='SELECT ';
				$query_.='(LOWER(date)::date) AS start_date, ';
				$query_.='(LOWER(date)::time) AS start_time,';
				$query_.='(UPPER(date)::time) AS end_time ';
				$query_.='FROM brigades_timesheets ';
				$query_.='WHERE brigades_timesheets.id_brigade='.$row['brigade_id'].' AND ((LOWER(brigades_timesheets.date)::date) + (LOWER(brigades_timesheets.date)::time)) >= NOW()::date LIMIT 1';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				while ($row_ = pg_fetch_assoc($result_)) {
					
					echo ''.$row_['start_date'].' с '.$row_['start_time'].' по '.$row_['end_time'];
				}
				pg_free_result($result_);
				echo '</rec_schedule>';

				echo '</rec>';

				array_push($brigades_array, $row['brigade_id']);
			}
		}
		pg_free_result($result);
		
		// $query='SELECT brigades.*, ST_X(coordinates::geometry) AS lon, ST_Y(coordinates::geometry) AS lat, ';
		// $query.='string_agg(users.fullname::character varying, \', \') AS specialists ';
		// $query.='FROM brigades ';
		// $query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade ';
		// $query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
		// $query.='LEFT JOIN users ON users.id=specialists.id_user ';
		// $query.='WHERE brigades.coordinates IS NOT NULL ';
		
		// $query.='GROUP BY brigades.id ';
		// $query.='ORDER BY brigades.id DESC';
		
		// $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		// while ($row = pg_fetch_assoc($result)) {
		// 	$query_='SELECT visit_to_address FROM visits ';
		// 	$query_.='WHERE id_brigade='.$row['id'].' AND (status=\'P\' OR status=\'W\') ';
		// 	$query.='AND ((LOWER(VISITS.TIME_RANGE)::date) + (LOWER(VISITS.TIME_RANGE)::time)) >= NOW() LIMIT 1';
		// 	$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
		// 	$address=pg_fetch_result($result_, 0);
		// 	pg_free_result($result_);

		// 	if($row['lat'] && $row['lon']){
		// 		echo '<rec>';
		// 		echo '<rec_id>'.$row['id'].'</rec_id>';
		// 		echo '<rec_name>'.$row['name'].'</rec_name>';
		// 		echo '<rec_specialists>'.$row['specialists'].'</rec_specialists>';
		// 		echo '<rec_car_model>'.$row['car_model'].'</rec_car_model>';
		// 		echo '<rec_car_driver>'.$row['car_driver'].'</rec_car_driver>';
		// 		echo '<rec_car_number>'.$row['car_number'].'</rec_car_number>';
		// 		echo '<rec_pass_number>'.$row['pass_number'].'</rec_pass_number>';
		// 		echo '<rec_lat>'.$row['lat'].'</rec_lat>';
		// 		echo '<rec_lon>'.$row['lon'].'</rec_lon>';
		// 		echo '<rec_address>'.$address.'</rec_address>';//адрес выезда
		// 		echo '</rec>';
		// 	}
		// }
		// pg_free_result($result);
		
		echo '</recs>';
		echo '</xml>';
	}

	function ambulance_add_brigade($configuration){
		$name=$_POST["name"];
		$specialists=$_POST["specialists"];
		$timesheets=$_POST["timesheets"];

		$car_driver=$_POST["car_driver"];
		$car_model=$_POST["car_model"];
		$car_number=$_POST["car_number"];
		$pass_number=$_POST["pass_number"];
		
		$id_user=user_id($configuration);
		
		if($name && $specialists && $timesheets){
			//добавляем бригаду

			$query='INSERT INTO brigades ';
			$query.='(name, car_driver, car_model, car_number, pass_number, created_at, created_by, updated_at, updated_by) ';
			$query.='VALUES (';
			$query.='\''.string_formating_for_sql($name).'\',';
			$query.='\''.string_formating_for_sql($car_driver).'\',';
			$query.='\''.string_formating_for_sql($car_model).'\',';
			$query.='\''.string_formating_for_sql($car_number).'\',';
			$query.='\''.string_formating_for_sql($pass_number).'\',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user.",";#created_by
			$query.="NOW()::timestamp(0),";#updated_at
			$query.="".$id_user."";#created_by
			$query.=') RETURNING id;';

			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id_brigade=$row[0];
			pg_free_result($result);
			// $id_brigade=777;

			//AMBULANCE-shift_type-10

			//
			$organization=string_formating_for_sql($_COOKIE['organization']);
			if($organization == 1){$organization=666;}

			//добавляем связи с врачами//brigades_specialists//[ 3426 ],[ 3525 ]
			$specialistsArray = explode(",", $specialists);
			$timesheetsArray = explode(";", $timesheets);

			for($i=0; $i<=count($specialistsArray);$i++){
				if($specialistsArray[$i] != ''){
					$id_spec = str_replace("[ ", "", $specialistsArray[$i]);
					$id_spec = str_replace(" ]", "", $id_spec);

					$query='INSERT INTO brigades_specialists ';
					$query.='(id_brigade, id_specialist, created_at, created_by) ';
					$query.='VALUES (';
					$query.="".$id_brigade.",";
					$query.="".$id_spec.",";
					$query.="NOW()::timestamp(0),";#created_at
					$query.="".$id_user."";#created_by
					$query.=');';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//добавляем расписание каждому врачу
					//shifts НВП с 10:00 до 12:00
					//shifts from_time 00:10:00
					//shifts duration в минутах
					//shifts id_organization
					//shifts id_type = 10

					for($j=0; $j<=count($timesheetsArray);$j++){
						if($timesheetsArray[$j]  != ''){
							
							//проверка на существование shifts
							$from=substr($timesheetsArray[$j], 13, 5);
							$to=substr($timesheetsArray[$j], 35, 5);

							//ПОИСК ДНЕВНОЙ СМЕНЫ
							$query='SELECT id FROM shifts WHERE from_time=\'00:00:00\' AND duration=\'1440\' AND id_organization='.$organization.' AND id_type=1 LIMIT 1';
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$id_shift_parent=pg_fetch_result($result, 0);
							pg_free_result($result);

							if($id_shift_parent){
								//ПОИСК СМЕНЫ НВП
								$query='SELECT id FROM shifts WHERE name=\'НВП с '.$from.' до '.$to.'\' AND id_organization='.$organization.' AND id_type=10 LIMIT 1';
								$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								$id_shift=pg_fetch_result($result, 0);
								pg_free_result($result);

								$start_date='2000-01-01 '.$from.':00';
								$end_date='2000-01-01 '.$to.':00';
								$period = new \DatePeriod(new \DateTime($start_date),new \DateInterval('PT10M'),new \DateTime($end_date));
								$period_=0;
								foreach ($period as $slot) {
									$period_++;
								}
								//ПОИСК СМЕНЫ НВП

								if($id_shift == ''){
									//создаём новое
									$query='INSERT INTO shifts ';
									$query.='(name, from_time, duration, id_type, id_organization, created_at, created_by, updated_at, updated_by) ';
									$query.='VALUES (';
									$query.="'НВП с ".$from." до ".$to."',";
									$query.="'".$from.":00',";
									$query.="".($period_*10).",";
									$query.="10,";
									$query.="".$organization.",";
									$query.="NOW()::timestamp(0),";#created_at
									$query.="".$id_user.",";#created_by
									$query.="NOW()::timestamp(0),";#updated_at
									$query.="".$id_user."";#created_by
									$query.=') RETURNING id;';
									
									$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
									$row = pg_fetch_row($result);
									$id_shift=$row[0];
									pg_free_result($result);
								}

								//ЗАВОДИМ РАБОЧИЙ ДЕНЬ
								$query='INSERT INTO timesheets ';
								$query.='(id_specialist, id_shift, date, created_at, created_by, updated_at, updated_by) ';
								$query.='VALUES (';
								$query.="".$id_spec.",";
								$query.="".$id_shift_parent.",";
								$query.="'".$timesheetsArray[$j]."',";
								$query.="NOW()::timestamp(0),";#created_at
								$query.="".$id_user.",";#created_by
								$query.="NOW()::timestamp(0),";#updated_at
								$query.="".$id_user."";#created_by
								$query.=') RETURNING id;';
								
								$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								$row = pg_fetch_row($result);
								$id_parent_timesheet=$row[0];
								pg_free_result($result);

								//ЗАВОДИМ НВП
								$query='INSERT INTO timesheets ';
								$query.='(id_specialist, parent_id, id_shift, date, created_at, created_by, updated_at, updated_by) ';
								$query.='VALUES (';
								$query.="".$id_spec.",";
								$query.="".$id_parent_timesheet.",";
								$query.="".$id_shift.",";
								$query.="'".$timesheetsArray[$j]."',";
								$query.="NOW()::timestamp(0),";#created_at
								$query.="".$id_user.",";#created_by
								$query.="NOW()::timestamp(0),";#updated_at
								$query.="".$id_user."";#created_by
								$query.=') RETURNING id;';
								
								$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								$row = pg_fetch_row($result);
								pg_free_result($result);
							}
						}
					}
				}
			}

			//добавляем расписание бригады//brigades_timesheets
			for($i=0; $i<=count($timesheetsArray);$i++){
				if($timesheetsArray[$i] != ''){
					$query='INSERT INTO brigades_timesheets ';
					$query.='(id_brigade, date, created_at, created_by) ';
					$query.='VALUES (';
					$query.="".$id_brigade.",";
					$query.="'".$timesheetsArray[$i]."',";
					$query.="NOW()::timestamp(0),";#created_at
					$query.="".$id_user."";#created_by
					$query.=');';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);
				}
			}
					
			echo xml('<message>brigade_added</message>');
		}else{
			echo xml('<message>empty_fields</message>');
		}
	}

	function ambulance_delete_brigade($configuration){
		$id=$_GET["id"];

		if($id){
			$query='SELECT id FROM visits WHERE id_brigade=\''.string_formating_for_sql($id).'\' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $visit=$row[0];
            pg_free_result($result);

			if($visit){
				echo xml('<message>brigade_deleted_error</message>');
			}else{

				$query_delete='DELETE FROM public.brigades_timesheets WHERE id_brigade='.$id.'';
				$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result);

				$query_delete='DELETE FROM public.brigades_specialists WHERE id_brigade='.$id.'';
				$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result);

				$query_delete='DELETE FROM public.brigades WHERE id='.$id.'';
				$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result);

				echo xml('<message>brigade_deleted</message>');
			}
		}else{
			echo xml('<message>empty_fields</message>');
		}
	}

	function ambulance_make_request($configuration){
		$id_owner=$_GET["owner"];
		$id_pets=$_GET["pets"];
		$date=$_GET["date"];
		$time=$_GET["time"];
		$request_address=$_GET["request_address"];
		$fias=$_GET["fias"];

		$brigade=$_GET["brigade"];
		$call_reason=$_GET["call_reason"];
		$description=$_GET["description"];

		$is_veteran=$_GET["is_veteran"];
		$is_disabled=$_GET["is_disabled"];
		$is_blind=$_GET["is_blind"];
		$is_orphan=$_GET["is_orphan"];
		$is_large_family=$_GET["is_large_family"];
		$is_veteran_of_labour=$_GET["is_veteran_of_labour"];
			
		//свой id
		$id_user = user_id($configuration);
		$org_id = user_org_id($configuration);
		
		if($id_user){
			$flag_error=0;
			if($id_owner == '' && $id_pets == ''){#владельца нет, животного нет
				$owner_surname=$_GET["owner_surname"];
				$owner_name=$_GET["owner_name"];
				$owner_secondname=$_GET["owner_secondname"];
				$owner_telephone=$_GET["owner_telephone"];
				$owner_address=$_GET["owner_address"];

				$pet_species=$_GET["pet_species"];#ВИД
				$pet_breeds=$_GET["pet_breeds"];#ВИД
				$pet_name=$_GET["pet_name"];#КЛИЧКА
				$pet_sex=$_GET["pet_sex"];#ПОЛ
		
				$add_new=$_GET["add_new"];
				
				if(
					$owner_surname != '' && 
					$owner_name != '' && 
					$owner_address != '' && 
					($owner_telephone != '' && 
					$owner_telephone != '+7') && 
					$pet_name != '' && 
					$add_new == 1){
					//поиск fias по текцщим данным
					$id_fias = str_replace("[ ", "", $owner_address);
					$id_fias = str_replace(" ]", "", $id_fias);
					$fias_tokken=fias_request($configuration);
					$fias_address_=fias_address($fias_tokken->access_token, $id_fias,$configuration);

					$fias_address=$fias_address_->suggestions[0];

					$query='SELECT id FROM fias_addresses WHERE ';
					if($fias_address->data->house_fias_id){
						$query.='houseguid=\''.string_formating_for_sql($fias_address->data->house_fias_id).'\' AND ';
					}else{
						$query.='houseguid IS NULL AND ';
					}
					$query.='cityguid=\''.string_formating_for_sql($fias_address->data->region_fias_id).'\' AND ';
					if($fias_address->data->street_fias_id){
						$query.='streetguid=\''.string_formating_for_sql($fias_address->data->street_fias_id).'\' AND ';	
					}else{
						$query.='streetguid IS NULL AND ';
					}
					$query.='roomguid IS NULL ';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$id_fias_from_table=pg_fetch_result($result, 0);
					pg_free_result($result);

					if(!$id_fias_from_table){//fias адрес не найден в таблице
						// "ш"
						// "ул"
						$street_prefix="";
						if($fias_address->data->street_type == 'ш'){
							$street_prefix='шоссе';
						}else{
							$street_prefix='улица';
						}

						$full_address='город '.$fias_address->data->region.', '.$street_prefix.' '.$fias_address->data->street.', дом '.$fias_address->data->house.'';

						$lon=$fias_address->data->polygon->coordinates[0][0][0][0];
						$lat=$fias_address->data->polygon->coordinates[0][0][0][1];

						//$fias_address->data->adm_area
						//сравнение адреса
						//Северный административный округ
						//в БД
						//Северный Административный Округ

						$query='INSERT INTO fias_addresses ';
						$query.='(full_address, lon, lat, region, city, street, house, cityguid, streetguid, houseguid, roomguid, created_by, updated_by, created_at, updated_at) ';
						$query.='VALUES (';
						$query.="'".$full_address."',";

						if($lon){$query.="'".$lon."',";}else{$query.="NULL,";}
						if($lat){$query.="'".$lat."',";}else{$query.="NULL,";}

						$query.="'город ".$fias_address->data->region."',";
						$query.="'город ".$fias_address->data->region."',";

						if($fias_address->data->street){
							$query.="'".$street_prefix." ".$fias_address->data->street."',";
						}else{
							$query.="NULL,";
						}
						if($fias_address->data->house){
							$query.="'дом ".$fias_address->data->house."',";
						}else{
							$query.="NULL,";
						}
						$query.="'".$fias_address->data->region_fias_id."',";
						if($fias_address->data->street_fias_id){
							$query.="'".$fias_address->data->street_fias_id."',";
						}else{
							$query.="NULL,";
						}
						if($fias_address->data->house_fias_id){
							$query.="'".$fias_address->data->house_fias_id."',";
						}else{
							$query.="NULL,";	
						}
						$query.="NULL,";

						$query.="".$id_user.",";#created_by
						$query.="".$id_user.",";#updated_by
						$query.="NOW()::timestamp(0),";#created_at
						$query.="NOW()::timestamp(0)";#updated_at
						$query.=') RETURNING id;';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$row = pg_fetch_row($result);
						$id_fias_from_table=$row[0];
						pg_free_result($result);
					}

					#СОЗДАЁМ ВЛАДЕЛЬЦА
					$query='INSERT INTO pet_owners ';
					$query.='(f_fio, i_fio, o_fio, fullname, id_fias_address, id_fact_fias_address, created_by, updated_by, created_at, updated_at, is_main) ';
					$query.='VALUES (';
					$query.="'".$owner_surname."',";
					$query.="'".$owner_name."',";
					$query.="'".$owner_secondname."',";
					if($owner_secondname != ''){
						$query.="'".$owner_surname." ".$owner_name." ".$owner_secondname."',";
					}else{
						$query.="'".$owner_surname." ".$owner_name."',";
					}
					if($id_fias_from_table){
						$query.="".$id_fias_from_table.",";#адрес
					}else{
						$query.="NULL,";
					}
					if($id_fias_from_table){
						$query.="".$id_fias_from_table.",";#адрес
					}else{
						$query.="NULL,";
					}
					$query.="".$id_user.",";#created_by
					$query.="".$id_user.",";#updated_by
					$query.="NOW()::timestamp(0),";#created_at
					$query.="NOW()::timestamp(0),";#updated_at
					$query.="true";
					$query.=') RETURNING id;';
					
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					$id_owner=$row[0];
					pg_free_result($result);
	
					#КОНТАКТЫ
					$query='INSERT INTO contacts ';
					$query.='(id_contact_type, entity_type, entity_id, name, created_by, updated_by, created_at, updated_at, main_flag, confirmed) ';
					$query.='VALUES (';
					$query.="1,";
					$query.="'pet_owner',";
					$query.="".$id_owner.",";
					$query.="'+".$owner_telephone."',";
					$query.="".$id_user.",";#created_by
					$query.="".$id_user.",";#updated_by
					$query.="NOW()::timestamp(0),";#created_at
					$query.="NOW()::timestamp(0),";#updated_at
					$query.="true,";
					$query.="true";
					$query.=');';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result);
				}else{
					$flag_error=1;
					echo xml('<messager>1</messager><message>empty_fields</message>');
				}
			}
			
			if($id_owner && $id_pets == ''){#владелец есть, животного нет
				$pet_species=$_GET["pet_species"];#ВИД
				$pet_breeds=$_GET["pet_breeds"];#ПОРОДА
				$pet_name=$_GET["pet_name"];#КЛИЧКА
				$pet_sex=$_GET["pet_sex"];#ПОЛ
	
				#СОЗДАЁМ ЖИВОТНОЕ
				$query='INSERT INTO pets ';
				$query.='(name, id_species, id_breed, sex, id_created_organization, created_by, updated_by, created_at, updated_at) ';
				$query.='VALUES (';
				$query.="'".$pet_name."',";#Кличка
				$query.="".$pet_species.",";#Вид
				if($pet_breeds){
					$query.="".$pet_breeds.",";#Порода
				}else{
					$query.="NULL,";#Порода
				}
				if($pet_sex){
					$query.="'".$pet_sex."',";#Пол
				}else{
					$query.="'m',";#Пол
				}
				$query.="".$org_id.",";#
				$query.="".$id_user.",";#created_by
				$query.="".$id_user.",";#updated_by
				$query.="NOW()::timestamp(0),";#created_at
				$query.="NOW()::timestamp(0)";#updated_at
				$query.=') RETURNING id;';
				//echo $query;

				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$id_pet=$row[0];
				pg_free_result($result);
	
				#СОЕДИНЯЕМ ЖИВОТНОЕ И ВЛАДЕЛЬЦА
				$query='INSERT INTO pets_to_owner ';
				$query.='(id_pet, id_owner, id_owner_type) ';
				$query.='VALUES (';
				$query.="".$id_pet.",";
				$query.="".$id_owner.",";
				$query.="1";#ВЛАДЕЛЕЦ/ПРЕДСТАВИТЕЛЬ
				$query.=');';
				
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result);

				$id_pets='{"id":"'.$id_pet.'"}';
			}
			
			$pets_array = json_decode(urldecode("[".$id_pets."]"), true);

			if($id_owner && $id_pets && $date && $time){
				//id заявки
				$id_request=0;
				$query='SELECT MAX(id_request) FROM public.visits WHERE channel=10';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $id_request=pg_fetch_result($result, 0);
                $id_request=$id_request+1;
                pg_free_result($result);

				if(!$id_request){
					$query='SELECT COUNT(*) FROM public.visits WHERE channel=10';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$id_request=pg_fetch_result($result, 0);
					$id_request=$id_request+1;
					pg_free_result($result);
				}

				if($fias == 1){
					$id_fias = str_replace("[ ", "", $request_address);
					$id_fias = str_replace(" ]", "", $id_fias);
					
					$fias_tokken=fias_request($configuration);
					$fias_address_=fias_address($fias_tokken->access_token, $id_fias, $configuration);
					$fias_address=$fias_address_->suggestions[0];

					$street_prefix="";
					if($fias_address->data->street_type == 'ш'){
						$street_prefix='шоссе';
					}else{
						$street_prefix='улица';
					}

					$request_address='город '.$fias_address->data->region.', '.$street_prefix.' '.$fias_address->data->street.', дом '.$fias_address->data->house.'';

					$query='SELECT id FROM fias_addresses WHERE ';
					$query.='full_address=\''.$request_address.'\' ';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$id_fias_from_table_=pg_fetch_result($result, 0);
					pg_free_result($result);

					if(!$id_fias_from_table_){//нет адреса в таблице
						$lon=$fias_address->data->polygon->coordinates[0][0][0][0];
						$lat=$fias_address->data->polygon->coordinates[0][0][0][1];

						$query='INSERT INTO fias_addresses ';
						$query.='(full_address, lon, lat, region, city, street, house, cityguid, streetguid, houseguid, roomguid, created_by, updated_by, created_at, updated_at) ';
						$query.='VALUES (';
						$query.="'".$request_address."',";

						if($lon){$query.="'".$lon."',";}else{$query.="NULL,";}
						if($lat){$query.="'".$lat."',";}else{$query.="NULL,";}
						
						$query.="'город ".$fias_address->data->region."',";
						$query.="'город ".$fias_address->data->region."',";
						$query.="'".$street_prefix." ".$fias_address->data->street."',";
						$query.="'дом ".$fias_address->data->house."',";

						$query.="'".$fias_address->data->region_fias_id."',";
						$query.="'".$fias_address->data->street_fias_id."',";
						$query.="'".$fias_address->data->house_fias_id."',";
						$query.="NULL,";

						$query.="".$id_user.",";#created_by
						$query.="".$id_user.",";#updated_by
						$query.="NOW()::timestamp(0),";#created_at
						$query.="NOW()::timestamp(0)";#updated_at
						$query.=') RETURNING id;';

						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$row = pg_fetch_row($result);
						pg_free_result($result);
					}
				}

				//id услуги с кодом 0364
				$query='SELECT id, price, cooldown, duration FROM gov_services ';
				$query.='WHERE deleted=false AND cod=\'0364\' LIMIT 1';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$service_id=$row[0];
				$service_price=$row[1];
				$service_cooldown=$row[2];
				$service_duration=$row[3];
				pg_free_result($result);

				$services_fulltime=$service_duration;

				//номер заявки
                $day_visit=date("d",strtotime($date));
                if(intval($day_visit) < 10){
                    $day_visit='0'.intval($day_visit);
                }
                $query='SELECT MAX(number) FROM visits WHERE id_organization=\''.$org_id.'\' AND start_dttm BETWEEN \''.$date.' 00:00:00\' AND \''.$date.' 00:00:00\'::DATE + INTERVAL \'1 DAY\'';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $counter=pg_fetch_result($result, 0);
                $counter=$counter+1;
                pg_free_result($result);
                $ticket_number='';
                if($counter < 10){
                    $ticket_number=$day_visit.'300'.$counter;
                }elseif($counter >= 10 && $counter < 100){
                    $ticket_number=$day_visit.'30'.$counter;
                }
			   
				#СОЗДАЁМ ЗАЯВКУ
				$query='INSERT INTO visits ';
				$query.='(status, id_request, id_owner, id_pet, id_brigade, description, call_reason, visit_to_address, id_organization, ';
				$query.='is_veteran, is_disabled, is_blind, is_orphan, is_large_family, is_veteran_of_labour, ';
				$query.='created_by, updated_by, created_at, updated_at, cooldown, number, time_range, channel, duration, ticket_number, start_dttm, time_range_without_cooldown, type, variety) ';
				$query.='VALUES (';
				$query.="'N',";#status
				$query.="".$id_request.",";#id_request
				$query.="".$id_owner.",";#Владелец
				if(count($pets_array) == 1){
					$query.="".$pets_array[0]['id'].",";#Животное
				}else{
					$query.="NULL,";#Животное
				}
				$query.="".$brigade.",";#Бригада
				if($description){
					$query.="'".$description."',";#Описание
				}else{
					$query.="NULL,";#Описание
				}
				if($call_reason){
					$query.="'".$call_reason."',";#Причина вызова
				}else{
					$query.="NULL,";#Причина вызова
				}
				if($request_address){
					$query.="'".$request_address."',";#visit_to_address
				}else{
					$query.="NULL,";#visit_to_address
				}
				$query.="".$org_id.",";#Организация

				if($is_veteran){$query.="true";}else{$query.="false";}$query.=",";
				if($is_disabled){$query.="true";}else{$query.="false";}$query.=",";
				if($is_blind){$query.="true";}else{$query.="false";}$query.=",";
				if($is_orphan){$query.="true";}else{$query.="false";}$query.=",";
				if($is_large_family){$query.="true";}else{$query.="false";}$query.=",";
				if($is_veteran_of_labour){$query.="true";}else{$query.="false";}$query.=",";
				
				$query.="".$id_user.",";#created_by
				$query.="".$id_user.",";#updated_by
				$query.="NOW()::timestamp(0),";#created_at
				$query.="NOW()::timestamp(0),";#updated_at
				$query.="".$service_cooldown.",";#cooldown
				$query.="".$counter.",";#number - по порядку по суткам
				$query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_fulltime.' MINUTES\'), \'[)\'),';#time_range["2021-12-02 14:00:00","2021-12-02 14:20:00")
				$query.="10,";#channel - по умолчанию 10 - НВП
				$query.="".$service_duration.",";#duration
				$query.="'".$ticket_number."',";#ticket_number - 023001 - [02]дата[3]-канал[001]-номер
				$query.="'".$date." ".$time.":00',";#start_dttm 2021-12-02 14:00:00
				$query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_fulltime.' MINUTES\'), \'[)\'),';#time_range_without_cooldown["2021-12-02 14:00:00","2021-12-02 14:20:00")
				$query.="'AMBULANCE',";#type
				
				if(count($pets_array) > 1){
					$query.="'MULTIPLE'";#variety
				}else{
					$query.="'SINGLE'";#variety
				}

				$query.=') RETURNING id;';
								
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$id_visit=$row[0];
				pg_free_result($result);
	
				// if($configuration['version'] == '1.0'){
				// 	visitlogUser($id_visit, 'N');
				// }
		
				#ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
				for ($k=0; $k<count($pets_array); $k++) {
					$id_pet=0;
					if($pets_array[$k]['id'] == '000'){
						$org_user_id='';
						if($_COOKIE['organization']){
							if($_COOKIE['organization'] == 1){
								$org_user_id=666;
							}else{
								$query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
								$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								$row = pg_fetch_row($result);
								$org_user_id=$row[0];
								pg_free_result($result);
							}
						}

						#СОЗДАЁМ ЖИВОТНОЕ
						$query='INSERT INTO pets ';
						$query.='(name, id_species, id_breed, sex, id_created_organization, created_by, updated_by, created_at, updated_at) ';
						$query.='VALUES (';
						$query.="'".$pets_array[$k]['name']."',";#Кличка
						$query.="".$pets_array[$k]['species'].",";#Вид
						$query.="NULL,";#Порода
						$query.="'".$pets_array[$k]['sex']."',";#Пол
						$query.="".$org_user_id.",";#
						$query.="".$id_user.",";#created_by
						$query.="".$id_user.",";#updated_by
						$query.="NOW()::timestamp(0),";#created_at
						$query.="NOW()::timestamp(0)";#updated_at
						$query.=') RETURNING id;';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$row = pg_fetch_row($result);
						$id_pet=$row[0];
						pg_free_result($result);

						#СОЕДИНЯЕМ ЖИВОТНОЕ И ВЛАДЕЛЬЦА
						$query='INSERT INTO pets_to_owner ';
						$query.='(id_pet, id_owner, id_owner_type) ';
						$query.='VALUES (';
						$query.="".$id_pet.",";
						$query.="".$id_owner.",";
						$query.="1";#ВЛАДЕЛЕЦ/ПРЕДСТАВИТЕЛЬ
						$query.=');';
						
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result);
					}
					$query='INSERT INTO visit_pets ';
					$query.='(id_pet, id_visit, created_by, updated_by, created_at, updated_at) ';
					$query.='VALUES (';
					if($id_pet){
						$query.="".$id_pet.",";
					}else{
						$query.="".$pets_array[$k]['id'].",";
					}
					$query.="".$id_visit.",";
					$query.="".$id_user.",";#created_by
					$query.="".$id_user.",";#updated_by
					$query.="NOW()::timestamp(0),";#created_at
					$query.="NOW()::timestamp(0)";#updated_at
					$query.=');';
				
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result);

			 		$query='INSERT INTO visits_gov_services ';
					$query.='(id_visit, id_service, id_pet, count, created_by, updated_by, created_at, updated_at) ';
					$query.='VALUES (';
					$query.="".$id_visit.",";#визит
					$query.="".$service_id.",";#услуга
					if($id_pet){
						$query.="".$id_pet.",";
					}else{
						$query.="".$pets_array[$k]['id'].",";#животное
					}
					$query.="'1',";#животное
					$query.="".$id_user.",";#created_by
					$query.="".$id_user.",";#updated_by
					$query.="NOW()::timestamp(0),";#created_at
					$query.="NOW()::timestamp(0)";#updated_at
					$query.=') RETURNING id;';
					
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					$id_gov_visit=$row[0];
					pg_free_result($result);

					#ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
					$query='INSERT INTO visit_service_pet ';
					$query.='(id_visits_gov_service, id_pet) ';
					$query.='VALUES (';
					$query.="".$id_gov_visit.",";
					if($id_pet){
						$query.="".$id_pet."";
					}else{
						$query.="".$pets_array[$k]['id']."";
					}
					$query.=');';
					
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result);
				}
	
				$message='<message>request_created</message>';
				$message.='<ticket_number>'.$ticket_number.'</ticket_number>';
				$message.='<date>'.$date.'</date>';
				$message.='<time>'.$time.'</time>';
				$message.='<duration>'.$services_fulltime.'</duration>';
				// $message.='<services>'.$services_name.'</services>';
				// $message.='<specialist>'.$specialist_name.'</specialist>';
				// $message.='<organization>'.$org_name.'</organization>';
				// $message.='<address>'.$org_address.'</address>';

				echo xml($message);
			}else{
				if(!$flag_error){
					echo xml('<message>empty_fields</message>');
				}
			}
		}else{
			echo xml('<message>invalid_user_id</message>');
		}
	}

	function ambulance_save_request($configuration){
		$id_request=$_GET["id_request"];
		$description=$_GET["description"];
			
		//свой id
		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);

		$org_id=0;
		
		if($_COOKIE['organization']){
			if($_COOKIE['organization'] == 1){
				$org_id=666;
			}else{
				$query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$org_id=$row[0];
				pg_free_result($result);
			}
		}
		
		if($id_user){
			if($id_request){
				$query='UPDATE visits SET description=\''.string_formating_for_sql($description).'\', updated_by='.$id_user.', updated_at=NOW()::timestamp(0) WHERE id_request='.string_formating_for_sql($id_request).'';	
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
		
				$message='<id_request>'.$id_request.'</id_request>';
				$message.='<message>request_saved</message>';
				echo xml($message);
			}else{
				echo xml('<message>invalid_request_id</message>');
			}
		}else{
			echo xml('<message>invalid_user_id</message>');
		}
	}

	function ambulance_cancel_request($configuration){
		$id_request=$_GET["id_request"];
			
		//свой id
		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);

		$org_id=0;
		
		if($_COOKIE['organization']){
			if($_COOKIE['organization'] == 1){
				$org_id=666;
			}else{
				$query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$org_id=$row[0];
				pg_free_result($result);
			}
		}
		
		if($id_user){
			if($id_request){
				$query='UPDATE visits SET status=\'A\', updated_by='.$id_user.', updated_at=NOW()::timestamp(0) WHERE id_request='.string_formating_for_sql($id_request).'';	
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
		
				$message='<id_request>'.$id_request.'</id_request>';
				$message.='<message>request_canceled</message>';
				echo xml($message);
			}else{
				echo xml('<message>invalid_request_id</message>');
			}
		}else{
			echo xml('<message>invalid_user_id</message>');
		}
	}

	function ambulance_inwork_request($configuration){
		$id_request=$_GET["id_request"];
				
		$id_user = user_id($configuration);

		if($id_user){
			if($id_request){
				$query='UPDATE visits SET status=\'W\', updated_by='.$id_user.', end_request_date=NOW()::timestamp(0), fact_start_dttm=NOW()::timestamp(0), updated_at=NOW()::timestamp(0) WHERE id_request='.string_formating_for_sql($id_request).'';	
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
		
				$message='<id_request>'.$id_request.'</id_request>';
				$message.='<message>request_inwork</message>';
				echo xml($message);
			}else{
				echo xml('<message>invalid_request_id</message>');
			}
		}else{
			echo xml('<message>invalid_user_id</message>');
		}
	}

	function ambulance_accept_request($configuration){
		$id_request=$_GET["id_request"];
			
		//свой id
		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);

		//При наличии у бригады заявки в статусе «Принято» бригада не должна иметь возможности принять вызов по следующей заявке.
		//При попытке принять вызов, не взяв в работу предыдущую заявку, должно отображаться уведомление «Для принятия вызова по новой заявке необходимо взять в работу предыдущую».
		//В правом верхнем углу уведомления необходимо реализовать кнопку "крест" для закрытия уведомления.
		
		if($id_user){
			if($id_request){
				//узнать номер бригады
				$query='SELECT id_brigade, id FROM visits WHERE id_request='.string_formating_for_sql($id_request).' LIMIT 1';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$id_brigade=$row[0];
				$id_visit=$row[1];
				pg_free_result($result);

				//
				$query_='SELECT id FROM visits WHERE id_brigade='.string_formating_for_sql($id_brigade).' AND status=\'P\' LIMIT 1';
				//echo $query_;
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				$row_ = pg_fetch_row($result_);
				$id_visit_=$row_[0];
				pg_free_result($result_);
				if($id_visit_){
					echo xml('<message>request_error</message>');
	
					return true;
				}
				//
				
				//узнать состав бригады
				$query_='SELECT id_specialist FROM brigades_specialists ';
				$query_.='WHERE brigades_specialists.id_brigade='.$id_brigade.'';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				while ($row_ = pg_fetch_assoc($result_)) {
					//добавить специалистов к приёму
					$query='INSERT INTO visits_specialists ';
					$query.='(id_specialist, id_visit, created_by, updated_by, created_at, updated_at) ';
					$query.='VALUES (';
					$query.="".$row_['id_specialist'].",";
					$query.="".$id_visit.",";
					$query.="".$id_user.",";#created_by
					$query.="".$id_user.",";#updated_by
					$query.="NOW()::timestamp(0),";#created_at
					$query.="NOW()::timestamp(0)";#updated_at
					$query.=');';

					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result);
					
				}
				pg_free_result($result_);
				
				$query='UPDATE visits SET status=\'P\', start_request_date=NOW()::timestamp(0), updated_by='.$id_user.', updated_at=NOW()::timestamp(0) WHERE id_request='.string_formating_for_sql($id_request).'';	
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
		
				$message='<id_request>'.$id_request.'</id_request>';
				$message.='<message>request_accepted</message>';
				echo xml($message);
			}else{
				echo xml('<message>invalid_request_id</message>');
			}
		}else{
			echo xml('<message>invalid_user_id</message>');
		}
	}

	function ambulance_brigades_slots_xml($config){
		$date='';
		for($i=0; $i<=14; $i++){
			$date_ = new DateTime;
			$date_->add( new DateInterval('P'. $i .'D') );
			if($date){$date.=',';}
			$date.=$date_->format('Y-m-d');
		}
	
		$query='SELECT brigades_timesheets.date, brigades_timesheets.id AS slot_id, brigades.name AS brigade_name, brigades.id AS brigade_id, '."\n";
		
		$query.='(LOWER(brigades_timesheets.date)::time) AS start_time,';
		$query.='(UPPER(brigades_timesheets.date)::time) AS end_time,';
		$query.='(LOWER(brigades_timesheets.date)::date) AS date, ';
		//$query.='lower(brigades_timesheets.date) AS start_date, upper(brigades_timesheets.date) AS end_date, '."\n";

		$query.='string_agg(users.fullname::character varying, \', \' ORDER BY users.fullname) AS specialists '."\n";
		$query.='FROM brigades_timesheets '."\n";
		$query.='LEFT JOIN brigades ON brigades.id=brigades_timesheets.id_brigade '."\n";
		$query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade '."\n";
		$query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist '."\n";
		$query.='LEFT JOIN users ON users.id=specialists.id_user '."\n";
		
		$query.='WHERE brigades_timesheets.date IS NOT NULL'."\n";
	
		$flag_date_exists=0;
		if($date){
			$dates = explode(",", $date);
			
			$query_d='';
			for ($i=0; $i<count($dates); $i++) {
				if(validate_date($dates[$i])){
					if($i>0){$query_d.=' OR ';}
					$query_d.='(tsrange(\''.$dates[$i].'\'::DATE, \''.$dates[$i].'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> brigades_timesheets.date)';
				}
			}
			if($query_d){
				$flag_date_exists=1;
				$query.=' AND ('.$query_d.')'."\n";
			}
		}
			
		$query.='GROUP BY brigades_timesheets.id, brigades.id '."\n";
		$query.='ORDER BY brigades_timesheets.date,brigades_timesheets.id ASC ';
		//echo $query;

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		echo '<recs>';
		
		if(!$flag_date_exists){
			$query.='LIMIT 10';
		}
	
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_date>'.$row['date'].'</rec_date>';
			echo '<rec_start_time>'.$row['start_time'].'</rec_start_time>';
			echo '<rec_end_time>'.$row['end_time'].'</rec_end_time>';
			echo '<rec_brigade_id>'.$row['brigade_id'].'</rec_brigade_id>';
			echo '<rec_brigade_name>'.$row['brigade_name'].'</rec_brigade_name>';
			echo '<rec_specialists>'.$row['specialists'].'</rec_specialists>';

			echo '</rec>';
		}
			
		pg_free_result($result);

		echo '</recs>';
		echo '</xml>';
	}

	function viewRequests($config){
		$id=$_GET["id"];		

		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form_search_recs" onsubmit="listShowRequests(); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Период</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '&nbsp;—&nbsp;';
		$date_ = strtotime("+7 day");
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d",$date_).'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Владелец</div>';
		echo '<div class="input col-12">';
		echo '<input type="text" name="owner" style="width: 300px;">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Бригада</div>';
		echo '<div class="input col-12">';
		show_brigades($config,2,0);
		echo '</div>';
		echo '</div>';

		/////////////////////
		$spec_organization_id='';

		if($_COOKIE['organization']){
			if($_COOKIE['organization'] == 1){
				$spec_organization_id=666;
			}else{
				$query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				$spec_organization_id=$row[0];
				pg_free_result($result);
			}
		}

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Специалист</div>';
		echo '<div class="input col-12">';
		show_specialists($config,2,0,$spec_organization_id);
		echo '</div>';
		echo '</div>';
		////////////////////////

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Состояние заявки</div>';
		echo '<div class="input col-12">';
		echo '<select class="status" name="status">';
		$statuses_array = array();
		array_push($statuses_array, array('status' => '','title' => 'Все'));
		array_push($statuses_array, array('status' => 'N','title' => 'Новая'));
		array_push($statuses_array, array('status' => 'F','title' => 'Завершена'));
		array_push($statuses_array, array('status' => 'A','title' => 'Отменена'));
		array_push($statuses_array, array('status' => 'W','title' => 'В работе'));
		array_push($statuses_array, array('status' => 'P','title' => 'Принято'));
		for ($i = 0; $i < count($statuses_array); $i++) {
            echo '<option value="'.$statuses_array[$i]['status'].'">'.$statuses_array[$i]['title'].'</option>';
	    }
    	echo '</select>';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Вид животных</div>';
		echo '<div class="input col-12">';
		echo '<select class="species" name="species">';
		$species_array = array();
		array_push($species_array, array('id' => '','title' => 'Все'));
		array_push($species_array, array('id' => '25','title' => 'Собаки'));
		array_push($species_array, array('id' => '9','title' => 'Кошки'));
		array_push($species_array, array('id' => 'OTHER','title' => 'Иные животные'));
		for ($i = 0; $i < count($species_array); $i++) {
            echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
	    }
		echo '</select>';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Кличка животного</div>';
		echo '<div class="input col-12">';
		echo '<input type="text" name="nick">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex buttons">';
		echo '<button tabindex="2" type="submit" style="width: 220px;" value="1" name="xls" id="xls">Сформировать отчет</button>';
		echo '<button tabindex="1" type="submit" style="margin-left: 15px; width: 120px;" value="1" name="submit" id="submit">Поиск</button>';
		echo '</div>';

		echo '</div>';

		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="row main_row">';
		
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		
		echo '<div class="col-xl-8 col-lg-10 main_row">';

		echo '<div class="table" id="ListRecs">';
		echo '<div class="p-3">...</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
		listShowRequests();
		});
HTML;

		if($id){
			$html.='showRequestWindow('.$id.');'."\n";
		}

		$html.='</script>';

		echo $html;
	}

	function viewBrigades($config){
		echo '<div class="row main_row" style="margin-top: 15px;">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 main_row">';
		
		echo '<div class="information_ message" id="message"></div>';

		echo '<div class="table" id="ListRecs">';
		echo '<div class="p-3">...</div>';
		echo '</div>';
		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '<script>';
		echo '$(document).ready(function () {';
		echo 'listShowBrigades();';
		echo '});';
		echo '</script>';
	}

	function viewMap($config){
		echo '<div class="row main_row" style="margin-top: 15px;">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 main_row">';
		
		echo '<div class="information_ message" id="message"></div>';

		echo '<div id="map_container" class="row main_row">';
		echo '<div id="info" class="map_info"></div>';
		echo '<div id="map" style="width: 100%; height: 800px"></div>';
		// echo '<div class="table" id="ListRecs">';
		// echo '<div class="p-3">...</div>';
		// echo '</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		// echo '<script src="https://api-maps.yandex.ru/2.1/?apikey='.$key_map.'&lang=ru_RU" type="text/javascript"></script>';
		$query='SELECT id, short_name, latitude, longitude, id_org_type FROM organizations WHERE id_org_type=44 OR id_org_type=42 OR id_org_type=41 OR id_org_type=40 OR id_org_type=39 OR id_org_type=38 ORDER BY short_name ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

		echo '<script>'."\n";
		echo '$(document).ready(function () {'."\n";
		echo 'ymaps.ready(init);'."\n";

		echo 'function init(){'."\n";
		
		echo 'var ArrayOrgsRecs=new Array();'."\n";
		echo 'var ArrayBrigadesRecs=new Array();'."\n";

    	while ($row = pg_fetch_assoc($result)) {
			echo 'ArrayOrgsRecs.push({id: '.$row['id'].', title: \''.$row['short_name'].'\', type: \''.$row['id_org_type'].'\', latitude: \''.$row['latitude'].'\', longitude: \''.$row['longitude'].'\'});'."\n";
		}
		pg_free_result($result);

$html = <<<HTML
    
		var map = new ymaps.Map("map", {
			center: [55.76, 37.64],
        	zoom: 12
        });

		

		var listItems = [
			new ymaps.control.ListBoxItem('Ветклиники'),
			new ymaps.control.ListBoxItem({options: {type: 'separator'}}),
			new ymaps.control.ListBoxItem('Лаборатории'),
		],

		myListBox = new ymaps.control.ListBox({
			data: {
				content: 'Объекты'
			},
			options: {
				float: 'right'
			},
			items: listItems
		});
		
		var mapClinics = new ymaps.GeoObjectCollection({}, {draggable: false});
		var mapLaboratories = new ymaps.GeoObjectCollection({}, {draggable: false});

		for(let i = 0; i < ArrayOrgsRecs.length; i++){
			if(ArrayOrgsRecs[i].type == '44' || ArrayOrgsRecs[i].type == '42' || ArrayOrgsRecs[i].type == '41' || ArrayOrgsRecs[i].type == '40' || ArrayOrgsRecs[i].type == '39'){
				let coords = [ArrayOrgsRecs[i].latitude, ArrayOrgsRecs[i].longitude];

				mapClinics.add(new ymaps.Placemark(coords, {balloonContent: ArrayOrgsRecs[i].title, hintContent: ArrayOrgsRecs[i].title},
				{
					iconLayout: 'default#image',
					iconImageHref: '/images/clinic.svg',
					iconImageSize: [32, 32],
					iconImageOffset: [0, 0]
				}
				));
			}
			if(ArrayOrgsRecs[i].type == '38'){
				let coords = [ArrayOrgsRecs[i].latitude, ArrayOrgsRecs[i].longitude];
				
				mapLaboratories.add(new ymaps.Placemark(coords, {balloonContent: ArrayOrgsRecs[i].title, hintContent: ArrayOrgsRecs[i].title},
					{
						iconLayout: 'default#image',
						iconImageHref: '/images/laboratory.svg',
						iconImageSize: [32, 32],
						iconImageOffset: [0, 0]
					}
				));
			}
		}	
		
		myListBox.get(0).events.add('click', function () {
			//Загружаем клиники
			if(myListBox.get(0).isSelected() == false){
				map.geoObjects.add(mapClinics);
			}else{
				map.geoObjects.remove(mapClinics);
			}
		});

		myListBox.get(2).events.add('click', function () {
			//Загружаем Лаборатории
			if(myListBox.get(2).isSelected() == false){
				map.geoObjects.add(mapLaboratories);
			}else{
				map.geoObjects.remove(mapLaboratories);
			}
		});

		////////////
		map.events.add('click', function (e) {
			var coords = e.get('coords');
			console.log(coords);
		});
		////////////
		
		let delayMap = 15000;//30000=30 сек
		var mapCars = new ymaps.GeoObjectCollection({}, {draggable: false});
		var mapRoutes;
		let loadBrigades = function() {
			jQuery.ajax({
				'async': true,
				'global': false,
				'cache': false,
				'type': 'GET',
				'dataType': 'xml',
				'data': 'action=brigades_map&mode=xml',
				'url': "index.php",
				'beforeSend': function() {
					//
				},
				'success': function (Recs) {
					if($(Recs).find('message').text() == 'token_invalid'){
						window.location.href = 'index.php';
					}else{
						mapCars.removeAll();
						map.geoObjects.removeAll(mapRoutes);

						//Загружаем клиники
						if(myListBox.get(0).isSelected() == true){
							map.geoObjects.add(mapClinics);
						}
						//Загружаем Лаборатории
						if(myListBox.get(2).isSelected() == true){
							map.geoObjects.add(mapLaboratories);
						}
						
						$(Recs).find('rec').each(function(){
							let coords = [$(this).find('rec_lat').text(), $(this).find('rec_lon').text()];
							mapCars.add(new ymaps.Placemark(coords, {hintContent: $(this).find('rec_name').text()},
								{
									iconLayout: 'default#image',
									iconImageHref: '/images/car.svg',
									iconImageSize: [32, 32],
									iconImageOffset: [0, 0]
								}
							));

							if($(this).find('rec_address').text()){
								//есть маршрут вызова
								
								mapRoutes = new ymaps.multiRouter.MultiRoute({   
									// Точки маршрута. Точки могут быть заданы как координатами, так и адресом. 
									referencePoints: [
										coords,
										''+$(this).find('rec_address').text()+''
									]
								});

								// , {
								// 	boundsAutoApply: true
								// }

								// Добавление маршрута на карту.
								map.geoObjects.add(mapRoutes);
							}
						});

						map.geoObjects.add(mapCars);
						let i = 0;
						$(Recs).find('rec').each(function(){
							let title = $(this).find('rec_name').text();
							let specialists = $(this).find('rec_specialists').text();
							let car_model = $(this).find('rec_car_model').text();
							let car_driver = $(this).find('rec_car_driver').text();
							let car_number = $(this).find('rec_car_number').text();
							let pass_number = $(this).find('rec_pass_number').text();
							let status = $(this).find('rec_status').text();
							let schedule = $(this).find('rec_schedule').text();

							mapCars.get(i).events.add('click', function () {
								let TempContent = '';
								TempContent += '<h2>'+title+'</h2><br /><br />';
								TempContent += '<h3>Состав бригады:</h3>';
								TempContent += specialists;
								TempContent += '<br /><br /><h3>Расписание:</h3>';
								TempContent += schedule;

								TempContent += '<br /><br /><h3>Текущая заявка:</h3>';
								if(status == 'P'){
									TempContent += 'Принято';
								}
								if(status == 'W'){
									TempContent += 'В работе';
								}

								$("#info").html(TempContent);
								$("#info").addClass("col-2");
								$("#map").addClass("col-10");
								$("#map_container").removeClass("main_row");
								map.container.fitToViewport();
							});
							i++;
						});
						map.container.fitToViewport();
					}
				}
			});
		}
		loadBrigades();
		var timerMap = setInterval(loadBrigades, delayMap);

		map.controls.add(myListBox);
		//map.redraw();
	}
});
HTML;

		$html.='</script>';

		echo $html;
	}

	if($action == 'auth'){
		authUser($configuration); 
	}else if($action == 'exit'){
		exitUser($configuration);
	}else if($action == 'add_brigade'){
		if(vallidateToken($configuration)){ambulance_add_brigade($configuration);}else{invalidToken($configuration);}
	}else if($action == 'make_request'){
		if(vallidateToken($configuration)){ambulance_make_request($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_request'){
		if(vallidateToken($configuration)){ambulance_save_request($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_brigade'){
		if(vallidateToken($configuration)){ambulance_delete_brigade($configuration);}else{invalidToken($configuration);}
	}else if($action == 'cancel_request'){
		if(vallidateToken($configuration)){ambulance_cancel_request($configuration);}else{invalidToken($configuration);}
	}else if($action == 'inwork_request'){
		if(vallidateToken($configuration)){ambulance_inwork_request($configuration);}else{invalidToken($configuration);}
	}else if($action == 'accept_request'){
		if(vallidateToken($configuration)){ambulance_accept_request($configuration);}else{invalidToken($configuration);}
	}else if($action == 'brigades' && $mode == 'xml'){
		if(vallidateToken($configuration)){ambulance_show_brigades_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'brigades_map' && $mode == 'xml'){
		if(vallidateToken($configuration)){ambulance_show_brigades_map_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'requests' && $mode == 'xml'){
		if(vallidateToken($configuration)){ambulance_show_requests_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'request' && $mode == 'xml'){
		if(vallidateToken($configuration)){ambulance_show_request_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'owners'){
		if(vallidateToken($configuration)){show_owners_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'contacts'){
		if(vallidateToken($configuration)){show_contacts_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_contacts'){
		if(vallidateToken($configuration)){save_contacts($configuration);}else{invalidToken($configuration);}
	}else if($action == 'specialists' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_specialists_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'addresses' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_addresses_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'slots' && $mode == 'xml'){
		if(vallidateToken($configuration)){ambulance_brigades_slots_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'service_types' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_service_types_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'species'){
		if(vallidateToken($configuration)){show_species_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'breeds'){
		if(vallidateToken($configuration)){show_breeds_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notification_read'){
    	if(vallidateToken($configuration)){read_notification_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notifications'){
    	if(vallidateToken($configuration)){show_notifications_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'fias_tokken' && $mode == 'xml'){if(vallidateToken($configuration)){
		$fias_tokken=fias_request($configuration);
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<fias_tokken>'.$fias_tokken->access_token.'</fias_tokken>';
		echo '</xml>';
	}else{invalidToken($configuration);}
	}else{
		if($_COOKIE['token']){
			if(vallidateToken($configuration)){
				header_site(0,$configuration, 'Ветеринарная помощь на дому','ambulance');
				sub_header_site($configuration);
				menu_site($configuration, 'ambulance', $action);

				informings_site($configuration);

				if($action == 'full_services_report'){
					viewFullServicesReport($configuration);
				}else if($action == 'brigades'){
					viewBrigades($configuration);
				}else if($action == 'map'){
					viewMap($configuration);
				}else{
					viewRequests($configuration);
				}
								
				sub_footer_site();
				footer_site();
			}else{
				header_site(1,$configuration);
				viewAuthUser('ambulance');
				footer_site();
			}
		}else{
			header_site(1,$configuration);
			viewAuthUser('ambulance');
			footer_site();
		}
	}
	
	if (function_exists('pg_connect')) {
		pg_close($dbconn);
	}
?>
