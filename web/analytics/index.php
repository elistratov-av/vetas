<?php

	$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';

	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';
	
	if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());$result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());pg_free_result($result);}

	if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
	if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : null;
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
	}

	function analytics_show_reports_xml($config){
		header("Content-type: text/xml; charset=utf-8");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		$params = get_params();

		if (isset($params["type"])) $type = $params["type"][0];
		if (isset($params["status"])) $status = $params["status"][0];
		if (isset($params["species"])) $species = $params["species"][0];
		if (isset($params["channels"])) $channels = $params["channels"][0];
		if (isset($params["organization"])) $organization = $params["organization"][0];
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];

		$channelsArray = explode(",", $channels);

		if($type == 2){//Соотношение онлайн записей по месту приема
			// "VISIT_VC"
			// "VISIT"
			// "AMBULANCE"
			// "VISIT_VC_SHELTER"
			// "AT_HOME"
			$type_clinic = array("VISIT_VC", "VISIT", "VISIT_VC_SHELTER");
			$type_home = array("AT_HOME", "AMBULANCE");

			$query = 'SELECT COUNT(visits.id) AS "count" FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.=' WHERE visits.time_range IS NOT NULL AND ((LOWER(visits.time_range)::date) BETWEEN \''.$date_from.'\' AND \''.$date_to.'\')';
			$query.=' AND (';
			for($i=0; $i<=count($type_clinic); $i++){
				if($type_clinic[$i]){
					if($i>0){$query.=' OR ';}
					$query.='type=\''.$type_clinic[$i].'\'';
				}
			}
			$query.=')';

			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
					
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$clinic_counter=$row[0];
			pg_free_result($result);
			//
			$query = 'SELECT COUNT(visits.id) AS "count" FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.=' WHERE visits.time_range IS NOT NULL AND ((LOWER(visits.time_range)::date) BETWEEN \''.$date_from.'\' AND \''.$date_to.'\')';
			$query.=' AND (';
			for($i=0; $i<=count($type_home); $i++){
				if($type_home[$i]){
					if($i>0){$query.=' OR ';}
					$query.='type=\''.$type_home[$i].'\'';
				}
			}
			$query.=')'; 
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}

			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$home_counter=$row[0];
			pg_free_result($result);
			
			///
			echo '<recs>';
			echo '<rec>';
			echo '<rec_name>Записи</rec_name>';
			echo '<rec_count_clinic>'.$clinic_counter.'</rec_count_clinic>';
			echo '<rec_count_home>'.$home_counter.'</rec_count_home>';
			echo '</rec>';
			echo '</recs>';
		}else if($type == 5){//Главная статистика
			$query='SELECT COUNT(*) AS "count" ';
			$query.='FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='LEFT JOIN public.species ON species.id=pets.id_species ';
			$query.='WHERE visits.id IS NOT NULL ';
			
			if($channels != ''){
				$query.='AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			$query.='AND ((LOWER(visits.time_range)::date) BETWEEN \''.$date_from.'\' AND \''.$date_to.'\') ';

			//$query.=' OR (public.visits.created_at  BETWEEN \''.$date_from.'\' AND \''.$date_to.'\' AND public.visits.time_range IS NULL)';

			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$count_all=$row[0];
			pg_free_result($result);

			$query='SELECT COUNT(*) AS "count"';
			$query.='FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='LEFT JOIN public.species ON species.id=pets.id_species ';
			$query.='WHERE visits.time_range IS NOT NULL AND status = \'F\'';

			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			$query.='AND ((LOWER(visits.time_range)::date) BETWEEN \''.$date_from.'\' AND \''.$date_to.'\') ';
			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$count_visits=$row[0];
			pg_free_result($result);

			echo '<all>'.$count_all.'</all>';
			echo '<visits>'.$count_visits.'</visits>';
			
			
		}else if($type == 3){//Соотношение записей через портал и приложение
			$query='SELECT COUNT(visits.id) AS "count" FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='LEFT JOIN public.species ON species.id=pets.id_species ';
			$query.='LEFT JOIN public.breeds ON breeds.id=pets.id_breed ';
			$query.='LEFT JOIN etp.message_v2 ON etp.message_v2.visit_id=visits.id ';
			$query.='WHERE visits.time_range IS NOT NULL AND etp.message_v2.service_number IS NOT NULL AND etp.message_v2.service_number LIKE \'0001-9000003%\' ';
			$query.='AND ((LOWER(visits.time_range)::date) BETWEEN \''.$date_from.'\' AND \''.$date_to.'\') ';
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}
			
			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$count_portal=$row[0];
			pg_free_result($result);
			//

			$query='SELECT COUNT(visits.id) AS "count" FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='LEFT JOIN public.species ON species.id=pets.id_species ';
			$query.='LEFT JOIN public.breeds ON breeds.id=pets.id_breed ';
			$query.='LEFT JOIN etp.message_v2 ON etp.message_v2.visit_id=visits.id ';
			$query.='WHERE visits.time_range IS NOT NULL AND etp.message_v2.service_number IS NOT NULL AND etp.message_v2.service_number LIKE \'0002-9000005%\' ';
			$query.='AND ((LOWER(visits.time_range)::date) BETWEEN \''.$date_from.'\' AND \''.$date_to.'\') ';
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$count_mobile=$row[0];
			pg_free_result($result);
			
			///
			echo '<recs>';
			echo '<rec>';
			echo '<rec_name>Записи</rec_name>';
			echo '<rec_count_portal>'.$count_portal.'</rec_count_portal>';
			echo '<rec_count_mobile>'.$count_mobile.'</rec_count_mobile>';
			echo '</rec>';

			echo '</recs>';
		}else if($type == 4){//Записи авторизованных и неавторизованных пользователей
			$type_clinic = array("VISIT_VC", "VISIT", "VISIT_VC_SHELTER");
			$type_home = array("AT_HOME", "AMBULANCE");

			$query='SELECT COUNT(visits.id) AS "count" FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='LEFT JOIN public.species ON species.id=pets.id_species ';
			$query.='LEFT JOIN public.breeds ON breeds.id=pets.id_breed ';
			//$query.='LEFT JOIN etp.message ON etp.message.visit_id=visits.id ';
			$query.='LEFT JOIN etp.message_v2 ON etp.message_v2.visit_id=visits.id ';
			$query.='WHERE visits.time_range IS NOT NULL AND etp.message_v2.sso_id IS NOT NULL AND etp.message_v2.sso_id != \'unauthorized\' ';
			$query.="AND ((LOWER(visits.time_range)::date) BETWEEN '$date_from' AND '$date_to') ";
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}
			
			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$auth_counter=$row[0];
			pg_free_result($result);
			
			//

			$query='SELECT COUNT(visits.id) AS "count" FROM public.visits ';
			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='LEFT JOIN public.species ON species.id=pets.id_species ';
			$query.='LEFT JOIN public.breeds ON breeds.id=pets.id_breed ';
			$query.='LEFT JOIN etp.message_v2 ON etp.message_v2.visit_id=visits.id ';
			$query.="WHERE visits.time_range IS NOT NULL AND etp.message_v2.sso_id IS NOT NULL AND etp.message_v2.sso_id = 'unauthorized' ";
			$query.="AND ((LOWER(visits.time_range)::date) BETWEEN '$date_from' AND '$date_to') ";
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$unauth_counter=$row[0];
			pg_free_result($result);
			
			///
			echo '<recs>';
			echo '<rec>';
			echo '<rec_name>Пользователи</rec_name>';
			echo '<rec_count_unauth>'.$unauth_counter.'</rec_count_unauth>';
			echo '<rec_count_auth>'.$auth_counter.'</rec_count_auth>';
			echo '</rec>';

			echo '</recs>';
		}else if($type == 1){//Топ 5 услуг, на которые записывались
			$query="SELECT COUNT(visits.status) AS \"count\",gov_services.name,gov_services.id_pricelist, gov_services.id FROM public.visits ";
			$query.="LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ";
			$query.="LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ";
			$query.="LEFT JOIN public.visits_gov_services ON public.visits_gov_services.id_visit=visits.id ";
			$query.="LEFT JOIN public.gov_services ON public.gov_services.id=visits_gov_services.id_service ";
			$query.="WHERE ";
			$query.="visits.time_range IS NOT NULL ";
			$query.="AND ((LOWER(visits.time_range)::date) BETWEEN '$date_from' AND '$date_to') ";
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}

			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$query.="GROUP BY gov_services.id, gov_services.name ";
			$query.="ORDER BY COUNT(visits_gov_services.id_service) DESC ";
			$query.="LIMIT 5";
				
			$results=[];
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				array_push($results, array(
					'id' => ''.$row['id'].'',
					'name' => ''.$row['name'].'',
					// (Прайс №'.$row['id_pricelist'].')'
					'count_all' => ''.$row['count'],
					'count_fin' => '0',
				));
			}
			pg_free_result($result);
		
			///
		
			$query="SELECT COUNT(visits.status),gov_services.name, gov_services.id FROM public.visits ";
			$query.="LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ";
			$query.="LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ";
			$query.="LEFT JOIN public.visits_gov_services ON public.visits_gov_services.id_visit=visits.id ";
			$query.="LEFT JOIN public.gov_services ON public.gov_services.id=visits_gov_services.id_service ";
			$query.="WHERE ";
			$query.="visits.time_range IS NOT NULL AND visits.status = 'F' ";
			$query.="AND ((LOWER(visits.time_range)::date) BETWEEN '$date_from' AND '$date_to') ";
			
			if($channels != ''){
				$query.=' AND (';
				for($i=0; $i<=count($channelsArray);$i++){
					if($channelsArray[$i]){
						if($i > 0){$query.=' OR ';}
						$query.='visits.channel='.intval($channelsArray[$i]).'';
					}
				}
				$query.=') ';
			}else{
				$query.=' AND (visits.channel=999) ';
			}
			
			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}
			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$query.="GROUP BY gov_services.id, gov_services.name ";
			$query.="ORDER BY COUNT(visits_gov_services.id_service) DESC ";
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				for($i=0; $i<=count($results); $i++){
					if($results[$i]['id'] == $row['id']){
						$results[$i]['count_fin'] = $row['count'];
						break;
					}
				}
			}
			pg_free_result($result);

			$content='';
			echo '<recs>';
			for($i=0; $i<=count($results); $i++){
				if($results[$i]['name']){
					$content.='<rec>';
					#echo '<rec_name>'.mb_strimwidth($results[$i]['name'], 0, 30, '...').'</rec_name>';
					$content.='<rec_name>'.$results[$i]['name'].'</rec_name>';
					$content.='<rec_id>'.$results[$i]['id'].'</rec_id>';
					$content.='<rec_count_all>'.$results[$i]['count_all'].'</rec_count_all>';
					$content.='<rec_count_fin>'.$results[$i]['count_fin'].'</rec_count_fin>';
					$content.='</rec>';
				}
			}
			$content.='</recs>';

			echo $content;
		}else{//Количество записей к ветеринару+Динамика записей к ветеринару по каналам записи
			$query='SELECT COUNT(*) AS "count",	public.visits.channel, (case when public.shift_type.description = \'Рабочий день\' then \'Запись по направлению\' else public.shift_type.description end) AS "channel_name", (LOWER(VISITS.time_range)::date) AS "date" ';
			$query.='FROM public.visits ';
			$query.='LEFT JOIN public.shift_type ON public.shift_type.id=public.visits.channel ';

			$query.='LEFT JOIN public.visit_pets ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN public.pets ON pets.id=visit_pets.id_pet ';
			$query.='WHERE ';
			$query.="visits.time_range IS NOT NULL  AND ((LOWER(visits.time_range)::date) BETWEEN '$date_from' AND '$date_to') ";
			if($status){
				$query.="AND visits.status='".$status."' ";
			}
			if($species){
				if($species == 'OTHER'){
					$query.="AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
				}else{
					$query.="AND public.pets.id_species='".intval($species)."' ";
				}
			}else{
				$query.="AND public.pets.id_species IS NOT NULL ";
			}

			if($organization){
				$query.="AND visits.id_organization='".$organization."' ";
			}
			$query.='GROUP BY (LOWER(visits.time_range)::date), channel,channel_name ';
			$query.='ORDER BY (LOWER(visits.time_range)::date) ASC ';

			$results=[];

			// 1	"Запись по направлению"
			// 2	"Запись с mos.ru"
			// 3	"Запись по телефону"
			// 4	"Живая очередь"
			// 10	"Выезд на дом (НВП)"
			// 14	"Выезд на дом (mos.ru)"
			$channels = array("1", "2", "3", "4", "10", "11", "13", "14", "12");

			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				array_push($results, array(
					'date' => ''.$row['date'].'',
					'channel' => ''.$row['channel'].'',
					'count' => ''.$row['count'].'',
				));
			}
			pg_free_result($result);

			echo '<recs>';
			$seconds = abs(strtotime($date_from) - strtotime($date_to) - 86400);
			$days = round($seconds / 86400, 1);
			
			if($days < 10){								//по дням
				$period = new \DatePeriod(new \DateTime($date_from),new \DateInterval('P1D'),new \DateTime($date_to.'+1 day'));
				foreach ($period as $date) {
					echo '<rec>';
					echo '<rec_date>'.$date->format('Y-m-d').'</rec_date>';
	
					for($j=0; $j<=count($channels); $j++){
						if($channels[$j]){
							$flag=1;
							for($i=0; $i<=count($results); $i++){
								if($results[$i]['channel'] == $channels[$j] && $date->format('Y-m-d') == $results[$i]['date']){
									echo '<rec_channel'.$channels[$j].'>'.$results[$i]['count'].'</rec_channel'.$channels[$j].'>';
									$flag=0;
								}
							}
							if($flag){
								echo '<rec_channel'.$channels[$j].'>0</rec_channel'.$channels[$j].'>';
							}
						}
					}
	
					echo '</rec>';
				}
			}else if($days >= 10 && $days < 31){		//5 дней
				$period = new \DatePeriod(new \DateTime($date_from),new \DateInterval('P5D'),new \DateTime($date_to.'+1 day'));

				foreach ($period as $date) {
					echo '<rec>';
					$temp_period = new \DatePeriod(new \DateTime($date->format('Y-m-d')),new \DateInterval('P1D'),new \DateTime($date->format('Y-m-d').'+5 day'));

					$temp=new \DateTime($temp_period->getEndDate()->format('Y-m-d').'-1 day');
					if(new \DateTime($date_to.'+1 day') < new \DateTime($date->format('Y-m-d').'+5 day')){
						$temp_=new \DateTime($date_to);
						echo '<rec_date>'.$date->format('Y-m-d').'-'.$temp_->format('Y-m-d').'</rec_date>';	
					}else{
						echo '<rec_date>'.$date->format('Y-m-d').'-'.$temp->format('Y-m-d').'</rec_date>';
					}
	
					for($j=0; $j<=count($channels); $j++){
						if($channels[$j]){
							$counter=0;
							for($i=0; $i<=count($results); $i++){
								if($results[$i]['channel'] == $channels[$j]){
									foreach ($temp_period as $temp_date) {
										echo $temp_date->format('Y-m-d')."\n";
										if($results[$i]['date'] == $temp_date->format('Y-m-d')){
											$counter=$counter+$results[$i]['count'];
										}
									}
								}
							}
							echo '<rec_channel'.$channels[$j].'>'.$counter.'</rec_channel'.$channels[$j].'>';
						}
					}
	
					echo '</rec>';
				}
			}else if($days >= 31 && $days < 365){		//1 мес.
				$period = new \DatePeriod(new \DateTime($date_from),new \DateInterval('P1M'),new \DateTime($date_to.'+1 day'));
				foreach ($period as $date) {
					echo '<rec>';
					echo '<rec_date>'.$date->format('Y-m').'</rec_date>';
	
					for($j=0; $j<=count($channels); $j++){
						if($channels[$j]){
							$flag=1;
							$counter=0;
							for($i=0; $i<=count($results); $i++){
								$temp=new \DateTime($results[$i]['date']);
								if($results[$i]['channel'] == $channels[$j] && $date->format('Y-m') == $temp->format('Y-m')){
									$counter=$counter+$results[$i]['count'];
									$flag=0;
								}
							}
							echo '<rec_channel'.$channels[$j].'>'.$counter.'</rec_channel'.$channels[$j].'>';
						}
					}
	
					echo '</rec>';
				}

			}else if($days >= 365 && $days < 365*3){//3 мес.
				$period = new \DatePeriod(new \DateTime($date_from),new \DateInterval('P3M'),new \DateTime($date_to.'+1 day'));
				foreach ($period as $date) {
					echo '<rec>';

					$temp_period = new \DatePeriod(new \DateTime($date->format('Y-m-d')),new \DateInterval('P1D'),new \DateTime($date->format('Y-m-d').'+3 month'));

					$date_from_ = new \DateTime($date->format('Y-m-d'));
					$date_to_ = new \DateTime($date->format('Y-m-d').'+3 month - 1 day');

					// echo $date_from_->format('Y-m-d');
					// echo $date_to_->format('Y-m-d');

					$temp=new \DateTime($temp_period->getEndDate()->format('Y-m-d').'-1 day');
					if(new \DateTime($date_to.'+1 day') < new \DateTime($date->format('Y-m-d').'+3 month')){
						$temp_=new \DateTime($date_to);
						echo '<rec_date>'.$date->format('Y-m-d').'-'.$temp_->format('Y-m-d').'</rec_date>';	
					}else{
						echo '<rec_date>'.$date->format('Y-m-d').'-'.$temp->format('Y-m-d').'</rec_date>';
					}
					
					for($j=0; $j<=count($channels); $j++){
						$counter=0;
						if($channels[$j]){
							$flag=1;
							
							for($i=0; $i<=count($results); $i++){
								$temp=new \DateTime($results[$i]['date']);
								if($results[$i]['channel'] == $channels[$j] && 
									(
										$temp >= $date_from_ && $temp <= $date_to_
									)
								){
									$counter=$counter+intval($results[$i]['count']);

									//if($i == 0){
										// echo '<rec_d>'.$temp->format('Y-m-d').'</rec_d>';
										// echo '<rec_с>'.$results[$i]['count'].'</rec_с>';
										// echo '<rec_сc>'.$counter.'</rec_сc>';
									//}
									$flag=0;
								}
							}
							echo '<rec_channel'.$channels[$j].'>'.$counter.'</rec_channel'.$channels[$j].'>';
						}
					}
	
					echo '</rec>';
				}
			}else{//год
				$period = new \DatePeriod(new \DateTime($date_from),new \DateInterval('P365D'),new \DateTime($date_to.'+1 day'));

				foreach ($period as $date) {
					echo '<rec>';

					echo '<rec_date>'.$date->format('Y').'</rec_date>';
					$counter=0;
					for($j=0; $j<=count($channels); $j++){
						if($channels[$j]){
							$flag=1;
							
							for($i=0; $i<=count($results); $i++){
								$temp=new \DateTime($results[$i]['date']);
								if($results[$i]['channel'] == $channels[$j] && $date->format('Y') == $temp->format('Y')){
									$counter=$counter+$results[$i]['count'];
									$flag=0;
								}
							}
							echo '<rec_channel'.$channels[$j].'>'.$counter.'</rec_channel'.$channels[$j].'>';
						}
					}
	
					echo '</rec>';
				}
			}
			echo '</recs>';
		}
		echo '</xml>';
	}

	function analytics_show_services_price_report_xml($config){
		set_time_limit(150);

		if(isset($_POST["xls"])){$xls=$_POST["xls"];}else{$xls=$_GET["xls"];}
		//
		if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
    	if($page == ''){$page=1;}
		$recs_on_page=50;//кол-во на странице
		//

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
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Отчёт по услугам');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($font_style);

			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "№п/п");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "Код");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', "Услуги");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', "Количество услуг");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', "Стоимость оказанных услуг, руб.");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);
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
		$TempData='';

		$params = get_params();
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];

		$type='';
		if(count($params["type"]) > 0){
			for ($i=0; $i<count($params["type"]); $i++) {
				if($i>0){
					$type.=',';
				}
				$type.=$params["type"][$i];
			}
			$type='\''.$type.'\'';
		}else{
			$type='NULL';
		}
		///
			
		$organization='';
		if(count($params["organization"]) > 0){
			for ($i=0; $i<count($params["organization"]); $i++) {
				if($i>0){
					$organization.=',';
				}
				$organization.=$params["organization"][$i];
			}
			$organization='\''.$organization.'\'';
		}else{
			$organization='NULL';
		}

		$specialists='';
		if(count($params["doc"]) > 0){
			for ($i=0; $i<count($params["doc"]); $i++) {
				if($i>0){$specialists.=',';}
				$docs = explode(",", $params["doc"][$i]);
				
				for ($j=0; $j<count($docs); $j++) {
					if(intval($docs[$j])){
						if($j>0){$specialists.=',';}
						$specialists.=''.intval($docs[$j]).'';
					}
				}
			}
		}
		if($specialists){
			$specialists='\''.$specialists.'\'';
		}else{
			$specialists='NULL';
		}
		
		///
		$services='';
		if(count($params["service"]) > 0){
			for ($i=0; $i<count($params["service"]); $i++) {
				if($i>0){$services.=',';}
				$services.=''.intval($params["service"][$i]).'';
			}
		}
		if($services){
			$services='\''.$services.'\'';
		}else{
			$services='NULL';
		}

		//дата начало
		//дата окончание
		//service_types//всё текст
		//organizations
		//specialists
		//services
	
		$TempData.='<recs>';
		
		//$query="SELECT  * FROM get_fullservicesvol2detailreport('$date_from'::DATE,'$date_to'::DATE,".$area.",".$districts.",".$visit_types.",".$channels.",".$organization.",".$species.",".$specialists.",".$services.")";
		//select * from get_servicesreport('20220101'::date, '20220301'::date, null,null,null,null)
		$query="SELECT  * FROM get_servicesreport('$date_from'::DATE,'$date_to'::DATE, ".$type.", ".$organization.", ".$specialists.", ".$services.")";
		//расчёт страниц
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);
		//расчёт страниц

		if(!$xls){
            $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
        }
		
		$service_count=0;
		$service_sum=0;
		
		$n=3;
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if(!$xls){
				$TempData.='<rec>';
				$TempData.='<rec_cod>'.$row['cod'].'</rec_cod>';
				$TempData.='<rec_service_name>'.$row['service_name'].'</rec_service_name>';
				$TempData.='<rec_service_count>'.$row['service_count'].'</rec_service_count>';
				$TempData.='<rec_service_sum>'.$row['service_sum'].'</rec_service_sum>';
				$TempData.='<rec_service_count_all>'.$row['service_count_all'].'</rec_service_count_all>';
				$TempData.='<rec_service_sum_all>'.$row['service_sum_all'].'</rec_service_sum_all>';
				$TempData.='</rec>';
			}

			if($xls){
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$n, ($n-2))
				->setCellValue('B'.$n, $row['cod'])
				->setCellValue('C'.$n, $row['service_name'])
				->setCellValue('D'.$n, $row['service_count'])
				->setCellValue('E'.$n, $row['service_sum']);

				$service_count = $service_count + $row['service_count'];
				$service_sum = $service_sum + $row['service_sum'];

				$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("D".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("D".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("E".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("E".$n)->getAlignment()->applyFromArray($cell_style);
				
				$objPHPExcel->getActiveSheet()->getRowDimension($n)->setRowHeight(50);
			}
			$n++;
		}
		pg_free_result($result);


		if(!$xls){
			$TempData.='</recs>';

			$TempData.='<counter>'.$recs_counter.'</counter>';

			$TempData.='<pages>';
			if($recs_counter > 0){
				$pages_number = intval($recs_counter / $recs_on_page);
				if($pages_number > 1){
					for($i = 1; $i < $pages_number+1; $i++){
						$TempData.='<page>'.$i.'</page>';
					}
				}
			}
			$TempData.='</pages>';

			$TempData.='</xml>';
			echo $TempData;
		}

		if($xls){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$n, 'Итого');
			
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$n.':C'.$n.'');

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('D'.$n, $service_count)
				->setCellValue('E'.$n, $service_sum);

			$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("D".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("D".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("E".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("E".$n)->getAlignment()->applyFromArray($cell_style);
		}

		if($xls){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			$objWriter->save('php://output');
		}
	}

	function analytics_show_vaccination_report_xml($config){
		if(isset($_POST["xls"])){$xls=$_POST["xls"];}else{$xls=$_GET["xls"];}

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
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Отчёт по охвату вакцинации');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:P1');
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getStyle("A1:P1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:P1")->applyFromArray($font_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Организация");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:A3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A2:A3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "Кошек на учете (всего)");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:B3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B2:B3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2:B3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', "Кошек на учете (за выбр. период)");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C2:C3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('C2:C3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("C2:C3")->applyFromArray($border_style);

			//
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', "Вакцинировано");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('D2:E2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D2:E2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("D2:E2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D3', "Вакциной Рабикан");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E3', "Комплексной вакциной");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('E3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', "Отказ от вакцинации");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('F2:F3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('F2:F3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("F2:F3")->applyFromArray($border_style);
			///////////////////////////////////

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2', "Собак на учете (всего)");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('G2:G3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('G2:G3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("G2:G3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2', "Собак на учете (за выбр. период)");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('H2:H3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('H2:H3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("H2:H3")->applyFromArray($border_style);
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2', "Вакцинировано");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('I2:J2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('I2:J2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("I2:J2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I3', "Вакциной Рабикан");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('I3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("I3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J3', "Комплексной вакциной");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('J3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("J3")->applyFromArray($border_style);
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2', "Отказ от вакцинации");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('K2:K3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('K2:K3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("K2:K3")->applyFromArray($border_style);

			////////////////////

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2', "Прочих животных на учете (всего)");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('L2:L3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('L2:L3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("L2:L3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2', "Прочих животных на учете (за выбр. период)");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('M2:M3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('M2:M3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("M2:M3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2', "Вакцинировано");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('N2:O2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('N2:O2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("N2:O2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N3', "Вакциной Рабикан");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('N3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("N3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('O3', "Комплексной вакциной");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('O3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("O3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('P2', "Отказ от вакцинации");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('P2:P3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('P2:P3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("P2:P3")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(100);
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
		
		$params = get_params();
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];

		$area='';
    	if(count($params["area"]) > 0){
			for ($i=0; $i<count($params["area"]); $i++) {
				if($i>0){
					$area.=',';
				}
				$area.=$params["area"][$i];
			}
			$area='\''.$area.'\'';
		}else{
			$area='NULL';
		}
		
		$organization='';
		if(count($params["organization"]) > 0){
			for ($i=0; $i<count($params["organization"]); $i++) {
				if($i>0){
					$organization.=',';
				}
				$organization.=$params["organization"][$i];
			}
			$organization='\''.$organization.'\'';
		}else{
			$organization='NULL';
		}
	
		if(!$xls){
			echo '<recs>';
		}
		$query="SELECT  * FROM get_vaccination_report('$date_from'::DATE,'$date_to'::DATE,".$area.",".$organization.") ORDER BY area_name ASC";
		$n=4;
		$area='';

		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if($area != $row['area_name']){
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, $row['area_name']);
				$objPHPExcel->getActiveSheet()->mergeCells('A'.$n.':Y'.$n);
				$area = $row['area_name'];
				$n++;
			}

			if(!$xls){
				echo '<rec>';
				echo '<rec_id_area>'.$row['id_area'].'</rec_id_area>';
				echo '<rec_area_name>'.$row['area_name'].'</rec_area_name>';
				echo '<rec_id_organization>'.$row['id_organization'].'</rec_id_organization>';
				echo '<rec_organization_name>'.$row['organization_name'].'</rec_organization_name>';
				echo '<rec_organization_address>'.$row['organization_address'].'</rec_organization_address>';
				
				echo '<rec_all_dog_count>'.$row['all_dog_count'].'</rec_all_dog_count>';
				echo '<rec_all_cat_count>'.$row['all_cat_count'].'</rec_all_cat_count>';
				echo '<rec_all_other_count>'.$row['all_other_count'].'</rec_all_other_count>';

				echo '<rec_all_dog_count_period>'.$row['all_dog_count_period'].'</rec_all_dog_count_period>';
				echo '<rec_all_cat_count_period>'.$row['all_cat_count_period'].'</rec_all_cat_count_period>';
				echo '<rec_all_other_count_period>'.$row['all_other_count_period'].'</rec_all_other_count_period>';

				echo '<rec_rabican_dog_vaccin_period>'.$row['rabican_dog_vaccin_period'].'</rec_rabican_dog_vaccin_period>';
				echo '<rec_rabican_cat_vaccin_period>'.$row['rabican_cat_vaccin_period'].'</rec_rabican_cat_vaccin_period>';
				echo '<rec_rabican_other_vaccin_period>'.$row['rabican_other_vaccin_period'].'</rec_rabican_other_vaccin_period>';

				echo '<rec_rabies_dog_vaccin_period>'.$row['rabies_dog_vaccin_period'].'</rec_rabies_dog_vaccin_period>';
				echo '<rec_rabies_cat_vaccin_period>'.$row['rabies_cat_vaccin_period'].'</rec_rabies_cat_vaccin_period>';
				echo '<rec_rabies_other_vaccin_period>'.$row['rabies_other_vaccin_period'].'</rec_rabies_other_vaccin_period>';

				echo '<rec_dog_cancellation_count>'.$row['dog_cancellation_count'].'</rec_dog_cancellation_count>';
				echo '<rec_cat_cancellation_count>'.$row['cat_cancellation_count'].'</rec_cat_cancellation_count>';
				echo '<rec_other_cancellation_count>'.$row['other_cancellation_count'].'</rec_other_cancellation_count>';
				echo '</rec>';
			}

			if($xls){
				if($row['shelter_address']){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, $row['organization_name'].", ".$row['organization_address']);
				}else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, $row['organization_name']);
				}

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B'.$n, $row['all_cat_count'])
				->setCellValue('C'.$n, $row['all_cat_count_period'])
				->setCellValue('D'.$n, $row['rabican_cat_vaccin_period'])
				->setCellValue('E'.$n, $row['rabies_cat_vaccin_period'])
				->setCellValue('F'.$n, $row['cat_cancellation_count'])

				->setCellValue('G'.$n, $row['all_dog_count'])
				->setCellValue('H'.$n, $row['all_dog_count_period'])
				->setCellValue('I'.$n, $row['rabican_dog_vaccin_period'])
				->setCellValue('J'.$n, $row['rabies_dog_vaccin_period'])
				->setCellValue('K'.$n, $row['dog_cancellation_count'])

				->setCellValue('L'.$n, $row['all_other_count'])
				->setCellValue('M'.$n, $row['all_other_count_period'])
				->setCellValue('N'.$n, $row['rabican_other_vaccin_period'])
				->setCellValue('O'.$n, $row['rabies_other_vaccin_period'])
				->setCellValue('P'.$n, $row['other_cancellation_count']);

				$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("D".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("D".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("E".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("E".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("F".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("F".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("G".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("G".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("H".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("H".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("I".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("I".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("J".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("J".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("K".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("K".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("L".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("L".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("M".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("M".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("N".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("N".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("O".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("O".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("P".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("P".$n)->getAlignment()->applyFromArray($cell_style);

				$objPHPExcel->getActiveSheet()->getRowDimension($n)->setRowHeight(50);
			}
			$n++;
		}
		pg_free_result($result);

		if(!$xls){
			echo '</recs>';
			echo '</xml>';
		}

		if($xls){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			// $objWriter->save(str_replace('.php', '.xlsx', __FILE__));

			$objWriter->save('php://output');
		}
	}

	function analytics_show_vaccination_shelter_report_xml($config){
		if(isset($_POST["xls"])){$xls=$_POST["xls"];}else{$xls=$_GET["xls"];}

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
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Охват вакцинации в приютах');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:Y1');
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getStyle("A1:Y1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:Y1")->applyFromArray($font_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Наименование приюта, адрес");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:A4');
			$objPHPExcel->getActiveSheet()->getStyle("A2:A4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "Поголовье животных содержащихся в приютах");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B2:C2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B2:C2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2:C2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', "Поголовье животных на первый день  заданого  периода, в т.ч. по видам");			
			$objPHPExcel->getActiveSheet()->mergeCells('D2:E2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D2:E2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("D2:E2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', "Движение животных за выбранный период");
			$objPHPExcel->getActiveSheet()->mergeCells('F2:K2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('F2:K2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("F2:K2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2', "Поголовье животных на последний день заданного периода, в т.ч. по видам");
			$objPHPExcel->getActiveSheet()->mergeCells('L2:M2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('L2:M2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("L2:M2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2', "Вакцинировано животных против бешенства (Рабикан)");
			$objPHPExcel->getActiveSheet()->mergeCells('N2:Q2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('N2:Q2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("N2:Q2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('R2', "Вакцинировано животных против бешенства (другие вакцины)");
			$objPHPExcel->getActiveSheet()->mergeCells('R2:U2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('R2:U2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("R2:U2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('V2', "Вакцинировано собак против лептоспироза");
			$objPHPExcel->getActiveSheet()->mergeCells('V2:W2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('V2:W2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("V2:W2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('X2', "Вакцинировано собак против чумы");
			$objPHPExcel->getActiveSheet()->mergeCells('X2:Y2');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('X2:Y2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("X2:Y2")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(100);

			////////////////////////////
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', "Всего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B3:C3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B3:C3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("B3:C3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D3', "Всего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('D3:E3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D3:E3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("D3:E3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F3', "Прибыло");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('F3:G3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('F3:G3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("F3:G3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H3', "Убыло");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('H3:I3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('H3:I3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("H3:I3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J3', "Пало");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('J3:K3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('J3:K3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("J3:K3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L3', "Всего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('L3:M3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('L3:M3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("L3:M3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N3', "с начала года текущего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('N3:O3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('N3:O3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("N3:O3")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('P3', "за выбранный период");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('P3:Q3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('P3:Q3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("P3:Q3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('R3', "с начала года текущего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('R3:S3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('R3:S3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("R3:S3")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('T3', "за выбранный период");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('T3:U3');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('T3:U3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("T3:U3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('V3', "с начала года  текущего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('V3:V4');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('V3:V4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("V3:V4")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('W3', "за выбранный период");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('W3:W4');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('W3:W4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("W3:W4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('X3', "с начала года  текущего");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('X3:X4');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('X3:X4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("X3:X4")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y3', "за выбранный период");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('Y3:Y4');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('Y3:Y4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("Y3:Y4")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(75);

			////////////////////////////

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("B4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('C4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("C4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("D4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('E4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("E4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('F4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("F4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('G4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("G4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('H4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("H4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('I4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("I4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('J4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("J4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('K4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("K4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('L4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("L4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('M4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('M4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("M4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('N4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("N4")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('O4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('O4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("O4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('P4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('P4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("P4")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('Q4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("Q4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('R4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('R4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("R4")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('S4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('S4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("S4")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('T4', "Собаки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('T4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("T4")->applyFromArray($border_style);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('U4', "Кошки");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('U4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("U4")->applyFromArray($border_style);
			
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
		
		$params = get_params();
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];

		$area='';
    	if(count($params["area"]) > 0){
			for ($i=0; $i<count($params["area"]); $i++) {
				if($i>0){
					$area.=',';
				}
				$area.=$params["area"][$i];
			}
			$area='\''.$area.'\'';
		}else{
			$area='NULL';
		}
		
		$organization='';
		if(count($params["shelter"]) > 0){
			for ($i=0; $i<count($params["shelter"]); $i++) {
				if($i>0){
					$organization.=',';
				}
				$organization.=$params["shelter"][$i];
			}
			$organization='\''.$organization.'\'';
		}else{
			$organization='NULL';
		}
	
		if(!$xls){
			echo '<recs>';
		}
		$query="SELECT  * FROM get_vaccination_shelter_report('$date_from'::DATE,'$date_to'::DATE,".$area.",".$organization.") ORDER BY area_name ASC";
		$n=5;
		$area='';
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if($area != $row['area_name']){
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, $row['area_name']);
				$objPHPExcel->getActiveSheet()->mergeCells('A'.$n.':Y'.$n);
				$area = $row['area_name'];
				$n++;
			}

			if(!$xls){
				echo '<rec>';
				echo '<rec_id_area>'.$row['id_area'].'</rec_id_area>';
				echo '<rec_area_name>'.$row['area_name'].'</rec_area_name>';
				echo '<rec_id_organization>'.$row['id_organization'].'</rec_id_organization>';
				echo '<rec_organization_name>'.$row['organization_name'].'</rec_organization_name>';
				echo '<rec_id_shelter>'.$row['id_shelter'].'</rec_id_shelter>';
				echo '<rec_shelter_name>'.$row['shelter_name'].'</rec_shelter_name>';
				echo '<rec_shelter_address>'.$row['shelter_address'].'</rec_shelter_address>';
				echo '<rec_all_dog_count>'.$row['all_dog_count'].'</rec_all_dog_count>';
				echo '<rec_all_cat_count>'.$row['all_cat_count'].'</rec_all_cat_count>';
				echo '<rec_firstday_dog_count>'.$row['firstday_dog_count'].'</rec_firstday_dog_count>';
				echo '<rec_firstday_cat_count>'.$row['firstday_cat_count'].'</rec_firstday_cat_count>';
				echo '<rec_lastday_dog_count>'.$row['lastday_dog_count'].'</rec_lastday_dog_count>';
				echo '<rec_lastday_cat_count>'.$row['lastday_cat_count'].'</rec_lastday_cat_count>';
				echo '<rec_arrival_dog_count>'.$row['arrival_dog_count'].'</rec_arrival_dog_count>';
				echo '<rec_arrival_сat_count>'.$row['arrival_сat_count'].'</rec_arrival_сat_count>';
				echo '<rec_death_dog_count>'.$row['death_dog_count'].'</rec_death_dog_count>';
				echo '<rec_death_cat_count>'.$row['death_cat_count'].'</rec_death_cat_count>';
				echo '<rec_left_dog_count>'.$row['left_dog_count'].'</rec_left_dog_count>';
				echo '<rec_left_cat_count>'.$row['left_cat_count'].'</rec_left_cat_count>';
				echo '<rec_rabican_dog_vaccin_year>'.$row['rabican_dog_vaccin_year'].'</rec_rabican_dog_vaccin_year>';
				echo '<rec_rabican_cat_vaccin_year>'.$row['rabican_cat_vaccin_year'].'</rec_rabican_cat_vaccin_year>';
				echo '<rec_rabican_dog_vaccin_period>'.$row['rabican_dog_vaccin_period'].'</rec_rabican_dog_vaccin_period>';
				echo '<rec_rabican_cat_vaccin_period>'.$row['rabican_cat_vaccin_period'].'</rec_rabican_cat_vaccin_period>';
				echo '<rec_rabies_dog_vaccin_year>'.$row['rabies_dog_vaccin_year'].'</rec_rabies_dog_vaccin_year>';
				echo '<rec_rabies_cat_vaccin_year>'.$row['rabies_cat_vaccin_year'].'</rec_rabies_cat_vaccin_year>';
				echo '<rec_rabies_dog_vaccin_period>'.$row['rabies_dog_vaccin_period'].'</rec_rabies_dog_vaccin_period>';
				echo '<rec_rabies_cat_vaccin_period>'.$row['rabies_cat_vaccin_period'].'</rec_rabies_cat_vaccin_period>';
				echo '<rec_plague_dog_vaccin_year>'.$row['plague_dog_vaccin_year'].'</rec_plague_dog_vaccin_year>';
				echo '<rec_plague_dog_vaccin_period>'.$row['plague_dog_vaccin_period'].'</rec_plague_dog_vaccin_period>';
				echo '<rec_lepra_dog_vaccin_year>'.$row['lepra_dog_vaccin_year'].'</rec_lepra_dog_vaccin_year>';
				echo '<rec_lepra_dog_vaccin_period>'.$row['lepra_dog_vaccin_period'].'</rec_lepra_dog_vaccin_period>';
				echo '</rec>';
			}

			if($xls){
				if($row['shelter_address']){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, $row['shelter_name'].", ".$row['shelter_address']);
				}else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, $row['shelter_name']);
				}

				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B'.$n, $row['all_dog_count'])
				->setCellValue('C'.$n, $row['all_cat_count'])
				->setCellValue('D'.$n, $row['firstday_dog_count'])
				->setCellValue('E'.$n, $row['firstday_cat_count'])

				->setCellValue('F'.$n, $row['arrival_dog_count'])
				->setCellValue('G'.$n, $row['arrival_сat_count'])
				->setCellValue('H'.$n, $row['left_dog_count'])
				->setCellValue('I'.$n, $row['left_cat_count'])
				->setCellValue('J'.$n, $row['death_dog_count'])
				->setCellValue('K'.$n, $row['death_cat_count'])

				->setCellValue('L'.$n, $row['lastday_dog_count'])
				->setCellValue('M'.$n, $row['lastday_cat_count'])

				->setCellValue('N'.$n, $row['rabican_dog_vaccin_year'])
				->setCellValue('O'.$n, $row['rabican_cat_vaccin_year'])
				->setCellValue('P'.$n, $row['rabican_dog_vaccin_period'])
				->setCellValue('Q'.$n, $row['rabican_cat_vaccin_period'])

				->setCellValue('R'.$n, $row['rabies_dog_vaccin_year'])
				->setCellValue('S'.$n, $row['rabies_cat_vaccin_year'])
				->setCellValue('T'.$n, $row['rabies_dog_vaccin_period'])
				->setCellValue('U'.$n, $row['rabies_cat_vaccin_period'])

				->setCellValue('X'.$n, $row['lepra_dog_vaccin_year'])
				->setCellValue('Y'.$n, $row['lepra_dog_vaccin_period'])
				->setCellValue('V'.$n, $row['plague_dog_vaccin_year'])
				->setCellValue('W'.$n, $row['plague_dog_vaccin_period']);

				$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("D".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("D".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("E".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("E".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("F".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("F".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("G".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("G".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("H".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("H".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("I".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("I".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("J".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("J".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("K".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("K".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("L".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("L".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("M".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("M".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("N".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("N".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("O".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("O".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("P".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("P".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("Q".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("Q".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("R".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("R".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("S".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("S".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("T".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("T".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("U".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("U".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("X".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("X".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("Y".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("Y".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("V".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("V".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("W".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("W".$n)->getAlignment()->applyFromArray($cell_style);

				$objPHPExcel->getActiveSheet()->getRowDimension($n)->setRowHeight(50);
			}
			$n++;
		}
		pg_free_result($result);
		if(!$xls){
			echo '</recs>';
			echo '</xml>';
		}

		if($xls){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			// $objWriter->save(str_replace('.php', '.xlsx', __FILE__));

			$objWriter->save('php://output');
		}
	}

	function analytics_show_veterinary_specialists_report_xml($config){
		if(isset($_POST["xls"])){$xls=$_POST["xls"];}else{$xls=$_GET["xls"];}
		if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
    	if($page == ''){$page=1;}

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
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="your_name.xlsx"');
			header('Cache-Control: max-age=0');
		}else{
			header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<xml>';
		}
		
		$params = get_params();
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];

		$organization='';
		if(count($params["organization"]) > 0){
			for ($i=0; $i<count($params["organization"]); $i++) {
				if($i>0){
					$organization.=',';
				}
				$organization.=$params["organization"][$i];
			}
			$organization='\''.$organization.'\'';
		}else{
			$organization='NULL';
		}

		$specialists='';
		if(count($params["doc"]) > 0){
			for ($i=0; $i<count($params["doc"]); $i++) {
				if($i>0){$specialists.=',';}
				$docs = explode(",", $params["doc"][$i]);
				
				for ($j=0; $j<count($docs); $j++) {
					if(intval($docs[$j])){
						if($j>0){$specialists.=',';}
						$specialists.=''.intval($docs[$j]).'';
					}
				}
			}
		}
		if($specialists){
			$specialists='\''.$specialists.'\'';
		}else{
			$specialists='NULL';
		}
	
		//дата начало
		//дата окончание
		//организации через ,
		//специалисты ,

		$query="SELECT  * FROM get_employeesreport('".$date_from."'::DATE,'".$date_to."'::DATE, ".$organization.", $specialists) ORDER BY service_type_name ASC, specialist_name DESC ";
		
		$n=3;

		$full_array = array();
		$service_types_array = array();
		$service_types_array_ = array();
		$users_array_ = array();
		$users_array = array();
		$all_service_sum = 0;
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

		while ($row = pg_fetch_assoc($result)) {
			
			array_push($full_array, 
				array(
					'user_id' => ''.$row['user_id'].'',
					'specialist_name' => ''.$row['specialist_name'].'',
					'service_type_name' => ''.$row['service_type_name'].'',
					'specialist_stype_service_count' => ''.$row['specialist_stype_service_count'].'',
					'stype_service_count' => ''.$row['stype_service_count'].'',
					'specialist_service_sum' => ''.$row['specialist_service_sum'].'',
					'specialist_service_count' => ''.$row['specialist_service_count'].'',
					'all_service_sum' => ''.$row['all_service_sum'].'',
				)
			);

			$flag = 1;
			for($i=0; $i<=count($service_types_array);$i++){
				if($service_types_array[$i] == $row['service_type_name']){
					$flag = 0;
				}
			}
			if($flag){
				array_push($service_types_array, $row['service_type_name']);
			}

			$flag = 1;
			for($i=0; $i<=count($users_array_);$i++){
				if($users_array_[$i] == $row['user_id']){
					$flag = 0;
				}
			}
			if($flag){
				array_push($users_array_, $row['user_id']);
			}
			array_push($users_array, array('id' => ''.$row['user_id'].'', 'name' => ''.$row['specialist_name'].''));
			
			$all_service_sum = $row['all_service_sum'];
		}
		pg_free_result($result);
		//Временное решение 1232
		$abc = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$abc_array = [];
		foreach ($abc as $char1) {
			$abc_array[] = $char1;
		}
		foreach ($abc as $char1) {
			foreach ($abc as $char2) {
				$abc_array[] = $char1 . $char2;
			}
		}
		foreach ($abc as $char1) {
			foreach ($abc as $char2) {
				foreach ($abc as $char3) {
					$abc_array[] = $char1 . $char2 . $char3;
				}
			}
		}
		
		//$service_types_array_ = array_unique($service_types_array, SORT_REGULAR);
		//$users_array_ = array_unique($users_array_, SORT_REGULAR);//считаем количество столбцов

		//SELECT * FROM get_employeesreport('2023-01-01'::DATE,'2024-12-31'::DATE,null,null) ORDER BY service_type_name ASC, specialist_name DESC

		if($xls){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Отчёт по работе ветеринарных специалистов');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
			$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($font_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "№п/п");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "Тип услуг");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);

			$t=0;
			for($i=0; $i<=count($users_array_);$i++){
				if($users_array_[$i]){
					for($j=0; $j<=count($users_array);$j++){
						if($users_array[$j]['id'] == $users_array_[$i]){
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].'2', $users_array[$j]['name']);
							$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].'2')->getAlignment()->applyFromArray($cell_style);
							$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2].'2')->applyFromArray($border_style);
							$t++;

							break;
						}
					}
				}
			}

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].'2', "Итого");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].'2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2]."2")->applyFromArray($border_style);

			///
			for($i=0; $i<=count($service_types_array);$i++){
				if($service_types_array[$i]){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, ($n-2));
					$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$n)->getAlignment()->applyFromArray($cell_style);
					$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);

					$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$n, $service_types_array[$i]);
					$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$n)->getAlignment()->applyFromArray($cell_style);
					$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);

					$t=0;
					for($k=0; $k<=count($users_array_);$k++){
						if($users_array_[$k]){
							$flag = false;
							//
							for($j=0; $j<count($full_array); $j++){
								if(
									$service_types_array[$i] == $full_array[$j]['service_type_name'] && 
									$users_array_[$k] == $full_array[$j]['user_id']
								){
									$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].''.$n, $full_array[$j]['specialist_stype_service_count']);
									$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].''.$n)->getAlignment()->applyFromArray($cell_style);
									$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2].''.$n)->applyFromArray($border_style);

									$flag = true;
								}
							}
							if(!$flag){
								$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].''.$n, 0);
								$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].''.$n)->getAlignment()->applyFromArray($cell_style);
								$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2].''.$n)->applyFromArray($border_style);
							}
							$t++;
							//
						}
					}

					//
					for($j=0; $j<count($full_array); $j++){
						if($service_types_array[$i] == $full_array[$j]['service_type_name']){
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].''.$n, $full_array[$j]['stype_service_count']);
							$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].''.$n)->getAlignment()->applyFromArray($cell_style);
							$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2].''.$n)->applyFromArray($border_style);
						}
					}
					//

					$objPHPExcel->getActiveSheet()->getRowDimension($n)->setRowHeight(50);

					$n++;
				}
			}
			///

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$n, "");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$n, "Общая стоимость приёмов");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);

			$t=0;
			for($i=0; $i<=count($users_array_);$i++){
				if($users_array_[$i]){
					for($j=0; $j<=count($users_array);$j++){
						if($users_array[$j]['id'] == $users_array_[$i]){

							for($k=0; $k<count($full_array); $k++){
								if($users_array_[$i] == $full_array[$k]['user_id']){
									$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].''.$n, $full_array[$k]['specialist_service_sum']);
									$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].''.$n)->getAlignment()->applyFromArray($cell_style);
									$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2].''.$n)->applyFromArray($border_style);
								}
							}
							$t++;

							break;
						}
					}
				}
			}

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($abc_array[$t+2].''.$n, $all_service_sum);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($abc_array[$t+2].''.$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle($abc_array[$t+2].''.$n)->applyFromArray($border_style);
		}

		if(!$xls){
			echo '<recs>';
			for($i=0; $i<=count($full_array);$i++){
				if($full_array[$i]){
					echo '<rec>';
					echo '<rec_specialist>'.$full_array[$i]['specialist_name'].'</rec_specialist>';
					echo '<rec_service>'.$full_array[$i]['service_type_name'].'</rec_service>';
					echo '<rec_specialist_service_count>'.$full_array[$i]['specialist_stype_service_count'].'</rec_specialist_service_count>';
					echo '<rec_specialist_service_sum>'.$full_array[$i]['specialist_service_sum'].'</rec_specialist_service_sum>';

					echo '<rec_service_count>'.$full_array[$i]['stype_service_count'].'</rec_service_count>';
					echo '</rec>';
				}
			}
			echo '</recs>';

			echo '<services>';
			for($i=0; $i<=count($service_types_array);$i++){
				if($service_types_array[$i]){
					echo '<service>';
					echo '<service_name>'.$service_types_array[$i].'</service_name>';
					echo '</service>';
				}
			}
			echo '</services>';

			echo '<specialists>';
			$specialists_num = 0;
			for($i=0; $i<=count($users_array_);$i++){
				if($users_array_[$i]){
					for($j=0; $j<=count($users_array);$j++){
						if($users_array[$j]['id'] == $users_array_[$i]){
							echo '<specialist>';
							echo '<specialist_name>'.$users_array[$j]['name'].'</specialist_name>';
							echo '</specialist>';
							$specialists_num++;
							break;
						}
					}
				}
			}
			echo '</specialists>';

			echo '<information>';
			echo '<all_service_sum>'.$all_service_sum.'</all_service_sum>';
			echo '<specialists_num>'.$specialists_num.'</specialists_num>';
			echo '</information>';
		}

		if(!$xls){
			echo '</xml>';
		}

		if($xls){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
		}
	}

	function analytics_show_animal_disease_report_xml($config){
		if(isset($_POST["xls"])){$xls=$_POST["xls"];}else{$xls=$_GET["xls"];}
		//
		if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
    	if($page == ''){$page=1;}
		$recs_on_page=50;//кол-во на странице
		//

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
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="your_name.xlsx"');
			header('Cache-Control: max-age=0');
		}else{
			header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<xml>';
		}

		if($xls){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Отчёт по болезням животных');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getStyle("A1:C1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:C1")->applyFromArray($font_style);

			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "№п/п");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "Наименование заболевания");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', "Количество");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);
		}
		
		if(isset($_POST["contagious"])){$contagious=$_POST["contagious"];}else{$contagious=$_GET["contagious"];}
		//$species=$_GET["species"];
		
		$params = get_params();
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];
		$contagious = isset($params["contagious"][0]) ? '1' : '0';

		$organization='';
		if(count($params["organization"]) > 0){
			for ($i=0; $i<count($params["organization"]); $i++) {
				if($i>0){
					$organization.=',';
				}
				$organization.=$params["organization"][$i];
			}
			$organization='\''.$organization.'\'';
		}else{
			$organization='NULL';
		}

		$specialists='';
		if(count($params["doc"]) > 0){
			for ($i=0; $i<count($params["doc"]); $i++) {
				if($i>0){$specialists.=',';}
				$docs = explode(",", $params["doc"][$i]);
				
				for ($j=0; $j<count($docs); $j++) {
					if(intval($docs[$j])){
						if($j>0){$specialists.=',';}
						$specialists.=''.intval($docs[$j]).'';
					}
				}
			}
		}
		if($specialists){
			$specialists='\''.$specialists.'\'';
		}else{
			$specialists='NULL';
		}

		$diseases='';
		if(count($params["disease"]) > 0){
			for ($i=0; $i<count($params["disease"]); $i++) {
				if($i>0){
					$diseases.=',';
				}
				$diseases.=$params["disease"][$i];
			}
			$diseases='\''.$diseases.'\'';
		}else{
			$diseases='NULL';
		}

		$species='';
		if(count($params["species"]) > 0){
			for ($i=0; $i<count($params["species"]); $i++) {
				if($i>0){
					$species.=',';
				}
				$species.=$params["species"][$i];
			}
			$species='\''.$species.'\'';
		}else{
			$species='NULL';
		}

		if($contagious){
			$contagious='true';
		}else{
			$contagious='false';
		}
	
		//дата начало
		//дата окончание
		//организации ,
		//специалисты ,
		//species - 'CAT','DOG','OTHER')
		//gos_diseases
		//danger
		
		$query="SELECT  * FROM get_diseasesreport('".$date_from."'::DATE,'".$date_to."'::DATE, ".$organization.", ".$specialists.", ".$species.", ".$diseases.", $contagious)";
		//расчёт страниц
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);
		//расчёт страниц

		if(!$xls){
            $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
        }

		$TempData='<recs>';
		
		$n=3;
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$diseases_count = 0;
		while ($row = pg_fetch_assoc($result)) {
			if(!$xls){
				$TempData.='<rec>';
				$TempData.='<rec_name>'.$row['gost_disease_name'].'</rec_name>';
				$TempData.='<rec_count>'.$row['diseases_count'].'</rec_count>';
				$TempData.='<rec_total>'.$row['total_diseases_count'].'</rec_total>';
				$TempData.='</rec>';
			}else{
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$n, ($n-2))
				->setCellValue('B'.$n, $row['gost_disease_name'])
				->setCellValue('C'.$n, $row['diseases_count']);

				$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);

				$diseases_count = $diseases_count + $row['diseases_count'];
			}
			$n++;
		}
		pg_free_result($result);

		if($xls){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$n, 'Итого');
			
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$n.':B'.$n.'');

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('C'.$n, $diseases_count);

			$objPHPExcel->getActiveSheet()->getStyle("A".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("A".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("B".$n)->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("C".$n)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$n)->getAlignment()->applyFromArray($cell_style);
		}

		if(!$xls){
			$TempData.='</recs>';

			$TempData.='<counter>'.$recs_counter.'</counter>';

			$TempData.='<pages>';
			if($recs_counter > 0){
				$pages_number = intval($recs_counter / $recs_on_page);
				if($pages_number > 1){
					for($i = 1; $i < $pages_number+1; $i++){
						$TempData.='<page>'.$i.'</page>';
					}
				}
			}
			$TempData.='</pages>';

			$TempData.='</xml>';
			echo $TempData;
		}

		if($xls){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
		}
	}

	function analytics_show_notifications_report_xml($config){
		if(isset($_POST["xls"])){$xls=$_POST["xls"];}else{$xls=$_GET["xls"];}
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
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(false);
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);

			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Отчёт по уведомлениям');
			$objPHPExcel->getActiveSheet()->mergeCells('A1:C1');
			$objPHPExcel->getActiveSheet()->getStyle("A1:C1")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->getStyle("A1:C1")->applyFromArray($font_style);

			//
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Первое уведомление");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A2:A4');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "О вакцинации");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', "Об идентификации");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B3')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B3")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', "О вакцинации и идентификации");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B4')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B4")->applyFromArray($border_style);
			//

			//
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', "Напоминание о вакцинации");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A5')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A5")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A5:A6');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', "Бешенство");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B5')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B5")->applyFromArray($border_style);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6', "Лептоспироз");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B6')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("B6")->applyFromArray($border_style);
			//

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A7', "Напоминание об идентификации");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A7:B7')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A7:B7")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A7:B7');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A8', "Уведомление о проведении противоэпизотических мероприятиях на местности");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A8:B8')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A8:B8")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A8:B8');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A9', "Уведомление о найденом/отловленном владельческом животном");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A9:B9')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A9:B9")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A9:B9');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A10', "Уведомление о готовности результатов исследований");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A10:B10')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A10:B10")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A10:B10');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A11', "Уведомление о переносе приема владельцу животного");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A11:B11')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A11:B11")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A11:B11');

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A12', "Отправленные результаты по завершенному приему (телеветеринария)");
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A12:B12')->getAlignment()->applyFromArray($cell_style);
			$objPHPExcel->getActiveSheet()->getStyle("A12:B12")->applyFromArray($border_style);
			$objPHPExcel->getActiveSheet()->mergeCells('A12:B12');

			
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(50);

			// $objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(75);
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

		$params = get_params();
		
		$date_from = $params["date_from"][0];
		$date_to = $params["date_to"][0];
		$species = $params["species"];
		$owners = $params["owners"][0];
		
		if(!$xls){
			echo '<recs>';
		}

		$query.='SELECT COUNT(event_code), event_code FROM subscription.log ';
		$query.='LEFT JOIN public.pet_owners ON subscription.log.id_owner=public.pet_owners.id ';
		$query.='LEFT JOIN public.pets ON subscription.log.id_pet=public.pets.id ';
		$query.='LEFT JOIN public.species ON public.pets.id_species = public.species.id ';
		$query.='WHERE subscription.log.is_success=\'true\' AND ';
		if (in_array('OTHER', $species)) {
			if (in_array('9', $species) && in_array('25', $species)) {
				$query .= 'public.pets.id_species IS NOT NULL AND ';
			} elseif (in_array('9', $species)) {
				$query .= 'public.pets.id_species NOT IN (\'25\') AND ';
			} elseif (in_array('25', $species)) {
				$query .= 'public.pets.id_species NOT IN (\'9\') AND ';
			} else {
				$query .= 'public.pets.id_species NOT IN (\'9\', \'25\') AND ';
			}
		} else {
			$species_list = implode(',', $species);

			$query .= sizeof($species_list) ? 'public.pets.id_species IN (' . $species_list . ') AND ' : ' ';
		}

		if($owners){
			$ownersArray = explode(",", $owners);

			$query.='(';

			for($i=0; $i<=count($ownersArray);$i++){		
				if($ownersArray[$i] != ''){
					$id_owner = str_replace("[ ", "", $ownersArray[$i]);
					$id_owner = str_replace(" ]", "", $id_owner);

					if($i>0){$query.=' OR ';}
					$query.='pet_owners.id = '.$id_owner.'';
				}
			}

			$query.=') AND ';
		}

		$query.='subscription.log.log_time BETWEEN \''.$date_from.' 00:00:00\' AND \''.$date_to.' 00:00:00\'::DATE + INTERVAL \'1 DAY\' ';
		$query.='GROUP BY event_code';
		
		$events_array = array();
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if(!$xls){
				echo '<rec>';
				echo '<rec_code>'.$row['event_code'].'</rec_code>';
				echo '<rec_count>'.$row['count'].'</rec_count>';
				echo '</rec>';
			}

			if($xls){
				array_push($events_array, array('count' => $row['count'],'code' => $row['event_code']));
			}
			$n++;
		}
		pg_free_result($result);
		if(!$xls){
			echo '</recs>';
			echo '</xml>';
		}

		if($xls){
			$initial_info_vaccination=0;
			$initial_info_identification=0;
			$initial_info_identification_and_vaccin=0;
			$remind_vaccination=0;
			$remind_vaccination_lepto=0;
			$remind_identification=0;
			$quarantine=0;
			$animal_found=0;
			$research=0;
			$transfer_visit_vetas_perenos_priema=0;
			$appointment_results=0;

			for($i=0;$i<=count($events_array);$i++){
				if($events_array[$i]['code'] == 'initial_info_vaccination'){$initial_info_vaccination=$initial_info_vaccination+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'initial_info_identification'){$initial_info_identification=$initial_info_identification+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'initial_info_identification_and_vaccin'){$initial_info_identification_and_vaccin=$initial_info_identification_and_vaccin+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'remind_vaccination'){$remind_vaccination=$remind_vaccination+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'remind_vaccination_lepto'){$remind_vaccination_lepto=$remind_vaccination_lepto+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'remind_identification'){$remind_identification=$remind_identification+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'quarantine'){$quarantine=$quarantine+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'animal_found'){$animal_found=$animal_found+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'research'){$research=$research+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'transfer_visit'){$transfer_visit_vetas_perenos_priema=$transfer_visit_vetas_perenos_priema+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'vetas_perenos_priema'){$transfer_visit_vetas_perenos_priema=$transfer_visit_vetas_perenos_priema+$events_array[$i]['count'];}
				if($events_array[$i]['code'] == 'appointment_results'){$appointment_results=$appointment_results+$events_array[$i]['count'];}
			}

			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('C2', $initial_info_vaccination)
			->setCellValue('C3', $initial_info_identification)
			->setCellValue('C4', $initial_info_identification_and_vaccin)
			->setCellValue('C5', $remind_vaccination)
			->setCellValue('C6', $remind_vaccination_lepto)
			->setCellValue('C7', $remind_identification)
			->setCellValue('C8', $quarantine)
			->setCellValue('C9', $animal_found)
			->setCellValue('C10', $research)
			->setCellValue('C11', $transfer_visit_vetas_perenos_priema)
			->setCellValue('C12', $appointment_results);

			for($i=2; $i<=12; $i++){
				$objPHPExcel->getActiveSheet()->getStyle("C".$i)->applyFromArray($border_style);$objPHPExcel->getActiveSheet()->getStyle("C".$i)->getAlignment()->applyFromArray($cell_style);
				$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(50);
			}
		}

		if($xls){
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			// $objWriter->save(str_replace('.php', '.xlsx', __FILE__));

			$objWriter->save('php://output');
		}
	}

	function addQuestion($config) {
		$groupId=intval(trim($_GET["groupid"]));
		$question=str_replace('*EOL*', '%EOL%', trim( $_GET["question"]));
		$answer=str_replace('*EOL*', '%EOL%', trim( $_GET["answer"]));

		$id_user = user_id($config);
		$sql = "insert into faq2(question, answer, created_by, updated_by, created_at, updated_at) ";
		$sql.='VALUES (';

		$sql.= "'" . $question . "',";
		$sql.= "'" . $answer . "',";
		$sql.= $id_user . ",";#created_by
		$sql.= $id_user . ",";#updated_by
		$sql.= "NOW()::timestamp(0),";#created_at
		$sql.= "NOW()::timestamp(0)";#updated_at
		$sql.=') RETURNING id;';

		$result = pg_query($sql) or die(xml('<message>question_already_exists</message>'));

		$row = pg_fetch_row($result);
		$id_faq=$row[0];
		pg_free_result($result);

		$sql = "insert into faq2_to_group(id_faq2, id_faq2_group, created_by, updated_by, created_at, updated_at) ";
		$sql.='VALUES (';
		$sql.= "" . $id_faq . ",";
		$sql.= "" . addslashes($groupId) . ",";
		$sql.= $id_user . ",";#created_by
		$sql.= $id_user . ",";#updated_by
		$sql.= "NOW()::timestamp(0),";#created_at
		$sql.= "NOW()::timestamp(0)";#updated_at
		$sql.=')';

		$result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);
	}

	function createGroup($config) {
		$group=$_GET["group"];
		$questions=$_GET["questions"];

		$id_user = user_id($config);

		$faqIds = implode(",", $questions);

		$faqIds = "(" . addslashes($faqIds) . ")";

		$sql = "select id from faq2_group where code = 'general_group'";
		$result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$generalGroupId=$row[0];
		pg_free_result($result);

		$sql = "insert into faq2_group(code, title, created_by, updated_by, created_at, updated_at) ";
		$sql.='VALUES (';
		$sql.= "'" . $group . "',";
		$sql.= "'" . $group . "',";
		$sql.= $id_user . ",";#created_by
		$sql.= $id_user . ",";#updated_by
		$sql.= "NOW()::timestamp(0),";#created_at
		$sql.= "NOW()::timestamp(0)";#updated_at
		$sql.=') RETURNING id;';

		$result = pg_query($sql) or die(xml('<message>group_already_exists</message>'));

		$row = pg_fetch_row($result);
		$group_id=$row[0];
		pg_free_result($result);


		foreach ($questions as $question) {

			$sql = "insert into faq2_to_group(id_faq2, id_faq2_group, created_by, updated_by, created_at, updated_at) ";
			$sql.='VALUES (';
			$sql.= "" . $question . ",";
			$sql.= "" . $group_id . ",";
			$sql.= $id_user . ",";#created_by
			$sql.= $id_user . ",";#updated_by
			$sql.= "NOW()::timestamp(0),";#created_at
			$sql.= "NOW()::timestamp(0)";#updated_at
			$sql.=')';
			$result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());
			pg_free_result($result);
		}

		if (sizeof($questions) > 0) {
			if($generalGroupId){
				$sql = "DELETE FROM faq2_to_group where id_faq2_group = $generalGroupId AND id_faq2 in $faqIds";
				$result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result);
			}
		}
	}

	function viewAnalytics($config){
		if($_SERVER['SERVER_NAME'] == 'localhost'){
			$base_url="http://localhost:8888/analytics/index.php";
		}else if($_SERVER['SERVER_NAME'] == 'vetas-prod.starlink-soft.ru'){
			$base_url="https://vetas-prod.starlink-soft.ru/analytics/index.php";
		}else if($_SERVER['SERVER_NAME'] == 'vetas-predprod.mos.ru'){
			$base_url="https://vetas-predprod.mos.ru/analytics/index.php";
		}else{
			$base_url="/analytics/";
		}
		//
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ShowCharts(); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Период</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '&nbsp;—&nbsp;';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Статус приёма</div>';
		echo '<div class="input col-12">';
		echo '<select class="status" name="status">';// multiple="multiple"
		
		$statuses_array = array();
		array_push($statuses_array, array('status' => '','title' => 'Все'));
		array_push($statuses_array, array('status' => 'N','title' => 'Новый'));
		array_push($statuses_array, array('status' => 'F','title' => 'Завершен'));
		array_push($statuses_array, array('status' => 'A','title' => 'Отменен'));
		array_push($statuses_array, array('status' => 'W','title' => 'В работе'));
		array_push($statuses_array, array('status' => 'T','title' => 'Перенесен'));
		array_push($statuses_array, array('status' => 'C','title' => 'Изменен'));
		array_push($statuses_array, array('status' => 'D','title' => 'Пациент не явился'));
		for ($i = 0; $i < count($statuses_array); $i++) {
            echo '<option value="'.$statuses_array[$i]['status'].'">'.$statuses_array[$i]['title'].'</option>';
	    }
    	echo '</select>';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Вид животных</div>';
		echo '<div class="input col-12">';
		echo '<select class="species" name="species">';// multiple="multiple"
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
		echo '<div class="label col-12" style="width: 50px;">Клиника</div>';
		echo '<div class="input col-12">';
		show_organizations($config,2,0);
		echo '</div>';
		echo '</div>';
			

		echo '<div class="d-inline-flex row">';
		echo '<div class="stat">';
		echo '<nobr>Всего записей: <span id="all">0</span></nobr><br>';
		echo '<nobr>Из них завершенных приемов: <span id="f_visits">0</span> <span id="f_visits_per" class="per">0</span><span class="per">%</span></nobr><br>';
		echo '<nobr>Незавершенные приемы: <span id="n_visits">0</span> <span id="n_visits_per" class="per">0</span><span class="per">%</span></nobr>';
		echo '</div>';
		echo '</div>';
		//
		
		echo '<div class="d-inline-flex buttons"><button type="submit" style="width: 120px;">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('."'ResetDashboardReport'".'); ShowCharts();">Сбросить</button>';
        echo '</div>';
		echo '</div>';

		echo '<input type="hidden" id="channels" name="channels" value="1,2,3,4,10,11,13,14,12">';
		echo '<input type="hidden" name="action" value="reports">';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '</div>';

		//графики
		echo '<div class="row main_row">';
		
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		
		echo '<div class="col-xl-8 col-lg-10 main_row">';

		echo '<div class="row">';
		echo '<div class="col-xl-4 col-lg-4 col-md-6"><div class="JxChart chart loading" id="JCMSReportChart1" style="height: 400px; margin-bottom: 30px;"></div></div>';
		echo '<div class="col-xl-4 col-lg-4 col-md-6"><div class="JxChart chart loading" id="JCMSReportChart2" style="height: 400px; margin-bottom: 30px;"></div></div>';
		echo '<div class="col-xl-4 col-lg-4 col-md-6"><div class="JxChart chart loading" id="JCMSReportChart3" style="height: 400px; margin-bottom: 30px;"></div></div>';

		echo '<div class="col-xl-4 col-lg-4 col-md-6"><div class="JxChart chart loading" id="JCMSReportChart4" style="height: 400px;"></div></div>';
		echo '<div class="col-xl-4 col-lg-4 col-md-6"><div class="JxChart chart loading" id="JCMSReportChart5" style="height: 400px;"></div></div>';
		echo '<div class="col-xl-4 col-lg-4 col-md-6"><div class="JxChart chart loading" id="JCMSReportChart6" style="height: 400px;"></div></div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';
		//графики

		$html = <<<HTML
		<script type="text/javascript">
		var new_chart1;
		var new_chart2;
		var new_chart3;
		var new_chart4;
		var new_chart5;
		var new_chart6;
		var channels = ["1", "2", "3", "4", "10", "11", "12", "13", "14", "12"];
		var timerId=null;

		function charts_control(TempName, TempValue){
			if(TempName == 'new_chart1'){
				new_chart4.hashChartDisabled=new_chart1.hashChartDisabled;
				new_chart4.ShowChart();
			}else{
				new_chart1.hashChartDisabled=new_chart4.hashChartDisabled;
				new_chart1.ShowChart();
			}

			let channels_='';
			for(let i=0; i<=channels.length-1; i++){
				if(new_chart1.hashChartDisabled['channel'+channels[i]] == '0' || typeof new_chart1.hashChartDisabled['channel'+channels[i]] === 'undefined'){
					if(channels_){channels_+=',';}
					channels_+=channels[i];
				}
			}
			$("#channels").val(channels_);

			if(timerId !== null) {
				clearTimeout(timerId);
				timerId = null;
			}
			timerId=setTimeout(ShowChartsTimer, 1500);
		}
		
		function ShowChartsTimer(){
			GetAnalyticsData();
			new_chart2.LoadDataURL('load');
			new_chart3.LoadDataURL('load');
			new_chart5.LoadDataURL('load');
			new_chart6.LoadDataURL('load');
			timerId = null;
		}

		function ShowCharts(){
			GetAnalyticsData();
			new_chart1.LoadDataURL('load');
			new_chart4.LoadDataURL('load');
			new_chart2.LoadDataURL('load');
			new_chart3.LoadDataURL('load');
			new_chart5.LoadDataURL('load');
			new_chart6.LoadDataURL('load');
		}

		async function GetAnalyticsData(){
			varFormData=jQuery('#form').serialize();
			let response = await fetch('index.php?type=5&'+varFormData, {method: 'GET'});
			let xml = await response.text();

			if($($.parseXML(xml)).find('all').text()){
				let all=$($.parseXML(xml)).find('all').text();
				let visits=$($.parseXML(xml)).find('visits').text();

				if(all != 0){
					outNum(all, "all");
					outNum(visits, "f_visits");
					outNum(parseInt(all - visits), "n_visits");
					outNum(100-parseInt(parseInt(all - visits)/(all/100)), "f_visits_per");
					outNum(parseInt(parseInt(all - visits)/(all/100)), "n_visits_per");
				}else{
					outNum(0, "all");
					outNum(0, "f_visits");
					outNum(0, "n_visits");
					outNum(0, "f_visits_per");
					outNum(0, "n_visits_per");
				}
			}
		}

		$( document ).ready(function() {
			let ArrayFields=new Array();

			const ArrayTypes = [
				{
					id: "1",
					title: "Количество записей к ветеринару",
					chart_types:['donut'],
					eriod: "1",
					percentage: "",
					filter: "",
					graphic: "",
					specification: "1",
					reverse: "1",
					chart_title: "1",
					filters: "",
					fields: [
						{title:"Дата",name:"date",width:"20",total:"0",dayofweek:"1",chart_title:"1"},
						{color:"#e39632",pattern:"full",show:"1",title:"По направлению",name:"channel1",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#d44249",pattern:"full",show:"1",title:"С mos.ru",name:"channel2",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#128583",pattern:"full",show:"1",title:"По телефону",name:"channel3",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#6fb8e2",pattern:"full",show:"1",title:"Живая очередь",name:"channel4",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#bb6fd6",pattern:"full",show:"1",title:"Выезд на дом (НВП)",name:"channel10",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#4169E1",pattern:"full",show:"1",title:"Прививочный пункт",name:"channel11",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#FF00FF",pattern:"full",show:"1",title:"Выезд в приют",name:"channel13",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#4B0082",pattern:"full",show:"1",title:"Выезд на дом (mos.ru)",name:"channel14",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
						{color:"#4BBB82",pattern:"full",show:"1",title:"Обходы",name:"channel12",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"}
					]},
					{id: "2",title: "Соотношение записей через портал и приложение",chart_types:['donut'],period: "1",percentage: "",filter: "",graphic: "",specification: "1",reverse: "0",chart_title: "1", filters: "",fields: [{"title":"Место приёма","name":"name","width":"20","total":"0","chart_title":"1"},{"color":"#6FB8E2","pattern":"full","show":"1","title":"Через портал","name":"count_portal","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"},{"color":"#D44249","pattern":"full","show":"1","title":"Мобильное приложение","name":"count_mobile","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"}]},
					{id: "3",title: "Соотношение онлайн записей по месту приема",chart_types:['donut'],period: "1",percentage: "",filter: "",graphic: "",specification: "1",reverse: "0",chart_title: "1", filters: "",fields: [{"title":"Место приёма","name":"name","width":"20","total":"0","chart_title":"1"},{"color":"#6FB8E2","pattern":"full","show":"1","title":"Ветеринарная клиника","name":"count_clinic","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"},{"color":"#BB6FD6","pattern":"full","show":"1","title":"Вызовы на дом","name":"count_home","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"}]},
					{
						id: "4",
						title: "Динамика записей к ветеринару по каналам записи",
						chart_types:['line'],
						period: "1",
						percentage: "",
						filter: "",
						graphic: "",
						specification: "1",
						reverse: "1",
						chart_title: "1",
						filters: "",
						fields: [
							{title:"Дата",name:"date",width:"20",total:"0",dayofweek:"1",chart_title:"1"},
							{color:"#e39632",pattern:"full",show:"1",title:"По направлению",name:"channel1",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#d44249",pattern:"full",show:"1",title:"С mos.ru",name:"channel2",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#128583",pattern:"full",show:"1",title:"По телефону",name:"channel3",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#6fb8e2",pattern:"full",show:"1",title:"Живая очередь",name:"channel4",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#bb6fd6",pattern:"full",show:"1",title:"Выезд на дом (НВП)",name:"channel10",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#4169E1",pattern:"full",show:"1",title:"Прививочный пункт",name:"channel11",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#FF00FF",pattern:"full",show:"1",title:"Выезд в приют",name:"channel13",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#4B0082",pattern:"full",show:"1",title:"Выезд на дом (mos.ru)",name:"channel14",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"},
							{color:"#4BBB82",pattern:"full",show:"1",title:"Обходы",name:"channel12",width:"16",total:"1",chart:"1",percentage:"1",filter:"1"}
						]}
			]
			
			ArrayTypes.push({id: "5",title: "Топ 5 услуг, на которые записывались",chart_types:['bar-overlay'],period: "1",percentage: "",filter: "",graphic: "",specification: "1",reverse: "0",chart_title: "1", filters: "",fields: [{"title":"Услуга","name":"name","width":"20","total":"0","chart_title":"1"},{"color":"#E2E0D7","pattern":"full","show":"1","title":"Записи","name":"count_all","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"},{"color":"#6FB8E2","pattern":"full","show":"1","title":"Из них завершено","name":"count_fin","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"}]});
			ArrayTypes.push({id: "6",title: "Записи авторизованных и неавторизованных пользователей",chart_types:['donut'],period: "1",percentage: "",filter: "",graphic: "",specification: "1",reverse: "0",chart_title: "1", filters: "",fields: [{"title":"Место приёма","name":"name","width":"20","total":"0","chart_title":"1"},{"color":"#6FB8E2","pattern":"full","show":"1","title":"Авторизованные","name":"count_auth","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"},{"color":"#E39632","pattern":"full","show":"1","title":"Не авторизованные","name":"count_unauth","width":"16","total":"1","chart":"1","percentage":"1","filter":"1"}]});

			GetAnalyticsData();
		
			var options1 ={
				name: 'new_chart1',
				table: 0,
				chart: 1,
				height: 320,
				current_type: 1,
				chart_expand_collapse: 1,
				chart_legend_function: 'charts_control',
				chart_legend: 1,
				chart_legend_stroke_radius: 10,
				chart_legend_stroke_width: 8,
				chart_legend_text_max_width: 40,
				chart_legend_text_percents: 1,
				field_chart: 'JCMSReportChart1',
				form: 'form',
				type: 'type',
				url: "$base_url",
				types: ArrayTypes
			};
			new_chart1 = new JxChart(options1);
			
			var options2 ={ 
				table: 0,
				chart: 1,
				height: 320,
				current_type: 2,
				grid_line_width: 1,
				chart_expand_collapse: 1,
				chart_max_pie_items: 10,
				chart_legend_function: 'charts_control',
				chart_legend: 1,
				chart_legend_stroke_radius: 10,
				chart_legend_stroke_width: 8,
				chart_legend_text_max_width: 40,
				chart_legend_clickable_disabled: 1,
				chart_legend_text_percents: 1,
				field_chart: 'JCMSReportChart2',
				form: 'form',
				type: 'type',
				url: "$base_url?type=3",
				types: ArrayTypes
			};
			new_chart2 = new JxChart(options2);

			var options3 ={ 
				table: 0,
				chart: 1,
				height: 320,
				current_type: 3,
				grid_line_width: 1,
				chart_expand_collapse: 1,
				chart_max_pie_items: 10,
				chart_legend_function: 'charts_control',
				chart_legend: 1,
				chart_legend_stroke_radius: 10,
				chart_legend_stroke_width: 8,
				chart_legend_text_max_width: 40,
				chart_legend_clickable_disabled: 1,
				chart_legend_text_percents: 1,
				field_chart: 'JCMSReportChart3',
				field_table: 'JCMSReportTable3',
				form: 'form',
				type: 'type',
				url: "$base_url?type=2",
				types: ArrayTypes
			};
			new_chart3 = new JxChart(options3);
			
			var options4 ={
				name: 'new_chart4',
				table: 0,
				chart: 1,
				height: 320,
				current_type: 4,
				grid_line_width: 1,
				chart_expand_collapse: 1,
				chart_max_pie_items: 10,
				chart_legend_function: 'charts_control',
				chart_legend: 1,
				chart_legend_stroke_radius: 3,
				chart_legend_stroke_width: 3,
				field_chart: 'JCMSReportChart4',
				field_table: 'JCMSReportTable4',
				form: 'form',
				type: 'type',
				url: "$base_url",
				types: ArrayTypes
			};
			new_chart4 = new JxChart(options4);

			var options5 ={ 
				table: 0,
				chart: 1,
				height: 320,
				current_type: 5,
				grid_line_width: 1,
				grid_horizontal_text_max_width: 30,
				chart_expand_collapse: 1,
				chart_max_pie_items: 10,
				chart_legend_function: 'charts_control',
				chart_legend: 1,
				chart_legend_stroke_radius: 3,
				chart_legend_stroke_width: 3,
				chart_legend_clickable_disabled: 1,
				field_chart: 'JCMSReportChart5',
				field_table: 'JCMSReportTable5',
				form: 'form',
				type: 'type',
				url: "$base_url?type=1",
				types: ArrayTypes
			};
			new_chart5 = new JxChart(options5);

			var options6 ={ 
				table: 0,
				chart: 1,
				height: 320,
				current_type: 6,
				grid_line_width: 1,
				chart_expand_collapse: 1,
				chart_max_pie_items: 10,
				chart_legend_function: 'charts_control',
				chart_legend: 1,
				chart_legend_stroke_radius: 10,
				chart_legend_stroke_width: 8,
				chart_legend_text_max_width: 40,
				chart_legend_clickable_disabled: 1,
				chart_legend_text_percents: 1,
				field_chart: 'JCMSReportChart6',
				form: 'form',
				type: 'type',
				url: "$base_url?type=4",
				types: ArrayTypes
			};
			new_chart6 = new JxChart(options6);
		});
		</script>
		HTML;

		echo $html;
	}


	//Аналитика / Общий отчёт по услугам
	function viewFullServicesReport($config){
		?>
		<div class="row main_row search">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
				<div class="col-xl-8 col-lg-10">
					<form id="full_services_report_form">
						<div class="row search_sub">
							<div class="d-inline-flex row">
								<div class="label col-12">Период</div>
								<div class="input col-12">
									<input type="date" name="from" id="from" autocomplete="off" style="width: 150px;" value="<?php echo date("Y");?>-01-01" min="2010-01-01" max="2050-01-01">
									&nbsp;—&nbsp;
									<input type="date" name="to" id="to" autocomplete="off" style="width: 150px;" value="<?php echo date("Y-m-d");?>" min="2010-01-01" max="2050-01-01">
								</div>
							</div>
							<div class="d-inline-flex row">
								<div class="label col-12" style="width: 50px;">Округ</div>
									<div class="input col-12">
										<select class="area" name="areas" id="areas-selector" multiple="multiple" style="width:100%;"></select>
									</div>
								</div>
							<div class="d-inline-flex row">
								<div class="label col-12" style="width: 50px;">Организация</div>
									<div class="input col-12" >
										<select class="organization" name="organizations" id="organizations-selector" multiple="multiple"></select>
									</div>
								</div>
							<div class="d-inline-flex row">
								<div class="label col-12" style="width: 50px;">Специалист</div>
								<div class="input col-12">
									<select class="doc" name="specialists" id="specialists-selector" multiple="multiple"></select>
								</div>
							</div>
							<div class="d-inline-flex row">
								<div class="label col-12" style="width: 50px;">Услуга</div>
								<div class="input col-12">
									<select class="service" name="services" id="services-selector" multiple="multiple"></select>
								</div>
							</div>
							<div class="d-inline-flex row">
								<div class="label col-12" style="width: 50px;">Статус приёма</div>
								<div class="input col-12">
									<select class="status" name="statuses" id="statuses-selector" multiple="multiple"></select>
								</div>
							</div>
							<div class="d-inline-flex row">
								<div class="stat">
									<div>Всего услуг по приёмам в статусе завершено за весь период: <span id="services_all">0</span></div>
									<div>Всего услуг по приёмам в статусе завершено за выбранный период: <span id="services_period">0</span></div>
								</div>
							</div>
							<div class="d-inline-flex buttons">
								<button type="button" style="width: 180px;" class="btn" id="xls">Сохранить в XLS</button>
								<button style="margin-left: 15px; width: 136px;" class="btn" id="btn-ok">Применить</button>
                                <button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('ResetFullServicesReport');">Сбросить</button>
							</div>
							<input type="hidden" name="excel" value="0" id="excel-value">
						</div>
					</form>
				</div>
				<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			</div>
		</div>
		<div class="row main_row">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10 main_row">
				<div class="report" id="ListRecs">
					<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>
				</div>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<script src="/js/analytics/fullServicesReport.js?v=<?php echo time();?>"  type="module"></script>
		<?php
	}

	//Аналитика / Детальный отчёт по услугам
	function viewServicesDetailReport($config){
		?>
		<div class="row main_row search">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10">
				<form id="detail_services_report_form">
					<div class="row search_sub">
						<div class="d-inline-flex row">
							<div class="label col-12">Период</div>
							<div class="input col-12">
								<input type="date" name="from" id="from" autocomplete="off" style="width: 150px;" value="<?php echo date("Y");?>-01-01" min="2010-01-01" max="2050-01-01">
								&nbsp;—&nbsp;
								<input type="date" name="to" id="to" autocomplete="off" style="width: 150px;" value="<?php echo date("Y-m-d");?>" min="2010-01-01" max="2050-01-01">
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Округ</div>
							<div class="input col-12">
								<select class="area" name="areas" id="areas-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Организация</div>
							<div class="input col-12">
								<select class="organization" name="organizations" id="organizations-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Специалист</div>
							<div class="input col-12">
								<select class="doc" name="specialists" id="specialists-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Тип приёма</div>
							<div class="input col-12">
								<select class="shift" name="types" id="types-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Канал записи</div>
							<div class="input col-12">
								<select class="channel" name="channels" id="channels-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Вид животных</div>
							<div class="input col-12">
								<select class="species" name="species" id="species-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Услуга</div>
							<div class="input col-12">
								<select class="service" name="services" id="services-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex buttons">
							<button type="button" style="width: 180px;" class="btn" id="xls">Сохранить в XLS</button>
							<button style="margin-left: 15px; width: 136px;" class="btn" id="btn-ok">Применить</button>
                            <button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('ResetDetailReport');">Сбросить</button>
                        </div>
					</div>
					<input type="hidden" name="excel" value="0" id="excel-value">
				</form>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<div class="row main_row">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10 main_row">
				<div class="report" id="ListRecs">
					<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>
				</div>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<script src="/js/analytics/detailServicesReport.js?v=<?php echo time();?>"  type="module"></script>
		<?php
	}

	function viewServicesPriceReport($config){
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ShowServicesPriceReport(); return false;">';
		echo '<div class="row search_sub">';

		// echo '<div class="d-inline-flex row">';
		// echo '<div class="label col-12">Дата начала&nbsp;—&nbsp;Дата окончания</div>';
		// echo '<div class="input col-12">';
		// echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		// echo '&nbsp;—&nbsp;';
		// echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		// echo '</div>';
		// echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата начала</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата окончания</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Организация</div>';
		echo '<div class="input col-12">';
		show_organizations($config,2,1,1);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Специалист</div>';
		echo '<div class="input col-12">';
		show_specialists2($config,2,1);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Типы услуг</div>';
		echo '<div class="input col-12">';
		show_services_type($config,2,1,1);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Услуги</div>';
		echo '<div class="input col-12">';
		show_services_simple($config,2,1);
		echo '</div>';
		echo '</div>';
		//
		
		echo '<div class="d-inline-flex buttons">';
		echo '<button type="submit" style="margin-left: 15px; width: 180px;" value="1" name="xls" id="xls">Сохранить в XLS</button>';
		echo '<button type="submit" style="margin-left: 15px; width: 136px;" value="1" name="submit" id="submit">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('."'ResetServicesPriceReport'".');">Сбросить</button>';
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

		echo '<div class="report" id="ListRecs">';
		echo '<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';
	}
	
	//Аналитика / Отчёт об оказании ветеринарных услуг
	function viewServicesReport($config){
		?>
		<div class="row main_row search">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10">
				<form id="price_services_report_form">
					<div class="row search_sub">
						<div class="d-inline-flex row">
							<div class="label col-12">Период</div>
							<div class="input col-12">
								<input type="date" name="from" id="from" autocomplete="off" style="width: 150px;" value="<?php echo date("Y");?>-01-01" min="2010-01-01" max="2050-01-01">
								&nbsp;—&nbsp;
								<input type="date" name="to" id="to" autocomplete="off" style="width: 150px;" value="<?php echo date("Y-m-d");?>" min="2010-01-01" max="2050-01-01">
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Округ</div>
							<div class="input col-12">
								<select class="area" name="areas" id="areas-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Район</div>
							<div class="input col-12">
								<select class="district" name="districts" id="districts-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Организация</div>
							<div class="input col-12">
								<select class="organization" name="organizations" id="organizations-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex buttons">
							<button style="width: 180px;" class="btn" id="xls" type="button">Сохранить в XLS</button>
							<button style="margin-left: 15px; width: 136px;" class="btn" id="btn-ok">Применить</button>
                            <button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('ResetServicesReport');">Сбросить</button>
                        </div>
					</div>
					<input type="hidden" name="excel" value="0" id="excel-value">
				</form>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<div class="row main_row">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10 main_row">
				<div class="report" id="ListRecs">
					<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>
				</div>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<script src="/js/analytics/servicesReport.js?v=<?php echo time();?>"  type="module"></script>
		<?php
	}

	//Аналитика / Общий отчёт по приемам
	function viewVisitsReport($config){
		?>
		<div class="row main_row search">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10">
				<form id="visits_report_form">
					<div class="row search_sub">
						<div class="d-inline-flex row">
							<div class="label col-12">Период</div>
							<div class="input col-12">
								<input type="date" name="from" id="from" autocomplete="off" style="width: 150px;" value="<?php echo date("Y");?>-01-01" min="2010-01-01" max="2050-01-01">
								&nbsp;—&nbsp;
								<input type="date" name="to" id="to" autocomplete="off" style="width: 150px;" value="<?php echo date("Y-m-d");?>" min="2010-01-01" max="2050-01-01">
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Округ</div>
							<div class="input col-12">
								<select class="area" name="areas" id="areas-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Район</div>
							<div class="input col-12">
								<select class="district" name="districts" id="districts-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Организация</div>
							<div class="input col-12">
								<select class="organization" name="organizations" id="organizations-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex buttons">
							<button style="width: 180px;" class="btn" id="xls" type="button">Сохранить в XLS</button>
							<button type="submit" class="btn" style="margin-left: 15px; width: 136px;"id="btn-ok">Применить</button>
                            <button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('ResetVisitsReport');">Сбросить</button>
                            <input type="hidden" name="excel" value="0" id="excel-value">
						</div>
					</div>
				</form>
				<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<div class="row main_row">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10 main_row">
				<div class="report" id="ListRecs">
					<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>
				</div>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<script src="/js/analytics/visitsReport.js?v=<?php echo time();?>" type="module"></script>
		<?php
	}

	//Аналитика / Детальный отчёт по приемам
	function viewVisitsDetailReport($config){
		?>
		<div class="row main_row search">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10">
				<form id="visits_detail_report_form">
					<div class="row search_sub">
						<div class="d-inline-flex row">
							<div class="label col-12">Период</div>
							<div class="input col-12">
								<input type="date" name="from" id="from" autocomplete="off" style="width: 150px;" value="<?php echo date("Y");?>-01-01" min="2010-01-01" max="2050-01-01">
								&nbsp;—&nbsp;
								<input type="date" name="to" id="to" autocomplete="off" style="width: 150px;" value="<?php echo date("Y-m-d");?>" min="2010-01-01" max="2050-01-01">
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Округ</div>
							<div class="input col-12">
								<select class="area" name="areas" id="areas-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Район</div>
							<div class="input col-12">
								<select class="district" name="districts" id="districts-selector" multiple="multiple" style="width:100%;"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Организация</div>
							<div class="input col-12">
								<select class="organization" name="organizations" id="organizations-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex row">
							<div class="label col-12" style="width: 50px;">Вид животных</div>
							<div class="input col-12">
								<select class="species" name="species" id="species-selector" multiple="multiple"></select>
							</div>
						</div>
						<div class="d-inline-flex buttons">
							<button style="width: 180px;" class="btn" id="xls" type="button">Сохранить в XLS</button>
							<button type="submit" class="btn" style="margin-left: 15px; width: 136px;"id="btn-ok">Применить</button>
                            <button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('ResetDetailVisitsReport');">Сбросить</button>
                            <input type="hidden" name="excel" value="0" id="excel-value">
						</div>
					</div>
				</form>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<div class="row main_row">
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
			<div class="col-xl-8 col-lg-10 main_row">
				<div class="report" id="ListRecs">
					<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>
				</div>
			</div>
			<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
		</div>
		<script src="/js/analytics/detailVisitsReport.js?v=<?php echo time();?>" type="module"></script>
		<?php
	}

	function viewVaccinationReport($config){
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ShowVaccinationReport(); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Период</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '&nbsp;—&nbsp;';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Округ</div>';
		echo '<div class="input col-12">';
		show_areas($config,2);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Организация</div>';
		echo '<div class="input col-12">';
		show_organizations($config,2,1);
		echo '</div>';
		echo '</div>';
		
		//
		
		echo '<div class="d-inline-flex buttons">';
		echo '<button type="submit" style="width: 180px;" value="1" name="xls" id="xls">Сохранить в XLS</button>';
		echo '<button type="submit" style="margin-left: 15px; width: 136px;" value="1" name="submit" id="submit">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('."'ResetVaccinationReport'".');">Сбросить</button>';
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

		echo '<div class="report" id="ListRecs">';
		echo '<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';
	}

	function viewVaccinationShelterReport($config){
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ShowVaccinationShelterReport(this); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Период</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '&nbsp;—&nbsp;';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Округ</div>';
		echo '<div class="input col-12">';
		show_areas($config,2);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Приют</div>';
		echo '<div class="input col-12">';
		show_shelters($config,2,1);//Только приюты
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex buttons">';
		echo '<button type="submit" style="width: 180px;" value="1" name="xls" id="xls">Сохранить в XLS</button>';
		echo '<button type="submit" style="margin-left: 15px; width: 136px;" value="1" name="submit" id="submit">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('."'ResetVaccinationShelterReport'".');">Сбросить</button>';
        echo '</div>';

		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="row main_row" style="font-size: 12px; text-align: center;">';
		
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		
		echo '<div class="col-xl-8 col-lg-10 main_row">';

		echo '<div class="report" id="ListRecs">';
		echo '<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';
	}

	function viewFaq($config){
        echo '<div class="row main_row search">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '<div class="col-xl-8 col-lg-10">';

		$sql = "select fg.id as groupid, fg.code, fg.title from faq2_group fg order by id asc";

		$result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());

		$faqArray = array();

		while ($row = pg_fetch_assoc($result)) {

			$group = [
				'code' => $row['code'],
				'title' => $row['title'],
				'questions' => []
			];
			$faqArray[$row['groupid']] = $group;
		}

		pg_free_result($result);


		$sql = "select f2g.id, f.question, f.answer, fg.id as groupid, fg.code, fg.title from faq2 f left join faq2_to_group f2g on f2g.id_faq2 = f.id left join faq2_group fg on fg.id = f2g.id_faq2_group";

        $result = pg_query($sql) or die('Ошибка запроса: ' . pg_last_error());


        while ($row = pg_fetch_assoc($result)) {

        	if ($row['groupid'] == null)
        		continue;

            if (!array_key_exists($row['groupid'], $faqArray)) {
				$question = [
					'id' => $row['id'],
					'question' => $row['question'],
					'answer' => str_replace( '%EOL%', '<br>', $row['answer'])
				];

				$group = [
					'code' => $row['code'],
					'title' => $row['title'],
					'questions' => []
				];
				$group['questions'][$row['id']] = $question;
				$faqArray[$row['groupid']] = $group;
			} else {
				$question = [
					'id' => $row['id'],
					'question' => $row['question'],
					'answer' => str_replace( '%EOL%', '<br>', $row['answer'])
				];
				$faqArray[$row['groupid']]['questions'][$row['id']] = $question;
			}
        }
        pg_free_result($result);


		foreach ($faqArray as $key => $group) {

			echo "<div class='group mt-4'>";
			  echo "<div class='group__title'>";
				echo "<div class='group__title__text'>";
				  echo $group['title'];
				echo "</div>";
				echo "<div class='group__title__add' onclick='onShowCreateQuestion(" . $key . ")'>";
					echo "<div class='group__title__add__icon'></div>";
				  echo "<span class='group__title__add__title'>Добавить вопрос</span>";
				echo "</div>";
			  echo "</div>";

			  echo "<div class='group__items'>";

			  foreach ($group['questions'] as $key => $question) {
				  echo "<div class='question'>";
				  echo "<div class='question__title-block' onclick='toggleQuestion(" . $question['id'] . ")'>";
					echo "<div id='i_" . $question['id'] . "' class='question__title-block_icon question__title-block_icon__close-icon'></div>";
					echo "<div class='question__title-block__title'>{$question["question"]}</div>";
				  echo "</div>";

				  echo "<div id='q_" . $question['id'] . "' class='question_answer-block q-hide'>";
					echo "<p class='question_answer-block__text'>";
					  echo $question["answer"];
					echo "</p>";
				  echo "</div>";

				echo "</div>";
			  }

			  echo "</div>";
			echo "</div>";

		}

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
        echo '</div>';

		echo '<div id="overlay"><div class="cv-spinner"><span class="spinner"></span></div></div>';
    }

	function viewNotificationsReport($config){
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ShowNotificationsReport(this); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Период</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '&nbsp;—&nbsp;';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';
		///
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Вид животных</div>';
		echo '<div class="input col-12">';
		echo '<select class="species" name="species" multiple>';// multiple="multiple"
		$species_array = array();
		array_push($species_array, array('id' => '25','title' => 'Собаки'));
		array_push($species_array, array('id' => '9','title' => 'Кошки'));
		array_push($species_array, array('id' => 'OTHER','title' => 'Иные животные'));
		for ($i = 0; $i < count($species_array); $i++) {
            echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
	    }
		echo '</select>';
		echo '</div>';
		echo '</div>';
		///

		///
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Отправитель</div>';
		echo '<div class="input col-12">';
		echo '<select class="sender" name="sender">';// multiple="multiple"
		$species_array = array();
		array_push($species_array, array('id' => '','title' => 'Система'));
		// array_push($species_array, array('id' => 'SYSTEM','title' => 'Система'));
		for ($i = 0; $i < count($species_array); $i++) {
            echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
	    }
		echo '</select>';
		echo '</div>';
		echo '</div>';
		///

		///
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Владелец</div>';
		echo '<div class="input col-12">';

		echo '<textarea id="owners" name="owners" rows="5" class="JxTag"></textarea>';
		
		echo '</div>';
		echo '</div>';
		///		

		echo '<div class="d-inline-flex buttons">';
		echo '<button type="submit" style="width: 180px;" value="1" name="xls" id="xls">Сохранить в XLS</button>';
		echo '<button type="submit" style="margin-left: 15px; width: 136px;" value="1" name="submit" id="submit">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetNotificationReport('."'ResetNotificationReport'".');">Сбросить</button>';
        echo '</div>';

		echo '</div>';

		echo '<input type="hidden" name="action" value="visits_report">';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="row main_row">';
		
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		
		echo '<div class="col-xl-8 col-lg-10 main_row">';

		echo '<div class="report" id="ListRecs">';
		echo '<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';

		$html = <<<HTML
		<script type="text/javascript">

		$(document).ready(function () {
			var options ={
			name: 'owners',
			mode: 'normal',
			new_tags: 0,
			max_tags: 999,
			request_min: 5,
			request_title: 'name',
			response_id: 'rec_id',
			response_title: 'rec_name',
			width: '100%',
			height: 320,
			max_tags_win: 10,
			url: "?action=owners&mode=xml"
		};
		tags_input = new JxTag(options);
		});

		</script>
		HTML;

		echo $html;
	}

	function viewAnimalDiseaseReport($config){
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ShowAnimalDiseaseReport(); return false;">';
		echo '<div class="row search_sub">';

		// echo '<div class="d-inline-flex row">';
		// echo '<div class="label col-12">Период</div>';
		// echo '<div class="input col-12">';
		// echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		// echo '&nbsp;—&nbsp;';
		// echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		// echo '</div>';
		// echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата начала</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата окончания</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		///

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Организация</div>';
		echo '<div class="input col-12">';
		show_organizations($config,2,1,1);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Специалист</div>';
		echo '<div class="input col-12">';
		show_specialists2($config,2,1);
		echo '</div>';
		echo '</div>';
		///

		///
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Вид животного</div>';
		echo '<div class="input col-12">';
		echo '<select class="species" name="species" multiple="multiple">';// multiple="multiple"
		$species_array = array();
		array_push($species_array, array('id' => 'DOG','title' => 'Собаки'));
		array_push($species_array, array('id' => 'CAT','title' => 'Кошки'));
		array_push($species_array, array('id' => 'OTHER','title' => 'Иные животные'));
		for ($i = 0; $i < count($species_array); $i++) {
            echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
	    }
		echo '</select>';
		echo '</div>';
		echo '</div>';
		///

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Наименование болезни</div>';
		echo '<div class="input col-12">';
		show_diseases($config,2,1);
		echo '</div>';
		echo '<div class="input col-12">';
		echo '<label class="checkbox_container">Заразные';
		echo '<input type="checkbox" name="contagious" id="contagious" value="1">';
		echo '<span class="checkbox_checkmark"></span>';
		echo '</label>';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex buttons">';
		echo '<button type="submit" style="margin-left: 15px; width: 180px;" value="1" name="xls" id="xls">Сохранить в XLS</button>';
		echo '<button type="submit" style="margin-left: 15px; width: 136px;" value="1" name="submit" id="submit">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 180px;" value="1" onclick="ResetReport('."'ResetAnimalDiseaseReport'".');">Сбросить</button>';
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

		echo '<div class="report" id="ListRecs">';
		echo '<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';

	}

	function viewVeterinarySpecialistsReport($config){
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		// echo '<form id="form" onsubmit="ShowVeterinarySpecialistsReport(this); return false;">';
		echo '<form id="form" onsubmit="ShowVeterinarySpecialistsReport(); return false;">';
		echo '<div class="row search_sub">';

		// echo '<div class="d-inline-flex row">';
		// echo '<div class="label col-12">Период</div>';
		// echo '<div class="input col-12">';
		// echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		// //echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="2023-01-01" min="2010-01-01" max="2050-01-01">';
		// echo '&nbsp;—&nbsp;';
		// echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		// echo '</div>';
		// echo '</div>';
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата начала</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="'.date("Y").'-01-01" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата окончания</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';
		///
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Организация</div>';
		echo '<div class="input col-12">';
		show_organizations($config,2,1,1);
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12" style="width: 50px;">Специалист</div>';
		echo '<div class="input col-12">';
		show_specialists2($config,2,1);
		echo '</div>';
		echo '</div>';
		///		

		echo '<div class="d-inline-flex buttons">';
		echo '<button type="submit" style="margin-left: 15px; width: 180px;" value="1" name="xls" id="xls">Сохранить в XLS</button>';
		echo '<button type="submit" style="margin-left: 15px; width: 136px;" value="1" name="submit" id="submit">Применить</button>';
        echo '<button type="reset" style="margin-left: 15px; width: 136px;" class="btn" value="1" onclick="ResetReport('."'ResetVeterinarySpecialistsReport'".');">Сбросить</button>';
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

		echo '<div class="report" id="ListRecs">';
		echo '<div class="p-3">Введите параметры отчёта и нажмите кнопку Применить.</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';

		echo $html;
	}

	if($action == 'auth'){
		authUser($configuration); 
	}else if($action == 'exit'){
		exitUser($configuration);
	}else if($action == 'reports' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_reports_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'owners' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_owners_analytics_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'services_price_report' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_services_price_report_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'vaccination_report' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_vaccination_report_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'vaccination_shelter_report' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_vaccination_shelter_report_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'veterinary_specialists_report' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_veterinary_specialists_report_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'animal_disease_report' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_animal_disease_report_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notification_read'){
    	if(vallidateToken($configuration)){read_notification_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notifications'){
    	if(vallidateToken($configuration)){show_notifications_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notifications_report' && $mode == 'xml'){
		if(vallidateToken($configuration)){analytics_show_notifications_report_xml($configuration);}else{invalidToken($configuration);}
	} else if ($action == 'add_question') {
		if(vallidateToken($configuration)){addQuestion($configuration);}else{invalidToken($configuration);}
	} else if ($action == 'create_group') {
		if(vallidateToken($configuration)){createGroup($configuration);}else{invalidToken($configuration);}
	} else if ($action == 'recovery_password') {
		header_site(1, $configuration);
		viewRecoveryPassword('analytics');
		footer_site();
	}else if($action == 'recovery'){
		recoveryPasswordUser($configuration);
	}else{
		if($_COOKIE['token']){
			if(vallidateToken($configuration)){
				header_site(0,$configuration, 'Аналитика ВетАС','analytics');
				sub_header_site($configuration);
				menu_site($configuration, 'analytics', $action);

				//пользователь авторизован//
				informings_site($configuration);

				if($action == 'full_services_report'){
					viewFullServicesReport($configuration);
				}else if($action == 'services_detail_report'){
					viewServicesDetailReport($configuration);
				}else if($action == 'services_report'){
					viewServicesReport($configuration);
				}else if($action == 'visits_report'){
					viewVisitsReport($configuration);
				}else if($action == 'visits_detail_report'){
					viewVisitsDetailReport($configuration);
				}else if($action == 'vaccination_report'){
					viewVaccinationReport($configuration);
				}else if($action == 'vaccination_shelter_report'){
					viewVaccinationShelterReport($configuration);
				}else if($action == 'notifications_report'){
					viewNotificationsReport($configuration);
				}else if($action == 'services_price_report'){
					viewServicesPriceReport($configuration);
				}else if($action == 'animal_disease_report'){
					viewAnimalDiseaseReport($configuration);
				}else if($action == 'veterinary_specialists_report'){
					viewVeterinarySpecialistsReport($configuration);
                }else if($action == 'faq'){
                    viewFaq($configuration);
				}else{
					viewAnalytics($configuration);
				}
								
				sub_footer_site();
				footer_site();
			}else{
				header_site(1,$configuration);
				viewAuthUser('analytics');
				footer_site();
			}
		}else{
			header_site(1,$configuration);
			viewAuthUser('analytics');
			footer_site();
		}
	}
	
	if (function_exists('pg_connect')) {
		pg_close($dbconn);
	}
?>
