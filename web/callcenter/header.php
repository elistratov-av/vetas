<?php
function header_site($size, $config, $title = NULL, $point = NULL, $view = NULL){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

if($size){
    echo '<html xmlns="http://www.w3.org/1999/xhtml" class="w-100 h-100">';
}else{
    echo '<html xmlns="http://www.w3.org/1999/xhtml">';
}

?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php

if($title){
    echo '<title>'.$config['title'].' > '.$title.'</title>';
}else{
    echo '<title>'.$config['title'].'</title>';
}

?>
<script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="/js/popper.min.js"></script>
<script type="text/javascript" src="/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script type="text/javascript" src="/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="/js/bootstrap-datepicker.ru.min.js"></script>
<script type="text/javascript" src="/js/bootstrap-multiselect.min.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script>
window.API_URL = '<?php echo $_ENV['API_URL'];?>'

</script>

<?php
if($point == 'support'){
    echo '<script type="text/javascript" src="/js/support.js"></script>';
    echo '<script type="text/javascript" src="/js/jxchart.js"></script>';
    echo '<script type="text/javascript" src="/js/jxtag.js"></script>';
    echo '<script type="text/javascript" src="/js/jxselect.js"></script>';
}

if($point == 'analytics'):?>
    <script type="text/javascript" src="/js/analytics.js"></script>
    <script type="text/javascript" src="/js/jxchart.js"></script>
    <script type="text/javascript" src="/js/jxtag.js"></script>
    <link rel="stylesheet" type="text/css" href="/images/group.css" />
    <link rel="stylesheet" type="text/css" href="/images/item.css" />
    <link rel="stylesheet" href="<?php echo $_ENV['API_URL'] ?>/css/analytics.css">
<?php endif;

if($point == 'ambulance'){
    $key_map = '4426dec4-5f67-4dde-b708-961eec0a99e8';
    
    echo '<script type="text/javascript" src="/js/ambulance.js"></script>';
    echo '<script type="text/javascript" src="/js/jxtag.js"></script>';
    echo '<script src="https://api-maps.yandex.ru/2.1/?apikey='.$key_map.'&lang=ru_RU" type="text/javascript"></script>';
}

if($point == 'callcenter'){
    echo '<script type="text/javascript" src="/js/callcenter.js"></script>';
    echo '<script type="text/javascript" src="/js/moment.min.js"></script>';
    echo '<script type="text/javascript" src="/js/daterangepicker.min.js"></script>';
    echo '<link rel="stylesheet" type="text/css" href="/images/daterangepicker.min.css" />';
    echo '<script type="text/javascript" src="/js/jxtag.js"></script>';
}

if($point == 'service'){
    echo '<script type="text/javascript" src="/js/service.js"></script>';
}

if($point == 'duplicates'){
    echo '<script type="text/javascript" src="/js/duplicates.js"></script>';
}
if($point == 'shelters'):
    $userId = user_id(null);
    $userRoles = getUserRoles($userId);
    $canEdit = array_reduce($userRoles, function($res, $item) {
        return $res || in_array($item, ['shelterActivityAdmin', 'shelterVeterinarian', 'shelterFaunaMonitoringSpecialist', 'shelterAnimalSocializationSpecialist', 'shelterSysAdmin']);
    }, false);
?>
<script>
    window.canUserEdit = Boolean(<?=$canEdit?>)
</script>
    <script type="text/javascript" src="/js/shelters.js"></script>
    <script type="text/javascript" src="/js/jxtag.js"></script>
<?endif;?>

<link href="/images/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="/images/sumoselect.min.css" rel="stylesheet" type="text/css" />
<link href="/images/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
<link href="/images/bootstrap-multiselect.min.css" rel="stylesheet" type="text/css" />
<?php

if($point == 'analytics'):?>
    <link href="/images/jxchart.css" rel="stylesheet" type="text/css" />
    <link href="/images/jxtag.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo $_ENV['API_URL'] ?>/css/analytics.css">
<?php endif;

if($point == 'ambulance'){
    echo '<link href="/images/jxtag.css" rel="stylesheet" type="text/css" />';
}

if($point == 'callcenter'){
    echo '<link href="/images/jxtag.css" rel="stylesheet" type="text/css" />';
}

if($point == 'shelters'){
    echo '<link href="/images/jxtag.css" rel="stylesheet" type="text/css" />';
}

if($point == 'support'){
    echo '<link href="/images/jxchart.css" rel="stylesheet" type="text/css" />';
    echo '<link href="/images/jxtag.css" rel="stylesheet" type="text/css" />';
    echo '<link href="/images/jxselect.css" rel="stylesheet" type="text/css" />';
}

?>
<link href="/images/main.css" rel="stylesheet" type="text/css" />
<link href="/images/media.css" rel="stylesheet" type="text/css" />

<!-- <link href="https://egip.mos.ru/jsapi/lib/ol-5.2.0.css" rel="stylesheet" type="text/css" />
<link href="https://egip.mos.ru/jsapi/lib/ol-ext-3.0.1.css" rel="stylesheet" type="text/css" /> -->

<link rel="shortcut icon" href="/images/favicon.svg" />
<?php if ($view) $view->head(); ?>
</head>

<?php

if($size){
    echo '<body class="w-100 h-100">';
}else{
    echo '<body>';
}
if ($view) $view->beginBody();
?>

<div id="JxChartTooltip" class="JxChart tooltip_"></div>
<div id="contextmenu" class="contextmenu"></div>
<div class="background" id="background" style="display: none;"></div>
<div class="background" id="background-sch" style="display: none;"></div>
<div class="background_m" id="background_m" style="display: none;"></div>
<div class="background_t" id="background_t" style="display: none;"></div>

<?php

if($point == 'callcenter'){

if(string_formating_for_sql($_COOKIE['organization'])){
?>

<div class="pcontainer" id="container-schedule" style="display: none;">
    <div class="sub_pcontainer" id="sub_container_sch">
        <div class="window main_row col-xl-8 col-lg-10 col-12">
            <div class="close" onclick="closeScheduleWindow();"></div>
            <div class="top">Просмотр расписания врача</div>
            <div class="row main_row control window_title_schedule">
                <div class="col-6" id="window_title">Поиск приёма</div>
                </div>
            <div id="FormSearch" class="search">
                <form id="form_schedule" onsubmit="ListShowSchedule(); return false;">
                    <div class="d-inline-flex"><div class="label">Период с:&nbsp;</div><div class="input"><input type="text" class="date-from" name="date-from" value="" autocomplete="off"></div></div>
                    <div class="d-inline-flex"><div class="label">по:&nbsp;</div><div class="input"><input type="text" class="date-to" name="date-to" value="" autocomplete="off"></div></div>
                    <div class="d-inline-flex"><div class="label">ФИО специалиста:&#42;&nbsp;</div>


<?php
                            echo '<div class="input" id="doc-s">';
                            echo '<select class="doc-s" name="doc" id="doc" required>';
                            echo '<option value="">Выберите специалиста</option>';

                            $spec_organization_id='';

                            if($_COOKIE['organization']){
                                if($_COOKIE['organization'] == 1){
                                    $spec_organization_id=307;
                                }else{
                                    $query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
                                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                    $row = pg_fetch_row($result);
                                    $spec_organization_id=$row[0];
                                    pg_free_result($result);
                                }
                            }

                            $docs_array_final = get_specialists($config,2,0);
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
                        echo '</div>';
?>


                    </div>
                    <div class="d-inline-flex"><button type="submit">Поиск</button></div>
                    </form>
                </div>
            <div class="results" id="ListSchedule"></div>
            </div>
    </div>
</div>

<?php
}
}

?>
<div class="pcontainer" id="container" style="display: none;"><div class="sub_pcontainer" id="sub_container"></div></div>
<div class="pcontainer_m" id="container_m" style="display: none;"><div class="sub_pcontainer_m" id="sub_container_m"></div></div>
<div class="pcontainer_t" id="container_t" style="display: none;"><div class="sub_pcontainer_t" id="sub_container_t"></div></div>
<div class="background" id="background-faq" style="display: none;"></div>
<div class="pcontainer" id="container-faq" style="display: none;"><div class="sub_pcontainer" id="sub_container_faq"></div></div>

<?php
    if($point == 'analytics'){
?>
        <div class="background" id="background-faq-group" style="display: none;"></div>
        <div class="pcontainer" id="container-faq-group" style="display: none;">
            <div class="sub_pcontainer" id="sub_container_faq-group">
                <div class="window main_row col-xl-4 col-lg-5 col-6">
                    <div class="close" onclick="closeFaqGroupWindow();"></div>
                    <div class="top">Новый раздел</div>
                    <form id="form-group" class="px-2 py-3" onsubmit="addGroup(); return false;">
                        <div class="d-flex justify-content-between">
                            <div class="label required">Название раздела:</div>
                            <div class="input w-50"><input type="text" name="group" value="" autoComplete="off" required></div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <div class="label">Добавить вопросы:&nbsp;</div>
                            <div class="input w-50">
<?php
                                // $query='SELECT id, question FROM faq2';
                                // $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                // echo '<select class="questions questions__custom" name="questions[]" id="questions" multiple="multiple">';
                                // while ($row = pg_fetch_assoc($result)) {
                                //     $questionId = $row['id'];
                                //     $questionText = $row['question'];
                                //     echo '<option value="' . $questionId . '">' . $questionText . '</option>';
                                // }
                                // pg_free_result($result);
                                // echo '</select>';
?>
                            </div>
                        </div>
                        <div id="group_exists" class="modal-body__fetch-error" style="display:none">Такой раздел с вопросами уже есть</div>
                        <div class="d-flex justify-content-around mt-4"><button class="btn btn-outline-primary w-50" type="submit">Добавить</button>
                            <input type="button" class="btn btn-outline-secondary" onclick="closeFaqGroupWindow(); event.stopPropagation();" value ="Отменить"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php
    }

    if ($point == 'callcenter')
        echo '<div id="overlay"><div class="cv-spinner"><span class="spinner"></span></div></div>';
}

function menu_site($configuration, $point = NULL, $action = NULL){
    echo '<div class="row main_row header" style="min-height: auto !important;">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
	echo '<div class="col-xl-8 col-lg-10">';
	
    //
    echo '<div class="menu row">';
    echo '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 sections" onclick="showMenu();">';
    echo 'Разделы';
	echo '</div>';
    if($point == 'callcenter' || ($point == 'analytics' && $action == 'faq')){
        echo '<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 title">';
    }else{
	    echo '<div class="col-xl-4 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12 title">';
    }

    if(isset($_POST["id"])){$id=$_POST["id"];}else{$id=$_GET["id"];}
    
    if($point == 'analytics'){
        if($action != 'faq')
            echo 'Аналитика';

        if($action && $action != 'faq'){echo ' / ';}

        if($action == 'visits_detail_report'){
            echo 'Детальный отчёт по приемам';
        }else if($action == 'visits_report'){
            echo 'Общий отчёт по приемам';
        }else if($action == 'full_services_report'){
            echo 'Общий отчёт по услугам';
        }else if($action == 'services_report'){
            echo 'Отчёт об оказании ветеринарных услуг';
        }else if($action == 'services_detail_report'){
            echo 'Детальный отчёт по услугам';
        }else if($action == 'services_price_report'){
            echo 'Отчёт по услугам';

        }else if($action == 'animal_disease_report'){
            echo 'Отчёт по болезням животных';
        }else if($action == 'veterinary_specialists_report'){
            echo 'Отчёт по работе ветеринарных специалистов';

        }else if($action == 'vaccination_report'){
            echo 'Охват вакцинации';
        }else if($action == 'vaccination_shelter_report'){
            echo 'Охват вакцинации в приютах';
        }else if($action == 'notifications_report'){
            echo 'Отчёт по уведомлениям';
        }else if($action == 'faq'){
            echo 'FAQ';
        }else{
            echo ' / Дашборды';
        }
    }
    else if($point == 'support'){
        echo 'Служба поддержки';
        if($action){echo ' / ';}

        if($action == 'users'){echo 'Пользователи';
        }else if($action == 'roles'){echo 'Роли';
        }else if($action == 'elements'){echo 'Элементы';
        }else if($action == 'organizations'){echo 'Организации';
        }else if($action == 'visits'){echo 'Приёмы';
        }else if($action == 'visits_logs'){echo 'Логи приёмов';
        }else if($action == 'pets'){echo 'Животные';
        }else if($action == 'owners'){echo 'Владельцы';
        }else if($action == 'found_pet'){echo 'Сервис "Поиск животных"';
        }else if($action == 'mosru'){echo 'MOS.RU';
        }else if($action == 'statuses_mosru'){echo 'Отправка статусов MOS.RU';
        }else if($action == 'recovery_password_list'){echo 'Восстановление пароля';
        }else if($action == 'analytics'){echo 'Аналитика';
        }else if($action == 'informing'){echo 'Информирование';
        }else if($action == 'faq'){echo 'FAQ';
        }else if($action == 'specializations'){echo 'Специализации';
        }else if($action == 'diseases'){echo 'Заболевания';
        }else if($action == 'breeds'){echo 'Породы';
        }else if($action == 'species'){echo 'Виды';
        } else if ($action == 'question') {
            echo 'Вопросы пользователей';
        }
    }else if($point == 'duplicates'){
        if($action != 'autoduplicate' && $action != 'duplicate_archive'){
            echo 'Реестр дубликатов';
            if($action){echo ' / ';}
        }
        if($action == 'duplicate_archive'){
           echo 'Архив дублей';
        }
        if($action == 'autoduplicate'){
            echo 'Реестр дубликатов автоматического объединения';
        }
        if($action == 'edit'){
            echo 'Сравнение дублей';
        }
    }else if($point == 'ambulance'){
        echo 'Ветеринарная помощь на дому';
        if($action){echo ' / ';}
        if($action == 'brigades'){
            echo 'Список бригад';
        }else if($action == 'requests'){
            echo 'Список заявок';
        }else if($action == 'map'){
            echo 'Карта';
        }else{
            echo ' / Список заявок';
        }
    }else if($point == 'shelters'){
        echo 'Приюты';
        if($action == 'shelters'){
            echo ' / Список животных';
        }else if($action == 'aviaries'){
             echo ' / Список вольеров';
        }else if($action == 'skills'){
            echo ' / Список навыков';
        }else if($action == 'add'){
            echo ' / Добавить животное';
        }else if($action == 'edit'){
            echo ' / Редактировать животное';
        }else if($action == 'wools'){
            echo ' / Список типов шерсти';
        }else{
            echo ' / Список животных';
        }
    }else if($point == 'service'){
        echo 'Редактор услуг';

        if($action){echo ' / ';}

        if($action == 'edit_service'){
            if($id){echo 'Изменение услуги';}else{echo 'Добавление услуги';}
        }else if($action == 'upload_price' || $action == 'price'){
            echo 'Загрузка прайс-листа';
        }else if($action == 'units_list'){
            echo 'Единицы измерения';
        }else if($action == 'services_specialists'){
            echo 'Список связей врачей и услуг';
        }
    }else if($point == 'callcenter'){
        echo 'Контактный центр';
    }
    
    echo '</div>';
    if($point == 'callcenter' || ($point == 'analytics' && $action == 'faq')){
        echo '<div class="col-xl-6 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12 row-right-content row-center-align">';
    }else{
	    echo '<div style="white-space: nowrap;" class="col-xl-5 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 row-right-content row-center-align">';
    }

    echo '<div>';
    if($point == 'ambulance'){
        //Проверка прав на кнопку
        if($action == 'brigades'){
            echo '<button onclick="showAddBrigageWindow();">Создать бригаду</button>';
        }
        if($action == 'requests' || $action == '' ){
            echo '<button onclick="showAddRequestWindow();">Сформировать заявку</button>';
        }
    }

    if($point == 'shelters'){

            if($action == 'edit' && $id){
                echo '<div id="shelters_buttons"></div>';
            }else if($action == 'aviaries'){
                echo '<button onclick="showAviaryWindow();">Создать вольер</button>';
            }else if($action == 'skills'){
                echo '<button onclick="showSkillWindow();">Создать навык</button>';
            }else if($action == 'wools'){
                echo '<button onclick="showWoolWindow();">Создать тип шерсти</button>';
            }else if($action == 'colors'){
                echo '<button onclick="showColorWindow();">Создать окрас</button>';
            }else if($action == 'sizes'){
                echo '<button onclick="showSizeWindow();">Создать размер</button>';
            }else if($action == 'tails'){
                echo '<button onclick="showTailWindow();">Создать тип хвоста</button>';
            }else if($action == 'ears'){
                echo '<button onclick="showEarWindow();">Создать тип ушей</button>';
            }else if($action == 'anamnesiss'){
                echo '<button onclick="showAnamnesisWindow();">Создать шаблон анамнеза</button>';
            }else{
                echo '<div id="shelters_buttons"></div>';
                //echo '<a href="./?action=add"><button>Добавить животное</button></a>';
            }

    }

    if($point == 'callcenter'){
        echo '<button onclick="showScheduleWindow();">Просмотр расписания врача</button>&nbsp;<button onclick="showVisitsWindow();">Список приёмов</button>';
    }

    if($point == 'analytics' && $action == 'faq'){
        echo '<button onclick="showFaqAddGroup();">Добавить раздел</button>';
    }

    if($point == 'service'){
        if(userCan($configuration, 'sysAdminGos')){
            echo '<a href="index.php?action=edit_service"><button style="width: 200px;">Добавить услугу</button></a>';
        }
    }

    if($point == 'support'){
        if(userCan($configuration, 'sysAdminGos')){
            if($action == 'informing'){
                echo '<button onclick="showInformingWindow();">Создать информирование</button>';
            }
            if($action == 'organizations'){
                echo '<button onclick="showAddEditOrganizationWindow();">Создать организацию</button>';
            }
            if($action == 'specializations'){
                echo '<button onclick="showSpecializationWindow();">Создать специализацию</button>';
            }
            if($action == 'diseases'){
                echo '<button onclick="showDiseaseWindow();">Добавить заболевание</button>';
            }
            if($action == 'breeds'){
                echo '<button onclick="showBreedsWindow();">Добавить породу</button>';
            }
            if($action == 'species'){
                echo '<button onclick="showSpeciesWindow();">Добавить вид</button>';
            }
        }
    }

    echo '</div>';

    echo '</div>';
    echo '</div>';
    //

	echo '</div>';
	echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block">';

    echo '</div>';
	echo '</div>';

    echo '<div id="modal_menu" class="modal_menu w-100" style="display: none;">';
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
	echo '<div class="col-xl-8 col-lg-10">';
	
    //
    echo '<div class="row sub_menu">';
    echo '<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12 main_row">';
    
    if($point == 'support'){
        echo '<div class="tab current pointer" onclick="showModalMenuTab(1);" id="tab1_modalmenu_">Разделы</div>';
        echo '<div class="tab pointer" style="margin-left: 10px;" onclick="showModalMenuTab(2);" id="tab2_modalmenu_">Справочники</div>';
    }else if($point == 'shelters'){
        //проверка на возможность просмотра справочника
        if(count(getManagingOrgs(user_org_id($configuration))) == 0){
            echo '<div class="tab current pointer" onclick="showModalMenuTab(1);" id="tab1_modalmenu_">Разделы</div>';
            echo '<div class="tab pointer" style="margin-left: 10px;" onclick="showModalMenuTab(2);" id="tab2_modalmenu_">Справочники</div>';
        }else{
            echo '<div class="tab current pointer" id="tab1_modalmenu_">Разделы</div>';
        }
    }else{
        echo '<div class="tab current">Разделы</div>';
    }

	echo '</div>';

    //tab1
    echo '<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12 row" id="tab1_modalmenu">';
    if($point == 'analytics'){
        echo '<ul class="col-6">';
        echo '<li><a href="index.php">Дашборды</a></li>';
        echo '<li>Отчёты по услугам';
        echo '<ul>';
        echo '<li><a href="index.php?action=full_services_report">Общий отчет по услугам</a></li>';
        echo '<li><a href="index.php?action=services_detail_report">Детальный отчет по услугам</a></li>';
        echo '<li><a href="index.php?action=services_report">Отчёт об оказании ветеринарных услуг</a></li>';
        echo '<li><a href="index.php?action=services_price_report">Отчёт по услугам</a></li>';
        echo '</ul>';
        echo '</li>';

        echo '<li>Отчёты по приёмам';
        echo '<ul>';
        echo '<li><a href="index.php?action=visits_report">Общий отчет по приемам</a></li>';
        echo '<li><a href="index.php?action=visits_detail_report">Детальный отчет по приемам</a></li>';
        echo '</ul>';
        echo '</li>';
        echo '</ul>';

        echo '<ul class="col-6">';
        echo '<li>Отчёты по вакцинациям';
        echo '<ul>';
        echo '<li><a href="index.php?action=vaccination_report">Охват вакцинации</a></li>';
        echo '<li><a href="index.php?action=vaccination_shelter_report">Охват вакцинации в приютах</a></li>';
        echo '</ul>';
        echo '</li>';

        echo '<li>Другие';
        echo '<ul>';
        echo '<li><a href="index.php?action=animal_disease_report">Отчёт по болезням животных</a></li>';
        echo '<li><a href="index.php?action=veterinary_specialists_report">Отчёт по работе ветеринарных специалистов</a></li>';

        echo '<li><a href="index.php?action=notifications_report">Отчёт по уведомлениям</a></li>';
        echo '<li><a href="index.php?action=faq">FAQ</a></li>';
        echo '</ul>';
        echo '</li>';

        echo '</ul>';
    }else if($point == 'support'){
        echo '<ul>';
        echo '<li><a href="index.php" style="display: block; padding-bottom: 15px;">Служба поддержки</a>';
        
        echo '<ul style="display: inline-block;border-right: 1px solid #E39632; margin-right: 30px; padding-right: 30px;">';
        echo '<li><a href="./?action=users">Пользователи</a></li>';
        echo '<li><a href="./?action=roles">Роли</a></li>';
        echo '<li><a href="./?action=elements">Элементы</a></li>';
		echo '<li><a href="./?action=organizations">Организации</a></li>';
		echo '<li><a href="./visit">Приёмы</a></li>';
		echo '<li><a href="./?action=specialists">Специалисты</a></li>';
        echo '<li><a href="./?action=pets">Животные</a></li>';

        echo '</ul>';

        echo '<ul style="display: inline-block; vertical-align: top;border-right: 1px solid #E39632; margin-right: 30px; padding-right: 30px;">';
		echo '<li><a href="./?action=owners">Владельцы</a></li>';
        echo '<li><a href="./?action=found_pet">Сервис "Поиск животных"</a></li>';
        echo '<li><a href="./?action=mosru">MOS.RU</a></li>';
        echo '<li><a href="./?action=statuses_mosru">Отправка статусов MOS.RU</a></li>';
        
        echo '<li><a href="./?action=informing">Информирование</a></li>';
        echo '<li><a href="./question">Вопросы пользователей</a></li>';
        
        echo '</ul>';

        echo '<ul style="display: inline-block; vertical-align: top;">';
        echo '<li><a href="./?action=recovery_password_list"><strong>Запросы на восстановление<br />пароля</strong></a></li>';
        echo '<li><a href="./?action=faq">FAQ</a></li>';
        echo '<li><a href="./?action=analytics">Аналитика</a></li>';
        echo '</ul>';
        
        echo '</li>';
        echo '</ul>';
    }else if($point == 'service'){
        echo '<ul>';
        echo '<li>Редактор услуг';
        echo '<ul>';
        // echo '<li><a href="./?action=edit_service">Добавить услугу</a></li>';
        echo '<li><a href="./">Список услуг</a></li>';
        if(userCan($configuration, 'sysAdminGos')){
            echo '<li><a href="./?action=upload_price">Загрузить прайс-лист</a></li>';
        }
        // echo '<li><a href="./?action=units_list">Единицы измерения</a></li>';
        echo '</ul></li>';
        echo '</ul>';
    }else if($point == 'ambulance'){
        echo '<ul>';
        echo '<li>Ветеринарная помощь на дому';
        echo '<ul>';
        echo '<li><a href="./?action=requests">Заявки</a></li>';
        echo '<li><a href="./?action=brigades">Бригады</a></li>';
        echo '<li><a href="./?action=map">Карта</a></li>';
        echo '</ul></li>';
        echo '</ul>';
    }else if($point == 'callcenter'){
        echo '<ul>';
        echo '<li><a href="./">Контактный центр</a></li>';
        echo '</ul>';
    }else if($point == 'duplicates'){
        echo '<ul>';
        echo '<li><a href="./">Реестр дубликатов</a></li>';
        //echo '<li><a href="./?action=autoduplicate">Реестр дубликатов автоматического объединения</a></li>';
       // echo '<li><a href="./?action=duplicate_archive">Архив дублей</a></li>';
        echo '</ul>';
    }else if($point == 'shelters'){
        echo '<ul>';
        echo '<li><a href="./">Животные приюта</a></li>';
        echo '</ul>';
    }
	echo '</div>';
    //tab1

    //tab2
    echo '<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12 row" id="tab2_modalmenu" style="display: none;">';
    echo '<ul>';

    if($point == 'support'){
        echo '<li><a href="./?action=specializations">Специализации</a></li>';
        echo '<li><a href="./?action=diseases">Заболевания</a></li>';
        echo '<li><a href="./?action=species">Виды</a></li>';
        echo '<li><a href="./?action=breeds">Породы</a></li>';
    }
    if($point == 'shelters'){
        echo '<li><a href="./?action=aviaries">Вольеры</a></li>';
        echo '<li><a href="./?action=skills">Навыки</a></li>';
        echo '<li><a href="./?action=colors">Окрасы</a></li>';
        echo '<li><a href="./?action=sizes">Размеры</a></li>';
        echo '<li><a href="./?action=tails">Типы хвостов</a></li>';
        echo '<li><a href="./?action=ears">Типы ушей</a></li>';
        echo '<li><a href="./?action=wools">Типы шерсти</a></li>';
        echo '<li><a href="./?action=anamnesiss">Шаблоны анамнеза</a></li>';
    }

    //echo '<li>';
    
    //echo '<ul>';
    // style="display: inline-block;border-right: 1px solid #E39632; margin-right: 30px; padding-right: 30px;"
    
    //echo '</ul>';

    // echo '<ul style="display: inline-block; vertical-align: top;">';
    
    // echo '<li><a href="./?action=owners">Владельцы</a></li>';
    // echo '<li><a href="./?action=found_pet">Сервис "Поиск животных"</a></li>';
    // echo '<li><a href="./?action=mosru">MOS.RU</a></li>';
    // echo '<li><a href="./?action=analytics">Аналитика</a></li>';
    // echo '<li><a href="./?action=informing">Информирование</a></li>';
    // echo '<li><a href="./?action=faq">FAQ</a></li>';
    // echo '</ul>';
    
    //echo '</li>';
    echo '</ul>';    
    echo '</div>';
    //tab2

    echo '</div>';
    //

	echo '</div>';
	echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block">';

    echo '</div>';
	echo '</div>';

    echo '</div>';
}

function informings_site($config){
    $query='SELECT informings.*, ';
	$query.='string_agg(organizations.short_name::character varying, \', \') AS organizations ';
	$query.='FROM informings ';
	$query.='LEFT JOIN informings_organizations ON informings.id=informings_organizations.id_informing ';
	$query.='LEFT JOIN organizations ON organizations.id=informings_organizations.id_organization ';

    $query.='WHERE (informings.date  @> (NOW()::TIMESTAMP))';

    $query.=' AND organizations.id='.user_org_id($config).' ';
    
	$query.='GROUP BY informings.id ';
	$query.='ORDER BY informings.id DESC';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $recs_counter = pg_num_rows($result);//количество записей
	

    if($recs_counter > 0){
        echo '<div class="row main_row header" style="min-height: auto !important;">';
        echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
        echo '<div class="col-xl-8 col-lg-10 slider">';
        
        //
        
        echo '<div id="carouselControls" class="carousel slide" data-ride="carousel">';
        

        echo '<div class="row">';
        echo '<div class="col-2 icon"></div>';
        echo '<div class="col-8">';

        echo '<div class="carousel-inner" style="height: 94px;">';
        ////

        $i=0;
        
        while ($row = pg_fetch_assoc($result)) {
            echo '<div class="carousel-item h-100 ';
            if($i == 0){echo 'active';}
            echo '">';
            echo '<div class="carousel-caption h-100 row-justify-content row-center-align">'.$row['text'].'</div>';
            echo '</div>';
            $i++;
        }
        
        ////
        echo '</div>';

        echo '</div>';
        
        if($recs_counter > 1){
            echo '<div class="col-2 row-justify-content row-center-align">';
            echo '<a class="carousel-control-prev" href="#carouselControls" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Previous</span></a>';
            echo '<a class="carousel-control-next" href="#carouselControls" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Next</span></a>';
            echo '</div>';
        }

        echo '</div>';


        echo '</div>';
        //

        echo '</div>';
        echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block">';

        echo '</div>';
        echo '</div>';

        echo '</div>';
    }

    pg_free_result($result);
}

?>
