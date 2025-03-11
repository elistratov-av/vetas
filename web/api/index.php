<?php

$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';

include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';

if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());}

if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}
$mode='json';

function api_geo_json($configuration){
    header('Content-Type: application/vnd.api+json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header('Connection: close');
    header('X-Powered-By: PHP/7.3.33');

    //
    $id_user=string_formating_for_sql($_GET["id_user"]);
    $id_org=string_formating_for_sql($_GET["id_org"]);
    $lat=string_formating_for_sql($_GET["lat"]);
    $lon=string_formating_for_sql($_GET["lon"]);

    if($id_user && $id_org && $lat && $lon){
        //вычисляем какая бригада движется
        $query='SELECT brigades_specialists.id_brigade FROM specialists ';
        $query.='LEFT JOIN brigades_specialists ON specialists.id=brigades_specialists.id_specialist ';
        $query.='WHERE specialists.id_user='.$id_user.' AND specialists.id_organization='.$id_org.'';

        $brigades='';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            if($brigades){$brigades.=",";}
            $brigades.=$row["id_brigade"];
        }
        pg_free_result($result);


        $query='SELECT visits.id_request, brigades.name AS brigade, brigades.id AS brigade_id, ';
        $query.='visits.status, visits.time_range, visits.id_owner, visits.created_at, visits.visit_to_address, ';
        $query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,(UPPER(VISITS.TIME_RANGE)::time) AS end_time, ';
        $query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, start_request_date, end_request_date, fact_start_dttm, fact_end_dttm ';
        $query.='FROM visits ';

        // $query.='LEFT JOIN fias_addresses ON visits.visit_to_address=fias_addresses.full_address ';
        $query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
        $query.='LEFT JOIN brigades ON brigades.id=visits.id_brigade ';
        $query.='LEFT JOIN brigades_specialists ON brigades.id=brigades_specialists.id_brigade ';
        $query.='LEFT JOIN specialists ON specialists.id=brigades_specialists.id_specialist ';
        $query.='LEFT JOIN users ON users.id=specialists.id_user ';

        $query.='WHERE visits.channel=10 AND visits.id_organization='.$id_org.' ';

        $query.='AND ((LOWER(VISITS.TIME_RANGE)::date)) >= NOW()::date ';//+ (LOWER(VISITS.TIME_RANGE)::time))

        $query.="AND (visits.status='P' OR visits.status='W') ";
        $query.="AND (brigades.id IN (".$brigades.")) ";
        $query.='GROUP BY pet_owners.id, visits.id, visits.id_request, brigades.name, brigades.id ';
        $query.='ORDER BY visits.time_range ';
        //echo $query;
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $brigades_='';
        while ($row = pg_fetch_assoc($result)) {
            // if($brigades_){$brigades_.=",";}
            // $brigades_.=$row["brigade_id"];

            /////////////////////
            $query_update='UPDATE brigades SET coordinates=POINT(\''.$lon.', '.$lat.'\') WHERE brigades.id='.$row["brigade_id"].' ';
            //echo $query;
            $result_update = pg_query($query_update) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result_update);
            /////////////////////

            $coord=fias_search_coordinates($row["visit_to_address"]);

            $query='SELECT ';
            $query.='ST_DistanceSphere(brigades.coordinates::geometry, ST_GeomFromText(\'POINT('.$coord[0]." ".$coord[1].')\')) AS distance, ';
            $query.='visits.status, visits.id AS visit_id, brigades.name AS brigade, brigades.id AS brigade_id, brigades.car_model AS car_model, ';
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
            $query.="AND (visits.status='P') AND ST_DistanceSphere(brigades.coordinates::geometry, ST_GeomFromText('POINT(".$coord[0]." ".$coord[1].")')) <= 100 AND brigades.id IN (".$brigades.") ";
            $query.='GROUP BY pet_owners.id, visits.id, visits.id_request, brigades.name, brigades.id ';
            //echo $query;
            $result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            while ($row_ = pg_fetch_assoc($result_)) {
                $query_update='UPDATE visits SET status=\'W\' WHERE id='.$row_['visit_id'].' ';
                $result_update = pg_query($query_update) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result_update);
            }
            pg_free_result($result_);
        }
        pg_free_result($result);

        //echo $brigades_;
        if($brigades_ != ''){
            // //записываем данные
            $query='UPDATE brigades SET coordinates=POINT(\''.$lon.', '.$lat.'\') WHERE brigades.id IN ('.$brigades_.') ';
            //echo $query;
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);

            ///////////////////////////////////////////////////////

            // $fias_tokken=fias_request($configuration);
            //echo $fias_tokken->access_token;
            // $coord=fias_search_coordinates();
            // echo print_r($fias_address->data, true);

            $query='SELECT ';
            $query.='ST_DistanceSphere(brigades.coordinates::geometry, ST_GeomFromText(\'POINT('.$lon.' '.$lat.')\')) AS distance, ';
            $query.='visits.status, visits.id AS visit_id, brigades.name AS brigade, brigades.id AS brigade_id, brigades.car_model AS car_model, ';
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
            $query.="AND (visits.status='P') AND ST_DistanceSphere(brigades.coordinates::geometry, ST_GeomFromText('POINT(".$lon." ".$lat.")')) <= 100 AND brigades.id IN (".$brigades_.") ";
            $query.='GROUP BY pet_owners.id, visits.id, visits.id_request, brigades.name, brigades.id ';
            //echo $query;
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $brigades_='';
            while ($row = pg_fetch_assoc($result)) {
                // $query='UPDATE visits SET status=\'W\' WHERE id='.$row['visit_id'].' ';
                // $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                // $row = pg_fetch_row($result);
                // pg_free_result($result);
            }
            pg_free_result($result);
            ///////////////////////////////////////////////////////
        }
    }

    echo '{"result": "OK","error": 0}';
}

function api_read_notification_json(){
    $id_notification=$_GET["id_notification"];
    $id_user=string_formating_for_sql($_GET["id_user"]);

    $query='UPDATE notifications SET read=\'t\' WHERE id_user='.$id_user.' AND id='.$id_notification.' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    pg_free_result($result);


    header('Content-Type: application/vnd.api+json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header('Connection: close');
    header('X-Powered-By: PHP/7.3.33');

    $data='{';
    $data.='"ok":"1"';
    $data.='}';

    echo $data;
}

function api_notifications_json(){
    //чистим старые уведомления старше 7 дней
    delete_notifications_by_time();

    header('Content-Type: application/vnd.api+json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header('Connection: close');
    header('X-Powered-By: PHP/7.3.33');

    $id_user=string_formating_for_sql($_GET["id_user"]);
    $token=string_formating_for_sql($_GET["token"]);
    $id_org = string_formating_for_sql($_GET["id_org"]);

    $query = 'SELECT count(*) FROM public.specialists ';
    $query .= "LEFT JOIN public.auth_assignment ON public.auth_assignment.id_specialist = public.specialists.id WHERE public.specialists.id_user = $id_user  AND public.specialists.id_organization = $id_org AND public.auth_assignment.item_name IN ('sysAdminGos', 'managementGos')";
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $role = pg_fetch_result($result, 0);
    //role = 1 - администратор, то видит только уведомления о Подтверждении оплаты
    if ($role>=1){

        $query='SELECT * FROM notifications ';
        $query .= 'WHERE id_user=\'' . $id_user . '\' AND title=\'Прием завершен без оплаты\' ORDER BY created_at DESC';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

    }
    else{
        $query = 'SELECT * FROM notifications ';
        $query .= 'WHERE id_user=\'' . $id_user . '\' AND title!=\'Прием завершен без оплаты\' ORDER BY created_at DESC';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    }

    $data='';
    while ($row = pg_fetch_assoc($result)) {
        if($data){
            $data.=',';
        }
        $data.='{';
        $data.='"id":"'.$row['id'].'",';
        $data.='"title":"'.$row['title'].'",';
        $data.='"text":"'.$row['text'].'",';
        $data.='"datetime":"'.date_format(new \DateTime($row['created_at']), "d.m.Y в H:i").'",';
        $data.='"read":"'.$row['read'].'"';
        $data.='}';

    }
    pg_free_result($result);

    // $query='UPDATE notifications SET read=\'t\' WHERE id_user='.$id_user.'';
    // $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    // $row = pg_fetch_row($result);
    // pg_free_result($result);
    $data = '['.$data.']';
    echo $data;
}

function api_count_notifications_json(){
    $id_user=intval(string_formating_for_sql($_GET["id_user"]));

    //чистим старые уведомления старше 7 дней
    delete_notifications_by_time();

    header('Content-Type: application/vnd.api+json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header('Connection: close');
    header('X-Powered-By: PHP/7.3.33');

    $id_user=intval($_GET["id_user"]);
    $token=string_formating_for_sql($_GET["token"]);
    $id_org = intval($_GET["id_org"]);

    $notifications = 0;
    if($id_user != '' && $id_org != ''){
        $query = 'SELECT count(*) FROM public.specialists ';
        $query .= "LEFT JOIN public.auth_assignment ON public.auth_assignment.id_specialist = public.specialists.id WHERE public.specialists.id_user = $id_user  AND public.specialists.id_organization = $id_org AND public.auth_assignment.item_name IN ('sysAdminGos', 'managementGos')";
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $role = pg_fetch_result($result, 0);

        //role = 1 - администратор, то считаем только уведомления о Подтверждении оплаты
        if ($role >= 1){
            $query='SELECT COUNT(*) FROM notifications ';
            $query .= 'WHERE id_user=\'' . $id_user . '\' AND read=\'f\'  AND title=\'Прием завершен без оплаты\' ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $notifications = pg_fetch_result($result, 0);
            pg_free_result($result);
        }else{
            $query = 'SELECT COUNT(*) FROM notifications ';
            $query .= 'WHERE id_user=\'' . $id_user . '\' AND read=\'f\' AND title!=\'Прием завершен без оплаты\' ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $notifications=pg_fetch_result($result, 0);
            pg_free_result($result);
        }
    }

    $data='{';
    $data.='"count":"'.$notifications.'"';
    $data.='}';

    echo $data;
}

function api_check_visit_notification_json(){
    $id_user=string_formating_for_sql($_GET["userId"]);
    $token=string_formating_for_sql($_GET["token"]);
    $id_visit=string_formating_for_sql($_GET["id_visit"]);
}

function api_find_owner_by_sso_id($data){
    $sso_id = $data['ids']['SSO'][0]['value'];

    //поиск по SSO ID
    //'is_deleted' => false
    $query='SELECT id_owner, id FROM elk.owners WHERE elk.owners.sso_id=\''.string_formating_for_sql($sso_id).'\' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row_o = pg_fetch_assoc($result);
    pg_free_result($result);

    if($row_o['id_owner']){
        //найден пользователь
        $id_owner = $row_o['id_owner'];

        $query='SELECT id_main_owner FROM pet_owners WHERE pet_owners.id='.$id_owner.' ';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row_o_ = pg_fetch_assoc($result);
        pg_free_result($result);

        if($row_o_['id_main_owner']){
            return $row_o['id_main_owner'];
        }else{
            return $id_owner;
        }
    }else{
        //не найден пользователь
        return false;
    }
}

function api_find_elk_owner_by_sso_id($data){
    $sso_id = $data['ids']['SSO'][0]['value'];


    $query='SELECT id FROM elk.owners WHERE elk.owners.sso_id=\''.string_formating_for_sql($sso_id).'\' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row_o = pg_fetch_assoc($result);
    pg_free_result($result);

    return $row_o['id'];
}

function api_find_owner($data){
    $snils = $data['documents']['doc_snils'][0]['value'];
    $sso_id = $data['ids']['SSO'][0]['value'];

    $first_name = $data['first_name'];
    $middle_name = $data['middle_name'];
    $last_name = $data['last_name'];

    $email = $data['contacts']['contact_email'][0]['value'];
    $mobile_phone = $data['contacts']['contact_mobile_registration'][0]['value'];
    $home_phone = $data['contacts']['contact_home_phone'][0]['value'];

    //поиск по СНИЛС
    $query='SELECT id FROM pet_owners WHERE snils=\''.string_formating_for_sql($snils).'\' AND is_deleted = false';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row_o = pg_fetch_assoc($result);
    pg_free_result($result);

    if($row_o['id']){
        $id_owner = $row_o['id'];

        //SSO ID не создано, надо создать
        $query='INSERT INTO elk.owners (sso_id, id_owner, first_name, last_name, middle_name, phone, email, snils, created_at, updated_at) ';
        $query.='VALUES (\''.$sso_id.'\','.$id_owner.',\''.$first_name.'\',\''.$last_name.'\',\''.$middle_name.'\',\'+7'.$mobile_phone.'\',\''.$email.'\',\''.$snils.'\', NOW()::timestamp(0), NOW()::timestamp(0)) RETURNING id;';
        //echo "\n".$query;
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        pg_free_result($result);

        //и обновить найденному по СНИЛС

        return $id_owner;
    }
    ###########

    //Поиск по ФИО без SSO ID c телефоном и почтой -- lower(f_fio)
    ####

    // Если в заявке передан СНИЛС и пользователя по нему не найдено (см. выше),
    // то ищем пользователя у которого СНИЛС не заполнен

    //extract_mosru_phonenumber
}

function api_fias_mosru_json($address_street, $address_house, $address_flat, $configuration){
    //ФИАС//address_street//address_house//address_flat
    $fias_tokken=fias_request($configuration);
    $fias_address=fias_search_address($fias_tokken->access_token, 'Город Москва,'.$address_street.','.$address_house.'', $configuration);

    if($address_street && $address_house){
        $room_fias_id='';
        $room_title='';
        $id_fias_from_table='';
        if($fias_address->data->house_fias_id && $fias_address->data->region_fias_id && $fias_address->data->street_fias_id){
            if($address_flat){
                $fias_address_room=fias_search_room($fias_tokken->access_token, $fias_address->data->house_fias_id, $address_flat, $configuration);
                $room_fias_id=$fias_address_room->data->room_fias_id;
                $room_title=$fias_address_room->data->flat;
            }

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
            if($fias_address_room->data->room_fias_id){
                $query.='roomguid=\''.string_formating_for_sql($fias_address_room->data->room_fias_id).'\' ';
            }else{
                $query.='roomguid IS NULL ';
            }
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $id_fias_from_table=pg_fetch_result($result, 0);
            pg_free_result($result);
        }

        if(!$id_fias_from_table){//fias адрес не найден в таблице
            $full_address='';
            if($room_title){
                $full_address='город '.$fias_address->data->region.', улица '.$fias_address->data->street.', дом '.$fias_address->data->house.', квартира '.$room_title.'';
            }else{
                $full_address='город '.$fias_address->data->region.', улица '.$fias_address->data->street.', дом '.$fias_address->data->house.'';
            }

            $lon=$fias_address->data->polygon->coordinates[0][0][0][0];
            $lat=$fias_address->data->polygon->coordinates[0][0][0][1];

            if($fias_address->data->region){
                $street_prefix="";
                if($fias_address->data->street_type == 'ш'){$street_prefix='шоссе';}else{$street_prefix='улица';}

                $query='INSERT INTO fias_addresses ';
                $query.='(full_address, lon, lat, region, city, street, house, room, cityguid, streetguid, houseguid, roomguid, regionguid, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="'".$full_address."',";

                if($lon){$query.="'".$lon."',";}else{$query.="NULL,";}
                if($lat){$query.="'".$lat."',";}else{$query.="NULL,";}

                $query.="'город ".$fias_address->data->region."',";
                $query.="'город ".$fias_address->data->region."',";
                if($fias_address->data->street){$query.="'".$street_prefix." ".$fias_address->data->street."',";}else{$query.="NULL,";}
                if($fias_address->data->house){$query.="'дом ".$fias_address->data->house."',";}else{$query.="NULL,";}

                if($room_title){
                    $query.="'квартира ".$fias_address->data->house."',";
                }else{
                    $query.="NULL,";
                }

                $query.="'".$fias_address->data->region_fias_id."',";
                $query.="'".$fias_address->data->street_fias_id."',";
                $query.="'".$fias_address->data->house_fias_id."',";
                if($room_fias_id){
                    $query.="'".$room_fias_id."',";
                }else{
                    $query.="NULL,";
                }

                $query.="77,";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=') RETURNING id;';
                $result = pg_query($query) or die('Ошибка запроса fias_addresses: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $id_fias_from_table=$row[0];
                pg_free_result($result);
            }
        }
        //ФИАС

        return $id_fias_from_table;
    }else{
        return false;
    }
}

function api_save_mosru_json($configuration){
    //МДМ
    $login=$_POST["login"];
    //ключ на подключение?!?

    //считываем пользователя

    $json = file_get_contents('php://input');

    $data = json_decode($json, TRUE);

    $snils = $data['documents']['doc_snils'][0]['value'];
    $sso_id = $data['ids']['SSO'][0]['value'];

    $first_name = $data['first_name'];
    $middle_name = $data['middle_name'];
    $last_name = $data['last_name'];

    $email = $data['contacts']['contact_email'][0]['value'];
    $mobile_phone = $data['contacts']['contact_mobile_registration'][0]['value'];
    $home_phone = $data['contacts']['contact_home_phone'][0]['value'];

    header('Content-Type: application/json; charset=utf-8');

    //обязательные параметры
    //'sso_id', 'last_name', 'first_name'
    //'pet_id', 'species'

    //вернуть логирование?

    // echo $first_name;
    // echo $middle_name;
    // echo $last_name;


    //$sso_id + признак удаления
    //стираем из elk таблицы

    if($sso_id && $first_name && $last_name){
        $id_owner = api_find_owner_by_sso_id($data);
        $id_elk_owner = '';

        if(!$id_owner){
            //SSO ID не найден ищем по другим параметрам
            $id_owner = api_find_owner($data);
        }else{
            $id_owner = $id_owner;
        }
        $id_elk_owner = api_find_elk_owner_by_sso_id($data);

        //echo $id_owner;
        //echo $id_elk_owner;

        $pets_array = array();
        if($id_owner){
            //echo '{"result": "'.$id_owner.'","error": 0}';
            //1. пользователь есть
            //1.1 проверяем что поменялось
            //1.2 проверяем состав животных, кто добавился кто удалился

            ///Формируем список животных владельца///
            $query='SELECT ';
            $query.='elk.pets.ext_id AS pet_id, ';
            $query.='chip.identification_code AS chip_title, ';
            $query.='label.identification_code AS kleimo_title, ';
            $query.='public.pets.id AS id, public.pets.name, public.pets.birthday AS birthday, public.pets.characteristics AS characteristics, public.pets.representatives AS representatives, public.pets.sex, public.pets.castrated, public.pets.mosru_guide_dog, breeds.name AS breed_name, species.name AS species_name FROM pets ';
            $query.='LEFT JOIN pets_to_owner ON pets_to_owner.id_pet=pets.id ';
            $query.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';
            $query.='LEFT JOIN species ON pets.id_species = species.id ';
            $query.='LEFT JOIN elk.pets ON elk.pets.id_pet = public.pets.id ';
            $query.='LEFT JOIN public.pet_identification AS chip ON chip.id_pet=public.pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
            $query.='LEFT JOIN public.pet_identification AS label ON label.id_pet=public.pets.id AND label.id = (SELECT max(l1.id) FROM public.pet_identification l1 WHERE label.id_pet = l1.id_pet AND l1.id_ident_type=2) ';
            $query.='WHERE (public.pets.is_main=true OR public.pets.is_main IS NULL) AND public.pets.reg_expire_date IS NULL AND (pets_to_owner.id_owner='.$id_owner.')';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            while ($row = pg_fetch_assoc($result)) {
                if($row['pet_id']){
                    array_push($pets_array, array('id' => $row['id'], 'name' => $row['name'], 'chip_number' => $row['chip_title'], 'stamp_number' => $row['kleimo_title'], 'birthday' => $row['birthday'], 'castrated' => $row['castrated'], 'mosru_guide_dog' => $row['mosru_guide_dog'], 'characteristics' => $row['characteristics'], 'representatives' => $row['representatives'], 'pet_id' => $row['pet_id'], 'delete' => 1, 'add' => 0));
                }else{
                    array_push($pets_array, array('id' => $row['id'], 'name' => $row['name'], 'chip_number' => $row['chip_title'], 'stamp_number' => $row['kleimo_title'], 'birthday' => $row['birthday'], 'castrated' => $row['castrated'], 'mosru_guide_dog' => $row['mosru_guide_dog'], 'characteristics' => $row['characteristics'], 'representatives' => $row['representatives'], 'pet_id' => $row['pet_id'], 'delete' => 0, 'add' => 0));
                }
            }
            pg_free_result($result);
            ///Формируем список животных владельца///
            api_pets_mosru_json($data, $pets_array, $id_owner, $id_elk_owner, $configuration);

            //чип/метка
            //идём в фиас за адресом
        }else{
            //2.владельца нет

            //создаём нового владельца

            ####################################
            #2.1. создаём владельца+таблица елк#
            ####################################
            $query='INSERT INTO pet_owners (f_fio, i_fio, o_fio, snils, sso_id, created_at, updated_at) ';
            $query.='VALUES (\''.mb_ucfirst(mb_strtolower($data['last_name'])).'\',\''.mb_ucfirst(mb_strtolower($data['first_name'])).'\',\''.mb_ucfirst(mb_strtolower($data['middle_name'])).'\',\''.$snils.'\',\''.$sso_id.'\', NOW()::timestamp(0), NOW()::timestamp(0)) RETURNING id;';
            //echo $query;
            $result = pg_query($query) or die('Ошибка запроса pet_owners: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_owner=$row[0];
            pg_free_result($result);

            $query='INSERT INTO elk.owners (sso_id, id_owner, first_name, last_name, middle_name, phone, email, snils, created_at, updated_at) ';
            $query.='VALUES (\''.$sso_id.'\','.$id_owner.',\''.$first_name.'\',\''.$last_name.'\',\''.$middle_name.'\',\''.$mobile_phone.'\',\''.$email.'\',\''.$snils.'\', NOW()::timestamp(0), NOW()::timestamp(0)) RETURNING id;';
            //echo "\n".$query;
            $result = pg_query($query) or die('Ошибка запроса elk.owners: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_elk_owner=$row[0];
            pg_free_result($result);
            ####################################
            #2.1. создаём владельца+таблица елк#
            ####################################

            //добавляем в таблицу контакты телефон+мыло
            #КОНТАКТЫ
            if($mobile_phone){
                $query='INSERT INTO contacts ';
                $query.='(id_contact_type, entity_type, entity_id, name, created_at, updated_at, main_flag, confirmed) ';
                $query.='VALUES (';
                $query.="1,";//id_contact_type
                $query.="'pet_owner',";//entity_type
                $query.="".$id_owner.",";//entity_id
                $query.="'+7".trim($mobile_phone)."',";//name
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0),";#updated_at
                $query.="true,";
                $query.="true";
                $query.=');';
                $result = pg_query($query) or die('Ошибка запроса contacts mobile phone: ' . pg_last_error());
                pg_free_result($result);
            }
            if($email){
                $query='INSERT INTO contacts ';
                $query.='(id_contact_type, entity_type, entity_id, name, created_at, updated_at, main_flag, confirmed) ';
                $query.='VALUES (';
                $query.="6,";
                $query.="'pet_owner',";
                $query.="".$id_owner.",";
                $query.="'".trim($email)."',";
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0),";#updated_at
                $query.="true,";
                $query.="true";
                $query.=');';
                $result = pg_query($query) or die('Ошибка запроса contacts email: ' . pg_last_error());
                pg_free_result($result);
            }
            #КОНТАКТЫ
            ####################################
            ####################################

            //echo '{"result": "Error","error": 400, "error_message": "Владелец с указаным SSO ID не найден."}';

            //2.2. создаём питомцев+таблица елк

            //формируем список животных для сравнения
            api_pets_mosru_json($data, $pets_array, $id_owner, $id_elk_owner, $configuration);
        }

        echo '{"result": "OK","error": 0}';
    }else{
        echo '{"result": "Error","error": 400, "error_message": "Не заполнены необходимые поля."}';
    }

    //echo '{"result": "Error","error": 400, "error_message": "\n- Не передан параметр First Name\n- Не передан параметр Last Name\n- Не передан параметр Sso Id\n- Не передан параметр Pet Id\n"}';
}

function api_pets_mosru_json($data, $pets_array, $id_owner, $id_elk_owner, $configuration){
    ///Формируем список животных владельца///


    //идём в фиас за адресом
    //1.3 удаленым признак не выводить на мос.ру в предложки, если есть приёмы
    //echo count($data['pets']);
    for ($k=0; $k<count($data['pets']); $k++) {
        if($data['pets'][$k]['pet_id'] && $data['pets'][$k]['deleted'] == 'true'){
            //удаление питомца с причиной
            for ($j=0; $j<count($pets_array); $j++) {
                if($pets_array[$j]['pet_id'] == $data['pets'][$k]['pet_id']){
                    $pets_array[$j]['delete'] = 1;
                    $pets_array[$j]['delete_reason_id'] = $data['pets'][$k]['delete_reason_id'];
                }
            }

            continue;
        }

        $flag = 1;
        for ($j=0; $j<count($pets_array); $j++) {
            if($pets_array[$j]['pet_id'] == $data['pets'][$k]['pet_id']){
                $pets_array[$j]['delete'] = 0;
                $flag = 0;
            }
        }

        $representatives='[{"name": "'.$data['pets'][$k]['owner_name1'].'","patronymic": "'.$data['pets'][$k]['owner_patronymic1'].'", "surname": "'.$data['pets'][$k]['owner_surname1'].'","email": "'.$data['pets'][$k]['owner_email1'].'","phone": "'.$data['pets'][$k]['owner_phone1'].'"},{"name": "'.$data['pets'][$k]['owner_name2'].'","patronymic": "'.$data['pets'][$k]['owner_patronymic2'].'", "surname": "'.$data['pets'][$k]['owner_surname2'].'","email": "'.$data['pets'][$k]['owner_email2'].'","phone": "'.$data['pets'][$k]['owner_phone2'].'"},{"name": "'.$data['pets'][$k]['owner_name3'].'","patronymic": "'.$data['pets'][$k]['owner_patronymic3'].'", "surname": "'.$data['pets'][$k]['owner_surname3'].'","email": "'.$data['pets'][$k]['owner_email3'].'","phone": "'.$data['pets'][$k]['owner_phone3'].'"}]';

        if($flag == 1){
            $flag_ = 1;
            //переносим логику мапинга по кличке
            for ($j=0; $j<count($pets_array); $j++) {
                //проходим по кличкам
                //они должны быть уникальные в ЛК
                if($pets_array[$j]['name'] == $data['pets'][$k]['name']){
                    // && $pets_array[$j]['pet_id'] == ''  - перепроверить/подумать

                    //нашли такую же
                    //но pet_id может быть уже в ЛК
                    if($flag_ == 1){
                        $pets_array[$j]['add_elk'] = 1;
                        $pets_array[$j]['pet_id'] = $data['pets'][$k]['pet_id'];
                        $flag_ = 0;
                    }
                }
            }
            //переносим логику мапинга по кличке
            //добавляем в ЛК и ПЕТС
            if($flag_ == 1 ||  $pets_array == null){
                array_push($pets_array, array(
                    'name' => $data['pets'][$k]['name'],
                    'pet_id' => $data['pets'][$k]['pet_id'],

                    'chip_number' => $data['pets'][$k]['chip_number'],
                    'stamp_number' => $data['pets'][$k]['stamp_number'],
                    'photo' => $data['pets'][$k]['photo'],

                    'gender' => $data['pets'][$k]['gender'],
                    'birth_date' => $data['pets'][$k]['birth_date'],
                    'species' => $data['pets'][$k]['species'],
                    'breed' => $data['pets'][$k]['breed'],
                    'sterile' => $data['pets'][$k]['sterile'],
                    'guide_dog' => $data['pets'][$k]['guide_dog'],
                    'special_signs' => $data['pets'][$k]['special_signs'],

                    'address_street' => $data['pets'][$k]['address_street'],
                    'address_house' => $data['pets'][$k]['address_house'],
                    'address_flat' => $data['pets'][$k]['address_flat'],

                    'representatives' => $representatives,

                    'delete' => 0,
                    'add' => 1,
                    'add_elk' => 1
                ));
            }
        }

        //Проверка изменений
        for ($j=0; $j<count($pets_array); $j++) {
            if($data['pets'][$k]['pet_id'] == $pets_array[$j]['pet_id']){
                if($data['pets'][$k]['name'] != $pets_array[$j]['name']){
                    $pets_array[$j]['name'] = $data['pets'][$k]['name'];
                    $pets_array[$j]['update'] = 1;
                }

                $data['pets'][$k]['birth_date']=date_format(new \DateTime($data['pets'][$k]['birth_date']), "Y-m-d");
                if($data['pets'][$k]['birth_date'] != $pets_array[$j]['birthday']){
                    $pets_array[$j]['birthday'] = $data['pets'][$k]['birth_date'];
                    $pets_array[$j]['update'] = 1;
                }

                if($data['pets'][$k]['special_signs'] != $pets_array[$j]['characteristics']){
                    $pets_array[$j]['characteristics'] = $data['pets'][$k]['special_signs'];
                    $pets_array[$j]['update'] = 1;
                }

                //ЧИП+КЛЕЙМО
                if($data['pets'][$k]['chip_number'] != $pets_array[$j]['chip']){
                    $pets_array[$j]['chip_number'] = $data['pets'][$k]['chip_number'];
                    $pets_array[$j]['update'] = 1;
                    $pets_array[$j]['chip_update'] = 1;
                }

                if($data['pets'][$k]['stamp_number'] != $pets_array[$j]['stamp_number']){
                    $pets_array[$j]['stamp_number'] = $data['pets'][$k]['stamp_number'];
                    $pets_array[$j]['update'] = 1;
                    $pets_array[$j]['stamp_update'] = 1;
                }
                //ЧИП+КЛЕЙМО

                if($data['pets'][$k]['breed'] != $pets_array[$j]['breed']){
                    $pets_array[$j]['characteristics'] = $data['pets'][$k]['special_signs'];
                    $pets_array[$j]['update'] = 1;
                }

                if($data['pets'][$k]['sterile'] == 1){$data['pets'][$k]['sterile']='t';}else{$data['pets'][$k]['sterile']='f';}
                if($data['pets'][$k]['sterile'] != $pets_array[$j]['castrated']){
                    $pets_array[$j]['castrated'] = $data['pets'][$k]['sterile'];
                    $pets_array[$j]['update'] = 1;
                }
                if($data['pets'][$k]['guide_dog'] == 1){$data['pets'][$k]['guide_dog']='t';}else{$data['pets'][$k]['guide_dog']='f';}
                if($data['pets'][$k]['guide_dog'] != $pets_array[$j]['mosru_guide_dog']){
                    $pets_array[$j]['mosru_guide_dog'] = $data['pets'][$k]['guide_dog'];
                    $pets_array[$j]['update'] = 1;
                }

                if($representatives != $pets_array[$j]['representatives']){
                    $pets_array[$j]['representatives'] = $representatives;
                    $pets_array[$j]['update'] = 1;
                }
                if($data['pets'][$k]['photo']){
                    $pets_array[$j]['photo'] = $data['pets'][$k]['photo'];
                    $pets_array[$j]['update'] = 1;
                }
            }
        }
        //Проверка изменений
    }

    for ($j=0; $j<count($pets_array); $j++) {
        if($pets_array[$j]['add'] == 1 || $pets_array[$j]['add_elk'] == 1 || $pets_array[$j]['delete'] == 1 || $pets_array[$j]['update'] == 1){
            //echo "\n".($j+1).". ".$pets_array[$j]['id'].' - '.$pets_array[$j]['pet_id'].' - '.$pets_array[$j]['name'].' - dd '.$pets_array[$j]['delete'].' - ad '.$pets_array[$j]['add'].' - ae '.$pets_array[$j]['add_elk'].' - up '.$pets_array[$j]['update'];
        }

        if($pets_array[$j]['delete'] == 1){
            //удаляем из таблицы ЛК и ставим признак mosru=false
            $query_delete='DELETE FROM elk.pets WHERE id_pet='.$pets_array[$j]['id'].'';
            $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result);
            //echo $query_delete."\n";

            $query_update='UPDATE public.pets SET mosru=\'f\' WHERE id='.$pets_array[$j]['id'].' ';
            $result = pg_query($query_update) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);

            if($pets_array[$j]['delete_reason_id']){
                $query_update='UPDATE public.pets SET mosru_delete_reason='.$pets_array[$j]['delete_reason_id'].' WHERE id='.$pets_array[$j]['id'].' ';
                $result = pg_query($query_update) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                pg_free_result($result);
            }
            //echo $query_update."\n";
        }

        //PET ID уже есть?
        $id_pet = '';
        //добавляем животное
        if($pets_array[$j]['add'] == 1){
            //is_main, id_main_pet, duble_validation

            //echo '3333';
            //$representatives='[{"name": "'.$data['pets'][$k]['owner_name1'].'","patronymic": "'.$data['pets'][$k]['owner_patronymic1'].'", "surname": "'.$data['pets'][$k]['owner_surname1'].'","email": "'.$data['pets'][$k]['owner_email1'].'","phone": "'.$data['pets'][$k]['owner_phone1'].'"},{"name": "'.$data['pets'][$k]['owner_name2'].'","patronymic": "'.$data['pets'][$k]['owner_patronymic2'].'", "surname": "'.$data['pets'][$k]['owner_surname2'].'","email": "'.$data['pets'][$k]['owner_email2'].'","phone": "'.$data['pets'][$k]['owner_phone2'].'"},{"name": "'.$data['pets'][$k]['owner_name3'].'","patronymic": "'.$data['pets'][$k]['owner_patronymic3'].'", "surname": "'.$data['pets'][$k]['owner_surname3'].'","email": "'.$data['pets'][$k]['owner_email3'].'","phone": "'.$data['pets'][$k]['owner_phone3'].'"}]';

            //ФИАС
            $id_fias_from_table=api_fias_mosru_json($pets_array[$j]['address_street'], $pets_array[$j]['address_house'], $pets_array[$j]['address_flat'], $configuration);
            //echo 'ФИАС'.$id_fias_from_table;
            //ФИАС

            $query_insert='INSERT INTO public.pets ';
            $query_insert.='(birthday, name, sex, id_species, id_breed, castrated, mosru_guide_dog, characteristics, id_fias_address, representatives, created_at, updated_at) ';
            //is_address_pet_owners,
            //mosru,
            $query_insert.='VALUES (';
            if($pets_array[$j]['birth_date']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['birth_date'])."',";}else{$query_insert.="NULL,";}//birthday
            if($pets_array[$j]['name']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['name'])."',";}else{$query_insert.="NULL,";}//name
            if($pets_array[$j]['gender']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['gender'])."',";}else{$query_insert.="NULL,";}//sex
            if($pets_array[$j]['species']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['species'])."',";}else{$query_insert.="NULL,";}//id_species
            if($pets_array[$j]['breed']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['breed'])."',";}else{$query_insert.="NULL,";}//id_breed
            if($pets_array[$j]['sterile'] == 'true'){
                $query_insert.="'t',";
            }else if($pets_array[$j]['sterile'] == 'false' || $pets_array[$j]['sterile'] == ''){
                $query_insert.="'f',";
            }else{
                $query_insert.="'f',";
                //castrated
            }

            if($pets_array[$j]['guide_dog']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['guide_dog'])."',";}else{$query_insert.="NULL,";}//mosru_guide_dog

            if($pets_array[$j]['special_signs']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['special_signs'])."',";}else{$query_insert.="NULL,";}//characteristics

            if($id_fias_from_table){
                $query_insert.="'".string_formating_for_sql($id_fias_from_table)."',";//id_fias_address
                // $query_insert.="'',";//id_fias_address
            }else{
                $query_insert.="NULL,";
                // $query_insert.="NULL,";
            }

            if($pets_array[$j]['representatives']){$query_insert.="'".$pets_array[$j]['representatives']."',";}else{$query_insert.="NULL,";}//representatives
            $query_insert.="NOW()::timestamp(0),";#created_at
            $query_insert.="NOW()::timestamp(0)";#updated_at
            $query_insert.=') RETURNING id;';
            //echo $query_insert."\n";
            $result = pg_query($query_insert) or die('Ошибка запроса pets: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_pet=$row[0];
            pg_free_result($result);

            if($pets_array[$j]['photo']){//добавляем фото
                $query_insert='INSERT INTO public.files ';
                $query_insert.='(hash, path, entity_id, entity_type) ';
                $query_insert.='VALUES (';
                $query_insert.='\''.$pets_array[$j]['photo'].'\',';
                $query_insert.="'/',";
                $query_insert.=''.$id_pet.',';
                $query_insert.="'photo-mos-ru'";
                $query_insert.=') RETURNING id;';
                $result = pg_query($query_insert) or die('Ошибка запроса files: ' . pg_last_error());
                pg_free_result($result);
            }

            // //добавляем связку с владельцем
            $query_insert='INSERT INTO public.pets_to_owner ';
            $query_insert.='(id_owner, id_pet, id_owner_type) ';
            $query_insert.='VALUES (';
            $query_insert.=''.$id_owner.',';
            $query_insert.=''.$id_pet.',';
            $query_insert.='1';
            $query_insert.=') RETURNING id;';
            $result = pg_query($query_insert) or die('Ошибка запроса pets_to_owner: ' . pg_last_error());
            pg_free_result($result);
            //echo $query_insert."\n";

            //ЧИП

            if($pets_array[$j]['chip_number']){
                //ищем чип, если есть уже, то не добавляем
                $query='SELECT id FROM pet_identification WHERE identification_code=\''.string_formating_for_sql($pets_array[$j]['chip_number']).'\' AND id_ident_type=1 LIMIT 1';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $chip=pg_fetch_result($result, 0);
                pg_free_result($result);
                //echo $chip;
                if(!$chip){
                    //добавляем новый $pets_array[$j]['chip_number']
                    $query_insert='INSERT INTO pet_identification ';
                    $query_insert.='(identification_code, id_pet, id_ident_type, created_at, updated_at) ';
                    $query_insert.='VALUES (';
                    $query_insert.='\''.$pets_array[$j]['chip_number'].'\',';
                    $query_insert.=''.$id_pet.',';
                    $query_insert.='1,';
                    $query_insert.="NOW()::timestamp(0),";#created_at
                    $query_insert.="NOW()::timestamp(0)";#updated_at
                    $query_insert.=') RETURNING id;';
                    //echo $query_insert;
                    $result = pg_query($query_insert) or die('Ошибка запроса pet_identification: ' . pg_last_error());
                    pg_free_result($result);
                }
            }
            //ЧИП

            //КЛЕЙМО
            if($pets_array[$j]['stamp_number']){
                $query_insert='INSERT INTO pet_identification ';
                $query_insert.='(identification_code, id_pet, id_ident_type, created_at, updated_at) ';
                $query_insert.='VALUES (';
                $query_insert.='\''.$pets_array[$j]['stamp_number'].'\',';
                $query_insert.=''.$id_pet.',';
                $query_insert.='2,';
                $query_insert.="NOW()::timestamp(0),";#created_at
                $query_insert.="NOW()::timestamp(0)";#updated_at
                $query_insert.=') RETURNING id;';
                //echo $query_insert;
                $result = pg_query($query_insert) or die('Ошибка запроса pet_identification: ' . pg_last_error());
                pg_free_result($result);
                //добавляем новый
            }
            //КЛЕЙМО
        }

        //ОБНОВЛЕНИЕ ДАННЫХ
        if($pets_array[$j]['update'] == 1 && $pets_array[$j]['add'] == 0){
            //обновляем в ЛК и основной таблице
            $query_update='UPDATE public.pets SET ';
            if($pets_array[$j]['name']){$query_update.="name='".$pets_array[$j]['name']."', ";}else{$query_update.="name=NULL, ";}

            if($pets_array[$j]['birthday']){$query_update.="birthday='".$pets_array[$j]['birthday']."', ";}else{$query_update.="birthday=NULL, ";}

            if($pets_array[$j]['castrated']){$query_update.="castrated='".$pets_array[$j]['castrated']."', ";}else{$query_update.="castrated='f', ";}
            if($pets_array[$j]['mosru_guide_dog']){$query_update.="mosru_guide_dog='".$pets_array[$j]['mosru_guide_dog']."', ";}else{$query_update.="mosru_guide_dog='f', ";}

            if($pets_array[$j]['characteristics']){$query_update.="characteristics='".$pets_array[$j]['characteristics']."', ";}else{$query_update.="characteristics=NULL, ";}
            if($pets_array[$j]['representatives']){$query_update.="representatives='".$pets_array[$j]['representatives']."', ";}else{$query_update.="representatives=NULL, ";}
            $query_update.="updated_at = NOW()::timestamp(0)";#updated_at
            $query_update.='WHERE id='.$pets_array[$j]['id'].' ';
            //echo $query_update;
            $result = pg_query($query_update) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result);

            $query_update='UPDATE elk.pets SET ';
            if($pets_array[$j]['name']){$query_update.="name='".$pets_array[$j]['name']."', ";}else{$query_update.="name=NULL, ";}
            if($pets_array[$j]['birthday']){$query_update.="birthday='".$pets_array[$j]['birthday']."', ";}else{$query_update.="birthday=NULL, ";}
            $query_update.="updated_at = NOW()::timestamp(0)";#updated_at
            $query_update.='WHERE id_pet='.$pets_array[$j]['id'].' ';
            //echo $query_update;
            $result = pg_query($query_update) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result);

            if($pets_array[$j]['photo']){//добавляем фото
                $query_delete='DELETE FROM public.files WHERE entity_id='.$pets_array[$j]['id'].' AND entity_type=\'photo-mos-ru\'';
                $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);

                $query_insert='INSERT INTO public.files ';
                $query_insert.='(hash, path, entity_id, entity_type) ';
                $query_insert.='VALUES (';
                $query_insert.='\''.$pets_array[$j]['photo'].'\',';
                $query_insert.="'/',";
                $query_insert.=''.$pets_array[$j]['id'].',';
                $query_insert.="'photo-mos-ru'";
                $query_insert.=') RETURNING id;';
                $result = pg_query($query_insert) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
            }

            //ЧИП+КЛЕЙМО-ОБНОВЛЕНИЕ
            if($pets_array[$j]['chip_update']){
                $query='SELECT id FROM pet_identification WHERE identification_code=\''.string_formating_for_sql($pets_array[$j]['chip_number']).'\' AND id_ident_type=1 LIMIT 1';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $chip=pg_fetch_result($result, 0);
                pg_free_result($result);

                if(!$chip){
                    $query_insert='INSERT INTO pet_identification ';
                    $query_insert.='(identification_code, id_pet, id_ident_type, created_at, updated_at) ';
                    $query_insert.='VALUES (';
                    $query_insert.='\''.$pets_array[$j]['chip_number'].'\',';
                    $query_insert.=''.$pets_array[$j]['id'].',';
                    $query_insert.='1,';
                    $query_insert.="NOW()::timestamp(0),";#created_at
                    $query_insert.="NOW()::timestamp(0)";#updated_at
                    $query_insert.=') RETURNING id;';
                    //echo $query_insert;
                    $result = pg_query($query_insert) or die('Ошибка запроса: ' . pg_last_error());
                    pg_free_result($result);
                }
            }
            if($pets_array[$j]['stamp_update']){
                $query_insert='INSERT INTO pet_identification ';
                $query_insert.='(identification_code, id_pet, id_ident_type, created_at, updated_at) ';
                $query_insert.='VALUES (';
                $query_insert.='\''.$pets_array[$j]['stamp_number'].'\',';
                $query_insert.=''.$pets_array[$j]['id'].',';
                $query_insert.='2,';
                $query_insert.="NOW()::timestamp(0),";#created_at
                $query_insert.="NOW()::timestamp(0)";#updated_at
                $query_insert.=') RETURNING id;';
                //echo $query_insert;
                $result = pg_query($query_insert) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
                //добавляем новый
            }
            //ЧИП+КЛЕЙМО-ОБНОВЛЕНИЕ
        }

        //добавляем животное в ЛК
        if($pets_array[$j]['add_elk'] == 1){
            $query_insert='INSERT INTO elk.pets ';
            $query_insert.='(id_pet, ext_id, id_elk_owner, id_pet_owner, id_species, id_breed, name, chip, birthday, sex, created_at, updated_at) ';
            $query_insert.='VALUES (';
            if($id_pet){
                $query_insert.=''.$id_pet.',';//id_pet
            }else{
                $query_insert.=''.$pets_array[$j]['id'].',';//id_pet
            }
            $query_insert.="'".string_formating_for_sql($pets_array[$j]['pet_id'])."',";//ext_id
            $query_insert.=''.$id_elk_owner.',';//id_elk_owner
            $query_insert.=''.$id_owner.',';//id_pet_owner

            if($pets_array[$j]['species']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['species'])."',";}else{$query_insert.="NULL,";}
            if($pets_array[$j]['breed']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['breed'])."',";}else{$query_insert.="NULL,";}
            if($pets_array[$j]['name']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['name'])."',";}else{$query_insert.="NULL,";}
            if($pets_array[$j]['chip_number']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['chip_number'])."',";}else{$query_insert.="NULL,";}
            if($pets_array[$j]['birth_date']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['birth_date'])."',";}else{$query_insert.="NULL,";}
            if($pets_array[$j]['gender']){$query_insert.="'".string_formating_for_sql($pets_array[$j]['gender'])."',";}else{$query_insert.="NULL,";}//sex

            $query_insert.="NOW()::timestamp(0),";#created_at
            $query_insert.="NOW()::timestamp(0)";#updated_at

            $query_insert.=') RETURNING id;';
            //echo $query_insert."\n";
            $result = pg_query($query_insert) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_elk_pet=$row[0];
            pg_free_result($result);
        }
    }
}

function api_visit_notification_json(){
    $id_user=string_formating_for_sql($_GET["id_user"]);
    $token=string_formating_for_sql($_GET["token"]);
    $id_visit=string_formating_for_sql($_GET["id_visit"]);
    $is_paid = string_formating_for_sql($_GET["is_paid"]);

    //ищем приём
    $query='SELECT visits_specialists.id_specialist AS specialist_id, users.id AS user_id, visits.id, visits.status, visits.time_range, visits.id_owner, visits.id_pet, ';
    $query.='pet_owners.fullname AS owner_name, ';
    $query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,';
    $query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, ';
    $query.='string_agg(pets.id::character varying, \',\') AS pets ';
    $query.='FROM visits ';
    $query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
    //$query.='LEFT JOIN organizations ON visits.id_organization=organizations.id ';
    $query.='LEFT JOIN visits_specialists ON visits.id=visits_specialists.id_visit ';
    $query.='LEFT JOIN specialists ON visits_specialists.id_specialist=specialists.id '."\n";

    $query.='LEFT JOIN visit_pets ON visit_pets.id_visit=visits.id ';#животные
    $query.='LEFT JOIN pets ON visit_pets.id_pet=pets.id ';#животные

    $query.='LEFT JOIN users ON specialists.id_user=users.id '."\n";
    $query.='WHERE visits.id='.$id_visit.' ';

    $query.='GROUP BY visits_specialists.id_specialist, visits.id, pet_owners.fullname, users.id ';
    //echo $query;

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    $id_spec_user = $row['user_id'];
    pg_free_result($result);

    $pets = ($row['pets'] != '')?explode(",",$row['pets']):NULL;
    $pets_title='';
    if(count($pets) > 0){
        #питомцы

        $q='SELECT pets.id,pets.name,pets.sex,breeds.name AS breed_name,species.name AS species_name FROM pets ';
        $q.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';
        $q.='LEFT JOIN species ON pets.id_species = species.id ';
        $q.='WHERE (pets.is_main=true OR pets.is_main IS NULL) AND ';
        $q.='(';
        for($i=0; $i<count($pets);$i++){
            if($i>0){$q.=' OR ';}
            $q.='pets.id='.$pets[$i].'';
        }
        $q.=')';
        $result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
        while ($row_c = pg_fetch_assoc($result_c)) {
            if($row_c['name']){
                if($pets_title){$pets_title.=', ';}
                $pets_title.=''.$row_c['name'].'';
            }
        }
        pg_free_result($result_c);
    }

    //ищем id врача
    $title = 'Прибытие владельца в клинику';
    //$text = '[ФИО владельца] (прием [дата и время приема], [кличка животного (или клички, если животных несколько)/кол-во животных (если выводок)] прибыл в клинику.';
    if(count($pets) > 1){
        $text = 'Владелец '.$row['owner_name'].' (прием '.date_format(new \DateTime($row['start_date']), "d.m.Y").' в '.$row['start_time'].') с питомцами '.$pets_title.'/'.count($pets).' прибыл в клинику.';
    }else{
        $text = 'Владелец '.$row['owner_name'].' (прием '.date_format(new \DateTime($row['start_date']), "d.m.Y").' в '.$row['start_time'].') с питомцем '.$pets_title.' прибыл в клинику.';
    }

    //echo $text;

    ###################################
    ###ищем сообщение может уже есть###
    ###################################
    $query='SELECT id FROM notifications ';
    $query.='WHERE title=\''.$title.'\' AND text=\''.$text.'\' AND id_user='.$id_spec_user.'';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    $id_notification = $row['id'];
    pg_free_result($result);


    header('Content-Type: application/json; charset=utf-8');
    header('Content-Type: application/vnd.api+json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header('Connection: close');
    header('X-Powered-By: PHP/7.3.33');
    $data = '';
    if(!$id_notification){
        $query='INSERT INTO notifications ';
        $query.='(title, text, id_visit, id_user, created_by, created_at) ';
        $query.='VALUES (';
        $query.="'".$title."',";
        $query.="'".$text."',";
        $query.="".$id_visit.",";
        $query.="".$id_spec_user.",";#Врач
        $query.="".$id_user.",";#created_by
        $query.="NOW()::timestamp(0)";#created_at
        $query.=') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id_notification_=$row[0];
        pg_free_result($result);

        $data.='{';
        $data.='"id": "'.$id_notification_.'"';
        $data.='}';
    }else{
        $data.='{';
        $data.='"error": "notification exists"';
        $data.='}';
    }

    echo $data;
}

if($action == 'notifications' && $mode == 'json'){
    api_notifications_json();
}else if($action == 'count_notifications' && $mode == 'json'){
    api_count_notifications_json();
}else if($action == 'read_notification' && $mode == 'json'){
    api_read_notification_json();
}else if($action == 'visit_notification' && $mode == 'json'){
    api_visit_notification_json();
}else if($action == 'check_visit_notification' && $mode == 'json'){
    //api_visit_notification_json();
}else if($action == 'save_mosru' && $mode == 'json'){//МДМ
    api_save_mosru_json($configuration);
}else if($action == 'geo' && $mode == 'json'){//МДМ
    api_geo_json($configuration);
}

if (function_exists('pg_connect')) {
    pg_close($dbconn);
}
