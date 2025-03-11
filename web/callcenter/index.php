<?php

/* CONFIG */
$configuration = require __DIR__ . '/config.php';
include_once 'functions.php';
include_once 'header.php';
include_once 'footer.php';
/* CONFIG */

if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());$result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());pg_free_result($result);}

if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}

function viewCallCenter($config){
    // echo '<div class="row main_row header">';
    // echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
	// echo '<div class="col-xl-8 col-lg-10">';
	// echo '<div class="header_sub row">';
    // echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 logo"><div class="logo_sub"></div></div>';
	// echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12 label b"></div>';
    // echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 d-table">';
    // echo '<div class="d-table-cell operator"></div>';
    // echo '<div class="d-table-cell align-middle text-center">'.$user_fullname.'<br><strong>Оператор колл-центра</strong></div>';
    // echo '<div class="d-table-cell align-middle text-center controls">';
    // echo '<div class="visits_button" title="Приёмы" onclick="showVisitsWindow();"></div>';
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
    echo '<form id="form" onsubmit="ListShowSlots(); return false;">';
    echo '<div class="row search_sub">';

    echo '<div class="legend">';
    echo '<div class="visit">Занято</div>';
    #echo '<div class="unavailable">Недоступно</div>';
    echo '<div class="insufficient_duration">Недоступно по времени</div>';
    echo '<div class="mos">Слот доступен на mos.ru</div>';
    echo '<div class="free">Свободно</div>';
    echo '</div>';

    echo '<div class="d-inline-flex">';show_dates($config);echo '</div>';
    echo '<div class="d-inline-flex">';show_times($config);echo '</div>';
    echo '<div class="d-inline-flex">';show_numbers($config);echo '</div>';
    echo '<div class="d-inline-flex">';show_metrostations($config);echo '</div>';
    echo '<div class="d-inline-flex">';show_areas($config,0);echo '</div>';
    echo '<div class="d-inline-flex">';show_organizations($config,0,1);echo '</div>';
    echo '<div class="d-inline-flex">';show_specialists($config,0,1);echo '</div>';
    echo '<div class="d-inline-flex">';show_services($config,1,1);echo '</div>';
    echo '<div class="d-inline-flex">';show_specializations($config,0,1);echo '</div>';
    echo '<div class="d-inline-flex buttons"><button type="reset" onclick="resetForm();" style="width: 100px;">Сброс</button><button type="submit" style="margin-left: 5px; width: 120px;">Поиск</button></div>';

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

    echo '<script>';
    echo '$(document).ready(function () {';
    echo 'ListShowSlots();';
    #echo 'ListShowVisits();';
    echo '});';
    echo '</script>';
}

function make_booking($configuration){
    $date=$_GET["date"];
    $time=$_GET["time"];
    $org_id=$_GET["org_id"];
    $specialist_id=$_GET["spec_id"];
    $services=$_GET["services"];

    list($services_fulltime,$services_duration,$services_cooldown, $services_name) = calculate_services_time($configuration, $services);
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user=pg_fetch_result($result, 0);
    pg_free_result($result);

    $query='SELECT MAX(number) FROM visits WHERE id_organization=\''.$org_id.'\' AND start_dttm BETWEEN \''.$date.' 00:00:00\' AND \''.$date.' 00:00:00\'::DATE + INTERVAL \'1 DAY\'';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $counter=pg_fetch_result($result, 0);
    $counter=$counter+1;
    pg_free_result($result);

    $query='INSERT INTO visits ';
    $query.='(status, id_organization, created_by, updated_by, created_at, updated_at, cooldown, time_range, channel, duration, number, ticket_number, start_dttm, time_range_without_cooldown, type, variety) ';
    $query.='VALUES (';
    $query.="'B',";###блокировка
    $query.="".$org_id.",";#Организация
    $query.="".$id_user.",";#created_by
    $query.="".$id_user.",";#updated_by
    $query.="NOW()::timestamp(0),";#created_at
    $query.="NOW()::timestamp(0),";#updated_at
    $query.="".$services_cooldown.",";#cooldown
    $query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_fulltime.' MINUTES\'), \'[)\'),';#time_range["2021-12-02 14:00:00","2021-12-02 14:20:00")
    $query.="3,";#channel - по умолчанию 3 - телефон
    $query.="".$services_duration.",";#duration
    $query.="".$counter.",";#number - по порядку по суткам
    $query.="'000000',";#ticket_number - 023001 - [02]дата[3]-канал[001]-номер
    $query.="'".$date." ".$time.":00',";#start_dttm 2021-12-02 14:00:00
    $query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_duration.' MINUTES\'), \'[)\'),';#time_range_without_cooldown["2021-12-02 14:00:00","2021-12-02 14:20:00")
    $query.="'VISIT',";#type
    $query.="'SINGLE'";#variety
    $query.=') RETURNING id;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $id_visit=$row[0];
    pg_free_result($result);

    if($configuration['version'] == '1.0'){
        visitlogUser($id_visit, 'B');
    }

    #ДОБАВЛЯЕМ СПЕЦИАЛИСТА К ВИЗИТУ
    $query='INSERT INTO visits_specialists ';
    $query.='(id_specialist, id_visit, created_by, updated_by, created_at, updated_at) ';
    $query.='VALUES (';
    $query.="".$specialist_id.",";
    $query.="".$id_visit.",";
    $query.="".$id_user.",";#created_by
    $query.="".$id_user.",";#updated_by
    $query.="NOW()::timestamp(0),";#created_at
    $query.="NOW()::timestamp(0)";#updated_at
    $query.=');';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    $message='<message>booking_created</message>';
    $message.='<id>'.$id_visit.'</id>';
    echo xml($message);
}

function delete_booking($configuration){
    $id_visit=$_GET["visit"];

    if($configuration['version'] == '1.0'){
        visitlogUser($id_visit, 'A');
    }

    $query_delete='DELETE FROM public.visits WHERE id='.$id_visit.' AND status=\'B\'';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    $query_delete='DELETE FROM public.visits_specialists WHERE id_visit='.$id_visit.'';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    $message='<message>booking_deleted</message>';
    $message.='<id>'.$id_visit.'</id>';
    echo xml($message);
}

function cancel_booking_by_time(){
    $q='SELECT * FROM visits WHERE created_at < NOW() - interval \'5\' MINUTE AND status = \'B\'';
    $result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $query_delete='DELETE FROM public.visits_specialists WHERE id_visit='.$row['id'].'';
        $result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
        pg_free_result($result_delete);
    }
    pg_free_result($result);

    $query_delete='DELETE FROM visits WHERE created_at < NOW() - interval \'5\' MINUTE AND status = \'B\'';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);
}

if($action == 'auth'){
    authUser($configuration);
}else if($action == 'recovery_password'){
    header_site(1, $configuration);
    viewRecoveryPassword('shelters');
    footer_site();
}else if($action == 'recovery'){
    recoveryPasswordUser($configuration);
}else if($action == 'exit'){
    exitUser($configuration);
}else if($action == 'owners'){
    if(vallidateToken($configuration)){show_owners_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'visits'){
    if(vallidateToken($configuration)){show_visits_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'contacts'){
    if(vallidateToken($configuration)){show_contacts_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'save_contacts'){
    if(vallidateToken($configuration)){save_contacts($configuration);}else{invalidToken($configuration);}
}else if($action == 'cancel_visit'){
    if(vallidateToken($configuration)){cancel_visit($configuration);}else{invalidToken($configuration);}
}else if($action == 'slots'){
    if(vallidateToken($configuration)){show_slots_xml($configuration);}else{invalidToken($configuration);}}
else if($action == 'schedule'){
    if(vallidateToken($configuration)){show_schedule_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'services'){
    if(vallidateToken($configuration)){show_services_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'species'){
    if(vallidateToken($configuration)){show_species_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'breeds'){
    if(vallidateToken($configuration)){show_breeds_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'templates'){
    if(vallidateToken($configuration)){show_templates_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'specialists'){
    if(vallidateToken($configuration)){show_specialists_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'make_booking'){
    if(vallidateToken($configuration)){make_booking($configuration);}else{invalidToken($configuration);}
}else if($action == 'delete_booking'){
    if(vallidateToken($configuration)){delete_booking($configuration);}else{invalidToken($configuration);}
}else if($action == 'reschedule_appointment'){
    if(vallidateToken($configuration)){reschedule_appointment($configuration);}else{invalidToken($configuration);}    
}else if($action == 'make_appointment'){
    if(vallidateToken($configuration)){make_appointment($configuration);}else{invalidToken($configuration);}
}else if($action == 'metrostations' && vallidateToken($configuration)){
    createMetroStations($configuration);
}else if($action == 'createmessages' && vallidateToken($configuration)){
    createMessages($configuration);
}else if($action == 'notification_read'){
    if(vallidateToken($configuration)){read_notification_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'notifications'){
    if(vallidateToken($configuration)){show_notifications_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'addresses' && $mode == 'xml'){
    if(vallidateToken($configuration)){show_addresses_xml($configuration);}else{invalidToken($configuration);}
}else if($action == 'fias_tokken' && $mode == 'xml'){if(vallidateToken($configuration)){$fias_tokken=fias_request($configuration);header("Content-type: text/xml; charset=utf-8");echo '<?xml version="1.0" encoding="UTF-8"?>';echo '<xml>';echo '<fias_tokken>'.$fias_tokken->access_token.'</fias_tokken>';echo '</xml>';}else{invalidToken($configuration);}
}else{
    if($_COOKIE['token']){
        if(vallidateToken($configuration)){
            header_site(0,$configuration, 'Контактный центр','callcenter');
			sub_header_site($configuration);
			menu_site($configuration, 'callcenter', $action);

            informings_site($configuration);
			//пользователь авторизован//

			viewCallCenter($configuration);
            viewRescheduleWindow($configuration);
								
			sub_footer_site();
			footer_site();

            // header_site(0,$configuration, 'Контактный центр');
            // viewCallCenter($configuration);
            // viewRescheduleWindow($configuration);
            // footer_site();
        }else{
            header_site(1,$configuration, 'Контактный центр');
            viewAuthUser('callcenter');
            footer_site();
        }
    }else{
        header_site(1,$configuration, 'Контактный центр');
        viewAuthUser('callcenter');
        footer_site();
    }
}

if (function_exists('pg_connect')) {
    pg_close($dbconn);
}

?>
