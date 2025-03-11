<?php

	$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';

	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';
	
	if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());}

	if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
	if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}
	$mode='json';

	function information_visits_json(){

		header('Content-Type: application/json; charset=utf-8');
			
		$sidguid=string_formating_for_sql($_GET["sidguid"]);
		$sidguid = substr_replace($sidguid, null, 0, 4);

		$query='SELECT visits_specialists.id_specialist AS specialist_id, visits.guid_video, visits.id AS visit_id, visits.duration, visits.id, visits.status, visits.time_range, visits.id_owner, visits.id_pet, ';
		$query.='organizations.short_name AS org_name, addresses.name AS org_address, visits.ticket_number, pet_owners.fullname AS owner_name, ';
		$query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,';
		$query.='(UPPER(VISITS.TIME_RANGE)::time) AS end_time,';
		$query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date ';
		$query.='FROM visits ';
		$query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
		$query.='LEFT JOIN organizations ON visits.id_organization=organizations.id ';
		$query.='LEFT JOIN visits_specialists ON visits.id=visits_specialists.id_visit ';
		$query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
		$query.='WHERE visits.guid_video=\''.$sidguid.'\' ';
		$query.='GROUP BY pet_owners.id, visits_specialists.id_specialist, visits.id, organizations.short_name, addresses.name ';
    	$query.='LIMIT 1 ';
		//echo $query;

		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$data='';
		while ($row = pg_fetch_assoc($result)) {
			$query_='SELECT users.fullname FROM specialists ';
			$query_.='LEFT JOIN users ON specialists.id_user=users.id ';
			$query_.='WHERE specialists.id='.$row['specialist_id'].'';
			$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
			$row_ = pg_fetch_row($result_);
			pg_free_result($result_);
			$specialist_name=$row_[0];

			$query_='SELECT name FROM public.visits_gov_services ';
			$query_.='LEFT JOIN gov_services ON visits_gov_services.id_service=gov_services.id ';
			$query_.='WHERE visits_gov_services.id_visit='.$row['visit_id'].'';
			$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
			$row_ = pg_fetch_row($result_);
			pg_free_result($result_);
			$service_name=$row_[0];

			$data.='{';
			$data.='"sid-guid": "s04-'.$row['guid_video'].'",';
			$data.='"Date": "'.$row['start_date'].'",';
			$data.='"Slot": "'.$row['start_time'].'",';
			$data.='"Duration": "'.$row['duration'].'",';
			$data.='"Organization": "'.$row['org_name'].'",';
			$data.='"FullNameDoctor": "'.$specialist_name.'",';
			$data.='"Service": "'.$service_name.'"';
			$data.='}';

		}
		pg_free_result($result);
		
		echo $data;
	}

	if($action == 'visits' && $mode == 'json'){
		information_visits_json();
	}
	
	if (function_exists('pg_connect')) {
		pg_close($dbconn);
	}
?>