<?php

	$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';
		
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';

	if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться с БД: ' . pg_last_error());}

	header('Content-Type: text/html');

	if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
	
	function show_services_type_($config){
		$params=get_params();
	
		echo '<div class="label">Тип:&nbsp;</div>';
		echo '<div class="input">';
		echo '<select class="type" name="type_" id="type_">';
		echo '<option value="">Все</option>';
		echo '<option value="notmosru">обычные</option>';
		echo '<option value="mosru">mos.ru</option>';
		echo '</select>';
		echo '</div>';
	}

	function show_prices($config){
		$params=get_params();
	
		$query='SELECT * FROM pricelists ';
	
		echo '<div class="label" style="min-width: 85px;">Прайслист:&nbsp;</div>';
		echo '<div class="input">';
		echo '<select class="pricelist" name="pricelist" id="pricelist">';
		echo '<option value="">Все</option>';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<option value="'.$row['id'].'">Прайслист № '.$row['id'].'</option>';
		}
	
		echo '</select>';
		echo '</div>';
	
		pg_free_result($result);
	}

	function show_services_service($config){
		$service=$_GET["service"];
	
		$query='SELECT * FROM gov_services WHERE (deleted=false) OR (deleted=true AND type=\'mosru\') ';
	
		echo '<div class="label">Услуга:&nbsp;</div>';
		echo '<div class="input">';
		echo '<select class="service" name="service" id="service">';
		echo '<option value="">Все</option>';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<option ';
			if($service == $row['id']){
				echo 'selected ';
			}
			echo ' value="'.$row['id'].'">'.$row['name'].' ';
			if($row['cod']){
				echo '['.$row['cod'].']';
			}else{
				echo '[mos.ru]';
			}
			echo '</option>';
		}
	
		echo '</select>';
		echo '</div>';
	
		pg_free_result($result);
	}
	
	function show_services_pricelist_xml($config){
		$page=intval($_GET["page"]);//страница
		if($page == ''){$page=1;}
		$recs_on_page=10;//кол-во на странице

		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if(isset($_POST["pricelist"])){$pricelist=$_POST["pricelist"];}else{$pricelist=$_GET["pricelist"];}
		if(isset($_POST["type"])){$type=$_POST["type"];}else{$type=$_GET["type"];}
		if(isset($_POST["type_"])){$type_=$_POST["type_"];}else{$type_=$_GET["type_"];}
		if(isset($_POST["cod"])){$cod=$_POST["cod"];}else{$cod=$_GET["cod"];}
		
		$q='SELECT gov_services.*,service_types.name AS service_types_name,';
		$q.='specializations.name AS specializations_name,specializations.description AS specializations_description,';
		$q.='service_measures.name AS measures_name,service_measures.description AS measures_description,service_measures.count_flag AS measures_count_flag FROM gov_services ';
		$q.='LEFT JOIN service_types ON gov_services.id_service_type=service_types.id ';
		$q.='LEFT JOIN service_measures ON gov_services.id_service_measure=service_measures.id ';
		$q.='LEFT JOIN specializations ON gov_services.id_specialization=specializations.id ';
	
		if($pricelist || $type || $cod || $type_){
			$q.='WHERE ';
			$q_s='';
			if($pricelist){
				$query_d='';
				$query_d.='id_pricelist='.$pricelist.' ';
				if($q_s){$q_s.=' AND ';}
				$q_s.='('.$query_d.')'."\n";
			}
			if($type){
				$query_d='';
				$query_d.='service_types.id='.$type.' ';
				if($q_s){$q_s.=' AND ';}
				$q_s.='('.$query_d.')'."\n";
			}
			if($cod){
				$query_d='';
				$query_d.='gov_services.cod=\''.$cod.'\' ';
				if($q_s){$q_s.=' AND ';}
				$q_s.='('.$query_d.')'."\n";
			}
			if($type_){
				$query_d='';
				if($type_ == 'mosru'){
					$query_d.='gov_services.type=\'mosru\' ';
				}else{
					$query_d.='gov_services.type IS NULL ';
				}
				if($q_s){$q_s.=' AND ';}
				$q_s.='('.$query_d.')'."\n";
			}
			$q.=$q_s;
		}
		$q.='ORDER BY gov_services.name ';

		//Пагинация
		echo '<from>'.intval(($page-1)*$recs_on_page+1).'</from>';
		echo '<to>'.intval(($page)*$recs_on_page).'</to>';

		echo '<current>'.$page.'</current>';

		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		$recs_counter = pg_num_rows($result);//количество записей
		pg_free_result($result);
		echo '<counter>'.$recs_counter.'</counter>';
		echo '<pages>';
		if($recs_counter > 0){
			$pages_number = intval($recs_counter / $recs_on_page);
			if($pages_number > 1){
				for($i = 1; $i < $pages_number+1; $i++){
					echo '<page>'.$i.'</page>';
				}
			}
			echo '<last>'.$pages_number.'</last>';
		}
		echo '</pages>';
		//Пагинация

		$q.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
		//echo $q;
		echo '<recs>';
		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_id_pricelist>'.$row['id_pricelist'].'</rec_id_pricelist>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_alternative_name>'.$row['alternative_name'].'</rec_alternative_name>';
			echo '<rec_briefname>'.$row['briefname'].'</rec_briefname>';
			###
			echo '<rec_parameters>';
			$q_='SELECT description_types.name AS name, description_types.tech_name AS tech_name, services_description_types.required AS required, description_types.entity_type AS entity_type FROM services_description_types ';
			$q_.='LEFT JOIN description_types ON services_description_types.id_description_type=description_types.id ';
			$q_.='WHERE id_service='.$row['id'].' ';
			$q_.='ORDER BY description_types.name';
			$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
			while ($row_ = pg_fetch_assoc($result_)) {
				echo '<parameter>';
				echo '<parameter_required>';
				if($row_['required'] == 't'){
					echo '1';
				}else{
					echo '0';
				}
				echo '</parameter_required>';
				echo '<parameter_name>'.$row_['name'].'</parameter_name>';
				echo '<parameter_type>'.$row_['entity_type'].'</parameter_type>';
				echo '<parameter_tech_name>'.$row_['tech_name'].'</parameter_tech_name>';
				echo '</parameter>';
			}
			pg_free_result($result_);
			echo '</rec_parameters>';
			###
	
			echo '<rec_price>'.$row['price'].'</rec_price>';
			echo '<rec_duration>'.$row['duration'].'</rec_duration>';
			echo '<rec_cooldown>'.$row['cooldown'].'</rec_cooldown>';
	
			echo '<rec_com_class_journal>'.$row['com_class_journal'].'</rec_com_class_journal>';
			echo '<rec_for_broods>'.$row['for_broods'].'</rec_for_broods>';
			echo '<rec_for_multiple>'.$row['for_multiple'].'</rec_for_multiple>';
	
			echo '<rec_service_types_name>'.$row['service_types_name'].'</rec_service_types_name>';
			echo '<rec_service_types_id>'.$row['id_service_type'].'</rec_service_types_id>';
	
			echo '<rec_specializations_name>'.$row['specializations_name'].'</rec_specializations_name>';
			echo '<rec_specializations_description>'.$row['specializations_description'].'</rec_specializations_description>';
			echo '<rec_id_specialization>'.$row['id_specialization'].'</rec_id_specialization>';
	
			//сколько врачей могут оказывать услугу
			$query_с='SELECT COUNT(*) FROM services_specialists WHERE id_service=\''.$row['id'].'\' ';
			$result_с = pg_query($query_с) or die('Ошибка запроса: ' . pg_last_error());
			echo '<rec_counter_docs>'.pg_fetch_result($result_с, 0).'</rec_counter_docs>';
			pg_free_result($result_с);
			//сколько врачей могут оказывать услугу
			
			echo '<rec_measures_name>'.$row['measures_name'].'</rec_measures_name>';
			echo '<rec_measures_description>'.$row['measures_description'].'</rec_measures_description>';
			echo '<rec_id_service_measure>'.$row['id_service_measure'].'</rec_id_service_measure>';
			
			echo '<rec_cod>'.$row['cod'].'</rec_cod>';
			echo '<rec_type>'.$row['type'].'</rec_type>';
			echo '<rec_id_service_goal>'.$row['id_service_goal'].'</rec_id_service_goal>';
			
			echo '<rec_at_home>';
			if($row['at_home'] == 't'){echo '1';}else{echo '0';}
			echo '</rec_at_home>';
	
			echo '<rec_at_clinic>';
			if($row['at_clinic'] == 't'){echo '1';}else{echo '0';}
			echo '</rec_at_clinic>';
	
			echo '<rec_deleted>';
			if($row['deleted'] == 't'){echo '1';}else{echo '0';}
			echo '</rec_deleted>';
	
			echo '<rec_once_per_day>';
			if($row['once_per_day'] == 't'){echo '1';}else{echo '0';}
			echo '</rec_once_per_day>';
	
			###
			echo '<rec_reports>';
			$q_='SELECT reports.name AS name, reports.id AS id FROM gov_services_reports ';
			$q_.='LEFT JOIN reports ON gov_services_reports.id_report=reports.id ';
			$q_.='WHERE gov_services_reports.id_service='.$row['id'].' ';
			$q_.='ORDER BY reports.name';
			$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
			$count = pg_num_rows($result_);
			if($count > 0){
				
				while ($row_ = pg_fetch_assoc($result_)) {
					echo '<report>';
					echo '<report_name>'.$row_['name'].'</report_name>';
					echo '<report_id>'.$row_['id'].'</report_id>';
					
					$q__='SELECT params.name, params.tech_name, gov_services_params.id_param, gov_services_params.req_in, gov_services_params.req_out FROM gov_services_params ';
					$q__.='LEFT JOIN params ON gov_services_params.id_param=params.id ';
					$q__.='INNER JOIN reports_params ON reports_params.id_param=params.id AND reports_params.id_report='.$row_['id'].'';
					$q__.='WHERE gov_services_params.id_service='.$row['id'].' ';
					$result__ = pg_query($q__) or die('Ошибка запроса: ' . pg_last_error());
					$count__ = pg_num_rows($result__);
					if($count__ > 0){
						echo '<report_parameters>';
						while ($row__ = pg_fetch_assoc($result__)) {
							echo '<parameter>';
	
							echo '<parameter_name>'.$row__['name'].'</parameter_name>';
							echo '<parameter_tech_name>'.$row__['tech_name'].'</parameter_tech_name>';
							echo '<parameter_id_param>'.$row__['id_param'].'</parameter_id_param>';
	
							echo '<parameter_req_in>';
							if($row__['req_in'] == 't'){echo '1';}else{echo '0';}
							echo '</parameter_req_in>';
	
							echo '<parameter_req_out>';
							if($row__['req_out'] == 't'){echo '1';}else{echo '0';}
							echo '</parameter_req_out>';
							
							echo '</parameter>';
						}
						echo '</report_parameters>';
					}
					pg_free_result($result__);
					echo '</report>';
				}
			}
			echo '</rec_reports>';
			pg_free_result($result_);
			###
	
			echo '</rec>';
		}
		echo '</recs>';
		pg_free_result($result);

		

		echo '</xml>';
	}

	function viewServices($config){
		$user_fullname='';
		if($_COOKIE['login']){
			$query='SELECT fullname FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				$user_fullname=$row['fullname'];
			}
			pg_free_result($result);
		}
	
		// echo '<div class="row main_row header" style="min-height: auto !important;">';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '<div class="col-xl-8 col-lg-10">';
		// echo '<div class="header_sub row">';
		// echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12"></div>';
		// echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12"><h1 style="text-align: center;">Редактор услуг</h1></div>';
		// echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-center">'.$user_fullname.'</div>';
		// echo '<div class="d-table-cell align-middle text-center controls">';
		// echo '<a href="index.php?action=exit"><div class="exit_button" title="Выход"></div></a>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '</div>';
	
		echo '<div class="row main_row search">';
	
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ListShowServices(); return false;">';
		echo '<div class="row search_sub">';
	
		echo '<div class="d-inline-flex">';show_prices($config);echo '</div>';
		echo '<div class="d-inline-flex">';show_services_type($config);echo '</div>';
		echo '<div class="d-inline-flex">';show_services_cod($config);echo '</div>';
		echo '<div class="d-inline-flex">';show_services_type_($config);echo '</div>';
		echo '<div class="d-inline-flex buttons"><button type="reset" onclick="resetForm();" style="position: absolute; right: 125px; width: 100px;">Сброс</button> <button type="submit" style="position: absolute; right: 20px; width: 100px;">Поиск</button></div>';
	
		echo '</div>';
	
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	
		echo '<div class="row main_row" style="margin-bottom: 14px;">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		// echo '<a href="index.php?action=upload_price"><button type="submit" class="mini" style="width: 175px; float: left; margin-right: 10px;">Загрузить прайс-лист</button></a>';
		// echo '<a href="index.php?action=units_list"><button type="submit" class="mini" style="width: 175px; float: left;">Единицы измерения</button></a>';
		// echo '<a href="index.php?action=edit_service"><button type="submit" style="width: 200px; float: right;">Добавить услугу</button></a>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	
		echo '<div class="row main_row">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 results">';
		echo '<div id="ListRecs" class="loading"></div>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	
		echo '<script>';
		echo '$(document).ready(function () {';
		echo 'ListShowServices();';
		echo '});';
		echo '</script>';
	}

	function viewServicesSpecialists($config){
		$user_fullname='';
		if($_COOKIE['login']){
			$query='SELECT fullname FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				$user_fullname=$row['fullname'];
			}
			pg_free_result($result);
		}
	
		// echo '<div class="row main_row header" style="min-height: auto !important;">';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '<div class="col-xl-8 col-lg-10">';
		// echo '<div class="header_sub row">';
		// echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-left"><a href="index.php" style="float: none;">К списку услуг</a></div>';
		// echo '</div>';
		// echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12"><h1 style="text-align: center;">Редактор услуг</h1><h2 style="text-align: center;">Список связей врачей и услуг</h2></div>';
		// echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-center">'.$user_fullname.'</div>';
		// echo '<div class="d-table-cell align-middle text-center controls">';
		// echo '<a href="index.php?action=exit"><div class="exit_button" title="Выход"></div></a>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '</div>';
	
		echo '<div class="row main_row search">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form" onsubmit="ListShowServicesSpecialists(); return false;">';
		echo '<div class="row search_sub">';
		echo '<div class="d-inline-flex">';show_services_service($config);echo '</div>';
		echo '<div class="d-inline-flex">';show_organizations($config,1,0);echo '</div>';
		#echo '<div class="d-inline-flex">';show_services_specialist($config);echo '</div>';
		echo '<div class="d-inline-flex buttons"><button type="reset" onclick="resetForm();" style="position: absolute; right: 125px; width: 100px;">Сброс</button> <button type="submit" style="position: absolute; right: 20px; width: 100px;">Поиск</button></div>';
		echo '<div class="w-100 info" id="ListInfo"></div>';
		echo '</div>';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	
		echo '<div class="row main_row">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 results">';
		echo '<div id="ListRecs" class="loading"></div>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	
	
		echo '<div class="row main_row controllers" id="controllers" style="display: none;">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<div class="row controllers_sub">';
		echo '<div class="d-inline-flex" id="info" style="padding-right: 15px;"></div>';
		echo '<div class="d-inline-flex buttons"><button type="submit" class="save_services_specialists" onclick="saveServicesSpecialist();" style="right: 20px; width: 130px;">Сохранить</button></div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	
	
		echo '<script>';
		echo '$(document).ready(function () {';
		echo 'ListShowServicesSpecialists();';
		echo '});';
		echo '</script>';
	}
	
	function viewServicesEditService($configuration){
		if(isset($_POST["id"])){$id=$_POST["id"];}else{$id=$_GET["id"];}
		$message=$_GET["message"];
		
		$service='';
		if($id){
			$query='SELECT * FROM gov_services WHERE ';
			$query.='id=\''.string_formating_for_sql($id).'\' ';
			$query.='LIMIT 1';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$service = pg_fetch_assoc($result);
			pg_free_result($result);
		}

		header_site(0,$configuration);

		$can = userCan($configuration, 'sysAdminGos');

		// echo '<div class="row main_row header" style="min-height: auto !important;">';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '<div class="col-xl-8 col-lg-10">';
		// echo '<div class="header_sub row">';
		// echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-left"><a href="index.php" style="float: none;">К списку услуг</a></div>';
		// echo '</div>';
		// echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12"><h1 style="text-align: center;">Редактор услуг</h1>';
		
		// echo '<h2 style="text-align: center;">';
		// if($id){echo 'Изменение услуги';}else{echo 'Добавление услуги';}
		// echo '</h2>';
		
		// echo '</div>';
		// echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-center">'.$user_fullname.'</div>';
		// echo '<div class="d-table-cell align-middle text-center controls">';
		// echo '<a href="index.php?action=exit"><div class="exit_button" title="Выход"></div></a>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '</div>';

		if($message == 'successfully'){
			echo '<div class="row main_row" style="margin-top: 15px;">';
			echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			echo '<div class="col-xl-8 col-lg-10 results">';
			echo '<div id="ListRecs" class="message">Услуга успешно сохранена.</div>';
			echo '</div>';
			echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			echo '</div>';
		}

		if($message == 'fields_not_filled'){
			echo '<div class="row main_row" style="margin-top: 15px;">';
			echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			echo '<div class="col-xl-8 col-lg-10 results">';
			echo '<div id="ListRecs" class="error">Не заполнены необходимые поля.</div>';
			echo '</div>';
			echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			echo '</div>';
		}

		echo '<div class="row main_row">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';

		if(!$can){
			echo '<div  style="margin-top: 15px;" class="tabs_text p-3">Услуга доступна только в режиме просмотра.</div>';
		}

		echo '<form method="POST">';
		echo '<input type="hidden" name="id" value="'.$id.'">';
		echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Прайс лист</h2>';
		echo '<select name="id_pricelist">';
		$query='SELECT id FROM pricelists ';
		$query.='ORDER BY id';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if($service['id_pricelist'] === $row['id']){
				echo '<option value="'.$row['id'].'" selected>'.$row['id'].'</option>';
			}else{
				echo '<option value="'.$row['id'].'">'.$row['id'].'</option>';
			}
		}
		pg_free_result($result);
		echo '</select>';

		echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Услуга</h2>';
		
		echo '<p><input type="checkbox" ';
		if($service['deleted'] == 't'){
			echo 'checked ';
		}
		echo 'name="deleted" id="deleted"> <label for="deleted">Удалена</label></p>';

		echo '<p class="required">Название: <input type="text" name="name" value="'.$service['name'].'" required></p>';
		echo '<p>Альтернативное название: <input type="text" name="alternative_name" value="'.$service['alternative_name'].'"></p>';

		###
		echo '<p class="required">Наименование услуги mos.ru: ';
		echo '<select name="id_service_mosru">';
		$query='SELECT id, name FROM gov_services WHERE type=\'mosru\'';
		$query.='ORDER BY name';
		echo '<option value="">Нет</option>';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if($service['id_service_mosru'] == $row['id']){
				echo '<option value="'.$row['id'].'" selected>'.$row['name'].' ['.$row['id'].']</option>';
			}else{
				echo '<option value="'.$row['id'].'">'.$row['name'].' ['.$row['id'].']</option>';
			}
		}
		pg_free_result($result);
		echo '</select>';
		echo '</p>';
		###

		echo '<p>Тип: <input type="text" name="type" value="'.$service['type'].'"></p>';
		echo '<p>Сокращенное наименование услуги: <input type="text" name="briefname" value="'.$service['briefname'].'"></p>';
		echo '<p>Код услуги: <input type="text" name="cod" id="cod" value="'.$service['cod'].'"></p>';

		echo '<p>Цена: <input type="number" step="any" name="price" value="'.$service['price'].'"></p>';
		
		###
		echo '<p class="required">Тип услуги: ';
		echo '<select name="id_service_type">';
		$query='SELECT id, name FROM service_types ';
		$query.='ORDER BY name';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if($service['id_service_type'] == $row['id']){
				echo '<option value="'.$row['id'].'" selected>'.$row['name'].' ['.$row['id'].']</option>';
			}else{
				echo '<option value="'.$row['id'].'">'.$row['name'].' ['.$row['id'].']</option>';
			}
		}
		pg_free_result($result);
		echo '</select>';
		echo '</p>';
		###

		###
		echo '<p class="required">Специализация услуги: ';
		echo '<select name="id_specialization">';
		$query='SELECT id, name FROM specializations ';
		$query.='ORDER BY name';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		echo '<option value="">Отсутвует</option>';
		while ($row = pg_fetch_assoc($result)) {
			if($service['id_specialization'] == $row['id']){
				echo '<option value="'.$row['id'].'" selected>'.$row['name'].' ['.$row['id'].']</option>';
			}else{
				echo '<option value="'.$row['id'].'">'.$row['name'].' ['.$row['id'].']</option>';
			}
		}
		pg_free_result($result);
		echo '</select>';
		echo '</p>';
		###

		echo '<p class="required">Длительность услуги: <input type="number" name="duration" min="10" step="10" value="'.$service['duration'].'" required></p>';
		echo '<p class="required">Длительность перерыва после услуги: <input type="number" min="0" step="10" name="cooldown" value="'.$service['cooldown'].'" required></p>';

		###
		echo '<p>Единица измерения услуги: ';
		echo '<select name="id_service_measure">';
		$query='SELECT id, name FROM service_measures ';
		$query.='ORDER BY name';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			if($service['id_service_measure'] == $row['id']){
				echo '<option value="'.$row['id'].'" selected>'.$row['name'].' ['.$row['id'].']</option>';
			}else{
				echo '<option value="'.$row['id'].'">'.$row['name'].' ['.$row['id'].']</option>';
			}
		}
		pg_free_result($result);
		echo '</select>';
		echo '</p>';
		###

		echo '<p><input type="checkbox" ';
		if($service['at_home'] == 't'){
			echo 'checked ';
		}
		echo 'name="at_home" id="at_home"> <label for="at_home">Услуга оказывается на дому</label></p>';
		echo '<p><input type="checkbox" ';
		if($service['at_clinic'] == 't'){echo 'checked ';}
		echo 'name="at_clinic" id="at_clinic"> <label for="at_clinic">Услуга оказывается в клинике</label></p>';
		echo '<p><input type="checkbox" ';
		if($service['once_per_day'] == 't'){echo 'checked ';}
		echo 'name="once_per_day" id="once_per_day"> <label for="once_per_day">Услуга может оказываться раз в день</label></p>';
		
		$com_class_journal = ["NULL","medicalAssistance","additional"];
		$com_class_journal_ = ["Отсутствует","Лечебная помощь","Дополнительные исследования"];

		echo '<p>Классификация услуги для журналов: ';
		echo '<select name="com_class_journal">';
		for($i=0;$i<count($com_class_journal);$i++){
			if($service['com_class_journal'] == $com_class_journal[$i]){
				echo '<option value="'.$com_class_journal[$i].'" selected>'.$com_class_journal_[$i].'</option>';
			}else{
				echo '<option value="'.$com_class_journal[$i].'">'.$com_class_journal_[$i].'</option>';
			}
		}
		echo '</select>';
		echo '</p>';

		$for_broods = ["NULL","HEAD","ALL"];
		$for_broods_ = ["Нет","Голова","Все"];
		echo '<p>Услуга для выводков: ';
		echo '<select name="for_broods">';
		for($i=0;$i<count($for_broods);$i++){
			if($service['for_broods'] == $for_broods[$i]){
				echo '<option value="'.$for_broods[$i].'" selected>'.$for_broods_[$i].'</option>';
			}else{
				echo '<option value="'.$for_broods[$i].'">'.$for_broods_[$i].'</option>';
			}
		}
		echo '</select>';
		echo '</p>';

		$for_multiple = ["NULL","HEAD","ALL"];
		$for_multiple_ = ["Нет","Голова","Все"];
		echo '<p>Услуга для множественных приемов: ';
		echo '<select name="for_multiple">';
		for($i=0;$i<count($for_multiple);$i++){
			if($service['for_multiple'] == $for_multiple[$i]){
				echo '<option value="'.$for_multiple[$i].'" selected>'.$for_multiple_[$i].'</option>';
			}else{
				echo '<option value="'.$for_multiple[$i].'">'.$for_multiple_[$i].'</option>';
			}
		}
		echo '</select>';
		echo '</p>';

		###ПАРАМЕТРЫ
		echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Параметры (приём)</h2>';
		$service_parameters_array = array();
		if($id){
			$q_='SELECT * FROM services_description_types ';
			$q_.='WHERE id_service='.$id.' ';
			$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
			while ($row_ = pg_fetch_assoc($result_)) {
				array_push($service_parameters_array, array(
					'id' => ''.$row_['id'].'',
					'id_description_type' => ''.$row_['id_description_type'].'',
					'required' => ''.$row_['required'].'',
				));
			}
			pg_free_result($result_);
		}
		echo '<div class="row">';
		$q_='SELECT description_types.name, description_types.id, description_types.tech_name, description_types.entity_type FROM description_types WHERE entity_type=\'visit\'';
		$q_.='ORDER BY description_types.entity_type, description_types.name';
		$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
		while ($row_ = pg_fetch_assoc($result_)) {
			echo '<div class="col-6" style="margin-bottom: 10px;">';
			echo '<input type="hidden" name="'.$row_['tech_name'].'" value="'.$row_['tech_name'].'">';
			echo '<input type="checkbox" name="'.$row_['tech_name'].'_" id="'.$row_['tech_name'].'_" value="1"';
			if($id){
				if(count($service_parameters_array) > 0){
					for ($k=0; $k<count($service_parameters_array); $k++) {
						if($service_parameters_array[$k]['id_description_type'] == $row_['id']){
							echo ' checked';
						}
					}
				}
			}
			echo '>';

			echo '<label for="'.$row_['tech_name'].'_">'.$row_['name'].'</label> (<input type="checkbox" name="'.$row_['tech_name'].'_required" id="'.$row_['tech_name'].'_required" value="1"';
			if($id){
				if(count($service_parameters_array) > 0){
					for ($k=0; $k<count($service_parameters_array); $k++) {
						if($service_parameters_array[$k]['id_description_type'] == $row_['id']){
							if($service_parameters_array[$k]['required'] == 't'){
								echo ' checked';
							}
						}
					}
				}
			}
			echo '><label for="'.$row_['tech_name'].'_required">обязательно</label>)';
			echo '</div>';
		}
		pg_free_result($result_);
		echo '</div>';
		###ПАРАМЕТРЫ

		###ОТЧЁТЫ
		$service_params_array = array();
		if($id){
			$q_='SELECT * FROM public.gov_services_params ';
			$q_.='WHERE id_service='.$id.' ';
			$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
			while ($row_ = pg_fetch_assoc($result_)) {
				array_push($service_params_array, array(
					'id' => ''.$row_['id'].'',
					'id_param' => ''.$row_['id_param'].'',
					'req_in' => ''.$row_['req_in'].'',
					'req_out' => ''.$row_['req_out'].'',
					'flag_in' => ''.$row_['flag_in'].'',
					'flag_out' => ''.$row_['flag_out'].'',
					'sort_by' => ''.$row_['sort_by'].'',
				));
			}
			pg_free_result($result_);
		}
		$service_reports_array = array();
		if($id){
			$q_='SELECT * FROM gov_services_reports ';
			$q_.='WHERE id_service='.$id.' ';
			$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
			while ($row_ = pg_fetch_assoc($result_)) {
				array_push($service_reports_array, array(
					'id_service' => ''.$row_['id_service'].'',
					'id_report' => ''.$row_['id_report'].'',
				));
			}
			pg_free_result($result_);
		}
		echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Отчёты по услуге</h2>';
		echo '<div class="row">';
		$q_='SELECT reports.name AS name, reports.id AS id FROM reports ';
		$q_.='ORDER BY reports.name';
		$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
		$count = pg_num_rows($result_);
		if($count > 0){
			while ($row_ = pg_fetch_assoc($result_)) {
				echo '<div class="col-6" style="margin-bottom: 10px;">';
				echo '<input type="checkbox" name="report_'.$row_['id'].'_" id="report_'.$row_['id'].'_" value="1"';
				if($id){
					if(count($service_reports_array) > 0){
						for ($k=0; $k<count($service_reports_array); $k++) {
							if($service_reports_array[$k]['id_report'] == $row_['id']){
								echo ' checked';
							}
						}
					}
				}
				echo '>';
				echo '<label for="report_'.$row_['id'].'_">'.$row_['name'].' ['.$row_['id'].']</label>';
				
				###
				$q__='SELECT params.id AS id, params.name AS name, params.tech_name AS tech_name, params.visit_flag AS visit_flag FROM reports_params ';
				$q__.='LEFT JOIN params ON reports_params.id_param=params.id ';
				$q__.='WHERE id_report = '.$row_['id'].' ORDER BY params.tech_name';
				$result__ = pg_query($q__) or die('Ошибка запроса: ' . pg_last_error());
				$count__ = pg_num_rows($result__);
				if($count__ > 0){
					echo ' <span style="cursor: pointer;" onclick="showhideDiv(\'report_'.$row_['id'].'\');">(параметры)</span>';
					echo '<div id="report_'.$row_['id'].'" style="font-size: 11px; display: none;">';
					echo '<ul>';
					while ($row__ = pg_fetch_assoc($result__)) {
						echo '<li>';
						echo '<input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_" value="1"';
						if(count($service_params_array) > 0){
							for ($k=0; $k<count($service_params_array); $k++) {
								if($service_params_array[$k]['id_param'] == $row__['id']){
									echo ' checked';
								}
							}
						}
						if($row__['visit_flag'] == 't'){
							echo ' checked disabled';
						}
						echo '> ';

						echo '<label for="report_'.$row_['id'].'_'.$row__['tech_name'].'_">';
						if($row__['visit_flag'] == 't'){
							echo '<strong>';
						}
						echo ''.$row__['name'].' ['.$row__['tech_name'].']';
						if($row__['visit_flag'] == 't'){
							echo '</strong>';
						}
						echo '</label>';

						if(count($service_params_array) > 0){
							echo '<table>';
							echo '<tr><td title="Обязательно для заполнения при добавлении услуги в приём">RI</td><td title="Обязательно для заполнения при сохранении услуги в приёме">RO</td><td title="Обязательно для заполнения при добавлении услуги в приём">FI</td><td title="Обязательно для заполнения при сохранении услуги в приёме">FO</td><td>Сорт.</td></tr>';
							echo '<tr>';
							$flag=1;
							for ($k=0; $k<count($service_params_array); $k++) {
								if($service_params_array[$k]['id_param'] == $row__['id']){
									echo '<td>';
									echo '<input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_in_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_in_" value="1"';
									if($service_params_array[$k]['req_in'] == 't'){echo ' checked';}
									echo '>';
									echo '</td>';

									echo '<td>';
									echo '<input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_out_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_out_" value="1"';
									if($service_params_array[$k]['req_out'] == 't'){echo ' checked';}
									echo '>';
									echo '</td>';

									echo '<td>';
									echo '<input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_in_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_in_" value="1"';
									if($service_params_array[$k]['flag_in'] == 't'){echo ' checked';}
									echo '>';
									echo '</td>';

									echo '<td>';
									echo '<input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_out_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_out_" value="1"';
									if($service_params_array[$k]['flag_out'] == 't'){echo ' checked';}
									echo '>';
									echo '</td>';

									echo '<td>';
									echo '<input type="text" style="font-size: 10px; width: 22px; padding: 4px; height: auto;" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_sort_by_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_sort_by_" value="'.$service_params_array[$k]['sort_by'].'">';
									echo '</td>';
									$flag=0;
								}
							}
							if($flag){
								echo '<td><input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_in_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_in_" value="1"></td>';
								echo '<td><input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_out_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_req_out_" value="1"></td>';
								echo '<td><input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_in_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_in_" value="1"></td>';
								echo '<td><input type="checkbox" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_out_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_flag_out_" value="1"></td>';
								echo '<td><input type="text" style="font-size: 10px; width: 22px; padding: 4px; height: auto;" name="report_'.$row_['id'].'_'.$row__['tech_name'].'_sort_by_" id="report_'.$row_['id'].'_'.$row__['tech_name'].'_sort_by_" value="0"></td>';
							}
							echo '</tr></table>';
						}

						echo '</li>';
					}
					echo '</ul>';
					echo '</div>';
				}
				pg_free_result($result__);

				echo '</div>';
			}
		}
		pg_free_result($result_);
		echo '</div>';
		###ОТЧЁТЫ

		// echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Отчёты по услуге по аналогии</h2>';

		// echo '<div class="row">';
		// $q_='SELECT * FROM gov_services WHERE id_pricelist=14 ';
		// $q_.='ORDER BY name';
		// echo '<p><select name="old_service">';
		// echo '<option value="">Нет</option>';
		// $result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
		// while ($row_ = pg_fetch_assoc($result_)) {
		// 	echo '<option value="'.$row_['id'].'">'.$row_['name'].'</option>';
		// }
		// pg_free_result($result_);
		// echo '</select></p>';
		// echo '</div>';

		#echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Параметры</h2>';

		if($can){
			echo '<input type="hidden" name="action" value="save_service">';
			if($id){
				echo '<button type="submit">Сохранить</button>';
			}else{
				echo '<button type="submit">Добавить</button>';
			}
		}
		
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';

		echo '<script>';
		echo '$(document).ready(function () {';
		echo '$(\'#cod\').mask("0999");';
		echo '});';
		echo '</script>';
	}

	function viewServicesUploadPriceFile($configuration){
		$file_inputname='file';
		$fileTmpPath = $_FILES[$file_inputname]['tmp_name'];
		$fileName = $_FILES[$file_inputname]['name'];
		$fileSize = $_FILES[$file_inputname]['size'];
		$fileType = $_FILES[$file_inputname]['type'];
		// $fileNameCmps = explode(".", $fileName);
		// $fileExtension = strtolower(end($fileNameCmps));
		$uploadFileDir = './';
		$dest_path = $uploadFileDir . 'price.xls';

		unlink($dest_path);

		if($fileName){
			if(move_uploaded_file($fileTmpPath, $dest_path)){
				header("Location: index.php?action=price");
				//echo xml('<message>successfully</message>');
			}else{
				header("Location: index.php?action=upload_price&message=file_not_uploaded");
				//echo xml('<message>file_not_uploaded</message>');
			}
		}else{
			header("Location: index.php?action=upload_price&message=empty_fields");
			//echo xml('<message>empty_fields</message>');
		}
	}

	function viewServicesUploadPrice($configuration){
		if(isset($_POST["id"])){$id=$_POST["id"];}else{$id=$_GET["id"];}

		if(isset($_POST["message"])){$message=$_POST["message"];}else{$message=$_GET["message"];}

		$user_fullname='';
		if($_COOKIE['login']){
			$query='SELECT fullname FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				$user_fullname=$row['fullname'];
			}
			pg_free_result($result);
		}

		$service='';
		if($id){
			$query='SELECT * FROM gov_services WHERE ';
			$query.='id=\''.string_formating_for_sql($id).'\' ';
			$query.='LIMIT 1';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$service = pg_fetch_assoc($result);
			pg_free_result($result);
		}

		// echo '<div class="row main_row header" style="min-height: auto !important;">';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '<div class="col-xl-8 col-lg-10">';
		// echo '<div class="header_sub row">';
		// echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-left"><a href="index.php" style="float: none;">К списку услуг</a></div>';
		// echo '</div>';
		// echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12"><h1 style="text-align: center;">Редактор услуг</h1>';
		
		// echo '<h2 style="text-align: center;">';
		// echo 'Загрузка прайс-листа';
		// echo '</h2>';
		
		// echo '</div>';
		// echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
		// echo '<div class="d-table-cell align-middle text-center">'.$user_fullname.'</div>';
		// echo '<div class="d-table-cell align-middle text-center controls">';
		// echo '<a href="index.php?action=exit"><div class="exit_button" title="Выход"></div></a>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '</div>';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '</div>';

		echo '<div class="row main_row">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 add_form">';
		echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Прайс лист</h2>';

		if($message == 'empty_fields'){
			echo '<div class="message error" style="margin-top: 15px; margin-bottom: 15px;">Ошибка! Не выбран файл для загрузки.</div>';
		}

		echo '<form enctype="multipart/form-data" action="index.php" method="POST" class="row">';
		echo '<input type="hidden" name="action" value="upload_pricefile" />';
    	echo '<input name="file" type="file" class="col-4" />';
    	echo '<input type="submit" value="Загрузить прайс-лист" class="col-4" />';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	}
	
	function viewServicesPriceList($configuration){
		require('library/php-excel-reader/excel_reader2.php');
		require('library/SpreadsheetReader.php');

		try
		{
			// echo '<div class="row main_row header" style="min-height: auto !important;">';
			// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			// echo '<div class="col-xl-8 col-lg-10">';
			// echo '<div class="header_sub row">';
			// echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
			// echo '<div class="d-table-cell align-middle text-left"><a href="index.php" style="float: none;">К списку услуг</a></div>';
			// echo '</div>';
			// echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12"><h1 style="text-align: center;">Редактор услуг</h1>';
			
			// echo '<h2 style="text-align: center;">';
			// echo 'Услуги из загруженного прайс-листа';
			// echo '</h2>';
			
			// echo '</div>';
			// echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
			// echo '<div class="d-table-cell align-middle text-center">'.$user_fullname.'</div>';
			// echo '<div class="d-table-cell align-middle text-center controls">';
			// echo '<a href="index.php?action=exit"><div class="exit_button" title="Выход"></div></a>';
			// echo '</div>';
			// echo '</div>';
			// echo '</div>';
			// echo '</div>';
			// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			// echo '</div>';

			$new_services=0;
			$edit_services=0;

			echo '<div class="row main_row">';

			echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
			echo '<div class="col-xl-8 col-lg-10">';
			echo '<h2 style="margin-top: 15px; margin-bottom: 15px;">Список услуги из прайс-листа</h2>';
			echo '<div class="legend">';
			echo '<div class="edit">Присутствуют изменения</div>';
			echo '<div class="new">Новая услуга</div>';
			echo '</div>';
			echo '<form>';

			$Spreadsheet = new SpreadsheetReader('price.xls');
			$BaseMem = memory_get_usage();

			$Sheets = $Spreadsheet -> Sheets();
			
			$query='SELECT organizations.id, organizations.short_name FROM organizations ';
			$query.='WHERE organizations.short_name LIKE \''.$Sheets[0].'%\' OR organizations.short_name=\''.$Sheets[0].'\'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id_organization=$row[0];
			$name_organization=$row[1];
			
			foreach ($Sheets as $Index => $Name){
				#echo '<strong>'.$Name.'</strong><br>'.PHP_EOL;

				$Spreadsheet -> ChangeSheet($Index);

				$services_array = array();
				
				foreach ($Spreadsheet as $Key => $Row)
				{
					array_push($services_array, array('name' => $Row[2],'price' => $Row[6], 'cod'=> $Row[1]));
					
				}
			}
			
			echo '<table class="services">';
			for($j=0;$j<=count($services_array);$j++){
				if($services_array[$j]['cod'] != ''){
					$query='SELECT id,name, price FROM gov_services ';
					$query.='WHERE gov_services.cod=\''.$services_array[$j]['cod'].'\' AND deleted=false';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);

					$query_='SELECT id FROM gov_services ';
					$query_.='WHERE gov_services.name=\''.$services_array[$j]['name'].'\' OR gov_services.name LIKE \''.$services_array[$j]['name'].'%\'';
					$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
					$row_ = pg_fetch_row($result_);

					$services_array[$j]['price']=str_replace(',', '', $services_array[$j]['price']);
					if($row[0]){
						if($row_[0]){
							echo '<tr><td>'.$services_array[$j]['name'].'</td>';

							if($services_array[$j]['price'] != $row[2]){
								echo '<td style="background: #E59721;">['.$services_array[$j]['cod'].']</td>';
								echo '<td style="background: #E59721;">'.$services_array[$j]['price'].'</td>';
								echo '</tr>';
								echo '<tr><td></td>';
								echo '<td></td>';
								echo '<td style="background: #E59721; opacity: 0.7;">↑ '.$row[2].'</td>';
								echo '</tr>';
							}else{
								echo '<td>['.$services_array[$j]['cod'].']</td>';
								echo '<td>'.$services_array[$j]['price'].'</td>';
								echo '</tr>';
							}
						}else{
							if($services_array[$j]['name'] != $row[1] || $services_array[$j]['price'] != $row[2]){
								echo '<tr style="background: #E59721;"><td>'.$services_array[$j]['name'].'</td><td>['.$services_array[$j]['cod'].']</td><td>'.$services_array[$j]['price'].'</td></tr>';
								
								echo '<tr>';
								if($services_array[$j]['name'] != $row[1]){
									echo '<td style="background: #E59721; opacity: 0.7;">↑ ';
								}else{
									echo '<td>';
								}
								echo ''.$row[1].'</td>';
								echo '<td></td>';

								if($services_array[$j]['price'] != $row[2]){
									echo '<td style="background: #E59721; opacity: 0.7;">↑ ';
								}else{
									echo '<td>';
								}
								echo ''.$row[2].'</td>';
								echo '</tr>';

								$edit_services++;
							}else{
								echo '<tr><td>'.$services_array[$j]['name'].'</td><td>['.$services_array[$j]['cod'].']</td><td>'.$services_array[$j]['price'].'</td></tr>';
							}
						}
					}else{
						echo '<tr style="background: #ea5424;"><td><strong>'.$services_array[$j]['name'].'</strong></td><td>['.$services_array[$j]['cod'].']</td><td>'.$services_array[$j]['price'].'</td></tr>';
						$new_services++;
					}
				}
			}
			echo "</table>\n";

			echo '<div class="w-100 info">';
			echo '<p>ВСЕГО услуг с изменениями: <strong>'.$edit_services.'</strong>. Услуги будут загружены с измененными названиями и ценами.</p>';
			echo '<p>ВСЕГО новых услуг: <strong>'.$new_services.'</strong>. Услуги не будут загружены, необходимо ручное заведение услуг.</p>';

			echo '<div class="w-100 service_item p-3" style="margin-top: 15px; margin-bottom: 15px;">Для всех визитов и специалистов с текущего дня обновятся связи с новыми услугами по аналогии.</div>';
			echo '</div>';
			

			echo '<input type="hidden" name="action" value="update_pricelist">';
			echo '<button type="submit">Обновить прайс-лист</button>';

			echo '</form>';
			echo '</div>';
			echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

			echo '</div>';
			
			#var_dump($services_array);
		}
		catch (Exception $E)
		{
			echo $E -> getMessage();
		}
	}

	function update_pricelist($configuration){
		$id_user=2731;

		require('library/php-excel-reader/excel_reader2.php');
		require('library/SpreadsheetReader.php');

		$debug = 0;

		try
		{
			header_site(0,$configuration);

			$counter_updates=0;

			echo '<div class="row main_row">';

			echo '<div class="col-2"></div>';
			echo '<h1 class="col-8 header_sub" style="color: #fff;">Процесс обновления из прайс-листа</h1>';
			echo '<div class="col-2"></div>';

			echo '<div class="col-2"></div>';
			echo '<div class="col-8">';
			$id_pricelist = 33;
			echo "<p style=\"padding-top: 15px;\">Добавление прайса:<br /><font size=\"1\"><strong>".$query."</strong></font></p>";
			echo "<p>Устанавливаем все предыдущие услуги с пометкой удалены для текущего прайс-листа:<br /><font size=\"1\"><strong>".$query."</strong></font></p>";
			//открываем xls и добавляем старые услуги, которые по коду найдены в предыдущем прайсе

			$Spreadsheet = new SpreadsheetReader('price.xls');
			$BaseMem = memory_get_usage();

			$Sheets = $Spreadsheet -> Sheets();
			//pg_query("UPDATE gov_services SET deleted = true, updated_by = $id_user, updated_at = 'NOW()::timestamp(0)' WHERE id_pricelist = 14");
			foreach ($Sheets as $Index => $Name){
				$Spreadsheet -> ChangeSheet($Index);
				$services_array = array();
				foreach ($Spreadsheet as $Key => $Row){
					array_push($services_array, array('name' => $Row[2],'price' => $Row[4], 'cod'=> $Row[1]));
				}
			}
			$ids = [];
			
			echo '<ul>';
			for($j=0;$j<=count($services_array);$j++){
				if($services_array[$j]['cod'] != ''){
					$query='SELECT * FROM gov_services ';
					$query.='WHERE gov_services.cod=\''.$services_array[$j]['cod'].'\' AND id_pricelist = 14 ORDER BY id DESC LIMIT 1';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$service_old_row = pg_fetch_assoc($result);
					pg_free_result($result);
					$counter_updates++;

					if($service_old_row['id']){###СТАРАЯ УСЛУГА НАЙДЕНА
						###ДОБАВЛЕНИЕ УСЛУГИ
						$query="INSERT INTO gov_services ";
						$query.="(id_pricelist, name, price, id_service_type, id_specialization, duration, id_service_measure, alternative_name, cod, cooldown, type, id_service_goal, at_home,";
						$query.="at_clinic, briefname, com_class_journal, for_broods, for_multiple, once_per_day, created_by, updated_by, created_at, updated_at, deleted) ";
						$query.="VALUES (";
						$query.="".$id_pricelist.", ";###ID прайслиста

						if($service_old_row['at_home'] == 't'){$service_old_row['at_home']='true';}else{$service_old_row['at_home']='false';}
						if($service_old_row['at_clinic'] == 't'){$service_old_row['at_clinic']='true';}else{$service_old_row['at_clinic']='false';}
						if($service_old_row['once_per_day'] == 't'){$service_old_row['once_per_day']='true';}else{$service_old_row['once_per_day']='false';}

						###ПРЕДЫДУЩИЕ ПАРАМЕТРЫ
						$query.="'".$services_array[$j]['name']."', ";###Название услуги
						$services_array[$j]['price']=str_replace(',', '', $services_array[$j]['price']);
						$query.="".$services_array[$j]['price'].", ";###Цена услуги
						$query.="".$service_old_row['id_service_type'].", ";###Тип услуги

						if($service_old_row['id_specialization'] != ''){
							$query.="".$service_old_row['id_specialization'].", ";###Специализация услуги
						}else{
							$query.="NULL, ";###Специализация услуги
						}

						$query.="".$service_old_row['duration'].", ";###Продолжительность
						$query.="".$service_old_row['id_service_measure'].", ";###Единица измерения услуги
						if($service_old_row['alternative_name'] != ''){
							$query.="'".$service_old_row['alternative_name']."', ";
						}else{
							$query.="NULL, ";
						}
						$query.="'".$service_old_row['cod']."', ";###Код услуги
						$query.="".$service_old_row['cooldown'].", ";###Длительность перерыва после услуги (в минутах)
						if($service_old_row['type'] != ''){
							$query.="".$service_old_row['type'].", ";
						}else{
							$query.="NULL, ";
						}
						if($service_old_row['id_service_goal'] != ''){
							$query.="".$service_old_row['id_service_goal'].", ";
						}else{
							$query.="NULL, ";
						}
						$query.="".$service_old_row['at_home'].", ";###Услуга оказывается на дому
						$query.="".$service_old_row['at_clinic'].", ";###Услуга оказывается в клинике
						if($service_old_row['briefname'] != ''){
							$query.="'".$service_old_row['briefname']."', ";
						}else{
							$query.="NULL, ";
						}
						$query.="'".$service_old_row['com_class_journal']."', ";

						if($service_old_row['for_broods'] != ''){
							$query.="'".$service_old_row['for_broods']."', ";
						}else{
							$query.="NULL, ";
						}
						if($service_old_row['for_multiple'] != ''){
							$query.="'".$service_old_row['for_multiple']."', ";
						}else{
							$query.="NULL, ";
						}

						$query.="".$service_old_row['once_per_day'].", ";###Флаг: услуга может оказываться раз в день
						###ПРЕДЫДУЩИЕ ПАРАМЕТРЫ
						$service_old_row['deleted'] = $service_old_row['deleted'] == 't' ? 'true' : 'false';
						$query.="'".$id_user."','".$id_user."', NOW()::timestamp(0), NOW()::timestamp(0), {$service_old_row['deleted']}) RETURNING id;";#24
						
						if(!$debug){
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$row = pg_fetch_row($result);
							$id_new_service=$row[0];
							pg_free_result($result);
						}
						$counter_updates++;

						echo "<li><font color=\"blue\">Добавляем</font> обновленную услугу <i>".$services_array[$j]['name']."</i> (".$services_array[$j]['cod'].") со всеми предыдущими параметрами (старый id - ".$service_old_row['id']."):<br /><font size=\"1\"><strong>".$query."</strong></font>";

						//обновляем связи специалист+услуга services_specialists

						echo '<br><br>Обновляем связь услуга специалист<font size="1">';
						echo '<ul>';
						//вначале считываем
						$query='SELECT * FROM services_specialists ';
						$query.='WHERE id_service='.$service_old_row['id'].' ';
						$result_specialists = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row_specialists = pg_fetch_assoc($result_specialists)) {
							$query="INSERT INTO services_specialists ";
							$query.="(id_service, id_specialist, id_organization, created_by, updated_by, created_at, updated_at) ";
							$query.="VALUES ('".$id_new_service."', '".$row_specialists['id_specialist']."', '".$row_specialists['id_organization']."','".$id_user."','".$id_user."', NOW()::timestamp(0), NOW()::timestamp(0));";
							echo '<li>'.$query.'</li>';
							if(!$debug){
								$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								pg_free_result($result_);
							}
							$counter_updates++;
						}
						pg_free_result($result);
						echo '</ul></font>';
						
						//обновляем связи services_description_types
						echo '<br>Обновляем связь услуга+входной параметр<font size="1">';
						echo '<ul>';
						//вначале считываем
						$query='SELECT * FROM services_description_types ';
						$query.='WHERE id_service='.$service_old_row['id'].' ';
						$result_description_types = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row_description_types = pg_fetch_assoc($result_description_types)) {
							if($row_description_types['required'] == 't'){$row_description_types['required']='true';}else{$row_description_types['required']='false';}
							$query="INSERT INTO services_description_types ";
							$query.="(id_service, id_description_type, required) ";
							$query.="VALUES (".$id_new_service.", ".$row_description_types['id_description_type'].", ".$row_description_types['required'].");";
							echo '<li>'.$query.'</li>';
							if(!$debug){
								$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								pg_free_result($result_);
							}
							$counter_updates++;
						}
						pg_free_result($result);
						echo '</ul></font>';

						//обновляем связи gov_services_reports
						echo '<br>Обновляем связь услуга+отчёт<font size="1">';
						echo '<ul>';
						//вначале считываем
						$query='SELECT * FROM gov_services_reports ';
						$query.='WHERE id_service='.$service_old_row['id'].' ';
						$result_services_reports = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row_services_reports = pg_fetch_assoc($result_services_reports)) {
							$query="INSERT INTO gov_services_reports ";
							$query.="(id_service, id_report, created_by, updated_by, created_at, updated_at) ";
							$query.="VALUES (".$id_new_service.", ".$row_services_reports['id_report'].",'".$id_user."','".$id_user."', NOW()::timestamp(0), NOW()::timestamp(0));";
							echo '<li>'.$query.'</li>';
							if(!$debug){
								$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								pg_free_result($result_);
							}
							$counter_updates++;
						}
						pg_free_result($result);
						echo '</ul></font>';

						echo '<br>Обновляем связь услуга+отчёт+параметр отчёта';
						echo '<font size="1"><ul>';
						//вначале считываем
						$query='SELECT * FROM gov_services_params ';
						$query.='WHERE id_service='.$service_old_row['id'].' ';
						$result_services_params = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row_services_params = pg_fetch_assoc($result_services_params)) {
							if($row_services_params['req_in'] == 't'){$row_services_params['req_in']='true';}else{$row_services_params['req_in']='false';}
							if($row_services_params['req_out'] == 't'){$row_services_params['req_out']='true';}else{$row_services_params['req_out']='false';}
							if($row_services_params['flag_in'] == 't'){$row_services_params['flag_in']='true';}else{$row_services_params['flag_in']='false';}
							if($row_services_params['flag_out'] == 't'){$row_services_params['flag_out']='true';}else{$row_services_params['flag_out']='false';}

							$query="INSERT INTO gov_services_params ";
							$query.="(id_service, ";#1
							$query.="id_param, ";#2
							$query.="req_in, ";#3
							$query.="req_out, ";#4
							$query.="sort_by, ";#5
							$query.="flag_in, ";#6
							$query.="flag_out, ";#7
							$query.="created_by, ";#8
							$query.="updated_by, ";#9
							$query.="created_at, ";#10
							$query.="updated_at) ";#11
							$query.="VALUES (".$id_new_service.", ";#1
							$query.="".$row_services_params['id_param'].", ";#2
							$query.="".$row_services_params['req_in'].", ";#3
							$query.="".$row_services_params['req_out'].", ";#4
							if($row_services_params['sort_by'] != ''){
								$query.="".$row_services_params['sort_by'].", ";#5
							}else{
								$query.="NULL, ";
							}
							$query.="".$row_services_params['flag_in'].", ";#6
							$query.="".$row_services_params['flag_out'].",";#7
							$query.="'".$id_user."',";#8
							$query.="'".$id_user."'";#9
							$query.=", NOW()::timestamp(0), ";#10
							$query.="NOW()::timestamp(0));";#11
							echo '<li>'.$query.'</li>';
							if(!$debug){
								$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								pg_free_result($result_);
							}
							$counter_updates++;
						}
						pg_free_result($result);
						echo '</ul></font>';

						echo "</li>";
					}else{
						echo "<li>Услугу необходимо <strong><font color=\"red\">добавить</font></strong> через редактор услуг <i>".$services_array[$j]['name']."</i> (".$services_array[$j]['cod'].")</li>";
					}
				}
			}

			echo "</ul>\n";
			echo '</div>';

			echo '</div>';
			echo '<div class="col-2"></div>';

			echo '</div>';

			footer_site();
			
			
		}

		catch (Exception $E)
		{
			echo $E -> getMessage();
		}
		//копирование всех зависимостей

		//species_services - id_species+id_service
	}

	function save_service($configuration){
		$id_user=2731;//нормальный юзер

		###Параметры услуги
		$id=$_POST["id"];
		$id_pricelist=$_POST["id_pricelist"];
		$deleted=$_POST["deleted"];
		$type=$_POST["type"];
		$name=$_POST["name"];
		$price=$_POST["price"];
		$id_service_type=$_POST["id_service_type"];
		$id_service_mosru=$_POST["id_service_mosru"];

		$id_specialization=$_POST["id_specialization"];
		$duration=$_POST["duration"];
		$id_service_measure=$_POST["id_service_measure"];
		$alternative_name=$_POST["alternative_name"];
		$cod=$_POST["cod"];
		$cooldown=$_POST["cooldown"];
		$id_service_goal=$_POST["id_service_goal"];
		$at_home=$_POST["at_home"];
		$at_clinic=$_POST["at_clinic"];
		$briefname=$_POST["briefname"];
		$com_class_journal=$_POST["com_class_journal"];
		$for_broods=$_POST["for_broods"];
		$for_multiple=$_POST["for_multiple"];
		$once_per_day=$_POST["once_per_day"];
		$old_service=$_POST["old_service"];


		
		###Параметры услуги

		$debug=0;

		#NULL
		#mosru
		#$type='NULL';

		#1	"Лечение"
		#2	"Лабораторно-диагностические исследования"
		$id_service_goal='NULL';

		if($deleted == 'on'){$deleted='true';}else{$deleted='false';}
		if($at_home == 'on'){$at_home='true';}else{$at_home='false';}
		if($at_clinic == 'on'){$at_clinic='true';}else{$at_clinic='false';}
		if($once_per_day == 'on'){$once_per_day='true';}else{$once_per_day='false';}

		// echo $name;
		// echo $duration;
		// echo $cooldown;

		if($name != '' && $duration != '' && $cooldown != ''){
			if($id){
				$query="UPDATE gov_services SET ";
				$query.="id_pricelist=".$id_pricelist.",";
				$query.="name='".$name."',";
				if($type != ''){$query.="type='".$type."',";}else{$query.="type=NULL, ";}
				if($price != ''){$query.="price=".$price.",";}else{$query.="price=0.00,";}

				if($id_service_type){$query.="id_service_type=".$id_service_type.",";}else{$query.="id_service_type=NULL,";}
				

				if($id_service_mosru){
					$query.="id_service_mosru=".$id_service_mosru.",";
				}else{
					$query.="id_service_mosru=NULL,";
				}

				if($id_specialization != ''){$query.="id_specialization='".$id_specialization."',";}else{$query.="id_specialization=NULL, ";}
				$query.="duration=".$duration.",";
				$query.="id_service_measure=".$id_service_measure.",";
				if($alternative_name != ''){$query.="alternative_name='".$alternative_name."',";}else{$query.="alternative_name=NULL, ";}
				$query.="cod='".$cod."',";
				if($cooldown){$query.="cooldown=".$cooldown.",";}else{$query.="cooldown=0,";}
				$query.="id_service_goal=".$id_service_goal.",";
				$query.="at_home=".$at_home.",";
				$query.="at_clinic=".$at_clinic.",";
				$query.="deleted=".$deleted.",";
				if($briefname != ''){$query.="briefname='".$briefname."',";}else{$query.="briefname=NULL, ";}			
				if($com_class_journal != 'NULL'){$query.="com_class_journal='".$com_class_journal."', ";}else{$query.="com_class_journal=".$com_class_journal.", ";}
				if($for_broods != 'NULL'){$query.="for_broods='".$for_broods."', ";}else{$query.="for_broods=".$for_broods.", ";}
				if($for_multiple != 'NULL'){$query.="for_multiple='".$for_multiple."', ";}else{$query.="for_multiple=".$for_multiple.", ";}
				$query.="once_per_day=".$once_per_day.",";
				$query.="updated_by=".$id_user.",";#updated_by
				$query.="updated_at=NOW()::timestamp(0)";#updated_at
				$query.=" WHERE id=".$id.";";

				if($debug != 1){
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);
				}else{
					echo "<p>".$query."</p>";
				}

				###ПАРАМЕТРЫ
				$q_='SELECT * FROM description_types ';
				$q_.='WHERE description_types.entity_type=\'visit\'';
				$q_.='ORDER BY description_types.sort_by';
				$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
				while ($row_ = pg_fetch_assoc($result_)) {
					$name_=$_POST[$row_['tech_name']];###name
					$name__=$_POST[$row_['tech_name']."_"];###checked
					$required_=$_POST[$row_['tech_name']."_required"];###required

					//проверка была или нет связь
					$q_='SELECT id, required FROM services_description_types ';
					$q_.='WHERE id_service='.$id.' AND  id_description_type='.$row_['id'].'';
					$r_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
					$res_ = pg_fetch_row($r_);
					pg_free_result($r_);
					// echo $q_;
					//проверка была или нет связь
					
					if($name_ == $row_['tech_name'] && $name__ == '1' && $res_[0] == 0){
						$query="INSERT INTO services_description_types ";
						$query.="(id_service, id_description_type, required) ";
						$query.="VALUES (";
						$query.="".$id.", ";###Услуга
						$query.="".$row_['id'].", ";
						if($required_ == '1'){
							$query.="true";
						}else{
							$query.="false";
						}
						$query.=");";
						if($debug != 1){
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$row = pg_fetch_row($result);
							pg_free_result($result);
						}else{
							echo "<p>$res_[0]".$query."</p>";
						}
					}
					
					if($name_ == $row_['tech_name'] && $name__ == '' && $res_[0] != 0){
						$query="DELETE FROM services_description_types ";
						$query.="WHERE id=".$res_[0]."";
						
						if($debug != 1){
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$row = pg_fetch_row($result);
							pg_free_result($result);
						}else{
							echo "<p>".$query."</p>";
						}
					}

					if($name_ == $row_['tech_name'] && $name__ == '1' && $res_[0] != 0 && (($res_[1] == 't' && $required_ == '') || ($res_[1] == 'f' && $required_ == '1'))){
						$query='UPDATE services_description_types SET ';
						if($required_){
							$query.='required=\'true\' ';
						}else{
							$query.='required=\'false\' ';
						}
						$query.="WHERE id=".$res_[0]."";

						if($debug != 1){
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$row = pg_fetch_row($result);
							pg_free_result($result);
						}else{
							echo "<p>".$query."</p>";
						}
					}
				}
				pg_free_result($result_);
				###ПАРАМЕТРЫ

				###ОТЧЁТЫ
				$q_='SELECT reports.name AS name, reports.id AS id FROM reports ';
				$q_.='ORDER BY reports.name';
				$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
				$count = pg_num_rows($result_);
				if($count > 0){
					while ($row_ = pg_fetch_assoc($result_)) {
						$report_=$_POST["report_".$row_['id']."_"];###checked

						//проверка была или нет связь
						$q_='SELECT id FROM gov_services_reports ';
						$q_.='WHERE id_service='.$id.' AND  id_report='.$row_['id'].'';
						$r_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
						$res_ = pg_fetch_row($r_);
						pg_free_result($r_);
						//проверка была или нет связь

						if($report_ == '1' && $res_[0] == 0){
							$query="INSERT INTO gov_services_reports ";
							$query.="(id_service, id_report) ";
							$query.="VALUES (";
							$query.="".$id.", ";###Услуга
							$query.="".$row_['id']."";
							$query.=");";
							if($debug != 1){
								$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								$row = pg_fetch_row($result);
								pg_free_result($result);
							}else{
								echo "<p>".$query."</p>";
							}

							###ДОБАВЛЯЕМ СВЯЗИ
							
							$q__='SELECT params.id AS id, params.name AS name, params.tech_name AS tech_name, params.visit_flag AS visit_flag FROM reports_params ';
							$q__.='LEFT JOIN params ON reports_params.id_param=params.id ';
							$q__.='WHERE id_report = '.$row_['id'].' ORDER BY params.tech_name';
							$result__ = pg_query($q__) or die('Ошибка запроса: ' . pg_last_error());
							$count__ = pg_num_rows($result__);
							if($count__ > 0){
								while ($row__ = pg_fetch_assoc($result__)) {
									$report_param_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_"];

									$report_param_req_in_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_req_in_"];
									$report_param_req_out_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_req_out_"];
									$report_param_flag_in_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_flag_in_"];
									$report_param_flag_out_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_flag_out_"];
									$report_param_sort_by_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_sort_by_"];
									
									if($report_param_){
										$query="INSERT INTO gov_services_params ";
										$query.="(id_param, id_service, req_in, req_out, flag_in, flag_out, sort_by) ";
										$query.="VALUES (";
										$query.="".$row__['id'].",";
										$query.="".$id.",";
										if($report_param_req_in_){$query.="true,";}else{$query.="false,";}
										if($report_param_req_out_){$query.="true,";}else{$query.="false,";}
										if($report_param_flag_in_){$query.="true,";}else{$query.="false,";}
										if($report_param_flag_out_){$query.="true,";}else{$query.="false,";}
										$query.="".$report_param_sort_by_."";
										$query.=");";
										
										if($debug != 1){
											$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
											$row = pg_fetch_row($result);
											pg_free_result($result);
										}else{
											echo "<p>".$query."</p>";
										}
									}
								}
							}
							pg_free_result($result__);
						}

						if($report_ == '' && $res_[0] != 0){
							###УДАЛЯЕМ ВСЕ СВЯЗИ
							$query="DELETE FROM gov_services_reports ";
							$query.="WHERE id=".$res_[0]."";
							
							if($debug != 1){
								$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
								$row = pg_fetch_row($result);
								pg_free_result($result);
							}else{
								echo "<p>".$query."</p>";
							}
							###НЕПРАВИЛЬНО РАБОТАЕТ УДАЛЯЕТ У ВСЕХ

							$q__='SELECT params.id AS id, params.name AS name, params.tech_name AS tech_name, params.visit_flag AS visit_flag FROM reports_params ';
							$q__.='LEFT JOIN params ON reports_params.id_param=params.id ';
							$q__.='WHERE id_report = '.$row_['id'].' ORDER BY params.tech_name';
							$result__ = pg_query($q__) or die('Ошибка запроса: ' . pg_last_error());
							$count__ = pg_num_rows($result__);
							if($count__ > 0){
								while ($row__ = pg_fetch_assoc($result__)) {
									$query="DELETE FROM gov_services_params ";
									$query.="WHERE id_param=".$row__['id']." AND id_service=".$id."";
									
									if($debug != 1){
										$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
										$row = pg_fetch_row($result);
										pg_free_result($result);
									}else{
										echo "<p>".$query."</p>";
									}
								}
							}
							pg_free_result($result__);
							###ЕСТЬ ЛИ ЭТОТ ПАРАМЕТР У ЭТОГО ОТЧЁТА
						}

						if($report_ == '1' && $res_[0] != 0){
							$q__='SELECT params.id AS id, params.name AS name, params.tech_name AS tech_name, params.visit_flag AS visit_flag FROM reports_params ';
							$q__.='LEFT JOIN params ON reports_params.id_param=params.id ';
							$q__.='WHERE id_report = '.$row_['id'].' ORDER BY params.tech_name';
							//echo $q__;
							$result__ = pg_query($q__) or die('Ошибка запроса: ' . pg_last_error());
							$count__ = pg_num_rows($result__);
							if($count__ > 0){
								while ($row__ = pg_fetch_assoc($result__)) {
									$report_param_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_"];

									$report_param_req_in_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_req_in_"];
									$report_param_req_out_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_req_out_"];
									$report_param_flag_in_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_flag_in_"];
									$report_param_flag_out_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_flag_out_"];
									$report_param_sort_by_=$_POST["report_".$row_['id']."_".$row__['tech_name']."_sort_by_"];

									if(!$report_param_sort_by_){
										$report_param_sort_by_=0;
									}
									
									if($report_param_){
										echo $row__['tech_name'];

										//есть параметр или нет?
										//проверка была или нет связь
										$q_='SELECT id FROM gov_services_params ';
										$q_.='WHERE id_service='.$id.' AND  id_param='.$row__['id'].'';
										$r_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
										$param_ = pg_fetch_row($r_);
										pg_free_result($r_);
										//проверка была или нет связь

										if($param_[0]){
											echo 'есть';
											$query='UPDATE gov_services_params SET ';
											$query.='id_param='.$row__['id'].', ';
											$query.='id_service='.$id.', ';
											$query.='sort_by='.$report_param_sort_by_.', ';
											if($report_param_req_in_){$query.="req_in=true,";}else{$query.="req_in=false,";}
											if($report_param_req_out_){$query.="req_out=true,";}else{$query.="req_out=false,";}
											if($report_param_flag_in_){$query.="flag_in=true,";}else{$query.="flag_in=false,";}
											if($report_param_flag_out_){$query.="flag_out=true";}else{$query.="flag_out=false";}
											$query.=" WHERE id=".$param_[0]."";
											if($debug != 1){
												$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
												$row = pg_fetch_row($result);
												pg_free_result($result);
											}else{
												echo "<p>".$query."</p>";
											}
										}else{
											$query="INSERT INTO gov_services_params ";
											$query.="(id_param, id_service, req_in, req_out, flag_in, flag_out, sort_by) ";
											$query.="VALUES (";
											$query.="".$row__['id'].",";
											$query.="".$id.",";
											if($report_param_req_in_){$query.="true,";}else{$query.="false,";}
											if($report_param_req_out_){$query.="true,";}else{$query.="false,";}
											if($report_param_flag_in_){$query.="true,";}else{$query.="false,";}
											if($report_param_flag_out_){$query.="true,";}else{$query.="false,";}
											$query.="".$report_param_sort_by_."";
											$query.=");";
											
											if($debug != 1){
												$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
												$row = pg_fetch_row($result);
												pg_free_result($result);
											}else{
												echo "<p>".$query."</p>";
											}	
										}
									}
								}
							}
							pg_free_result($result__);
						}
					}
				}
				pg_free_result($result_);
				###ОТЧЁТЫ

				if($debug != 1){
					header("Location: index.php?action=edit_service&id=".$id."&message=successfully");
				}else{
					echo "<strong>Услуга ".$id." обновлена</strong>";
				}
			}else{
				###ДОБАВЛЕНИЕ УСЛУГИ
				$query="INSERT INTO gov_services ";
				$query.="(id_pricelist, name, type, price, id_service_type, id_service_mosru, id_specialization, duration, id_service_measure, alternative_name, cod, cooldown, id_service_goal, at_home,";
				$query.="at_clinic, deleted, briefname, com_class_journal, for_broods, for_multiple, once_per_day, created_by, updated_by, created_at, updated_at) ";
				$query.="VALUES (";
				$query.="".$id_pricelist.", ";###ID прайслиста
				$query.="'".$name."', ";###Название услуги
				if($type != ''){$query.="'".$type."', ";}else{$query.="NULL, ";}###Тип (null — обычная, mosru — услуга для mosru
				if($price != ''){$query.="".$price.",";}else{$query.="0.00,";}###Цена услуги

				$query.="".$id_service_type.", ";###Тип услуги


				if($id_service_mosru){
					$query.="".$id_service_mosru.", ";###
				}else{
					$query.="NULL, ";###
				}

				if($id_specialization != ''){$query.="'".$id_specialization."', ";}else{$query.="NULL, ";}###Специализация услуги
				$query.="".$duration.", ";###Продолжительность
				$query.="".$id_service_measure.", ";###Единица измерения услуги
				if($alternative_name != ''){$query.="'".$alternative_name."', ";}else{$query.="NULL, ";}###Альтернативное название
				$query.="'".$cod."', ";###Код услуги
				$query.="".$cooldown.", ";###Длительность перерыва после услуги (в минутах)
				$query.="".$id_service_goal.", ";###ХЗ что это такое и нахрен оно нужно
				$query.="".$at_home.", ";###Услуга оказывается на дому
				$query.="".$at_clinic.", ";###Услуга оказывается в клинике
				$query.="".$deleted.", ";###Услуга удалена/скрыта
				if($briefname != ''){$query.="'".$briefname."', ";}else{$query.="NULL, ";}###Сокращенное наименование услуги
				if($com_class_journal != 'NULL'){$query.="'".$com_class_journal."', ";}else{$query.="".$com_class_journal.", ";}###Классификация услуги для журналов
				if($for_broods != 'NULL'){$query.="'".$for_broods."', ";}else{$query.="".$for_broods.", ";}###Флаг: услуга для выводков
				if($for_multiple != 'NULL'){$query.="'".$for_multiple."', ";}else{$query.="".$for_multiple.", ";}###Флаг: услуга для множественных приемов

				$query.="".$once_per_day.", ";###Флаг: услуга может оказываться раз в день
				$query.="'".$id_user."','".$id_user."', NOW()::timestamp(0), NOW()::timestamp(0)) RETURNING id;";

				$id_service=0;
				if($debug != 1){
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					$id_service=$row[0];
					pg_free_result($result);
				}
				//echo $query."\n\n";

				############
				$q_='SELECT * FROM description_types ';
				$q_.='WHERE description_types.entity_type=\'visit\'';
				$q_.='ORDER BY description_types.sort_by';
				
				$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
				while ($row_ = pg_fetch_assoc($result_)) {
					$name_=$_POST[$row_['tech_name']];
					$name__=$_POST[$row_['tech_name']."_"];
					$required_=$_POST[$row_['tech_name']."_required"];
					
					if($name_ == $row_['tech_name'] && $name__ == '1'){
						$query="INSERT INTO services_description_types ";
						$query.="(id_service, id_description_type, required) ";
						$query.="VALUES (";
						$query.="".$id_service.", ";###Услуга
						$query.="".$row_['id'].", ";
						if($required_ == '1'){
							$query.="true";
						}else{
							$query.="false";
						}
						$query.=");";
						if($debug != 1){
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$row = pg_fetch_row($result);
							pg_free_result($result);
						}

						echo $query."\n\n";
					}
				}
				pg_free_result($result_);
				###############

				###ОТЧЁТЫ
				#$q_='SELECT * FROM reports ';
				#$q_.='ORDER BY name';
				#$result_ = pg_query($q_) or die('Ошибка запроса: ' . pg_last_error());
				#while ($row_ = pg_fetch_assoc($result_)) {
				#	$id=$_POST['report_'.$row_['id']];
				#	if($id){
				#		echo 'отчёт'.$id;
				#	}
				#}
				#pg_free_result($result_);
				###ОТЧЁТЫ

				if($old_service){
					#########################################old_service
					echo '<br>Обновляем связь услуга+отчёт';
					echo '<ul>';
					//вначале считываем
					$query='SELECT * FROM gov_services_reports ';
					$query.='WHERE id_service='.$old_service.' ';
					$result_services_reports = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_services_reports = pg_fetch_assoc($result_services_reports)) {
						$query="INSERT INTO gov_services_reports ";
						$query.="(id_service, id_report, created_by, updated_by, created_at, updated_at) ";
						$query.="VALUES (".$id_service.", ".$row_services_reports['id_report'].",'".$id_user."','".$id_user."', NOW()::timestamp(0), NOW()::timestamp(0));";
						echo '<li>'.$query.'</li>';
						if($debug != 1){
							$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_);
						}
					}
					pg_free_result($result_services_reports);
					echo '</ul>';


					echo '<br>Обновляем связь услуга+отчёт+параметр отчёта';
					echo '<ul>';
					//вначале считываем
					$query='SELECT * FROM gov_services_params ';
					$query.='WHERE id_service='.$old_service.' ';
					$result_services_params = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_services_params = pg_fetch_assoc($result_services_params)) {
						if($row_services_params['req_in'] == 't'){$row_services_params['req_in']='true';}else{$row_services_params['req_in']='false';}
						if($row_services_params['req_out'] == 't'){$row_services_params['req_out']='true';}else{$row_services_params['req_out']='false';}
						if($row_services_params['flag_in'] == 't'){$row_services_params['flag_in']='true';}else{$row_services_params['flag_in']='false';}
						if($row_services_params['flag_out'] == 't'){$row_services_params['flag_out']='true';}else{$row_services_params['flag_out']='false';}

						$query="INSERT INTO gov_services_params ";
						$query.="(id_service, ";#1
						$query.="id_param, ";#2
						$query.="req_in, ";#3
						$query.="req_out, ";#4
						$query.="sort_by, ";#5
						$query.="flag_in, ";#6
						$query.="flag_out, ";#7
						$query.="created_by, ";#8
						$query.="updated_by, ";#9
						$query.="created_at, ";#10
						$query.="updated_at) ";#11
						$query.="VALUES (".$id_service.", ";#1
						$query.="".$row_services_params['id_param'].", ";#2
						$query.="".$row_services_params['req_in'].", ";#3
						$query.="".$row_services_params['req_out'].", ";#4
						if($row_services_params['sort_by'] != ''){
							$query.="".$row_services_params['sort_by'].", ";#5
						}else{
							$query.="NULL, ";
						}
						$query.="".$row_services_params['flag_in'].", ";#6
						$query.="".$row_services_params['flag_out'].",";#7
						$query.="'".$id_user."',";#8
						$query.="'".$id_user."'";#9
						$query.=", NOW()::timestamp(0), ";#10
						$query.="NOW()::timestamp(0));";#11
						echo '<li>'.$query.'</li>';
						if($debug != 1){
							$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_);
						}
					}
					pg_free_result($result_services_params);
					echo '</ul>';
					#########################################
				}

				header("Location: index.php?action=edit_service&id=".$id_service."&message=successfully");

				$query_delete="DELETE FROM public.visits_gov_services WHERE id_service=".$id_service.";<br>";###Удаляем из визитов
				$query_delete.="DELETE FROM public.gov_services WHERE created_by=".$id_user.";<br>";###Удаляем из услуг
				$query_delete.="DELETE FROM public.gov_services_reports WHERE id_service=".$id_service.";<br>";###Удаляем из отчётов
				$query_delete.="DELETE FROM public.gov_services_params WHERE id_service=".$id_service.";<br>";###Удаляем из параметров очтётов
				$query_delete.="DELETE FROM public.services_description_types WHERE id_service=".$id_service.";<br>";###Удаляем из входных параметров
				echo "<strong>Для отката услуги необходимо запустить такой скрипт:</strong><br>".$query_delete;
			}
		}else{
			if($debug != 1){
				header("Location: index.php?action=edit_service&id=".$id."&message=fields_not_filled");
			}else{
				echo 'Не заполнены необходимые поля!';
			}
		}
	}

	if($action == 'auth'){
		authUser($configuration); 
	}else if($action == 'exit'){
		exitUser($configuration);
	}else if($action == 'services'){
		if(vallidateToken($configuration)){
			show_services_pricelist_xml($configuration);
		}else{
			invalidToken($configuration);
		}
	}else if($action == 'specialists'){
		if(vallidateToken($configuration)){show_services_specialists_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_services_specialists'){
		if(vallidateToken($configuration)){save_services_specialists($configuration);}else{invalidToken($configuration);}
	}else if($action == 'update_pricelist'){
		if(vallidateToken($configuration)){update_pricelist($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_service'){
		if(vallidateToken($configuration)){save_service($configuration);}else{invalidToken($configuration);}
	}else if($action == 'upload_pricefile'){
		if(vallidateToken($configuration)){viewServicesUploadPriceFile($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notifications'){
    	if(vallidateToken($configuration)){show_notifications_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'owners'){
    	if(vallidateToken($configuration)){show_owners_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save'){
		
	
	}else if($action == 'update_visits_with_new_services'){
		$query.='SELECT visits.id AS id, visits.time_range, gov_services.id AS id_service, gov_services.cod, visits_gov_services.id AS visits_gov_services, visit_pets.id_pet AS pet FROM public.visits ';
		$query.='LEFT JOIN visit_pets ON visits.id=visit_pets.id_visit ';
		$query.='LEFT JOIN visits_gov_services ON visits.id=visits_gov_services.id_visit ';
		$query.='LEFT JOIN visit_service_pet ON visits_gov_services.id=visit_service_pet.id_visits_gov_service ';
		$query.='LEFT JOIN gov_services ON gov_services.id=visits_gov_services.id_service ';
		$query.='WHERE (tsrange(\'2022-01-01\'::DATE, \'2022-01-01\'::DATE + INTERVAL \'120 DAY\', \'[)\') @> visits.time_range) AND visits.time_range IS NOT NULL ';
		$query.='AND visits.created_at < \'2022-01-01 00:00:00\' AND visits.status=\'N\' AND gov_services.deleted=true AND gov_services.cod IS NOT NULL ';
		$query.='ORDER BY time_range ASC';

		echo '<ul>';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			$query='SELECT * FROM gov_services ';
			$query.='WHERE gov_services.cod=\''.$row['cod'].'\' AND deleted=false ORDER BY id ASC LIMIT 1';
			$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$new_row = pg_fetch_assoc($result_);
			pg_free_result($result_);
			
			echo '<li><strong>ВИЗИТ '.$row['id'].' '.$row['time_range'].' '.$row['visits_gov_services'].' - ЖИВОТНОЕ '.$row['pet'].' УСЛУГА '.$row['id_service'].'('.$row['cod'].')='.$new_row['id'].'('.$new_row['cod'].')</strong></li>';

			if($new_row['cod']){
				$query_delete='DELETE FROM visit_service_pet WHERE id_visits_gov_service='.$row['visits_gov_services'].'';
				$result_ = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result_);
				echo '<li>Удаляем связь '.$query_delete.'</li>';

				$query_delete='DELETE FROM visits_gov_services WHERE id_visit='.$row['id'].'';
				$result_ = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result_);
				echo '<li>Удаляем связь '.$query_delete.'</li>';

				#ДОБАВЛЯЕМ СВЯЗКУ ВИЗИТ+УСЛУГА+ЖИВОТНОЕ
				$query_add='INSERT INTO visits_gov_services ';
				$query_add.='(id_visit, id_service, id_pet, count, created_by, updated_by, created_at, updated_at) ';
				$query_add.='VALUES (';
				$query_add.="".$row['id'].",";#визит
				$query_add.="".$new_row['id'].",";#услуга
				$query_add.="".$row['pet'].",";#животное
				$query_add.="'1',";#животное
				$query_add.="".$id_user.",";#created_by
				$query_add.="".$id_user.",";#updated_by
				$query_add.="NOW()::timestamp(0),";#created_at
				$query_add.="NOW()::timestamp(0)";#updated_at
				$query_add.=') RETURNING id;';
				$result_ = pg_query($query_add) or die('Ошибка запроса: ' . pg_last_error());
				$row_ = pg_fetch_row($result_);
				$id_gov_visit=$row_[0];
				pg_free_result($result_);
				echo '<li>Добавляем связь '.$query_add.'</li>';

				#ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
				$query_add='INSERT INTO visit_service_pet ';
				$query_add.='(id_visits_gov_service, id_pet) ';
				$query_add.='VALUES (';
				$query_add.="".$id_gov_visit.",";
				$query_add.="".$row['pet']."";
				$query_add.=');';
				$result_ = pg_query($query_add) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result_);
				echo '<li>Добавляем связь '.$query_add.'</li>';
			}
			
		}
		pg_free_result($result);
		echo '</ul>';
	}else if ($action == 'recovery_password'){
		header_site(1, $configuration);
		viewRecoveryPassword('shelters');
		footer_site();
	}else{
		if($_COOKIE['token']){
			if(vallidateToken($configuration)){
				header_site(0,$configuration, 'Редактор услуг','service');
				sub_header_site($configuration);
				menu_site($configuration, 'service', $action);
				informings_site($configuration);

				if(userCan($configuration, 'sysAdminGos') || userCan($configuration, 'vetSpecGos')){
					if($action == 'services_specialists'){
						viewServicesSpecialists($configuration);
					}else if($action == 'upload_price' && userCan($configuration, 'sysAdminGos')){
						viewServicesUploadPrice($configuration);
					}else if($action == 'price' && userCan($configuration, 'sysAdminGos')){
						viewServicesPriceList($configuration);
					}else if($action == 'edit_service'){
						viewServicesEditService($configuration);
					}else{
						viewServices($configuration);
					}
				}else{
					viewAttention($configuration);
				}

				sub_footer_site($configuration);
				footer_site();
			}else{
				header_site(1,$configuration);
				viewAuthUser('service');
				footer_site();
			}
		}else{
			header_site(1,$configuration);
			viewAuthUser('service');
			footer_site();
		}
	}

	if (function_exists('pg_connect')) {
		pg_close($dbconn);
	}
	
?>