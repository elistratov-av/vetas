<?php

/* SYSTEM */
function get_version_query($config){
    $query='';
    if($config['version'] == '1.0' || isset($config['version']) == false){//Ограничиваем организации
        $query.=' (organizations.id_org_type=37 OR organizations.id_org_type=39 OR organizations.id_org_type=40 OR organizations.id_org_type=41 OR organizations.id_org_type=53) ';
    }else{
        $query.=' (organizations.organization_type_const = \'sbbg\' OR organizations.organization_type_id=40 OR organizations.organization_type_id=41 ';
        $query.='OR organizations.organization_type_id=39 OR organizations.organization_type_id=37 OR organizations.organization_type_id=53) '."\n";
    }
    return $query;
}

function generateHash($name){
    return sha1($name . microtime());
}

if(!function_exists('mb_ucfirst')){
    function mb_ucfirst($string, $enc = 'UTF-8'){
        return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) . 
        mb_substr($string, 1, mb_strlen($string, $enc), $enc);
    }
}

function extract_mosru_phonenumber($phone){
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $len = mb_strlen($phone);

    if ($len != 10 && $len != 11) {
        return null;
    }

    if ($len == 10) {
        // на случай если начнут передавать 7, чтобы у нас ничего не поломалось
        $phone = '7' . $phone;
    }

    return '+' . $phone;
}

function calculate_age($birthday) {
    $birthday_timestamp = strtotime($birthday);
    $age = date('Y') - date('Y', $birthday_timestamp);
    if (date('md', $birthday_timestamp) > date('md')) {
      $age--;
    }
    return $age;
}

function get_month ($month) {
    if($month == 1){
        return 'января';
    }else if($month == 2){
        return 'февраля';
    }else if($month == 3){
        return 'марта';
    }else if($month == 4){
        return 'апреля';
    }else if($month == 5){
        return 'мая';
    }else if($month == 6){
        return 'июня';
    }else if($month == 7){
        return 'июля';
    }else if($month == 8){
        return 'августа';
    }else if($month == 9){
        return 'сентября';
    }else if($month == 10){
        return 'октября';
    }else if($month == 11){
        return 'ноября';
    }else if($month == 12){
        return 'декабря';
    }
}

function user_id($configuration) {
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
	$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	$id_user=pg_fetch_result($result, 0);
	pg_free_result($result);

    return $id_user;
}

function user_org_id($configuration) {
    $org_id = 0;

    if($_COOKIE['organization'] && $configuration){
        if($_COOKIE['organization'] == 1){
            $org_id=$configuration['test_organization'];
        }else{
            $query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $org_id=$row[0];
            pg_free_result($result);
        }
    }

    return $org_id;
}

function getUserRoles($userId) {
    if (!$userId || !$_COOKIE['organization']) return [];
    $result = pg_fetch_all(pg_query("SELECT auth_assignment.item_name AS role_name  FROM users 
    LEFT JOIN specialists ON specialists.id_user = users.id
    LEFT JOIN auth_assignment ON auth_assignment.id_specialist = specialists.id
    WHERE users.id = $userId AND specialists.id_organization = {$_COOKIE['organization']}"));
    return array_map(function($item) { return $item['role_name']; }, $result);
}

function getUserAction($configuration) {
    $userId = user_id($configuration);
    $orgId = user_org_id($configuration);
    pg_fetch_result(pg_query("SELECT * FROM public.users 
    LEFT JOIN 
    WHERE id = $userId
    "));
}

function managing_organization_tree_orgs() {
    $query='SELECT * FROM organizations ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $results = array();
    while ($row = pg_fetch_assoc($result)) {
        if($row["managing_organization_id"] == ''){
            $row["managing_organization_id"]=0;
        }
        $results[$row["managing_organization_id"]][] = $row;
    }
    pg_free_result($result);

    return $results;
}
function managing_organization_tree($managing_organization_id, $level) {
    global $orgs_array;
    global $m_orgs_array;

    if (isset($orgs_array[$managing_organization_id])) {
        foreach ($orgs_array[$managing_organization_id] as $value) {
            
            $level = $level + 1;
            array_push($m_orgs_array, array('id' => $value["id"],'level' => $level));
            
            //echo $value["id"].'--'.$level.'+++';

            managing_organization_tree($value["id"], $level);
            $level = $level - 1; //Уменьшаем уровень вложености
        }
    }
}
function getManagingOrgs($org_user_id){
    //Проверяем есть ли у этой организации управляемые
    global $orgs_array; 
    
    $orgs_array = managing_organization_tree_orgs();
    global $m_orgs_array;
    $m_orgs_array = array();
    
    managing_organization_tree($org_user_id, 0);

    return $m_orgs_array;
    //Проверяем есть ли у этой организации управляемые
}

function user_org_title($configuration) {
    $org_title = '';

    if($_COOKIE['organization']){
        if($_COOKIE['organization'] == 1){
            $org_id=$configuration['test_organization'];
        }else{
            $org_id=$_COOKIE['organization'];
        }
        $query='SELECT short_name FROM organizations WHERE id=\''.string_formating_for_sql($org_id).'\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $org_title=$row[0];
        pg_free_result($result);
    }

    return $org_title;
}

function token_request_curl($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT,120);
    curl_setopt($ch, CURLOPT_MAXREDIRS,10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);

    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );

    curl_close($ch);
    //echo var_dump($errmsg);
    return json_decode($result); // Return the received data
}

function token_request($url, $data) {
    $options = array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
        'http' => array(
            'timeout' => 1200,
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
        )
    );
    $context  = stream_context_create( $options );
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result );
}

function validate_date($value){
    $pattern = "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/"; // Основной 2013-10-22  
    if ( preg_match($pattern, $value, $razdeli) ) :
        if ( checkdate($razdeli[2],$razdeli[3],$razdeli[1]) )
            return true;
        else
            return false;
        else :
            return false;
    endif;
}

function fias_request($config) {
    $url = "{$config['api_mos']}/token";
    $key = $config['api_mos_key'];

    $data='grant_type=client_credentials';
    $options = array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
        'http' => array(
            'timeout' => 1200,
            'method'  => 'POST',
            'content' => $data,
            'header'=>  "Authorization: Basic ".$key."\r\n" .
                        "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "Accept: */*\r\n"
        )
    );
    
    $context  = stream_context_create( $options );
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result );
}

function org_request($url, $data, $token) {
    $options = array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
        'http' => array(
            'timeout' => 1200,
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header'=>  "Authorization: Bearer ".$token."\r\n" .
                        "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n"
        )
    );
    
    $context  = stream_context_create( $options );
    $result = file_get_contents( $url, false, $context );
    return json_decode( $result );
}

function jwt_request($baseurl, $token, $post) {
    header('Content-Type: application/json'); // Specify the type of data
    $ch = curl_init($baseurl.'/v2/specialist/specialist/list'); // Initialise cURL
    $post = json_encode($post); // Encode the data array into a JSON string
    $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json' , $authorization )); // Inject the token into the header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    $result = curl_exec($ch); // Execute the cURL statement
    curl_close($ch); // Close the cURL connection
    return json_decode($result); // Return the received data
}

function get_params(){
    // $query  = explode('&', $_SERVER['QUERY_STRING']);
    // $params = array();

    // foreach( $query as $param ){
    // if (strpos($param, '=') === false) $param += '=';
    // list($name, $value) = explode('=', $param, 2);
    // $params[urldecode($name)][] = urldecode($value);
    // }
    $params = null;

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $query  = explode('&', $_SERVER['QUERY_STRING']);
        $params = array();

        foreach ($query as $param) {
            if (strpos($param, '=') === false) $param += '=';
            list($name, $value) = explode('=', $param, 2);
            $params[urldecode($name)][] = urldecode($value);
        }
    } else $params = json_decode(file_get_contents('php://input'), true);

    return $params;
}

function get_unique_elements($tempArray) {
    $uniqueArray = array();
  
    foreach($tempArray as $row) {
      $niddle = $row['id_user'];
      if(array_key_exists($niddle, $uniqueArray)) continue;
      $uniqueArray[$niddle] = $row;
    }
  
    return $uniqueArray;
}

function sort_by_column(&$array, $column, $order = SORT_ASC){
    array_multisort(
        array_column($array, $column),
            $order,
            $array
        );
}

function string_add_spaces(string $word = null, int $default_space_count){
    return str_repeat('<w:t xml:space="preserve"> </w:t>', $default_space_count - strlen($word) > 0 ? $default_space_count - strlen($word) : 0);
}

function string_replace_underline(string $word){
    $replace = str_replace('&lt;U&gt;', '</w:t></w:r><w:r><w:rPr><w:u w:val="single"/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve">', $word);
    return str_replace('&lt;/U&gt;', '</w:t></w:r><w:r><w:rPr><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>', $replace);
}

function string_formating_for_sql($string){
    if($string != ''){
        foreach($string as $k => $v){
            $string[$k] = preg_replace('/ {2,}/',' ',trim(pg_escape_string($v)));
        }

        $string=pg_escape_string($string);
        $string=htmlspecialchars($string,ENT_QUOTES);
    }

    return $string;
}

function string_formating_for_xml($string){
    $string = preg_replace('/\s&\s/', '&amp;', $string);
    $string = preg_replace('/</', '&lt;', $string);
    $string = preg_replace('/>/', '&gt;', $string);
    $string = preg_replace('/&/', '&amp;', $string);

    return $string;
}

function xml($var){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo ''.$var.'';
    echo '</xml>';
}

function get_metro_line($line){
    $metro_lines_array = array();
    array_push($metro_lines_array, array('line' => 'Калининская линия','color' => 'FFCD1C'));
    array_push($metro_lines_array, array('line' => 'Замоскворецкая линия','color' => '4FB04F'));
    array_push($metro_lines_array, array('line' => 'Калужско-Рижская линия','color' => 'F07E24'));
    array_push($metro_lines_array, array('line' => 'Сокольническая линия','color' => 'E42313'));
    array_push($metro_lines_array, array('line' => 'Арбатско-Покровская линия','color' => '0072BA'));
    array_push($metro_lines_array, array('line' => 'Филёвская линия','color' => '1EBCEF'));
    array_push($metro_lines_array, array('line' => 'Серпуховско-Тимирязевская линия','color' => 'ADACAC'));
    array_push($metro_lines_array, array('line' => 'Таганско-Краснопресненская линия','color' => '943E90'));
    array_push($metro_lines_array, array('line' => 'Кольцевая линия','color' => '915133'));
    array_push($metro_lines_array, array('line' => 'Люблинско-Дмитровская линия','color' => 'BED12C'));
    array_push($metro_lines_array, array('line' => 'Каховская линия','color' => '88CDCF'));
    array_push($metro_lines_array, array('line' => 'Бутовская линия Лёгкого метро','color' => 'ADACAC'));
    array_push($metro_lines_array, array('line' => 'Серпуховско-Тимирязевская линия','color' => 'BAC8E8'));
    array_push($metro_lines_array, array('line' => 'Солнцевская линия','color' => 'FFCD1C'));
    array_push($metro_lines_array, array('line' => 'Московское центральное кольцо','color' => 'CC4C6E'));
    array_push($metro_lines_array, array('line' => 'Московская монорельсовая транспортная система','color' => '006DA8'));
    array_push($metro_lines_array, array('line' => 'Большая кольцевая линия','color' => '88CDCF'));
    array_push($metro_lines_array, array('line' => 'Некрасовская линия','color' => 'CC0066'));
    array_push($metro_lines_array, array('line' => 'МЦД-1','color' => 'F5A528'));
    array_push($metro_lines_array, array('line' => 'МЦД-2','color' => 'E74683'));
    
    $line_color='';
    for($i=0;$i<=count($metro_lines_array);$i++){
        if($line == $metro_lines_array[$i]['line']){
            $line_color=$metro_lines_array[$i]['color'];
        }
    }

    if($line_color != ''){
        return $line_color;
    }else{
        return '000000';
    }
}

function debug($var){
    file_put_contents('debug.log',$var, FILE_APPEND);
}

/* SYSTEM */

function viewRecoveryPassword($entry_point = NULL){
    echo '<div style="height: calc(100% - 150px);">';
    echo '<div class="row main_row h-100 justify-content-center align-items-center">';

    echo '<div class="auth" style="height: 300px;">';
    echo '<form id="form" onsubmit="recoveryPasswordUser(); return false;">';
    
    echo '<div class="col-12 title d-table-cell">Восстановление пароля</div>';
    
    echo '<div class="col-12" id="auth_title_login">Логин: </div>';
    echo '<div class="col-12" id="auth_input_login">';
    echo '<input type="text" name="login" id="login">';
    echo '</div>';
    
    if($entry_point){
        echo '<input type="hidden" name="entry_point" value="'.$entry_point.'">';
    }

    echo '<div class="col-12 p-3 error" id="message"></div>';
    
    echo '<div class="col-12">';
    echo '<button type="submit" style="font-size: 12px;" id="submit_recovery_password" title="Отправить запрос на восстановление пароля">Отправить запрос на восстановление пароля</button>';
    echo '</div>';

    echo '<div class="col-12">';
    echo '<a href="index.php">Вернуться к авторизации</a>';
    echo '</div>';

    echo '</div>';

    echo '</form>';

    echo '</div>';
    echo '</div>';
}

function viewAuthUser($entry_point = NULL){
    echo '<div style="height: calc(100% - 150px);">';
    echo '<div class="row main_row h-100 justify-content-center align-items-center">';

    echo '<div class="auth">';
    echo '<form id="form" onsubmit="authUser(); return false;">';
    
    echo '<div class="col-12 title d-table-cell">Вход в систему</div>';
    
    echo '<div class="col-12" id="auth_title_login">Логин: </div>';
    echo '<div class="col-12" id="auth_input_login">';
    echo '<input type="text" name="login" id="login">';
    echo '</div>';
    echo '<div class="col-12" id="auth_title_password">Пароль: </div>';
    echo '<div class="col-12" id="auth_input_password">';
    echo '<input type="password" name="password" id="password">';
    echo '</div>';

    echo '<div class="col-12" id="auth_title_organization" style="display: none;">Выберите организацию: </div>';
    echo '<div class="col-12" id="auth_input_organization" style="display: none;">';
    echo '<select name="organization" id="organization"></select>';
    echo '<input type="hidden" name="login_" id="login_">';
    echo '<input type="hidden" name="token" id="token">';

    if($entry_point){
        echo '<input type="hidden" name="entry_point" value="'.$entry_point.'">';
    }

    echo '</div>';

    echo '<div class="col-12 error" id="message"></div>';
    
    echo '<div class="col-12">';
    echo '<button type="submit">Войти</button>';
    echo '</div>';

    echo '<div class="col-12">';
    echo '<a href="index.php?action=recovery_password">Забыли пароль?</a>';
    echo '</div>';

    echo '</div>';

    echo '</form>';

    echo '</div>';
    echo '</div>';
}

function viewRescheduleWindow($config){
    echo '<div class="window col-xl-10 col-lg-10 col-12" id="reschedule" style="display: none;">';
    echo '<div class="close" onclick="closeMessageWindow();"></div>';

    echo '<div class="top">Список слотов для переноса приёма</div>';
	echo '<div>';

    echo '<div class="search">';

    echo '<form id="form_reschedule" onsubmit="ListShowSlotsReschedule(); return false;">';
    echo '<div class="row search_sub">';

    echo '<div class="legend">';
    echo '<div class="visit">Занято</div>';
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
    echo '<div class="d-inline-flex">';show_specialists($config,0);echo '</div>';
    echo '<div class="d-inline-flex">';show_services($config,0,1);echo '</div>';
    echo '<div class="d-inline-flex buttons"><button type="reset" onclick="resetForm();" style="width: 100px;">Сброс</button><button type="submit" style="width: 100px;">Поиск</button></div>';

    echo '<div class="w-100 info" id="ListInfoReschedule"></div>';

    echo '</div>';

    echo '</form>';

    
    echo '</div>';

    echo '<div class="row main_row" style="height: 600px; overflow-y: scroll;">';
    //echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-12 col-lg-12 results">';
    echo '<div id="ListRecsReschedule" class="loading"></div>';
    echo '</div>';
    //echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';

    echo '</div>';

    echo '<div class="controls"><button class="ok" style="width: 120px;" onclick="closeMessageWindow();">Закрыть</button></div>';

    echo '</div>';
	echo '</div>';
}

function delete_notifications_by_time(){
    $query_delete='DELETE FROM notifications WHERE created_at < NOW() - interval \'7\' DAY';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);
}

function timesheets(){
    // $query='INSERT INTO shifts ';
    // $query.='(name, from_time, duration, id_organization, created_by, updated_by, created_at, updated_at, id_type) ';
    // $query.='VALUES (';
    // $query.="'Временная блокировка времени',";
    // $query.="'".$time.":00',";
    // $query.="".$services_fulltime.",";
    // $query.="".$org_id.",";
    // $query.="".$id_user.",";#created_by
    // $query.="".$id_user.",";#updated_by
    // $query.="NOW()::timestamp(0),";#created_at
    // $query.="NOW()::timestamp(0),";#updated_at
    // $query.="'15'";
    // $query.=') RETURNING id;';
    
    // ###Создаём таймшит
    // $query='INSERT INTO timesheets ';
    // $query.='(id_specialist, id_shift, created_by, updated_by, created_at, updated_at, date) ';
    // $query.='VALUES (';
    // $query.="".$specialist_id.",";
    // $query.="NULL,";
    // $query.="".$id_user.",";#created_by
    // $query.="".$id_user.",";#updated_by
    // $query.="NOW()::timestamp(0),";#created_at
    // $query.="NOW()::timestamp(0),";#updated_at
    // $query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_fulltime.' MINUTES\'), \'[)\')';
    // $query.=') RETURNING id;';
    // $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    // $row = pg_fetch_row($result);
    // $id_timesheet=$row[0];
    // pg_free_result($result);
    // return $id_timesheet;
}

function calculate_services_time($configuration, $services){
    //считаем время выбранных услуг
    $servicesArray = explode(";", $services);

    $q='SELECT id, name, price, duration, cooldown FROM gov_services ';
    $q.='WHERE deleted=false AND ';
    $q.='(';
    for($i=0; $i<=count($servicesArray);$i++){
        if($servicesArray[$i]){
            if($i>0){$q.=' OR ';}
            $q.='id = '.$servicesArray[$i].'';
        }
    }
    $q.=')';
    if($configuration['debug'] == 0){
        $q.='AND type IS NULL ';
    }
    
    $services_name='';
    $services_cooldown=0;
    $services_duration=0;
    $result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $services_duration=$services_duration+$row['duration'];
        if($services_cooldown < $row['cooldown']){
            $services_cooldown=$row['cooldown'];
        }
        if($services_name != ''){
            $services_name.=', ';
        }
        $services_name.="«".$row['name']."»";
    }
    $services_fulltime=$services_duration+$services_cooldown;
    pg_free_result($result);
    //считаем время выбранных услуг

    return array($services_fulltime, $services_duration, $services_cooldown, $services_name);
}

function reschedule_appointment($configuration){
    $id_visit=$_GET["visit"];

    $date=$_GET["date"];
    $time=$_GET["time"];
    $services=$_GET["services"];
    $org_id=$_GET["org_id"];//ID организации в которой производится запись на приём
    $specialist_id=$_GET["spec_id"];

    //свой id
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user=pg_fetch_result($result, 0);
    pg_free_result($result);

    if($id_user){
        ###ИЩЕМ ДАННЫЕ ПРЕДЫДУЩЕГО ПРИЁМА
        $query='SELECT id_owner, id_pet FROM visits WHERE id=\''.string_formating_for_sql($id_visit).'\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id_owner=$row[0];
        $id_pet=$row[1];
        pg_free_result($result);

        #ОТМЕНЯЕМ ВИЗИТ ПРЕДЫДУЩИЙ ВИЗИТ
        $query='UPDATE visits SET ';
        $query.='status=\'A\', ';
        $query.='cancel_initiator=\'OWNER\',';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id_visit.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
        if($configuration['version'] == '1.0'){
            visitlogUser($id_visit, 'A');
        }
        #ОТМЕНЯЕМ ВИЗИТ ПРЕДЫДУЩИЙ ВИЗИТ

        if($id_owner && $id_pet){
            #посчитать номер визита
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
            //считаем время выбранных услуг
            list($services_fulltime,$services_duration,$services_cooldown, $services_name) = calculate_services_time($configuration, $services);
            $servicesArray = explode(";", $services);
           
            #СОЗДАЁМ ВИЗИТ
            $query='INSERT INTO visits ';
            $query.='(status, id_owner, id_pet, id_organization, created_by, updated_by, created_at, updated_at, cooldown, number, time_range, channel, duration, ticket_number, start_dttm, time_range_without_cooldown, type, variety) ';
            $query.='VALUES (';
            $query.="'N',";
            $query.="".$id_owner.",";#Владелец
            $query.="".$id_pet.",";#Животное
            $query.="".$org_id.",";#Организация
            $query.="".$id_user.",";#created_by
            $query.="".$id_user.",";#updated_by
            $query.="NOW()::timestamp(0),";#created_at
            $query.="NOW()::timestamp(0),";#updated_at
            $query.="".$services_cooldown.",";#cooldown
            $query.="".$counter.",";#number - по порядку по суткам
            $query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_fulltime.' MINUTES\'), \'[)\'),';#time_range["2021-12-02 14:00:00","2021-12-02 14:20:00")
            $query.="3,";#channel - по умолчанию 3 - телефон
            $query.="".$services_duration.",";#duration
            $query.="'".$ticket_number."',";#ticket_number - 023001 - [02]дата[3]-канал[001]-номер
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
                visitlogUser($id_visit, 'N');
            }
    
            #ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
            $query='INSERT INTO visit_pets ';
            $query.='(id_pet, id_visit, created_by, updated_by, created_at, updated_at) ';
            $query.='VALUES (';
            $query.="".$id_pet.",";
            $query.="".$id_visit.",";
            $query.="".$id_user.",";#created_by
            $query.="".$id_user.",";#updated_by
            $query.="NOW()::timestamp(0),";#created_at
            $query.="NOW()::timestamp(0)";#updated_at
            $query.=');';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result);

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

            for($i=0; $i<=count($servicesArray);$i++){
                if($servicesArray[$i]){
                    #ДОБАВЛЯЕМ СВЯЗКУ ВИЗИТ+УСЛУГА+ЖИВОТНОЕ
                    $query='INSERT INTO visits_gov_services ';
                    $query.='(id_visit, id_service, id_pet, count, created_by, updated_by, created_at, updated_at) ';
                    $query.='VALUES (';
                    $query.="".$id_visit.",";#визит
                    $query.="".$servicesArray[$i].",";#услуга
                    $query.="".$id_pet.",";#животное
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
                    $query.="".$id_pet."";
                    $query.=');';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    pg_free_result($result);
                }
            }

            ###Всё ок достаём все данные по визиту и выводим красивое окошко
            $query='SELECT organizations.short_name, addresses.name FROM organizations ';
            $query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
            $query.='WHERE organizations.id='.$org_id.'';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $org_name=$row[0];
            $org_address=$row[1];

            $query='SELECT users.fullname FROM specialists ';
            $query.='LEFT JOIN users ON specialists.id_user=users.id ';
            $query.='WHERE specialists.id='.$specialist_id.'';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $specialist_name=$row[0];

            $message='<message>visit_created</message>';
            $message.='<ticket_number>'.$ticket_number.'</ticket_number>';
            $message.='<date>'.$date.'</date>';
            $message.='<time>'.$time.'</time>';
            $message.='<duration>'.$services_fulltime.'</duration>';
            $message.='<services>'.$services_name.'</services>';
            $message.='<specialist>'.$specialist_name.'</specialist>';
            $message.='<organization>'.$org_name.'</organization>';
            $message.='<address>'.$org_address.'</address>';

             
            echo xml($message);
        }else{
            echo xml('<message>invalid_owner_pet</message>');
        }
    }else{
        echo xml('<message>invalid_user_id</message>');
    }
}

function make_appointment($configuration){
    $id_owner=$_GET["owner"];
    $id_pet=$_GET["pet"];
    $date=$_GET["date"];
    $time=$_GET["time"];
    $services=$_GET["services"];
    $org_id=$_GET["org_id"];//ID организации в которой производится запись на приём
    $specialist_id=$_GET["spec_id"];
    $visit_id=$_GET["visit_id"];
    
    #TODO: сделать проверку на существование владельца и животного
    #TODO: сделать проверку на введенные данные
    cancel_booking_by_time();//Удаляем booking принудительно по времени

    $query='SELECT id FROM visits WHERE id=\''.string_formating_for_sql($visit_id).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_booking_visit=pg_fetch_result($result, 0);
    pg_free_result($result);
    
    //свой id
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user=pg_fetch_result($result, 0);
    pg_free_result($result);
    #$org_user_id=445;
    
    if($id_booking_visit){
        if($id_user){
            if($id_owner == '' && $id_pet == ''){#владельца нет, животного нет
                $owner_surname=$_GET["owner_surname"];
                $owner_name=$_GET["owner_name"];
                $owner_secondname=$_GET["owner_secondname"];
                $owner_telephone=$_GET["owner_telephone"];
                $owner_email=$_GET["owner_email"];
                $owner_address=$_GET["owner_address"];
    
                $pet_species=$_GET["pet_species"];#ВИД
                $pet_name=$_GET["pet_name"];#КЛИЧКА
                $pet_breed=$_GET["pet_breed"];#Порода
                $pet_sex=$_GET["pet_sex"];#ПОЛ
    
                $add_new=$_GET["add_new"];
                
                //$owner_address != '' && 
                if($owner_surname != '' && $owner_name != '' && ($owner_telephone != '' && $owner_telephone != '+7') && $pet_name != '' && $pet_breed != '' && $add_new == 1){
                    $id_fias_from_table='';
                    if($owner_address != ''){
                        ##################################
                        ###поиск fias по текцщим данным###
                        ##################################
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
                        ##################################
                        ###поиск fias по текцщим данным###
                        ##################################
                        if(!$id_fias_from_table){//fias адрес не найден в таблице
                            $street_prefix="";
                            if($fias_address->data->street_type == 'ш'){
                                $street_prefix='шоссе';
                            }else{
                                $street_prefix='улица';
                            }

                            $full_address='';
                            
                            if($fias_address->data->city_type_full){
                                $full_address=''.$fias_address->data->region_type_full.' '.$fias_address->data->region.', '.$fias_address->data->city_type_full.' '.$fias_address->data->city.', '.$street_prefix.' '.$fias_address->data->street.', дом '.$fias_address->data->house.'';
                            }else{
                                $full_address=''.$fias_address->data->region_type_full.' '.$fias_address->data->region.', '.$street_prefix.' '.$fias_address->data->street.', дом '.$fias_address->data->house.'';
                            }

                            $lon=$fias_address->data->polygon->coordinates[0][0][0][0];
                            $lat=$fias_address->data->polygon->coordinates[0][0][0][1];

                            //$fias_address->data->adm_area
                            //сравнение адреса
                            //Северный административный округ
                            //в БД
                            //Северный Административный Округ

                            $query='INSERT INTO fias_addresses ';
                            $query.='(full_address, lon, lat, region, city, street, house, cityguid, streetguid, houseguid, roomguid, regionguid, created_by, updated_by, created_at, updated_at) ';
                            $query.='VALUES (';
                            $query.="'".$full_address."',";

                            if($lon){$query.="'".$lon."',";}else{$query.="NULL,";}
                            if($lat){$query.="'".$lat."',";}else{$query.="NULL,";}

                            $query.="'".$fias_address->data->region_type_full." ".$fias_address->data->region."',";	//$query.="'город ".$fias_address->data->region."',";

                            $query.="'".$fias_address->data->city_type_full." ".$fias_address->data->city."',";
                            $query.="'".$street_prefix." ".$fias_address->data->street."',";
                            $query.="'дом ".$fias_address->data->house."',";

                            $query.="'".$fias_address->data->region_fias_id."',";
                            if($fias_address->data->street_fias_id){$query.="'".$fias_address->data->street_fias_id."',";}else{$query.="NULL,";}
				            if($fias_address->data->house_fias_id){$query.="'".$fias_address->data->house_fias_id."',";}else{$query.="NULL,";}
				            $query.="NULL,";

                            //$query.="77,";//Москва
                            $query.="".$fias_address->data->region_fns_code.",";//Остальные

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
                    }

                    #СОЗДАЁМ ВЛАДЕЛЬЦА
                    $query='INSERT INTO pet_owners ';
                    $query.='(f_fio, i_fio, o_fio, fullname, ';
                    
                    if($id_fias_from_table){
                        $query.='id_fias_address, id_fact_fias_address, ';
                    }
                    
                    $query.='created_by, updated_by, created_at, updated_at, is_main) ';
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
					    $query.="".$id_fias_from_table.",";#адрес
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
                    $query.="'+".trim($owner_telephone)."',";
                    $query.="".$id_user.",";#created_by
                    $query.="".$id_user.",";#updated_by
                    $query.="NOW()::timestamp(0),";#created_at
                    $query.="NOW()::timestamp(0),";#updated_at
                    $query.="true,";
                    $query.="true";
                    $query.=');';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    pg_free_result($result);
    
                    if($owner_email){
                        $query='INSERT INTO contacts ';
                        $query.='(id_contact_type, entity_type, entity_id, name, created_by, updated_by, created_at, updated_at, main_flag, confirmed) ';
                        $query.='VALUES (';
                        $query.="6,";
                        $query.="'pet_owner',";
                        $query.="".$id_owner.",";
                        $query.="'".$owner_email."',";
                        $query.="".$id_user.",";#created_by
                        $query.="".$id_user.",";#updated_by
                        $query.="NOW()::timestamp(0),";#created_at
                        $query.="NOW()::timestamp(0),";#updated_at
                        $query.="false,";
                        $query.="false";
                        $query.=');';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        pg_free_result($result);
                    }
                }else{
                    echo xml('<message>empty_fields</message>');
                }
            }
            
            if($id_owner && $id_pet == ''){#владелец есть, животного нет
                $pet_species=$_GET["pet_species"];#ВИД
                $pet_breed=$_GET["pet_breed"];#Порода
                $pet_name=$_GET["pet_name"];#КЛИЧКА
                $pet_sex=$_GET["pet_sex"];#ПОЛ
    
                #СОЗДАЁМ ЖИВОТНОЕ
                $query='INSERT INTO pets ';
                $query.='(name, id_species, id_breed, sex, created_by, updated_by, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="'".$pet_name."',";#Кличка
                $query.="".$pet_species.",";#Вид
                if($pet_breed != 'undefined'){
                    $query.="".$pet_breed.",";#Порода
                }else{
                    $query.="NULL,";#Порода
                }
                $query.="'".$pet_sex."',";#Пол
                #$query.="".$org_user_id.",";#TODO: Организацияid_reg_organization, ди из аль везде проставляют null
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
            
            if($id_owner && $id_pet){
                #посчитать номер визита
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
                
                //считаем время выбранных услуг
                list($services_fulltime,$services_duration,$services_cooldown, $services_name) = calculate_services_time($configuration, $services);
                $servicesArray = explode(";", $services);
               
                #СОЗДАЁМ ВИЗИТ
                $query='INSERT INTO visits ';
                $query.='(status, id_owner, id_pet, id_organization, created_by, updated_by, created_at, updated_at, cooldown, number, time_range, channel, duration, ticket_number, start_dttm, time_range_without_cooldown, type, variety) ';
                $query.='VALUES (';
                $query.="'N',";
                $query.="".$id_owner.",";#Владелец
                $query.="".$id_pet.",";#Животное
                $query.="".$org_id.",";#Организация
                $query.="".$id_user.",";#created_by
                $query.="".$id_user.",";#updated_by
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0),";#updated_at
                $query.="".$services_cooldown.",";#cooldown
                $query.="".$counter.",";#number - по порядку по суткам
                $query.='tsrange(\''.$date.' '.$time.':00\', (\''.$date.' '.$time.':00\'::TIMESTAMP + INTERVAL \''.$services_fulltime.' MINUTES\'), \'[)\'),';#time_range["2021-12-02 14:00:00","2021-12-02 14:20:00")
                $query.="3,";#channel - по умолчанию 3 - телефон
                $query.="".$services_duration.",";#duration
                $query.="'".$ticket_number."',";#ticket_number - 023001 - [02]дата[3]-канал[001]-номер
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
                    visitlogUser($id_visit, 'N');
                }
        
                #ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
                $query='INSERT INTO visit_pets ';
                $query.='(id_pet, id_visit, created_by, updated_by, created_at, updated_at) ';
                $query.='VALUES (';
                $query.="".$id_pet.",";
                $query.="".$id_visit.",";
                $query.="".$id_user.",";#created_by
                $query.="".$id_user.",";#updated_by
                $query.="NOW()::timestamp(0),";#created_at
                $query.="NOW()::timestamp(0)";#updated_at
                $query.=');';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
    
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

                    
                for($i=0; $i<=count($servicesArray);$i++){
                    if($servicesArray[$i]){
                        #ДОБАВЛЯЕМ СВЯЗКУ ВИЗИТ+УСЛУГА+ЖИВОТНОЕ
                        $query='INSERT INTO visits_gov_services ';
                        $query.='(id_visit, id_service, id_pet, count, created_by, updated_by, created_at, updated_at) ';
                        $query.='VALUES (';
                        $query.="".$id_visit.",";#визит
                        $query.="".$servicesArray[$i].",";#услуга
                        $query.="".$id_pet.",";#животное
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

                        //считываем параметры услуги
                        //SELECT * FROM public.gov_services_params WHERE id_service=$servicesArray[$i]


                        //SELECT * FROM public.params WHERE id=[массив параметров услуги] AND visit_flag=true
                        //считваем параметры услуги и предзаполняем их//visit_papam_values
                        $query='INSERT INTO public.visit_param_values(';
                        $query.='num_value, ';
                        $query.='char_value, ';
                        $query.='date_value, ';
                        $query.='dict_value, ';
                        $query.='id_visit, ';
                        $query.='id_param, ';
                        $query.='created_by, ';
                        $query.='updated_by, ';
                        $query.='created_at, ';
                        $query.='updated_at, ';
                        $query.='id_visitservice, ';
                        $query.='id_pet';
                        $query.=')';
                        $query.='VALUES (';

                        $query.='num_value, ';#num_value/float
                        $query.='char_value, ';#char_value/text
                        $query.='date_value, ';#date_value/int
                        $query.='dict_value, ';#dict_value/int

                        $query.="".$id_visit.",";#визит
                        $query.="".$id_param.",";#id_param

                        $query.="".$id_user.",";#created_by
                        $query.="".$id_user.",";#updated_by
                        $query.="NOW()::timestamp(0),";#created_at
                        $query.="NOW()::timestamp(0),";#updated_at

                        $query.="".$id_gov_visit.", ";#id_visitservice/int
                        $query.="".$id_pet."";
                        $query.=');';
                        //
    
                        #ДОБАВЛЯЕМ ЖИВОТНОЕ К ВИЗИТУ
                        $query='INSERT INTO visit_service_pet ';
                        $query.='(id_visits_gov_service, id_pet) ';
                        $query.='VALUES (';
                        $query.="".$id_gov_visit.",";
                        $query.="".$id_pet."";
                        $query.=');';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        pg_free_result($result);
                    }
                }
    
                ###Всё ок достаём все данные по визиту и выводим красивое окошко
                $query='SELECT organizations.short_name, addresses.name FROM organizations ';
                $query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
                $query.='WHERE organizations.id='.$org_id.'';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $org_name=$row[0];
                $org_address=$row[1];
    
                $query='SELECT users.fullname FROM specialists ';
                $query.='LEFT JOIN users ON specialists.id_user=users.id ';
                $query.='WHERE specialists.id='.$specialist_id.'';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $specialist_name=$row[0];
    
                $message='<message>visit_created</message>';
                $message.='<ticket_number>'.$ticket_number.'</ticket_number>';
                $message.='<date>'.$date.'</date>';
                $message.='<time>'.$time.'</time>';
                $message.='<duration>'.$services_fulltime.'</duration>';
                $message.='<services>'.$services_name.'</services>';
                $message.='<specialist>'.$specialist_name.'</specialist>';
                $message.='<organization>'.$org_name.'</organization>';
                $message.='<address>'.$org_address.'</address>';
    
                 
                echo xml($message);
            }
        }else{
            echo xml('<message>invalid_user_id</message>');
        }
    }else{
        echo xml('<message>booking_empty</message>');
    }
}

function cancel_visit($configuration){
    $id_visit=$_GET["id"];
    
    //свой id
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user=pg_fetch_result($result, 0);
    pg_free_result($result);
    #$org_user_id=445;
    
    if($id_user){
        if($id_visit){
            #ОТМЕНЯЕМ ВИЗИТ
            $query='UPDATE visits SET ';
            $query.='status=\'A\', ';
            $query.='cancel_initiator=\'OWNER\',';
            $query.="updated_by=".$id_user.",";#updated_by
            $query.="updated_at=NOW()::timestamp(0)";#updated_at
            $query.=' WHERE id='.$id_visit.';';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);

            if($configuration['version'] == '1.0'){
                visitlogUser($id_visit, 'A');
            }

            echo xml('<message>visit_canceled</message>');
        }else{
            echo xml('<message>invalid_visit_id</message>');
        }
    }else{
        echo xml('<message>invalid_user_id</message>');
    }
}

function save_services_specialists($configuration){
    $params=get_params();
    $services_specialists_array = array();
    
    for ($i=0; $i<count($params["specialist"]); $i++) {
        $query='SELECT id_organization FROM specialists WHERE id=\''.$params["specialist"][$i].'\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $organization=pg_fetch_result($result, 0);
        pg_free_result($result);

        array_push($services_specialists_array, array(
            'specialist' => ''.$params["specialist"][$i].'',
            'service' => ''.$params["service"][$i].'',
            'action' => ''.$params["action_"][$i].'',
            'organization' => ''.$organization.'',
        ));
    }
    //$row_specialists['id_organization'] - узнать организацию специалиста
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user=pg_fetch_result($result, 0);
    pg_free_result($result);

    if(count($services_specialists_array)>0){
        for ($k=0; $k<count($services_specialists_array); $k++) {
            if($services_specialists_array[$k]['action'] == "add"){###добавляем связь
                $query="INSERT INTO services_specialists ";
				$query.="(id_service, id_specialist, id_organization, created_by, updated_by, created_at, updated_at) ";
				$query.="VALUES ('".$services_specialists_array[$k]['service']."', '".$services_specialists_array[$k]['specialist']."', ";
                $query.="'".$services_specialists_array[$k]['organization']."','".$id_user."','".$id_user."', NOW()::timestamp(0), NOW()::timestamp(0));";
				$result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				pg_free_result($result_);
            }
            if($services_specialists_array[$k]['action'] == "delete"){###удаляем связь
                $query_delete="DELETE FROM public.services_specialists WHERE ";
                $query_delete.="id_service=".$services_specialists_array[$k]['service']."";
                $query_delete.=" AND id_specialist=".$services_specialists_array[$k]['specialist']."";
                $query_delete.=" AND id_organization=".$services_specialists_array[$k]['organization']."";
                $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
            }
        }

        echo xml('<message>services_specialists_saved</message>');
    }
}

function save_contacts($configuration){
    $new=$_GET["new"];
    
    //свой id
    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user=pg_fetch_result($result, 0);
    pg_free_result($result);

    if($id_user){
        $id_owner=$_GET["id_owner"];
        if($id_owner == ''){
            echo xml('<message>invalid_owner_id</message>');
            exit;
        }

        if($new == 0){
            $params=get_params();
            
            for ($i=0; $i<count($params["id"]); $i++) {
                $query='UPDATE contacts SET ';
                if($params["type"][$i] == 'mobiletelephone'){
                    $query.='name=\'+7'.$params["value"][$i].'\', ';
                }else{
                    $query.='name=\''.$params["value"][$i].'\', ';
                }
                $query.="updated_by=".$id_user.",";#updated_by
                $query.="updated_at=NOW()::timestamp(0)";#updated_at
                $query.=' WHERE id='. $params["id"][$i].';';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                pg_free_result($result);
            }
        }
        
        if($new == 1){
            $type=$_GET["type"];
            $value=$_GET["value"];
            
            $query='INSERT INTO contacts ';
            $query.='(id_contact_type, entity_type, entity_id, name, created_by, updated_by, created_at, updated_at, main_flag, confirmed) ';
            $query.='VALUES (';
            $query.="".$type.",";
            $query.="'pet_owner',";
            $query.="".$id_owner.",";
            if($type == 1){
                $query.="'+7".trim($value)."',";
            }else{
                $query.="'".$value."',";
            }
            $query.="".$id_user.",";#created_by
            $query.="".$id_user.",";#updated_by
            $query.="NOW()::timestamp(0),";#created_at
            $query.="NOW()::timestamp(0),";#updated_at
            $query.="true,";
            $query.="true";
            $query.=');';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result);
        }
        echo xml('<message>contacts_saved</message>');
    }else{
        echo xml('<message>invalid_user_id</message>');
    }
}

function vallidateToken($configuration){
    if($_COOKIE['login'] && $_COOKIE['token']){
        error_reporting(0);//отключаем ошибки

        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV_TEST') or define('YII_ENV_TEST', 'dev');
        require __DIR__ . $configuration['path_autoload'];
        require __DIR__ . $configuration['path_yii'];
        $config = require __DIR__ . $configuration['path_yii_config'];
        
        (new yii\web\Application($config));
        #$hash = Yii::$app->getSecurity()->generatePasswordHash($_COOKIE['login']);

        if(Yii::$app->getSecurity()->validatePassword($_COOKIE['login'], $_COOKIE['token'])){
            //всё ок - обвновляем время токена
            setcookie('token', $_COOKIE['token'], time()+$configuration['token_time'], '/');
            setcookie('login', $_COOKIE['login'], time()+$configuration['token_time'], '/');
            setcookie('organization', $_COOKIE['organization'], time()+$configuration['token_time'], '/');

            return 1;
        }else{
            setcookie('token', '', time()+$config_['token_time'], '/');
            setcookie('login', '', time()+$config_['token_time'], '/');
            
            return 0;
        }
    }
}

function invalidToken($config_){
    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    setcookie('token', '', time()+$config_['token_time'], '/');
    setcookie('login', '', time()+$config_['token_time'], '/');
    echo '<message>token_invalid</message>';
    echo '</xml>';
}

function createMessages($config){
    $query='DROP TABLE IF EXISTS templates;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    $query='CREATE TABLE IF NOT EXISTS templates (id SERIAL PRIMARY KEY, source varchar(64) not null, code varchar(64) not null, message text not null);';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    $query="INSERT INTO templates (source, code, message) VALUES ('callcenter', 'visit_created', 'Назначен приём на {date} в {time} продожительностью {duration} минут в {organization} по адресу {address}. Специалистом {specialist} будут оказаны следующие услуги: {services}. Талон № <strong>{ticket_number}</strong>');";
    $query.="INSERT INTO templates (source, code, message) VALUES ('callcenter', 'visit_canceled', 'Приём отменен.');";
    $query.="INSERT INTO templates (source, code, message) VALUES ('callcenter', 'visit_cancel_confirm', 'Вы действительно хотите отменить приём?');";
    $query.="INSERT INTO templates (source, code, message) VALUES ('callcenter', 'visit_rescheduled', 'Приём перенесен на {date} в {time}. Приём продожительностью {duration} минут в {organization} по адресу {address} будет осуществлён специалистом {specialist} и будут оказаны следующие услуги: {services}. Талон № <strong>{ticket_number}</strong>');";
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo 'Шаблоны заведены!';
}

function createMetroStations($config){
    $query='DROP TABLE metro_stations;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    //создаём если нет
    $query='CREATE TABLE IF NOT EXISTS metro_stations (id SERIAL PRIMARY KEY, station varchar(128) not null, line varchar(128) not null, area integer not null, coordinates point not null);';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    //очищаем таблицу
    $query='TRUNCATE metro_stations;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    ini_set('max_execution_time', '120');

    $json_url='https://apidata.mos.ru/v1/datasets/624/rows?api_key='.$config['apidatamos_key'].'';
    $json = file_get_contents($json_url);
    $data = json_decode($json, TRUE);

    $metro_stations_array = array();
    for ($k=0; $k<count($data); $k++) {
        if($data[$k]['Cells']['ObjectStatus'] = 'действует'){
            $flag=1;
            foreach ($metro_stations_array as $key => $value) {
                if($value['station'] == $data[$k]['Cells']['NameOfStation']) {
                    $flag=0;
                }
            }
            if($flag == 1){
                $metro_station_area = str_replace("административный округ", "Административный Округ", $data[$k]['Cells']['AdmArea']);

                $query='SELECT id FROM areas WHERE name=\''.$metro_station_area.'\' LIMIT 1';
                #echo $query;
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $area_id=$row[0];
                pg_free_result($result);

                if($area_id == ''){
                    $area_id=0;
                }
                
                array_push($metro_stations_array, array(
                    'station' => ''.$data[$k]['Cells']['NameOfStation'].'',
                    'line' => ''.$data[$k]['Cells']['Line'].'',
                    'area' => ''.$area_id.'',
                    'coordinates' => ''.$data[$k]['Cells']['geoData']['coordinates'][0].','.$data[$k]['Cells']['geoData']['coordinates'][1].'',
                ));
            }
        }
    }

    echo 'Нашлось <strong>'.count($metro_stations_array).'</strong> станций метро.<br>';

    $query='';
    for ($k=0; $k<count($metro_stations_array); $k++) {
        $query.="INSERT INTO metro_stations ";
        $query.="(station, line, area, coordinates) ";
        $query.="VALUES ('".$metro_stations_array[$k]['station']."', '".$metro_stations_array[$k]['line']."', ".$metro_stations_array[$k]['area'].", ";
        $query.=" POINT('".$metro_stations_array[$k]['coordinates']."'));";
    }
    #echo $query;
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo 'Добавлено <strong>'.count($metro_stations_array).'</strong> станций метро.';
}

function convert_num($temp){
    if($temp < 10){
        return '0'.$temp;
    }else{
        return $temp;
    }
}

function exitUser($config){
    $login=string_formating_for_sql($_COOKIE['login']);
    $query='SELECT id FROM users WHERE login=\''.$login.'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $user_id=$row[0];
    pg_free_result($result);

    if($config['version'] == '1.0'){
        logUser($login, $user_id, '2', 'true');
    }

    //стираем cookies
    setcookie('token', '', time()+$config['token_time'], '/');
    setcookie('login', '', time()+$config['token_time'], '/');
    setcookie('organization', '', time()+$config['token_time'], '/');
    header('Location: index.php');
}

function hasHashedPassword($hash){
    return substr($hash, 0, 7) === '$2y$13$';
}

function getIp() {
    $keys = ['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (array_key_exists($key, $_SERVER)){
            return  $_SERVER[$key];
        }
    }
}

function logUser($login, $id_user, $type, $is_success){
    $ip=getIp();
    $ua=$_SERVER['HTTP_USER_AGENT'];
    $target=5; #колл-центр
    $id='';

    if($login && $id_user){
        $query='INSERT INTO audit.log_users_auth ';
        $query.='(login, id_user, target, type, is_success, ip, ua, created_at, updated_at) ';
        $query.='VALUES (';
        $query.="'".$login."',";
        $query.="".$id_user.",";
        $query.="".$target.",";
        $query.="".$type.",";###вход-1/выход-2
        $query.="'".$is_success."',";
        $query.="'".$ip."',";
        $query.="'".$ua."',";
        $query.="NOW()::timestamp(0),";#created_at
        $query.="NOW()::timestamp(0)";#updated_at
        $query.=') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id=$row[0];
        pg_free_result($result);
    }

    return $id;
}

function visitlogUser($id_visit, $status_visit){
    $query='with t as (SELECT * FROM visits WHERE id='.$id_visit.') select json_agg(t) from t;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $snapshot=$row[0];
    pg_free_result($result);

    $query='SELECT id, fullname FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $id_user=$row[0];
    $fio_user=$row[1];
    pg_free_result($result);
    
    $query='INSERT INTO audit.visits_logs ';
    $query.='(id_visit, status_visit, snapshot, initiator, id_user, fio_user, api_version, snapshot_generator_version, id_organization, date) ';
    $query.='VALUES (';
    $query.="".$id_visit.",";
    $query.="'".$status_visit."',";
    $query.="'".$snapshot."',";
    $query.="'CLINIC',";
    $query.="".$id_user.",";
    $query.="'".$fio_user."',";
    $query.="'2',";
    $query.="'2',";
    $query.="".string_formating_for_sql($_COOKIE['organization']).",";
    $query.="NOW()::timestamp(0)";#updated_at
    $query.=') RETURNING id;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $id=$row[0];
    pg_free_result($result);

    return $id;
}

function userCan($configuration, $item_name = null){
    $user_id = user_id($configuration);
    $org_id = user_org_id($configuration);

    if($user_id){
        $query='SELECT id FROM public.specialists WHERE id_user='.$user_id.' AND id_organization='.$org_id.' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $spec_id=$row[0];
        pg_free_result($result);
        
        $query='SELECT id_user FROM public.auth_assignment WHERE id_user='.$user_id.' AND id_specialist='.$spec_id.' AND item_name=\''.$item_name.'\' ';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $can=$row[0];
        pg_free_result($result);

        if($can != ''){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function userCanAuth($user_id, $entry_point){
    if($user_id){
        $can = '';
        $query='SELECT id FROM public.specialists WHERE id_user='.$user_id.'';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $canArray=[];
        //доступы по ролям
        if($entry_point == 'analytics'){
            array_push($canArray, 'sysAdminGos');
            array_push($canArray, 'managementGos');
        }
        if($entry_point == 'duplicates'){
            array_push($canArray, 'sysAdminGos');
            array_push($canArray, 'managementGos');
            array_push($canArray, 'vetSpecGos');
        }
        if($entry_point == 'support'){
            array_push($canArray, 'sysAdminGos');
        }
        if($entry_point == 'service'){
            array_push($canArray, 'sysAdminGos');
            array_push($canArray, 'vetSpecGos');
        }
        if($entry_point == 'shelters'){
            array_push($canArray, 'shelterSysAdmin');
            array_push($canArray, 'shelterFaunaSpecialist');
            array_push($canArray, 'shelterActivityAdmin');
            array_push($canArray, 'shelterVeterinarian');
            array_push($canArray, 'shelterAnimalSocializationSpecialist');
            array_push($canArray, 'shelterFaunaMonitoringSpecialist');
            array_push($canArray, 'specShelter');
            array_push($canArray, 'managementShelter');
            array_push($canArray, 'shelterViewer');
        }
        if($entry_point == 'callcenter'){
            array_push($canArray, 'callCenterOperator');
        }
        if($entry_point == 'ambulance'){
            array_push($canArray, 'dispatcher');
        }

        while ($row = pg_fetch_assoc($result)){
            $query_='SELECT id_user FROM public.auth_assignment WHERE id_user='.$user_id.' AND id_specialist='.$row['id'].' ';

            $query_.=' AND (';
            $query__='';
            for($i=0; $i<=count($canArray);$i++){
                if($canArray[$i]){
                    if($query__){$query__.=" OR ";}
                    $query__.=' item_name=\''.$canArray[$i].'\'';
                }
            }
            $query_.=$query__;
            $query_.=' )';
            $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
            $row_ = pg_fetch_row($result_);
            if($row_[0] != '' && $can == ''){
                $can=$row_[0];
            }
            pg_free_result($result_);
        }
        pg_free_result($result);
        
        if($can != ''){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function recoveryPasswordUser($config_){
    $login=$_POST["login"];

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $user_is_blocked = false;
    
    if($login != ''){
        #ищем пользователя
        $query='SELECT id, is_blocked FROM users WHERE ';
        $query.='login=\''.string_formating_for_sql($login).'\' ';
        $query.='LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $user_id=$row['id'];
            if($row['is_blocked'] == 't'){
                $user_is_blocked=true;
            }
        }
        pg_free_result($result);
       
        if(!$user_id){
            echo '<message>recovery_password_error</message>';
        }else{
            $query='INSERT INTO recovery_password ';
            $query.='(status, login, date) ';
            $query.='VALUES (';
            $query.="0,";
            $query.="'".$login."',";
            $query.="NOW()::timestamp(0)";#created_at
            $query.=') RETURNING id;';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id=$row[0];
            pg_free_result($result);

            if($id){
                echo '<message>ok</message>';
            }
        }
    }else{
        echo '<message>empty_fields</message>';
    }
    echo '</xml>';
}

function authUser($config_){
    $login=$_POST["login"];
    $login_=$_POST["login_"];
    $password=$_POST["password"];
    $organization=$_POST["organization"];
    $token=$_POST["token"];
    $entry_point=$_POST["entry_point"];
    
    header("Content-type: text/xml; charset=utf-8");
    ob_start( );
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    
    if(($login != '' && $password !='') || ($login_ != '' && $organization != '')){
        $user_id='';
        $user_hash='';
        $user_is_blocked=false;
        
        #ищем пользователя
        $query='SELECT id,password,is_blocked FROM users WHERE ';
        if($login){
            $query.='login=\''.string_formating_for_sql($login).'\' ';
        }else{
            $query.='login=\''.string_formating_for_sql($login_).'\' ';
        }
        $query.='LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $user_id=$row['id'];
            $user_hash=$row['password'];
            if($row['is_blocked'] == 't'){
                $user_is_blocked=true;
            }
        }
        pg_free_result($result);

        if(!$user_id){
            echo '<message>auth_error</message>';
            echo '</xml>';
            return true;
        }

        ###ИЩЕМ ЕСТЬ ЛИ У ПОЛЬЗОВАТЕЛЯ ПРАВА ПОД ЭНД ПОИНТ
        if($config_['debug'] == 1){//в режиме дебага
            $user_can=1;
        }else{
            $user_can = userCanAuth($user_id, $entry_point);
        }
        
        $id_organization=0;
        if($user_can && $entry_point == 'ambulance'){
            //id=\''.string_formating_for_sql($id_specialist_dispatcher).'\' AND 
            $query='SELECT id_organization FROM specialists WHERE id_organization=\'666\' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_organization=$row[0];
            pg_free_result($result);

            if($id_organization){
                $user_can=1;                
            }
        }
        ###ИЩЕМ ЕСТЬ ЛИ У ПОЛЬЗОВАТЕЛЯ ПРАВА ПОД ЭНД ПОИНТ
        
        #проверяем пароль
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'dev');
        require __DIR__ . $config_['path_autoload'];
        // подключение файла класса Yii
        require __DIR__ . $config_['path_yii'];
        // загрузка конфигурации приложения
        $config = require __DIR__ . $config_['path_yii_config'];
        // создание и конфигурация приложения, а также вызов метода для обработки входящего запроса

        (new yii\web\Application($config));
        #Yii::$app->getSecurity()->generateRandomString()
        
        $baseurl = $_ENV['LOCAL_API_URL'];

        $validate=0;
        if(hasHashedPassword($user_hash)){
            //пароль старый
            if($config_['debug'] == 1){
                $validate=1;
            }else{
                if(Yii::$app->getSecurity()->validatePassword($password, $user_hash)){
                    //проверка старого пароля
                    $validate=1;
                }   
            }
        }else{
             //пароль новый
             $keyString = 'KEUDfBcb+SiOarQeWwT5G9Zf4R5JZ8zu7x1ClgR+th4=';
             if (empty($keyString)) {//не задан ключ
                 #'You should specify passwordEncryptionKey'
             }
             $key = base64_decode($keyString);
             $user_password = \Yii::$app->security->decryptByKey(base64_decode($user_hash), $key);

             if($password == $user_password){
                $validate=1; 
             }
        }
        
        //echo $entry_point;
        if($user_id && !$user_is_blocked && $user_can && $login != '' && $organization == '' && $token == ''
        ) {
            if($validate){
                $xml='';

                if($config_['debug'] == 1){
                    $xml.='<message>login_ok</message>';
                    $xml.='<login>'.$login.'</login>';
                    $xml.='<token>1111-1111-1111-1111</token>';
                    $xml.='<entry_point>'.$entry_point.'</entry_point>';
                    $xml.='<organizations>';
                    
                    $xml.='<organization>';
                    $xml.='<short_name>Тестовая организация</short_name>';
                    $xml.='<id_organization>1</id_organization>';
                    $xml.='</organization>';
                    
                    $xml.='</organizations>';
                }else{
                    $data = array('login' => $login, 'password' => $password);
                    $response=token_request($baseurl."/v2/user/user/token", $data);
                    $token=$response->result->token->token;
                    
                    $xml.='<message>login_ok</message>';
                    $xml.='<login>'.$login.'</login>';
                    $xml.='<token>'.$token.'</token>';
                    $xml.='<entry_point>'.$entry_point.'</entry_point>';
                    
                    $xml.='<organizations>';
                    if(isset($response->result->organization_options)){
                        $orgs=$response->result->organization_options;

                        if($id_organization){
                            for($l=0;$l<=count($orgs);$l++){
                                if(isset($orgs[$l]->id_organization) && $id_organization == $orgs[$l]->id_organization){
                                    $xml.='<organization>';
                                    $xml.='<short_name>'.string_formating_for_xml($orgs[$l]->short_name).'</short_name>';
                                    $xml.='<id_organization>'.string_formating_for_xml($orgs[$l]->id_organization).'</id_organization>';
                                    $xml.='</organization>';
                                }
                            }
                        }else{
                            for($l=0;$l<=count($orgs);$l++){
                                if(isset($orgs[$l]->id_organization)){
                                    $xml.='<organization>';
                                    $xml.='<short_name>'.string_formating_for_xml($orgs[$l]->short_name).'</short_name>';
                                    $xml.='<id_organization>'.string_formating_for_xml($orgs[$l]->id_organization).'</id_organization>';
                                    $xml.='</organization>';
                                }
                            }
                        }
                    }else{
                        $orgs=$response->result->organizations;
                        for($l=0;$l<=count($orgs);$l++){
                            if(isset($orgs[$l]->id)){
                                $xml.='<organization>';
                                $xml.='<short_name>'.string_formating_for_xml($orgs[$l]->short_name).'</short_name>';
                                $xml.='<id_organization>'.string_formating_for_xml($orgs[$l]->id).'</id_organization>';
                                $xml.='</organization>';
                            }
                        }
                    }
                    $xml.='</organizations>';
                }
                if($config_['version'] == '1.0'){
                    logUser($login, $user_id, '1', 'true');
                }
                
                echo $xml;
            }else{
                if($config_['version'] == '1.0'){
                    logUser($login, $user_id, '1', 'false');
                }
                echo '<message>auth_error</message>';
            }
        }elseif($organization && $token && $login_ != ''){
            setcookie('token', Yii::$app->getSecurity()->generatePasswordHash($login_), time()+$config_['token_time'], '/');
            setcookie('login', $login_, time()+$config_['token_time'], '/');
            setcookie('organization', $organization, time()+$config_['token_time'], '/');

            if($config_['debug'] == 1){
                echo '<token>1111-1111-1111-1111</token>';
                echo '<userid>'.$user_id.'</userid>';
                echo '<entry_point>'.$entry_point.'</entry_point>';
                echo '<message>auth_ok</message>';
            }else{
                ###выбор организации
                $url_organization = $baseurl."/v2/user/user/select-organization";
                $data_organization = array('id_organization' => $organization);
                $response_organization=org_request($url_organization, $data_organization, $token);

                echo '<token>'.$response_organization->result->token->token.'</token>';
                echo '<userid>'.$user_id.'</userid>';
                echo '<entry_point>'.$entry_point.'</entry_point>';
                echo '<message>auth_ok</message>';
            }
        }elseif(!$user_can){
            if($config_['version'] == '1.0'){
                if($user_id){
                    logUser($login, $user_id, '1', 'false');
                }else{
                    logUser($login, 0, '1', 'false');
                }
            }
            echo '<message>auth_error</message>';
        }elseif($user_is_blocked){
            echo '<message>user_is_blocked</message>';
        }else{
            if($config_['version'] == '1.0'){
                logUser($login, 0, '1', 'false');
            }
            echo '<message>auth_error</message>';
        }
    }else{
        echo '<message>empty_fields</message>';
    }

    echo '</xml>';
}

function sub_header_site($configuration = null){
    $user_fullname='';
    $user_organization='';
    if($_COOKIE['login']){
        $query='SELECT fullname FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $user_fullname=$row[0];
        pg_free_result($result);
    }
    if($_COOKIE['organization']){
        if($_COOKIE['organization'] == 1){
            if($configuration){
                $query='SELECT short_name FROM organizations WHERE id=\''.string_formating_for_sql($configuration['test_organization']).'\' LIMIT 1';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $user_organization=$row[0];
                pg_free_result($result);

                $user_organization=$row[0];
            }else{
                $user_organization='Тестовая организация';
            }
        }else{
            $query='SELECT short_name FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $user_organization=$row[0];
            pg_free_result($result);
        }
    }
    
    echo '<div class="row main_row header" style="min-height: auto !important;">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10">';

    echo '<div class="header_site row">';
    echo '<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12 col-12 logo">';
    echo 'Комитет<br />ветеринарии';
    echo '</div>';
    echo '<div class="col-xl-6 col-lg-5 col-md-6 col-sm-6 col-xs-12 col-12 title">ВетАС<br />Ветеринарная автоматизированная система</div>';

    
    echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 user">';
    echo '<div class="col-1 notification_button" onclick="showNotificationsWindow();";>';

    $id_user = user_id($configuration);
    delete_notifications_by_time();
    if($id_user){
        $query='SELECT COUNT(*) FROM notifications ';
        $query.='WHERE id_user=\''.$id_user.'\' AND read=\'f\' ';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $notifications=pg_fetch_result($result, 0);
        pg_free_result($result);

        if($notifications > 0){
            echo '<div class="notifications">'.$notifications.'</div>';
        }
    }

    echo '</div>';
    //Меню
    echo '<div class="col-9 user-logo">'.$user_fullname.'<br /><span>'.$user_organization.'</span></div>';
    //echo '<a href="index.php?action=exit">Выход</a>';
    // echo '<div class="exit" onclick="exitUser();">Выход</div>';
    echo '<div class="col-2 menu_button" onclick="showUserMenu();";></div>';
    echo '<div id="modal_user_menu" class="modal_user_menu w-100" style="display: none;">';
    echo '<div onclick="exitUser();">Выход</div>';
    echo '</div>';


    echo '</div>';
    //Меню
    echo '</div>';

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function sub_footer_site(){
    // echo '</div>';
    // echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    // echo '</div>';
}

function show_districts($config, $type){
    $params=get_params();
    ###Вывод округов
    if($type != 2){
        echo '<div class="label">Район:&nbsp;</div>';
        echo '<div class="input">';
    }

    echo '<select class="district" name="district" multiple="multiple" style="width:100%;">';
    $query='SELECT id,name FROM districts ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $flag=0;

        for ($i=0; $i<count($params["area"]); $i++) {
            if($row['id'] == $params["area"][$i]){
                $flag=1;
            }
        }
        
        if($flag === 1){
            echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
        }else{
            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
    }
    pg_free_result($result);
    echo '</select>';

    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'district\')"></div>';
        echo '</div>';
    }
    ###Вывод округов
}

function show_areas($config, $type){
    $params=get_params();
    ###Вывод округов
    if($type != 2){
        echo '<div class="label">Округ:&nbsp;</div>';
        echo '<div class="input">';
    }

    echo '<select class="area" name="area" multiple="multiple" style="width:100%;" onchange="changeAreas();">';
    $query='SELECT id,name FROM areas ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $flag=0;

        for ($i=0; $i<count($params["area"]); $i++) {
            if($row['id'] == $params["area"][$i]){
                $flag=1;
            }
        }
        
        if($flag === 1){
            echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
        }else{
            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
    }
    pg_free_result($result);
    echo '</select>';

    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'area\')"></div>';
        echo '</div>';
    }
    ###Вывод округов
}

function showAreasV2() {
    $areas = pg_fetch_all(pg_query("SELECT id, name FROM areas ORDER BY name"));
    ?>
    <select class="area" name="area" id="areas-selector" multiple="multiple" style="width:100%;">
    <?php foreach($areas as $area):?>
        <option value="<?php echo $area['id'];?>"><?php echo $area['name'];?></option>
    <?php endforeach;?>
    </select>
    <script>
        const areasSelector = document.querySelector('#areas-selector')
        areasSelector?.addEventListener('change', e => {
            renderOrganizations()
        })
    </script>
    <?php
}

function showOrganizationsV2() {
    $organiztions = 'SELECT id, short_name as name, id_area as area_id FROM organizations ORDER BY short_name';
    ?>
    <select class="organization" name="organization" id="organizations-selector" multiple="multiple" style="width:100%;">
    <?php foreach($organiztions as $organiztion):?>
        <option value="<?php echo $organiztion['id'];?>" data-area="<?php echo $organiztion['area_id'];?>"><?php echo $organiztion['name'];?></option>
    <?php endforeach;?>
    <?php

}

function show_specializations($config, $type){
    $params=get_params();
    ###Вывод специализаций
    if($type != 2){
        echo '<div class="label" style="min-width: 125px;">Специализация:&nbsp;</div>';
        echo '<div class="input" style="min-width: 300px;">';
    }

    echo '<select class="specialization" name="specialization" style="width:100%;">';
    echo '<option value="">Все</option>';
    $query='SELECT id, name FROM specializations ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
    }
    pg_free_result($result);
    echo '</select>';

    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'specialization\')"></div>';
        echo '</div>';
    }
    ###Вывод специализаций
}

function show_organizations($config, $type, $multiple, $filter = null){
    $organization=$_GET["organization"];
    $params=get_params();

    $query='SELECT id, short_name, id_area FROM organizations WHERE ';
    $query.=get_version_query($config);//Ограничиваем организации
    $query.=' ORDER BY short_name ';

    if($type != 2){
        echo '<div class="label">Клиника:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    if($multiple){
        echo '<select class="organization" name="organization" id="organization" multiple="multiple"';
        if($filter){
            echo ' onchange="changeOrganizations();"';
        }
        echo '>';
    }else{
        echo '<select class="organization" name="organization" id="organization"';
        if($filter){
            echo ' onchange="changeOrganizations();"';
        }
        echo '>';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($organization && $type == 1){
            if($organization == $row['id']){
                echo '<option selected value="'.$row['id'].'" area="'.$row['id_area'].'">'.$row['short_name'].'</option>';
            }else{
                echo '<option value="'.$row['id'].'" area="'.$row['id_area'].'">'.$row['short_name'].'</option>';
            }
        }else{
            echo '<option value="'.$row['id'].'" area="'.$row['id_area'].'">'.$row['short_name'].'</option>';
        }
    }

    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'organization\')"></div>';
        echo '</div>';
    }

    pg_free_result($result);
}

function show_brigades($config, $type, $multiple){
    $brigade=$_GET["brigade"];
    $params=get_params();

    $query='SELECT id, name FROM brigades ';
    $query.=' ORDER BY name ';

    if($type != 2){
        echo '<div class="label">Бригада:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    if($multiple){
        echo '<select class="brigade" name="brigade" id="brigade" multiple="multiple">';
    }else{
        echo '<select class="brigade" name="brigade" id="brigade">';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($brigade && $type == 1){
            if($brigade == $row['id']){
                echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
            }else{
                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
            }
        }else{
            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
    }

    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'brigade\')"></div>';
        echo '</div>';
    }

    pg_free_result($result);
}

function show_diseases($config, $type, $multiple){
    $disease=$_GET["disease"];
    $params=get_params();

    $query='SELECT DISTINCT gost_diseases.id, gost_diseases.name, gost_diseases.gost_code FROM diseases ';
    $query.='INNER JOIN gost_diseases ON gost_diseases.id=diseases.id_gost_disease ';
    $query.='ORDER BY gost_diseases.name ';

    if($type != 2){
        echo '<div class="label">Заболевание:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    if($multiple){
        echo '<select class="disease" name="disease" id="disease" multiple="multiple">';
    }else{
        echo '<select class="disease" name="disease" id="disease">';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($disease && $type == 1){
            if($disease == $row['id']){
                echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
            }else{
                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
            }
        }else{
            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
    }

    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'disease\')"></div>';
        echo '</div>';
    }

    pg_free_result($result);
}

function show_breeds($disabled = false) {
    $breeds_array = pg_fetch_all(pg_query("SELECT id, name, species_id FROM breeds ORDER BY name"));
    ?>
    <select class="breed" name="breed" id="breed" <?=$disabled ? 'disabled' : ''?>>
    <? foreach($breeds_array as $breed): ?>
        <option value="<?= $breed['id'] ?>" species_id="<?= $breed['species_id'] ?>"><?= $breed['name'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}

function showEntites($entityTable, $className, $disabled = false) {
    $entities = pg_fetch_all(pg_query("SELECT id, title FROM $entityTable ORDER BY title"));
    ?>
    <select class="<?= $className ?>" name="<?= $className ?>" id="<?= $className ?>" <?=$disabled ? 'disabled' : ''?>>
    <option value="">Не выбран</option>
    <? foreach($entities as $entity): ?>
            <option value="<?= $entity['id'] ?>"><?= $entity['title'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}


function show_colors($disabled = false) {
    $colors = pg_fetch_all(pg_query("SELECT id, title FROM pet_ref_color ORDER BY title"));
    ?>
    <select class="color" name="color" id="color" <?=$disabled ? 'disabled' : ''?>>
    <option value="">Не выбран</option>
    <? foreach($colors as $color): ?>
            <option value="<?= $color['id'] ?>"><?= $color['title'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}

function show_sizes($disabled = false) {
    $sizes = pg_fetch_all(pg_query("SELECT id, title FROM pet_ref_size ORDER BY title"));
    ?>
    <select class="size" name="size" id="size" <?=$disabled ? 'disabled' : ''?>>
    <option value="">Не выбран</option>
    <?
    foreach($sizes as $size) : ?>
        <option value="<?= $size['id'] ?>"><?= $size['title'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}

function show_wools($disabled = false){
    $wools = pg_fetch_all(pg_query("SELECT id, title FROM pet_ref_wool_type ORDER BY title"));
    ?>
    <select class="wool" name="wool" id="wool" <?=$disabled ? 'disabled' : ''?>>
    <option value="">Не выбран</option>
    <?
    foreach($wools as $wool) : ?>
        <option value="<?= $wool['id'] ?>"><?= $wool['title'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}

function show_tails($disabled = false){
    $tails = pg_fetch_all(pg_query("SELECT id, title FROM pet_ref_tail_type ORDER BY title"));
    ?>
    <select class="tail" name="tail" id="tail" <?=$disabled ? 'disabled' : ''?>>
    <option value="">Не выбран</option>
    <?
    foreach($tails as $tail) : ?>
        <option value="<?= $tail['id'] ?>"><?= $tail['title'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}

function show_ears($disabled = false){
    $ears = pg_fetch_all(pg_query("SELECT id, title FROM pet_ref_ear_type ORDER BY title"));
    ?>
    <select class="ear" name="ear" id="ear" <?=$disabled ? 'disabled' : ''?>>
    <option value="">Не выбран</option>
    <?
    foreach($ears as $ear) : ?>
        <option value="<?= $ear['id'] ?>"><?= $ear['title'] ?></option>
    <? endforeach; ?>
    </select>
    <?
}

function show_shelters($config, $type = NULL, $multiple = NULL){
    $organization=$_GET["organization"];
    $params=get_params();

    $query='SELECT id, short_name, id_area FROM organizations WHERE id_org_type=45';
    $query.=' ORDER BY short_name ';

    if($type != 2){
        echo '<div class="label">Приют:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    if($multiple){
        echo '<select class="shelter" name="shelter" id="shelter" multiple="multiple">';
    }else{
        echo '<select class="shelter" name="shelter" id="shelter">';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($organization && $type == 1){
            if($organization == $row['id']){
                echo '<option selected value="'.$row['id'].'" area="'.$row['id_area'].'">'.$row['short_name'].'</option>';
            }else{
                echo '<option value="'.$row['id'].'" area="'.$row['id_area'].'">'.$row['short_name'].'</option>';
            }
        }else{
            echo '<option value="'.$row['id'].'" area="'.$row['id_area'].'">'.$row['short_name'].'</option>';
        }
    }

    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'shelter\')"></div>';
        echo '</div>';
    }

    pg_free_result($result);
}

function show_organizations_types($config, $type, $multiple = NULL, $filter = null){
    $query='SELECT id, name FROM org_types ';
    $query.=' ORDER BY name ';
   
    if($type != 2){
        echo '<div class="label">Тип организации:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    if($multiple){
        echo '<select class="organization_type" name="organization_type" id="organization_type" multiple="multiple"';
        if($filter){
            // echo ' onchange="changeServiceTypes();"';
        }
        echo '>';
    }else{
        echo '<select class="organization_type" name="organization_type" id="organization_type">';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($type && $type == 1){
            if($type == $row['id']){
                echo '<option selected value="'.$row['id'].'">'.$row['_name'].'</option>';
            }else{
                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
            }
        }else{
            echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
    }

    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'organization_type\')"></div>';
        echo '</div>';
    }

    pg_free_result($result);
}

function show_services_type($config, $type = NULL, $multiple = NULL, $filter = null){
    $params=get_params();

    $query='SELECT * FROM service_types ';

    if($type != 2){
        echo '<div class="label">Тип услуги:&nbsp;</div>';
        echo '<div class="input">';
    }

    if($multiple){
        echo '<select class="type" name="type" id="type" multiple="multiple"';
        if($filter){
            echo ' onchange="changeServiceTypes();"';
        }
        echo '>';
    }else{
        echo '<select class="type" name="type" id="type">';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
    }

    echo '</select>';

    if($type != 2){
        echo '</div>';
    }

    pg_free_result($result);
}

function show_species($config, $type = NULL, $multiple = NULL, $filter = null){
    $params=get_params();

    $query='SELECT * FROM species ';

    if($type != 2){
        echo '<div class="label">Вид:&nbsp;</div>';
        echo '<div class="input">';
    }

    if($multiple){
        echo '<select class="type" name="type" id="type" multiple="multiple"';
        if($filter){
            echo ' onchange="changeServiceTypes();"';
        }
        echo '>';
    }else{
        echo '<select class="species" name="species" id="species">';
        echo '<option value="">Все</option>';
    }
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
    }

    echo '</select>';

    if($type != 2){
        echo '</div>';
    }

    pg_free_result($result);
}

function show_services_cod($config){
    $params=get_params();

    echo '<div class="label">Код услуги:&nbsp;</div>';
    echo '<div class="input">';
    echo '<input type="text" name="cod" id="cod">';
    echo '</div>';
}

function show_specialists2($config, $type, $multiple = NULL, $org = NULL){
    ###Вывод врачей
    $docs_array = array();

    $query='SELECT specialists.id AS id,users.fullname AS fullname,users.id AS id_user, specialists.id_organization AS organization FROM specialists ';
    $query.='LEFT JOIN users ON specialists.id_user=users.id ';
    $query.='WHERE specialists.expel_date IS NULL ';
    if($org){
        $query.=' AND id_organization=\''.$org.'\'';
    }
    $query.='ORDER BY users.fullname ';
    
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($docs_array, array(
            'fullname' => ''.$row['fullname'].'',
            'id' => ''.$row['id'].'',
            'id_user' => ''.$row['id_user'].'',
            'org' => ''.$row['organization'].'',
        ));
    }
    pg_free_result($result);

    ksort($docs_array);
    $tempArr = array_unique($docs_array, SORT_REGULAR);

    foreach ($tempArr as $key => $value) {
        foreach ($docs_array as $key1 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $tempArr[$key]["ids"][] = $value2['id'];
            }
        }
    }
    
    $docs_array_final= array();
    $t=0;
    foreach ($tempArr as $key => $value) {
        $flag=1;
        foreach ($docs_array_final as $key2 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $flag=0;
            }
        }
        if($flag == 1){
            array_push($docs_array_final, $tempArr[$t]);
        }
        $t++;
    }
    
    if($type != 2){
        echo '<div class="label">Врач:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    if($multiple){
        echo '<select class="doc" name="doc" multiple="multiple">';
    }else{
        echo '<select class="doc" name="doc" id="doc">';
        echo '<option value="">Все</option>';
    }

    $docs_count = count($docs_array_final);
    for ($i = 0; $i < $docs_count; $i++) {
        if($docs_array_final[$i]['ids']){
            echo '<option org="'.$docs_array_final[$i]['org'].'" value="'.$docs_array_final[$i]['id_user'].'">'.$docs_array_final[$i]['fullname'].'</option>';
        }
    }
    echo '</select>';

    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'doc\')"></div>';
        echo '</div>';
    }
    ###Вывод врачей
}

function show_specialists($config, $type, $multiple = NULL, $org = NULL){
    ###Вывод врачей
    $docs_array = array();

    $query='SELECT specialists.id AS id,users.fullname AS fullname,users.id AS id_user FROM specialists ';
    $query.='LEFT JOIN users ON specialists.id_user=users.id ';
    $query.='WHERE specialists.expel_date IS NULL ';
    if($org){
        $query.=' AND id_organization=\''.$org.'\'';
    }
    $query.='ORDER BY users.fullname ';
    
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($docs_array, array(
            'fullname' => ''.$row['fullname'].'',
            'id' => ''.$row['id'].'',
            'id_user' => ''.$row['id_user'].'',
        ));
    }
    pg_free_result($result);

    ksort($docs_array);
    $tempArr = array_unique($docs_array, SORT_REGULAR);

    foreach ($tempArr as $key => $value) {
        foreach ($docs_array as $key1 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $tempArr[$key]["ids"][] = $value2['id'];
            }
        }
    }
    
    $docs_array_final= array();
    $t=0;
    foreach ($tempArr as $key => $value) {
        $flag=1;
        foreach ($docs_array_final as $key2 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $flag=0;
            }
        }
        if($flag == 1){
            array_push($docs_array_final, $tempArr[$t]);
        }
        $t++;
    }
    
    if($type != 2){
        echo '<div class="label">Врач:&nbsp;</div>';
        echo '<div class="input">';
    }
    
    
    if($multiple){
        echo '<select class="doc" name="doc" multiple="multiple">';
    }else{
        echo '<select class="doc" name="doc" id="doc">';
        echo '<option value="">Все</option>';
    }

    $docs_count = count($docs_array_final);
    for ($i = 0; $i < $docs_count; $i++) {
        if($docs_array_final[$i]['ids']){
            echo '<option value="';

            for ($k=0; $k<count($docs_array_final[$i]['ids']); $k++) {
                if($k>0){echo ',';}
                echo $docs_array_final[$i]['ids'][$k];
            }

            echo '">'.$docs_array_final[$i]['fullname'].'</option>';
        }
    }
    echo '</select>';

    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'doc\')"></div>';
        echo '</div>';
    }
    ###Вывод врачей
}


function get_specialists($config, $type, $multiple = NULL, $org = NULL){
    $docs_array = array();

    $query='SELECT specialists.id AS id,users.fullname AS fullname,users.id AS id_user FROM specialists ';
    $query.='LEFT JOIN users ON specialists.id_user=users.id ';
    $query.='WHERE specialists.expel_date IS NULL ';
    if($org){
        $query.=' AND id_organization=\''.$org.'\'';
    }
    $query.='ORDER BY users.fullname ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($docs_array, array(
            'fullname' => ''.$row['fullname'].'',
            'id' => ''.$row['id'].'',
            'id_user' => ''.$row['id_user'].'',
        ));
    }
    pg_free_result($result);

    ksort($docs_array);
    $tempArr = array_unique($docs_array, SORT_REGULAR);

    foreach ($tempArr as $key => $value) {
        foreach ($docs_array as $key1 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $tempArr[$key]["ids"][] = $value2['id'];
            }
        }
    }

    $docs_array_final= array();
    $t=0;
    foreach ($tempArr as $key => $value) {
        $flag=1;
        foreach ($docs_array_final as $key2 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $flag=0;
            }
        }
        if($flag == 1){
            array_push($docs_array_final, $tempArr[$t]);
        }
        $t++;
    }

    return $docs_array_final;
}

function show_metrostations(){
    $query='SELECT id, station, line, area FROM metro_stations ';
    $query.='ORDER BY station ';
    
    echo '<div class="label">Метро:&nbsp;</div>';
    echo '<div class="input"><select class="metro" name="metro" id="metro" multiple="multiple">';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'" area="'.$row['area'].'">'.$row['station'].'</option>';
    }
    pg_free_result($result);
    echo '</select>';
    echo '<div class="unselect" onclick="resetForm(\'metro\')"></div>';
    echo '</div>';
    ###Вывод врачей
}

function show_services($config, $type = NULL, $multiple = NULL){
    $params=get_params();

    #не выбрано ни одной услуги, принудительно выбираем 1 и 256
    $flag_forcibly=0;
    if(count($params["service"]) == 0){
        $flag_forcibly=1;
    }

    ###Вывод услуг
    if($type != 2){
        echo '<div class="label">Услуга:&nbsp;</div>';
        echo '<div class="input">';
    }

    if($multiple){
        echo '<select class="service" name="service" id="service" multiple="multiple">';
    }else{
        echo '<select class="service" name="service" id="service">';
        echo '<option value="">Все</option>';
    }

    $services_array = array();

    $query='SELECT id,name,id_service_type,cod FROM gov_services ';
    $query.='WHERE deleted=false ';
    
    if($config['debug'] == 0){
        $query.='AND type IS NULL ';
    }

    $query.='ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($services_array, array(
            'id' => ''.$row['id'].'',
            'name' => ''.$row['name'].'',
            'type' => ''.$row['id_service_type'].'',
            'cod' => ''.$row['cod'].''
        ));
    }
    pg_free_result($result);

    $query='SELECT id,name FROM service_types ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<optgroup label="'.$row['name'].'">';

        for ($k=0; $k<count($services_array); $k++) {
            if($services_array[$k]['type'] == $row['id']){
            
                $flag=0;

                for ($i=0; $i<count($params["service"]); $i++) {
                    if($services_array[$k]['id'] == $params["service"][$i]){
                        $flag=1;
                    }
                }

                if($type != 2){
                    if($flag_forcibly == 1 && $services_array[$k]['cod'] == '0201'){
                        $flag=1;
                    }
                }
                
                if($flag == 1){
                    echo '<option selected value="'.$services_array[$k]['id'].'">'.$services_array[$k]['name'].' ['.$services_array[$k]['cod'].']</option>';
                }else{
                    echo '<option value="'.$services_array[$k]['id'].'">'.$services_array[$k]['name'].' ['.$services_array[$k]['cod'].']</option>';
                }
            }
        }

        echo '</optgroup>';
    }
    pg_free_result($result);
        
    
    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'service\')"></div>';
        echo '</div>';
    }
}

function show_services_simple($config, $type = NULL, $multiple = NULL){
    $params=get_params();

    ###Вывод услуг
    if($type != 2){
        echo '<div class="label">Услуга:&nbsp;</div>';
        echo '<div class="input">';
    }

    if($multiple){
        echo '<select class="service" name="service" id="service" multiple="multiple">';
    }else{
        echo '<select class="service" name="service" id="service">';
        echo '<option value="">Все</option>';
    }

    $services_array = array();

    $query='SELECT id,name,id_service_type,cod FROM gov_services ';
    $query.='WHERE deleted=false ';
    
    if($config['debug'] == 0){
        $query.='AND type IS NULL ';
    }

    $query.='ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'" type="'.$row['id_service_type'].'">'.$row['name'].' ['.$row['cod'].']</option>';
    }
    pg_free_result($result);
    
    echo '</select>';
    if($type != 2){
        echo '<div class="unselect" onclick="resetForm(\'service\')"></div>';
        echo '</div>';
    }
}

function show_dates(){
    if(isset($_GET["date"])){
        $date=$_GET["date"];
    }else{
        $date = date("Y-m-d");
    }

    echo '<div class="label" style="width: 45px;">Дата:&nbsp;</div>';
    echo '<div class="input"><div class="label-from">с</div><input name="date-from" class="date date-from" value="'.$date.'" autocomplete="off">';
    echo '<div class="label-to">по</div><input name="date-to" class="date date-to" value="'.$date.'" autocomplete="off">';
    echo '<div class="unselect" onclick="resetForm(\'date\')"></div>';
    echo '</div>';
}
function show_dates_old(){
    if(isset($_GET["date"])){
        $date=$_GET["date"];
    }else{
        $date = date("Y-m-d");
    }

    echo '<div class="label" style="width: 45px;">Дата:&nbsp;</div>';
    echo '<div class="input"><input name="date" class="date" value="'.$date.'" autocomplete="off">';
    echo '<div class="unselect" onclick="resetForm(\'date\')"></div>';
    echo '</div>';
}

function show_times(){
    if(isset($_GET["date"])){
        $date=$_GET["date"];
    }else{
        $date = date("Y-m-d");
    }

    echo '<div class="label">Время:&nbsp;</div>';
    echo '<div class="input">';
    echo '<select class="time" name="time" multiple="multiple">';
    echo '<option value="1">Утро (с 7 до 12)</option>';
    echo '<option value="2">День (с 12 до 17)</option>';
    echo '<option value="3">Вечер (с 17 до 22)</option>';
    echo '<option value="4">Ночь (с 22 до 7)</option>';
    echo '</select>';
    echo '<div class="unselect" onclick="resetForm(\'time\')"></div>';
    echo '</div>';
}

function show_numbers(){
    if(isset($_GET["date"])){
        $date=$_GET["date"];
    }else{
        $date = date("Y-m-d");
    }

    echo '<div class="label">Кол-во:&nbsp;</div>';
    echo '<div class="input"><input name="number" type="number" value="1" autocomplete="off" min="1" max="1" disabled></div>';
}

function show_service_types_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT * FROM service_types ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_document_types_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT * FROM document_types ORDER BY document_types.id';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '<rec_type>'.$row['type'].'</rec_type>';
        echo '<rec_group>'.$row['group'].'</rec_group>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_documents_xml($config){
    //животное
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT * FROM documents ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '<rec_type>'.$row['type'].'</rec_type>';
        echo '<rec_group>'.$row['group'].'</rec_group>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_services_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT gov_services.id,gov_services.name,gov_services.price,gov_services.duration,gov_services.id_service_type, service_types.name AS name_service_type, cooldown FROM gov_services ';
    $query.='LEFT JOIN service_types ON gov_services.id_service_type=service_types.id ';
    $query.='WHERE deleted=false ';
    if($config['debug'] == 0){
        $query.='AND type IS NULL ';
    }
    $query.='ORDER BY name_service_type';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '<rec_price>'.$row['price'].'</rec_price>';
        echo '<rec_duration>'.$row['duration'].'</rec_duration>';
        echo '<rec_cooldown>'.$row['cooldown'].'</rec_cooldown>';
        echo '<rec_type>'.$row['id_service_type'].'</rec_type>';
        echo '<rec_name_service_type>'.$row['name_service_type'].'</rec_name_service_type>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_species_xml($config){
    if(isset($_POST["name"])){$name=$_POST["name"];}else{$name=$_GET["name"];}

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT id, name FROM species ';
    if($name){
        $query.='WHERE name ILIKE \'%'.$name.'%\' ';
    }
    $query.='ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_specializations_xml($config){
    if(isset($_POST["name"])){$name=$_POST["name"];}else{$name=$_GET["name"];}

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT specializations.id, specializations.name, '."\n";
    $query.="((SELECT (string_agg(CONCAT('[',gov_services.cod::character varying,'] ',gov_services.name::character varying), ', ' ORDER BY gov_services.name)) FROM gov_services WHERE gov_services.id_specialization=specializations.id AND gov_services.deleted=false)) AS services ";
    $query.='FROM specializations ';
    if($name){
        $query.='WHERE specializations.name ILIKE \'%'.$name.'%\' ';
    }
    $query.='ORDER BY specializations.name';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '<rec_services>'.$row['services'].'</rec_services>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_diseases_xml($config){
    if(isset($_POST["name"])){$name=$_POST["name"];}else{$name=$_GET["name"];}
    if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
	if($page == ''){$page=1;}

    $recs_on_page=50;//кол-во на странице

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT diseases.id, diseases.name, diseases.cod '."\n";
    //$query.="((SELECT (string_agg(CONCAT('[',gov_services.cod::character varying,'] ',gov_services.name::character varying), ', ' ORDER BY gov_services.name)) FROM gov_services WHERE gov_services.id_specialization=specializations.id AND gov_services.deleted=false)) AS services ";
    $query.='FROM diseases ';
    if($name){
        $query.='WHERE diseases.name ILIKE \'%'.$name.'%\' ';
    }
     //Пагинация
     $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
     $recs_counter = pg_num_rows($result);//количество записей
     pg_free_result($result);
 
     echo '<from>'.intval(($page-1)*$recs_on_page+1).'</from>';
     echo '<to>'.intval(($page)*$recs_on_page).'</to>';
     echo '<current>'.$page.'</current>';
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

    $query.='ORDER BY diseases.name ';
    $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
    
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_cod>'.$row['cod'].'</rec_cod>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        // echo '<rec_services>'.$row['services'].'</rec_services>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function read_notification_xml($config){
    $id=$_GET["id"];

    $query='UPDATE notifications SET read=\'t\' WHERE id_user='.user_id($config).' AND id='.$id.' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    pg_free_result($result);
}

function show_notifications_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
 
    $query='SELECT * FROM notifications ';
	$query.='WHERE id_user=\''.user_id($config).'\' ORDER BY created_at DESC';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_title>'.$row['title'].'</rec_title>';
        echo '<rec_text>'.$row['text'].'</rec_text>';
        echo '<rec_datetime>'.date_format(new \DateTime($row['created_at']), "d.m.Y в h:m").'</rec_datetime>';
        echo '<rec_read>'.$row['read'].'</rec_read>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';

    // $query='UPDATE notifications SET read=\'t\' WHERE id_user='.user_id($config).' ';
    // $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    // $row = pg_fetch_row($result);
    // pg_free_result($result);
}

function show_areas_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT id, name FROM areas ';
    $query.='ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_breeds_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT id, name, species_id FROM breeds ';
    $query.='ORDER BY name';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.$row['name'].'</rec_name>';
        echo '<rec_species>'.$row['species_id'].'</rec_species>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_templates_xml($config){
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT code, message FROM templates WHERE source=\'callcenter\'';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {

        echo '<rec>';
        echo '<rec_code>'.$row['code'].'</rec_code>';
        echo '<rec_message>'.string_formating_for_xml($row['message']).'</rec_message>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}


function fias_address($tokken, $id, $config){
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $tokken",
    ];
    $data = json_encode([], JSON_UNESCAPED_UNICODE);    
    return json_decode(file_get_contents_curl("{$config['api_mos']}/api/fias/13.5/getAddress?fiasId=$id", $headers, $data, true));
}

function fias_search_coordinates($address){
    $query='SELECT lat, lon FROM fias_addresses WHERE ';
    $query.='full_address=\''.$address.'\' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    pg_free_result($result);

    return [$row['lon'], $row['lat']];
}

function fias_search_address($tokken, $address, $config){
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $tokken",
    ];
    $data = json_encode([
        'count' => 10,
        'query' => $address
    ], JSON_UNESCAPED_UNICODE);
    $results = json_decode(file_get_contents_curl("{$config['api_mos']}/api/fias/13.5/searchAll", $headers, $data));
    $data_array = $results->suggestions;
    if(isset($data_array[0]->highlight_value)) return $results->suggestions[0];
}

function fias_search_room($tokken, $house, $room, $config) {
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $tokken",
    ];
    $data = json_encode([
        'count' => 10,
        'house_fias_id' => $house,
        'query' => $room
    ], JSON_UNESCAPED_UNICODE);
    $results = json_decode(file_get_contents_curl("{$config['api_mos']}/api/fias/13.5/searchRoom", $headers, $data));
    $data_array = $results->suggestions;

    if(isset($data_array[0]->highlight_value)){
        return $results->suggestions[0];
    }
}

function show_addresses_xml($config) {
    if(isset($_POST["title"])) $title = $_POST["title"];
    else $title = $_GET["title"];
    if(isset($_POST["tokken"])) $tokken = $_POST["tokken"];
    else $tokken = $_GET["tokken"];
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $tokken",
    ];
    $data = json_encode([
        'count' => 10,
        'query' => $title
    ], JSON_UNESCAPED_UNICODE);
    $results = json_decode(file_get_contents_curl("{$config['api_mos']}/api/fias/13.5/searchAll", $headers, $data));
    if(!$results || isset($results->fault) || isset($results->code)) {
        $res = fias_request($config);
        $res = isset($res) ? $res->access_token : null;
        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer $res",
        ];
        $results = json_decode(file_get_contents_curl("{$config['api_mos']}/api/fias/13.5/searchAll", $headers, $data));
    }
    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    $data_array = $results->suggestions;
    for($l=0; $l <= count($data_array); $l++) {
        if(isset($data_array[$l]->highlight_value)) {
            $str=$data_array[$l]->unrestricted_value;
            echo '<rec>';
            echo '<rec_id>'.$data_array[$l]->data->fias_id.'</rec_id>';
            echo '<rec_title>'.string_formating_for_xml($str).'</rec_title>';
            echo '</rec>';
        }
    }
    echo '</xml>';
}

function file_get_contents_curl( $url, $headers, $data, $get = null ) {

    $ch = curl_init();
  
    curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
    if($get !=  true){
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
    }
    //curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
  
    $data = curl_exec( $ch );
    curl_close( $ch );
  
    return $data;
}

function show_owners_analytics_xml($config){
    if(isset($_POST["name"])){$title=$_POST["name"];}else{$title=$_GET["name"];}

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT pet_owners.id, pet_owners.fullname, fias_addresses.full_address FROM pet_owners ';
    $query.='LEFT JOIN fias_addresses ON pet_owners.id_fias_address=fias_addresses.id ';#адрес
    $query.='WHERE fullname ILIKE \'%'.string_formating_for_sql($title).'%\' ';
    $query.='AND (id_pet_owner_tmp IS NULL)';
    $query.='LIMIT 10';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_name>'.string_formating_for_xml($row['fullname']).'';
        if($row['full_address']){
            echo '('.string_formating_for_xml($row['full_address']).')';
        }
        echo '</rec_name>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_visits_xml($config){
    $date=$_GET["date"];
    $owner_name=$_GET["name"];
    $owner_telephone=$_GET["telephone"];

    $dateFrom = $_GET["date-from"] ?? null;
    $dateTo = $_GET["date-to"] ?? null;

    if ($dateTo == '') {
        $dateTo = "2299-12-31";
    }

    $query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $id_user=$row[0];
    $id_organization=$row[1];
    pg_free_result($result);
    
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='SELECT visits_specialists.id_specialist AS specialist_id, visits.id, visits.status, visits.time_range, visits.id_owner, visits.id_pet, ';
    $query.='organizations.short_name AS org_name, addresses.name AS org_address, visits.ticket_number, pet_owners.fullname AS owner_name, ';
    $query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,';
	$query.='(UPPER(VISITS.TIME_RANGE)::time) AS end_time,';
    $query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, ';
    $query.='string_agg(contacts.id::character varying, \',\') AS contacts ';
    $query.='FROM visits ';
    $query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
    $query.='LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
    $query.='LEFT JOIN organizations ON visits.id_organization=organizations.id ';
    $query.='LEFT JOIN visits_specialists ON visits.id=visits_specialists.id_visit ';
    $query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
    $query.='WHERE ';
    $query.='visits.status=\'N\' AND visits.channel=3 ';
    
    $query.='AND ((LOWER(VISITS.TIME_RANGE)::date) + (LOWER(VISITS.TIME_RANGE)::time)+ INTERVAL \'3 hour\') >= NOW() AND ';
    //$query.='AND (LOWER(VISITS.TIME_RANGE)::date) >= NOW() AND ';

    if($config['debug'] == 1){
        $query.='visits.created_by = '.$id_user.' ';
    }else{
        ###ищем все визиты операторов контактного центра
        $query_='SELECT id_user FROM auth_assignment WHERE item_name=\'callCenterOperator\'';
        $query.='(';
        $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
        $a=0;
        while ($row_ = pg_fetch_assoc($result_)) {
            if($a>0){$query.=' OR ';}
            $query.='visits.created_by = '.$row_['id_user'].' ';
            $a++;
        }
        pg_free_result($result_);
        $query.=')';
        ###ищем все визиты операторов контактного центра
    }
    
    if($owner_name != ''){
        $query.=' AND (';
        $query.='pet_owners.fullname LIKE \''.string_formating_for_sql($owner_name).'%\' OR ';
        $query.='pet_owners.fullname LIKE \'%'.string_formating_for_sql($owner_name).'%\'';
        $query.=')';
    }

    if($owner_telephone != ''){
        $query.=' AND (contacts.name LIKE \'+7'.string_formating_for_sql($owner_telephone).'%\' OR contacts.name LIKE \''.string_formating_for_sql($owner_telephone).'%\')';
    }

    $flag_date_exists=0;
    $query_d='';

    if ($dateFrom) {
        $query_d.='tsrange(\''. $dateFrom .'\'::DATE, \''. $dateTo .'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date';
    }
    else if($date){

        $dates = explode(" - ", $date);

        if (is_array($dates) && count($dates) > 1) {
            $query_d.='tsrange(\''.$dates[0].'\'::DATE, \''.$dates[1].'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> visits.date';
        } else {

            $dates = explode(",", string_formating_for_sql($date));

            for ($i = 0; $i < count($dates); $i++) {
                if (validate_date($dates[$i])) {
                    if ($i > 0) {
                        $query_d .= ' OR ';
                    }
                    $query_d .= '(tsrange(\'' . $dates[$i] . '\'::DATE, \'' . $dates[$i] . '\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> visits.time_range)';
                }
            }
        }
    }

    if($query_d){
        $flag_date_exists=1;
        $query.=' AND ('.$query_d.')'."\n";
    }

    $query.='GROUP BY pet_owners.id, visits_specialists.id_specialist, visits.id, organizations.short_name, addresses.name ';
    $query.='ORDER BY visits.time_range ';
    if(!$flag_date_exists){
        $query.='LIMIT 10';
    }

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $query_='SELECT users.fullname FROM specialists ';
        $query_.='LEFT JOIN users ON specialists.id_user=users.id ';
        $query_.='WHERE specialists.id='.$row['specialist_id'].'';
        $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
        $row_ = pg_fetch_row($result_);
        pg_free_result($result_);
        $specialist_name=$row_[0];

        echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_specialist_name>'.$specialist_name.'</rec_specialist_name>';
        echo '<rec_org_name>'.$row['org_name'].'</rec_org_name>';
        echo '<rec_org_address>'.$row['org_address'].'</rec_org_address>';
        echo '<rec_status>'.$row['status'].'</rec_status>';

        echo '<rec_owner_name>'.$row['owner_name'].'</rec_owner_name>';
        echo '<rec_start_date>'.$row['start_date'].'</rec_start_date>';
        echo '<rec_start_time>'.$row['start_time'].'</rec_start_time>';
        echo '<rec_end_time>'.$row['end_time'].'</rec_end_time>';

        //оказываемые услуги на приёме
        
        $query_='SELECT * FROM visits_gov_services ';
        $query_.='LEFT JOIN gov_services ON visits_gov_services.id_service=gov_services.id ';
        $query_.='WHERE visits_gov_services.id_visit='.$row['id'].'';
        $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
        echo '<rec_services>';
        while ($row_ = pg_fetch_assoc($result_)) {
            echo '<service>';
            echo '<name>'.$row_['name'].'</name>';
            echo '</service>';
        }
        pg_free_result($result_);
        echo '</rec_services>';
        //оказываемые услуги на приёме

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

        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

function show_specialists_xml($config){
    if(isset($_POST["org_type"])){$org_type=$_POST["org_type"];}else{$org_type=$_GET["mode"];}
    if(isset($_POST["name"])){$name=$_POST["name"];}else{$name=$_GET["name"];}

    ###Вывод врачей
    $docs_array = array();

    $query='SELECT specialists.id AS id,specialists.id_organization AS id_organization, organizations.short_name AS name_organization, users.fullname AS fullname,users.id AS id_user FROM specialists ';
    $query.='LEFT JOIN users ON specialists.id_user=users.id ';
    $query.='LEFT JOIN organizations ON specialists.id_organization=organizations.id ';
    $query.='WHERE specialists.expel_date IS NULL AND ';

    if($name){
        $query.=' users.fullname ILIKE \'%'.string_formating_for_sql($name).'%\' AND ';
    }

    if($org_type){
        $query.=' (organizations.id_org_type=\'46\' OR organizations.id_org_type=\'42\')';
    }else{
        $query.=get_version_query($config);//Ограничиваем организации
    }

    #$query.='(organizations.id_org_type=40 OR organizations.id_org_type=41 OR organizations.id_org_type=39 OR organizations.id_org_type=37) ';
    #$query.='(organizations.organization_type_id=40 OR organizations.organization_type_id=41 OR organizations.organization_type_id=39 OR organizations.organization_type_id=37) ';
    $query.='ORDER BY users.fullname ';
    
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($docs_array, array(
            'fullname' => ''.$row['fullname'].'',
            'id' => ''.$row['id'].'',
            'id_user' => ''.$row['id_user'].'',
            'id_organization' => ''.$row['id_organization'].'',
            'name_organization' => ''.$row['name_organization'].'',
        ));
    }
    pg_free_result($result);

    ksort($docs_array);
    $tempArr = array_unique($docs_array, SORT_REGULAR);

    foreach ($tempArr as $key => $value) {
        foreach ($docs_array as $key1 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $tempArr[$key]["ids"][] = $value2['id'];
            }
        }
    }
    
    $docs_array_final= array();
    $t=0;
    foreach ($tempArr as $key => $value) {
        $flag=1;
        foreach ($docs_array_final as $key2 => $value2) {
            if($value['id_user'] == $value2['id_user']) {
                $flag=0;
            }
        }
        if($flag == 1){
            array_push($docs_array_final, $tempArr[$t]);
        }
        $t++;
    }
    
    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $docs_count = count($docs_array_final);
    for ($i = 0; $i < $docs_count; $i++) {
        if($docs_array_final[$i]['ids']){
            echo '<rec>';

            if($org_type){
                echo '<rec_id>'.$docs_array_final[$i]['ids'][0].'</rec_id>';
            }else{
                echo '<rec_ids>';
                for ($k=0; $k<count($docs_array_final[$i]['ids']); $k++) {
                    if($k>0){echo ',';}
                    echo $docs_array_final[$i]['ids'][$k];
                }
                echo '</rec_ids>';
            }

            //echo '<rec_user>'.$docs_array_final[$i]['id_user'].'</rec_user>';

            echo '<rec_id_organization>'.$docs_array_final[$i]['id_organization'].'</rec_id_organization>';
            echo '<rec_name_organization>'.$docs_array_final[$i]['name_organization'].'</rec_name_organization>';

            echo '<rec_name>'.$docs_array_final[$i]['fullname'].'</rec_name>';

            echo '</rec>';
        }
    }
    echo '</xml>';
}

function show_slots_xml($config){
    ###
    $date=$_GET["date"];
    $area = $_GET["area"];
    $doc=$_GET["doc"];
    $service=$_GET["service"];
    $specialization=$_GET["specialization"];

    $dateFrom = $_GET["date-from"] ?? null;
    $dateTo = $_GET["date-to"] ?? null;

    if ($dateTo == '') {
        $dateTo = "2299-12-31";
    }
    ###

    ###Удаляем бронированные записи
    cancel_booking_by_time();

    //считаем время выбранных услуг
    $services_name='';
    $services_id='';
    $services_price=0;
    $services_duration=0;
    $services_cooldown=0;

    $query='SELECT id, name, price, duration, cooldown FROM gov_services ';
    $query.='WHERE deleted=false ';
    $params=get_params();
    if(count($params["service"]) > 0){
        $query.='AND (';
        for ($i=0; $i<count($params["service"]); $i++) {
            if($i>0){
                $query.=' OR ';
            }
            $query.='id = '.intval($params["service"][$i]).'';
        }
        $query.=')';
    }

    if($config['debug'] == 0){
        $query.='AND type IS NULL ';
    }
    
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($services_id != ''){$services_id.=';';}
        $services_id.=$row['id'];
        if($services_name != ''){$services_name.=';';}
        $services_name.=$row['name'];
        $services_price=$services_price+$row['price'];
        $services_duration=$services_duration+$row['duration'];
        if($services_cooldown < $row['cooldown']){
            $services_cooldown=$row['cooldown'];
        }
    }
    pg_free_result($result);
    //считаем время выбранных услуг

    $query='SELECT timesheets.date, timesheets.id AS slot_id, addresses.name AS org_address, '."\n";

    $query.='((SELECT (string_agg(specializations.name::character varying, \';\' ORDER BY users_specializations.id)) FROM users_specializations LEFT JOIN specializations ON users_specializations.id_specialization=specializations.id WHERE users.id=users_specializations.id_user)) AS specializations,'."\n";

    $query.='((SELECT (string_agg(metro_stations.id::character varying, \',\' ORDER BY metro_stations.id)) FROM metro_stations WHERE ST_DistanceSphere(metro_stations.coordinates::geometry, ST_GeomFromText(CONCAT(\'POINT(\', organizations.longitude,\' \', organizations.latitude, \')\'))) <= 1500)) AS metro_stations,'."\n";

    #$query.='(DATE_PART(\'hour\',lower(timesheets.date))) AS start_hour,';
    #$query.='(DATE_PART(\'hour\',upper(timesheets.date))) AS end_hour,';
    #$query.='(DATE_PART(\'minute\',lower(timesheets.date))) AS start_minutes,';
    #$query.='(DATE_PART(\'minute\',upper(timesheets.date))) AS end_minutes,';

    $query.='(DATE_PART(\'hour\', upper(timesheets.date) - lower(timesheets.date)) * 60 + DATE_PART(\'minute\', upper(timesheets.date) - lower(timesheets.date))) AS int_minutes_date,'."\n";
    $query.='(extract(hour from upper(timesheets.date)-lower(timesheets.date))) AS int_date, lower(timesheets.date) AS start_date, upper(timesheets.date) AS end_date, '."\n";
    #$query.='shifts.name, ';
    $query.='specialists.id AS doc_id,'."\n";
    $query.='users.fullname AS doc_name,'."\n";
    
    $query.='organizations.short_name AS org_name,organizations.id AS org_id,organizations.latitude AS org_latitude, organizations.longitude AS org_longitude, areas.name AS org_area, districts.name AS org_district,'."\n";
    
    $query.='string_agg(services.id::character varying, \',\' ORDER BY services.id) AS services '."\n";
    $query.='FROM timesheets '."\n";
    $query.='LEFT JOIN shifts ON timesheets.id_shift=shifts.id '."\n";
    $query.='LEFT JOIN specialists ON timesheets.id_specialist=specialists.id '."\n";
    $query.='LEFT JOIN users ON specialists.id_user=users.id '."\n";

    //$query.='LEFT JOIN users_specializations ON users_specializations.id_user=users.id ';
    //$query.='LEFT JOIN specializations ON users_specializations.id_specialization=specializations.id ';

    $query.='LEFT JOIN organizations ON specialists.id_organization=organizations.id '."\n";
    $query.='LEFT JOIN addresses ON organizations.id_address=addresses.id '."\n";
    $query.='LEFT JOIN areas ON organizations.id_area=areas.id '."\n";
    $query.='LEFT JOIN districts ON organizations.id_district=districts.id '."\n";
    $query.='LEFT JOIN services_specialists ON specialists.id=services_specialists.id_specialist '."\n";
    $query.='LEFT JOIN services ON services.id=services_specialists.id_service '."\n";
    
    $query.='WHERE shifts.id_type=3 '."\n";//Запись только по телефону

    $query.=' AND '.get_version_query($config);

    $flag_date_exists=0;

    $query_d='';

    if ($dateFrom != null) {
        $query_d.='tsrange(\''. $dateFrom .'\'::DATE, \''. $dateTo .'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date';
    }
    else if($date){

        $dates = explode(" - ", $date);

        if (is_array($dates) && count($dates) > 1) {
            $query_d.='tsrange(\''.$dates[0].'\'::DATE, \''.$dates[1].'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date';
        } else {
            $dates = explode(",", $date);

            for ($i=0; $i<count($dates); $i++) {
                if(validate_date($dates[$i])){
                    if($i>0){$query_d.=' OR ';}
                    $query_d.='(tsrange(\''.$dates[$i].'\'::DATE, \''.$dates[$i].'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date)';
                }
            }
        }
    }

    if($query_d){
        $flag_date_exists=1;
        $query.=' AND ('.$query_d.')'."\n";
    }

    $params=get_params();
    if(count($params["area"]) > 0){
        $query_d='';
        for ($i=0; $i<count($params["area"]); $i++) {
            if(intval($params["area"][$i])){
                if($i>0){$query_d.=' OR ';}
                $query_d.='areas.id = '.intval($params["area"][$i]).'';
            }
        }
        if($query_d){
            $query.=' AND ('.$query_d.')'."\n";
        }
    }

    if(count($params["organization"]) > 0){
        $query_d='';
                
        for ($i=0; $i<count($params["organization"]); $i++) {
            if(intval($params["organization"][$i])){
                if($i>0){$query_d.=' OR ';}
                $query_d.='organizations.id = '.intval($params["organization"][$i]).'';
            }
        }
        if($query_d){
            $query.=' AND ('.$query_d.')'."\n";
        }
    }

    if(count($params["doc"]) > 0){
        $query_d='';
        for ($i=0; $i<count($params["doc"]); $i++) {
            if($i>0){$query_d.=' OR ';}
            $docs = explode(",", $params["doc"][$i]);
            
            for ($j=0; $j<count($docs); $j++) {
                if(intval($docs[$j])){
                    if($j>0){$query_d.=' OR ';}
                    $query_d.='timesheets.id_specialist = '.intval($docs[$j]).'';
                }
            }
        }
        if($query_d){
            $query.=' AND ('.$query_d.')'."\n";
        }
    }

    $servicesArray=[];
    if(count($params["service"]) > 0){
        $query.=' AND (';
        for ($i=0; $i<count($params["service"]); $i++) {
            if($i>0){$query.=' OR ';}
            $query.='services.id = '.intval($params["service"][$i]).'';
            array_push($servicesArray, intval($params["service"][$i]));
        }
        $query.=') '."\n";
    }
    
    $query.='GROUP BY timesheets.id, org_address,doc_id,doc_name,org_name,org_id,org_latitude, org_longitude,org_area,org_district, users.id '."\n";
    sort($servicesArray);
    $query.='HAVING string_agg(services.id::character varying, \',\' ORDER BY services.id)=\''.implode(",", $servicesArray).'\' ';
    if($specialization){
        $query.=' AND ';
        $query.='(';
        $query.='(SELECT (string_agg(users_specializations.id_specialization::character varying, \';\')) FROM users_specializations WHERE users.id=users_specializations.id_user) LIKE \'%'.$specialization.';%\' OR ';
        $query.='(SELECT (string_agg(users_specializations.id_specialization::character varying, \';\')) FROM users_specializations WHERE users.id=users_specializations.id_user) LIKE \'%;'.$specialization.'\' OR ';
        $query.='(SELECT (string_agg(users_specializations.id_specialization::character varying, \';\')) FROM users_specializations WHERE users.id=users_specializations.id_user) = \''.$specialization.'\'';
        $query.=')';
    }
    $query.='ORDER BY timesheets.date,timesheets.id ASC ';

    //echo $query;

    header("Content-type: text/xml; charset=utf-8");

    $answer='';
    $answer.='<?xml version="1.0" encoding="UTF-8"?>';
    $answer.='<xml>';

    $answer.='<info>';
    $answer.='<services>'.$services_id.'</services>';
    $answer.='<name>'.$services_name.'</name>';
    $answer.='<price>'.$services_price.'</price>';
    $answer.='<duration>'.$services_duration.'</duration>';
    $answer.='<cooldown>'.$services_cooldown.'</cooldown>';
    
    if(count($params["time"]) > 0){
        for ($i=0; $i<count($params["time"]); $i++) {
            $answer.='<time_'.$params["time"][$i].'>1</time_'.$params["time"][$i].'>';
        }
    }

    $answer.='</info>';

    if(!$flag_date_exists){
        $query.='LIMIT 10';
    }

    if(count($params["service"]) > 0){
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        while ($row = pg_fetch_assoc($result)) {
            //считаем количество слотов
            $time_services=intval($services_duration+$services_cooldown);
            $slots_services=$time_services/10;

            //если количество слотов услуг превышает кол-во свободных то не показываем запись
            if(intval($time_services) <= intval($row['int_minutes_date'])){
                //ищем визиты
                $visits_array = array();

                $q='SELECT visits.*,lower(time_range) AS time_lower, upper(time_range) AS time_upper FROM visits ';
			    $q.='LEFT JOIN visits_specialists ON visits_specialists.id_visit = visits.id ';
			    $q.='WHERE (visits_specialists.id_specialist='.$row['doc_id'].') AND (status NOT IN (\'A\', \'T\')) AND (channel != 4) AND (type != \'AMBULANCE\') ';
			    $q.='AND ((visits.time_range && \'["'.$row['start_date'].'","'.$row['end_date'].'")\'::tsrange))';
                $result_v = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
                while ($row_v = pg_fetch_assoc($result_v)) {
                    array_push($visits_array, array(
                        'time_lower' => ''.$row_v['time_lower'].'',
                        'time_upper' => ''.$row_v['time_upper'].'',
                    ));
                }
                pg_free_result($result_v);
                //ищем визиты
               
                $answer_rec='<rec>'."\n";
                $answer_rec.='<rec_id>'.$row['slot_id'].'</rec_id>';
                
                $answer_rec.='<rec_doc_id>'.$row['doc_id'].'</rec_doc_id>';
                $answer_rec.='<rec_doc_name>'.$row['doc_name'].'</rec_doc_name>';

                $answer_rec.='<rec_doc_specializations>'.$row['specializations'].'</rec_doc_specializations>';

                $answer_rec.='<rec_org_id>'.$row['org_id'].'</rec_org_id>';
                $answer_rec.='<rec_org_name>'.$row['org_name'].'</rec_org_name>';
                $answer_rec.='<rec_org_area>'.$row['org_area'].'</rec_org_area>';
                $answer_rec.='<rec_org_district>'.$row['org_district'].'</rec_org_district>';
                $answer_rec.='<rec_org_address>'.$row['org_address'].'</rec_org_address>';

                #ищем близжайшие станции метро
                $flag_metro_search=0;
                $flag_metro_found=0;

                if(count($params["metro"]) > 0){
                    $flag_metro_search=1;
                }
                
                $metro_stations = explode(",", $row['metro_stations']);
                if(count($metro_stations) > 0 && $row['metro_stations'] != ''){
                    $answer_rec.='<rec_org_stations>';
                    $query="SELECT id, station, line, ST_DistanceSphere(coordinates::geometry, ST_GeomFromText('POINT(".$row['org_longitude']." ".$row['org_latitude'].")')) AS distance FROM metro_stations ";
                    $query.="WHERE ";
                    $k=0;
                    for ($j=0; $j<count($metro_stations); $j++) {
                        if($metro_stations[$j] != ''){
                            if($k>0){$query.=' OR ';}
                            $query.='id = '.string_formating_for_sql($metro_stations[$j]).' ';
                            $k++;
                        }
                    }
                    $query.="ORDER BY distance";

                    $result_m = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    while ($row_m = pg_fetch_assoc($result_m)) {
                        $answer_rec.='<station>';
                        $answer_rec.='<line>'.$row_m['line'].'</line>';
                        $answer_rec.='<color>'.get_metro_line($row_m['line']).'</color>';
                        $answer_rec.='<title>'.$row_m['station'].'</title>';
                        if($row_m['distance'] != 0){
                            $answer_rec.='<distance>'.sprintf("%01.2f", $row_m['distance']/1000).'</distance>';
                        }else{
                            $answer_rec.='<distance>'.sprintf("%01.2f", 0).'</distance>';
                        }
                        if(count($params["metro"]) > 0){
                            for ($i=0; $i<count($params["metro"]); $i++) {
                                if($row_m['id'] == $params["metro"][$i]){
                                    #метро совпало
                                    $flag_metro_found=1;
                                }
                            }
                        }
                        $answer_rec.='</station>';
                    }
                    pg_free_result($result_m);
                    $answer_rec.='</rec_org_stations>';
                    #ищем близжайшие станции метро
                }

                $answer_rec.='<rec_start_date>'.$row['start_date'].'</rec_start_date>';
                $answer_rec.='<rec_end_date>'.$row['end_date'].'</rec_end_date>';

                $answer_rec.='<rec_minutes>'.($row['int_minutes_date']/10).'</rec_minutes>';

                $answer_rec.='<rec_slots>';

                ####################################################################
                ###добавляем слоты с mos.ru, те котрые не совпадают с телефонными###
                ####################################################################
                $query_m='SELECT lower(timesheets.date) AS start_date, upper(timesheets.date) AS end_date, timesheets.date, timesheets.id AS slot_id ';
                $query_m.='FROM timesheets ';
                $query_m.='LEFT JOIN shifts ON timesheets.id_shift=shifts.id ';
                $query_m.='LEFT JOIN specialists ON timesheets.id_specialist=specialists.id ';
                $query_m.='LEFT JOIN users ON specialists.id_user=users.id ';
                $query_m.='WHERE shifts.id_type=2 AND ';//Запись mos.ru
                $query_m.='specialists.id='.$row['doc_id'].' AND ';
                $query_m.='(tsrange(\''.$row['start_date'].'\'::DATE, \''.$row['start_date'].'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date) LIMIT 1';
                $result_m = pg_query($query_m) or die('Ошибка запроса: ' . pg_last_error());
                $row_m = pg_fetch_row($result_m);
                $start_date_mos=$row_m[0];
                $end_date_mos=$row_m[1];
                $period_mos = new \DatePeriod(new \DateTime($start_date_mos),new \DateInterval('PT10M'),new \DateTime($end_date_mos));
                ####################################################################
                ###добавляем слоты с mos.ru, те котрые не совпадают с телефонными###
                ####################################################################
                                                
                $period = new \DatePeriod(new \DateTime($row['start_date']),new \DateInterval('PT10M'),new \DateTime($row['end_date']));
                $slots = [];
                
                foreach ($period as $slot) {
                    #$status='UNAVAILABLE';//По умолчанию не доступен
                    
                    if ($slot->getTimestamp() < time()) {//запрет ретро записей
                        # || $specialist->isExpelledAtDate($slot->format('Y-m-d')) // TODO: специалист уволен
                        $status='UNAVAILABLE';
                    }else{
                        $status='FREE';
                    }

                    foreach ($visits_array as $visit) {
                        // для существующих приёмов помечаем слоты занятыми
                        if ($slot >= new \DateTime($visit['time_lower']) && $slot < new \DateTime($visit['time_upper'])) {
                            $status='VISIT';
                            break;
                        }
                    }

                    $time_of_day=0;
                    if ($slot >= new \DateTime($slot->format('Y-m-d').' 07:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '12:00:00')) {
                        $time_of_day=1;
                    }else if ($slot >= new \DateTime($slot->format('Y-m-d').' 12:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '17:00:00')) {
                        $time_of_day=2;
                    }else if ($slot >= new \DateTime($slot->format('Y-m-d').' 17:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '22:00:00')) {
                        $time_of_day=3;
                    }else if ($slot >= new \DateTime($slot->format('Y-m-d').' 00:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '07:00:00') || $slot >= new \DateTime($slot->format('Y-m-d').' 22:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '23:59:59')) {
                        $time_of_day=4;
                    }

                    $slots[] = [
                        'time' => $slot->format('H:i'),
                        'date' => $slot->format('Y-m-d'),
                        'status' => $status,
                        'time_of_day' => $time_of_day,
                    ];
                }

                $reversedSlots = array_reverse($slots);
                $excludedCount = 0;
                foreach ($reversedSlots as &$slot){
                    if ($slot['status'] !== 'FREE'){
                        $excludedCount = 0; // Интервал который мы могли вычеркивать - уже закончился, обнуляем
                        continue;
                    }

                    //Для записи на 2 слота пометить только один последний как недоступный
                    if ($excludedCount > ($slots_services - 2)){
                        continue;
                    }

                    //Начинаем вычеркивать слоты, которых не хватит под указанные услуги
                    $slot['status'] = 'INSUFFICIENT_DURATION';
                    $excludedCount++;
                }
                unset($slot);

                $slots=array_reverse($reversedSlots);
                
                ###Добаляем слоты с mos.ru
                foreach ($period_mos as $slot_mos) {
                    $flag_add=1;
                    foreach ($slots as $slot) {
                        if($slot_mos->format('H:i') == $slot['time']){
                            $flag_add=0;
                        }
                    }

                    if($flag_add == 1){
                        $time_of_day=0;

                        if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 07:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '12:00:00')) {
                            $time_of_day=1;
                        }else if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 12:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '17:00:00')) {
                            $time_of_day=2;
                        }else if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 17:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '22:00:00')) {
                            $time_of_day=3;
                        }else if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 00:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '07:00:00') || $slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 22:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '23:59:59')) {
                            $time_of_day=4;
                        }

                        $slots[] = [
                            'time' => $slot_mos->format('H:i'),
                            'date' => $slot_mos->format('Y-m-d'),
                            'status' => 'MOS',
                            'time_of_day' => $time_of_day,
                        ];
                    }
                }
                ###Добаляем слоты с mos.ru
                
                sort_by_column($slots, 'time');
                foreach ($slots as $slot) {
                    $answer_rec.='<slot>';
                    $answer_rec.='<time_of_day>'.$slot['time_of_day'].'</time_of_day>';
                    $answer_rec.='<time>'.$slot['time'].'</time>';
                    $answer_rec.='<date>'.$slot['date'].'</date>';
                    $answer_rec.='<status>'.$slot['status'].'</status>';
                    $answer_rec.='</slot>';
                }
                $answer_rec.='</rec_slots>';

                $first_free_slot='';
                ###Ищем первый доступный слот
                foreach ($slots as $slot) {
                    if($slot['status'] == 'FREE'){
                        $first_free_slot=$slot['date'].' '.$slot['time'];
                        break;
                    }
                }
                $answer_rec.='<rec_first_free_slot>'.$first_free_slot.'</rec_first_free_slot>';
                ###Ищем первый доступный слот
                
                $answer_rec.='<rec_services>';
                $services = explode(",", $row['services']);
                for($i=0; $i<count($services);$i++){
                    $answer_rec.='<service>';
                    $answer_rec.='<id>'.$services[$i].'</id>';
                    $answer_rec.='</service>';
                }
                $answer_rec.='</rec_services>';

                $answer_rec.='</rec>'."\n";

                if(($flag_metro_search == 1 && $flag_metro_found) == 1 || $flag_metro_search == 0){
                    $answer.=$answer_rec;
                }
            }
        }
        
        pg_free_result($result);
        
    }else{
        $answer.='<message>service_not_selected</message>';
    }
    $answer.='</xml>';

    echo $answer;
}

function show_schedule_xml($config){
    ###
    $doc=$_GET["doc"];
    $dateFrom = $_GET["date-from"] ?? "1990-01-01";
    $dateTo = $_GET["date-to"] ?? "2100-01-01";

    $dateFrom = $dateFrom == '' ? "1990-01-01" : $dateFrom;
    $dateTo = $dateTo == '' ? "2100-01-01" : $dateTo;

    //считаем время выбранных услуг
    $services_name='';
    $services_id='';
    $services_price=0;
    $services_duration=0;
    $services_cooldown=0;

    $query='SELECT id, name, price, duration, cooldown FROM gov_services ';
    $query.='WHERE deleted=false ';

    if($config['debug'] == 0){
        $query.='AND type IS NULL ';
    }

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        if($services_id != ''){$services_id.=';';}
        $services_id.=$row['id'];
        if($services_name != ''){$services_name.=';';}
        $services_name.=$row['name'];
        $services_price=$services_price+$row['price'];
        $services_duration=$services_duration+$row['duration'];
        if($services_cooldown < $row['cooldown']){
            $services_cooldown=$row['cooldown'];
        }
    }
    pg_free_result($result);
    //считаем время выбранных услуг

    $query='SELECT timesheets.date, timesheets.id AS slot_id, addresses.name AS org_address, st.type, '."\n";

    $query.='((SELECT (string_agg(specializations.name::character varying, \';\' ORDER BY users_specializations.id)) FROM users_specializations LEFT JOIN specializations ON users_specializations.id_specialization=specializations.id WHERE users.id=users_specializations.id_user)) AS specializations,'."\n";

    $query.='((SELECT (string_agg(metro_stations.id::character varying, \',\' ORDER BY metro_stations.id)) FROM metro_stations WHERE ST_DistanceSphere(metro_stations.coordinates::geometry, ST_GeomFromText(CONCAT(\'POINT(\', organizations.longitude,\' \', organizations.latitude, \')\'))) <= 1500)) AS metro_stations,'."\n";

    $query.='(DATE_PART(\'hour\', upper(timesheets.date) - lower(timesheets.date)) * 60 + DATE_PART(\'minute\', upper(timesheets.date) - lower(timesheets.date))) AS int_minutes_date,'."\n";
    $query.='(extract(hour from upper(timesheets.date)-lower(timesheets.date))) AS int_date, lower(timesheets.date) AS start_date, upper(timesheets.date) AS end_date, '."\n";
    #$query.='shifts.name, ';
    $query.='specialists.id AS doc_id,'."\n";
    $query.='users.fullname AS doc_name,'."\n";

    $query.='organizations.short_name AS org_name,organizations.id AS org_id,organizations.latitude AS org_latitude, organizations.longitude AS org_longitude, areas.name AS org_area, districts.name AS org_district,'."\n";

    $query.='string_agg(services.id::character varying, \',\' ORDER BY services.id) AS services '."\n";
    $query.='FROM timesheets '."\n";
    $query.='LEFT JOIN shifts ON timesheets.id_shift=shifts.id '."\n";
    $query.='left join shift_type st on shifts.id_type = st.id '."\n";
    $query.='LEFT JOIN specialists ON timesheets.id_specialist=specialists.id '."\n";
    $query.='LEFT JOIN users ON specialists.id_user=users.id '."\n";

    //$query.='LEFT JOIN users_specializations ON users_specializations.id_user=users.id ';
    //$query.='LEFT JOIN specializations ON users_specializations.id_specialization=specializations.id ';

    $query.='LEFT JOIN organizations ON specialists.id_organization=organizations.id '."\n";
    $query.='LEFT JOIN addresses ON organizations.id_address=addresses.id '."\n";
    $query.='LEFT JOIN areas ON organizations.id_area=areas.id '."\n";
    $query.='LEFT JOIN districts ON organizations.id_district=districts.id '."\n";
    $query.='LEFT JOIN services_specialists ON specialists.id=services_specialists.id_specialist '."\n";
    $query.='LEFT JOIN services ON services.id=services_specialists.id_service '."\n";

    $query.='WHERE '."\n";//Запись только по телефону

    //$query.=' AND '.get_version_query($config);

    $query_d ='tsrange(\''. $dateFrom .'\'::DATE, \''. $dateTo .'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date';


    $query.=' ('.$query_d.')'."\n";

    $params=get_params();
    if(count($params["doc"]) > 0){
        $query_d='';
        for ($i=0; $i<count($params["doc"]); $i++) {
            if($i>0){$query_d.=' OR ';}
            $docs = explode(",", $params["doc"][$i]);

            for ($j=0; $j<count($docs); $j++) {
                if(intval($docs[$j])){
                    if($j>0){$query_d.=' OR ';}
                    $query_d.='timesheets.id_specialist = '.intval($docs[$j]).'';
                }
            }
        }
        if($query_d){
            $query.=' AND ('.$query_d.')'."\n";
        }
    }

    $query.='GROUP BY timesheets.id, org_address,doc_id,doc_name,org_name,org_id,org_latitude, org_longitude,org_area,org_district, users.id, st.type '."\n";
    $query.='ORDER BY timesheets.date,timesheets.id ASC ';

    header("Content-type: text/xml; charset=utf-8");

    $answer='';
    $answer.='<?xml version="1.0" encoding="UTF-8"?>';
    $answer.='<xml>';

    $answer.='<info>';
    $answer.='<services>'.$services_id.'</services>';
    $answer.='<name>'.$services_name.'</name>';
    $answer.='<price>'.$services_price.'</price>';
    $answer.='<duration>'.$services_duration.'</duration>';
    $answer.='<cooldown>'.$services_cooldown.'</cooldown>';

    if(count($params["time"]) > 0){
        for ($i=0; $i<count($params["time"]); $i++) {
            $answer.='<time_'.$params["time"][$i].'>1</time_'.$params["time"][$i].'>';
        }
    }

    $answer.='</info>';

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        while ($row = pg_fetch_assoc($result)) {
            //считаем количество слотов
            $time_services=intval($services_duration+$services_cooldown);
            $slots_services=$time_services/10;

            //если количество слотов услуг превышает кол-во свободных то не показываем запись

                //ищем визиты
                $visits_array = array();

                $q='SELECT visits.*,lower(time_range) AS time_lower, upper(time_range) AS time_upper FROM visits ';
			    $q.='LEFT JOIN visits_specialists ON visits_specialists.id_visit = visits.id ';
			    $q.='WHERE (visits_specialists.id_specialist='.$row['doc_id'].') AND (status NOT IN (\'A\', \'T\')) AND (channel != 4) AND (type != \'AMBULANCE\') ';
			    $q.='AND ((visits.time_range && \'["'.$row['start_date'].'","'.$row['end_date'].'")\'::tsrange))';
                $result_v = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
                while ($row_v = pg_fetch_assoc($result_v)) {
                    array_push($visits_array, array(
                        'time_lower' => ''.$row_v['time_lower'].'',
                        'time_upper' => ''.$row_v['time_upper'].'',
                    ));
                }
                pg_free_result($result_v);
                //ищем визиты

                $answer_rec='<rec>'."\n";
                $answer_rec.='<rec_id>'.$row['slot_id'].'</rec_id>';

                $answer_rec.='<rec_doc_id>'.$row['doc_id'].'</rec_doc_id>';
                $answer_rec.='<rec_doc_name>'.$row['doc_name'].'</rec_doc_name>';

                $answer_rec.='<rec_doc_specializations>'.$row['specializations'].'</rec_doc_specializations>';

                $answer_rec.='<rec_org_id>'.$row['org_id'].'</rec_org_id>';
                $answer_rec.='<rec_org_name>'.$row['org_name'].'</rec_org_name>';
                $answer_rec.='<rec_org_area>'.$row['org_area'].'</rec_org_area>';
                $answer_rec.='<rec_org_district>'.$row['org_district'].'</rec_org_district>';
                $answer_rec.='<rec_org_address>'.$row['org_address'].'</rec_org_address>';

                #ищем близжайшие станции метро
                $flag_metro_search=0;
                $flag_metro_found=0;

                if(count($params["metro"]) > 0){
                    $flag_metro_search=1;
                }

                $metro_stations = explode(",", $row['metro_stations']);
                if(count($metro_stations) > 0 && $row['metro_stations'] != ''){
                    $answer_rec.='<rec_org_stations>';
                    $query="SELECT id, station, line, ST_DistanceSphere(coordinates::geometry, ST_GeomFromText('POINT(".$row['org_longitude']." ".$row['org_latitude'].")')) AS distance FROM metro_stations ";
                    $query.="WHERE ";
                    $k=0;
                    for ($j=0; $j<count($metro_stations); $j++) {
                        if($metro_stations[$j] != ''){
                            if($k>0){$query.=' OR ';}
                            $query.='id = '.string_formating_for_sql($metro_stations[$j]).' ';
                            $k++;
                        }
                    }
                    $query.="ORDER BY distance";

                    $result_m = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    while ($row_m = pg_fetch_assoc($result_m)) {
                        $answer_rec.='<station>';
                        $answer_rec.='<line>'.$row_m['line'].'</line>';
                        $answer_rec.='<color>'.get_metro_line($row_m['line']).'</color>';
                        $answer_rec.='<title>'.$row_m['station'].'</title>';
                        if($row_m['distance'] != 0){
                            $answer_rec.='<distance>'.sprintf("%01.2f", $row_m['distance']/1000).'</distance>';
                        }else{
                            $answer_rec.='<distance>'.sprintf("%01.2f", 0).'</distance>';
                        }
                        if(count($params["metro"]) > 0){
                            for ($i=0; $i<count($params["metro"]); $i++) {
                                if($row_m['id'] == $params["metro"][$i]){
                                    #метро совпало
                                    $flag_metro_found=1;
                                }
                            }
                        }
                        $answer_rec.='</station>';
                    }
                    pg_free_result($result_m);
                    $answer_rec.='</rec_org_stations>';
                    #ищем близжайшие станции метро
                }

                $answer_rec.='<rec_start_date>'.$row['start_date'].'</rec_start_date>';
                $answer_rec.='<rec_end_date>'.$row['end_date'].'</rec_end_date>';

                $answer_rec.='<rec_minutes>'.($row['int_minutes_date']/10).'</rec_minutes>';

                $answer_rec.='<rec_slots>';

                ####################################################################
                ###добавляем слоты с mos.ru, те котрые не совпадают с телефонными###
                ####################################################################
                $query_m='SELECT lower(timesheets.date) AS start_date, upper(timesheets.date) AS end_date, timesheets.date, timesheets.id AS slot_id ';
                $query_m.='FROM timesheets ';
                $query_m.='LEFT JOIN shifts ON timesheets.id_shift=shifts.id ';
                $query_m.='LEFT JOIN specialists ON timesheets.id_specialist=specialists.id ';
                $query_m.='LEFT JOIN users ON specialists.id_user=users.id ';
                $query_m.='WHERE shifts.id_type=2 AND ';//Запись mos.ru
                $query_m.='specialists.id='.$row['doc_id'].' AND ';
                $query_m.='(tsrange(\''.$row['start_date'].'\'::DATE, \''.$row['start_date'].'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> timesheets.date) LIMIT 1';
                $result_m = pg_query($query_m) or die('Ошибка запроса: ' . pg_last_error());
                $row_m = pg_fetch_row($result_m);
                $start_date_mos=$row_m[0];
                $end_date_mos=$row_m[1];
                $period_mos = new \DatePeriod(new \DateTime($start_date_mos),new \DateInterval('PT10M'),new \DateTime($end_date_mos));
                ####################################################################
                ###добавляем слоты с mos.ru, те котрые не совпадают с телефонными###
                ####################################################################

                $period = new \DatePeriod(new \DateTime($row['start_date']),new \DateInterval('PT10M'),new \DateTime($row['end_date']));
                $slots = [];

                foreach ($period as $slot) {
                    #$status='UNAVAILABLE';//По умолчанию не доступен

                    if ($row["type"] == 'HOLIDAY') {
                      $status = 'HOLIDAY';
                    } else if ($row["type"] == 'VACATION') {
                      $status = 'VACATION';
                    } else if ($row["type"] == 'SICK_LEAVE') {
                      $status = 'SICK_LEAVE';
                    }
                    else {
                        $status='FREE';
                    }

                    foreach ($visits_array as $visit) {
                        // для существующих приёмов помечаем слоты занятыми
                        if ($slot >= new \DateTime($visit['time_lower']) && $slot < new \DateTime($visit['time_upper'])) {
                            $status='VISIT';
                            break;
                        }
                    }

                    $time_of_day=0;
                    if ($slot >= new \DateTime($slot->format('Y-m-d').' 07:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '12:00:00')) {
                        $time_of_day=1;
                    }else if ($slot >= new \DateTime($slot->format('Y-m-d').' 12:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '17:00:00')) {
                        $time_of_day=2;
                    }else if ($slot >= new \DateTime($slot->format('Y-m-d').' 17:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '22:00:00')) {
                        $time_of_day=3;
                    }else if ($slot >= new \DateTime($slot->format('Y-m-d').' 00:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '07:00:00') || $slot >= new \DateTime($slot->format('Y-m-d').' 22:00:00') && $slot < new \DateTime($slot->format('Y-m-d'). '23:59:59')) {
                        $time_of_day=4;
                    }

                    $slots[] = [
                        'time' => $slot->format('H:i'),
                        'date' => $slot->format('Y-m-d'),
                        'status' => $status,
                        'time_of_day' => $time_of_day,
                    ];
                }

                  ###Добаляем слоты с mos.ru
                foreach ($period_mos as $slot_mos) {
                    $flag_add=1;
                    foreach ($slots as $slot) {
                        if($slot_mos->format('H:i') == $slot['time']){
                            $flag_add=0;
                        }
                    }

                    if($flag_add == 1){
                        $time_of_day=0;

                        if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 07:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '12:00:00')) {
                            $time_of_day=1;
                        }else if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 12:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '17:00:00')) {
                            $time_of_day=2;
                        }else if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 17:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '22:00:00')) {
                            $time_of_day=3;
                        }else if ($slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 00:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '07:00:00') || $slot_mos >= new \DateTime($slot_mos->format('Y-m-d').' 22:00:00') && $slot_mos < new \DateTime($slot_mos->format('Y-m-d'). '23:59:59')) {
                            $time_of_day=4;
                        }

                        $slots[] = [
                            'time' => $slot_mos->format('H:i'),
                            'date' => $slot_mos->format('Y-m-d'),
                            'status' => 'MOS',
                            'time_of_day' => $time_of_day,
                        ];
                    }
                }
                ###Добаляем слоты с mos.ru

                sort_by_column($slots, 'time');
                foreach ($slots as $slot) {
                    $answer_rec.='<slot>';
                    $answer_rec.='<time_of_day>'.$slot['time_of_day'].'</time_of_day>';
                    $answer_rec.='<time>'.$slot['time'].'</time>';
                    $answer_rec.='<date>'.$slot['date'].'</date>';
                    $answer_rec.='<status>'.$slot['status'].'</status>';
                    $answer_rec.='</slot>';
                }
                $answer_rec.='</rec_slots>';

                $first_free_slot='';
                ###Ищем первый доступный слот
                foreach ($slots as $slot) {
                    if($slot['status'] == 'FREE'){
                        $first_free_slot=$slot['date'].' '.$slot['time'];
                        break;
                    }
                }
                $answer_rec.='<rec_first_free_slot>'.$first_free_slot.'</rec_first_free_slot>';
                ###Ищем первый доступный слот

                $answer_rec.='<rec_services>';
                $services = explode(",", $row['services']);
                for($i=0; $i<count($services);$i++){
                    $answer_rec.='<service>';
                    $answer_rec.='<id>'.$services[$i].'</id>';
                    $answer_rec.='</service>';
                }
                $answer_rec.='</rec_services>';

                $answer_rec.='</rec>'."\n";

                if(($flag_metro_search == 1 && $flag_metro_found) == 1 || $flag_metro_search == 0){
                    $answer.=$answer_rec;
                }

        }

        pg_free_result($result);
    $answer.='</xml>';

    echo $answer;
}

function show_contacts_xml($config){
    ###
    $id=$_GET["owner"];
    ###

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    if($id){
        #ищем контакты с текущим телефоном

        $q='SELECT contacts.id,contacts.name,contacts.main_flag,contacts.confirmed,contact_types.name AS contact_type_title, contacts.id_contact_type AS contact_type_id FROM contacts ';
        $q.='LEFT JOIN contact_types ON contacts.id_contact_type = contact_types.id ';
        $q.='WHERE contacts.entity_id='.$id.' AND contacts.entity_type=\'pet_owner\'';

        $result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
        while ($row_c = pg_fetch_assoc($result_c)) {
            echo '<rec>';
            echo '<id>'.$row_c['id'].'</id>';
            echo '<name>'.$row_c['name'].'</name>';
            echo '<main>'.$row_c['main_flag'].'</main>';
            echo '<confirmed>'.$row_c['confirmed'].'</confirmed>';
            echo '<type_title>'.$row_c['contact_type_title'].'</type_title>';
            echo '<type_id>'.$row_c['contact_type_id'].'</type_id>';
            echo '</rec>';
        }
        pg_free_result($result_c);
        
    }else{
        echo '<message>id_not_found</message>';
    }
    echo '</xml>';
}

function show_owners_xml($config){
    ###
    $name=$_GET["name"];
    $telephone = $_GET["telephone"];
    ###

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    if($name || $telephone){
        #ищем контакты с текущим телефоном

        $query='SELECT pet_owners.id, pet_owners.fullname, pet_owners.birthday, pet_owners.snils, fias_addresses.full_address, ';
        $query.='string_agg(contacts.id::character varying, \',\') AS contacts, ';
        $query.='string_agg(pets.id::character varying, \',\') AS pets ';
        $query.='FROM pet_owners ';
        $query.='LEFT JOIN fias_addresses ON pet_owners.id_fias_address=fias_addresses.id ';#адрес
        $query.='LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
        $query.='LEFT JOIN pets_to_owner ON pet_owners.id=pets_to_owner.id_owner ';#животные
        $query.='LEFT JOIN pets ON pets_to_owner.id_pet=pets.id ';#животные
        #ПОИСК
        $query.='WHERE ';
        
        $query.='pet_owners.is_deleted=false AND pet_owners.id_main_owner IS NULL ';
        if($name){
            $query.=' AND fullname ILIKE \'%'.string_formating_for_sql($name).'%\'';
        }
        #ПОИСК
        #$query.=' AND pet_owners.is_deleted=false AND pet_owners.is_main=true ';

        if($telephone != ''){
            $query.=' AND (contacts.name LIKE \'+7'.string_formating_for_sql($telephone).'%\'';
            $query.=' OR contacts.name LIKE \'+7 '.string_formating_for_sql($telephone).'%\'';
            $query.=' OR contacts.name=\'+7 '.string_formating_for_sql($telephone).'\'';
            $query.=' OR contacts.name=\'+ 7'.string_formating_for_sql($telephone).'\'';
            $query.=' OR contacts.name = \'+7'.string_formating_for_sql($telephone).'\')';
        }
        $query.='GROUP BY pet_owners.id, fias_addresses.full_address ';
        $query.='ORDER BY pet_owners.fullname,pet_owners.id ASC LIMIT 50';

        // echo $query;
        
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        while ($row = pg_fetch_assoc($result)) {
            echo '<rec>';
            echo '<rec_id>'.$row['id'].'</rec_id>';
            echo '<rec_name>'.$row['fullname'].'</rec_name>';
            echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';
            echo '<rec_snils>'.$row['snils'].'</rec_snils>';
            echo '<rec_address>'.$row['full_address'].'</rec_address>';
            
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

            $pets = ($row['pets'] != '')?explode(",",$row['pets']):NULL;
            if(count($pets) > 0){
                #питомцы
                echo '<rec_pets>';
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
                #echo $q;

                $result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
                while ($row_c = pg_fetch_assoc($result_c)) {
                    echo '<pet>';
                    echo '<id>'.$row_c['id'].'</id>';
                    echo '<name>'.$row_c['name'].'</name>';
                    echo '<sex>'.$row_c['sex'].'</sex>';
                    echo '<breed>'.$row_c['breed_name'].'</breed>';
                    echo '<species>'.$row_c['species_name'].'</species>';
                    echo '</pet>';
                }
                pg_free_result($result_c);
                echo '</rec_pets>';
                #питомцы
            }

            echo '</rec>';
        }
        pg_free_result($result);
        
    }else{
        echo '<message>fields_not_filled</message>';
    }
    echo '</xml>';
}

function show_services_specialists_xml($config){
    if(isset($_POST["service"])){$service=$_POST["service"];}else{$service=$_GET["service"];}
    if(isset($_POST["organization"])){$organization=$_POST["organization"];}else{$organization=$_GET["organization"];}

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $q='SELECT specialists.id AS id,specialists.id_organization AS id_organization, organizations.short_name AS name_organization, users.fullname AS fullname,users.id AS id_user, string_agg(services_specialists.id_service::character varying, \',\') AS services FROM specialists ';
    $q.='LEFT JOIN users ON specialists.id_user=users.id ';
    $q.='LEFT JOIN organizations ON specialists.id_organization=organizations.id ';
    $q.='LEFT JOIN services_specialists ON specialists.id=services_specialists.id_specialist '."\n";
    $q.='WHERE specialists.expel_date IS NULL ';
    // if($service){
    //     $q.=' AND services_specialists.id_service = '.$service.'';
    // }
    if($organization){
        $q.=' AND specialists.id_organization = '.$organization.'';
    }
    $q.=' AND '.get_version_query($config);//Ограничиваем организации
    
    $q.=' GROUP BY specialists.id, organizations.short_name, users.fullname, users.id ';

    if($service){
        $q.=' HAVING ';
        $q.=' (string_agg(services_specialists.id_service::character varying, \',\') LIKE \''.$service.',%\' OR ';
        $q.=' string_agg(services_specialists.id_service::character varying, \',\') LIKE \'%,'.$service.'\' OR ';
        $q.=' string_agg(services_specialists.id_service::character varying, \',\') LIKE \'%,'.$service.',%\') ';
    }

    $q.='ORDER BY users.fullname ';
    #echo $q;
    
    echo '<recs>';
    $result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
    $counter = pg_num_rows($result);
    while ($row = pg_fetch_assoc($result)) {
		echo '<rec>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_fullname>'.$row['fullname'].'</rec_fullname>';
        echo '<rec_name_organization>'.$row['name_organization'].'</rec_name_organization>';

        $servicesArray = explode(",", $row['services']);
        echo '<rec_services>';
        for($i=0; $i<=count($servicesArray);$i++){
            if($servicesArray[$i]){
                echo '<service>';
                echo '<service_id>'.$servicesArray[$i].'</service_id>';
                echo '</service>';
            }
        }
        echo '</rec_services>';
        
        echo '</rec>';
    }
    echo '</recs>';
    echo '<counter>'.$counter.'</counter>';
    pg_free_result($result);


    $query='SELECT * FROM gov_services ';
    $query.='WHERE deleted=false ';
    $query.='ORDER BY name ';
        
    echo '<services>';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<service>';
        echo '<id>'.$row['id'].'</id>';
        echo '<cod>'.$row['cod'].'</cod>';
        echo '<name>'.$row['name'].'</name>';
        echo '<type>'.$row['id_service_type'].'</type>';
        echo '<pricelist>'.$row['id_pricelist'].'</pricelist>';
        echo '</service>';
    }
    pg_free_result($result);
    echo '</services>';

    echo '<types>';
    $query='SELECT * FROM service_types ';
    $query.='ORDER BY name ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<type>';
        echo '<id>'.$row['id'].'</id>';
        echo '<name>'.$row['name'].'</name>';
        echo '</type>';
    }
    pg_free_result($result);
    echo '</types>';

    echo '</xml>';
}

function viewAttention($config){
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10 main_row">';
    echo '<div class="information_ message error" id="message" style="margin-top: 15px; display: block;">Отсутствуют права доступа к разделу.</div>';
    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

?>