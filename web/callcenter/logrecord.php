<?php
include_once 'functions.php';

//====================================================================================
function show_log_records_xml($config){
//file_put_contents('c:/log.t2t', print_r($_GET, true) . PHP_EOL, FILE_APPEND);   
//file_put_contents('c:/log.t2t', print_r($_POST, true) . PHP_EOL, FILE_APPEND); 

    $params=get_params();

//file_put_contents('c:/log.t2t', print_r($params, true) . PHP_EOL, FILE_APPEND); 

    if(isset($_POST["visit-id"])){$visitid=$_POST["visit-id"];}else{$visitid=$_GET["visit-id"];}
    if(isset($_POST["description"])){$description=$_POST["description"];}else{$description=$_GET["description"];}
    if(isset($_POST["species"])){$species=$_POST["species"];}else{$species=$_GET["species"];}
    if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
    if($page == ''){$page=1;}

    $recs_on_page=10;//кол-во на странице

    header("Content-type: text/xml; charset=utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query='FROM visits v
    LEFT JOIN organizations o ON o.id=v.id_organization
    LEFT JOIN users u ON u.id=v.created_by ';

    if(count($params["status"]) > 0){
        $query.=' where v.status in (\'';
        $query.=implode("','", $params["status"]);
        $query.='\') ';
    }
    else $query.=' where v.status in (\'N\', \'A\', \'O\', \'T\') ';

    if($visitid!=''){
        $query.=' and v.id='.intval($visitid).' ';
    }
    
    /*$query='SELECT breeds.id, breeds.name, species.name AS species, breeds.description, breeds.species_id ';
    $query.='FROM breeds ';
    $query.='LEFT JOIN species ON breeds.species_id=species.id ';
    $query.='WHERE breeds.name IS NOT NULL ';
    if($name){
        $query.='AND breeds.name ILIKE \'%'.$name.'%\' ';
    }
    if($description){
        $query.='AND breeds.description ILIKE \'%'.$description.'%\' ';
    }
    if($species){
        $query.='AND breeds.species_id='.$species.' ';
    }*/
    //Пагинация
    $result = pg_query('select count(*) count '.$query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    $recs_counter = $row['count'];//количество записей
    pg_free_result($result);

    echo '<from>'.intval(($page-1)*$recs_on_page+1).'</from>';
    echo '<to>'.intval(($page)*$recs_on_page).'</to>';
    echo '<current>'.$page.'</current>';
    echo '<counter>'.$recs_counter.'</counter>';
    
    echo '<pages>';
    if($recs_counter > 0){
        $pages_number = intval($recs_counter / $recs_on_page);
        echo '<last>'.$pages_number.'</last>';
    }
    echo '</pages>';
    //Пагинация

    $query=
        'SELECT
            v.created_at,
            to_char(v.created_at,\'DD.MM.YYYY\') date_at,
	        to_char(v.created_at,\'HH24:MI\') time_at,
            v.id_organization,
            o.short_name,
            (
                select STRING_AGG(gs.name, \', \' ORDER BY gs.name) from gov_services gs
                where gs.id in (select vgs.id_service from visits_gov_services vgs where vgs.id_visit=v.id)
            ) name_service,
            v.id,
            to_char(lower(v.time_range),\'DD.MM.YYYY HH24:MI\') time_range,
            v.status,
            case v.status WHEN \'N\' then \'Запланирован\' when \'A\' then \'Отменен\' when \'O\' then \'Завершен\' else \'К переносу\' END sname,
            v.created_by,
            u.fullname,
            v.comment '
        .$query;

    $query.=' LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
    
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
        echo '<rec_date_at>'.$row['date_at'].'</rec_date_at>';
        echo '<rec_time_at>'.$row['time_at'].'</rec_time_at>';
        echo '<rec_id_organization>'.$row['id_organization'].'</rec_id_organization>';
        echo '<rec_short_name>'.$row['short_name'].'</rec_short_name>';
        echo '<rec_name_service>'.$row['name_service'].'</rec_name_service>';
        echo '<rec_id>'.$row['id'].'</rec_id>';
        echo '<rec_time_range>'.$row['time_range'].'</rec_time_range>';
        echo '<rec_status>'.$row['status'].'</rec_status>';
        echo '<rec_sname>'.$row['sname'].'</rec_sname>';
        echo '<rec_created_by>'.$row['created_by'].'</rec_created_by>';
        echo '<rec_fullname>'.$row['fullname'].'</rec_fullname>';
        echo '<rec_comment>'.$row['comment'].'</rec_comment>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</xml>';
}

//====================================================================================
function show_services_1($config, $type = NULL, $multiple = NULL){
    $params=get_params();

    #не выбрано ни одной услуги, принудительно выбираем 1 и 256
    $flag_forcibly=0;
    if(count($params["service"]) == 0){
        $flag_forcibly=1;
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
}

//====================================================================================
function show_users(){
    $query='select distinct u.fullname, u.id
        from users u join specialists s on u.id = s.id_user
        join auth_assignment a on u.id = a.id_user and s.id = a.id_specialist
        where a.item_name = \'callCenterOperator\'
        and s.expel_date is null
        order by u.fullname';

    echo '<select name="users" id="users" multiple="multiple">';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'">'.$row['fullname'].'</option>';
    }

    echo '</select>';

    pg_free_result($result);
}

//====================================================================================
function viewLogRecord($config){
echo <<<HTML
<div class="row main_row search" style="position:relative !important;">

<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
<div class="col-xl-8 col-lg-10">
    <form id="form" onsubmit="ListLogRecord(); return false;">

    <div class="search_sub" style="color:#748496;">
        <div class="row">
            <div class="d-inline-flex" style="width: 300px !important; padding: 5px 0px 0 15px;">Дата создания записи сотрудником: &nbsp; </div>
            <div class="d-inline-flex label-from">с</div>
            <input type="date" style="width: 150px !important; margin: 0 10px 0 10px;" name="create-d-from" id="create-d-from" value="" autocomplete="off">
            <div class="d-inline-flex label-to">по</div>
            <input type="date" style="width: 150px !important; margin: 0 1px 0 10px;" name="create-d-to" id="create-d-to" value="" autocomplete="off">
            <div class="d-inline-flex unselect" onclick="$('#create-d-from').val(''); $('#create-d-to').val(''); return false;"></div>
        </div>
        <div class="row" style="margin-top:15px;">
            <div class="d-inline-flex" style="width: 300px !important; padding: 5px 0px 0 15px">Время создания записи сотрудником:</div>
            <div class="d-inline-flex label-from">с</div>
            <input type="time" style="width: 100px !important; margin: 0 10px 0 10px" name="create-t-from" id="create-t-from" value="" autocomplete="off">
            <div class="d-inline-flex label-to">по</div>
            <input type="time" style="width: 100px !important; margin: 0 1px 0 10px" name="create-t-to" id="create-t-to" value="" autocomplete="off">
            <div class="unselect" onclick="$('#create-t-from').val(''); $('#create-t-to').val('');return false;"></div>
            <div class="d-inline-flex" style="width: 95px !important; padding: 5px 15px 0 15px">Клиника:</div>
HTML; show_organizations($config,2,1); echo <<<HTML
            <div class="unselect" onclick="$('#organization').val('').multiselect('refresh'); return false;"></div>           
        </div>
        <div class="row" style="margin-top:15px;">
            <div class="d-inline-flex" style="width: 95px !important; padding: 5px 15px 0 15px">Услуга:</div>
HTML; show_services_1($config,1,1); echo <<<HTML
            <div class="unselect" onclick="$('#service').val('').multiselect('refresh'); return false;"></div>
            <div class="d-inline-flex" style="width: 100px !important; padding: 5px 0px 0 15px">ID приема:</div>
            <input type="number" style="background: url('') !important; width: 150px !important; margin: 0 1px 0 10px" name="visit-id" id="visit-id" value="3345207" autocomplete="off">
            <div class="unselect" onclick="$('#visit-id').val(''); return false;"></div>            
            <div class="d-inline-flex" style="width: 100px !important; padding: 5px 15px 0 15px">Оператор:</div>
HTML; show_users(); echo <<<HTML
            <div class="unselect" onclick="$('#users').val('').multiselect('refresh'); return false;"></div>            
        </div>
        <div class="row" style="margin-top:15px;">
            <div class="d-inline-flex" style="width: 180px !important; padding: 5px 0px 0 15px">Дата и время приема:</div>
            <div class="d-inline-flex label-from">с</div>
            <input type="datetime-local" style="width: 200px !important; margin: 0 10px 0 10px" name="visit-from" id="visit-from" value="" autocomplete="off">
            <div class="d-inline-flex label-to">по</div>
            <input type="datetime-local" style="width: 200px !important; margin: 0 1px 0 10px" name="visit-to" id="visit-to" value="" autocomplete="off">
            <div class="unselect" onclick="$('#visit-from').val(''); $('#visit-to').val('');return false;"></div>
            <div class="d-inline-flex" style="width: 145px !important; padding: 5px 15px 0 15px">Статус приема:</div>
            <select id="status" name="status" multiple="multiple">
                <option value="N">Запланирован</option>
                <option value="A">Отменен</option>
                <option value="O">Завершен</option>
                <option value="T">"К переносу</option>
            </select>
            <div class="unselect" onclick="$('#status').val('').multiselect('refresh'); return false;"></div>           
        </div>
        <div class="row" style="margin-top:15px;">
            <div class="d-inline-flex" style="padding: 5px 0px 0 15px">
                <label><input type="checkbox" style="border: 2px solid #ddd !important;" class="styled-checkbox" id="my-visits" name="my-visits">&nbsp&nbsp&nbsp Мои записи</label>
            </div>
        </div>

        <div class="row buttons" style="margin-top:-10px;">
            <button type="reset" onclick="resetLogForm();" style="width: 100px;">Сброс</button>
            <button type="submit" style="margin:0 30px 0 5px; width: 120px;">Поиск</button>
        </div>

        <!--<div class="w-100 info" id="ListInfo"></div>-->

    </div>

    </form>
</div>
<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
</div>

<div class="row main_row">
<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
<div class="col-xl-8 col-lg-10 results">
<!--<div id="ListRecs" class="loading"></div>-->
    <div id="ListRecs" class="message table">Для поиска данных введите параметры поиска</div>
</div>
<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
</div>
HTML;
}
