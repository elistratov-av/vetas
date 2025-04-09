<?php
error_reporting(0);

use Mpdf\Tag\Tr;

$configuration = require $_SERVER['DOCUMENT_ROOT'] . '/callcenter/config.php';

include_once $_SERVER['DOCUMENT_ROOT'] . '/callcenter/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/callcenter/header.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/callcenter/footer.php';

if (!function_exists('pg_connect')) {
    echo xml('<message>Функция pg_connect отсутсвует!</message>');
    exit;
} else {
    $dbconn = pg_connect("host=" . $configuration['host'] . " port=" . $configuration['port'] . " dbname=" . $configuration['dbname'] . " user=" . $configuration['user'] . " password=" . $configuration['password'] . "") or die('Не удалось соединиться: ' . pg_last_error());
    $result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);
}

$userId = user_id(null);
$userRoles = getUserRoles($userId);
$canEdit = array_reduce($userRoles, function($res, $item) {
    return $res || in_array($item, ['shelterActivityAdmin', 'shelterVeterinarian', 'shelterFaunaMonitoringSpecialist', 'shelterAnimalSocializationSpecialist', 'shelterSysAdmin']);
}, false);


if (isset($_POST["action"])) {
    $action = $_POST["action"];
} else {
    $action = $_GET["action"];
}
if (isset($_POST["mode"])) {
    $mode = $_POST["mode"];
} else {
    $mode = $_GET["mode"];
}


function shelters_pet_doc($config_)
{
    $id_pet = intval($_GET["id"]);
    $id_report = $_GET["doc"];

    //error_reporting(0);//отключаем ошибки
    require('library/PhpWord/TemplateProcessor.php');
    require('library/PhpWord/Settings.php');
    require('library/PhpWord/Exception/Exception.php');
    require('library/PhpWord/Exception/CopyFileException.php');
    require('library/PhpWord/Shared/ZipArchive.php');
    require('library/PhpWord/Shared/Text.php');

    //////////////////////////////////////////////////
    //
    $query = 'SELECT ';
    $query .= 'chip.identification_code AS chip_title, ';
    $query .= 'label.identification_code AS label_title, ';
    //
    $query .= 'pet_ref_color.title AS color_title,';
    $query .= 'pet_ref_color.id AS color_id,';
    //
    $query .= 'pet_ref_wool_type.title AS wool_title,';
    $query .= 'pet_ref_wool_type.id AS wool_id,';
    //
    $query .= 'pet_ref_tail_type.title AS tail_title,';
    $query .= 'pet_ref_tail_type.id AS tail_id,';
    //
    $query .= 'pet_ref_size.title AS size_title,';
    $query .= 'pet_ref_size.id AS size_id,';
    //
    $query .= 'pet_ref_ear_type.title AS ear_title,';
    $query .= 'pet_ref_ear_type.id AS ear_id,';
    //
    $query .= 'shelter_guests.socialized AS socialized, ';
    //
    $query .= 'shelter_guests.arrival_reason AS arrival_reason, ';
    $query .= 'shelter_guests.arrival_date AS arrival_date, ';
    $query .= 'shelter_guests.arrival_act_number AS arrival_act_number, ';
    $query .= 'shelter_guests.arrival_act_number_date AS arrival_act_number_date, ';
    //
    $query .= 'shelter_guests.arrival_work_order AS arrival_work_order, ';
    $query .= 'shelter_guests.arrival_work_order_date AS arrival_work_order_date, ';
    //
    $query .= 'shelter_guests.is_quarantine AS is_quarantine, ';
    $query .= 'shelter_guests.quarantine_from AS quarantine_from, ';
    $query .= 'shelter_guests.quarantine_to AS quarantine_to, ';
    //
    $query .= 'shelter_guests.catching_act_number AS catching_act_number, ';
    $query .= 'shelter_guests.catching_act_date AS catching_act_date, ';
    $query .= 'shelter_guests.catching_address AS catching_address, ';
    $query .= 'fias_addresses_catching.full_address AS catching_address_, ';
    $query .= 'shelter_guests.is_catching_video AS is_catching_video, ';
    $query .= 'shelter_guests.catching_video AS catching_video, ';
    //
    $query .= 'shelter_guests.departure_reason AS departure_reason, ';
    $query .= 'shelter_guests.departure_date AS departure_date, ';
    $query .= 'shelter_guests.departure_comment AS departure_comment, ';
    $query .= 'shelter_guests.departure_specialist AS departure_specialist, ';
    //
    $query .= 'max(prv.valid_until) AS vac_date, shelter_guests.*, areas.name AS area, ';
    $query .= 'shelters.short_name AS shelter, fias_addresses.full_address AS shelter_address, ';
    $query .= 'managing.short_name AS managing, pets.id AS pet_id, ';
    $query .= 'shelter_guests.id_organization AS id_org, aviary.title AS aviary_name, ';
    $query .= 'species.name AS species_name, ';
    $query .= 'species.id AS species_id, ';
    $query .= 'breeds.name AS breeds_name, ';
    $query .= 'breeds.id AS breeds_id, ';

    $query .= 'managing.short_name AS managing_name, ';

    $query .= 'pets.birthday AS birthday, ';
    $query .= 'pets.characteristics AS characteristics, ';

    $query .= 'pets.early_castrated AS early_castrated, ';
    $query .= 'pets.castrated AS castrated, ';

    $query .= 'pets.sex AS sex, ';
    $query .= 'pets.character AS character, ';
    $query .= 'pets.name ';

    $query .= 'FROM shelter_guests ';

    // $query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';

    $query .= 'LEFT JOIN fias_addresses AS fias_addresses_catching  ON shelter_guests.catching_address=fias_addresses_catching.id ';#адрес

    $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
    $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
    $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
    $query .= 'LEFT JOIN public.breeds ON breeds.id=pets.id_breed ';

    $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
    $query .= 'LEFT JOIN public.pet_ref_wool_type ON pet_ref_wool_type.id=pets.wool_type_id ';
    $query .= 'LEFT JOIN public.pet_ref_tail_type ON pet_ref_tail_type.id=pets.tail_type_id ';
    $query .= 'LEFT JOIN public.pet_ref_size ON pet_ref_size.id=pets.size_id ';
    $query .= 'LEFT JOIN public.pet_ref_ear_type ON pet_ref_ear_type.id=pets.ear_type_id ';
    $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
    $query .= 'LEFT JOIN public.pet_identification AS label ON label.id_pet=pets.id AND label.id = (SELECT max(l1.id) FROM public.pet_identification l1 WHERE label.id_pet = l1.id_pet AND l1.id_ident_type=5) ';
    $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';

    $query .= 'LEFT JOIN fias_addresses AS fias_addresses ON fias_addresses.id=shelters.id_fias_address ';

    $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
    $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
    $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
    $query .= 'WHERE pets.id=' . string_formating_for_sql($id_pet) . ' ';
    $query .= 'GROUP BY ';
    $query .= 'chip.identification_code, label.identification_code, ';
    $query .= 'fias_addresses_catching.full_address, ';
    $query .= 'pet_ref_color.title, pet_ref_color.id,';
    $query .= 'pet_ref_wool_type.title, pet_ref_wool_type.id,';
    $query .= 'pet_ref_tail_type.title, pet_ref_tail_type.id,';
    $query .= 'pet_ref_size.title, pet_ref_size.id,';
    $query .= 'pet_ref_ear_type.title, pet_ref_ear_type.id,';

    $query .= 'areas.name, shelter_guests.id, shelters.short_name, fias_addresses.full_address, ';

    $query .= 'managing.short_name, ';
    $query .= 'pets.characteristics, pets.character, pets.sex, ';
    $query .= 'pets.id, aviary.title, species.name, breeds.name, species.id, breeds.id ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    pg_free_result($result);
    //////////////////////////////////////////////////

    //////////////////////////////////////////////////
    $socialized = '';
    if ($row['socialized'] == 't') {
        $socialized = '<U>да</U>/нет';
    } else {
        $socialized = 'да/<U>нет</U>';
    }

    $species = '';
    if ($row['species_id'] == 25) {
        $species = '<U>собака</U>/кошка';
    } else {
        $species = 'собака/<U>кошка</U>';
    }

    $ku = '' . $row['id'] . '';
    if ($row['species_name'] == 'кошки') {
        $ku .= 'к';
    } else {
        $ku .= 'с';
    }
    $ku .= '' . $row['id_org'] . '';
    $card_num = 'К/У' . $ku . string_add_spaces($ku, 7);
    $sex = '';
    if ($row['sex'] == 'm') {
        $sex = 'мужской';
    } else {
        $sex = 'женский';
    }
    $temp_date = '«' . Date('d') . '» ' . get_month(Date('n')) . ' ' . Date('Y');
    $age = '';
    $date_year_birthday = '';
    if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
        $date_year_birthday = date("m.Y", strtotime($row['birthday']));
        $birthday_interval = (new DateTime())->diff($birthday);
        $age = '' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.';
    }

    //
    $query_ = 'SELECT pet_health.weight FROM pet_health ';
    $query_ .= 'WHERE id_pet=' . string_formating_for_sql($id_pet) . ' ';
    $query_ .= 'ORDER BY pet_health.date DESC LIMIT 1';
    $result = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
    $row_ = pg_fetch_assoc($result);
    $weight = $row_['weight'];//взять из сведений о здоровье
    pg_free_result($result);
    //

    $socialized = 'Нет';
    if ($row['socialized'] == 't') {
        $socialized = 'Да';
    }
    //////////////////////////////////////////////////
    $ident = '';
    $ident_chip = '';
    if ($row['chip_title'] || $row['label_title']) {
        if ($row['chip_title']) {
            $ident .= 'чип ' . $row['chip_title'];
            $ident_chip = $row['chip_title'];
        }

        if ($row['label_title']) {
            if ($ident) {
                $ident .= ', ';
            }
            $ident .= 'метка ' . $row['label_title'];
        }
    }

    if ($id_report == 'animal_card') {
        $code = 'animal_card';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);


        $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');
        //
        $data = [
            'shelter_name' => empty($row['shelter']) ? '___________________________________________________________________________' : $row['shelter'],
            'shelter_address' => empty($row['shelter_address']) ? '_____________________________________________________' : $row['shelter_address'],

            'aviary_name' => $row['aviary_name'],
            'managing_name' => $row['managing_name'],
            'chief_name' => empty($row['chief_name']) ? string_add_spaces('', 30) : $row['chief_name'],

            'card_num' => $card_num,
            'temp_date' => $temp_date,
            'breed' => mb_strtolower($row['breeds_name']),
            'age' => $age . string_add_spaces($age, 104),
            'color' => mb_strtolower($row['color_title']) . string_add_spaces(mb_strtolower($row['color_title']), 117),
            'wool' => mb_strtolower($row['wool_title']) . string_add_spaces(mb_strtolower($row['wool_title']), 114),
            'ears' => mb_strtolower($row['ear_title']) . string_add_spaces(mb_strtolower($row['ear_title']), 120),
            'tail' => mb_strtolower($row['tail_title']) . string_add_spaces(mb_strtolower($row['tail_title']), 117),
            'size' => mb_strtolower($row['size_title']) . string_add_spaces(mb_strtolower($row['size_title']), 107),
            'name' => $row['name'] . string_add_spaces($row['name'], 93),
            'sex' => $sex . string_add_spaces($sex, 121),
            'weight' => empty($weight) ? '___' : $weight . ' кг',
            'character' => $row['character'],
            'characteristics' => $row['characteristics'] . string_add_spaces($row['characteristics'], 99),
            'socialized' => $socialized,
            'ident' => empty($ident) ? '_______________________________________' : $ident,
        ];
        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }

        if ($row['species_id'] == 25) {
            $document->setImageValue('dog', array('path' => $templateDir . 'field_full.png', 'width' => 15, 'height' => 15, 'ratio' => true));
            $document->setImageValue('cat', array('path' => $templateDir . 'field_empty.png', 'width' => 15, 'height' => 15, 'ratio' => true));
        } else {
            $document->setImageValue('cat', array('path' => $templateDir . 'field_full.png', 'width' => 15, 'height' => 15, 'ratio' => true));
            $document->setImageValue('dog', array('path' => $templateDir . 'field_empty.png', 'width' => 15, 'height' => 15, 'ratio' => true));
        }

        $path = $config_['api_url'];
        $query = 'SELECT files.path FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($id_pet) . ' AND (entity_type=\'shelter\' OR entity_type=\'shelter_main\')';
        $query .= 'ORDER BY entity_type DESC, documents.id ASC LIMIT 1';
        $result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row_ = pg_fetch_assoc($result_);
        $path_file = $row_['path'];
        pg_free_result($result_);

        if ($path_file) {
            //$path должен быть абсолютный путь
            // /var/www/html/web
            $path = $_SERVER['DOCUMENT_ROOT'];
            $document->setImageValue('photo', array('path' => $path . '' . $path_file, 'width' => 250, 'height' => 250, 'ratio' => true));
        } else {
            $document->setValue("photo", '');
        }

        //////ОСНОВНАЯ ТАБЛИЦА
        $main_table_array = array();
        if ($row['arrival_act_number']) {
            $arrival_act_number_date = '' . date_format(new \DateTime($row['arrival_act_number_date']), "d.m.Y") . '';
            array_push($main_table_array, array('title' => 'Акт приема-передачи/приема', 'value' => '№ ' . $row['arrival_act_number'] . ' от ' . $arrival_act_number_date . ' г.'));
        }

        $arrival_reason = '';
        if ($row['arrival_reason'] == 'CATCH') {
            $arrival_reason = 'С места отлова';
        } else if ($row['arrival_reason'] == 'COURT_DECISION') {
            $arrival_reason = 'По решению суда';
        } else if ($row['arrival_reason'] == 'FOUNDLING') {
            $arrival_reason = 'Животное оставлено без надзора';
        } else if ($row['arrival_reason'] == 'OWNER_REFUSAL') {
            $arrival_reason = 'Владелец отказался от права собственности';
        } else {
            $arrival_reason = $row['arrival_reason'];
        }
        array_push($main_table_array, array('title' => 'Животное поступило', 'value' => $arrival_reason));

        if ($arrival_reason == 'С места отлова') {
            if ($row['arrival_work_order']) {
                $arrival_work_order_date = '' . date_format(new \DateTime($row['arrival_work_order_date']), "d.m.Y") . '';
                array_push($main_table_array, array('title' => 'Заказ-наряд', 'value' => '№ ' . $row['arrival_work_order'] . ' от ' . $arrival_work_order_date . ' г.'));
            }
            if ($row['catching_act_number']) {
                $catching_act_date = '' . date_format(new \DateTime($row['catching_act_date']), "d.m.Y") . '';
                array_push($main_table_array, array('title' => 'Акт отлова', 'value' => '№ ' . $row['catching_act_number'] . ' от ' . $catching_act_date . ' г.'));
            }
            if ($row['catching_address_']) {
                array_push($main_table_array, array('title' => 'Адрес места отлова', 'value' => $row['catching_address_']));
            }
            if ($row['is_catching_video'] == 't') {
                // array_push($main_table_array, array('title' => 'Видеофиксация отлова','value' => $row['catching_video']));
                array_push($main_table_array, array('title' => 'Видеофиксация отлова', 'value' => 'да'));
            } else {
                array_push($main_table_array, array('title' => 'Видеофиксация отлова', 'value' => 'нет'));
            }
            if ($row['is_quarantine'] == 't') {
                $quarantine_from = '' . date_format(new \DateTime($row['quarantine_from']), "d.m.Y") . '';
                $quarantine_to = '' . date_format(new \DateTime($row['quarantine_to']), "d.m.Y") . '';
                array_push($main_table_array, array('title' => 'Период проведения карантинных мероприятий', 'value' => 'с ' . $quarantine_from . ' по ' . $quarantine_to . ''));
            }
        }

        //стериализация
        if ($row['early_castrated'] == 't') {
            array_push($main_table_array, array('title' => 'Ранее стерилизован', 'value' => 'Да'));
        } else if ($row['castrated'] == 't') {
            array_push($main_table_array, array('title' => 'Дата стерилизации', 'value' => 'castrated_date'));
            array_push($main_table_array, array('title' => 'Место стерилизации', 'value' => 'castrated_org'));
            array_push($main_table_array, array('title' => 'Ф.И.О. ветеринарного врача', 'value' => 'castrated_specialist'));
        }
        //
        if ($row['arrival_date']) {
            $arrival_date = '' . date_format(new \DateTime($row['arrival_date']), "d.m.Y") . '';
            array_push($main_table_array, array('title' => 'Дата поступления в приют на содержание', 'value' => $arrival_date));
        }

        if ($row['departure_date']) {
            $departure_date = '' . date_format(new \DateTime($row['departure_date']), "d.m.Y") . '';
            array_push($main_table_array, array('title' => 'Дата выбытия из приюта', 'value' => $departure_date));
            if ($row['departure_reason'] == 'RETURNED_TO_NEW_OWNER') {
                array_push($main_table_array, array('title' => 'Причина выбытия из приюта', 'value' => 'Передача новому владельцу'));
            } else if ($row['departure_reason'] == 'DEATH') {
                array_push($main_table_array, array('title' => 'Причина выбытия из приюта', 'value' => 'Смерть'));
            } else if ($row['departure_reason'] == 'EUTHANASIA') {
                array_push($main_table_array, array('title' => 'Причина выбытия из приюта', 'value' => 'Эвтаназия'));
            } else if ($row['departure_reason'] == 'RETURNED_TO_OWNER') {
                array_push($main_table_array, array('title' => 'Причина выбытия из приюта', 'value' => 'Возврат прежнему владельцу'));
            } else {
                array_push($main_table_array, array('title' => 'Причина выбытия из приюта', 'value' => $row['departure_reason']));
            }
        }

        if ($row['departure_reason'] == 'RETURNED_TO_NEW_OWNER') {
            array_push($main_table_array, array('title' => 'Договор о передаче животного в собственность (под опеку) ', 'value' => '№ ___ от ' . $row['departure_date'] . ''));
        }

        if ($row['departure_reason'] == 'DEATH') {
            array_push($main_table_array, array('title' => 'Причина смерти: '));
            array_push($main_table_array, array('title' => 'Акт смерти ', 'value' => '№ ___ от ' . $row['departure_date'] . ''));
        }

        if ($row['departure_reason'] == 'EUTHANASIA') {
            array_push($main_table_array, array('title' => 'Причина эвтаназии:  ', 'value' => $row['departure_comment'] . ''));
        }

        $main_table = '';
        $main_table = '<w:tbl>';
        // $main_table.='<w:tblPr><w:tblStyle w:val="a5"/>';//с границей 1px
        $main_table .= '<w:tblPr><w:tblStyle w:val="a1"/>';
        $main_table .= '<w:tblW w:w="0" w:type="auto"/>';
        $main_table .= '<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr>';

        for ($i = 0; $i < count($main_table_array); $i++) {
            $main_table .= '<w:tr>';
            $main_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
            $main_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $main_table_array[$i]['title'] . '</w:t></w:p></w:tc>';
            $main_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
            $main_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $main_table_array[$i]['value'] . '</w:t></w:p></w:tc>';
            $main_table .= '</w:tr>';
        }
        $main_table .= '</w:tbl>';
        $document->setValue("main_table", $main_table);
        //////ОСНОВНАЯ ТАБЛИЦА

        $document->setValue("ecto_table", shelters_ecto_table($id_pet));
        /////////////
        $document->setValue("vac_table", shelters_vac_rab_table($id_pet));
        // /////////////
        $document->setValue("health_table", shelters_health_table($id_pet));
        /////////////

        if ($row['departure_reason'] == 'RETURNED_TO_NEW_OWNER') {
            $query_ = 'SELECT pet_owners.fullname,pet_owners.entrepreneur as entrepreneur, pet_owners.jur_name as jur_name, string_agg(contacts.id::character varying, \',\') AS contacts, Ad.full_address AS address, Fad.full_address AS factadd FROM pet_owners ';
            $query_ .= 'LEFT JOIN fias_addresses Ad ON Ad.id=pet_owners.id_fias_address ';
            $query_ .= 'LEFT JOIN fias_addresses Fad ON Fad.id=pet_owners.id_fact_fias_address ';
            $query_ .= 'LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
            $query_ .= 'WHERE pet_owners.id=' . $row['id_owner'] . '';
            $query_ .= 'GROUP BY pet_owners.fullname, Ad.full_address, Fad.full_address, entrepreneur, jur_name';
            $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
            $row_ = pg_fetch_assoc($result_);
            $is_entrepreneur = $row_['entrepreneur'];
            $jur_name = $row_['jur_name'];

            $contacts_ = '';
            $contacts = ($row_['contacts'] != '') ? explode(",", $row_['contacts']) : NULL;
            if (count($contacts) > 0) {
                #контакты
                $q = 'SELECT contacts.name,contacts.main_flag,contacts.confirmed,contact_types.name AS contact_type_title, contacts.id_contact_type AS contact_type_id FROM contacts ';
                $q .= 'LEFT JOIN contact_types ON contacts.id_contact_type = contact_types.id ';
                $q .= 'WHERE ';

                for ($i = 0; $i < count($contacts); $i++) {
                    if ($i > 0) {
                        $q .= ' OR ';
                    }
                    $q .= 'contacts.id=' . $contacts[$i] . '';
                }

                $result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
                while ($row_c = pg_fetch_assoc($result_c)) {
                    if ($contacts_) {
                        $contacts_ .= ', ';
                    }
                    $contacts_ .= '' . $row_c['name'] . ' (' . $row_c['contact_type_title'] . ')';
                }
                pg_free_result($result_c);
                #контакты
            }
            // if($row_['address'] != $row_['factadd']){

            // }

            $document->setValue("owners_info", shelters_info_new_owner($row_['fullname'], $row_['factadd'], $contacts_, $is_entrepreneur, $jur_name));

            pg_free_result($result_);
        } else {
            $document->setValue("owners_info", '');
            pg_free_result($result_);
        }

        $filename = 'Карточка учета животного.docx';
        //

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');
    } else if ($id_report == 'anketa') {
        $code = 'anketa';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');

        //
        $data = [
        ];
        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $filename = 'Анкета желающего взять животное из приюта.docx';
        //

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');
    } else if ($id_report == 'return') {
        $code = 'return';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');

        //
        $pet_charcteristics = $row['species_name'] . ', пол ';
        if ($row['sex'] == 'm') {
            $pet_charcteristics .= 'мужской';
        } else {
            $pet_charcteristics .= 'женский';
        }
        $pet_charcteristics .= ', окрас ' . mb_strtolower($row['color_title']);

        //
        $data = [
            'shelter_name' => empty($row['shelter']) ? '___________________________________________________________________________' : $row['shelter'],
            'shelter_address' => empty($row['shelter_address']) ? '_____________________________________________________' : $row['shelter_address'],
            'pet_charcteristics' => $pet_charcteristics,
            'ident' => empty($ident) ? '_____________________________________' : $ident,
            'departure_date' => !empty($row['departure_date'])
                ? '«' . $row['departure_date']->format('d') . '» '
                . $row['departure_date']->format('n') . ' '
                . $row['departure_date']->format('Y')
                : '«___»____________20___ ',
            'legal_type' => empty($legal_type) ? 'Ф.И.О./организация (нужное подчеркнуть)' : $legal_type,
            'fio' => empty($fio) ? '_______________________________________________ __________________________________________________________________' : $fio,
            'address' => empty($address) ? '___________________________________' : $address,
            'phone' => empty($phone) ? '_______________________' : $phone->name
        ];

        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $filename = 'Акт возврата.docx';
        //

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');
    } else if ($id_report == 'transfer') {
        $code = 'transfer';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');

        $uo_name = '';
        $chief_name = '';
        $age = '';
        $ident = '';
        $shelter_address = '';
        $inn = '';
        $kpp = '';
        $ogrn = '';
        $org_phone = '';
        $legal_type = '';
        $fio = '';
        $fact_address = '';
        $address = '';
        $phone = '';

        $data = [
            'shelter_name' => empty($uo_name) ? '' : $uo_name,
            'chief_name' => empty($chief_name)
                ? '_______________ _________________________________________'
                : $chief_name,
            'name' => $row['name'] ? '' : ', кличка ' . $row['name'],
            'sex' => ', пол ' . $sex,
            'age' => empty($age) ? '' : ', возраст ' . $age,
            'breed' => empty($row['breeds_name']) || empty($row['breeds_name']) ? '' : ', порода ' . mb_strtolower($row['breeds_name']),
            'color' => empty($row['color_title']) || empty($row['color_title']) ? '' : ', окрас ' . mb_strtolower($row['color_title']),
            'wool' => empty($row['wool_title']) || empty($row['wool_title']) ? '' : ', шерсть ' . mb_strtolower($row['wool_title']),
            'size' => empty($row['size_title']) || empty($row['size_title']) ? '' : ', размер ' . mb_strtolower($row['size_title']),
            'ident' => $ident,
            'characteristics' => empty($row['characteristics']) ? '' : ', особые приметы ' . $row['characteristics'],
            'character' => empty($row['character']) ? '' : ', характер ' . $row['character'],
            'socialized' => string_replace_underline(htmlspecialchars($socialized)),
            'shelter_address' => empty($shelter_address) ? '' : $shelter_address,
            'ur_shelter_address' => empty($shelter_address) ? '' : $shelter_address,
            'inn' => empty($inn) ? '' : $inn,
            'kpp' => empty($kpp) ? '' : $kpp,
            'ogrn' => empty($ogrn) ? '' : $ogrn,
            'org_phone' => empty($org_phone) ? '' : $org_phone,
            'card_num' => ', № карточки учета  К/У' . $ku,
            'departure_date' => !empty($row['departure_date'])
                ? '«' . $row['departure_date']->format('d') . '» '
                . $row['departure_date']->format('n') . ' '
                . $row['departure_date']->format('Y')
                : '«___»____________20___ ',
            'legal_type' => empty($legal_type) ? 'Ф.И.О./организация (нужное подчеркнуть)' : $legal_type,
            'fio' => empty($fio) ? '__________________________________________________________________' : $fio,
            'address' => empty($address) ? '_____________________________________' : $address,
            'fact_address' => empty($fact_address) ? '_____________________________________________ __________________________________________________________________' : $fact_address,
            'phone' => empty($phone) ? '_______________________' : $phone,
            'species' => string_replace_underline(htmlspecialchars($species))
        ];

        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $filename = 'Договор передачи.docx';
        //

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');
    } else if ($id_report == 'death') {
        $code = 'death';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');
        //
        $ident = '';
        $shelter_name = '';
        $shelter_address = '';
        $chief_name = '';
        $phone = '';

        $data = [
            'species' => string_replace_underline(htmlspecialchars($species)),
            'age' => $age . string_add_spaces($age, 104),
            'ident_code' => $ident . string_add_spaces($ident, 66),
            'color' => mb_strtolower($row['color_title']) . string_add_spaces(mb_strtolower($row['color_title']), 117),
            'wool' => mb_strtolower($row['wool_title']) . string_add_spaces(mb_strtolower($row['wool_title']), 114),
            'ears' => mb_strtolower($row['ear_title']) . string_add_spaces(mb_strtolower($row['ear_title']), 120),
            'tail' => mb_strtolower($row['tail_title']) . string_add_spaces(mb_strtolower($row['tail_title']), 117),
            'size' => mb_strtolower($row['size_title']) . string_add_spaces(mb_strtolower($row['size_title']), 107),
            'card_num' => 'К/У' . $ku . string_add_spaces($ku, 7),
            'name' => $row['name'] . string_add_spaces($row['name'], 93),
            'sex' => $sex . string_add_spaces($sex, 121),
            'characteristics' => $row['characteristics'] . string_add_spaces($row['characteristics'], 99),
            'death_reason' => $death_reason . string_add_spaces('', 84),
            'departure_date' => !empty($row['departure_date'])
                ? '«' . $row['departure_date']->format('d') . '» '
                . $row['departure_date']->format('n') . ' '
                . $row['departure_date']->format('Y')
                : '«___»____________20___ ',
            'temp_date' => $temp_date,
            'shelter_name' => empty($shelter_name)
                ? '_____________________________________________________'
                : $shelter_name,
            'shelter_address' => empty($shelter_address)
                ? '__________________________________________________________________'
                : $shelter_address,
            'chief_name' => empty($chief_name)
                ? '_____________________ __________________________________________________________________'
                : $chief_name,
            'phone' => !empty($phone)
                ? $phone
                : '__________________'
        ];
        //

        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $filename = 'Акт смерти.docx';
        //

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');
    } else if ($id_report == 'volier_animal_card') {
        $code = 'volier_animal_card';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $document->setValue('UPPER', '<w:t xml:space="preserve"> 0</w:t>');
        //
        $data = [
            'shelter_name' => empty($row['shelter']) ? '___________________________________________________________________________' : $row['shelter'],
            'shelter_address' => empty($row['shelter_address']) ? '_____________________________________________________' : $row['shelter_address'],
            'aviary_name' => $row['aviary_name'],
            'managing_name' => $row['managing_name'],
            'chief_name' => empty($row['chief_name']) ? string_add_spaces('', 30) : $row['chief_name'],
            'card_num' => $card_num,
            'temp_date' => $temp_date,
            'breed' => mb_strtolower($row['breeds_name']),
            'age' => $date_year_birthday,
            'color' => mb_strtolower($row['color_title']) . string_add_spaces(mb_strtolower($row['color_title']), 117),
            'wool' => mb_strtolower($row['wool_title']) . string_add_spaces(mb_strtolower($row['wool_title']), 114),
            'ears' => mb_strtolower($row['ear_title']) . string_add_spaces(mb_strtolower($row['ear_title']), 120),
            'tail' => mb_strtolower($row['tail_title']) . string_add_spaces(mb_strtolower($row['tail_title']), 117),
            'size' => mb_strtolower($row['size_title']) . string_add_spaces(mb_strtolower($row['size_title']), 107),
            'name' => $row['name'] . string_add_spaces($row['name'], 93),
            'sex' => $sex . string_add_spaces($sex, 121),
            'weight' => empty($weight) ? '___' : $weight . ' кг',
            'character' => $row['character'],
            'characteristics' => $row['characteristics'] . string_add_spaces($row['characteristics'], 99),
            'socialized' => $socialized,
            'ident' => empty($ident_chip) ? '_______________________________________' : $ident_chip,
        ];
        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }

        $path = $config_['api_url'];
        $query = 'SELECT files.path FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($id_pet) . ' AND (entity_type=\'shelter\' OR entity_type=\'shelter_main\')';
        $query .= 'ORDER BY entity_type DESC, documents.id ASC LIMIT 1';
        $result_ = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row_ = pg_fetch_assoc($result_);
        $path_file = $row_['path'];
        pg_free_result($result_);

        if ($path_file) {
            //$path должен быть абсолютный путь
            // /var/www/html/web
            $path = $_SERVER['DOCUMENT_ROOT'];
            $document->setImageValue('photo', array('path' => $path . '' . $path_file, 'width' => 250, 'height' => 250, 'ratio' => true));
        } else {
            $path = $_SERVER['DOCUMENT_ROOT'];
            $document->setImageValue('photo', array('path' => $path . '/img/photo.png', 'width' => 250, 'height' => 250, 'ratio' => true));
        }

        $filename = 'Карточка учета животного на вольер.docx';
        //

        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessing‌​ml.document");
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');
    }
}

function shelters_info_new_owner($fio, $address, $contacts, $is_entrepreneur, $jur_name)
{
    if (!$address) {
        $address = '-';
    }
    if (!$contacts) {
        $contacts = '-';
    }

    $owners_info = '<w:p w14:paraId="5B550CCD" w14:textId="0639678D" w:rsidR="008A608C" w:rsidRDefault="003476F8" w:rsidP="00B41D41">';
    $owners_info .= '<w:pPr><w:tabs><w:tab w:val="left" w:pos="1535"/></w:tabs><w:spacing w:before="1"/><w:ind w:right="6"/><w:contextualSpacing/><w:jc w:val="center"/><w:rPr>';
    $owners_info .= '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr></w:pPr><w:r w:rsidRPr="007F618B">';
    $owners_info .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>Сведения о новых владельцах</w:t></w:r></w:p>';
    $owners_info .= '<w:p w14:paraId="78E14200" w14:textId="77777777" w:rsidR="00B41D41" w:rsidRDefault="00B41D41" w:rsidP="00B41D41"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1535"/></w:tabs><w:spacing w:before="1"/><w:ind w:right="6"/>';
    $owners_info .= '<w:contextualSpacing/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/>';
    $owners_info .= '<w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr></w:pPr></w:p>';

    $owners_info .= '<w:p w14:paraId="44039EB2" w14:textId="06511BA0" w:rsidR="00B41D41" w:rsidRPr="00B41D41" w:rsidRDefault="00B41D41" w:rsidP="00B41D41"><w:pPr><w:tabs><w:tab w:val="left" w:pos="1535"/>';
    $owners_info .= '</w:tabs><w:spacing w:before="1"/><w:ind w:right="6"/><w:contextualSpacing/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/>';
    $owners_info .= '<w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr></w:pPr><w:r w:rsidRPr="00B41D41"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
    $owners_info .= '<w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t>' . ($is_entrepreneur == 'f' ? 'Физическое лицо (Ф.И.О.):' : 'Юридическое лицо:') . '</w:t></w:r><w:r w:rsidRPr="00B41D41"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
    $owners_info .= '<w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve"></w:t></w:r><w:r w:rsidRPr="00B41D41"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/>';
    $owners_info .= '<w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr><w:t xml:space="preserve"> ' . ($is_entrepreneur == 'f' ? $fio : $jur_name) . '</w:t></w:r></w:p>';

    $owners_info .= '<w:p w14:paraId="521F4628" w14:textId="1ACE70AD" w:rsidR="00B41D41" w:rsidRPr="00B41D41" w:rsidRDefault="00B41D41" w:rsidP="00B41D41"><w:pPr>';
    $owners_info .= '<w:tabs><w:tab w:val="left" w:pos="1535"/></w:tabs><w:spacing w:before="1"/><w:ind w:right="6"/><w:contextualSpacing/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/>';
    $owners_info .= '<w:sz w:val="28"/><w:szCs w:val="28"/><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r w:rsidRPr="00B41D41"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/>';
    $owners_info .= '<w:szCs w:val="28"/></w:rPr><w:t>Ф.И.О. опекунов:</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/>';
    $owners_info .= '<w:lang w:val="en-US"/></w:rPr><w:t xml:space="preserve"> ' . $fio . ' </w:t></w:r></w:p>';

    $owners_info .= '<w:p w14:paraId="4C7DAFC8" w14:textId="38B0FDAC" w:rsidR="00B41D41" w:rsidRPr="00B41D41" w:rsidRDefault="00B41D41" w:rsidP="00B41D41"><w:pPr>';
    $owners_info .= '<w:tabs><w:tab w:val="left" w:pos="1535"/></w:tabs><w:spacing w:before="1"/><w:ind w:right="6"/><w:contextualSpacing/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/>';
    $owners_info .= '<w:sz w:val="28"/><w:szCs w:val="28"/><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r w:rsidRPr="00B41D41"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/>';
    $owners_info .= '<w:szCs w:val="28"/></w:rPr><w:t>адрес:</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/><w:lang w:val="en-US"/>';
    $owners_info .= '</w:rPr><w:t xml:space="preserve"> ' . $address . '</w:t></w:r></w:p>';

    $owners_info .= '<w:p w14:paraId="521F4628" w14:textId="1ACE70AD" w:rsidR="00B41D41" w:rsidRPr="00B41D41" w:rsidRDefault="00B41D41" w:rsidP="00B41D41"><w:pPr>';
    $owners_info .= '<w:tabs><w:tab w:val="left" w:pos="1535"/></w:tabs><w:spacing w:before="1"/><w:ind w:right="6"/><w:contextualSpacing/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/>';
    $owners_info .= '<w:sz w:val="28"/><w:szCs w:val="28"/><w:lang w:val="en-US"/></w:rPr></w:pPr><w:r w:rsidRPr="00B41D41"><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/>';
    $owners_info .= '<w:szCs w:val="28"/></w:rPr><w:t>контактные данные:</w:t></w:r><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/>';
    $owners_info .= '<w:lang w:val="en-US"/></w:rPr><w:t xml:space="preserve"> ' . $contacts . '</w:t></w:r></w:p>';

    return $owners_info;
}

function shelters_ecto_table($id_pet)
{
    /////////////
    $ecto_table = '<w:tbl>';
    $ecto_table .= '<w:tblPr><w:tblStyle w:val="a5"/>';
    $ecto_table .= '<w:tblW w:w="0" w:type="auto"/>';
    $ecto_table .= '<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr>';

    // Header row
    $ecto_table .= '<w:tr>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>№ п/п</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Дата</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Препарат</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Доза</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Подпись ветеринарного врача и печать</w:t></w:p></w:tc>';
    $ecto_table .= '</w:tr>';

    $ecto_array = array();

    $query = 'SELECT pet_ectoparasites.*, users.fullname AS specialist_name, ';
    $query .= 'organizations.short_name AS organization, ';
    $query .= 'organizations.id AS organization_id, ';
    $query .= 'outside_org.name AS organization_out, ';
    $query .= 'outside_org.id AS organization_out_id, ';
    $query .= 'pet_ectoparasites.id_specialist AS specialist_id, specialists.id_organization AS specialist_org ';
    $query .= 'FROM pet_ectoparasites ';

    $query .= 'LEFT JOIN organizations ON pet_ectoparasites.id_organization=organizations.id ';
    $query .= 'LEFT JOIN outside_org ON pet_ectoparasites.id_organization=outside_org.id ';

    $query .= 'LEFT JOIN specialists ON pet_ectoparasites.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id_pet) . ' ';

    $query .= 'ORDER BY pet_ectoparasites.date DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        // echo '<id>' . $row['id'] . '</id>';
        // echo '<drug_name>' . $row['drug_name'] . '</drug_name>';
        // echo '<drug_id>' . $row['id_drug'] . '</drug_id>';
        // echo '<producer_name>' . $row['producer_name'] . '</producer_name>';
        // echo '<dose>' . $row['dose'] . '</dose>';
        // echo '<date_exp>' . $row['date_exp'] . '</date_exp>';
        // echo '<date>' . $row['date'] . '</date>';
        // echo '<valid_until>' . $row['valid_until'] . '</valid_until>';
        // echo '<specialist_id>' . $row['specialist_id'] . '</specialist_id>';
        // echo '<specialist_org>' . $row['specialist_org'] . '</specialist_org>';

        // echo '<organization_name>' . $title_org . '</organization_name>';
        // if ($row['is_out_org'] == 't') {
        //     echo '<protected>1</protected>';
        //     echo '<specialist_name></specialist_name>';
        //     echo '<organization>' . $row['organization_out'] . '</organization>';
        //     echo '<organization_id>' . $row['organization_out_id'] . '</organization_id>';
        // } else {
        //     if ($row['organization_id'] && $row['organization_id'] != $id_org) {
        //         echo '<protected>1</protected>';
        //     } else {
        //         echo '<protected>0</protected>';
        //     }
        //     echo '<specialist_name>' . $row['specialist_name'] . '</specialist_name>';
        //     echo '<organization>' . $row['organization'] . '</organization>';
        //     echo '<organization_id>' . $row['organization_id'] . '</organization_id>';
        $date = '';
        if ($row['date']) {
            $date = '' . date_format(new \DateTime($row['date']), "d.m.Y") . '';
        }

        array_push($ecto_array, array(
            'vaccine' => $row['drug_name'],
            'date' => $date,
            'specialist' => $row['specialist_name'],
            'dose' => $row['dose'],
        ));
    }
    pg_free_result($result);

    for ($k = 0; $k < count($ecto_array); $k++) {
        $ecto_table .= '<w:tr>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . ($k + 1) . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $ecto_array[$k]['date'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $ecto_array[$k]['vaccine'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $ecto_array[$k]['dose'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $ecto_array[$k]['specialist'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '</w:tr>';
    }
    $ecto_table .= '</w:tbl>';

    return $ecto_table;
}

function shelters_vac_rab_table($id_pet)
{
    /////////////
    $ecto_table = '<w:tbl>';
    $ecto_table .= '<w:tblPr><w:tblStyle w:val="a5"/>';
    $ecto_table .= '<w:tblW w:w="0" w:type="auto"/>';
    $ecto_table .= '<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr>';

    // Header row
    $ecto_table .= '<w:tr>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>№ п/п</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Дата</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Вид вакцины</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>№ серии</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Подпись ветеринарного врача и печать</w:t></w:p></w:tc>';
    $ecto_table .= '</w:tr>';

    $vac_rab_array = array();

    $query = 'SELECT pet_rabies_vaccination.*, users.fullname AS specialist_name, ';
    $query .= 'pet_rabies_vaccination.id_specialist AS specialist_id, pet_rabies_vaccination.id_organization AS specialist_org, ';
    $query .= 'organizations.short_name AS organization, ';
    $query .= 'organizations.id AS organization_id, ';
    $query .= 'outside_org.name AS organization_out, ';
    $query .= 'outside_org.id AS organization_out_id ';
    $query .= 'FROM pet_rabies_vaccination ';
    $query .= 'LEFT JOIN specialists ON pet_rabies_vaccination.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'LEFT JOIN organizations ON pet_rabies_vaccination.id_organization=organizations.id ';
    $query .= 'LEFT JOIN outside_org ON pet_rabies_vaccination.id_organization=outside_org.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id_pet) . ' ';
    $query .= 'ORDER BY pet_rabies_vaccination.date DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $date = '';
        if ($row['date']) {
            $date = '' . date_format(new \DateTime($row['date']), "d.m.Y") . '';
        }

        array_push($vac_rab_array, array(
            'vaccine' => $row['drug_name'],
            'date' => $date,
            'specialist' => $row['specialist_name'],
            'batch' => $row['batch'],
        ));
    }
    pg_free_result($result);

    for ($k = 0; $k < count($vac_rab_array); $k++) {
        $ecto_table .= '<w:tr>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . ($k + 1) . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $vac_rab_array[$k]['date'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $vac_rab_array[$k]['vaccine'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $vac_rab_array[$k]['batch'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $vac_rab_array[$k]['specialist'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '</w:tr>';
    }
    $ecto_table .= '</w:tbl>';

    return $ecto_table;
}

function shelters_health_table($id_pet)
{
    $anamnesis_array = array();
    $query = 'SELECT id, title FROM pet_ref_anamnesis ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($anamnesis_array, array('id' => $row['id'], 'title' => $row['title']));
    }
    pg_free_result($result);

    /////////////
    $ecto_table = '<w:tbl>';
    $ecto_table .= '<w:tblPr><w:tblStyle w:val="a5"/>';
    $ecto_table .= '<w:tblW w:w="0" w:type="auto"/>';
    $ecto_table .= '<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr>';

    // Header row
    $ecto_table .= '<w:tr>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>№ п/п</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Дата</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Температура</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Вес</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Анамнез</w:t></w:p></w:tc>';
    $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
    $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>Подпись ветеринарного врача и печать</w:t></w:p></w:tc>';
    $ecto_table .= '</w:tr>';

    $recs_array = array();

    $query = 'SELECT pet_health.*, users.fullname AS specialist_name, pet_health.id_specialist AS specialist_id, specialists.id_organization AS specialist_org FROM pet_health ';
    $query .= 'LEFT JOIN specialists ON pet_health.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id_pet) . ' ';
    $query .= 'ORDER BY pet_health.date DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $date = '';
        if ($row['date']) {
            $date = '' . date_format(new \DateTime($row['date']), "d.m.Y") . '';
        }

        ///
        $anamnesis = '';
        if (strripos($row['anamnesis'], '[ ') >= 0) {
            $anamnesisArray = explode(" ],[ ", $row['anamnesis']);
            for ($i = 0; $i <= count($anamnesisArray); $i++) {
                if ($anamnesisArray[$i] != '') {
                    $id_ = str_replace("[ ", "", $anamnesisArray[$i]);
                    $id_ = str_replace(" ]", "", $id_);

                    if (strripos($id_, '``text``')) {
                        $id_ = str_replace('{``text``: ', "", $id_);
                        $id_ = str_replace("``", "", $id_);
                        $id_ = str_replace("{", "", $id_);
                        $id_ = str_replace("}", "", $id_);
                        if ($anamnesis) {
                            $anamnesis .= ' ';
                        }
                        $anamnesis .= $id_;
                    } else {
                        for ($j = 0; $j <= count($anamnesis_array); $j++) {
                            if ($anamnesis_array[$j]['id'] == $id_) {
                                if ($anamnesis) {
                                    $anamnesis .= ' ';
                                }
                                $anamnesis .= $anamnesis_array[$j]['title'];
                            }
                        }
                    }
                }
            }
        } else {
            if ($anamnesis) {
                $anamnesis .= ' ';
            }
            $anamnesis .= $row['anamnesis'];
        }
        ///

        array_push($recs_array, array(
            'anamnesis' => $anamnesis,
            'date' => $date,
            'specialist' => $row['specialist_name'],
            'weight' => $row['weight'],
            'temperature' => $row['temperature'],
        ));
    }
    pg_free_result($result);

    for ($k = 0; $k < count($recs_array); $k++) {
        $ecto_table .= '<w:tr>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . ($k + 1) . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $recs_array[$k]['date'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $recs_array[$k]['temperature'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $recs_array[$k]['weight'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $recs_array[$k]['anamnesis'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '<w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/></w:rPr>';
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/></w:tcPr><w:p><w:t>' . $recs_array[$k]['specialist'] . '</w:t></w:p></w:tc>';
        $ecto_table .= '</w:tr>';
    }
    $ecto_table .= '</w:tbl>';

    return $ecto_table;
}

function vaccination_pet_table($pets_data)
{
    /////////////
    $ecto_table = '<w:tbl>';
    $ecto_table .= '<w:tblPr><w:tblStyle w:val="a5"/>';
    $ecto_table .= '<w:tblW w:w="0" w:type="auto"/>';
    $ecto_table .= '<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/></w:tblPr>';

    $ecto_table .= '<w:tr>';
    $columns = [
        '№ п/п', 'вид', 'кличка', 'особые приметы', 'порода',
        'пол', 'год рождения', 'окрас', '№ чипа'
    ];
    foreach ($columns as $column) {
        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/><w:tcBorders>
        <w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        <w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        <w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        <w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        </w:tcBorders></w:tcPr>
        <w:p><w:pPr><w:jc w:val="center"/></w:pPr>
        <w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>
        <w:t>' . $column . '</w:t></w:r></w:p></w:tc>';
    }
    $ecto_table .= '</w:tr>';

    $recs_array = $pets_data;


    for ($k = 0; $k < count($recs_array); $k++) {

        $ecto_table .= '<w:tr>';

        $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/><w:tcBorders>
    <w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>
    <w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>
    <w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>
    <w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>
    </w:tcBorders></w:tcPr>
    <w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr>
    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
    <w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>' . ($k + 1) . '</w:t></w:r></w:p></w:tc>';

        foreach (['species_name', 'pet_name', 'description', 'breed_name', 'sex', 'birthday', 'color', 'chip_title'] as $field) {
            if ($field == 'sex') {
                if ($recs_array[$k][$field] == 'f') {
                    $recs_array[$k][$field] = 'женский';
                } else {
                    $recs_array[$k][$field] = 'мужской';
                }
            }

            $ecto_table .= '<w:tc><w:tcPr><w:tcW w:w="4675" w:type="dxa"/><w:tcBorders>
        <w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        <w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        <w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        <w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>
        </w:tcBorders></w:tcPr>
        <w:p><w:pPr><w:jc w:val="center"/><w:wordWrap w:val="on"/></w:pPr><w:r><w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
        <w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>' . $recs_array[$k][$field] . '</w:t></w:r></w:p></w:tc>';
        }

        $ecto_table .= '</w:tr>';
    }

    $ecto_table .= '</w:tbl>';

    $health_table = '';

    if (count($recs_array) > 0) {
        $health_table = $ecto_table;
    }

    return $health_table;
}

/* anamnesiss */
function shelters_anamnesiss_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_anamnesis ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_anamnesis_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';

    $query = 'SELECT pet_ref_anamnesis.* FROM pet_ref_anamnesis ';
    $query .= 'WHERE pet_ref_anamnesis.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_anamnesis_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_anamnesis SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_anamnesis ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>anamnesis_saved</message>');
}

function shelters_delete_anamnesis_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_anamnesis WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>anamnesis_deleted</message>');
}

/* anamnesiss */

/* skills */
function shelters_skills_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_skill ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_skill_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';


    $query = 'SELECT pet_ref_skill.* FROM pet_ref_skill ';
    $query .= 'WHERE pet_ref_skill.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_skill_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_skill SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_skill ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>skill_saved</message>');
}

function shelters_delete_skill_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_skill WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>skill_deleted</message>');
}

/* skills */


/* colors */
function shelters_colors_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_color ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_color_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';


    $query = 'SELECT pet_ref_color.* FROM pet_ref_color ';
    $query .= 'WHERE pet_ref_color.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_color_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_color SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_color ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>color_saved</message>');
}

function shelters_delete_color_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_color WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>color_deleted</message>');
}

/* colors */

/* wools */
function shelters_wools_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_wool_type ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_wool_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';

    $query = 'SELECT pet_ref_wool_type.* FROM pet_ref_wool_type ';
    $query .= 'WHERE pet_ref_wool_type.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_wool_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_wool_type SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_wool_type ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>wool_saved</message>');
}

function shelters_delete_wool_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_wool_type WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>wool_deleted</message>');
}

/* wools */

/* tails */
function shelters_tails_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_tail_type ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_tail_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';

    $query = 'SELECT pet_ref_tail_type.* FROM pet_ref_tail_type ';
    $query .= 'WHERE pet_ref_tail_type.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_tail_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_tail_type SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_tail_type ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>tail_saved</message>');
}

function shelters_delete_tail_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_tail_type WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>tail_deleted</message>');
}

/* tails */

/* ears */
function shelters_ears_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_ear_type ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_ear_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';

    $query = 'SELECT pet_ref_ear_type.* FROM pet_ref_ear_type ';
    $query .= 'WHERE pet_ref_ear_type.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_ear_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_ear_type SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_ear_type ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>ear_saved</message>');
}

function shelters_delete_ear_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_ear_type WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>ear_deleted</message>');
}

/* ears */

/* sizes */
function shelters_sizes_xml($configuration)
{
    $title = $_GET["title"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $query = 'SELECT * FROM pet_ref_size ';
    if ($title) {
        $query .= ' WHERE title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_size_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';

    $query = 'SELECT pet_ref_size.* FROM pet_ref_size ';
    $query .= 'WHERE pet_ref_size.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_size_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE pet_ref_size SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO pet_ref_size ';
        $query .= '(title, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>size_saved</message>');
}

function shelters_delete_size_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.pet_ref_size WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>size_deleted</message>');
}

/* sizes */

function shelters_aviaries_xml($configuration)
{
    $title = $_GET["title"];
    $number = $_GET["number"];
    $description = $_GET["description"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    $org_user_id = user_org_id($configuration);
    $query = 'SELECT * FROM aviary WHERE organization_id=' . $org_user_id . ' ';
    if ($title) {
        $query .= ' AND title ILIKE \'%' . string_formating_for_sql($title) . '%\'';
    }
    if ($number) {
        $query .= ' AND number ILIKE \'%' . string_formating_for_sql($number) . '%\'';
    }
    if ($description) {
        $query .= ' AND description ILIKE \'%' . string_formating_for_sql($description) . '%\'';
    }

    $query .= 'ORDER BY title';

    echo '<recs>';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '<rec_number>' . $row['number'] . '</rec_number>';
        echo '<rec_description>' . $row['description'] . '</rec_description>';
        echo '</rec>';
    }

    echo '</recs>';
    echo '</xml>';

    pg_free_result($result);
}

function shelters_aviary_xml($config)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';


    $query = 'SELECT aviary.* FROM aviary ';
    $query .= 'WHERE aviary.id=' . string_formating_for_sql($id) . ' ';

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec_id>' . $row['id'] . '</rec_id>';
        echo '<rec_title>' . $row['title'] . '</rec_title>';
        echo '<rec_number>' . $row['number'] . '</rec_number>';
        echo '<rec_description>' . $row['description'] . '</rec_description>';
    }
    pg_free_result($result);

    echo '</rec>';
    echo '</xml>';
}

function shelters_save_aviary_xml($configuration)
{
    $id = $_POST["id"];
    $title = $_POST["title"];
    $number = $_POST["number"];
    $description = $_POST["description"];

    $id_user = user_id($configuration);

    if ($id && $id != 'null') {
        $query = 'UPDATE aviary SET ';
        $query .= 'title = \'' . string_formating_for_sql($title) . '\',';
        $query .= 'number = \'' . string_formating_for_sql($number) . '\',';
        $query .= 'description = \'' . string_formating_for_sql($description) . '\',';
        $query .= 'updated_at = NOW()::timestamp(0),';
        $query .= 'updated_by = ' . $id_user . '';
        $query .= 'WHERE id=' . $id . '';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $query = 'INSERT INTO aviary ';
        $query .= '(title, number, description, organization_id, created_at, created_by, updated_at, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($title) . '\',';
        $query .= '\'' . string_formating_for_sql($number) . '\',';
        $query .= '\'' . string_formating_for_sql($description) . '\',';
        $query .= '' . user_org_id($configuration) . ',';
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id = $row[0];
        pg_free_result($result);
    }

    echo xml('<message>aviary_saved</message>');
}

function shelters_delete_aviary_xml($configuration)
{
    $id = $_GET["id"];

    $query_delete = 'DELETE FROM public.aviary WHERE id=' . $id . '';
    $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);

    echo xml('<message>aviary_deleted</message>');
}

function shelters_show_departure_documents_xml($configuration)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';

    if ($id) {
        $query = 'SELECT id FROM shelter_guests WHERE id_pet=' . $id . ' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $sg_id = $row[0];
        pg_free_result($result);

        //FILES
        echo '<files>';
        $query = 'SELECT files.id AS id_file, files.path, documents.name, document_types.type, documents.id, documents.protected_at FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($sg_id) . ' AND entity_type=\'shelter_guests\' ';
        $query .= 'AND (document_types.group=\'RETURNED_TO_NEW_OWNER\' OR document_types.group=\'DEATH\' OR document_types.group=\'EUTHANASIA\' OR document_types.group=\'RETURNED_TO_OWNER\') ';
        $query .= 'ORDER BY documents.date ASC, documents.id ASC';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            echo '<file>';
            echo '<id_file>' . $row['id_file'] . '</id_file>';
            echo '<id>' . $row['id'] . '</id>';
            echo '<path>' . $row['path'] . '</path>';
            echo '<name>' . $row['name'] . '</name>';
            echo '<type>' . $row['type'] . '</type>';
            if ($row['protected_at']) {
                echo '<protected>1</protected>';
            } else {
                echo '<protected>0</protected>';
            }
            echo '</file>';
        }
        pg_free_result($result);
        echo '</files>';
        //FILES
    }

    echo '</xml>';
}

function shelters_show_temp_documents_xml($configuration)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    //FILES
    echo '<files>';
    $query = 'SELECT files.id AS id_file, files.path, documents.name, document_types.type, documents.id, documents.protected_at FROM files ';
    $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
    $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
    $query .= 'WHERE entity_id=' . string_formating_for_sql($id) . ' AND entity_type=\'shelter_guests_temp\'';
    $query .= 'ORDER BY documents.date ASC, documents.id ASC';
    echo $query;
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<file>';
        echo '<id_file>' . $row['id_file'] . '</id_file>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<path>' . $row['path'] . '</path>';
        echo '<name>' . $row['name'] . '</name>';
        echo '<type>' . $row['type'] . '</type>';
        if ($row['protected_at']) {
            echo '<protected>1</protected>';
        } else {
            echo '<protected>0</protected>';
        }
        echo '</file>';
    }
    pg_free_result($result);
    echo '</files>';
    //FILES

    echo '</xml>';
}

function shelters_show_pets_xml($configuration, $info = null)
{
    $page = intval($_GET["page"]);//страница
    if ($page == '') {
        $page = 1;
    }
    $sort = $_GET["sort"];//сортировка
    if ($sort == '') {
        $sort = 'arrival_date';
    }
    $direction = $_GET["direction"];//направление

    $recs_on_page = 50;//кол-во на странице

    $xls = $_GET["xls"];//выгрузка XLS

    require('library/PHPExcel.php');
    $objPHPExcel = new PHPExcel();

    //Стиль XLS
    $font_style = ['font' => ['size' => 16]];
    $border_style = array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000'))));

    $cell_style = array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    );
    //Стиль XLS

    if ($xls) {
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Выгрузка списка животных');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($font_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->getFont()->setBold(true);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Статус");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "№ К/У");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', "№ чипа");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', "Кем вакцинировано животное");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', "Дата вакцинации");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', "Кличка");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2', "Вид");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2', "Дата поступления");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2', "Дата выбытия");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2', "Вольер");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2', "Социализация");
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("A2:K2")->getFont()->setBold(true);

        foreach (range('A', 'K') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
    }

    if (
        $sort != 'area' &&
        $sort != 'managing' &&
        $sort != 'shelter' &&
        $sort != 'status' &&
        $sort != 'ku' &&
        $sort != 'chip' &&
        $sort != 'vac_date' &&
        $sort != 'vac_spec' &&
        $sort != 'name' &&
        $sort != 'species' &&
        $sort != 'arrival_date' &&
        $sort != 'departure_date' &&
        $sort != 'aviary' &&
        $sort != 'socialized'
    ) {
        $sort = 'arrival_date';
    }

    $TempData = '';

    if ($xls) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="your_name.xlsx"');
        header('Cache-Control: max-age=0');
    } else {
        header("Content-type: text/xml; charset=utf-8");
        $TempData .= '<?xml version="1.0" encoding="UTF-8"?>';
        $TempData .= '<xml>';
    }

    $shelter = string_formating_for_sql($_GET["shelter"]);
    $operating = string_formating_for_sql($_GET["operating"]);
    $species = string_formating_for_sql($_GET["species"]);
    $nick = string_formating_for_sql($_GET["nick"]);
    $chip = string_formating_for_sql($_GET["chip"]);
    $castrated = string_formating_for_sql($_GET["castrated"]);
    $vaccination = string_formating_for_sql($_GET["vaccination"]);
    $socialized = string_formating_for_sql($_GET["socialized"]);
    $departure_reason = string_formating_for_sql($_GET["departure_reason"]);
    $vaccine = string_formating_for_sql($_GET["vaccine"]);
    $date_vac_from = string_formating_for_sql($_GET["date_vac_from"]);
    $date_vac_to = string_formating_for_sql($_GET["date_vac_to"]);
    //
    $is_out_org = string_formating_for_sql($_GET["is_out_org"]);
    $organization = string_formating_for_sql($_GET["organization"]);
    $specialist = string_formating_for_sql($_GET["specialist"]);
    //

    $org_user_id = user_org_id($configuration);

    $query = 'WITH ranked_guests AS ( SELECT ';

    $query .= 'max(prv.valid_until) AS vac_date_until, ';
    $query .= 'users.fullname AS vac_spec, ';
    $query .= 'max(prv.date) AS vac_date, ';
    $query .= 'max(prv.id_vaccine) AS vac, ';
    $query .= 'outorgs.name AS vac_org, ';

    $query .= 'prv.id_specialist AS vac_spec_id, ';
    $query .= 'outorgs.id AS vac_org_id, ';

    if ($info) {
        $query .= 'max(ph.date) AS quarantine_date, ';
    }
    $query .= 'shelter_guests.*, ';
    $query .= 'areas.name AS area, shelters.short_name AS shelter, managing.short_name AS managing, ';
    $query .= 'pets.id AS pet_id, ';
    $query .= 'shelter_guests.id_organization AS id_org, ';
    $query .= 'aviary.title AS aviary_title, ';
    $query .= 'pet_identification.identification_code AS chip, ';
    $query .= 'species.name AS species_name, pets.name ';
    $query .= ' , ROW_NUMBER() OVER (PARTITION BY shelter_guests.id_pet ORDER BY shelter_guests.arrival_date DESC) AS rn';
    $query.=' FROM shelter_guests ';

    $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
    $query .= 'LEFT JOIN public.pet_identification ON (pet_identification.id_pet=shelter_guests.id_pet AND pet_identification.id_ident_type=1) ';
    $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
    $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
    $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
    $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
    $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
    if ($info) {
        $query .= 'LEFT JOIN public.pet_health AS ph ON ph.id_pet=pets.id AND ph.status=\'QUARANTINE\' ';
    }
    $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON (prv.id_pet=pets.id AND prv.valid_until=(SELECT max(pet_rabies_vaccination.valid_until) FROM pet_rabies_vaccination WHERE pet_rabies_vaccination.id_pet=pets.id))  ';
    //
    $query .= 'LEFT JOIN public.outside_org AS outorgs ON outorgs.id=prv.id_organization ';
    $query .= 'LEFT JOIN public.specialists AS specialists ON specialists.id=prv.id_specialist ';
    $query .= 'LEFT JOIN public.users AS users ON users.id=specialists.id_user ';
    //

    //Проверяем есть ли у этой организации управляемые
    $m_orgs_array = getManagingOrgs($org_user_id);

    $levels_array = array();
    if (count($m_orgs_array) > 0) {
        $query .= 'WHERE (';
        for ($i = 0; $i <= count($m_orgs_array); $i++) {
            if ($m_orgs_array[$i]) {
                if ($i > 0) {
                    $query .= ' OR ';
                }
                $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                    array_push($levels_array, $m_orgs_array[$i]['level']);
                }
            }
        }
        $query .= ')';
    } else {
        $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
    }

    $query .= ' AND (pets.name IS NOT NULL AND pets.name <> \'\')';
    //Проверяем есть ли у этой организации управляемые

    $params = get_params();

    if (count($params["status"]) > 0) {
        $query_d = '';
        for ($i = 0; $i < count($params["status"]); $i++) {
            if ($params["status"][$i]) {
                if ($i > 0) {
                    $query_d .= ' OR ';
                }
                $query_d .= 'shelter_guests.status = \'' . string_formating_for_sql($params["status"][$i]) . '\'';
            }
        }
        if ($query_d) {
            $query .= ' AND (' . $query_d . ')' . "\n";
        }
    }
    //if($status){$query.="AND shelter_guests.status='".$status."' ";}

    if ($nick) {
        $query .= " AND pets.name ILIKE '%" . $nick . "%' ";
    }
    if ($chip) {
        $query .= " AND public.pet_identification.identification_code = '" . $chip . "' ";
    }
    if ($castrated) {
        $query .= " AND (pets.castrated='" . $castrated . "' OR pets.early_castrated='" . $castrated . "') ";
    }
    if ($socialized) {
        $query .= " AND shelter_guests.socialized='" . $socialized . "' ";
    }

    if ($is_out_org) {
        if ($organization) {
            $query .= " AND outorgs.id='" . $organization . "' ";
        }
    } else {
        if ($specialist) {
            $query .= " AND prv.id_specialist='" . $specialist . "' ";
        }
    }

    if ($species) {
        if ($species == 'OTHER') {
            $query .= "AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
        } else {
            $query .= "AND public.pets.id_species='" . $species . "' ";
        }
    }
    if ($departure_reason) {
        $query .= "AND shelter_guests.departure_reason='" . $departure_reason . "' ";
    }
    if ($shelter) {
        $query .= "AND shelter_guests.id_organization=" . $shelter . " ";
    }
    if ($operating) {
        $query .= "AND shelters.managing_organization_id=" . $operating . " ";
    }

    if (count($params["area"]) > 0) {
        $query_d = '';
        for ($i = 0; $i < count($params["area"]); $i++) {
            if (intval($params["area"][$i])) {
                if ($i > 0) {
                    $query_d .= ' OR ';
                }
                $query_d .= 'areas.id = ' . intval($params["area"][$i]) . '';
            }
        }
        if ($query_d) {
            $query .= ' AND (' . $query_d . ')' . "\n";
        }
    }

    $query .= 'GROUP BY ';

    $query .= 'prv.id_specialist, outorgs.id, ';

    $query .= 'outorgs.name, users.fullname, areas.name, ';
    $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id, aviary.title, species.name, ';
    $query .= 'shelter_guests.id, shelter_guests.socialized, shelter_guests.id_pet, shelter_guests.aviary_id, shelter_guests.id_organization, ';
    $query .= 'shelter_guests.status, shelter_guests.departure_date, shelter_guests.departure_reason, shelter_guests.arrival_date, public.pet_identification.identification_code ';
    //$query.='GROUP BY areas.name, shelter_guests.id, shelters.short_name, managing.short_name, pets.id, aviary.title, species.name ';

    if ($vaccination != "" || $date_vac_from != "" || $date_vac_to != "" || $vaccine != "") {
        $query .= 'HAVING ';
        //1-вакцинировано
        if ($vaccination == "1") {
            $query .= "max(prv.valid_until) > NOW() ";
        }

        //2-не вакцинировано
        if ($vaccination == "2") {
            $query .= "((max(prv.valid_until) IS NULL) OR max(prv.valid_until) < NOW())";
        }

        //3-занчивается вакцинация
        if ($vaccination == "3") {
            $query .= "(max(prv.valid_until) >= NOW() AND max(prv.valid_until) <= NOW()+INTERVAL '30 DAY') ";
        }

        if ($date_vac_from || $date_vac_to) {
            if ($vaccination != "") {
                $query .= " AND ";
            }
            if ($date_vac_from && $date_vac_to) {
                $query .= " (max(prv.date) >= '" . $date_vac_from . "' AND max(prv.date) <= '" . $date_vac_to . "') ";
            } else if ($date_vac_from && !$date_vac_to) {
                $query .= " max(prv.date) >= '" . $date_vac_from . "' ";
            } else if (!$date_vac_from && $date_vac_to) {
                $query .= " max(prv.date) <= '" . $date_vac_to . "' ";
            }
        }

        if ($vaccine) {
            if ($vaccination || $date_vac_from || $date_vac_to) {
                $query .= " AND ";
            }
            $query .= " max(prv.id_vaccine) = '" . $vaccine . "' ";
        }
    }

    $query .= ' ) SELECT * FROM ranked_guests WHERE rn = 1 ';

    if ($info) {


        //хреновое решение, надо написать отдельный запрос
        $quarantine_counter = 0;
        $vaccination_counter = 0;
        $vaccination_end_counter = 0;
        $not_vaccination_counter = 0;

        $date = new \DateTime();
        $today_date = new \DateTime($date->format('Y-m-d') . '+0 day');
        $vac_date_30 = new \DateTime($date->format('Y-m-d') . '+30 day');

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $recs_counter = pg_num_rows($result);//количество записей

        while ($row = pg_fetch_assoc($result)) {
            $vac_date = new \DateTime($row['vac_date_until']);

            if ($row['vac_date_until'] == '' || $vac_date < $today_date) {
                $not_vaccination_counter++;
            } else {
                //
                if ($vac_date >= $today_date && $vac_date <= $vac_date_30) {
                    $vaccination_end_counter++;
                }
            }
        }
        pg_free_result($result);
        $vaccination_counter = $recs_counter - $not_vaccination_counter;

        //сюда же апдейт карантинов и т.д.

        //сюда же апдейт карантинов и т.д.

        $TempData .= '<quarantine>' . $quarantine_counter . '</quarantine>';
        $TempData .= '<vaccination>' . $vaccination_counter . '</vaccination>';
        $TempData .= '<vaccination_end>' . $vaccination_end_counter . '</vaccination_end>';
        $TempData .= '<not_vaccination>' . $not_vaccination_counter . '</not_vaccination>';
    } else {
        //Пагинация
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);
        //

        if (count($m_orgs_array) > 0) {
            $areas_array = array();

            $TempData .= '<shelters>';
            $query_ = 'SELECT id, short_name, id_area FROM organizations ';

            $query_ .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query_ .= ' OR ';
                    }
                    $query_ .= 'id=' . $m_orgs_array[$i]['id'] . '';
                }
            }
            $query_ .= ') AND organization_type_const=\'shelter\'';
            $query_ .= ' ORDER BY short_name ';
            $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
            while ($row = pg_fetch_assoc($result_)) {
                $TempData .= '<shelter>';
                $TempData .= '<id>' . $row['id'] . '</id>';
                $TempData .= '<title>' . $row['short_name'] . '</title>';
                $TempData .= '</shelter>';

                array_push($areas_array, $row['id_area']);
            }
            pg_free_result($result_);
            $TempData .= '</shelters>';

            //

            $TempData .= '<areas>';
            if (count($areas_array) > 0) {
                $query_ = 'SELECT id, name FROM areas ';

                $query_ .= 'WHERE (';
                for ($i = 0; $i <= count($areas_array); $i++) {
                    if ($areas_array[$i]) {
                        if ($i > 0) {
                            $query_ .= ' OR ';
                        }
                        $query_ .= 'id=' . $areas_array[$i] . '';
                    }
                }
                $query_ .= ') ORDER BY name ';

                $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
                while ($row = pg_fetch_assoc($result_)) {
                    $TempData .= '<area>';
                    $TempData .= '<id>' . $row['id'] . '</id>';
                    $TempData .= '<title>' . $row['name'] . '</title>';
                    $TempData .= '</area>';
                }
                pg_free_result($result_);
            }
            $TempData .= '</areas>';
        }

        $TempData .= '<levels>' . count($levels_array) . '</levels>';

        $TempData .= '<from>' . intval(($page - 1) * $recs_on_page + 1) . '</from>';
        $TempData .= '<to>' . intval(($page) * $recs_on_page) . '</to>';

        $TempData .= '<current>' . $page . '</current>';
        $TempData .= '<sort>' . $sort . '</sort>';
        $TempData .= '<direction>' . $direction . '</direction>';

        $TempData .= '<counter>' . $recs_counter . '</counter>';

        //
        // $TempData.='<quarantine>'.$recs_counter.'</quarantine>';
        // $TempData.='<vaccination>'.$recs_counter.'</vaccination>';
        // $TempData.='<not_vaccination>'.$recs_counter.'</not_vaccination>';
        //

        $TempData .= '<pages>';
        if ($recs_counter > 0) {
            $pages_number = intval($recs_counter / $recs_on_page);
            if ($pages_number > 1) {
                for ($i = 1; $i < $pages_number + 1; $i++) {
                    $TempData .= '<page>' . $i . '</page>';
                }
            }
            $TempData .= '<last>' . $pages_number . '</last>';
        }
        $TempData .= '</pages>';
        //Пагинация

        //сортировка по умолчанию
        $query .= 'ORDER BY ';//.$sort.' '
        if ($sort == 'area') {
            $query .= 'area ';
        } else if ($sort == 'managing') {
            $query .= 'managing ';
        } else if ($sort == 'shelter') {
            $query .= 'shelter ';
        } else if ($sort == 'status') {
            $query .= 'status ';
        } else if ($sort == 'ku') {
            $query .= 'id ';
        } else if ($sort == 'name') {
            $query .= ' name ';
        } else if ($sort == 'chip') {
            $query .= 'chip ';
        } else if ($sort == 'vac_date') {
            $query .= 'vac_date_until ';
        } else if ($sort == 'vac_spec') {
            $query .= 'vac_spec ';
            if ($direction) {
                $query .= 'ASC NULLS LAST ';
            } else {
                $query .= 'DESC NULLS LAST ';
            }
            $query .= ', vac_org ';
        } else if ($sort == 'species') {
            $query .= 'species_name ';
        } else if ($sort == 'arrival_date') {
            $query .= 'arrival_date ';
        } else if ($sort == 'departure_date') {
            $query .= 'departure_date ';
        } else if ($sort == 'aviary') {
            $query .= 'aviary_title ';
        } else if ($sort == 'socialized') {
            $query .= 'socialized ';
        } else {
            $query .= 'shelter_guests.id ';
        }


        if ($direction) {
            $query .= 'ASC NULLS LAST ';
        } else {
            $query .= 'DESC NULLS LAST ';
        }
        //сортировка по умолчанию

        $count = 0;

        $TempData .= '<recs>';

        //
        if (!$xls) {
            $query .= 'LIMIT ' . $recs_on_page . ' OFFSET ' . intval(($page - 1) * $recs_on_page) . '';
        }
        //
        //echo $query;

        $Data = '';
        $n = 3;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            //КУ
            $ku = $row['id'];
            if ($row['species_name'] == 'кошки') {
                $ku .= 'к';
            } else {
                $ku .= 'с';
            }
            $ku .= '' . $row['id_org'] . '';
            //КУ

            //
            $arrival_date = '';
            if ($row['arrival_date']) {
                $arrival_date = '' . date_format(new \DateTime($row['arrival_date']), "d.m.Y") . '';
            }
            $departure_date = '';
            if ($row['departure_date']) {
                $departure_date = '' . date_format(new \DateTime($row['departure_date']), "d.m.Y") . '';
            }
            $vac_date = '';
            if ($row['vac_date']) {
                $vac_date = '' . date_format(new \DateTime($row['vac_date']), "d.m.Y") . '';
            }

            $vac_date_until = '';
            if ($row['vac_date_until']) {
                $vac_date_until = '' . date_format(new \DateTime($row['vac_date_until']), "d.m.Y") . '';
            }
            //

            if (!$xls) {
                $Data .= '<rec>';
                $Data .= '<rec_id>' . $row['pet_id'] . '</rec_id>';

                $Data .= '<rec_area>' . $row['area'] . '</rec_area>';
                $Data .= '<rec_managing>' . $row['managing'] . '</rec_managing>';
                $Data .= '<rec_shelter>' . $row['shelter'] . '</rec_shelter>';

                $Data .= '<rec_chip>' . $row['chip'] . '</rec_chip>';
                $Data .= '<rec_vac_spec>' . $row['vac_spec'] . '</rec_vac_spec>';
                $Data .= '<rec_vac_date>' . $vac_date . '</rec_vac_date>';
                $Data .= '<rec_vac_date_until>' . $vac_date_until . '</rec_vac_date_until>';
                $Data .= '<rec_vac_org>' . $row['vac_org'] . '</rec_vac_org>';

                $Data .= '<rec_ku>' . $ku . '</rec_ku>';
                $Data .= '<rec_status>' . $row['status'] . '</rec_status>';
                $Data .= '<rec_name>' . $row['name'] . '</rec_name>';
                $Data .= '<rec_aviary>' . $row['aviary_title'] . '</rec_aviary>';
                $Data .= '<rec_species>' . $row['species_name'] . '</rec_species>';

                $Data .= '<rec_arrival_date>' . $arrival_date . '</rec_arrival_date>';
                $Data .= '<rec_departure_date>' . $departure_date . '</rec_departure_date>';

                $Data .= '<rec_socialized>' . $row['socialized'] . '</rec_socialized>';

                $Data .= '</rec>';
            }
            if ($xls) {
                $status = '';
                if ($row['status'] == 'IN_SHELTER') {
                    $status = 'В приюте';
                } else if ($row['status'] == 'QUARANTINE') {
                    $status = 'Карантин';
                } else if ($row['status'] == 'QUARANTINE_OTHER') {
                    $status = 'Карантин (продлён)';
                } else if ($row['status'] == 'DEPARTURED') {
                    $status = 'Выбыло';
                } else if ($row['status'] == 'IN_ISOLATION') {
                    $status = 'В изоляторе';
                } else if ($row['status'] == 'IN_HOSPITAL') {
                    $status = 'В стационаре';
                } else {
                    $status = $row['status'];
                }

                $vac_label = '';
                if ($row['vac_spec']) {
                    $vac_label = $row['vac_spec'];
                } else {
                    $vac_label = $row['vac_org'];
                }
                $soc_label = '';
                if ($row['socialized'] == 't') {
                    $soc_label = 'Да';
                } else {
                    $soc_label = 'Нет';
                }

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, $status)
                    ->setCellValue('B' . $n, $ku)
                    ->setCellValue('C' . $n, $row['chip'])
                    ->setCellValue('D' . $n, $vac_label)
                    ->setCellValue('E' . $n, $vac_date)
                    ->setCellValue('F' . $n, $row['name'])
                    ->setCellValue('G' . $n, $row['species_name'])
                    ->setCellValue('H' . $n, $arrival_date)
                    ->setCellValue('I' . $n, $departure_date)
                    ->setCellValue('J' . $n, $row['aviary_title'])
                    ->setCellValue('K' . $n, $soc_label);
                //чип записываем в виде txt - без сокращения до +Е
                $objPHPExcel->getActiveSheet()->getStyle('C' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $n, $row['chip'], \PHPExcel_Cell_DataType::TYPE_STRING);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);

                $objPHPExcel->getActiveSheet()->getRowDimension($n)->setRowHeight(50);
            }

            $n++;
        }
        pg_free_result($result);

        $TempData .= $Data;
        $TempData .= '</recs>';
    }
    $TempData .= '</xml>';

    if (!$xls) {
        echo $TempData;
    } else {
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}

function shelters_show_vac_pets_xml($configuration)
{
    $TempData = '';

    header("Content-type: text/xml; charset=utf-8");
    $TempData .= '<?xml version="1.0" encoding="UTF-8"?>';
    $TempData .= '<xml>';

    $pets = string_formating_for_sql($_GET["pets"]);

    $org_user_id = user_org_id($configuration);
    $TempData .= '<organization>' . user_org_title($configuration) . '</organization>';

    $query = 'SELECT ';
    $query .= 'max(prv.valid_until) AS vac_date_until, ';
    $query .= 'users.fullname AS vac_spec, ';
    $query .= 'max(prv.date) AS vac_date, ';
    $query .= 'max(prv.id_vaccine) AS vac, ';
    $query .= 'outorgs.name AS vac_org, ';
    $query .= 'shelter_guests.*, ';
    $query .= 'areas.name AS area, shelters.short_name AS shelter, managing.short_name AS managing, ';
    $query .= 'pets.id AS pet_id, ';
    $query .= 'shelter_guests.id_organization AS id_org, ';
    $query .= 'aviary.title AS aviary_title, ';
    $query .= 'pet_identification.identification_code AS chip, ';
    $query .= 'species.name AS species_name, pets.name ';

    $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, shelter_guests.departure_reason, shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization, shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date, shelter_guests.id FROM shelter_guests) AS shelter_guests ';

    $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
    $query .= 'LEFT JOIN public.pet_identification ON (pet_identification.id_pet=shelter_guests.id_pet AND pet_identification.id_ident_type=1) ';
    $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
    $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
    $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
    $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
    $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
    $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON (prv.id_pet=pets.id AND prv.valid_until=(SELECT max(pet_rabies_vaccination.valid_until) FROM pet_rabies_vaccination WHERE pet_rabies_vaccination.id_pet=pets.id))  ';
    //
    $query .= 'LEFT JOIN public.outside_org AS outorgs ON outorgs.id=prv.id_organization ';
    $query .= 'LEFT JOIN public.specialists AS specialists ON specialists.id=prv.id_specialist ';
    $query .= 'LEFT JOIN public.users AS users ON users.id=specialists.id_user ';
    //


    $query .= "WHERE ";
    $query_ = "";
    $petsArray = explode(";", $pets);
    for ($i = 0; $i <= count($petsArray); $i++) {
        if ($petsArray[$i]) {
            if ($query_) {
                $query_ .= " OR ";
            }
            $query_ .= "pets.id=" . $petsArray[$i] . "";
        }
    }
    $query .= $query_;
    $query .= 'GROUP BY ';
    $query .= 'outorgs.name, users.fullname, areas.name, ';
    $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id, aviary.title, species.name, ';
    $query .= 'shelter_guests.id, shelter_guests.socialized, shelter_guests.id_pet, shelter_guests.aviary_id, shelter_guests.id_organization, ';
    $query .= 'shelter_guests.status, shelter_guests.departure_date, shelter_guests.departure_reason, shelter_guests.arrival_date, public.pet_identification.identification_code ';

    #echo '<br>'.$query.'<br>';


    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $recs_counter = pg_num_rows($result);//количество записей
    pg_free_result($result);

    $TempData .= '<recs>';

    $Data = '';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        //КУ
        $ku = $row['id'];
        if ($row['species_name'] == 'кошки') {
            $ku .= 'к';
        } else {
            $ku .= 'с';
        }
        $ku .= '' . $row['id_org'] . '';
        //КУ

        //
        $arrival_date = '';
        if ($row['arrival_date']) {
            $arrival_date = '' . date_format(new \DateTime($row['arrival_date']), "d.m.Y") . '';
        }
        $departure_date = '';
        if ($row['departure_date']) {
            $departure_date = '' . date_format(new \DateTime($row['departure_date']), "d.m.Y") . '';
        }
        $vac_date = '';
        if ($row['vac_date']) {
            $vac_date = '' . date_format(new \DateTime($row['vac_date']), "d.m.Y") . '';
        }

        $vac_date_until = '';
        if ($row['vac_date_until']) {
            $vac_date_until = '' . date_format(new \DateTime($row['vac_date_until']), "d.m.Y") . '';
        }
        //

        $Data .= '<rec>';
        $Data .= '<rec_id>' . $row['pet_id'] . '</rec_id>';
        $Data .= '<rec_area>' . $row['area'] . '</rec_area>';
        $Data .= '<rec_managing>' . $row['managing'] . '</rec_managing>';
        $Data .= '<rec_shelter>' . $row['shelter'] . '</rec_shelter>';
        $Data .= '<rec_chip>' . $row['chip'] . '</rec_chip>';
        $Data .= '<rec_vac_spec>' . $row['vac_spec'] . '</rec_vac_spec>';
        $Data .= '<rec_vac_date>' . $vac_date . '</rec_vac_date>';
        $Data .= '<rec_vac_date_until>' . $vac_date_until . '</rec_vac_date_until>';
        $Data .= '<rec_vac_org>' . $row['vac_org'] . '</rec_vac_org>';

        $Data .= '<rec_ku>' . $ku . '</rec_ku>';
        $Data .= '<rec_status>' . $row['status'] . '</rec_status>';
        $Data .= '<rec_name>' . $row['name'] . '</rec_name>';
        $Data .= '<rec_aviary>' . $row['aviary_title'] . '</rec_aviary>';
        $Data .= '<rec_species>' . $row['species_name'] . '</rec_species>';

        $Data .= '<rec_arrival_date>' . $arrival_date . '</rec_arrival_date>';
        $Data .= '<rec_departure_date>' . $departure_date . '</rec_departure_date>';

        $Data .= '<rec_socialized>' . $row['socialized'] . '</rec_socialized>';

        $Data .= '</rec>';
    }
    pg_free_result($result);

    $TempData .= $Data;
    $TempData .= '</recs>';


    //VACCINES
    $TempData .= '<vaccines>';
    $query = 'SELECT tmc.tmc.id, tmc.tmc.name, tmc.tmc.produced FROM tmc.tmc ';
    $query .= 'WHERE type=\'vaccine\' AND is_deleted=\'false\'';
    $query .= 'ORDER BY tmc.tmc.name ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $TempData .= '<rec>';
        $TempData .= '<id>' . $row['id'] . '</id>';
        $TempData .= '<name>' . string_formating_for_xml($row['name']) . '</name>';
        $TempData .= '<producer>' . string_formating_for_xml($row['produced']) . '</producer>';
        $TempData .= '</rec>' . "\n";
    }
    pg_free_result($result);
    $TempData .= '</vaccines>';
    //VACCINES

    //organizations
    $TempData .= '<organizations>';
    $query = 'SELECT id, name FROM outside_org ORDER BY name ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $TempData .= '<rec>';
        $TempData .= '<id>' . $row['id'] . '</id>';
        $TempData .= '<name>' . string_formating_for_xml($row['name']) . '</name>';
        $TempData .= '</rec>' . "\n";
    }
    pg_free_result($result);
    $TempData .= '</organizations>';
    //organizations

    //SPECIALISTS
    $TempData .= '<specialists>';
    $query = 'SELECT specialists.id AS specialist_id, users.fullname AS specialist_name FROM specialists ';
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE specialists.id_organization=' . string_formating_for_sql($org_user_id) . ' ';
    $query .= 'ORDER BY users.fullname ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        $TempData .= '<rec>';
        $TempData .= '<id>' . $row['specialist_id'] . '</id>';
        $TempData .= '<name>' . string_formating_for_xml($row['specialist_name']) . '</name>';
        $TempData .= '</rec>';
    }
    pg_free_result($result);
    $TempData .= '</specialists>';
    //SPECIALISTS


    $TempData .= '</xml>';

    echo $TempData;
}

function shelters_save_vac_pets_xml($configuration)
{
    $pets = string_formating_for_sql($_POST["pets"]);

    $date = string_formating_for_sql($_POST["date"]);
    $drug = string_formating_for_sql($_POST["drug"]);
    $organization = string_formating_for_sql($_POST["organization"]);
    $specialist = string_formating_for_sql($_POST["specialist"]);
    $batch = string_formating_for_sql($_POST["batch"]);
    $expiry_date = string_formating_for_sql($_POST["expiry_date"]);
    $valid_until = string_formating_for_sql($_POST["valid_until"]);
    $is_out_org = string_formating_for_sql($_POST["is_out_org"]);

    $id_user = user_id($configuration);
    $id_org = user_org_id($configuration);

    $petsArray = explode(";", $pets);
    for ($i = 0; $i <= count($petsArray); $i++) {
        if ($petsArray[$i]) {
            //drug_name,producer_name считываем из БД название
            $type_tmc = '';
            $drug_name = '';
            $producer_name = '';
            if ($drug) {
                $query = 'SELECT type, name, produced FROM tmc.tmc WHERE id=\'' . string_formating_for_sql($drug) . '\' LIMIT 1';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $type_tmc = $row[0];
                $drug_name = $row[1];
                $producer_name = $row[2];
                pg_free_result($result);
            }
            ///

            $query = 'INSERT INTO pet_rabies_vaccination ';
            $query .= '(id_pet, date, id_organization, id_vaccine, drug_name, producer_name, type_tmc, batch, id_specialist, expiry_date, valid_until, is_out_org, created_at, created_by, updated_at, updated_by) ';
            $query .= 'VALUES (';
            $query .= "" . $petsArray[$i] . ",";
            $query .= "'" . $date . "',";
            $query .= "" . $id_org . ",";

            if ($drug) {
                $query .= "" . string_formating_for_sql($drug) . ",";
                $query .= "'" . $drug_name . "',";
                $query .= "'" . $producer_name . "',";
                $query .= "'" . $type_tmc . "',";
            } else {
                $query .= "NULL,";
                $query .= "NULL,";
                $query .= "NULL,";
                $query .= "NULL,";
            }
            $query .= "'" . string_formating_for_sql($batch) . "',";

            if ($specialist) {
                $query .= "" . string_formating_for_sql($specialist) . ",";
            } else {
                $query .= "NULL,";
            }
            if ($expiry_date) {
                $query .= "'" . $expiry_date . "',";
            } else {
                $query .= "NULL,";
            }
            if ($valid_until) {
                $query .= "'" . $valid_until . "',";
            } else {
                $query .= "NULL,";
            }
            if (string_formating_for_sql($is_out_org) == 1) {
                $query .= "true,";
            } else {
                $query .= "false,";
            }

            $query .= "NOW()::timestamp(0),";#created_at
            $query .= "" . $id_user . ",";#created_by
            $query .= "NOW()::timestamp(0),";#updated_at
            $query .= "" . $id_user . "";#updated_by
            $query .= ') RETURNING id;';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_rec = $row[0];
            pg_free_result($result);
        }
    }

    $message = '<message>vac_pets_saved</message>';
    echo xml($message);
}


/**
 * @throws PHPExcel_Exception
 * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
 * @throws PHPExcel_Reader_Exception
 * @throws \PhpOffice\PhpWord\Exception\CopyFileException
 * @throws PHPExcel_Writer_Exception
 */

/**
 * @throws PHPExcel_Exception
 * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
 * @throws PHPExcel_Reader_Exception
 * @throws \PhpOffice\PhpWord\Exception\CopyFileException
 * @throws PHPExcel_Writer_Exception
 */
function shelters_report($configuration)
{
    $report = $_GET["report"];

    //error_reporting(0);//отключаем ошибки
    require('library/PHPExcel.php');
    require('library/PhpWord/TemplateProcessor.php');
    require('library/PhpWord/Settings.php');
    require('library/PhpWord/Exception/Exception.php');
    require('library/PhpWord/Exception/CopyFileException.php');
    require('library/PhpWord/Shared/ZipArchive.php');
    require('library/PhpWord/Shared/Text.php');

    $objPHPExcel = new PHPExcel();

    //Стиль XLS
    $font_style = ['font' => ['size' => 16]];
    $border_style = array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000'))));

    $cell_style = array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    );

    //входящие параметры для отчёта
    $area = string_formating_for_sql($_GET["area"]);//округ
    $shelter = string_formating_for_sql($_GET["shelter"]);//приют
    $date_from = string_formating_for_sql($_GET["date_from"]);
    $date_to = string_formating_for_sql($_GET["date_to"]);

    $arrival_date_from = string_formating_for_sql($_GET["arrival_date_from"]);
    $arrival_date_to = string_formating_for_sql($_GET["arrival_date_to"]);

    $departure_date_from = string_formating_for_sql($_GET["departure_date_from"]);
    $departure_date_to = string_formating_for_sql($_GET["departure_date_to"]);

    $species = string_formating_for_sql($_GET["species"]);
    $nick = string_formating_for_sql($_GET["nick"]);
    $status = string_formating_for_sql($_GET["status"]);
    $sex = string_formating_for_sql($_GET["sex"]);
    $socialized = string_formating_for_sql($_GET["socialized"]);
    $departure_reason = string_formating_for_sql($_GET["departure_reason"]);
    //входящие параметры для отчёта

    if ($report == 'monitoring_report') {//Отчёт о мониторинге
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('P')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:P2');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        //Округ
        $objPHPExcel->getActiveSheet()->mergeCells('A4:P4');
        //Приют
        $objPHPExcel->getActiveSheet()->mergeCells('A5:P5');
        //Период
        $objPHPExcel->getActiveSheet()->mergeCells('A6:P6');

        $objPHPExcel->getActiveSheet()->setCellValue('A8', "№ п/п");
        $objPHPExcel->getActiveSheet()->mergeCells('A8:A9');
        $objPHPExcel->getActiveSheet()->getStyle('A8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B8', "Округ");
        $objPHPExcel->getActiveSheet()->mergeCells('B8:B9');
        $objPHPExcel->getActiveSheet()->getStyle('B8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C8', "Наименование приюта");
        $objPHPExcel->getActiveSheet()->mergeCells('C8:C9');
        $objPHPExcel->getActiveSheet()->getStyle('C8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D8', "Адрес приюта");
        $objPHPExcel->getActiveSheet()->mergeCells('D8:D9');
        $objPHPExcel->getActiveSheet()->getStyle('D8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D9")->applyFromArray($border_style);

        // Количество животных в приюте на начало       ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('E8:G8');
        $objPHPExcel->getActiveSheet()->setCellValue('E8', "Количество животных в приюте на начало отчетного периода, ед.");
        $objPHPExcel->getActiveSheet()->getStyle('E8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("G8")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E9', 'Всего в том числе');
        $objPHPExcel->getActiveSheet()->getStyle('E9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F9', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('F9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G9', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('G9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G9")->applyFromArray($border_style);

        // Прибыло в приют в течении отчетного        ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('H8:J8');
        $objPHPExcel->getActiveSheet()->setCellValue('H8', "Прибыло в приют в течении отчетного периода, ед.");
        $objPHPExcel->getActiveSheet()->getStyle('H8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("I8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J8")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H9', 'Всего в том числе');
        $objPHPExcel->getActiveSheet()->getStyle('H9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I9', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('I9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J9', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('J9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J9")->applyFromArray($border_style);

        // Выбыло из приюта в течении отчетного       ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('K8:M8');
        $objPHPExcel->getActiveSheet()->setCellValue('K8', "Выбыло из приюта в течении отчетного периода, ед.");
        $objPHPExcel->getActiveSheet()->getStyle('K8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("L8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("M8")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K9', 'Всего в том числе');
        $objPHPExcel->getActiveSheet()->getStyle('K9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('L9', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('L9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("L9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('M9', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('M9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("M9")->applyFromArray($border_style);

        // Количество животных в приюте на конец       ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('N8:P8');
        $objPHPExcel->getActiveSheet()->setCellValue('N8', "Количество животных в приюте на конец отчетного периода, ед.");
        $objPHPExcel->getActiveSheet()->getStyle('N8')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("N8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("O8")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("P8")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('N9', 'Всего в том числе');
        $objPHPExcel->getActiveSheet()->getStyle('N9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("N9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('O9', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('O9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("O9")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('P9', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('P9')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("P9")->applyFromArray($border_style);

    } else if ($report == 'dvigenie_beznadzor') {//ежемесячный отчет "Информация по движению безнадзорных животных (собак, кошек) в приютах
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('P')->setAutoSize(true);

        // Устанавливаем текст в ячейку A1 с переносом строки
        // Устанавливаем выравнивание текста по центру

        $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle("A1")->applyFromArray($border_style);

        // общие стили обьединенных ячеек
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("G3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("H3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("I3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("L2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("M2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("N2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("O2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("O3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("P2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("P3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B4")->applyFromArray($border_style);

        //Администр. округ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('A2:A4');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "Администр. округ");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        //Адрес приюта================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('B2:B4');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Адрес приюта");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        //Вместимость приюта================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('C2:D3');
        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Вместимость приюта");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('C4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('D4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D4")->applyFromArray($border_style);

        //Содержатся  в приюте на первое число месяца================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('E2:F3');
        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Содержатся в приюте на первое число месяца отчетного периода");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('E4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('F4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F4")->applyFromArray($border_style);

        //Отловлено животных за отчетный период================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('G2:H3');
        $objPHPExcel->getActiveSheet()->mergeCells('G3:H3');
        $objPHPExcel->getActiveSheet()->setCellValue('G2', "Отловлено животных за отчетный период");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('G4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H4")->applyFromArray($border_style);

        //Поступило в приют животных  за отчетный период================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('I2:J3');
        $objPHPExcel->getActiveSheet()->setCellValue('I2', "Поступило в приют животных  за отчетный период");
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('I4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('J4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J4")->applyFromArray($border_style);

        //Выбыло из приюта животных за отчетный период===============================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('K2:N2');
        $objPHPExcel->getActiveSheet()->setCellValue('K2', "Выбыло из приюта животных за отчетный период");
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        //Передано новым владельцам===============================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('K3:L3');
        $objPHPExcel->getActiveSheet()->setCellValue('K3', "Передано новым владельцам");
        $objPHPExcel->getActiveSheet()->getStyle('K3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('K4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('L4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('L4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("L4")->applyFromArray($border_style);

        //Выбыло по смерти и др.===============================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('M3:N3');
        $objPHPExcel->getActiveSheet()->setCellValue('M3', "Выбыло по смерти и др.");
        $objPHPExcel->getActiveSheet()->getStyle('M3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("M3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('M4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('M4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("M4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('N4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('N4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("N4")->applyFromArray($border_style);

        //Количество животных, содержащихся на конец месяца.===============================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('O2:P3');
        $objPHPExcel->getActiveSheet()->mergeCells('O3:P3');
        $objPHPExcel->getActiveSheet()->setCellValue('O2', "Количество животных, содержащихся на конец месяца  отчетного периода");
        $objPHPExcel->getActiveSheet()->getStyle('O2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('O4', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('O4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("O4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('P4', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('P4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("P4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A5', "1");
        $objPHPExcel->getActiveSheet()->setCellValue('B5', "2");
        $objPHPExcel->getActiveSheet()->setCellValue('C5', "3");
        $objPHPExcel->getActiveSheet()->setCellValue('D5', "4");
        $objPHPExcel->getActiveSheet()->setCellValue('E5', "5");
        $objPHPExcel->getActiveSheet()->setCellValue('F5', "6");
        $objPHPExcel->getActiveSheet()->setCellValue('G5', "7");
        $objPHPExcel->getActiveSheet()->setCellValue('H5', "8");
        $objPHPExcel->getActiveSheet()->setCellValue('I5', "9");
        $objPHPExcel->getActiveSheet()->setCellValue('J5', "10");
        $objPHPExcel->getActiveSheet()->setCellValue('K5', "11");
        $objPHPExcel->getActiveSheet()->setCellValue('L5', "12");
        $objPHPExcel->getActiveSheet()->setCellValue('M5', "13");
        $objPHPExcel->getActiveSheet()->setCellValue('N5', "14");
        $objPHPExcel->getActiveSheet()->setCellValue('O5', "15");
        $objPHPExcel->getActiveSheet()->setCellValue('P5', "16");

    } else if ($report == 'catch_in_shelter_week_info') {//Информация по отловленным и поступившим в приюты (собак, кошек) за неделю
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('P')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')))));
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')))));

        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle("A1")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "Администр. округ");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('B2:B3');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Адрес приюта");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('C2:D2');
        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Отловлено");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C3', "Собаки");
        $objPHPExcel->getActiveSheet()->getStyle('C3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D3', "Кошки");
        $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('E2:F2');
        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Поступило в приюты");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E3', "Собаки");
        $objPHPExcel->getActiveSheet()->getStyle('E3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F3', "Кошки");
        $objPHPExcel->getActiveSheet()->getStyle('F3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getColumnDimension("A1")->setWidth(300);
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(50);

    } else if ($report == 'shelters_report') {//Отчёт о животных, содержащихся в приютах
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('O')->setAutoSize(true);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Отчёт о животных, содержащихся в приютах');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:O1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:O1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:O1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "Округ");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Управляющая организация");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Приют");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Основания для приема в приют \r\n(Наименование и № документа)");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Статус");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "№ К/У");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G2', "№ чипа");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H2', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I2', "Вид");
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J2', "Пол");
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K2', "Окрас");
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('L2', "Возраст");
        $objPHPExcel->getActiveSheet()->getStyle('L2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("L2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('M2', "Дата поступления");
        $objPHPExcel->getActiveSheet()->getStyle('M2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("M2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('N2', "Дата выбытия");
        $objPHPExcel->getActiveSheet()->getStyle('N2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("N2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('O2', "Социализация");
        $objPHPExcel->getActiveSheet()->getStyle('O2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("O2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('P2', "Вакцинация");
        $objPHPExcel->getActiveSheet()->getStyle('P2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("P2")->applyFromArray($border_style);

    } else if ($report == 'mosvet_week_report') {//Мосветобъединение недельный отчет
        $objPHPExcel->setActiveSheetIndex(0)->setTitle("Отловленные собаки");
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('K')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);

        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Мосветобъединение недельный отчет');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Порода");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Пол");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "Год рождения");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G2', "Окрас");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H2', "Особые приметы");
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I2', "Идентификационный номер");
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J2', "Дата поступления в приют");
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K2', "№ карточки");
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        //        ------------------------------------------------------------------

        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1)->setTitle("Отловленные кошки");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Мосветобъединение недельный отчет');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Порода");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Пол");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "Год рождения");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G2', "Окрас");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H2', "Особые приметы");
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I2', "Идентификационный номер");
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J2', "Дата поступления в приют");
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K2', "№ карточки");
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        //        ------------------------------------------------------------------

        $objPHPExcel->createSheet(2);
        $objPHPExcel->setActiveSheetIndex(2)->setTitle("павшие");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Реестр павших животных');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№ п/п");
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Информация о животном');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:H2');
        $objPHPExcel->getActiveSheet()->getStyle("B2:H2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B3', "Вид");
        $objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C3', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D3', "Особые приметы");
        $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E3', "Порода");
        $objPHPExcel->getActiveSheet()->getStyle('E3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F3', "Пол");
        $objPHPExcel->getActiveSheet()->getStyle('F3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G3', "Год рождения");
        $objPHPExcel->getActiveSheet()->getStyle('G3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H3', "Окрас");
        $objPHPExcel->getActiveSheet()->getStyle('H3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I2', "№ чипа");
        $objPHPExcel->getActiveSheet()->mergeCells('I2:I3');
        $objPHPExcel->getActiveSheet()->getStyle("I3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')))));
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J2', "Дата падежа");
        $objPHPExcel->getActiveSheet()->mergeCells('J2:J3');
        $objPHPExcel->getActiveSheet()->getStyle("J3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')))));
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K2', "Причина смерти");
        $objPHPExcel->getActiveSheet()->mergeCells('K2:K3');
        $objPHPExcel->getActiveSheet()->getStyle("K3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')))));
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('L2', "Дата поступления в приют");
        $objPHPExcel->getActiveSheet()->mergeCells('L2:L3');
        $objPHPExcel->getActiveSheet()->getStyle("L3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')))));
        $objPHPExcel->getActiveSheet()->getStyle('L2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("L2")->applyFromArray($border_style);

        //        ------------------------------------------------------------------

        $objPHPExcel->createSheet(3);
        $objPHPExcel->setActiveSheetIndex(3)->setTitle("передача");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Реестр животных, переданных новым владельцам');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№ п/п");
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Дата передачи");
        $objPHPExcel->getActiveSheet()->mergeCells('B2:B3');
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->mergeCells('C2:D2');
        $objPHPExcel->getActiveSheet()->setCellValue('C2', '№ договора');
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C3', "собака");
        $objPHPExcel->getActiveSheet()->getStyle('C3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D3', "кошка");
        $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "кличка");
        $objPHPExcel->getActiveSheet()->mergeCells('E2:E3');
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');
        $objPHPExcel->getActiveSheet()->setCellValue('F2', 'Животное');
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F3', "собака");
        $objPHPExcel->getActiveSheet()->getStyle('F3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G3', "кошка");
        $objPHPExcel->getActiveSheet()->getStyle('G3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H2', "№ чипа");
        $objPHPExcel->getActiveSheet()->mergeCells('H2:H3');
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("H3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->setCellValue('I2', "Ф.И.О. нового владельца");
        $objPHPExcel->getActiveSheet()->mergeCells('I2:I3');
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("I3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->setCellValue('J2', "Примечание");
        $objPHPExcel->getActiveSheet()->mergeCells('J2:J3');
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->setCellValue('K2', "№ карточки");
        $objPHPExcel->getActiveSheet()->mergeCells('K2:K3');
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("K3")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

    } else if ($report == 'aviaries_report') {//Список животных по вольерам
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

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Список животных по вольерам за ' . date("Y-m-d") . '');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№ вольера");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "№ К/У");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "№ чипа");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "ФИО ответственного за содержание животного");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

    } else if ($report == 'euthanasia_report') {//Журнал учета случаев эвтаназии
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Журнал учета случаев эвтаназии за период с ' . $date_from . ' по ' . $date_to . '');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($font_style);

        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(false);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);

        //

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "Приют");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "№ К/У");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "№ чипа");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Дата эвтаназии");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "Причина эвтаназии");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G2', "ФИО ветеринарного врача, проводившего эвтаназию");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H2', "Подпись ветеринарного врача");
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);

    } else if ($report == 'new_owner_info') {//Информация о новых владельцах животных
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "Административный округ");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Фамилия Имя Отчество нового владельца");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Дата передачи животного по договору");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Категория животного (собака, кошка)");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Идентификационная метка (микрочип)");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

    } else if ($report == 'mosvet_death_info') {//"Мосветобъединение ежемесячная информация от АО по павшим животным
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);

        // общие стили обьединенных ячеек

        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("H3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("I3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J3")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:J2');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "пало животных за отчетный период");

        //кошки================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', "кошки");
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray($border_style);

        //Всего (с указанием номеров чипов)================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', "Всего (с указанием номеров чипов)");
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A4")->applyFromArray($border_style);

        //В том числе:================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('B4:E4');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', "В том числе:");
        $objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B4")->applyFromArray($border_style);

        //труп направлен в ГВЛ на пат. вскрытие с последующей утилизацией================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('B5', "труп направлен в ГВЛ на пат. вскрытие с последующей утилизацией (с указанием номера чипа)");
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B5")->applyFromArray($border_style);

        //труп направлен на утилизацию в стороннюю организацию, по оформленному ВСД ветврачом СББЖ АО (с указанием номера чипа)================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('C5', "труп направлен на утилизацию в стороннюю организацию, по оформленному ВСД ветврачом СББЖ АО (с указанием номера чипа)");
        $objPHPExcel->getActiveSheet()->getStyle('C5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C5")->applyFromArray($border_style);

        //№ ВСД, оформленный ветврачом СББЖ АО================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('D5', "№ ВСД, оформленный ветврачом СББЖ АО");
        $objPHPExcel->getActiveSheet()->getStyle('D5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D5")->applyFromArray($border_style);

        //Биологические отходы находятся на хранении  (с указанием номера чипа)================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('E5', "Биологические отходы находятся на хранении  (с указанием номера чипа)");
        $objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E5")->applyFromArray($border_style);

        //собаки================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('F3:J3');
        $objPHPExcel->getActiveSheet()->setCellValue('F3', "собаки");
        $objPHPExcel->getActiveSheet()->getStyle('F3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);

        //Всего (с указанием номеров чипов)================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('F4:F5');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', "Всего (с указанием номеров чипов)");
        $objPHPExcel->getActiveSheet()->getStyle('F4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F4")->applyFromArray($border_style);

        //В том числе:================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('G4:J4');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', "В том числе:");
        $objPHPExcel->getActiveSheet()->getStyle('G4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G4")->applyFromArray($border_style);

        //труп направлен в ГВЛ на пат. вскрытие с последующей утилизацией================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('G5', "труп направлен в ГВЛ на пат. вскрытие с последующей утилизацией (с указанием номера чипа)");
        $objPHPExcel->getActiveSheet()->getStyle('G5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G5")->applyFromArray($border_style);

        //труп направлен на утилизацию в стороннюю организацию, по оформленному ВСД ветврачом СББЖ АО (с указанием номера чипа)================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('H5', "труп направлен на утилизацию в стороннюю организацию, по оформленному ВСД ветврачом СББЖ АО (с указанием номера чипа)");
        $objPHPExcel->getActiveSheet()->getStyle('H5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H5")->applyFromArray($border_style);

        //№ ВСД, оформленный ветврачом СББЖ АО================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('I5', "№ ВСД, оформленный ветврачом СББЖ АО");
        $objPHPExcel->getActiveSheet()->getStyle('I5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I5")->applyFromArray($border_style);

        //Биологические отходы находятся на хранении  (с указанием номера чипа)================================================================================

        $objPHPExcel->getActiveSheet()->setCellValue('J5', "Биологические отходы находятся на хранении  (с указанием номера чипа)");
        $objPHPExcel->getActiveSheet()->getStyle('J5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J5")->applyFromArray($border_style);

    } else if ($report == 'out_death_signature') {//Выбытие по причине смерти с описью
        $currentDate = date('d.m.Y');
        $objPHPExcel->setActiveSheetIndex(0)->setTitle("Опись");
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');

        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

        $objPHPExcel->getActiveSheet()->setCellValue('A3', "вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B3', "кличка");
        $objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C3', "порода");
        $objPHPExcel->getActiveSheet()->getStyle('C3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D3', "пол");
        $objPHPExcel->getActiveSheet()->getStyle('D3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E3', "год рождения");
        $objPHPExcel->getActiveSheet()->getStyle('E3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F3', "окрас");
        $objPHPExcel->getActiveSheet()->getStyle('F3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G3', "чип");
        $objPHPExcel->getActiveSheet()->getStyle('G3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G3")->applyFromArray($border_style);

        //        -----------------------------------------------------------------

        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1)->setTitle("Заявка");

        $formatted_date_from = date("d.m.Yг.", strtotime($date_from));

        $objPHPExcel->getActiveSheet()->setCellValue('A1', $shelter ?? $org_name_value);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');

        $objPHPExcel->getActiveSheet()->setCellValue('A2', $shelter . ' Выбытие животных в период с ' . $formatted_date_from . '');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:K2');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(false);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

        $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', "№ п/п (акт смерти)");
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('B4:I4');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', "Информация о животном");
        $objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B4")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B5', "вид");
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("C4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('C5', "кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("D4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('D5', "порода");
        $objPHPExcel->getActiveSheet()->getStyle('D5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("E4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('E5', "пол");
        $objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("F4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('F5', "год рождения");
        $objPHPExcel->getActiveSheet()->getStyle('F5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("G4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('G5', "сектор вольер");
        $objPHPExcel->getActiveSheet()->getStyle('G5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("H4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('H5', "окрас");
        $objPHPExcel->getActiveSheet()->getStyle('H5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->getStyle("I4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('I5', "№ чипа");
        $objPHPExcel->getActiveSheet()->getStyle('I5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
        $objPHPExcel->getActiveSheet()->setCellValue('J4', "Дата падежа");
        $objPHPExcel->getActiveSheet()->getStyle('J4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('K4:K5');
        $objPHPExcel->getActiveSheet()->setCellValue('K4', "Причина смерти");
        $objPHPExcel->getActiveSheet()->getStyle('K4')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K4")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("K5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('A6', "1");
        $objPHPExcel->getActiveSheet()->getStyle("A6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('B6', "2");
        $objPHPExcel->getActiveSheet()->getStyle("B6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('C6', "3");
        $objPHPExcel->getActiveSheet()->getStyle("C6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('D6', "4");
        $objPHPExcel->getActiveSheet()->getStyle("D6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('E6', "5");
        $objPHPExcel->getActiveSheet()->getStyle("E6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('F6', "6");
        $objPHPExcel->getActiveSheet()->getStyle("F6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('G6', "7");
        $objPHPExcel->getActiveSheet()->getStyle("G6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('H6', "8");
        $objPHPExcel->getActiveSheet()->getStyle("H6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('I6', "9");
        $objPHPExcel->getActiveSheet()->getStyle("I6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('J6', "10");
        $objPHPExcel->getActiveSheet()->getStyle("J6")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->setCellValue('K6', "11");
        $objPHPExcel->getActiveSheet()->getStyle("K6")->applyFromArray($border_style);


    } else if ($report == 'castrated_info') {//Информация по стерилизации животных за отчетную неделю/месяц
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle("A1")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№ п/п");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('B2:B3');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Округ");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('C2:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Наименование приюта");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('D2:D3');
        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Адрес приюта");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('E2:G2');
        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Количество простерилизованных животных в приюте в течение отчетного периода, ед.");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E3', "Всего, в том числе");
        $objPHPExcel->getActiveSheet()->getStyle('E3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F3', "Собаки");
        $objPHPExcel->getActiveSheet()->getStyle('F3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F3")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G3', "Кошки");
        $objPHPExcel->getActiveSheet()->getStyle('G3')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G3")->applyFromArray($border_style);

    } else if ($report == 'reestr_count_animals') {//реестр о количестве животных в приюте
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('O')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Чип");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Пол");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "Порода");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G2', "Дата рождения");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H2', "Окрас");
        $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I2', "Вакцинация от бешенства 2022 года.");
        $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J2', "Вакцинация от бешенства 2023 года.");
        $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('K2', "Вакцинация от бешенства 2024 года.");
        $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("K2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('L2', "Вакцинация от лептоспироза и чумы плотоядных 2022 года.");
        $objPHPExcel->getActiveSheet()->getStyle('L2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("L2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('M2', "Вакцинация от лептоспироза и чумы плотоядных 2023 года.");
        $objPHPExcel->getActiveSheet()->getStyle('M2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("M2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('N2', "Вакцинация от лептоспироза и чумы плотоядных 2024 года.");
        $objPHPExcel->getActiveSheet()->getStyle('N2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("N2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('O2', "Дата поступления в приют");
        $objPHPExcel->getActiveSheet()->getStyle('O2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("O2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('P2', "Дата снятия с учета");
        $objPHPExcel->getActiveSheet()->getStyle('P2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("P2")->applyFromArray($border_style);

    } else if ($report == 'puppy_to_dog' or $report == 'kitty_to_cat') {//акт "Перевода из щенков\котят в собаки\кошки с описью"
        $currentDate = date('d.m.Y');

        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);

        $objPHPExcel->setActiveSheetIndex(0)->setTitle("Опись");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Опись животных');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');

        $objPHPExcel->getActiveSheet()->setCellValue('A2', "№");
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('B2', "Вид животного");
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C2', "Кличка");
        $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D2', "Возраст");
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E2', "Пол");
        $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F2', "Окрас");
        $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F2")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G2', "идентификационный номер");
        $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G2")->applyFromArray($border_style);

        //        ------------------------------------------------------------------

        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1)->setTitle("Акт");
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Акт №');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->applyFromArray($cell_style);

        if ($report == 'puppy_to_dog') {
            $objPHPExcel->getActiveSheet()->setCellValue('A2', 'о переводе животных (собак) из категории');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
            $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);

            $objPHPExcel->getActiveSheet()->setCellValue('A3', 'щенки в категорию "взрослые особи"');
            $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');
            $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->applyFromArray($cell_style);

            $objPHPExcel->getActiveSheet()->setCellValue('A10', 'Прошу перевести с ' . $currentDate . ' собак из категории "щенки" в категорию "взрослые ');
            $objPHPExcel->getActiveSheet()->setCellValue('A11', 'собаки" согласно приложению (описи) ');
            $objPHPExcel->getActiveSheet()->mergeCells('A11:H11');
        }
        if ($report == 'kitty_to_cat') {
            $objPHPExcel->getActiveSheet()->setCellValue('A2', 'о переводе животных (кошек) из категории');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
            $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->applyFromArray($cell_style);

            $objPHPExcel->getActiveSheet()->setCellValue('A3', 'котята в категорию "взрослые особи"');
            $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');
            $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->applyFromArray($cell_style);

            $objPHPExcel->getActiveSheet()->mergeCells('A10:H10');
            $objPHPExcel->getActiveSheet()->setCellValue('A10', 'Прошу перевести с ' . $currentDate . ' кошек из категории "котята" в категорию "взрослые ');
            $objPHPExcel->getActiveSheet()->setCellValue('A11', 'кошки" согласно приложению (описи) ');
            $objPHPExcel->getActiveSheet()->mergeCells('A11:H11');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'Административный округ: ');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:C7');
        $objPHPExcel->getActiveSheet()->mergeCells('D7:H7');

        $objPHPExcel->getActiveSheet()->setCellValue('A8', 'Приют: ');
        $objPHPExcel->getActiveSheet()->mergeCells('B8:H8');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:H10');

        // подписанты
        $objPHPExcel->getActiveSheet()->mergeCells('A15:D15');
        $objPHPExcel->getActiveSheet()->mergeCells('F15:H15');
        $objPHPExcel->getActiveSheet()->mergeCells('A17:D17');
        $objPHPExcel->getActiveSheet()->mergeCells('F17:H17');
        $objPHPExcel->getActiveSheet()->mergeCells('A19:D19');
        $objPHPExcel->getActiveSheet()->mergeCells('F19:H19');

        $objPHPExcel->getActiveSheet()->setCellValue('A15', '_____________________');
        $objPHPExcel->getActiveSheet()->setCellValue('A17', '_____________________');
        $objPHPExcel->getActiveSheet()->setCellValue('A19', '_____________________');
        $objPHPExcel->getActiveSheet()->setCellValue('F15', '_____________________');
        $objPHPExcel->getActiveSheet()->setCellValue('F17', '_____________________');
        $objPHPExcel->getActiveSheet()->setCellValue('F19', '_____________________');

    } else if ($report == 'fauna_monitoring_report') {//Отчёт о мониторинге
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn('J')->setAutoSize(true);

        $objPHPExcel->getActiveSheet()->getStyle("A7")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("B7")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("F5")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("H5")->applyFromArray(array('borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')), 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("D5")->applyFromArray(array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("C5")->applyFromArray(array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("C6")->applyFromArray(array('borders' => array('left' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("H6")->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("J5")->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("J6")->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));


        $objPHPExcel->getActiveSheet()->getStyle("F5")->applyFromArray(array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("G5")->applyFromArray(array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("I5")->applyFromArray(array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));
        $objPHPExcel->getActiveSheet()->getStyle("J5")->applyFromArray(array('borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->getStyle("A6")->applyFromArray(array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '000')),)));

        $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->mergeCells('A5:A7');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', "№/№");
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("A5")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->mergeCells('B5:B7');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', "Административный округ");
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B5")->applyFromArray($border_style);

        // Кол-во отловленных животных       ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('C5:D6');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', "Кол-во отловленных животных");
        $objPHPExcel->getActiveSheet()->getStyle('C5')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C7', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('C7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C7")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D7', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('D7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D7")->applyFromArray($border_style);

        // Кол-во животных, поступивших в приют        ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('E5:H5');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', "Кол-во животных, поступивших в приют");
        $objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E5")->applyFromArray($border_style);

        // возвращенные (подкидыши)        ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('E6:F6');
        $objPHPExcel->getActiveSheet()->setCellValue('E6', "возвращенные (подкидыши)");
        $objPHPExcel->getActiveSheet()->getStyle('E6')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E6")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E7', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('E7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E7")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('F7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F7")->applyFromArray($border_style);

        // доставленные после стерилизации)        ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('G6:H6');
        $objPHPExcel->getActiveSheet()->setCellValue('G6', "доставленные после стерилизации");
        $objPHPExcel->getActiveSheet()->getStyle('G6')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G6")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G7', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('G7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("G7")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('H7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("H7")->applyFromArray($border_style);

        // Примечание         ================================================================================

        $objPHPExcel->getActiveSheet()->mergeCells('I5:J7');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', "Примечание  ");
        $objPHPExcel->getActiveSheet()->getStyle('I5')->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('I7', 'Собаки');
        $objPHPExcel->getActiveSheet()->getStyle('I7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("I7")->applyFromArray($border_style);

        $objPHPExcel->getActiveSheet()->setCellValue('J7', 'Кошки');
        $objPHPExcel->getActiveSheet()->getStyle('J7')->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("J7")->applyFromArray($border_style);

    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="report.xlsx"');
    header('Cache-Control: max-age=0');

    $org_user_id = user_org_id($configuration);

    if (!$shelter) {
        $shelter = $org_user_id;
    }

    if ($report == 'monitoring_report') {
        $shelter = $_GET["shelter"];
        $fio_chif = $_GET['fio_chif'];

        $query_orgs = '';
        if (!$area) {
            if (!$shelter) {
                //Проверяем есть ли у этой организации управляемые
                $m_orgs_array = getManagingOrgs($org_user_id);

                if (count($m_orgs_array) > 0) {
                    $query_orgs .= 'WHERE (';
                    for ($i = 0; $i <= count($m_orgs_array); $i++) {
                        if ($m_orgs_array[$i]) {
                            if ($i > 0) {
                                $query_orgs .= ' OR ';
                            }
                            $query_orgs .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                        }
                    }
                    $query_orgs .= ')';
                } else {
                    $query_orgs .= 'WHERE organizations.id=' . $org_user_id . ' ';
                }
                //Проверяем есть ли у этой организации управляемые
            } else {
                $query_orgs .= 'WHERE organizations.id=' . $shelter . ' ';
            }
        } else {
            $query_orgs .= 'WHERE organizations.id_area=' . $area . ' AND organizations.organization_type_const=\'shelter\' ';

            $m_orgs_array = getManagingOrgs($org_user_id);
            if (count($m_orgs_array) > 0) {
                $query_orgs_ .= 'AND (';
                for ($i = 0; $i <= count($m_orgs_array); $i++) {
                    if ($m_orgs_array[$i]) {
                        if ($i > 0) {
                            $query_orgs_ .= ' OR ';
                        }
                        $query_orgs_ .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                    }
                }
                $query_orgs_ .= ')';
            }
            if ($query_orgs_) {
                $query_orgs .= $query_orgs_;
            }
        }

        $query = 'SELECT organizations.id, organizations.short_name as short_name, ';
        $query .= 'organizations.name AS shelter, managing.name,areas.name AS area,fias_addresses.full_address AS address ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=organizations.managing_organization_id ';
        $query .= 'LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';
        $query .= $query_orgs;

        $n = 10;

        //кошки - 9
        //собаки - 25

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $count_row = 0;
        while ($row = pg_fetch_assoc($result)) {
            $count_row += 1;

            $cats_start = 0;
            $dogs_start = 0;
            $dogs_in = 0;
            $cats_in = 0;
            $dogs_out = 0;
            $cats_out = 0;
            $dogs_finish = 0;
            $cats_finish = 0;

            //Количество животных на начало отчетного периода, ед.

            // кошки

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date >= '" . $date_from . "' OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.status != 'DEPARTURED'";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_start = $res[0];
            pg_free_result($result_count);

            // собаки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date >= '" . $date_from . "' OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.status != 'DEPARTURED'";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_start = $res[0];
            pg_free_result($result_count);
            //

            //Прибыло в течение отчетного периода, ед.
            // кошки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.arrival_date <= '" . $date_to . "' AND shelter_guests.arrival_date >= '" . $date_from . "') ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_in = $res[0];
            pg_free_result($result_count);

            // собаки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.arrival_date <= '" . $date_to . "' AND shelter_guests.arrival_date >= '" . $date_from . "') ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_in = $res[0];
            pg_free_result($result_count);
            //

            //Выбыло в течение отчетного периода, ед.

            //кошки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date <= '" . $date_to . "' AND shelter_guests.departure_date >= '" . $date_from . "') ";
            $query_count .= "AND pets.id_species=9 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.status = 'DEPARTURED' ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_out = $res[0];
            pg_free_result($result_count);

            // собаки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date <= '" . $date_to . "' AND shelter_guests.departure_date >= '" . $date_from . "') ";
            $query_count .= "AND pets.id_species=25 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.status = 'DEPARTURED' ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_out = $res[0];
            pg_free_result($result_count);

            //Количество животных на конец отчетного периода, ед.

            // кошки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date >= '" . $date_to . "' OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.status != 'DEPARTURED'";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_finish = $res[0];
            pg_free_result($result_count);

            // собаки
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date >= '" . $date_to . "' OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.status != 'DEPARTURED'";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_finish = $res[0];
            pg_free_result($result_count);
            //

//            $cats_finish = $cats_in + $cats_start - $cats_out;
//            $dogs_finish = $dogs_in + $dogs_start - $dogs_out;

            $formatted_date_from = date("d.m.Yг.", strtotime($date_from));
            $formatted_date_to = date("d.m.Yг.", strtotime($date_to));

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', "Отчёт")
                ->setCellValue('A2', "о мониторинге животных в (городских) приютах " . $row['area'])
                ->setCellValue('A4', 'Административный округ: ' . $row['area'])
                ->setCellValue('A5', 'Приют: ' . $row['shelter'])
                ->setCellValue('A6', 'Период: c ' . $formatted_date_from . ' по ' . $formatted_date_to)
                ->setCellValue('A' . $n, $count_row)
                ->setCellValue('B' . $n, $row['area'])
                ->setCellValue('C' . $n, $row['shelter'])
                ->setCellValue('D' . $n, $row['address'])
                ->setCellValue('E' . $n, $cats_start + $dogs_start)
                ->setCellValue('F' . $n, $cats_start)
                ->setCellValue('G' . $n, $dogs_start)
                ->setCellValue('H' . $n, $cats_in + $dogs_in)
                ->setCellValue('I' . $n, $cats_in)
                ->setCellValue('J' . $n, $dogs_in)
                ->setCellValue('K' . $n, $cats_out + $dogs_out)
                ->setCellValue('L' . $n, $cats_out)
                ->setCellValue('M' . $n, $dogs_out)
                ->setCellValue('N' . $n, $dogs_finish + $cats_finish)
                ->setCellValue('O' . $n, $cats_finish)
                ->setCellValue('P' . $n, $dogs_finish)
                ->setCellValue('B' . ($n + 4), "Начальник отдела:")
                ->setCellValue('N' . ($n + 4), $fio_chif ?? "__________________");

            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->getAlignment()->applyFromArray($cell_style);

            $n++;
        }

        pg_free_result($result);
    }
    if ($report == 'dvigenie_beznadzor') {

        $shelter = $_GET["shelter"];

        $query_orgs = '';
        if (!$area) {
            if (!$shelter) {
                //Проверяем есть ли у этой организации управляемые
                $m_orgs_array = getManagingOrgs($org_user_id);

                if (count($m_orgs_array) > 0) {
                    $query_orgs .= 'WHERE (';
                    for ($i = 0; $i <= count($m_orgs_array); $i++) {
                        if ($m_orgs_array[$i]) {
                            if ($i > 0) {
                                $query_orgs .= ' OR ';
                            }
                            $query_orgs .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                        }
                    }
                    $query_orgs .= ')';
                } else {
                    $query_orgs .= 'WHERE organizations.id=' . $org_user_id . ' ';
                }
                //Проверяем есть ли у этой организации управляемые
            } else {
                $query_orgs .= 'WHERE organizations.id=' . $shelter . ' ';
            }
        } else {
            $query_orgs .= 'WHERE organizations.id_area=' . $area . ' AND organizations.organization_type_const=\'shelter\' ';

            $m_orgs_array = getManagingOrgs($org_user_id);
            if (count($m_orgs_array) > 0) {
                $query_orgs_ .= 'AND (';
                for ($i = 0; $i <= count($m_orgs_array); $i++) {
                    if ($m_orgs_array[$i]) {
                        if ($i > 0) {
                            $query_orgs_ .= ' OR ';
                        }
                        $query_orgs_ .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                    }
                }
                $query_orgs_ .= ')';
            }
            if ($query_orgs_) {
                $query_orgs .= $query_orgs_;
            }
        }

        $query = 'SELECT organizations.id, organizations.short_name as short_name, ';
        $query .= 'organizations.name AS shelter, managing.name,areas.name AS area,fias_addresses.full_address AS address ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=organizations.managing_organization_id ';
        $query .= 'LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';
        $query .= $query_orgs;

        $currentDate = date('Y-m-d'); // Получаем текущую дату в формате 'год-месяц-день'

        $firstDay = date('Y-m-01', strtotime($date_to)); // Находим первое число текущего месяца
        $lastDay = date('Y-m-t', strtotime($date_to)); // Находим последнее число текущего месяца

        $firstDay_after = date('Y-m-01', strtotime('+1 month', strtotime($date_to))); // Находим первое число сл месяца
        $lastDay_after = date('Y-m-t', strtotime('+1 month', strtotime($date_to))); // Находим последнее число сл месяца

        //кошки - 9
        //собаки - 25

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $count_row = 0;
        while ($row = pg_fetch_assoc($result)) {
            $count_row += 1;

            //кошек Содержатся в приюте на первое число месяца отчетного период
            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_date <= '" . $firstDay . "' ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_start_all_depar = $res[0];
            pg_free_result($result_count);

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.arrival_date <= '" . $firstDay . "' ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_start_all_arr = $res[0];
            pg_free_result($result_count);

            $cats_start_all = $cats_start_all_arr - $cats_start_all_depar;

            //собак Содержатся в приюте на первое число месяца отчетного период

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_date <= '" . $firstDay . "' ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_start_all_dep = $res[0];
            pg_free_result($result_count);

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.arrival_date <= '" . $firstDay . "' ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_start_all_arr = $res[0];
            pg_free_result($result_count);

            $dogs_start_all = $dogs_start_all_arr - $dogs_start_all_dep;
            //

            //кошек Отловлено животных за отчетный период

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.arrival_date < '" . $firstDay_after . "' AND shelter_guests.arrival_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=9 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.arrival_reason = 'CATCH'";


            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_catch_in_in_otchet_period = $res[0];
            pg_free_result($result_count);

            //собак Отловлено животных за отчетный период

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.arrival_date < '" . $firstDay_after . "' AND shelter_guests.arrival_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=25 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.arrival_reason = 'CATCH'";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_catch_in_in_otchet_period = $res[0];
            pg_free_result($result_count);

            //кошек Поступило в приют животных за отчетный период ед.
            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.arrival_date < '" . $firstDay_after . "' AND shelter_guests.arrival_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=9 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_in_in_otchet_period = $res[0];
            pg_free_result($result_count);

            //собак Поступило в приют животных за отчетный период ед.
            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.arrival_date < '" . $firstDay_after . "' AND shelter_guests.arrival_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=25 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_in_in_otchet_period = $res[0];
            pg_free_result($result_count);

            //кошек Выбыло в течение отчетного периода, ед причина Передано новым владельцам .
            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_reason = 'RETURNED_TO_NEW_OWNER' ";
            $query_count .= " AND (shelter_guests.departure_date < '" . $firstDay_after . "' AND shelter_guests.departure_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=9 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_out_in_otchet_period_to_new_owner = $res[0];
            pg_free_result($result_count);

            //кошек Выбыло в течение отчетного периода, ед причина Выбыло по смерти и др.
            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_reason != 'RETURNED_TO_NEW_OWNER' ";
            $query_count .= " AND (shelter_guests.departure_date < '" . $firstDay_after . "' AND shelter_guests.departure_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=9 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";


            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_out_in_otchet_period_die_and_else = $res[0];
            pg_free_result($result_count);

            //собак Выбыло в течение отчетного периода, ед причина Передано новым владельцам.

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_reason = 'RETURNED_TO_NEW_OWNER' ";
            $query_count .= " AND (shelter_guests.departure_date < '" . $firstDay_after . "' AND shelter_guests.departure_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=25 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";


            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_out_in_otchet_period_to_new_owner = $res[0];
            pg_free_result($result_count);

            //собак Выбыло в течение отчетного периода, ед причина Выбыло по смерти и др.

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_reason != 'RETURNED_TO_NEW_OWNER' ";
            $query_count .= " AND (shelter_guests.departure_date < '" . $firstDay_after . "' AND shelter_guests.departure_date >= '" . $firstDay . "') ";
            $query_count .= "AND pets.id_species=25 ";
            $query_count .= " AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_out_in_otchet_period_die_and_else = $res[0];
            pg_free_result($result_count);

            //кошек Содержатся в приюте на конец месяца отчетного период
            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_date <= '" . $firstDay_after . "' ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_finish_all_depar = $res[0];
            pg_free_result($result_count);

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.arrival_date <= '" . $firstDay_after . "' ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_finish_all_arr = $res[0];
            pg_free_result($result_count);

            $cats_finish_all = $cats_finish_all_arr - $cats_finish_all_depar;

            //собак Содержатся в приюте на конец месяца отчетного период

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.departure_date <= '" . $firstDay_after . "' ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_finish_all_dep = $res[0];
            pg_free_result($result_count);

            $query_count = 'SELECT COUNT(DISTINCT(shelter_guests.id_pet)) ';
            $query_count .= 'FROM shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND shelter_guests.arrival_date <= '" . $firstDay_after . "' ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_finish_all_arr = $res[0];
            pg_free_result($result_count);

            $dogs_finish_all = $dogs_finish_all_arr - $dogs_finish_all_dep;
            //

            $formatted_last_day = date("d.m.Yг.", strtotime($lastDay));

            $n = 6;

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', "Информация по движению безнадзорных животных (собак, кошек) в приютах по состоянию на " . $formatted_last_day)
                ->setCellValue('A' . $n, $row['area'])
                ->setCellValue('B' . $n, $row['address'])
                ->setCellValue('C' . $n, ' ')
                ->setCellValue('D' . $n, ' ')
                ->setCellValue('E' . $n, $dogs_start_all)
                ->setCellValue('F' . $n, $cats_start_all)
                ->setCellValue('G' . $n, $dogs_catch_in_in_otchet_period)
                ->setCellValue('H' . $n, $cats_catch_in_in_otchet_period)
                ->setCellValue('I' . $n, $dogs_in_in_otchet_period)
                ->setCellValue('J' . $n, $cats_in_in_otchet_period)
                ->setCellValue('K' . $n, $dogs_out_in_otchet_period_to_new_owner)
                ->setCellValue('L' . $n, $cats_out_in_otchet_period_to_new_owner)
                ->setCellValue('M' . $n, $dogs_out_in_otchet_period_die_and_else)
                ->setCellValue('N' . $n, $cats_out_in_otchet_period_die_and_else)
                ->setCellValue('O' . $n, $dogs_finish_all)
                ->setCellValue('P' . $n, $cats_finish_all);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->getAlignment()->applyFromArray($cell_style);

            // $objPHPExcel->getActiveSheet()->getColumnDimension("A1")->setWidth(300);

            $n++;
        }
        pg_free_result($result);
    } else if ($report == 'catch_in_shelter_week_info') { //Информация по отловленным и поступившим в приюты (собак, кошек) за неделю

        $period = $_GET["period"];
        $currentDate = date('Y-m-d');

        $formatted_date_from = date("d.m.Yг.", strtotime($date_from));
        $formatted_date_to = date("d.m.Yг.", strtotime($date_to));

        $query = 'SELECT areas.name,
        organizations.id, 
        organizations.id_area, 
        organizations.short_name as short_name, 
        organizations.name AS shelter, 
        areas.name AS area, 
        fias_addresses.full_address AS address ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';
        $query .= 'WHERE organizations.organization_type_const=\'shelter\' AND fias_addresses.full_address IS NOT NULL AND fias_addresses.full_address <> \'\' ';
        $query .= 'ORDER BY areas.name, organizations.id_area ';
        $n = 5;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $last_area = '';
        $area_totals = [
            'dogs_count_catch' => 0,
            'cats_count_catch' => 0,
            'dogs_count' => 0,
            'cats_count' => 0,
        ];
        $grand_totals = [
            'dogs_count_catch' => 0,
            'cats_count_catch' => 0,
            'dogs_count' => 0,
            'cats_count' => 0,
        ];  // Initialize grand totals

        while ($row = pg_fetch_assoc($result)) {
            // Пропустить записи с неуказанным адресом
            if (empty($row['address'])) {
                continue;
            }

            $count_row += 1;

            // кошек на начало
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
            $query_count .= 'shelter_guests.status, shelter_guests.arrival_reason, shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';
            $query_count .= 'WHERE ';
            $query_count .= 'organizations.id_area is NOT NULL ';
            $query_count .= "AND shelter_guests.id_organization=" . $row['id'] . " AND shelter_guests.arrival_reason = 'CATCH' AND (shelter_guests.arrival_date BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id, ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason,  ';
            $query_count .= 'shelter_guests.id_organization, shelter_guests.status,shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date';

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $cats_count_catch = pg_num_rows($result_count);
            pg_free_result($result_count);

            // собак на начало
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet,  ';
            $query_count .= 'shelter_guests.arrival_reason, shelter_guests.status, shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';
            $query_count .= 'WHERE ';
            $query_count .= 'organizations.id_area is NOT NULL ';
            $query_count .= "AND shelter_guests.id_organization=" . $row['id'] . " AND shelter_guests.arrival_reason = 'CATCH' AND (shelter_guests.arrival_date BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id,  ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $dogs_count_catch = pg_num_rows($result_count);
            pg_free_result($result_count);

            // кошек пойманных на начало
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet,  ';
            $query_count .= 'shelter_guests.status, shelter_guests.arrival_reason, shelter_guests.arrival_date, ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization, ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';
            $query_count .= 'WHERE ';
            $query_count .= 'organizations.id_area is NOT NULL ';
            $query_count .= "AND shelter_guests.id_organization=" . $row['id'] . " AND shelter_guests.arrival_reason <> 'CATCH' AND (shelter_guests.arrival_date BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id,  ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason,  ';
            $query_count .= 'shelter_guests.id_organization, shelter_guests.status,shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date';

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $cats_count = pg_num_rows($result_count);
            pg_free_result($result_count);

            // собак пойманных на начало
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet,  ';
            $query_count .= 'shelter_guests.arrival_reason, shelter_guests.status, shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization, shelter_guests.departure_reason,  ';
            $query_count .= 'shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';
            $query_count .= 'WHERE ';
            $query_count .= 'organizations.id_area is NOT NULL ';
            $query_count .= "AND shelter_guests.id_organization=" . $row['id'] . " AND shelter_guests.arrival_reason <> 'CATCH' AND (shelter_guests.arrival_date BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id,  ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason,  ';
            $query_count .= 'shelter_guests.id_organization, shelter_guests.status,shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date';

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $dogs_count = pg_num_rows($result_count);
            pg_free_result($result_count);

            $formatted_date_from = date("d.m.Yг.", strtotime($date_from));
            $formatted_date_to = date("d.m.Yг.", strtotime($date_to));

            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);


            // проверка поменялась ли area
            if ($row['area'] !== $last_area) {
                // если area меняется - пишем итог
                if ($last_area !== '') {
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('B' . $n, 'Итого:')
                        ->setCellValue('C' . $n, $area_totals['dogs_count_catch'])
                        ->setCellValue('D' . $n, $area_totals['cats_count_catch'])
                        ->setCellValue('E' . $n, $area_totals['dogs_count'])
                        ->setCellValue('F' . $n, $area_totals['cats_count']);

                    $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setItalic(true);

                    $n++;
                }

                // обьединяем для последней area
                if (isset($merge_start_row) && $merge_start_row < $n - 1) {
                    $objPHPExcel->getActiveSheet()->mergeCells("A{$merge_start_row}:A" . ($n - 1));
                    $objPHPExcel->getActiveSheet()->getStyle("A{$merge_start_row}:A" . ($n - 1))
                        ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                    $objPHPExcel->getActiveSheet()->getStyle("A{$merge_start_row}:A" . ($n - 1))->getAlignment()->setWrapText(true);
                }

                // сброс значений под каждый округ
                $area_totals = [
                    'dogs_count_catch' => 0,
                    'cats_count_catch' => 0,
                    'dogs_count' => 0,
                    'cats_count' => 0,
                ];

                // имя округа - если нет то ДЖКХ
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, $row['area'] ?? 'ДЖКХ');

                $last_area = $row['area'];
                $merge_start_row = $n;
            }

            $area_totals['dogs_count_catch'] += $dogs_count_catch;
            $area_totals['cats_count_catch'] += $cats_count_catch;
            $area_totals['dogs_count'] += $dogs_count;
            $area_totals['cats_count'] += $cats_count;

            $grand_totals['dogs_count_catch'] += $dogs_count_catch;
            $grand_totals['cats_count_catch'] += $cats_count_catch;
            $grand_totals['dogs_count'] += $dogs_count;
            $grand_totals['cats_count'] += $cats_count;

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B' . $n, $row['address'])
                ->setCellValue('C' . $n, $dogs_count_catch)
                ->setCellValue('D' . $n, $cats_count_catch)
                ->setCellValue('E' . $n, $dogs_count)
                ->setCellValue('F' . $n, $cats_count);

            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);

            $n++;
        }

        // итог по последней
        if ($last_area !== '') {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('B' . $n, 'Итого:')
                ->setCellValue('C' . $n, $area_totals['dogs_count_catch'])
                ->setCellValue('D' . $n, $area_totals['cats_count_catch'])
                ->setCellValue('E' . $n, $area_totals['dogs_count'])
                ->setCellValue('F' . $n, $area_totals['cats_count']);

            $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $n)->getFont()->setItalic(true);

            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);

            //обьединяем
            if (isset($merge_start_row) && $merge_start_row < $n) {
                $objPHPExcel->getActiveSheet()->mergeCells("A{$merge_start_row}:A" . $n);
                $objPHPExcel->getActiveSheet()->getStyle("A{$merge_start_row}:A" . $n)
                    ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }

            $n++;
        }

        // финальный итог по всем
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $n, 'ВСЕГО:')
            ->setCellValue('C' . $n, $grand_totals['dogs_count_catch'])
            ->setCellValue('D' . $n, $grand_totals['cats_count_catch'])
            ->setCellValue('E' . $n, $grand_totals['dogs_count'])
            ->setCellValue('F' . $n, $grand_totals['cats_count']);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', "Информация по отловленным и поступившим в приюты (собак, кошек) с\n" . $formatted_date_from . ' по ' . $formatted_date_to)
            ->setCellValue('A4', 1)
            ->setCellValue('B4', 2)
            ->setCellValue('C4', 3)
            ->setCellValue('D4', 4)
            ->setCellValue('E4', 5)
            ->setCellValue('F4', 6);

        $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
        $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);


        pg_free_result($result);
    }

    if ($report == 'new_owner_info') {

        $currentDate = date('Y-m-d');
        $formatted_date_to = date("d.m.Yг.", strtotime($date_to));
        $formatted_date_current = date("d.m.Yг.", strtotime($currentDate));

        $shelter = $_GET["shelter"];

        $query_orgs = '';
        if (!$area) {
            if (!$shelter) {
                //Проверяем есть ли у этой организации управляемые
                $m_orgs_array = getManagingOrgs($org_user_id);

                if (count($m_orgs_array) > 0) {
                    $query_orgs .= 'WHERE (';
                    for ($i = 0; $i <= count($m_orgs_array); $i++) {
                        if ($m_orgs_array[$i]) {
                            if ($i > 0) {
                                $query_orgs .= ' OR ';
                            }
                            $query_orgs .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                        }
                    }
                    $query_orgs .= ')';
                } else {
                    $query_orgs .= 'WHERE organizations.id=' . $org_user_id . ' ';
                }
                //Проверяем есть ли у этой организации управляемые
            } else {
                $query_orgs .= 'WHERE organizations.id=' . $shelter . ' ';
            }
        } else {
            $query_orgs .= 'WHERE organizations.id_area=' . $area . ' AND organizations.organization_type_const=\'shelter\' ';

            $m_orgs_array = getManagingOrgs($org_user_id);
            if (count($m_orgs_array) > 0) {
                $query_orgs_ .= 'AND (';
                for ($i = 0; $i <= count($m_orgs_array); $i++) {
                    if ($m_orgs_array[$i]) {
                        if ($i > 0) {
                            $query_orgs_ .= ' OR ';
                        }
                        $query_orgs_ .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                    }
                }
                $query_orgs_ .= ')';
            }
            if ($query_orgs_) {
                $query_orgs .= $query_orgs_;
            }
        }

        $query = 'SELECT organizations.id, organizations.short_name as short_name,  ';
        $query .= 'organizations.name AS shelter, managing.name, ';
        $query .= 'areas.name AS area,fias_addresses.full_address AS address ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=organizations.managing_organization_id ';
        $query .= 'LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';
        $query .= $query_orgs;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $count_row = 0;
        while ($row = pg_fetch_assoc($result)) {
            $count_row += 1;

            $query_res = 'SELECT public.shelter_guests.id_pet, ';
            $query_res .= 'public.shelter_guests.id_owner, public.pet_owners.fullname,  ';
            $query_res .= 'public.species.name, public.pet_identification.identification_code, ';
            $query_res .= 'public.pet_identification.id_ident_type, public.shelter_guests.departure_date,  ';
            $query_res .= 'public.fias_addresses.full_address as full_address ';

            $query_res .= 'FROM shelter_guests ';
            $query_res .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_res .= 'LEFT JOIN public.species ON pets.id_species=species.id ';
            $query_res .= 'LEFT JOIN public.pet_owners ON public.shelter_guests.id_owner=pet_owners.id ';
            $query_res .= 'LEFT JOIN public.pet_identification ON shelter_guests.id_pet=pet_identification.id_pet ';
            $query_res .= 'LEFT JOIN public.fias_addresses ON public.fias_addresses.id=pet_owners.id_fact_fias_address ';
            $query_res .= 'WHERE ';
            $query_res .= "shelter_guests.id_organization=" . $row['id'];
            $query_res .= " AND (shelter_guests.departure_date <= '" . $date_to . "' ";
            $query_res .= " AND shelter_guests.departure_date IS NOT NULL) ";
            $query_res .= "AND shelter_guests.departure_reason = 'RETURNED_TO_NEW_OWNER' ";
            $query_res .= "AND shelter_guests.departure_reason IS NOT NULL  ";
            $query_res .= "AND shelter_guests.status = 'DEPARTURED'  ";
//            $query_res .= "AND public.pet_owners.fullname  IS NOT NULL ";
            $query_res .= "ORDER BY shelter_guests.departure_date ASC ";

            $result = pg_query($query_res) or die('Ошибка запроса: ' . pg_last_error());
            $animals_out_in_otchet_period_to_new_owner = pg_fetch_all($result);
            pg_free_result($result);

            $n = 3;

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', "Информация по переданным животным по состоянию на " . $formatted_date_to);

            foreach ($animals_out_in_otchet_period_to_new_owner as $animal) {
                $objPHPExcel->setActiveSheetIndex(0)
//                    ->setCellValue('A' . $n, $row['area'] . ', ' . $row['address'])
                    ->setCellValue('A' . $n, $animal['full_address'])
                    ->setCellValue('B' . $n, $animal['fullname'])
                    ->setCellValue('C' . $n, date("d.m.Yг.", strtotime($animal['departure_date'])))
                    ->setCellValue('D' . $n, $animal['name']);

                //чип записываем в виде числа - без сокращения до +E
                $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit('E' . $n, $animal['identification_code'], \PHPExcel_Cell_DataType::TYPE_STRING);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                $n++;
            }
        }
        pg_free_result($result);
    }
    if ($report == 'mosvet_death_info') {

        // $period = $_GET["period"];
        $period = 2;

        $shelter = $_GET["shelter"];

        $query_orgs = '';
        if (!$area) {
            if (!$shelter) {
                //Проверяем есть ли у этой организации управляемые
                $m_orgs_array = getManagingOrgs($org_user_id);

                if (count($m_orgs_array) > 0) {
                    $query_orgs .= 'WHERE (';
                    for ($i = 0; $i <= count($m_orgs_array); $i++) {
                        if ($m_orgs_array[$i]) {
                            if ($i > 0) {
                                $query_orgs .= ' OR ';
                            }
                            $query_orgs .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                        }
                    }
                    $query_orgs .= ')';
                } else {
                    $query_orgs .= 'WHERE organizations.id=' . $org_user_id . ' ';
                }
                //Проверяем есть ли у этой организации управляемые
            } else {
                $query_orgs .= 'WHERE organizations.id=' . $shelter . ' ';
            }
        } else {
            $query_orgs .= 'WHERE organizations.id_area=' . $area . ' AND organizations.organization_type_const=\'shelter\' ';

            $m_orgs_array = getManagingOrgs($org_user_id);
            if (count($m_orgs_array) > 0) {
                $query_orgs_ .= 'AND (';
                for ($i = 0; $i <= count($m_orgs_array); $i++) {
                    if ($m_orgs_array[$i]) {
                        if ($i > 0) {
                            $query_orgs_ .= ' OR ';
                        }
                        $query_orgs_ .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                    }
                }
                $query_orgs_ .= ')';
            }
            if ($query_orgs_) {
                $query_orgs .= $query_orgs_;
            }
        }

        $query = 'SELECT organizations.id, organizations.short_name as short_name, organizations.name AS shelter, managing.name,areas.name AS area,fias_addresses.full_address AS address ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=organizations.managing_organization_id ';
        $query .= 'LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';
        $query .= $query_orgs;

        //кошки - 9
        //собаки - 25

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $count_row = 0;
        while ($row = pg_fetch_assoc($result)) {
            $count_row += 1;
            $currentDate = date('Y-m-d'); // Получаем текущую дату в формате 'год-месяц-день'
            // Недельный
            if ($period == '1') {
                // Находим номер дня недели
                $dayOfWeek = date('N', strtotime($currentDate));
                // Находим первое число текущей недели
                $firstDay = date('Y-m-d', strtotime($currentDate . ' - ' . ($dayOfWeek - 1) . ' days'));
                // Находим последнее число текущей недели
                $lastDay = date('Y-m-d', strtotime($currentDate . ' + ' . (7 - $dayOfWeek) . ' days'));
            }
            // Месячный
            if ($period == '2') {
                $firstDay = date('Y-m-01', strtotime($currentDate)); // Находим первое число текущего месяца
                $lastDay = date('Y-m-t', strtotime($currentDate)); // Находим последнее число текущего месяца

            }

            //пало животных за отчетный период
            //кошки
            //Всего

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date, shelter_guests.id_organization, shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'] . " AND shelter_guests.departure_date <= '" . $lastDay . "'";
            $query_count .= " OR shelter_guests.departure_date IS NULL ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.departure_reason IS NULL ";

            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id, shelter_guests.id,shelter_guests.departure_reason, shelter_guests.id_organization, shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $cats_all = pg_num_rows($result_count);
            pg_free_result($result_count);

            // труп направлен в ГВЛ на пат. вскрытие с последующей утилизацией
            $cats_gvl = 0;
            // труп направлен на утилизацию в стороннюю организацию, по оформленному ВСД ветврачом СББЖ АО
            $cats_util = 0;
            // № ВСД, оформленный ветврачом СББЖ АО
            $cats_vsd = 0;
            // Биологические отходы находятся на хранении
            $cats_othod = 0;

            // собак
            // Всего
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date, shelter_guests.id_organization, shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'] . " AND shelter_guests.departure_date <= '" . $lastDay . "'";
            $query_count .= " OR shelter_guests.departure_date IS NULL ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND shelter_guests.departure_reason IS NULL ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id, shelter_guests.id,shelter_guests.departure_reason, shelter_guests.id_organization, shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $dogs_all = pg_num_rows($result_count);
            pg_free_result($result_count);

            // труп направлен в ГВЛ на пат.вскрытие с последующей утилизацией (с указанием номера чипа)
            $dogs_gvl = 0;
            // труп направлен на утилизацию в стороннюю организацию, по оформленному ВСД ветврачом СББЖ АО (с указанием номера чипа)
            $dogs_util = 0;
            // № ВСД, оформленный ветврачом СББЖ АО
            $dogs_vsd = 0;
            // Биологические отходы находятся на хранении  (с указанием номера чипа)
            $dogs_othod = 0;

            $formatted_date_from = date("d.m.Yг.", strtotime($currentDate));

            $n = 6;

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', "Дополнительная информация по павшим животным в приюте за " . $formatted_date_from);

            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);

            $n++;
        }

        pg_free_result($result);

    } else if ($report == 'castrated_info') {//Информация по стерилизации животных за отчетную неделю/месяц
        $period = $_GET["period"];
        $fio_chif = $_GET['fio_chif'];
        $shelter = $_GET["shelter"];

        $formatted_date_to = date("d.m.Yг.", strtotime($date_to));
        $formatted_date_from = date("d.m.Yг.", strtotime($date_from));
        $formatted_date_current = date("d.m.Yг.", strtotime($currentDate));

        $firstDay = $date_from;
        $lastDay = $date_to;

        $query_orgs = '';
        if (!$area) {
            if (!$shelter) {
                //Проверяем есть ли у этой организации управляемые
                $m_orgs_array = getManagingOrgs($org_user_id);

                if (count($m_orgs_array) > 0) {
                    $query_orgs .= 'WHERE (';
                    for ($i = 0; $i <= count($m_orgs_array); $i++) {
                        if ($m_orgs_array[$i]) {
                            if ($i > 0) {
                                $query_orgs .= ' OR ';
                            }
                            $query_orgs .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                        }
                    }
                    $query_orgs .= ')';
                } else {
                    $query_orgs .= 'WHERE organizations.id=' . $org_user_id . ' ';
                }
                //Проверяем есть ли у этой организации управляемые
            } else {
                $query_orgs .= 'WHERE organizations.id=' . $shelter . ' ';
            }
        } else {
            $query_orgs .= 'WHERE organizations.id_area=' . $area . ' AND organizations.organization_type_const=\'shelter\' ';

            $m_orgs_array = getManagingOrgs($org_user_id);
            if (count($m_orgs_array) > 0) {
                $query_orgs_ .= 'AND (';
                for ($i = 0; $i <= count($m_orgs_array); $i++) {
                    if ($m_orgs_array[$i]) {
                        if ($i > 0) {
                            $query_orgs_ .= ' OR ';
                        }
                        $query_orgs_ .= 'organizations.id=' . $m_orgs_array[$i]['id'] . '';
                    }
                }
                $query_orgs_ .= ')';
            }
            if ($query_orgs_) {
                $query_orgs .= $query_orgs_;
            }
        }

        //кошки - 9
        //собаки - 25
        $query = 'SELECT organizations.id, organizations.short_name as short_name, ';
        $query .= 'organizations.name AS shelter, managing.name,areas.name AS area,fias_addresses.full_address AS address ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=organizations.managing_organization_id ';
        $query .= 'LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';
        $query .= $query_orgs;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $count_row = 0;
        while ($row = pg_fetch_assoc($result)) {

            $count_row += 1;
            $currentDate = date('Y-m-d'); // Получаем текущую дату в формате 'год-месяц-день'

            //кошек простерилизованно

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM  shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';

            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date <= '" . $lastDay . "' ";
            $query_count .= "OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.castrated = true AND pets.castrated_date IS NOT NULL ";
            $query_count .= "AND pets.castrated_date >= '" . $firstDay . "' AND pets.castrated_date <= '" . $lastDay . "' ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $cats_castrated = $res[0];
            pg_free_result($result_count);

            //собак простерилизованно

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM  shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';

            $query_count .= 'WHERE ';
            $query_count .= "shelter_guests.id_organization=" . $row['id'];
            $query_count .= " AND (shelter_guests.departure_date <= '" . $lastDay . "' ";
            $query_count .= "OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.castrated = true AND pets.castrated_date IS NOT NULL ";
            $query_count .= "AND pets.castrated_date >= '" . $firstDay . "' AND pets.castrated_date <= '" . $lastDay . "' ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";

            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $res = pg_fetch_row($result_count);
            $dogs_castrated = $res[0];
            pg_free_result($result_count);

            //

            $n = 4;

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', " Информация по движению животных в приюте (кошек, собак) " . "\n Период с " . $formatted_date_from . " по " . $formatted_date_to)
                ->setCellValue('A' . $n, ($n - 3))
                ->setCellValue('B' . $n, $row['area'])
                ->setCellValue('C' . $n, $row['short_name'])
                ->setCellValue('D' . $n, $row['address'])
                ->setCellValue('E' . $n, ($dogs_castrated + $cats_castrated))
                ->setCellValue('F' . $n, $dogs_castrated)
                ->setCellValue('G' . $n, $cats_castrated)
                ->setCellValue('A' . ($n + 3), 'Начальник отдела:')
                ->setCellValue('B' . ($n + 3), $fio_chif);

//
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);

            $objPHPExcel->getActiveSheet()->getColumnDimension("A1")->setWidth(300);
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(50);
            $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(50);
        }

        pg_free_result($result);

    } else if ($report == 'out_death_signature') {//Выбытие по причине смерти с описью
        $shelter = string_formating_for_sql($_GET["shelter"]);
        $death_reason = $_GET["death_reason"] ?? null;

        $n = 4;
        $formatted_date_from = date("d.m.Yг.", strtotime($date_from));
        $formatted_date_to = date("d.m.Yг.", strtotime($date_to));

        $currentDate = date('Y-m-d');

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color,';
        $query .= 'MAX(prv.valid_until) AS vac_date, ';
        $query .= 'chip.identification_code AS chip_title,  ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id,  ';
        $query .= 'shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name,  ';
        $query .= 'pets.name, pets.birthday, pets.sex , shelter_guests.death_reason_id, ';
        $query .= 'public.death_reason.name as death_reason ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date, shelter_guests.death_reason_id, ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization,  ';
        $query .= 'shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.id FROM shelter_guests) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1 ) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
        $query .= 'LEFT JOIN public.death_reason ON public.death_reason.id=shelter_guests.death_reason_id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
            $query1 = 'SELECT public.organizations.short_name FROM public.organizations  WHERE id =' . $org_user_id . ' ';
            $org_name = pg_query($query1) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($org_name);
            if ($row) {
                $org_name_value = $row[0];
            }
            pg_free_result($org_name);
        }

        $query .= "AND pets.name IS NOT NULL AND pets.name <> '' ";

        $query .= "AND shelter_guests.departure_date >= '" . $date_from . "' ";
        $query .= "AND shelter_guests.departure_reason = 'DEATH' ";
        if ($death_reason) {
            $query .= 'AND shelter_guests.death_reason_id =' . $death_reason;
        }

        //Проверяем есть ли у этой организации управляемые

        $query .= ' GROUP BY shelter_guests.id_pet, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist, ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name,  ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id,  ';
        $query .= 'aviary.title, species.name, pbr.name, characteristics, ';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.death_reason_id, public.death_reason.name ';
        $query .= "ORDER BY shelter_guests.departure_date ASC ";

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $result_sheet_2 = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());


        while ($row = pg_fetch_assoc($result)) {

            $sex = '';
            if ($row['sex'] == 'f') {
                $sex = 'женский';
            } else {
                $sex = 'мужской';
            }

            $yearOfBirth = date("Y", strtotime($row['birthday']));
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getNumberFormat()->setFormatCode('0');

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $n, $row['species_name'])
                ->setCellValue('B' . $n, $row['name'])
                ->setCellValue('C' . $n, $row['breed_name'])
                ->setCellValue('D' . $n, $sex)
                ->setCellValue('E' . $n, $yearOfBirth)
                ->setCellValue('F' . $n, $row['color']);

            //чип записываем в виде числа - без сокращения до +E
            $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $n++;
        }

        // --------------------------------------------- Опись
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'Опись животных к заявке №_ от ' . $formatted_date_from . '');

        // --------------------------------------------- Заявка
        $n = 7;
        $objPHPExcel->setActiveSheetIndex(1)->setCellValue('A1',
            $shelter ?? $org_name_value);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G1');

        $objPHPExcel->setActiveSheetIndex(1)->setCellValue('A2',
            ' Выбытие животных в период с ' . $formatted_date_from . '');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:G2');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:G2');

        while ($row = pg_fetch_assoc($result_sheet_2)) {

            $sex = '';
            if ($row['sex'] == 'f') {
                $sex = 'женский';
            } else {
                $sex = 'мужской';
            }

            $yearOfBirth = date("Y", strtotime($row['birthday']));
            $formatted_departure_date = date("d.m.Yг.", strtotime($row['departure_date']));
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getNumberFormat()->setFormatCode('0');


            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A' . $n, isset($row['departure_comment']) ? $row['departure_comment'] : ($n - 6))
                ->setCellValue('B' . $n, $row['species_name'])
                ->setCellValue('C' . $n, $row['name'])
                ->setCellValue('D' . $n, $row['breed_name'])
                ->setCellValue('E' . $n, $sex)
                ->setCellValue('F' . $n, $yearOfBirth)
                ->setCellValue('G' . $n, $row['aviary_title'])
                ->setCellValue('H' . $n, $row['color'])
                ->setCellValue('J' . $n, $formatted_departure_date)
                ->setCellValue('K' . $n, isset($row['death_reason']) ? $row['death_reason'] : ('-'));

            //чип записываем в виде числа - без сокращения до +Е
            $objPHPExcel->getActiveSheet()->getStyle('I' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('I' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);

            $n++;
        }
    } else if ($report == 'mosvet_week_report') {//Отчёт о животных, содержащихся в приютах

        // $period = $_GET["period"];
        $period = 1;
        $currentDate = date('Y-m-d'); // Получаем текущую дату в формате 'год-месяц-день'

        // Недельный
        if ($period == '1') {
            // Находим номер дня недели
            $dayOfWeek = date('N', strtotime($currentDate));
            // Находим первое число текущей недели
            $firstDay = date('Y-m-d', strtotime($currentDate . ' - ' . ($dayOfWeek - 1) . ' days'));
            // Находим последнее число текущей недели
            $lastDay = date('Y-m-d', strtotime($currentDate . ' + ' . (7 - $dayOfWeek) . ' days'));
        }
        // Месячный
        if ($period == '2') {
            $firstDay = date('Y-m-01', strtotime($currentDate)); // Находим первое число текущего месяца
            $lastDay = date('Y-m-t', strtotime($currentDate)); // Находим последнее число текущего месяца
        }

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color,';
        $query .= 'MAX(prv.valid_until) AS vac_date, ';
        $query .= 'chip.identification_code AS chip_title,  ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id,  ';
        $query .= 'shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name,  ';
        $query .= 'pets.name, pets.birthday, pets.sex ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.departure_specialist, ';
        $query .= 'shelter_guests.departure_reason, shelter_guests.socialized, shelter_guests.aviary_id, ';
        $query .= 'shelter_guests.id_organization, shelter_guests.status, shelter_guests.arrival_date, ';
        $query .= 'shelter_guests.departure_date, shelter_guests.id FROM shelter_guests) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ' . "\n";
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        //9-кошки. 25-собаки

        // отловленные собаки
        $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
        $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND (shelter_guests.arrival_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "')";

        $query .= 'GROUP BY shelter_guests.id_pet, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name,  ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id,  ';
        $query .= 'aviary.title, species.name, pbr.name, characteristics,';

        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date';

        $n = 3;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {


            $aviary_title = 'Нет данных';
            if ($row['aviary_title']) {
                $aviary_title = $row['aviary_title'];
            }
            $chief_name = 'Нет данных';
            if ($row['chief_name']) {
                $chief_name = $row['chief_name'];
            }
            $sex = '';
            if ($row['sex'] == 'f') {
                $sex = 'сука';
            } else {
                $sex = 'кобель';
            }

            $ku = '' . $row['id'] . '';
            if ($row['species_name'] == 'кошки') {
                $ku .= 'к';
            } else {
                $ku .= 'с';
            }
            $ku .= '' . $row['id_org'] . '';


            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $n, $n - 2)
                ->setCellValue('B' . $n, $row['species_name'])
                ->setCellValue('C' . $n, $row['name'])
                ->setCellValue('D' . $n, $row['breed_name'])
                ->setCellValue('E' . $n, $sex)
                ->setCellValue('F' . $n, date("m.Y", strtotime($row['birthday'])))
                ->setCellValue('G' . $n, $row['color'])
                ->setCellValue('H' . $n, $row['characteristics'])
                ->setCellValue('I' . $n, $row['chip_title'])
                ->setCellValue('J' . $n, date("d.m.Y", strtotime($row['arrival_date'])))
                ->setCellValue('K' . $n, $ku);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);
        }

        // отловленные кошки_______________________________

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color,';
        $query .= 'MAX(prv.valid_until) AS vac_date, ';
        $query .= 'chip.identification_code AS chip_title,  ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id, shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name, pets.name, pets.birthday, pets.sex ';

        // $query.='FROM shelter_guests ';
        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date,';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization,  ';
        $query .= 'shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date, ';
        $query .= 'shelter_guests.id FROM shelter_guests) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        //9-кошки. 25-собаки
        $query .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
        $query .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND (shelter_guests.arrival_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "')";

        $query .= 'GROUP BY shelter_guests.id_pet, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name,  ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, ';
        $query .= 'pets.id, aviary.title, species.name, pbr.name, characteristics,';

        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date';

        $m = 3;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {

            $aviary_title = 'Нет данных';
            if ($row['aviary_title']) {
                $aviary_title = $row['aviary_title'];
            }
            $chief_name = 'Нет данных';
            if ($row['chief_name']) {
                $chief_name = $row['chief_name'];
            }
            $sex = '';
            if ($row['sex'] == 'f') {
                $sex = 'сука';
            } else {
                $sex = 'кобель';
            }

            $ku = '' . $row['id'] . '';
            if ($row['species_name'] == 'кошки') {
                $ku .= 'к';
            } else {
                $ku .= 'с';
            }
            $ku .= '' . $row['id_org'] . '';

            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A' . $m, $m - 2)
                ->setCellValue('B' . $m, $row['species_name'])
                ->setCellValue('C' . $m, $row['name'])
                ->setCellValue('D' . $m, $row['breed_name'])
                ->setCellValue('E' . $m, $sex)
                ->setCellValue('F' . $m, date("m.Y", strtotime($row['birthday'])))
                ->setCellValue('G' . $m, $row['color'])
                ->setCellValue('H' . $m, $row['characteristics'])
                ->setCellValue('I' . $m, $row['chip_title'])
                ->setCellValue('J' . $m, date("d.m.Y", strtotime($row['arrival_date'])))
                ->setCellValue('K' . $m, $ku);

            //чип записываем в виде txt - без сокращения до +Е
            $objPHPExcel->getActiveSheet()->getStyle('I' . $m)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('I' . $m, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $m)->getAlignment()->applyFromArray($cell_style);

            $m++;
        }

        // павшие_______________________________

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color,';
        $query .= 'MAX(prv.valid_until) AS vac_date, ';
        $query .= 'chip.identification_code AS chip_title,  ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing,  ';
        $query .= 'pets.id AS pet_id, shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name,  ';
        $query .= 'pets.name, pets.birthday, pets.sex ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, ';
        $query .= 'shelter_guests.id_organization, shelter_guests.status, shelter_guests.arrival_date,  ';
        $query .= 'shelter_guests.departure_date, shelter_guests.id FROM shelter_guests) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ' . "\n";
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        $query .= "AND pets.name IS NOT NULL AND pets.name <> '' ";
        $query .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND (shelter_guests.departure_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "')";
        $query .= "AND (shelter_guests.departure_reason = 'DEATH' OR shelter_guests.departure_reason = 'EUTHANASIA') ";

        $query .= 'GROUP BY shelter_guests.id_pet, shelter_guests.departure_date, ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist, ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name, ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id, ';
        $query .= 'aviary.title, species.name, pbr.name, characteristics,';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date';

        $m = 4;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {

            $aviary_title = 'Нет данных';
            if ($row['aviary_title']) {
                $aviary_title = $row['aviary_title'];
            }
            $chief_name = 'Нет данных';
            if ($row['chief_name']) {
                $chief_name = $row['chief_name'];
            }
            $sex = '';
            if ($row['sex'] == 'f') {
                $sex = 'сука';
            } else {
                $sex = 'кобель';
            }

            $ku = '' . $row['id'] . '';
            if ($row['species_name'] == 'кошки') {
                $ku .= 'к';
            } else {
                $ku .= 'с';
            }
            $ku .= '' . $row['id_org'] . '';

            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A' . $m, $m - 3)
                ->setCellValue('B' . $m, $row['species_name'])
                ->setCellValue('C' . $m, $row['name'])
                ->setCellValue('D' . $m, $row['characteristics'])
                ->setCellValue('E' . $m, $row['breeds_name'])
                ->setCellValue('F' . $m, $sex)
                ->setCellValue('G' . $m, date("m.Y", strtotime($row['birthday'])))
                ->setCellValue('H' . $m, $row['color'])
                ->setCellValue('I' . $m, $row['chip_title'] ?? ' ')
                ->setCellValue('J' . $m, date("d.m.Y", strtotime($row['departure_date'])))
                ->setCellValue('K' . $m, $row['death_reason'] ?? ' ')
                ->setCellValue('L' . $m, date("d.m.Y", strtotime($row['arrival_date'])));

            //чип записываем в виде txt - без сокращения до +Е
            $objPHPExcel->getActiveSheet()->getStyle('I' . $m)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('I' . $m, $row['chip_title'] ?? ' ', \PHPExcel_Cell_DataType::TYPE_STRING);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("L" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("L" . $m)->getAlignment()->applyFromArray($cell_style);

            $m++;
        }

        // передача_______________________________

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color, ';
        $query .= 'MAX(prv.valid_until) AS vac_date, ';
        $query .= 'chip.identification_code AS chip_title, ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area, ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name, ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id, ';
        $query .= 'shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name, pets.name, pets.birthday, pets.sex ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date,';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason, shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization, shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date, shelter_guests.id FROM shelter_guests) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        $query .= "AND pets.name IS NOT NULL AND pets.name <> '' ";
        $query .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND (shelter_guests.departure_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "')";
        $query .= "AND shelter_guests.departure_reason LIKE '%RETURNED%' ";

        $query .= 'GROUP BY shelter_guests.id_pet, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name,  ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id, aviary.title,  ';
        $query .= 'species.name, pbr.name, characteristics, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date ';

        $m = 3;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {

            $aviary_title = 'Нет данных';
            if ($row['aviary_title']) {
                $aviary_title = $row['aviary_title'];
            }
            $chief_name = 'Нет данных';
            if ($row['chief_name']) {
                $chief_name = $row['chief_name'];
            }
            $sex = '';
            if ($row['sex'] == 'f') {
                $sex = 'сука';
            } else {
                $sex = 'кобель';
            }

            $ku = '' . $row['id'] . '';
            if ($row['species_name'] == 'кошки') {
                $ku .= 'к';
            } else {
                $ku .= 'с';
            }
            $ku .= '' . $row['id_org'] . '';

            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A' . $m, $m - 3)
                ->setCellValue('B' . $m, date("d.m.Y", strtotime($row['departure_date'])))
                ->setCellValue('C' . $m, $row['species_name'] != 'кошки' ? '+' : '')
                ->setCellValue('D' . $m, $row['species_name'] == 'кошки' ? '+' : '')
                ->setCellValue('E' . $m, $row['name'])
                ->setCellValue('F' . $m, $row['species_name'])
                ->setCellValue('G' . $m, $row['species_name'] != 'кошки' ? '+' : '')
                ->setCellValue('H' . $m, $row['species_name'] == 'кошки' ? '+' : '')
                ->setCellValue('I' . $m, isset($row['Ф.И.О. нового владельца']) ? $row['Ф.И.О. нового владельца'] : ' ')
                ->setCellValue('J' . $m, $row['characteristics'])
                ->setCellValue('K' . $m, $ku);

            $objPHPExcel->getActiveSheet()->getStyle("A" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $m)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $m)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $m)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("K" . $m)->getAlignment()->applyFromArray($cell_style);

            $m++;
        }
    } else if ($report == 'fauna_monitoring_report') {
        $shelter = $_GET["shelter"];

        $query = 'SELECT DISTINCT ON (areas.name) organizations.id, organizations.id_area, ';
        $query .= 'organizations.short_name as short_name, organizations.name AS shelter, areas.name AS area ';
        $query .= 'FROM organizations ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=organizations.id_area ';
        $query .= 'ORDER BY areas.name, organizations.id_area';

        $n = 8;

        //кошки - 9
        //собаки - 25

        $currentDate = date('Y-m-d'); // Получаем текущую дату в формате 'год-месяц-день'

        // Недельный

        // Находим номер дня недели
        $dayOfWeek = date('N', strtotime($currentDate));
        // Находим первое число текущей недели
        $firstDay = date('Y-m-d', strtotime($currentDate . ' - ' . ($dayOfWeek - 1) . ' days'));
        // Находим последнее число текущей недели
        $lastDay = date('Y-m-d', strtotime($currentDate . ' + ' . (7 - $dayOfWeek) . ' days'));

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $count_row = 0;
        $catch_dogs_all = 0;
        $catch_cats_all = 0;
        $vozvrat_dogs_all = 0;
        $vozvrat_cats_all = 0;
        $after_castr_dogs_all = 0;
        $after_castr_cats_all = 0;

        while ($row = pg_fetch_assoc($result)) {
            $count_row += 1;
            $id_area = $row['id_area'] ?? 'NULL';

            $k1_1 = 0;
            $k1_2 = 0;
            $k2_1 = 0;
            $k2_2 = 0;
            $k3_1 = 0;
            $k3_2 = 0;

            //Количество животных на начало отчетного периода, ед.
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, shelter_guests.arrival_reason, shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date, shelter_guests.id_organization, shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';

            $query_count .= 'WHERE ';
            $query_count .= "organizations.id_area=" . $id_area . " AND shelter_guests.arrival_date <= '" . $firstDay . "' AND ";
            $query_count .= "(shelter_guests.departure_date <= '" . $lastDay . "' OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= " AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= "AND shelter_guests.arrival_reason = 'CATCH' ";

            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id,  ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';
            //shelter_guests.status = 'DEACTIVATED' AND shelter_guests.status != 'DEPARTURED'";
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $k1_1 = pg_num_rows($result_count);
            pg_free_result($result_count);
            $catch_cats_all += $k1_1;

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
            $query_count .= 'shelter_guests.arrival_reason, shelter_guests.status, shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization, shelter_guests.departure_reason,  ';
            $query_count .= 'shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';

            $query_count .= 'WHERE ';
            $query_count .= "organizations.id_area=" . $id_area . " AND shelter_guests.arrival_date <= '" . $firstDay . "' AND ";
            $query_count .= "(shelter_guests.departure_date <= '" . $lastDay . "' OR shelter_guests.departure_date IS NULL) ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= "AND shelter_guests.arrival_reason = 'CATCH' ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id, shelter_guests.id, ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id_organization, ';
            $query_count .= 'shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';
            //shelter_guests.status != 'DEACTIVATED' AND shelter_guests.status != 'DEPARTURED'";
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $k1_2 = pg_num_rows($result_count);
            pg_free_result($result_count);
            $catch_dogs_all += $k1_2;
            //

            //Кол-во животных, поступивших в приют

            // возвращенные (подкидыши)
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
            $query_count .= 'shelter_guests.arrival_reason, shelter_guests.status, shelter_guests.arrival_date, ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization, ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';
            $query_count .= 'WHERE ';
            $query_count .= "organizations.id_area=" . $id_area . " AND (shelter_guests.arrival_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "') ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= "AND shelter_guests.arrival_reason = 'OWNER_REFUSAL' ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id, ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason, shelter_guests.id_organization, ';
            $query_count .= 'shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';
            //shelter_guests.status != 'DEACTIVATED'";
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $k2_1 = pg_num_rows($result_count);
            pg_free_result($result_count);
            $vozvrat_cats_all += $k2_1;

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet,  ';
            $query_count .= 'shelter_guests.arrival_reason, shelter_guests.status, shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';

            $query_count .= 'WHERE ';
            $query_count .= "organizations.id_area=" . $id_area . " AND (shelter_guests.arrival_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "') ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= "AND shelter_guests.arrival_reason = 'OWNER_REFUSAL' ";
            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id,  ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.status,shelter_guests.arrival_date, shelter_guests.departure_date';
            //shelter_guests.status != 'DEACTIVATED'";
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            // $k2_2=pg_fetch_result($result_count, 0);
            $k2_2 = pg_num_rows($result_count);
            pg_free_result($result_count);
            $vozvrat_dogs_all += $k2_2;
            //

            //доставленные после стерилизации
            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet,  ';
            $query_count .= 'shelter_guests.status, shelter_guests.arrival_date, ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';
            $query_count .= 'WHERE ';
            $query_count .= "organizations.id_area=" . $id_area . " AND (shelter_guests.departure_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "') ";
            $query_count .= "AND pets.castrated_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "' ";
            $query_count .= "AND pets.id_species=9 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
            $query_count .= 'GROUP BY  ';
            $query_count .= 'shelter_guests.id_pet, pets.id, pets.castrated_date,  ';
            $query_count .= 'shelter_guests.id,shelter_guests.departure_reason, ';
            $query_count .= 'shelter_guests.id_organization, shelter_guests.status,shelter_guests.arrival_date,  ';
            $query_count .= 'shelter_guests.departure_date';
            //shelter_guests.status != 'DEACTIVATED'";
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $k3_1 = pg_num_rows($result_count);
            pg_free_result($result_count);
            $after_castr_cats_all += $k3_1;

            $query_count = 'SELECT COUNT(shelter_guests.id_pet) ';
            $query_count .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet,  ';
            $query_count .= 'shelter_guests.status, shelter_guests.arrival_date, ';
            $query_count .= 'shelter_guests.departure_date, shelter_guests.id_organization,  ';
            $query_count .= 'shelter_guests.departure_reason, shelter_guests.id FROM shelter_guests) AS shelter_guests ';
            $query_count .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
            $query_count .= 'LEFT JOIN public.organizations ON organizations.id=shelter_guests.id_organization ';

            $query_count .= 'WHERE ';
            $query_count .= "organizations.id_area=" . $id_area . " AND (shelter_guests.departure_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "') ";
            $query_count .= "AND pets.castrated_date BETWEEN '" . $firstDay . "' AND '" . $lastDay . "' ";
            $query_count .= "AND pets.id_species=25 AND (pets.name IS NOT NULL AND pets.name <> '') ";
            $query_count .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
            $query_count .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";

            $query_count .= 'GROUP BY shelter_guests.id_pet, pets.id,  ';
            $query_count .= 'pets.castrated_date, shelter_guests.id,shelter_guests.departure_reason, ';
            $query_count .= 'shelter_guests.id_organization, shelter_guests.status, ';
            $query_count .= 'shelter_guests.arrival_date, shelter_guests.departure_date';
            //shelter_guests.status != 'DEACTIVATED'";
            $result_count = pg_query($query_count) or die('Ошибка запроса: ' . pg_last_error());
            $k3_2 = pg_num_rows($result_count);
            pg_free_result($result_count);
            $after_castr_dogs_all += $k3_2;

            $formatted_date_from = date("d.m.Yг.", strtotime($firstDay));
            $formatted_date_to = date("d.m.Yг.", strtotime($lastDay));

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', "Справка по мониторингу городской фауны за отчетную неделю с  " . $formatted_date_from . ' по ' . $formatted_date_to)
                ->setCellValue('A' . $n, $count_row)
                ->setCellValue('B' . $n, $row['area'])
                ->setCellValue('C' . $n, $k1_1)
                ->setCellValue('D' . $n, $k1_2)
                ->setCellValue('E' . $n, $k2_1)
                ->setCellValue('F' . $n, $k2_2)
                ->setCellValue('G' . $n, $k3_1)
                ->setCellValue('H' . $n, $k3_2)
                ->setCellValue('I' . $n, isset($row['description']) ? $row['description'] : '')
                ->setCellValue('J' . $n, isset($row['description']) ? $row['description'] : '');


            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
            $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);

            $n++;
        }
        //Итого:
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($n) . ':' . 'B' . ($n));
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($n), 'Итого: ');
        $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($n), $catch_cats_all);
        $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($n), $catch_dogs_all);
        $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($n), $vozvrat_cats_all);
        $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($n), $vozvrat_dogs_all);
        $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($n), $after_castr_cats_all);
        $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($n), $after_castr_dogs_all);
        $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);

        $objPHPExcel->getActiveSheet()->mergeCells('I' . ($n) . ':' . 'J' . ($n));
        $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
        $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);

        pg_free_result($result);
    } else if ($report == 'act_beshenstvo') {

        $temp_date = '«' . Date('d') . '» ' . get_month(Date('n')) . ' ' . Date('Y');
        $day = ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"];
        $dayOfWeek = date('N');

        $formattedDate = $day[$dayOfWeek] . ', ' . Date('d') . ' ' . get_month(Date('n')) . ', ' . Date('Y');

        $date_to = $_GET['date_to'] . ' 00:00:00';
        $date_from = $_GET['date_from'] . ' 00:00:00';
        $vac_specialist = $_GET['vac_specialist'];
        $vac_specialist2 = $_GET['vac_specialist2'];

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color, pets.description,  ';
        $query .= 'pets.name as pet_name, pets.id_species as id_species, pet_owners.fullname, ';
        $query .= 'MAX(prv.valid_until) AS vac_date, public.visits.fact_start_dttm, ';
        $query .= 'chip.identification_code AS chip_title,  ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area, shelters.short_name AS shelter_title,  ';
        $query .= 'shelters.chief_name AS chief_name, pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id, shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name, pets.name, pets.birthday, pets.sex, ';
        $query .= 'public.visit_service_vaccination.batch as batch1,  ';
        $query .= 'public.visit_service_vaccination.valid_until as valid_until1, ';
        $query .= 'public.visit_service_tmc.count,  public.visit_service_tmc.count_utilize, ';
        $query .= 'prv.drug_name,  prv.producer_name, prv.batch, prv.valid_until,   ';
        $query .= 'tmc.tmc.name, tmc.tmc.produced ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date,';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason, ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization,  ';
        $query .= 'shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.id FROM shelter_guests LIMIT 1) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1 ) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
        $query .= 'LEFT JOIN pets_to_owner ON pets.id=pets_to_owner.id_pet ';
        $query .= 'LEFT JOIN pet_owners ON pets_to_owner.id_owner=pet_owners.id ';
        $query .= 'LEFT JOIN public.visit_pets ON public.visit_pets.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.visits_gov_services ON public.visit_pets.id_visit=public.visits_gov_services.id_visit ';
        $query .= 'LEFT JOIN public.gov_services ON public.gov_services.id=public.visits_gov_services.id_service ';
        $query .= 'LEFT JOIN public.visits ON public.visit_pets.id_visit=public.visits.id ';
        $query .= 'LEFT JOIN public.visit_service_tmc ON public.visit_service_tmc.id_visit=public.visits.id ';
        $query .= 'LEFT JOIN public.visit_service_vaccination ON public.visit_service_tmc.id=public.visit_service_vaccination.id_visit_service_tmc ';
        $query .= 'LEFT JOIN tmc.tmc ON tmc.id=public.visit_service_tmc.id_tmc AND public.visit_service_tmc.type_tmc = tmc.type ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        $query .= "AND id_species IN (9, 25) AND (pets.name IS NOT NULL AND pets.name <> '') ";
        $query .= "AND (shelter_guests.status = 'IN_SHELTER' ";
        $query .= "OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND public.gov_services.name LIKE '%Вакцинация животных с проведением клинического осмотра, идентификации, консультации, инъекции%'";
        $query .= "AND (public.visits.fact_start_dttm BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";

        $query .= 'GROUP BY ';
        $query .= 'public.visits.fact_start_dttm, ';
        $query .= 'pet_owners.fullname, id_species,  ';
        $query .= 'public.visit_service_vaccination.batch ,valid_until1, ';
        $query .= 'public.visit_service_tmc.count, ';
        $query .= 'prv.drug_name,  prv.producer_name, prv.batch, prv.valid_until,   ';
        $query .= 'tmc.tmc.name, tmc.tmc.produced, ';
        $query .= 'public.visit_service_tmc.count_utilize, ';
        $query .= 'shelter_guests.id_pet, shelter_guests.departure_date, ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status,  ';
        $query .= 'shelter_guests.id_organization, shelter_guests.aviary_id,  ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.socialized,  ';
        $query .= 'shelter_guests.departure_reason, shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, ';
        $query .= 'pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, ';
        $query .= 'shelters.chief_name, shelter_guests.id, shelters.short_name, ';
        $query .= 'managing.short_name, ';
        $query .= 'pets.id, aviary.title, ';
        $query .= 'species.name, pets.name, pets.description, ';
        $query .= 'pbr.name, ';
        $query .= 'characteristics ';

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $pets_data = array(); // Создаем пустой массив для хранения данных
        $dogs_array = array();
        $cats_array = array();
        $fioArray = array();
        $vac_producerArray = array();
        $vac_serial_numberArray = array();
        $vac_expire_dateArray = array();
        $vac_nameArray = array();
        $vac_dozaArray = array();
        $vac_mlArray = array();
        $spirt_mlArray = array();
        $shpriz2Array = array();
        $shpriz5Array = array();
        $salfetkaArray = array();
        $perchatkaArray = array();
        $zapis_otArray = array();
        $zapis_toArray = array();
        $flacon_countArray = array();
        $ostatok_vac_doza_countArray = array();
        $ostatok_value_mlArray = array();
        $igla_countArray = array();

        while ($inner_row = pg_fetch_assoc($result)) {
            $pets_data[] = $inner_row; // Добавляем данные в массив
        }

        foreach ($pets_data as $subArray) {
            $fioArray[] = $subArray['fullname'];
            $vac_producerArray[] = $subArray['produced'];
            $vac_serial_numberArray[] = $subArray['batch1'] ?? $subArray['batch'] ?? ' ';;
            $vac_expire_dateArray[] = $newDate = date("d.m.Y", strtotime($subArray['valid_until1'])) ?? date("d.m.Y", strtotime($subArray['valid_until'])) ?? ' ';;
            $vac_nameArray[] = $subArray['name'] ?? ' ';
            $vac_dozaArray[] = $subArray['count'] ?? ' ';
            $vac_mlArray[] = $subArray['count'] ?? ' ';
            $spirt_mlArray[] = $subArray['count'] ?? ' ';
            $shpriz2Array[] = $subArray['count'] ?? ' ';
            $shpriz5Array[] = $subArray['count'] ?? ' ';
            $salfetkaArray[] = $subArray['count'] ?? ' ';
            $perchatkaArray[] = $subArray['count'] ?? ' ';
            $zapis_otArray[] = $subArray['start_num'] ?? ' ';
            $zapis_toArray[] = $subArray['finish_num'] ?? ' ';
            $flacon_countArray[] = $subArray['count'] ?? ' ';
            $ostatok_vac_doza_countArray[] = $subArray['count_utilize'] ?? ' ';
            $ostatok_value_mlArray[] = $subArray['count_utilize'] ?? ' ';
            $igla_countArray[] = $subArray['count_utilize'] ?? ' ';
            if ($subArray['id_species'] == 25) {
                $dogs_array[] = $subArray; // Добавляем подмассив в первую группу, если id_species равен 25
            } elseif ($subArray['id_species'] == 9) {
                $cats_array[] = $subArray; // Добавляем подмассив во вторую группу, если id_species равен 9
            }
        }

        pg_free_result($result);

        $code = 'act_beshenstvo';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $data = [
            'managing_name' => count($m_orgs_array) > 0 ? implode(", ", $m_orgs_array) : '',
            'shelter_name' => isset($pets_data[0]['shelter_title']) ? $pets_data[0]['shelter_title'] : ' ',
            'pets_count' => count($pets_data),
            'cats_count' => count($cats_array),
            'dogs_count' => count($dogs_array),
            'vac_specialist' => $vac_specialist,
            'vac_specialist2' => $vac_specialist2,
            'fio_arr' => implode(", ", $fioArray),
            'current_date' => $formattedDate,
            'temp_date' => $temp_date,
            'act_number' => 1,
            'vac_specialist_post' => 'Ветеринарный врач/Специалист',
            'vac_specialist2_post' => 'Ветеринарный врач/Специалист',
            'vac_producer' => implode(", ", $vac_producerArray),
            'vac_serial_number' => implode(", ", $vac_serial_numberArray),
            'vac_expire_date' => implode(", ", $vac_expire_dateArray),
            'vac_name' => implode(", ", $vac_nameArray),
            'vac_doza' => implode(", ", $vac_dozaArray),
            'vac_ml' => implode(", ", $vac_mlArray),
            'spirt_ml' => implode(", ", $spirt_mlArray),
            'shpriz2' => implode(", ", $shpriz2Array),
            'shpriz5' => implode(", ", $shpriz5Array),
            'salfetka' => implode(", ", $salfetkaArray),
            'perchatka' => implode(", ", $perchatkaArray),
            'zapis_ot' => implode(", ", $zapis_otArray),
            'zapis_to' => implode(", ", $zapis_toArray),
            'flacon_count' => implode(", ", $flacon_countArray),
            'ostatok_vac_doza_count' => implode(", ", $ostatok_vac_doza_countArray),
            'ostatok_value_ml' => implode(", ", $ostatok_value_mlArray),
            'igla_count' => implode(", ", $igla_countArray),
        ];
        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $document->setValue("health_table", vaccination_pet_table($pets_data));
        $filename = 'Акт Бешенство.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');

    } else if ($report == 'act_degelmintizacii') {

        $temp_date = '«' . Date('d') . '» ' . get_month(Date('n')) . ' ' . Date('Y');
        $day = ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"];
        $dayOfWeek = date('N');

        $formattedDate = $day[$dayOfWeek] . ', ' . Date('d') . ' ' . get_month(Date('n')) . ', ' . Date('Y');
        $simpleDate = date('d.m.Y');

        $date_to = $_GET['date_to'] . ' 00:00:00';
        $date_from = $_GET['date_from'] . ' 00:00:00';
        $vac_specialist = $_GET['vac_specialist'];
        $vac_specialist2 = $_GET['vac_specialist2'];

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color, pets.description, pets.name as pet_name,  ';
        $query .= 'pets.id_species as id_species, pet_owners.fullname, ';
        $query .= 'MAX(prv.valid_until) AS vac_date, public.visits.fact_start_dttm, ';
        $query .= 'chip.identification_code AS chip_title, users.fullname AS departure_specialist_fio, ';
        $query .= 'pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id,  ';
        $query .= 'shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name,  ';
        $query .= 'pets.name, pets.birthday, pets.sex, ';
        $query .= 'public.visit_service_vaccination.batch as batch1,  ';
        $query .= 'public.visit_service_vaccination.valid_until as valid_until1, ';
        $query .= 'public.visit_service_tmc.count,  public.visit_service_tmc.count_utilize, ';
        $query .= 'prv.drug_name,  prv.producer_name, prv.batch, prv.valid_until,   ';
        $query .= 'tmc.tmc.name, tmc.tmc.produced ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason,';
        $query .= 'shelter_guests.arrival_act_number,';
        $query .= 'shelter_guests.arrival_act_number_date,';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization,  ';
        $query .= 'shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.id FROM shelter_guests LIMIT 1) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id  ';
        $query .= 'AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet  ';
        $query .= 'AND c1.id_ident_type=1 ) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
        $query .= 'LEFT JOIN pets_to_owner ON pets.id=pets_to_owner.id_pet ';
        $query .= 'LEFT JOIN pet_owners ON pets_to_owner.id_owner=pet_owners.id ';
        $query .= 'LEFT JOIN public.visit_pets ON public.visit_pets.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.visits_gov_services ON public.visit_pets.id_visit=public.visits_gov_services.id_visit ';
        $query .= 'LEFT JOIN public.gov_services ON public.gov_services.id=public.visits_gov_services.id_service ';
        $query .= 'LEFT JOIN public.visits ON public.visit_pets.id_visit=public.visits.id ';
        $query .= 'LEFT JOIN public.visit_service_tmc ON public.visit_service_tmc.id_visit=public.visits.id ';
        $query .= 'LEFT JOIN public.visit_service_vaccination ON public.visit_service_tmc.id=public.visit_service_vaccination.id_visit_service_tmc ';
        $query .= 'LEFT JOIN tmc.tmc ON tmc.id=public.visit_service_tmc.id_tmc AND public.visit_service_tmc.type_tmc = tmc.type ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        $query .= "AND id_species IN (9, 25) AND (pets.name IS NOT NULL AND pets.name <> '') ";
        $query .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND public.gov_services.name = 'Вакцинация животных с проведением клинического осмотра, идентификации, консультации, инъекции (без стоимости вакцины)'";
        $query .= "AND (public.visits.fact_start_dttm BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";

        $query .= 'GROUP BY ';
        $query .= 'public.visits.fact_start_dttm, ';
        $query .= 'pet_owners.fullname, id_species,  ';
        $query .= 'public.visit_service_vaccination.batch ,valid_until1, ';
        $query .= 'public.visit_service_tmc.count, ';
        $query .= 'prv.drug_name,  prv.producer_name, prv.batch, prv.valid_until,   ';
        $query .= 'tmc.tmc.name, tmc.tmc.produced, ';
        $query .= 'public.visit_service_tmc.count_utilize, ';
        $query .= 'shelter_guests.id_pet, shelter_guests.departure_date, ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status,  ';
        $query .= 'shelter_guests.id_organization, shelter_guests.aviary_id,  ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.socialized,  ';
        $query .= 'shelter_guests.departure_reason, shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, ';
        $query .= 'pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, ';
        $query .= 'shelters.chief_name, shelter_guests.id, shelters.short_name, ';
        $query .= 'managing.short_name, ';
        $query .= 'pets.id, aviary.title, ';
        $query .= 'species.name, pets.name, pets.description, ';
        $query .= 'pbr.name, ';
        $query .= 'characteristics ';

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $pets_data = array(); // Создаем пустой массив для хранения данных
        $dogs_array = array();
        $cats_array = array();
        $fioArray = array();
        $vac_producerArray = array();
        $vac_serial_numberArray = array();
        $vac_expire_dateArray = array();
        $vac_nameArray = array();
        $vac_dozaArray = array();
        $vac_mlArray = array();
        $spirt_mlArray = array();
        $shpriz2Array = array();
        $shpriz5Array = array();
        $salfetkaArray = array();
        $perchatkaArray = array();
        $zapis_otArray = array();
        $zapis_toArray = array();
        $flacon_countArray = array();
        $ostatok_vac_doza_countArray = array();
        $ostatok_value_mlArray = array();
        $igla_countArray = array();

        while ($inner_row = pg_fetch_assoc($result)) {
            $pets_data[] = $inner_row; // Добавляем данные в массив
        }

        foreach ($pets_data as $subArray) {
            $fioArray[] = $subArray['fullname'];
            $vac_producerArray[] = $subArray['produced'];
            $vac_serial_numberArray[] = $subArray['batch1'] ?? $subArray['batch'] ?? ' ';;
            $vac_expire_dateArray[] = $newDate = date("d.m.Y", strtotime($subArray['valid_until1'])) ?? date("d.m.Y", strtotime($subArray['valid_until'])) ?? ' ';;
            $vac_nameArray[] = $subArray['name'] ?? ' ';
            $vac_dozaArray[] = $subArray['count'] ?? ' ';
            $vac_mlArray[] = $subArray['count'] ?? ' ';
            $spirt_mlArray[] = $subArray['count'] ?? ' ';
            $shpriz2Array[] = $subArray['count'] ?? ' ';
            $shpriz5Array[] = $subArray['count'] ?? ' ';
            $salfetkaArray[] = $subArray['count'] ?? ' ';
            $perchatkaArray[] = $subArray['count'] ?? ' ';
            $zapis_otArray[] = $subArray['start_num'] ?? ' ';
            $zapis_toArray[] = $subArray['finish_num'] ?? ' ';
            $flacon_countArray[] = $subArray['count'] ?? ' ';
            $ostatok_vac_doza_countArray[] = $subArray['count_utilize'] ?? ' ';
            $ostatok_value_mlArray[] = $subArray['count_utilize'] ?? ' ';
            $igla_countArray[] = $subArray['count_utilize'] ?? ' ';
            if ($subArray['id_species'] == 25) {
                $dogs_array[] = $subArray; // Добавляем подмассив в первую группу, если id_species равен 25
            } elseif ($subArray['id_species'] == 9) {
                $cats_array[] = $subArray; // Добавляем подмассив во вторую группу, если id_species равен 9
            }
        }

        pg_free_result($result);

        $code = 'act_degelmintizacii';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $data = [
            'managing_name' => count($m_orgs_array) > 0 ? implode(", ", $m_orgs_array) : '',
            'shelter_name' => isset($pets_data[0]['shelter_title']) ? $pets_data[0]['shelter_title'] : ' ',
            'pets_count' => count($pets_data),
            'cats_count' => count($cats_array),
            'dogs_count' => count($dogs_array),
            'vac_specialist' => $vac_specialist,
            'vac_specialist2' => $vac_specialist2,
            'fio_arr' => implode(", ", $fioArray),
            'current_date' => $formattedDate,
            'temp_date' => $temp_date,
            'simpleDate' => $simpleDate,
            'act_number' => 1,
            'vac_specialist_post' => 'Ветеринарный врач/Специалист',
            'vac_specialist2_post' => 'Ветеринарный врач/Специалист',
            'vac_producer' => implode(", ", $vac_producerArray),
            'vac_serial_number' => implode(", ", $vac_serial_numberArray),
            'vac_expire_date' => implode(", ", $vac_expire_dateArray),
            'vac_name' => implode(", ", $vac_nameArray),
            'vac_doza' => implode(", ", $vac_dozaArray),
            'vac_ml' => implode(", ", $vac_mlArray),
            'spirt_ml' => implode(", ", $spirt_mlArray),
            'shpriz2' => implode(", ", $shpriz2Array),
            'shpriz5' => implode(", ", $shpriz5Array),
            'salfetka' => implode(", ", $salfetkaArray),
            'perchatka' => implode(", ", $perchatkaArray),
            'zapis_ot' => implode(", ", $zapis_otArray),
            'zapis_to' => implode(", ", $zapis_toArray),
            'flacon_count' => implode(", ", $flacon_countArray),
            'ostatok_vac_doza_count' => implode(", ", $ostatok_vac_doza_countArray),
            'ostatok_value_ml' => implode(", ", $ostatok_value_mlArray),
            'igla_count' => implode(", ", $igla_countArray),
        ];
        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $document->setValue("health_table", vaccination_pet_table($pets_data));
        $filename = 'Акт Дегельминтизации.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');

    } else if ($report == 'act_ektoparazit') {

        $temp_date = '«' . Date('d') . '» ' . get_month(Date('n')) . ' ' . Date('Y');
        $day = ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"];
        $dayOfWeek = date('N');

        $formattedDate = $day[$dayOfWeek] . ', ' . Date('d') . ' ' . get_month(Date('n')) . ', ' . Date('Y');
        $simpleDate = date('d.m.Y');

        $date_to = $_GET['date_to'] . ' 00:00:00';
        $date_from = $_GET['date_from'] . ' 00:00:00';
        $vac_specialist = $_GET['vac_specialist'];
        $vac_specialist2 = $_GET['vac_specialist2'];

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color, pets.description, pets.name as pet_name,  ';
        $query .= 'pets.id_species as id_species, pet_owners.fullname, ';
        $query .= 'MAX(prv.valid_until) AS vac_date, public.visits.fact_start_dttm, ';
        $query .= 'chip.identification_code AS chip_title,  ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing,  ';
        $query .= 'pets.id AS pet_id, shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name,  ';
        $query .= 'pets.name, pets.birthday, pets.sex, ';
        $query .= 'public.visit_service_vaccination.batch as batch1,  ';
        $query .= 'public.visit_service_vaccination.valid_until as valid_until1, ';
        $query .= 'public.visit_service_tmc.count,  public.visit_service_tmc.count_utilize, ';
        $query .= 'prv.drug_name,  prv.producer_name, prv.batch, prv.valid_until,   ';
        $query .= 'tmc.tmc.name, tmc.tmc.produced ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization,  ';
        $query .= 'shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.id FROM shelter_guests LIMIT 1) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1 ) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
        $query .= 'LEFT JOIN pets_to_owner ON pets.id=pets_to_owner.id_pet ';
        $query .= 'LEFT JOIN pet_owners ON pets_to_owner.id_owner=pet_owners.id ';
        $query .= 'LEFT JOIN public.visit_pets ON public.visit_pets.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.visits_gov_services ON public.visit_pets.id_visit=public.visits_gov_services.id_visit ';
        $query .= 'LEFT JOIN public.gov_services ON public.gov_services.id=public.visits_gov_services.id_service ';
        $query .= 'LEFT JOIN public.visits ON public.visit_pets.id_visit=public.visits.id ';
        $query .= 'LEFT JOIN public.visit_service_tmc ON public.visit_service_tmc.id_visit=public.visits.id ';
        $query .= 'LEFT JOIN public.visit_service_vaccination ON public.visit_service_tmc.id=public.visit_service_vaccination.id_visit_service_tmc ';
        $query .= 'LEFT JOIN tmc.tmc ON tmc.id=public.visit_service_tmc.id_tmc AND public.visit_service_tmc.type_tmc = tmc.type ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        $query .= "AND id_species IN (9, 25) AND (pets.name IS NOT NULL AND pets.name <> '') ";
        $query .= "AND (shelter_guests.status = 'IN_SHELTER' OR shelter_guests.status = 'QUARANTINE' OR shelter_guests.status = 'QUARANTINE_OTHER' ";
        $query .= "OR shelter_guests.status = 'IN_ISOLATION' OR shelter_guests.status = 'IN_HOSPITAL') ";
        $query .= "AND public.gov_services.name LIKE '%Обработка против эктопаразитов%'";
        $query .= "AND (public.visits.fact_start_dttm BETWEEN '" . $date_from . "' AND '" . $date_to . "') ";

        $query .= 'GROUP BY ';
        $query .= 'public.visits.fact_start_dttm, ';
        $query .= 'pet_owners.fullname, id_species,  ';
        $query .= 'public.visit_service_vaccination.batch ,valid_until1, ';
        $query .= 'public.visit_service_tmc.count, ';
        $query .= 'prv.drug_name,  prv.producer_name, prv.batch, prv.valid_until,   ';
        $query .= 'tmc.tmc.name, tmc.tmc.produced, ';
        $query .= 'public.visit_service_tmc.count_utilize, ';
        $query .= 'shelter_guests.id_pet, shelter_guests.departure_date, ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status,  ';
        $query .= 'shelter_guests.id_organization, shelter_guests.aviary_id,  ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.socialized,  ';
        $query .= 'shelter_guests.departure_reason, shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, ';
        $query .= 'pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, ';
        $query .= 'shelters.chief_name, shelter_guests.id, shelters.short_name, ';
        $query .= 'managing.short_name, ';
        $query .= 'pets.id, aviary.title, ';
        $query .= 'species.name, pets.name, pets.description, ';
        $query .= 'pbr.name, ';
        $query .= 'characteristics ';

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        $pets_data = array(); // Создаем пустой массив для хранения данных
        $dogs_array = array();
        $cats_array = array();
        $fioArray = array();
        $vac_producerArray = array();
        $vac_serial_numberArray = array();
        $vac_expire_dateArray = array();
        $vac_nameArray = array();
        $vac_dozaArray = array();
        $vac_mlArray = array();
        $spirt_mlArray = array();
        $shpriz2Array = array();
        $shpriz5Array = array();
        $salfetkaArray = array();
        $perchatkaArray = array();
        $zapis_otArray = array();
        $zapis_toArray = array();
        $flacon_countArray = array();
        $ostatok_vac_doza_countArray = array();
        $ostatok_value_mlArray = array();
        $igla_countArray = array();

        while ($inner_row = pg_fetch_assoc($result)) {
            $pets_data[] = $inner_row; // Добавляем данные в массив
        }

        foreach ($pets_data as $subArray) {
            $fioArray[] = $subArray['fullname'];
            $vac_producerArray[] = $subArray['produced'];
            $vac_serial_numberArray[] = $subArray['batch1'] ?? $subArray['batch'] ?? ' ';;
            $vac_expire_dateArray[] = $newDate = date("d.m.Y", strtotime($subArray['valid_until1'])) ?? date("d.m.Y", strtotime($subArray['valid_until'])) ?? ' ';;
            $vac_nameArray[] = $subArray['name'] ?? ' ';
            $vac_dozaArray[] = $subArray['count'] ?? ' ';
            $vac_mlArray[] = $subArray['count'] ?? ' ';
            $spirt_mlArray[] = $subArray['count'] ?? ' ';
            $shpriz2Array[] = $subArray['count'] ?? ' ';
            $shpriz5Array[] = $subArray['count'] ?? ' ';
            $salfetkaArray[] = $subArray['count'] ?? ' ';
            $perchatkaArray[] = $subArray['count'] ?? ' ';
            $zapis_otArray[] = $subArray['start_num'] ?? ' ';
            $zapis_toArray[] = $subArray['finish_num'] ?? ' ';
            $flacon_countArray[] = $subArray['count'] ?? ' ';
            $ostatok_vac_doza_countArray[] = $subArray['count_utilize'] ?? ' ';
            $ostatok_value_mlArray[] = $subArray['count_utilize'] ?? ' ';
            $igla_countArray[] = $subArray['count_utilize'] ?? ' ';
            if ($subArray['id_species'] == 25) {
                $dogs_array[] = $subArray; // Добавляем подмассив в первую группу, если id_species равен 25
            } elseif ($subArray['id_species'] == 9) {
                $cats_array[] = $subArray; // Добавляем подмассив во вторую группу, если id_species равен 9
            }
        }

        pg_free_result($result);

        $code = 'act_ektoparazit';
        $ext = 'docx';
        $templateDir = 'templates/';
        $document = new \PhpOffice\PhpWord\TemplateProcessor($templateDir . $code . '.' . $ext);

        $data = [
            'managing_name' => count($m_orgs_array) > 0 ? implode(", ", $m_orgs_array) : '',
            'shelter_name' => isset($pets_data[0]['shelter_title']) ? $pets_data[0]['shelter_title'] : ' ',
            'pets_count' => count($pets_data),
            'cats_count' => count($cats_array),
            'dogs_count' => count($dogs_array),
            'vac_specialist' => $vac_specialist,
            'vac_specialist2' => $vac_specialist2,
            'fio_arr' => implode(", ", $fioArray),
            'current_date' => $formattedDate,
            'simpleDate' => $simpleDate,
            'temp_date' => $temp_date,
            'act_number' => 1,
            'vac_specialist_post' => 'Ветеринарный врач/Специалист',
            'vac_specialist2_post' => 'Ветеринарный врач/Специалист',
            'vac_producer' => implode(", ", $vac_producerArray),
            'vac_serial_number' => implode(", ", $vac_serial_numberArray),
            'vac_expire_date' => implode(", ", $vac_expire_dateArray),
            'vac_name' => implode(", ", $vac_nameArray),
            'vac_doza' => implode(", ", $vac_dozaArray),
            'vac_ml' => implode(", ", $vac_mlArray),
            'spirt_ml' => implode(", ", $spirt_mlArray),
            'shpriz2' => implode(", ", $shpriz2Array),
            'shpriz5' => implode(", ", $shpriz5Array),
            'salfetka' => implode(", ", $salfetkaArray),
            'perchatka' => implode(", ", $perchatkaArray),
            'zapis_ot' => implode(", ", $zapis_otArray),
            'zapis_to' => implode(", ", $zapis_toArray),
            'flacon_count' => implode(", ", $flacon_countArray),
            'ostatok_vac_doza_count' => implode(", ", $ostatok_vac_doza_countArray),
            'ostatok_value_ml' => implode(", ", $ostatok_value_mlArray),
            'igla_count' => implode(", ", $igla_countArray),
        ];
        foreach ($data as $key => $val) {
            $document->setValue($key, $val);
        }
        $document->setValue("health_table", vaccination_pet_table($pets_data));
        $filename = 'Акт обработок от эктопаразитов.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename=' . $filename);
        $document->saveAs('php://output');

    } else if ($report == 'kitty_to_cat' or $report == 'puppy_to_dog') {
        $date_from = $_GET['date_from'];
        $date_to = $_GET['date_to'];
        $fio_chif = $_GET['fio_chif'] ?? null;
        $fio_chif2 = $_GET['fio_chif2'] ?? null;
        $fio_specialist = $_GET['fio_specialist'] ?? null;

        // Поиск наименования приюта и района если отчет будет пустой
        {
            $query1 = 'SELECT public.organizations.short_name, public.areas.name 
            FROM public.organizations 
            LEFT JOIN public.areas ON public.organizations.id_area = public.areas.id 
            WHERE public.organizations.id = ' . $org_user_id;
            $org_data = pg_query($query1) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($org_data);

            if ($row) {
                $org_name_value = $row[0];
                $area_name_value = $row[1];
            }
            pg_free_result($org_data);
        }

        $objPHPExcel->setActiveSheetIndex(1)
            ->setCellValue('D7', $area_name_value)
            ->setCellValue('B8', $org_name_value);

        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color, pets.id_species, ';
        $query .= 'chip.identification_code AS chip_title, ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area, shelters.short_name AS shelter_title, ';
        $query .= 'shelters.chief_name AS chief_name, pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id, shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name, pets.name, pets.birthday, pets.sex ';
        $query .= 'FROM shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id ';
        $query .= 'AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1  ';
        $query .= 'WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

//        $query .= "AND pets.name IS NOT NULL ";

        if ($report == 'puppy_to_dog') {
            $query .= " AND pets.id_species = '25' ";
            if ($date_from && $date_to) {
                $query_count .= " AND (shelter_guests.departure_date BETWEEN '" . $date_from . "' AND '" . $date_to . "' ";
                $query_count .= "OR shelter_guests.departure_date IS NULL)";
            }
        }
        if ($report == 'kitty_to_cat') {
            $query .= " AND pets.id_species = '9' ";
            if ($date_from && $date_to) {
                $query_count .= " AND (shelter_guests.departure_date BETWEEN '" . $date_from . "' AND '" . $date_to . "' ";
                $query_count .= "OR shelter_guests.departure_date IS NULL)";
            }
        }
        $query .= 'GROUP BY shelter_guests.id_pet, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name,  ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id, aviary.title,  ';
        $query .= 'species.name, pbr.name, characteristics, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date';

        $n = 3;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $currentDate = date('d.m.Y');

            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('D7', $row['area'])
                ->setCellValue('B8', $row['shelter_title']);


            if ($report == 'puppy_to_dog') {
                $age = '';
                if ($row['birthday'] != '') {
                    $new_date_to = \DateTime::createFromFormat('Y-m-d', $date_to);
                    if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
                        $birthday_interval = $new_date_to->diff($birthday);
                        $age = '' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.';
                    }
                }

//                Щенки в собак - в 10 месяцев
//                к дате рождения прибавляем год, получаем факт перевода

                $birthday1 = new DateTime($row['birthday']);
                $birthday1->modify('+6 months');
                $date_to1 = new DateTime($date_to);
                $date_from1 = new DateTime($date_from);

                if ($birthday1 <= $date_to1 && $birthday1 >= $date_from1) {

                    $sex = '';
                    if ($row['sex'] == 'f') {
                        $sex = 'сука';
                    } else {
                        $sex = 'кобель';
                    }

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $n, $n - 2)
                        ->setCellValue('B' . $n, 'щенок')
                        ->setCellValue('C' . $n, $row['name'])
                        ->setCellValue('D' . $n, $age)
                        ->setCellValue('E' . $n, $sex)
                        ->setCellValue('F' . $n, $row['color']);

                    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit('G' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                    // размер ячеек, где даты и чип
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                } else {
                    $n--;
                }
            }
            if ($report == 'kitty_to_cat') {
                $age = '';
                if ($row['birthday'] != '') {
                    $new_date_to = \DateTime::createFromFormat('Y-m-d', $date_to);
                    if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
                        $birthday_interval = $new_date_to->diff($birthday);
                        $age = '' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.';
                    }
                }

//                Котята переводятся в котов после 12 месяцев
//                к дате рождения прибавляем год, получаем факт перевода

                $birthday1 = new DateTime($row['birthday']);
                $birthday1->modify('+12 months');
                $date_to1 = new DateTime($date_to);
                $date_from1 = new DateTime($date_from);

                if ($birthday1 <= $date_to1 && $birthday1 >= $date_from1) {

                    $sex = '';
                    if ($row['sex'] == 'f') {
                        $sex = 'сука';
                    } else {
                        $sex = 'кобель';
                    }

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $n, $n - 2)
                        ->setCellValue('B' . $n, 'котенок')
                        ->setCellValue('C' . $n, $row['name'])
                        ->setCellValue('D' . $n, $age)
                        ->setCellValue('E' . $n, $sex)
                        ->setCellValue('F' . $n, $row['color']);

                    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit('G' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                    // размер ячеек, где даты и чип
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                } else {
                    $n--;
                }
            }

            $n++;

        }

        $objPHPExcel->setActiveSheetIndex(1)
            ->setCellValue('A15', $fio_chif ? ' Начальник отдела' : '_____________________')
            ->setCellValue('A17', $fio_chif2 ? 'Начальника отдела №2' : '_____________________')
            ->setCellValue('A19', $fio_specialist ? 'Ветеринарный врач' : '_____________________')
            ->setCellValue('F15', $fio_chif ?? '_____________________')
            ->setCellValue('F17', $fio_chif2 ?? '_____________________')
            ->setCellValue('F19', $fio_specialist ?? '_____________________');

        pg_free_result($result);
    } else {
        $query = 'SELECT ';
        $query .= 'pet_ref_color.title AS color,';
        $query .= 'MAX(prv.valid_until) AS vac_date, ';
        $query .= 'MAX(pov.valid_until) AS other_vac_date, ';
        $query .= 'chip.identification_code AS chip_title, ';
        $query .= 'users.fullname AS departure_specialist_fio, pbr.name AS breed_name, ';
        $query .= 'shelter_guests.*, areas.name AS area,  ';
        $query .= 'shelters.short_name AS shelter_title, shelters.chief_name AS chief_name,  ';
        $query .= 'pets.characteristics AS characteristics, ';
        $query .= 'managing.short_name AS managing, pets.id AS pet_id,  ';
        $query .= 'shelter_guests.id_organization AS id_org, ';
        $query .= 'aviary.title AS aviary_title, species.name AS species_name, pets.name, pets.birthday, pets.sex ';

        $query .= 'FROM (SELECT DISTINCT ON(shelter_guests.id_pet) shelter_guests.id_pet, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date, ';
        $query .= 'shelter_guests.departure_specialist, shelter_guests.departure_reason,  ';
        $query .= 'shelter_guests.socialized, shelter_guests.aviary_id, shelter_guests.id_organization,  ';
        $query .= 'shelter_guests.status, shelter_guests.arrival_date, shelter_guests.departure_date,  ';
        $query .= 'shelter_guests.id FROM shelter_guests) AS shelter_guests ';

        $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
        $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
        $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
        $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id  ';
        $query .= 'AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1  ';
        $query .= 'WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
        $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
        $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
        $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
        $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';
        $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.pet_other_vaccinations AS pov ON pov.id_pet=pets.id ';
        $query .= 'LEFT JOIN public.breeds AS pbr ON pbr.id=pets.id_breed ';
        $query .= 'LEFT JOIN specialists ON shelter_guests.departure_specialist=specialists.id ';
        $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';

        //Проверяем есть ли у этой организации управляемые
        $m_orgs_array = getManagingOrgs($org_user_id);

        $shelter = string_formating_for_sql($_GET["shelter"]);

        $levels_array = array();
        if (!empty($shelter)) {
            $query .= 'WHERE shelter_guests.id_organization=' . $shelter . ' ';
        } elseif (count($m_orgs_array) > 0) {
            $query .= 'WHERE (';
            for ($i = 0; $i <= count($m_orgs_array); $i++) {
                if ($m_orgs_array[$i]) {
                    if ($i > 0) {
                        $query .= ' OR ';
                    }
                    $query .= 'shelter_guests.id_organization=' . $m_orgs_array[$i]['id'] . '';

                    if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                        array_push($levels_array, $m_orgs_array[$i]['level']);
                    }
                }
            }
            $query .= ') ';
        } else {
            $query .= 'WHERE shelter_guests.id_organization=' . $org_user_id . ' ';
        }

        $query .= "AND pets.name IS NOT NULL AND pets.name <> '' ";
        //Проверяем есть ли у этой организации управляемые

        if ($arrival_date_from && $arrival_date_to) {
            $query .= "AND (shelter_guests.arrival_date BETWEEN '" . $arrival_date_from . "' AND '" . $arrival_date_to . "')";
        }
        if ($departure_date_from && $departure_date_to) {
            $query .= "AND (shelter_guests.departure_date BETWEEN '" . $departure_date_from . "' AND '" . $departure_date_to . "')";
        }

        if ($status) {
            $statuses = explode(",", $status);
            $ss = '';
            foreach ($statuses as $num => $item) {
                if ($num == (count($statuses) - 1)) {
                    $ss .= "'" . $item . "'";
                } else {
                    $ss .= "'" . $item . "', ";
                }
            }
            $query .= "AND shelter_guests.status IN (" . $ss . ") ";
        } else {
            $query .= "AND shelter_guests.status != 'DEACTIVATED' ";
        }

        if ($sex) {
            $sexs = explode(",", $sex);
            $ss = '';
            foreach ($sexs as $num => $item) {
                if ($num == (count($sexs) - 1)) {
                    $ss .= "'" . $item . "'";
                } else {
                    $ss .= "'" . $item . "', ";
                }
            }
            $query .= "AND pets.sex IN (" . $ss . ") ";
        }

        if ($socialized) {
            $query .= "AND shelter_guests.socialized='" . $socialized . "' ";
        }

        if ($species) {
            if ($species == 'OTHER') {
                $query .= "AND (public.pets.id_species!='9' AND public.pets.id_species!='25') ";
            } else {

                $spcs = explode(",", $species);
                $ss = '';
                foreach ($spcs as $num => $item) {
                    if ($num == (count($spcs) - 1)) {
                        $ss .= "'" . $item . "'";
                    } else {
                        $ss .= "'" . $item . "', ";
                    }
                }
                $query .= "AND public.pets.id_species IN (" . $ss . ") ";
            }
        }

        if ($departure_reason) {
            $departures = explode(",", $departure_reason);
            $ss = '';
            foreach ($departures as $num => $item) {
                if ($num == (count($departures) - 1)) {
                    $ss .= "'" . $item . "'";
                } else {
                    $ss .= "'" . $item . "', ";
                }
            }
            $query .= "AND shelter_guests.departure_reason IN (" . $ss . ") ";
        }

        if ($report == 'euthanasia_report') {//Журнал учета случаев эвтаназии
            $query .= " AND shelter_guests.departure_reason='EUTHANASIA' AND (departure_date BETWEEN '" . $date_from . "' AND '" . $date_to . "')";
        }

        if ($report == 'aviaries_report') {//отчёт по вольерам
            $query .= " AND shelter_guests.departure_date IS NULL ";
        }

        if ($report == 'reestr_count_animals') {
            if ($date_from && $date_to) {
                $query .= "AND shelter_guests.arrival_date >= '" . $date_from . "' ";
                $query .= "AND shelter_guests.departure_date <= '" . $date_to . "' ";
            }
        }

        $query .= 'GROUP BY shelter_guests.id_pet, shelter_guests.departure_date, ';
        $query .= 'shelter_guests.arrival_date, shelter_guests.status, shelter_guests.id_organization, ';
        $query .= 'shelter_guests.aviary_id, shelter_guests.departure_specialist, ';
        $query .= 'shelter_guests.socialized, shelter_guests.departure_reason, pet_ref_color.title, areas.name, ';
        $query .= 'chip.identification_code, users.fullname, shelters.chief_name, ';
        $query .= 'shelter_guests.id, shelters.short_name, managing.short_name, pets.id, aviary.title, ';
        $query .= 'species.name, pbr.name, characteristics, ';
        $query .= 'shelter_guests.arrival_reason, ';
        $query .= 'shelter_guests.arrival_act_number, ';
        $query .= 'shelter_guests.arrival_act_number_date';

        $n = 3;

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

        while ($row = pg_fetch_assoc($result)) {

            // $count++;
            $ku = '' . $row['id'] . '';
            if ($row['species_name'] == 'кошки') {
                $ku .= 'к';
            } else {
                $ku .= 'с';
            }
            $ku .= '' . $row['id_org'] . '';

            $aviary_title = 'Нет данных';
            if ($row['aviary_title']) {
                $aviary_title = $row['aviary_title'];
            }
            $chief_name = 'Нет данных';
            if ($row['chief_name']) {
                $chief_name = $row['chief_name'];
            }

            if ($report == 'aviaries_report') {//Список животных по вольерам
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getNumberFormat()->setFormatCode('0');

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, $aviary_title)
                    ->setCellValue('B' . $n, $row['species_name'])
                    ->setCellValue('C' . $n, $row['name'])
                    ->setCellValue('D' . $n, $ku)
                    ->setCellValue('E' . $n, $row['chip_title'])
                    ->setCellValue('F' . $n, $chief_name);

                //чип записываем в виде txt - без сокращения до +Е
                $objPHPExcel->getActiveSheet()->getStyle('E' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
            } else if ($report == 'shelters_report') {//Отчёт о животных, содержащихся в приютах
                $sex = '';
                if ($row['sex'] == 'f') {
                    $sex = 'женский';
                } else {
                    $sex = 'мужской';
                }

                $status = '';
                if ($row['status'] == 'IN_SHELTER') {
                    $status = 'В приюте';
                } else if ($row['status'] == 'QUARANTINE') {
                    $status = 'Карантин';
                } else if ($row['status'] == 'QUARANTINE_OTHER') {
                    $status = 'Карантин (продлён)';
                } else if ($row['status'] == 'DEPARTURED') {
                    $status = 'Выбыло';
                } else if ($row['status'] == 'IN_ISOLATION') {
                    $status = 'В изоляторе';
                } else if ($row['status'] == 'IN_HOSPITAL') {
                    $status = 'В стационаре';
                } else {
                    $status = $row['status'];
                }

                $age = '';
                if ($row['birthday'] != '') {
                    if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
                        $birthday_interval = (new DateTime())->diff($birthday);
                        $age = '' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.';
                    }
                }

                $socialized = 'Нет';
                if ($row['socialized'] == 't') {
                    $socialized = 'Да';
                }

                $arrival = '';
                if (!empty($row['arrival_reason'])) {

                    if ($row['arrival_reason'] == 'CATCH') {
                        $reason = 'Отлов';
                    } else if ($row['arrival_reason'] == 'COURT_DECISION') {
                        $reason = 'Решение суда';
                    } else if ($row['arrival_reason'] == 'FOUNDLING') {
                        $reason = 'Подкидыш';
                    } else if ($row['arrival_reason'] == 'OWNER_REFUSAL') {
                        $reason = 'Отказ владельца';
                    } else {
                        $reason = $row['arrival_reason'];
                    }

                    $arrival = $reason;
                    if (!empty($row['arrival_act_number'])) {
                        $arrival .= ' (№ ' . $row['arrival_act_number'] . ' от ' . $row['arrival_act_number_date'] . ')';
                    }
                }

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, $row['area'])
                    ->setCellValue('B' . $n, $row['managing'])
                    ->setCellValue('C' . $n, $row['shelter_title'])
                    ->setCellValue('D' . $n, $arrival)
                    ->setCellValue('E' . $n, $status)
                    ->setCellValue('F' . $n, $ku)
                    ->setCellValue('G' . $n, $row['chip_title'])
                    ->setCellValue('H' . $n, $row['name'])
                    ->setCellValue('I' . $n, $row['species_name'])
                    ->setCellValue('J' . $n, $sex)
                    ->setCellValue('K' . $n, $row['color'])
                    ->setCellValue('L' . $n, $age)
                    ->setCellValue('M' . $n, $row['arrival_date'])
                    ->setCellValue('N' . $n, $row['departure_date'])
                    ->setCellValue('O' . $n, $socialized)
                    ->setCellValue('P' . $n, $row['vac_date']);

                //чип записываем в виде числа - без сокращения до +E
                $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->getAlignment()->applyFromArray($cell_style);

            } else if ($report == 'euthanasia_report') {

                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getNumberFormat()->setFormatCode('0');

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, $row['shelter_title'])
                    ->setCellValue('B' . $n, $row['species_name'])
                    ->setCellValue('C' . $n, $ku)
                    ->setCellValue('D' . $n, $row['chip_title'])
                    ->setCellValue('E' . $n, $row['departure_date'])
                    ->setCellValue('F' . $n, $row['departure_comment'])
                    ->setCellValue('G' . $n, $row['departure_specialist_fio'])
                    ->setCellValue('H' . $n, '');

                //чип записываем в виде txt - без сокращения до +Е
                $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
            } else if ($report == 'reestr_count_animals') {//реестр о количестве животных в приюте

                $sex = '';
                if ($row['sex'] == 'f') {
                    $sex = 'женский';
                } else {
                    $sex = 'мужской';
                }

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $n, $n - 2)
                    ->setCellValue('B' . $n, $row['species_name'])
                    ->setCellValue('C' . $n, $row['name'])
                    ->setCellValue('D' . $n, $row['chip_title'])
                    ->setCellValue('E' . $n, $sex)
                    ->setCellValue('F' . $n, $row['breed_name'])
                    ->setCellValue('G' . $n, date("m.Y", strtotime($row['birthday'])))
                    ->setCellValue('H' . $n, $row['color']);

                //чип записываем в виде txt - без сокращения до +Е
                $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                if ($row['arrival_date']) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O' . $n, date("d.m.Y", strtotime($row['arrival_date'])));
                }
                if ($row['departure_date']) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P' . $n, date("d.m.Y", strtotime($row['departure_date'])));
                }

                if (date("Y", strtotime($row['vac_date'])) == '2022') {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . $n, date("d.m.Y", strtotime($row['vac_date'])));
                }
                if (date("Y", strtotime($row['vac_date'])) == '2023') {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J' . $n, date("d.m.Y", strtotime($row['vac_date'])));
                }
                if (date("Y", strtotime($row['vac_date'])) == '2024') {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K' . $n, date("d.m.Y", strtotime($row['vac_date'])));
                }
                if (date("Y", strtotime($row['other_vac_date'])) == '2022') {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L' . $n, date("d.m.Y", strtotime($row['other_vac_date'])));
                }
                if (date("Y", strtotime($row['other_vac_date'])) == '2023') {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M' . $n, date("d.m.Y", strtotime($row['other_vac_date'])));
                }
                if (date("Y", strtotime($row['other_vac_date'])) == '2024') {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N' . $n, date("d.m.Y", strtotime($row['other_vac_date'])));
                }

                //чип записываем в виде txt - без сокращения до +Е
                $objPHPExcel->getActiveSheet()->getStyle('D' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                // размер ячеек, где даты и чип, #
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(11);
                $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(11);
                $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(11);
                $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(11);
                $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(11);
                $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(11);

                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("H" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("I" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("J" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("K" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("L" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("M" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("N" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->getAlignment()->applyFromArray($cell_style);
                $objPHPExcel->getActiveSheet()->getStyle("O" . $n)->applyFromArray($border_style);
                $objPHPExcel->getActiveSheet()->getStyle("P" . $n)->getAlignment()->applyFromArray($cell_style);
            } else if ($report == 'puppy_to_dog') {
                $age = '';
                if ($row['birthday'] != '') {
                    if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
                        $birthday_interval = (new DateTime())->diff($birthday);
                        $age = '' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.';
                    }
                }

//                Щенки в собак - в 10 месяцев
//                к дате рождения прибавляем год, получаем факт перевода

                $birthday1 = new DateTime($row['birthday']);
                $birthday1->modify('+10 months');
                $date_to1 = new DateTime($date_to);
                $date_from1 = new DateTime($date_from);

                if ($birthday1 <= $date_to1 && $birthday1 >= $date_from1) {

                    $sex = '';
                    if ($row['sex'] == 'f') {
                        $sex = 'сука';
                    } else {
                        $sex = 'кобель';
                    }

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $n, $n - 2)
                        ->setCellValue('B' . $n, 'щенок')
                        ->setCellValue('C' . $n, $row['name'])
                        ->setCellValue('D' . $n, $age)
                        ->setCellValue('E' . $n, $sex)
                        ->setCellValue('F' . $n, $row['color']);

                    //чип записываем в виде txt - без сокращения до +Е
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                    // размер ячеек, где даты и чип
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                } else {
                    $n--;
                }
            }
            if ($report == 'kitty_to_cat') {

                $fio_chif = null;
                $fio_chif2 = null;
                $fio_specialist = null;

                $age = '';
                if ($row['birthday'] != '') {
                    if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
                        $birthday_interval = (new DateTime())->diff($birthday);
                        $age = '' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.';
                    }
                }

//                Котята переводятся в котов после 12 месяцев
//                к дате рождения прибавляем год, получаем факт перевода

                $birthday1 = new DateTime($row['birthday']);
                $birthday1->modify('+12 months');
                $date_to1 = new DateTime($date_to);
                $date_from1 = new DateTime($date_from);

                if ($birthday1 <= $date_to1 && $birthday1 >= $date_from1) {

                    $sex = '';
                    if ($row['sex'] == 'f') {
                        $sex = 'сука';
                    } else {
                        $sex = 'кобель';
                    }

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $n, $n - 2)
                        ->setCellValue('B' . $n, 'котенок')
                        ->setCellValue('C' . $n, $row['name'])
                        ->setCellValue('D' . $n, $age)
                        ->setCellValue('E' . $n, $sex)
                        ->setCellValue('F' . $n, $row['color']);

                    //чип записываем в виде txt - без сокращения до +Е
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $n)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $n, $row['chip_title'], \PHPExcel_Cell_DataType::TYPE_STRING);

                    // размер ячеек, где даты и чип
                    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("A" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("B" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("C" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("D" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("E" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("F" . $n)->applyFromArray($border_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->getAlignment()->applyFromArray($cell_style);
                    $objPHPExcel->getActiveSheet()->getStyle("G" . $n)->applyFromArray($border_style);
                } else {
                    $n--;
                }
                $currentDate = date('d.m.Y');
                $objPHPExcel->setActiveSheetIndex(1)
                    ->setCellValue('D7', $row['area'])
                    ->setCellValue('B8', $row['shelter_title'])
                    ->setCellValue('A10', 'Прошу перевести с ' . $currentDate . ' кошек из категории "котята" в категорию "взрослые ')->setCellValue('A15', 'Начальник структурного подразделения')
                    ->setCellValue('A15', $fio_chif ? 'Начальник структурного подразделения' : '_____________________')
                    ->setCellValue('A17', $fio_chif2 ? 'Начальник структурного подразделения' : '_____________________')
                    ->setCellValue('A19', $fio_specialist ? 'Специалист' : '_____________________')
                    ->setCellValue('F15', $fio_chif ?? '_____________________')
                    ->setCellValue('F17', $fio_chif2 ?? '_____________________')
                    ->setCellValue('F19', $fio_specialist ?? '_____________________');

            }

            $n++;

        }

        pg_free_result($result);
    }

    if ($report != 'act_beshenstvo'
        && $report != 'act_degelmintizacii'
        && $report != 'act_ektoparazit') {
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
        $objWriter->save('php://output');
    }
}

function choose_date_aviary($configuration)
{
    $date = string_formating_for_sql($_GET["date"]);
    $pet = string_formating_for_sql($_GET["pet"]);
    $id_user = string_formating_for_sql(user_id($configuration));

    if ($id_user) {
        if ($pet) {
            $query = "UPDATE shelter_guests SET arrival_date='$date', updated_at=NOW()::timestamp(0), updated_by = $id_user WHERE id_pet= $pet";
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);

            $message = '<id_pet>' . $pet . '</id_pet>';
            $message .= '<message>date_choosen</message>';
            echo xml($message);
        } else {
            $message = '<message>invalid_pet_id</message>';
            echo xml($message);
        }

    } else {
        echo xml('<message>invalid_id_user</message>');
    }
}

function shelters_choose_aviary($configuration)
{
    $aviary = $_GET["aviary"];
    $pet = $_GET["pet"];

    $id_user = user_id($configuration);

    if ($id_user) {
        if ($pet) {
            //проверка на смену вольера
            $query = 'SELECT aviary_id FROM shelter_guests WHERE id_pet=\'' . string_formating_for_sql($pet) . '\' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $aviary_id = $row[0];
            pg_free_result($result);

            if ($aviary_id != $aviary) {
                //смена в историю и сохранение
                $id_org = user_org_id($configuration);
                shelters_history_event_create('AVIARY', $pet, $id_org, $id_user, $aviary);

                $query = 'UPDATE shelter_guests SET ';
                if ($aviary == '0') {
                    $query .= 'aviary_id=NULL, ';
                } else {
                    $query .= 'aviary_id=\'' . string_formating_for_sql($aviary) . '\', ';
                }
                $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . '';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                pg_free_result($result);

                $message = '<id_pet>' . $pet . '</id_pet>';
                $message .= '<message>aviary_choosen</message>';
                echo xml($message);
            } else {
                $message = '<message>aviary_not_choosen</message>';
                echo xml($message);
            }

        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_add_new_owner($configuration)
{
    $owner_surname = $_GET["owner_surname"];
    $owner_name = $_GET["owner_name"];
    $owner_secondname = $_GET["owner_secondname"];
    $owner_telephone = $_GET["owner_telephone"];
    $owner_address = $_GET["owner_address"];

    $id_user = user_id($configuration);

    if ($id_user) {
        if (
            $owner_surname != '' &&
            $owner_name != '' &&
            $owner_address != '' &&
            ($owner_telephone != '' &&
                $owner_telephone != '+7')
        ) {
            //поиск fias по текцщим данным
            $id_fias = str_replace("[ ", "", $owner_address);
            $id_fias = str_replace(" ]", "", $id_fias);
            $fias_tokken = fias_request($configuration);
            $fias_address_ = fias_address($fias_tokken->access_token, $id_fias, $configuration);

            $fias_address = $fias_address_->suggestions[0];
            $query = 'SELECT id FROM fias_addresses WHERE ';
            if ($fias_address->data->house_fias_id || $fias_address->data->house_fias_id != 'null') {
                $query .= 'houseguid=\'' . string_formating_for_sql($fias_address->data->house_fias_id) . '\' AND ';
            } else {
                $query .= 'houseguid IS NULL AND ';
            }
            $query .= 'cityguid=\'' . string_formating_for_sql($fias_address->data->region_fias_id) . '\' AND ';
            if ($fias_address->data->street_fias_id) {
                $query .= 'streetguid=\'' . string_formating_for_sql($fias_address->data->street_fias_id) . '\' AND ';
            } else {
                $query .= 'streetguid IS NULL AND ';
            }
            $query .= 'roomguid IS NULL ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $id_fias_from_table = pg_fetch_result($result, 0);
            pg_free_result($result);

            if (!$id_fias_from_table) {//fias адрес не найден в таблице
                // "ш"
                // "ул"
                $street_prefix = "";
                if ($fias_address->data->street_type == 'ш') {
                    $street_prefix = 'шоссе';
                } else {
                    $street_prefix = 'улица';
                }

                $full_address = 'город ' . $fias_address->data->region . ', ' . $street_prefix . ' ' . $fias_address->data->street . ', дом ' . $fias_address->data->house . '';

                $lon = $fias_address->data->polygon->coordinates[0][0][0][0];
                $lat = $fias_address->data->polygon->coordinates[0][0][0][1];

                //$fias_address->data->adm_area
                //сравнение адреса
                //Северный административный округ
                //в БД
                //Северный Административный Округ

                $query = 'INSERT INTO fias_addresses ';
                $query .= '(full_address, lon, lat, region, city, street, house, cityguid, streetguid, houseguid, roomguid, created_by, updated_by, created_at, updated_at) ';
                $query .= 'VALUES (';
                $query .= "'" . $full_address . "',";

                if ($lon) {
                    $query .= "'" . $lon . "',";
                } else {
                    $query .= "NULL,";
                }
                if ($lat) {
                    $query .= "'" . $lat . "',";
                } else {
                    $query .= "NULL,";
                }

                $query .= "'город " . $fias_address->data->region . "',";
                $query .= "'город " . $fias_address->data->region . "',";

                if ($fias_address->data->street) {
                    $query .= "'" . $street_prefix . " " . $fias_address->data->street . "',";
                } else {
                    $query .= "NULL,";
                }
                if ($fias_address->data->house) {
                    $query .= "'дом " . $fias_address->data->house . "',";
                } else {
                    $query .= "NULL,";
                }
                $query .= "'" . $fias_address->data->region_fias_id . "',";
                if ($fias_address->data->street_fias_id) {
                    $query .= "'" . $fias_address->data->street_fias_id . "',";
                } else {
                    $query .= "NULL,";
                }
                if ($fias_address->data->house_fias_id) {
                    $query .= "'" . $fias_address->data->house_fias_id . "',";
                } else {
                    $query .= "NULL,";
                }
                $query .= "NULL,";

                $query .= "" . $id_user . ",";#created_by
                $query .= "" . $id_user . ",";#updated_by
                $query .= "NOW()::timestamp(0),";#created_at
                $query .= "NOW()::timestamp(0)";#updated_at
                $query .= ') RETURNING id;';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $id_fias_from_table = $row[0];
                pg_free_result($result);
            }

            #СОЗДАЁМ ВЛАДЕЛЬЦА
            $fio = '';
            $query = 'INSERT INTO pet_owners ';
            $query .= '(f_fio, i_fio, o_fio, fullname, id_fias_address, id_fact_fias_address, created_by, updated_by, created_at, updated_at, is_main) ';
            $query .= 'VALUES (';
            $query .= "'" . $owner_surname . "',";
            $query .= "'" . $owner_name . "',";
            $query .= "'" . $owner_secondname . "',";
            if ($owner_secondname != '') {
                $fio = $owner_surname . " " . $owner_name . " " . $owner_secondname;
                $query .= "'" . $owner_surname . " " . $owner_name . " " . $owner_secondname . "',";
            } else {
                $fio = $owner_surname . " " . $owner_name;
                $query .= "'" . $owner_surname . " " . $owner_name . "',";
            }
            if ($id_fias_from_table) {
                $query .= "" . $id_fias_from_table . ",";#адрес
            } else {
                $query .= "NULL,";
            }
            if ($id_fias_from_table) {
                $query .= "" . $id_fias_from_table . ",";#адрес
            } else {
                $query .= "NULL,";
            }
            $query .= "" . $id_user . ",";#created_by
            $query .= "" . $id_user . ",";#updated_by
            $query .= "NOW()::timestamp(0),";#created_at
            $query .= "NOW()::timestamp(0),";#updated_at
            $query .= "true";
            $query .= ') RETURNING id;';

            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $id_owner = $row[0];
            pg_free_result($result);

            #КОНТАКТЫ
            $query = 'INSERT INTO contacts ';
            $query .= '(id_contact_type, entity_type, entity_id, name, created_by, updated_by, created_at, updated_at, main_flag, confirmed) ';
            $query .= 'VALUES (';
            $query .= "1,";
            $query .= "'pet_owner',";
            $query .= "" . $id_owner . ",";
            $query .= "'+" . trim($owner_telephone) . "',";
            $query .= "" . $id_user . ",";#created_by
            $query .= "" . $id_user . ",";#updated_by
            $query .= "NOW()::timestamp(0),";#created_at
            $query .= "NOW()::timestamp(0),";#updated_at
            $query .= "true,";
            $query .= "true";
            $query .= ');';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            pg_free_result($result);

            echo xml('<message>owner_added</message><id>' . $id_owner . '</id><name>' . $fio . '</name>');
        } else {
            echo xml('<messager>1</messager><message>empty_fields</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_download_document($configuration)
{
    $document_id = $_GET['document_id'];
    $document_name = '';
    if ($document_id) {
        $query = 'SELECT name FROM public.documents WHERE file_id=\'' . $document_id . '\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $document_name = $row[0];
        pg_free_result($result);
    } else {
        $message = '<message>document_id_not_exist</message>';
        // echo xml($message);
        echo '<script>alert("Файл не найден!"); window.history.back();</script>';
        exit;
    }

    $path = '';
    if ($document_name) {
        $query = 'SELECT path FROM public.files WHERE id=\'' . $document_id . '\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $path = $row[0];
        pg_free_result($result);
    } else {
        $message = '<message>document_name_not_exist</message>';
        // echo xml($message);
        echo '<script>alert("Файл не найден!"); window.history.back();</script>';
        exit;
    }

    if ($path && $document_name) {

        $file_path = ".." . $path;

        // проверяем, существует ли файл
        if (file_exists($file_path)) {

            $fileNameFromPath = basename($file_path);
            $fileArr = explode('.', $fileNameFromPath);
            $fileType = $fileArr[count($fileArr) - 1];
            $file_name = $document_name . "." . $fileType;

            // Отправляем заголовки для скачивания файла
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');

            // Скачиваем файл
            readfile($file_path);
            exit();

        } else {

            $fileNameFromPath = basename($file_path);
            $fileArr = explode('.', $fileNameFromPath);
            $fileType = $fileArr[count($fileArr) - 1];
            $file_name = $document_name . "." . $fileType;

            // Отправляем заголовки для скачивания файла
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');

            // Скачиваем файл
            readfile("./templates/act.docx");
            exit();

        }
    }
}

function shelters_download_image($configuration)
{
    $document_id = $_GET['image_id'];

    $document_name = '';
    $path = '';

    if ($document_id) {
        $query = 'SELECT path, name FROM public.files WHERE id=\'' . $document_id . '\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $path = $row[0];
        $document_name = $row[1];
        pg_free_result($result);
    } else {
        $message = '<message>document_id_not_exist</message>';
        // echo xml($message);
        echo '<script>alert("Файл не найден!"); window.history.back();</script>';
        exit;
    }

    if ($path && $document_name) {
        $file_path = ".." . $path;
        // проверяем, существует ли файл
        if (file_exists($file_path)) {

            $fileNameFromPath = basename($file_path);
            $fileArr = explode('.', $fileNameFromPath);
            $fileType = $fileArr[count($fileArr) - 1];
            $file_name = $document_name;

            // Отправляем заголовки для скачивания файла
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');

            // Скачиваем файл
            readfile($file_path);
            exit();
        } else {

            $fileNameFromPath = basename($file_path);
            $fileArr = explode('.', $fileNameFromPath);
            $fileType = $fileArr[count($fileArr) - 1];
            $file_name = $document_name;

            // Отправляем заголовки для скачивания файла
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');

            // Скачиваем файл
            readfile("./templates/act.docx");
            exit();

        }
    }
}

function shelters_main_image($configuration)
{
    $image_id = $_GET['image_id'];

    $id_user = user_id($configuration);

    $query = 'SELECT entity_id FROM public.files WHERE id=\'' . $image_id . '\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $entity_id = $row[0];
    pg_free_result($result);

    $query = 'UPDATE files SET ';
    $query .= 'entity_type=\'shelter\', ';
    $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE entity_id=' . string_formating_for_sql($entity_id) . ' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    pg_free_result($result);

    $query = 'UPDATE files SET ';
    $query .= 'entity_type=\'shelter_main\', ';
    $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . string_formating_for_sql($image_id) . ' ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    pg_free_result($result);
}

function shelters_delete_image($configuration)
{
    $image_id = $_GET['image_id'];

    $path = '';
    if ($image_id) {
        $query = 'SELECT path FROM public.files WHERE id=\'' . $image_id . '\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $path = $row[0];
        pg_free_result($result);
    } else {
        $message = '<message>document_id_is_not_set</message>';
        echo xml($message);
        exit();
    }

    if ($path) {
        $query = 'DELETE FROM public.files WHERE id=\'' . $image_id . '\'';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $message = '<message>path_is_not_set</message>';
        echo xml($message);
        exit();
    }

    if ($path) {
        $file = ".." . $path;
        if (file_exists($file)) {
            if (unlink($file)) { // удаляем файл
                $message = '<message>file_deleted</message>';
                echo xml($message);
            } else {
                echo "Error deleting file";
                exit();
            }
        } else {
            $message = '<message>file_not_found</message>';
            echo xml($message);
            exit();
        }
    }
}

function shelters_delete_document($configuration)
{
    $document_id = $_GET['document_id'];
    if ($document_id) {
        $query = 'DELETE FROM public.documents WHERE file_id = \'' . $document_id . '\'';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $message = '<message>document_id_is_not_set</message>';
        echo xml($message);
        exit();
    }

    $path = '';
    if ($document_id) {
        $query = 'SELECT path FROM public.files WHERE id=\'' . $document_id . '\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $path = $row[0];
        pg_free_result($result);
    } else {
        $message = '<message>document_id_is_not_set</message>';
        echo xml($message);
        exit();
    }

    if ($path) {
        $query = 'DELETE FROM public.files WHERE id=\'' . $document_id . '\'';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
    } else {
        $message = '<message>path_is_not_set</message>';
        echo xml($message);
        exit();
    }

    if ($path) {
        $file = ".." . $path;
        if (file_exists($file)) {
            if (unlink($file)) { // удаляем файл
                $message = '<message>file_deleted</message>';
                echo xml($message);
            } else {
                echo "Error deleting file";
                exit();
            }
        } else {
            // echo "file not found";
            $message = '<message>file_not_found</message>';
            echo xml($message);

            exit();
        }
    }
}

function shelters_add_image($configuration)
{
    $pet = $_POST["pet"];
    $temp_pet = $_POST["temp_pet"];

    $id_user = user_id($configuration);

    if ($id_user) {
        if ($pet || $temp_pet) {
            $file_inputname = 'file';
            $fileTmpPath = $_FILES[$file_inputname]['tmp_name'];
            $fileName = $_FILES[$file_inputname]['name'];
            $fileSize = $_FILES[$file_inputname]['size'];//размер файла
            $fileType = $_FILES[$file_inputname]['type'];//тип файла

            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $fileNameGenerated = generateHash($fileName);
            $prefixToFolder = "..";
            $uploadFileDir = '/upload/shelters/';

            // создаем папку если ее нет
            if (!is_dir($prefixToFolder . $uploadFileDir)) {
                mkdir($prefixToFolder . $uploadFileDir, 0777, true);
            }

            $dest_path = $prefixToFolder . '' . $uploadFileDir . '' . $fileNameGenerated . '.' . $fileExtension . '';
            $dest_path_to_db = $uploadFileDir . '' . $fileNameGenerated . '.' . $fileExtension . '';

            $message = '<message>' . $fileNameGenerated . '</message>';

            if ($fileName) {
                move_uploaded_file($fileTmpPath, $dest_path);

                // $id_sg = '';
                // if($pet){
                //     $query = 'SELECT id FROM shelter_guests WHERE id_pet=\'' . $pet . '\' LIMIT 1';
                //     $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                //     $row = pg_fetch_row($result);
                //     $id_sg = $row[0];
                //     pg_free_result($result);
                // }

                $query = 'INSERT INTO files ';
                $query .= '(hash, path, name, created, entity_id, entity_type, created_at, created_by) ';
                $query .= 'VALUES (';
                $query .= "'" . $fileNameGenerated . "',";//hash
                $query .= "'" . $dest_path_to_db . "',";//path
                $query .= "'" . $fileName . "',";//name
                $query .= "NOW()::timestamp(0),";#created
                if ($pet) {
                    $query .= "'" . $pet . "',";//entity_id
                    $query .= "'shelter',";//entity_type
                } else {
                    //создаём временное id
                    $query .= "'" . $temp_pet . "',";//entity_id
                    $query .= "'shelter_temp',";//entity_type
                }

                $query .= "NOW()::timestamp(0),";#created_at
                $query .= "" . $id_user . "";#created_by
                $query .= ') RETURNING id;';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $id_file = $row[0];
                pg_free_result($result);

                $message = '<message>image_added</message>';
            } else {
                $message = '<message>image_file_error</message>';
            }

            echo xml($message);
        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_add_document($configuration)
{
    $type = $_POST["type"];
    $number = $_POST["number"];
    $date = $_POST["date"];
    $pet = $_POST["pet"];
    $temp_pet = $_POST["temp_pet"];

    $id_user = user_id($configuration);

    if ($id_user) {
        if ($pet || $temp_pet) {
            $file_inputname = 'file';
            $fileTmpPath = $_FILES[$file_inputname]['tmp_name'];
            $fileName = $_FILES[$file_inputname]['name'];
            $fileSize = $_FILES[$file_inputname]['size'];//размер файла
            $fileType = $_FILES[$file_inputname]['type'];//тип файла

            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $fileNameGenerated = generateHash($fileName);
            $prefixToFolder = "..";
            $uploadFileDir = '/upload/shelters/';

            // создаем папку если ее нет
            if (!is_dir($prefixToFolder . $uploadFileDir)) {
                mkdir($prefixToFolder . $uploadFileDir, 0777, true);
            }

            $dest_path = $prefixToFolder . '' . $uploadFileDir . '' . $fileNameGenerated . '.' . $fileExtension . '';
            $dest_path_to_db = $uploadFileDir . '' . $fileNameGenerated . '.' . $fileExtension . '';

            $message = '<message>' . $fileNameGenerated . '</message>';

            if ($fileName) {
                move_uploaded_file($fileTmpPath, $dest_path);

                if ($pet) {
                    $query = 'SELECT id FROM shelter_guests WHERE id_pet=\'' . $pet . '\' LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    $id_sg = $row[0];
                    pg_free_result($result);
                }

                $query = 'SELECT name FROM document_types WHERE id=\'' . $type . '\' LIMIT 1';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $doc_title = $row[0];
                pg_free_result($result);

                $query = 'INSERT INTO files ';
                $query .= '(hash, path, name, created, entity_id, entity_type, created_at, created_by) ';
                $query .= 'VALUES (';
                $query .= "'" . $fileNameGenerated . "',";//hash
                $query .= "'" . $dest_path_to_db . "',";//path
                $query .= "'" . $fileName . "',";//name
                $query .= "NOW()::timestamp(0),";#created
                if ($pet) {
                    $query .= "'" . $id_sg . "',";//entity_id
                    $query .= "'shelter_guests',";//entity_type
                } else {
                    //создаём временное id
                    $query .= "'" . $temp_pet . "',";//entity_id
                    $query .= "'shelter_guests_temp',";//entity_type
                }

                $query .= "NOW()::timestamp(0),";#created_at
                $query .= "" . $id_user . "";#created_by
                $query .= ') RETURNING id;';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $id_file = $row[0];
                pg_free_result($result);

                $doc_name = '' . $doc_title . ' № ' . $number . ' от ' . $date;

                $query = 'INSERT INTO documents ';
                $query .= '(file_id, type_id, number, date, name, created_date, created_by) ';
                $query .= 'VALUES (';
                $query .= "" . $id_file . ",";//file_id
                $query .= "'" . $type . "',";//type_id
                $query .= "'" . $number . "',";//number
                $query .= "'" . $date . "',";//date
                $query .= "'" . $doc_name . "',";//name
                $query .= "NOW()::timestamp(0),";#created_at
                $query .= "" . $id_user . "";#created_by
                $query .= ') RETURNING id;';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $id_file = $row[0];
                pg_free_result($result);

                if ($id_sg) {
                    //обновляем поля у SG

                    //ищем акты приёма
                    $query = 'SELECT files.id AS id_file, files.path, documents.name, documents.number, documents.date, document_types.type, document_types.group, documents.id, documents.protected_at FROM files ';
                    $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
                    $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
                    $query .= 'WHERE entity_id=' . string_formating_for_sql($id_sg) . ' AND entity_type=\'shelter_guests\'';
                    $query .= 'AND (document_types.group=\'CATCH\' OR document_types.group=\'COURT_DECISION\' OR document_types.group=\'FOUNDLING\' OR document_types.group=\'OWNER_REFUSAL\') ';
                    $query .= 'AND (document_types.type LIKE \'TYPE_ACT_ARRIVE_%\') ';
                    $query .= 'ORDER BY documents.date DESC, documents.id ASC ';
                    $query .= 'LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_assoc($result);
                    $number = '';
                    $number = $row['number'];
                    $date = $row['date'];
                    pg_free_result($result);

                    $query = 'UPDATE shelter_guests SET ';
                    if ($number) {
                        $query .= 'arrival_act_number=\'' . $number . '\', ';
                        $query .= 'arrival_act_number_date=\'' . $date . '\', ';
                    } else {
                        $query .= 'arrival_act_number=NULL, ';
                        $query .= 'arrival_act_number_date=NULL, ';
                    }
                    $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . $id_sg . ' ';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    pg_free_result($result);

                    //ищем заказы наряда
                    $query = 'SELECT files.id AS id_file, files.path, documents.name, documents.number, documents.date, document_types.type, document_types.group, documents.id, documents.protected_at FROM files ';
                    $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
                    $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
                    $query .= 'WHERE entity_id=' . string_formating_for_sql($id_sg) . ' AND entity_type=\'shelter_guests\'';
                    $query .= 'AND (document_types.type=\'TYPE_WORK_ORDER\') ';
                    $query .= 'ORDER BY documents.date DESC, documents.id ASC ';
                    $query .= 'LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_assoc($result);
                    $number = '';
                    $number = $row['number'];
                    $date = $row['date'];
                    pg_free_result($result);

                    $query = 'UPDATE shelter_guests SET ';
                    if ($number) {
                        $query .= 'arrival_work_order=\'' . $number . '\', ';
                        $query .= 'arrival_work_order_date=\'' . $date . '\', ';
                    } else {
                        $query .= 'arrival_work_order=NULL, ';
                        $query .= 'arrival_work_order_date=NULL, ';
                    }
                    $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . $id_sg . ' ';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    pg_free_result($result);

                    //ищем акты отлова
                    $query = 'SELECT files.id AS id_file, files.path, documents.name, documents.number, documents.date, document_types.type, document_types.group, documents.id, documents.protected_at FROM files ';
                    $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
                    $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
                    $query .= 'WHERE entity_id=' . string_formating_for_sql($id_sg) . ' AND entity_type=\'shelter_guests\'';
                    $query .= 'AND (document_types.type=\'TYPE_ACT_CATCH\') ';
                    $query .= 'ORDER BY documents.date DESC, documents.id ASC ';
                    $query .= 'LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_assoc($result);
                    $number = $row['number'];
                    $date = $row['date'];
                    pg_free_result($result);

                    $query = 'UPDATE shelter_guests SET ';
                    if ($number) {
                        $query .= 'catching_act_number=\'' . $number . '\', ';
                        $query .= 'catching_act_date=\'' . $date . '\', ';
                    } else {
                        $query .= 'catching_act_number=NULL, ';
                        $query .= 'catching_act_date=NULL, ';
                    }

                    $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . $id_sg . ' ';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    pg_free_result($result);
                    //ищем акты отлова
                }

                $message = '<message>document_added</message>';
            } else {
                $message = '<message>document_file_error</message>';
            }

            echo xml($message);
        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_choose_health($configuration)
{
    $status = $_GET["status"];
    $pet = $_GET["pet"];

    $id_user = user_id($configuration);

    if ($id_user) {
        if ($pet) {
            $query = 'UPDATE shelter_guests SET ';
            if ($status == '1') {
                $query .= 'status=\'IN_HOSPITAL\', ';
            } else if ($status == '2') {
                $query .= 'status=\'IN_ISOLATION\', ';
            } else {
                $query .= 'status=\'IN_SHELTER\', ';

            }
            $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . '';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);

            $message = '<id_pet>' . $pet . '</id_pet>';
            $message .= '<message>health_choosen</message>';

            echo xml($message);
        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_get_pet($configuration)
{
    $id = $_GET["pet"];
    $query = 'SELECT ';
    $query .= 'pets.id AS id, ';

    $query .= 'pets.name AS name, ';
    $query .= 'pets.sex AS sex, ';
    $query .= 'pets.id_species AS specie, ';
    $query .= 'pets.id_breed AS breed, ';
    $query .= 'pets.birthday AS birthday, ';

    $query .= 'pets.reg_expire_date AS reg_expire_date, ';
    $query .= 'pets.id_reg_expire_reason AS id_reg_expire_reason, ';


    $query .= 'shelter_guests.id_organization AS id_org, ';

    $query .= 'shelter_guests.id_pet AS shelters_id, ';
    $query .= 'shelter_guests.arrival_reason AS arrival_reason, ';
    $query .= 'shelter_guests.arrival_date AS arrival_date, ';

    $query .= 'shelter_guests.departure_reason AS departure_reason, ';
    $query .= 'shelter_guests.departure_date AS departure_date ';

    $query .= 'FROM public.pet_identification ';

    $query .= 'LEFT JOIN public.pets ON pets.id=pet_identification.id_pet ';
    $query .= 'LEFT JOIN public.shelter_guests ON pets.id=shelter_guests.id_pet ';

    $query .= 'WHERE pets.id=\'' . $id . '\'';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    pg_free_result($result);

    $message = '';
    $message .= '<id_pet>' . $row['id'] . '</id_pet>';
    $message .= '<name>' . $row['name'] . '</name>';
    $message .= '<sex>' . $row['sex'] . '</sex>';

    $birthdayArray = explode("-", $row['birthday']);
    $message .= '<birthday>' . $birthdayArray[1] . '.' . $birthdayArray[0] . '</birthday>';

    $message .= '<specie>' . $row['specie'] . '</specie>';
    $message .= '<breed>' . $row['breed'] . '</breed>';

    echo xml($message);
}

function shelters_add_pet($configuration)
{
    //Проверка чипов
    //1. животное обнаружено в этом же приюте - переход к нему
    //2. животное обнаружено вообще - автозаполнение вроде было?
    //3. животное обнаружено в другом приюте - сообщение?
    //возможно на разных животных один и тот же чип
    //Проверка всех полей

    $chip = $_GET["chip"];
    $label = $_GET["label"];
    $shelter = $_GET["shelter"];
    $pet = $_GET["pet"];
    $temp_pet = $_GET["temp_pet"];
    $id_user = user_id($configuration);

    $query = 'SELECT ';
    $query .= 'pets.id AS id, ';
    $query .= 'pets.name AS name, ';
    $query .= 'pets.sex AS sex, ';
    $query .= 'pets.id_species AS specie, ';
    $query .= 'pets.id_breed AS breed, ';
    $query .= 'pets.birthday AS birthday, ';
    $query .= 'pets.reg_expire_date AS reg_expire_date, ';
    $query .= 'pets.id_reg_expire_reason AS id_reg_expire_reason, ';
    $query .= 'shelter_guests.id_organization AS id_org, ';
    $query .= 'shelter_guests.id_pet AS shelters_id, ';
    $query .= 'shelter_guests.arrival_reason AS arrival_reason, ';
    $query .= 'shelter_guests.arrival_date AS arrival_date, ';
    $query .= 'shelter_guests.departure_reason AS departure_reason, ';
    $query .= 'shelter_guests.departure_date AS departure_date ';
    $query .= 'FROM public.pet_identification ';
    $query .= 'LEFT JOIN public.pets ON pets.id=pet_identification.id_pet ';
    $query .= 'LEFT JOIN public.shelter_guests ON pets.id=shelter_guests.id_pet ';
    $query .= 'WHERE pet_identification.id_ident_type=1 AND pet_identification.identification_code=\'' . $chip . '\'';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    pg_free_result($result);

    if ($row['id']) {
        //животное присутствует в системе
        if ($row['shelters_id'] != '' && ($row['departure_reason'] == 'DEATH' || $row['departure_reason'] == 'EUTHANASIA')) {
            //4. произошёл падёж животного приюта
            $message = '<message>chip_error</message>';
            $message .= '<chip>1</chip>';
            $message .= '<text>death</text>';
            echo xml($message);

            return true;
        }

        if ($row['reg_expire_date'] != '') {
            //животное снято с учёта
            $message = '<message>chip_error</message>';
            $message .= '<chip>1</chip>';
            $message .= '<text>departured</text>';
            echo xml($message);

            return true;
        }

        if ($row['shelters_id'] == '') {
            $message = '<message>chip_error</message>';
            $message .= '<chip>1</chip>';
            $message .= '<id_pet>' . $row['id'] . '</id_pet>';

            // $message.='<name>'.$row['name'].'</name>';
            // $message.='<sex>'.$row['sex'].'</sex>';
            // $message.='<birthday>'.$row['birthday'].'</birthday>';
            // $message.='<specie>'.$row['specie'].'</specie>';
            // $message.='<breed>'.$row['breed'].'</breed>';

            $message .= '<text>in_system</text>';
            echo xml($message);

            return true;
        }

        if ($row['departure_reason'] != '') {
            $message = '<message>chip_error</message>';
            $message .= '<id_pet>' . $row['id'] . '</id_pet>';

            if ($row['id_org'] == $shelter) {
                $message .= '<text>in_this_shelter</text>';
            } else {
                $message .= '<text>in_another_shelter</text>';

                $query_ = 'SELECT name FROM public.organizations WHERE id=' . $row['id_org'] . '';
                $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
                $row_ = pg_fetch_assoc($result_);
                pg_free_result($result_);

                $message .= '<shelter_title>' . $row_['name'] . '</shelter_title>';
            }
            $message .= '<chip>1</chip>';

            echo xml($message);
            return true;
        }

        if ($row['departure_reason'] == '') {
            $message = '<message>chip_error</message>';

            $message .= '<id_pet>' . $row['id'] . '</id_pet>';

            //1. животное обнаружено в этом же приюте - переход к нему
            //3. животное обнаружено в другом приюте - сообщение?

            if ($row['id_org'] == $shelter) {
                $message .= '<text>now_in_this_shelter</text>';
            } else {
                $query_ = 'SELECT name FROM public.organizations WHERE id=' . $row['id_org'] . '';
                $result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
                $row_ = pg_fetch_assoc($result_);
                pg_free_result($result_);

                $message .= '<shelter_title>' . $row_['name'] . '</shelter_title>';

                $message .= '<text>now_in_another_shelter</text>';
            }

            $message .= '<chip>1</chip>';

            echo xml($message);
            return true;
        }
    }

    //надо ли создавать животное в pets?

    /////////////////////////
    //Проверка на документы//
    $arrival_reason = $_GET["arrival_reason"];
    if ($arrival_reason) {
        //ищем акты приёма
        $query = 'SELECT files.id AS id_file FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($temp_pet) . ' AND entity_type=\'shelter_guests_temp\'';
        $query .= 'AND (document_types.group=\'CATCH\' OR document_types.group=\'COURT_DECISION\' OR document_types.group=\'FOUNDLING\' OR document_types.group=\'OWNER_REFUSAL\') ';
        $query .= 'ORDER BY documents.date ASC, documents.id ASC ';
        $query .= 'LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_assoc($result);
        $id_file = $row['id_file'];
        pg_free_result($result);

        if (!$id_file) {
            $message = '<message>docs_error</message>';
            $message .= '<text>act_arrive</text>';
            echo xml($message);

            return true;
        }
    }
    //Проверка на документы//
    /////////////////////////

    $birthday = $_GET["birthday"];

    if ($birthday == "" && $pet == "") {
        $message = '<message>empty_fields</message>';
        if (!$birthday) {
            $message .= '<birthday>1</birthday>';
        }

        echo xml($message);

        return true;
    }


    $breed = $_GET["breed"];

    if ($breed == "" && $pet == "") {
        $message = '<message>empty_fields</message>';
        if (!$breed) {
            $message .= '<breed>1</breed>';
        }

        echo xml($message);
        return true;
    }


    $birthdayArray = explode(".", $birthday);

    $name = $_GET["name"];
    $sex = $_GET["sex"];
    $specie = $_GET["specie"];
    $breed = $_GET["breed"];
    $characteristics = $_GET["characteristics"];
    $character = $_GET["character"];
    $size = $_GET["size"];
    $color = $_GET["color"];
    $ear = $_GET["ear"];
    $tail = $_GET["tail"];
    $wool = $_GET["wool"];

    $id_org = user_org_id($configuration);

    $id_pet = 0;
    if (!$pet) {
        $query = 'INSERT INTO pets (';
        $query .= 'birthday, ';
        $query .= 'name, ';
        $query .= 'sex, ';
        $query .= 'id_species, ';
        $query .= 'id_breed, ';
        $query .= 'characteristics, ';
        $query .= 'character, ';
        $query .= 'id_created_organization, ';
        $query .= 'size_id, ';
        $query .= 'color_id, ';
        $query .= 'ear_type_id, ';
        $query .= 'tail_type_id, ';
        $query .= 'wool_type_id, ';
        $query .= 'created_at, updated_at, created_by, updated_by) ';
        $query .= 'VALUES (';
        $query .= '\'' . string_formating_for_sql($birthdayArray[1]) . '-' . string_formating_for_sql($birthdayArray[0]) . '-15\',';
        if ($name) {
            $query .= "'" . string_formating_for_sql($name) . "',";
        } else {
            $query .= "NULL,";
        }
        if ($sex) {
            $query .= "'" . string_formating_for_sql($sex) . "',";
        } else {
            $query .= "NULL,";
        }
        if ($specie) {
            $query .= "" . string_formating_for_sql($specie) . ",";
        } else {
            $query .= "NULL,";
        }
        if ($breed) {
            $query .= "" . string_formating_for_sql($breed) . ",";
        } else {
            $query .= "NULL,";
        }
        if ($characteristics) {
            $query .= "'" . string_formating_for_sql($characteristics) . "',";
        } else {
            $query .= "NULL,";
        }
        if ($character) {
            $query .= "'" . string_formating_for_sql($character) . "',";
        } else {
            $query .= "NULL,";
        }
        $query .= "" . $id_org . ",";#id_org
        if ($size) {
            $query .= "" . string_formating_for_sql($size) . ",";
        } else {
            $query .= "NULL,";
        }
        if ($color) {
            $query .= "" . string_formating_for_sql($color) . ",";
        } else {
            $query .= "NULL,";
        }
        if ($ear) {
            $query .= "" . string_formating_for_sql($ear) . ",";
        } else {
            $query .= "NULL,";
        }
        if ($tail) {
            $query .= "" . string_formating_for_sql($tail) . ",";
        } else {
            $query .= "NULL,";
        }
        if ($wool) {
            $query .= "" . string_formating_for_sql($wool) . ",";
        } else {
            $query .= "NULL,";
        }
        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "" . $id_user . "";#updated_by
        $query .= ') RETURNING id;';

        //echo $query;
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id_pet = $row[0];
        pg_free_result($result);
        shelters_history_event_create('REGISTERED', $id_pet, $id_org, $id_user, '');
    } else {
        $id_pet = $pet;
    }

    //есть ли чип этот?
    $query = 'SELECT id FROM public.pet_identification ';
    $query .= 'WHERE pet_identification.id_ident_type=1 AND pet_identification.identification_code=\'' . $chip . '\' AND id_pet=' . $id_pet . '';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);
    pg_free_result($result);
    if (!$row['id'] && $chip && $id_pet) {
        //добавляем

        $query_insert = 'INSERT INTO pet_identification ';
        $query_insert .= '(identification_code, id_pet, id_ident_type, created_at, updated_at) ';
        $query_insert .= 'VALUES (';
        $query_insert .= '\'' . $chip . '\',';
        $query_insert .= '' . $id_pet . ',';
        $query_insert .= '1,';
        $query_insert .= "NOW()::timestamp(0),";#created_at
        $query_insert .= "NOW()::timestamp(0)";#updated_at
        $query_insert .= ') RETURNING id;';
        $result = pg_query($query_insert) or die('Ошибка запроса pet_identification: ' . pg_last_error());
        pg_free_result($result);
    }
    //есть ли чип этот?

    //есть ли эта метка?
    // $query='SELECT id FROM public.pet_identification ';
    // $query.='WHERE pet_identification.id_ident_type=8 AND pet_identification.identification_code=\''.$chip.'\' AND id_pet='.$id_pet.'';
    // $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    // $row = pg_fetch_assoc($result);
    // pg_free_result($result);
    if ($label && $id_pet) {
        //добавляем
        $query_insert = 'INSERT INTO pet_identification ';
        $query_insert .= '(identification_code, id_pet, id_ident_type, created_at, updated_at) ';
        $query_insert .= 'VALUES (';
        $query_insert .= '\'' . $label . '\',';
        $query_insert .= '' . $id_pet . ',';
        $query_insert .= '5,';
        $query_insert .= "NOW()::timestamp(0),";#created_at
        $query_insert .= "NOW()::timestamp(0)";#updated_at
        $query_insert .= ') RETURNING id;';
        $result = pg_query($query_insert) or die('Ошибка запроса pet_identification: ' . pg_last_error());
        pg_free_result($result);
    }
    //есть ли эта метка?

    $id_pet_shelter = '';

    if ($id_pet) {
        $arrival_reason = $_GET["arrival_reason"];
        $is_quarantine = $_GET["is_quarantine"];
        $date_from = $_GET["date_from"];
        $date_to = $_GET["date_to"];
        $catching_address = $_GET["catching_address"];
        $catching_video = $_GET["catching_video"];

        ###
        $id_fias_from_table = '';
        if ($catching_address != '') {
            ##################################
            ###поиск fias по текцщим данным###
            ##################################
            $id_fias = str_replace("[ ", "", $catching_address);
            $id_fias = str_replace(" ]", "", $id_fias);
            $fias_tokken = fias_request($configuration);
            $fias_address_ = fias_address($fias_tokken->access_token, $id_fias, $configuration);

            $fias_address = $fias_address_->suggestions[0];

            $query = 'SELECT id FROM fias_addresses WHERE ';
            if ($fias_address->data->house_fias_id) {
                $query .= 'houseguid=\'' . string_formating_for_sql($fias_address->data->house_fias_id) . '\' AND ';
            } else {
                $query .= 'houseguid IS NULL AND ';
            }
            $query .= 'cityguid=\'' . string_formating_for_sql($fias_address->data->region_fias_id) . '\' AND ';
            if ($fias_address->data->street_fias_id) {
                $query .= 'streetguid=\'' . string_formating_for_sql($fias_address->data->street_fias_id) . '\' AND ';
            } else {
                $query .= 'streetguid IS NULL AND ';
            }
            $query .= 'roomguid IS NULL ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $id_fias_from_table = pg_fetch_result($result, 0);
            pg_free_result($result);
            ##################################
            ###поиск fias по текцщим данным###
            ##################################
            if (!$id_fias_from_table) {//fias адрес не найден в таблице
                $street_prefix = "";
                if ($fias_address->data->street_type == 'ш') {
                    $street_prefix = 'шоссе';
                } else {
                    $street_prefix = 'улица';
                }

                $full_address = '';

                if ($fias_address->data->city_type_full) {
                    $full_address = '' . $fias_address->data->region_type_full . ' ' . $fias_address->data->region . ', ' . $fias_address->data->city_type_full . ' ' . $fias_address->data->city . ', ' . $street_prefix . ' ' . $fias_address->data->street . ', дом ' . $fias_address->data->house . '';
                } else {
                    $full_address = '' . $fias_address->data->region_type_full . ' ' . $fias_address->data->region . ', ' . $street_prefix . ' ' . $fias_address->data->street . ', дом ' . $fias_address->data->house . '';
                }

                $lon = $fias_address->data->polygon->coordinates[0][0][0][0];
                $lat = $fias_address->data->polygon->coordinates[0][0][0][1];

                $query = 'INSERT INTO fias_addresses ';
                $query .= '(full_address, lon, lat, region, city, street, house, cityguid, streetguid, houseguid, roomguid, regionguid, created_by, updated_by, created_at, updated_at) ';
                $query .= 'VALUES (';
                $query .= "'" . $full_address . "',";

                if ($lon) {
                    $query .= "'" . $lon . "',";
                } else {
                    $query .= "NULL,";
                }
                if ($lat) {
                    $query .= "'" . $lat . "',";
                } else {
                    $query .= "NULL,";
                }

                $query .= "'" . $fias_address->data->region_type_full . " " . $fias_address->data->region . "',";    //$query.="'город ".$fias_address->data->region."',";

                $query .= "'" . $fias_address->data->city_type_full . " " . $fias_address->data->city . "',";
                $query .= "'" . $street_prefix . " " . $fias_address->data->street . "',";
                $query .= "'дом " . $fias_address->data->house . "',";

                $query .= "'" . $fias_address->data->region_fias_id . "',";
                if ($fias_address->data->street_fias_id) {
                    $query .= "'" . $fias_address->data->street_fias_id . "',";
                } else {
                    $query .= "NULL,";
                }
                if ($fias_address->data->house_fias_id) {
                    $query .= "'" . $fias_address->data->house_fias_id . "',";
                } else {
                    $query .= "NULL,";
                }
                $query .= "NULL,";

                //$query.="77,";//Москва
                $query .= "" . $fias_address->data->region_fns_code . ",";//Остальные

                $query .= "" . $id_user . ",";#created_by
                $query .= "" . $id_user . ",";#updated_by
                $query .= "NOW()::timestamp(0),";#created_at
                $query .= "NOW()::timestamp(0)";#updated_at
                $query .= ') RETURNING id;';

                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                $id_fias_from_table = $row[0];
                pg_free_result($result);
            }
        }
        ###

        $query = 'INSERT INTO shelter_guests (';
        $query .= 'id_organization, ';//приют
        $query .= 'id_pet, ';
        $query .= 'arrival_date, ';
        $query .= 'arrival_reason, ';
        $query .= 'status, ';
        $query .= 'is_quarantine, ';
        $query .= 'quarantine_from, ';
        $query .= 'quarantine_to, ';
        $query .= 'arrival_act_number, ';
        $query .= 'arrival_act_number_date, ';
        $query .= 'arrival_work_order_date, ';
        $query .= 'catching_act_number, ';
        $query .= 'catching_address, ';
        $query .= 'is_catching_video, ';
        $query .= 'catching_video, ';
        $query .= 'created_at, updated_at, created_by, updated_by) ';
        $query .= 'VALUES (';
        $query .= "" . $id_org . ",";
        $query .= "'" . $id_pet . "',";
        $query .= "NOW()::timestamp(0),";#arrival_date
        $query .= "'" . $arrival_reason . "',";
        if ($is_quarantine) {
            $query .= "'QUARANTINE',";
        } else {
            $query .= "'IN_SHELTER',";
        }

        if ($is_quarantine) {
            $query .= "'t',";
        } else {
            $query .= "'f',";
        }

        if ($is_quarantine) {
            $query .= "'" . $date_from . "',";//quarantine_from
            $query .= "'" . $date_to . "',";//quarantine_to
        } else {
            $query .= "NULL,";//quarantine_from
            $query .= "NULL,";//quarantine_to
        }

        $query .= "NULL,";//arrival_act_number
        $query .= "NULL,";//arrival_act_number_date
        $query .= "NULL,";//arrival_work_order_date
        $query .= "NULL,";//catching_act_number

        if ($id_fias_from_table) {
            $query .= "" . $id_fias_from_table . ",";//catching_address
        } else {
            $query .= "NULL,";//catching_address
        }

        if ($catching_video) {//is_catching_video
            $query .= "'t',";
            $query .= "'" . $catching_video . "',";//catching_video
        } else {
            $query .= "'f',";
            $query .= "NULL,";//catching_video
        }

        $query .= "NOW()::timestamp(0),";#created_at
        $query .= "NOW()::timestamp(0),";#updated_at
        $query .= "" . $id_user . ",";#created_by
        $query .= "" . $id_user . "";#updated_by
        $query .= ') RETURNING id;';
        //echo $query;
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id_pet_shelter = $row[0];
        pg_free_result($result);

        shelters_history_event_create('SHELTER', $id_pet, $id_org, $id_user, $id_org, '', 1);

        //установлен ли карантин
        if ($is_quarantine) {
            $quarantine_period = new \DatePeriod(new \DateTime($date_from), new \DateInterval('P1D'), new \DateTime($date_to . ' + 1 day'));
            foreach ($quarantine_period as $day) {
                shelters_quarantine_create($id_pet, $id_org, $id_user, $day->format('Y-m-d'));
            }
            shelters_history_event_create('QUARANTINE', $id_pet, $id_org, $id_user, '', '', 2);
        }


    }

    if ($id_pet_shelter) {
        //добавляем файлы к животному
        $query = 'UPDATE files SET ';
        $query .= 'entity_id=' . $id_pet_shelter . ', ';
        $query .= 'entity_type=\'shelter_guests\', ';
        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE entity_id=' . string_formating_for_sql($temp_pet) . ' AND entity_type=\'shelter_guests_temp\' ';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

        //ищем акты приёма
        $query = 'SELECT files.id AS id_file, files.path, documents.name, documents.number, documents.date, document_types.type, document_types.group, documents.id, documents.protected_at FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($id_pet_shelter) . ' AND entity_type=\'shelter_guests\'';
        $query .= 'AND (document_types.group=\'CATCH\' OR document_types.group=\'COURT_DECISION\' OR document_types.group=\'FOUNDLING\' OR document_types.group=\'OWNER_REFUSAL\') ';
        $query .= 'AND (document_types.type LIKE \'TYPE_ACT_ARRIVE_%\') ';
        $query .= 'ORDER BY documents.date DESC, documents.id ASC ';
        $query .= 'LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_assoc($result);
        $number = '';
        $number = $row['number'];
        $date = $row['date'];
        pg_free_result($result);

        if ($number != '') {
            $query = 'UPDATE shelter_guests SET ';
            $query .= 'arrival_act_number=\'' . $number . '\', ';
            $query .= 'arrival_act_number_date=\'' . $date . '\', ';
            $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . $id_pet_shelter . ' ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);
        }

        //ищем заказы наряда
        $query = 'SELECT files.id AS id_file, files.path, documents.name, documents.number, documents.date, document_types.type, document_types.group, documents.id, documents.protected_at FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($id_pet_shelter) . ' AND entity_type=\'shelter_guests\'';
        $query .= 'AND (document_types.type=\'TYPE_WORK_ORDER\') ';
        $query .= 'ORDER BY documents.date DESC, documents.id ASC ';
        $query .= 'LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_assoc($result);
        $number = '';
        $number = $row['number'];
        $date = $row['date'];
        pg_free_result($result);

        if ($number != '') {
            $query = 'UPDATE shelter_guests SET ';
            $query .= 'arrival_work_order=\'' . $number . '\', ';
            $query .= 'arrival_work_order_date=\'' . $date . '\', ';
            $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . $id_pet_shelter . ' ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);
        }

        //ищем акты отлова
        $query = 'SELECT files.id AS id_file, files.path, documents.name, documents.number, documents.date, document_types.type, document_types.group, documents.id, documents.protected_at FROM files ';
        $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
        $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
        $query .= 'WHERE entity_id=' . string_formating_for_sql($id_pet_shelter) . ' AND entity_type=\'shelter_guests\'';
        $query .= 'AND (document_types.type=\'TYPE_ACT_CATCH\') ';
        $query .= 'ORDER BY documents.date DESC, documents.id ASC ';
        $query .= 'LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_assoc($result);
        $number = '';
        $number = $row['number'];
        $date = $row['date'];
        pg_free_result($result);

        if ($number != '') {
            $query = 'UPDATE shelter_guests SET ';
            $query .= 'catching_act_number=\'' . $number . '\', ';
            $query .= 'catching_act_date=\'' . $date . '\', ';
            $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . $id_pet_shelter . ' ';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);
        }
    }

    $message = '<id_pet>' . $id_pet . '</id_pet>';
    $message .= '<message>pet_added</message>';
    echo xml($message);
}

function shelters_save_pet($configuration)
{
    $pet = $_POST["pet"];
    $tab = $_POST["tab"];

    //свой id
    $id_user = user_id($configuration);
    $id_org = user_org_id($configuration);

    if ($id_user) {
        if ($pet) {
            if ($tab == 'tab1') {

                // поиск значения идентификатора который будет основным
                $keys = array_keys($_POST);
                $mainFlagIndex = array_search('main_flag', $keys);
                if ($mainFlagIndex !== false && $mainFlagIndex > 0) {
                    // Получаем подмассив до $mainIndex
                    $subArray = array_slice($keys, 0, $mainFlagIndex);
                    // Получаем последнее значение в подмассиве
                    $lastValueBeforeMain = end($subArray);
                }
                $mainChipIdentData = isset($lastValueBeforeMain) ? $_POST[$lastValueBeforeMain] : null;

                $chipValues = [];
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'chip') === 0) {
                        $chipValues[] = $value;
                    }
                }

                $name = $_POST["name"];
                $label = $_POST["label"];
                $birthday = $_POST["birthday"];

                $characteristics = $_POST["characteristics"];
                $character = $_POST["character"];

                if (count($chipValues) > 0) {
                    foreach ($chipValues as $chip) {
                        $query = "SELECT pets.id AS id,
                            pets.id_reg_expire_reason AS id_reg_expire_reason,
                            shelter_guests.departure_reason AS departure_reason,
                            shelter_guests.departure_date AS departure_date,
                            pets.reg_expire_date AS reg_expire_date
                        FROM public.pet_identification
                            LEFT JOIN public.pets ON pets.id=pet_identification.id_pet
                            LEFT JOIN public.shelter_guests ON pets.id=shelter_guests.id_pet
                        WHERE pet_identification.id_ident_type=1 AND pet_identification.identification_code='$chip'";
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_assoc($result);
                        pg_free_result($result);

                        if ($row['id'] && $row['id'] != $pet) {
                            //животное присутствует в системе
                            if ($row['shelters_id'] != '' && ($row['departure_reason'] == 'DEATH' || $row['departure_reason'] == 'EUTHANASIA')) {
                                // произошёл падёж животного
                                $message = '<message>chip_error</message>';
                                $message .= '<chip>1</chip>';
                                $message .= '<id_pet>' . $row['id'] . '</id_pet>';
                                $message .= '<text>death</text>';
                                $message .= '<code>' . $chip . '</code>';
                                echo xml($message);

                                return true;
                            }
                            if ($row['reg_expire_date'] != '') {
                                //животное снято с учёта
                                $message = '<message>chip_error</message>';
                                $message .= '<chip>1</chip>';
                                $message .= '<id_pet>' . $row['id'] . '</id_pet>';
                                $message .= '<text>departured</text>';
                                $message .= '<code>' . $chip . '</code>';
                                echo xml($message);

                                return true;
                            }
                            if ($row['shelters_id'] == '') {
                                $message = '<message>chip_error</message>';
                                $message .= '<chip>1</chip>';
                                $message .= '<id_pet>' . $row['id'] . '</id_pet>';
                                $message .= '<text>in_system</text>';
                                $message .= '<code>' . $chip . '</code>';
                                echo xml($message);

                                return true;
                            }
                            if ($row['departure_reason'] != '') {
                                $message = '<message>chip_error</message>';
                                $message .= '<id_pet>' . $row['id'] . '</id_pet>';
                                $message .= '<text>in_system</text>';
                                $message .= '<chip>1</chip>';
                                $message .= '<code>' . $chip . '</code>';

                                echo xml($message);
                                return true;
                            }
                            if ($row['departure_reason'] == '') {
                                $message = '<message>chip_error</message>';
                                $message .= '<id_pet>' . $row['id'] . '</id_pet>';
                                $message .= '<text>now_in_shelter</text>';
                                $message .= '<chip>1</chip>';
                                $message .= '<code>' . $chip . '</code>';
                                echo xml($message);

                                return true;
                            }
                        }
                    }
                }

                //
                $socialized = $_POST["socialized"];
                $specie = $_POST["specie"];
                $breed = $_POST["breed"];
                $sex = $_POST["sex"];
                $color = $_POST["color"];
                $size = $_POST["size"];
                $wool = $_POST["wool"];
                $ear = $_POST["ear"];
                $tail = $_POST["tail"];

                $skill = $_POST["skill"];
                //

                if ($birthday && $specie && $sex) {
                    $birthdayArray = explode(".", $birthday);
                    if (
                        intval($birthdayArray[0]) <= 12 && intval($birthdayArray[0]) >= 1 &&
                        intval($birthdayArray[1]) >= 1900 && intval($birthdayArray[0]) <= 2024
                    ) {
                        $query = 'UPDATE pets SET ';
                        $query .= 'name=\'' . string_formating_for_sql($name) . '\', ';
                        $query .= 'characteristics=\'' . string_formating_for_sql($characteristics) . '\', ';
                        $query .= 'character=\'' . string_formating_for_sql($character) . '\', ';

                        $query .= 'birthday=\'' . string_formating_for_sql($birthdayArray[1]) . '-' . string_formating_for_sql($birthdayArray[0]) . '-15\', ';

//
                        $query .= 'id_species=' . string_formating_for_sql($specie) . ', ';
                        if ($breed) {
                            $query .= 'id_breed=' . string_formating_for_sql($breed) . ', ';
                        } else {
                            $query .= 'id_breed=NULL, ';
                        }
                        $query .= 'sex=\'' . string_formating_for_sql($sex) . '\', ';
                        if ($color) {
                            $query .= 'color_id=' . string_formating_for_sql($color) . ', ';
                        } else {
                            $query .= 'color_id=NULL, ';
                        }
                        if ($size) {
                            $query .= 'size_id=' . string_formating_for_sql($size) . ', ';
                        } else {
                            $query .= 'size_id=NULL, ';
                        }
                        if ($wool) {
                            $query .= 'wool_type_id=' . string_formating_for_sql($wool) . ', ';
                        } else {
                            $query .= 'wool_type_id=NULL, ';
                        }
                        if ($ear) {
                            $query .= 'ear_type_id=' . string_formating_for_sql($ear) . ', ';
                        } else {
                            $ear .= 'ear_type_id=NULL, ';
                        }
                        if ($tail) {
                            $query .= 'tail_type_id=' . string_formating_for_sql($tail) . ', ';
                        } else {
                            $query .= 'tail_type_id=NULL, ';
                        }
                        //

                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . string_formating_for_sql($pet) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);

                        ///////////////////
                        $query = 'UPDATE shelter_guests SET ';
                        if ($socialized == 1) {
                            $query .= 'socialized=\'t\', ';
                        } else {
                            $query .= 'socialized=\'f\', ';
                        }
                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);
                        ////////////////////

                        if (count($chipValues) > 0) {
                            $newChipsArray = array_map(function($value) {
                                return "'$value'";
                            }, $chipValues);

                            $chipValuesString = implode(', ', $newChipsArray);

                            //удаляем из тех что были в бд, если их удалили на фронте
                            $queryChips = 'DELETE FROM pet_identification ';
                            $queryChips .= 'WHERE id_ident_type=1 AND id_pet=' . $pet . ' ';
                            $queryChips .= "AND identification_code not in (".$chipValuesString.")";
                            pg_query($queryChips) or die('Ошибка запроса: ' . pg_last_error());

                            foreach ($chipValues as $chip) {

                                //добавляем или обновляем
                                $query = 'SELECT pet_identification.id FROM public.pet_identification ';
                                $query .= 'LEFT JOIN public.pets ON pets.id=pet_identification.id_pet ';
                                $query .= 'LEFT JOIN public.shelter_guests ON pets.id=shelter_guests.id_pet ';
                                $query .= 'WHERE pet_identification.id_ident_type=1 AND pet_identification.identification_code=\'' . $chip . '\' AND pet_identification.id_pet=' . $pet . '';
                                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                $row_ident = pg_fetch_assoc($result);
                                pg_free_result($result);

                                if ($row_ident['id']) {
                                    $query = 'UPDATE pet_identification SET ';
                                    $query .= 'identification_code=\'' . $chip . '\', ';
                                    $chip == $mainChipIdentData ? $query .= "main_flag = true," : $query .= "main_flag = false,";
                                    $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . ' AND id=' . $row_ident['id'] . '';
                                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                    $row = pg_fetch_row($result);
                                    pg_free_result($result);
                                } else {
                                    $query = 'INSERT INTO pet_identification ';
                                    $query .= '(id_pet, id_ident_type, identification_code, main_flag, identif_comp, identif_org, created_at, created_by, updated_at, updated_by) ';
                                    $query .= 'VALUES (';
                                    $query .= "" . $pet . ",";
                                    $query .= "1,";
                                    $query .= "'" . $chip . "',";
                                    $chip == $mainChipIdentData ? $query .= "bool(1)," : $query .= "bool(0),";
                                    $query .= "NULL,";
                                    $query .= "" . $id_org . ",";
                                    $query .= "NOW()::timestamp(0),";#created_at
                                    $query .= "" . $id_user . ",";#created_by
                                    $query .= "NOW()::timestamp(0),";#updated_at
                                    $query .= "" . $id_user . "";#updated_by
                                    $query .= ') RETURNING id;';
                                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                    $row = pg_fetch_row($result);
                                    $id_rec = $row[0];
                                    pg_free_result($result);
                                }
                            }
                        } else {
                                //удаляем все чипы животного из бд, тк их удалили на фронте
                                $queryChips = 'DELETE FROM pet_identification ';
                                $queryChips .= 'WHERE id_ident_type=1 AND id_pet=' . $pet . ' ';
                                pg_query($queryChips) or die('Ошибка запроса: ' . pg_last_error());
                            }
                        if ($label) {
                            //добавляем или обновляем
                            $query = 'SELECT pet_identification.id FROM public.pet_identification ';
                            $query .= 'LEFT JOIN public.pets ON pets.id=pet_identification.id_pet ';
                            $query .= 'LEFT JOIN public.shelter_guests ON pets.id=shelter_guests.id_pet ';
                            $query .= 'WHERE pet_identification.id_ident_type=8 AND pet_identification.identification_code=\'' . $label . '\' AND pet_identification.id_pet=' . $pet . '';
                            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                            $row_ident = pg_fetch_assoc($result);
                            pg_free_result($result);

                            if ($row_ident['id']) {
                                $query = 'UPDATE pet_identification SET ';
                                $query .= 'identification_code=\'' . $label . '\', ';
                                $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . ' AND id=' . $row_ident['id'] . '';
                                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                $row = pg_fetch_row($result);
                                pg_free_result($result);
                            } else {
                                $query = 'INSERT INTO pet_identification ';
                                $query .= '(id_pet, id_ident_type, identification_code, main_flag, identif_comp, identif_org, created_at, created_by, updated_at, updated_by) ';
                                $query .= 'VALUES (';
                                $query .= "" . $pet . ",";
                                $query .= "5,";
                                $query .= "'" . $label . "',";
                                $query .= "true,";
                                $query .= "NULL,";
                                $query .= "" . $id_org . ",";
                                $query .= "NOW()::timestamp(0),";#created_at
                                $query .= "" . $id_user . ",";#created_by
                                $query .= "NOW()::timestamp(0),";#updated_at
                                $query .= "" . $id_user . "";#updated_by
                                $query .= ') RETURNING id;';
                                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                $row = pg_fetch_row($result);
                                $id_rec = $row[0];
                                pg_free_result($result);
                            }
                        }

                        $query_delete = 'DELETE FROM public.pet_to_skills WHERE id_pet=' . $pet . '';
                        $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                        pg_free_result($result);

                        if ($skill) {
                            $skillArray = explode(",", $skill);

                            for ($i = 0; $i <= count($skillArray); $i++) {
                                if ($skillArray[$i] != '') {
                                    $id_skill = str_replace("[ ", "", $skillArray[$i]);
                                    $id_skill = str_replace(" ]", "", $id_skill);

                                    $query = 'INSERT INTO pet_to_skills ';
                                    $query .= '(id_pet, id_skill) ';
                                    $query .= 'VALUES (';
                                    $query .= "" . $pet . ",";
                                    $query .= "" . $id_skill . ");";
                                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                                    $row = pg_fetch_row($result);
                                    pg_free_result($result);
                                }
                            }
                        }

                        $message = '<id_pet>' . $pet . '</id_pet>';
                        $message .= '<message>pet_saved</message>';
                        echo xml($message);
                    } else {
                        $message = '<message>error_fields</message>';
                        $message .= '<birthday>1</birthday>';
                        echo xml($message);
                    }
                } else {
                    $message = '<message>empty_fields</message>';
                    if (!$birthday) {
                        $message .= '<birthday>1</birthday>';
                    }
                    if (!$specie) {
                        $message .= '<specie>1</specie>';
                    }
                    if (!$sex) {
                        $message .= '<sex>1</sex>';
                    }
                    echo xml($message);
                }
            } else if ($tab == 'tab3_1') {
                $castrated = $_POST["castrated"];
                $early_castrated = $_POST["early_castrated"];
                $castrated_date = $_POST["castrated_date"];
                $castrated_specialist_id = $_POST["castrated_specialist_id"];
                $castrated_org_id = $_POST["castrated_org_id"];

                //стерилизация/кастрация
                $query = 'UPDATE pets SET ';
                if ($castrated) {
                    $query .= 'castrated=\'t\', ';
                } else {
                    $query .= 'castrated=\'f\', ';
                }

                if ($castrated_date) {
                    $query .= 'castrated_date=\'' . $castrated_date . '\', ';
                }

                if ($early_castrated) {
                    $query .= 'early_castrated=\'t\', ';
                } else {
                    $query .= 'early_castrated=\'f\', ';
                }

                $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . string_formating_for_sql($pet) . '';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                pg_free_result($result);

                $message = '<id_pet>' . $pet . '</id_pet>';
                $message .= '<message>pet_saved</message>';
                echo xml($message);
            } else if ($tab == 'tab3_2') {
                $health = $_POST["health"];

                $health_array = json_decode(urldecode("[" . $health . "]"), true);
                debug($health);
                //копим массив
                $not_delete_array = array();

                //echo count($health_array);
                for ($k = 0; $k < count($health_array); $k++) {
                    if ($health_array[$k]['id'] == '') {
                        //новая запись
                        $query = 'INSERT INTO pet_health ';
                        $query .= '(id_pet, status, date, id_organization, temperature, weight, anamnesis, id_specialist, created_at, created_by, updated_at, updated_by) ';
                        $query .= 'VALUES (';
                        $query .= "" . $pet . ",";
                        $query .= "'" . $health_array[$k]['status'] . "',";
                        $query .= "'" . $health_array[$k]['date'] . "',";
                        $query .= "" . $id_org . ",";
                        if ($health_array[$k]['temperature']) {
                            $query .= "'" . $health_array[$k]['temperature'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($health_array[$k]['weight']) {
                            $query .= "'" . $health_array[$k]['weight'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($health_array[$k]['anamnesis']) {
                            $query .= "'" . $health_array[$k]['anamnesis'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($health_array[$k]['specialist']) {
                            $query .= "'" . $health_array[$k]['specialist'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        $query .= "NOW()::timestamp(0),";#created_at
                        $query .= "" . $id_user . ",";#created_by
                        $query .= "NOW()::timestamp(0),";#updated_at
                        $query .= "" . $id_user . "";#updated_by
                        $query .= ') RETURNING id;';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $id_rec = $row[0];
                        pg_free_result($result);

                        array_push($not_delete_array, $id_rec);

                    } else {
                        $query = 'UPDATE pet_health SET ';
                        $query .= 'status=\'' . string_formating_for_sql($health_array[$k]['status']) . '\', ';
                        $query .= 'date=\'' . string_formating_for_sql($health_array[$k]['date']) . '\', ';

                        if ($health_array[$k]['temperature']) {
                            $query .= 'temperature=\'' . string_formating_for_sql($health_array[$k]['temperature']) . '\', ';
                        } else {
                            $query .= 'temperature=NULL, ';
                        }
                        if ($health_array[$k]['weight']) {
                            $query .= 'weight=\'' . string_formating_for_sql($health_array[$k]['weight']) . '\', ';
                        } else {
                            $query .= 'weight=NULL, ';
                        }
                        if ($health_array[$k]['anamnesis']) {
                            $query .= 'anamnesis=\'' . string_formating_for_sql($health_array[$k]['anamnesis']) . '\', ';
                        } else {
                            $query .= 'anamnesis=NULL, ';
                        }
                        if ($health_array[$k]['specialist']) {
                            $query .= 'id_specialist=\'' . string_formating_for_sql($health_array[$k]['specialist']) . '\', ';
                        } else {
                            $query .= 'id_specialist=NULL, ';
                        }
                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . ' AND id=' . string_formating_for_sql($health_array[$k]['id']) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);

                        array_push($not_delete_array, $health_array[$k]['id']);
                    }
                }
                //то что не в массиве удаляем
                //защищенные записи
                $query = 'SELECT pet_health.id, specialists.id_organization AS specialist_org FROM pet_health ';
                $query .= 'LEFT JOIN specialists ON pet_health.id_specialist=specialists.id ' . "\n";
                $query .= 'WHERE id_pet=' . string_formating_for_sql($pet) . ' ';
                $query .= 'ORDER BY pet_health.date ASC';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                while ($row = pg_fetch_assoc($result)) {
                    if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {
                        array_push($not_delete_array, $row['id']);
                    }
                }
                pg_free_result($result);
                //защищенные записи

                $delete_recs = '';
                for ($k = 0; $k < count($not_delete_array); $k++) {
                    $delete_recs .= ' AND id!=' . $not_delete_array[$k] . '';
                }
                $query_delete = 'DELETE FROM pet_health WHERE id_pet=' . string_formating_for_sql($pet) . ' ' . $delete_recs . '';
                debug($query_delete);
                $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);

                $message = '<id_pet>' . $pet . '</id_pet>';
                $message .= '<message>pet_saved</message>';
                echo xml($message);
            } else if ($tab == 'tab4') {
                $rabies_vaccinations = $_POST["rabies_vaccinations"];
                $ectoparasites = $_POST["ectoparasites"];
                $other_vaccinations = $_POST["other_vaccinations"];
                //////////////////////////////////////////
                //////////////////////////////////////////
                //////////////////////////////////////////
                $rabies_vaccinations_array = json_decode(urldecode("[" . $rabies_vaccinations . "]"), true);
                //копим массив
                $not_delete_array = array();
                for ($k = 0; $k < count($rabies_vaccinations_array); $k++) {
                    //drug_name,producer_name считываем из БД название
                    $type_tmc = '';
                    $drug_name = '';
                    $producer_name = '';
                    if ($rabies_vaccinations_array[$k]['drug']) {
                        $query = 'SELECT type, name, produced FROM tmc.tmc WHERE id=\'' . string_formating_for_sql($rabies_vaccinations_array[$k]['drug']) . '\' LIMIT 1';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $type_tmc = $row[0];
                        $drug_name = $row[1];
                        $producer_name = $row[2];
                        pg_free_result($result);
                    }
                    ///

                    if ($rabies_vaccinations_array[$k]['id'] == '') {

                        //type_tmc = vaccine

                        //новая запись
                        $query = 'INSERT INTO pet_rabies_vaccination ';
                        $query .= '(id_pet, date, id_organization, id_vaccine, drug_name, producer_name, type_tmc, batch, id_specialist, expiry_date, valid_until, is_out_org, created_at, created_by, updated_at, updated_by) ';
                        $query .= 'VALUES (';
                        $query .= "" . $pet . ",";
                        $query .= "'" . $rabies_vaccinations_array[$k]['date'] . "',";
                        if ($id_org) {
                            $query .= "" . $id_org . ",";
                        } else {
                            $query .= "NULL,";
                        }

                        if ($rabies_vaccinations_array[$k]['drug']) {
                            $query .= "" . string_formating_for_sql($rabies_vaccinations_array[$k]['drug']) . ",";
                            $query .= "'" . string_formating_for_sql($drug_name) . "',";
                            $query .= "'" . string_formating_for_sql($producer_name) . "',";
                            $query .= "'" . string_formating_for_sql($type_tmc) . "',";
                        } else {
                            $query .= "NULL,";
                            $query .= "NULL,";
                            $query .= "NULL,";
                            $query .= "NULL,";
                        }
                        $query .= "'" . string_formating_for_sql($rabies_vaccinations_array[$k]['batch']) . "',";

                        if ($rabies_vaccinations_array[$k]['specialist']) {
                            $query .= "" . string_formating_for_sql($rabies_vaccinations_array[$k]['specialist']) . ",";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($rabies_vaccinations_array[$k]['expiry_date']) {
                            $query .= "'" . $rabies_vaccinations_array[$k]['expiry_date'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($rabies_vaccinations_array[$k]['valid_until']) {
                            $query .= "'" . $rabies_vaccinations_array[$k]['valid_until'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if (string_formating_for_sql($rabies_vaccinations_array[$k]['is_out_org']) == 1) {
                            $query .= "true,";
                        } else {
                            $query .= "false,";
                        }


                        $query .= "NOW()::timestamp(0),";#created_at
                        $query .= "" . $id_user . ",";#created_by
                        $query .= "NOW()::timestamp(0),";#updated_at
                        $query .= "" . $id_user . "";#updated_by
                        $query .= ') RETURNING id;';

                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $id_rec = $row[0];
                        pg_free_result($result);

                        array_push($not_delete_array, $id_rec);

                    } else {
                        $query = 'UPDATE pet_rabies_vaccination SET ';

                        $query .= 'date=\'' . string_formating_for_sql($rabies_vaccinations_array[$k]['date']) . '\', ';

                        if ($rabies_vaccinations_array[$k]['drug']) {
                            $query .= "id_vaccine=" . string_formating_for_sql($rabies_vaccinations_array[$k]['drug']) . ",";
                            $query .= "drug_name='" . string_formating_for_sql($drug_name) . "',";
                            $query .= "producer_name='" .string_formating_for_sql($producer_name) . "',";
                            $query .= "type_tmc='" . string_formating_for_sql($type_tmc) . "',";
                        } else {
                            $query .= "id_vaccine=NULL,";
                            $query .= "drug_name=NULL,";
                            $query .= "producer_name=NULL,";
                            $query .= "type_tmc=NULL,";
                        }

                        $query .= 'batch=\'' . string_formating_for_sql($rabies_vaccinations_array[$k]['batch']) . '\', ';
                        if ($rabies_vaccinations_array[$k]['valid_until']) {
                            $query .= "valid_until='" . $rabies_vaccinations_array[$k]['valid_until'] . "',";
                        } else {
                            $query .= "valid_until=NULL,";
                        }
                        if ($rabies_vaccinations_array[$k]['expiry_date']) {
                            $query .= "expiry_date='" . $rabies_vaccinations_array[$k]['expiry_date'] . "',";
                        } else {
                            $query .= "expiry_date=NULL,";
                        }
                        if ($rabies_vaccinations_array[$k]['specialist']) {
                            $query .= "id_specialist=" . string_formating_for_sql($rabies_vaccinations_array[$k]['specialist']) . ",";
                        } else {
                            $query .= "id_specialist=NULL,";
                        }

                        if (string_formating_for_sql($rabies_vaccinations_array[$k]['is_out_org']) == 1) {
                            $query .= "is_out_org=true,";
                            $query .= "id_organization=";
                            if ($rabies_vaccinations_array[$k]['organization']) {
                                $query .= "" . string_formating_for_sql($rabies_vaccinations_array[$k]['organization']) . ",";
                            } else {
                                $query .= "NULL,";
                            }
                        } else {
                            $query .= "is_out_org=false,";
                            $query .= "id_organization=";
                            if ($id_org) {
                                $query .= "" . string_formating_for_sql($id_org) . ",";
                            } else {
                                $query .= "NULL,";
                            }
                        }

                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . ' AND id=' . string_formating_for_sql($rabies_vaccinations_array[$k]['id']) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);

                        array_push($not_delete_array, $rabies_vaccinations_array[$k]['id']);
                    }
                }
                //то что не в массиве удаляем
                //защищенные записи
                $query = 'SELECT pet_rabies_vaccination.id, specialists.id_organization AS specialist_org FROM pet_rabies_vaccination ';
                $query .= 'LEFT JOIN specialists ON pet_rabies_vaccination.id_specialist=specialists.id ' . "\n";
                $query .= 'WHERE id_pet=' . string_formating_for_sql($pet) . ' ';
                $query .= 'ORDER BY pet_rabies_vaccination.date ASC';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                while ($row = pg_fetch_assoc($result)) {
                    if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {
                        array_push($not_delete_array, $row['id']);
                    }
                }
                pg_free_result($result);
                //защищенные записи

                $delete_recs = '';
                for ($k = 0; $k < count($not_delete_array); $k++) {
                    $delete_recs .= ' AND id!=' . $not_delete_array[$k] . '';
                }
                $query_delete = 'DELETE FROM pet_rabies_vaccination WHERE id_pet=' . string_formating_for_sql($pet) . ' ' . $delete_recs . '';
                $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
                //////////////////////////////////////////
                //////////////////////////////////////////
                //////////////////////////////////////////
                $other_vaccinations_array = json_decode(urldecode("[" . $other_vaccinations . "]"), true);
                //копим массив
                $not_delete_array = array();
                for ($k = 0; $k < count($other_vaccinations_array); $k++) {
                    //drug_name,producer_name считываем из БД название
                    $type_tmc = '';
                    $drug_name = '';
                    $producer_name = '';
                    if ($other_vaccinations_array[$k]['drug']) {
                        $query = 'SELECT type, name, produced FROM tmc.tmc WHERE id=\'' . string_formating_for_sql($other_vaccinations_array[$k]['drug']) . '\' LIMIT 1';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $type_tmc = $row[0];
                        $drug_name = $row[1];
                        $producer_name = $row[2];
                        pg_free_result($result);
                    }
                    ///

                    if ($other_vaccinations_array[$k]['id'] == '') {
                        //новая запись
                        $query = 'INSERT INTO pet_other_vaccinations ';
                        $query .= '(id_pet, date, id_organization, id_vaccine, drug_name, producer_name, type_tmc, batch, id_specialist, expiry_date, valid_until, is_out_org, created_at, created_by, updated_at, updated_by) ';
                        $query .= 'VALUES (';
                        $query .= "" . $pet . ",";
                        $query .= "'" . $other_vaccinations_array[$k]['date'] . "',";
                        $query .= "" . $id_org . "," ?? 'NULL, ';

                        if ($other_vaccinations_array[$k]['drug']) {
                            $query .= "" . string_formating_for_sql($other_vaccinations_array[$k]['drug']) . ",";
                            $query .= "'" . string_formating_for_sql($drug_name) . "',";
                            $query .= "'" . string_formating_for_sql($producer_name) . "',";
                            $query .= "'" . string_formating_for_sql($type_tmc) . "',";
                        } else {
                            $query .= "NULL,";
                            $query .= "NULL,";
                            $query .= "NULL,";
                            $query .= "NULL,";
                        }

                        $query .= "'" . string_formating_for_sql($other_vaccinations_array[$k]['batch']) . "',";

                        if ($other_vaccinations_array[$k]['specialist']) {
                            $query .= "" . string_formating_for_sql($other_vaccinations_array[$k]['specialist']) . ",";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($other_vaccinations_array[$k]['expiry_date']) {
                            $query .= "'" . $other_vaccinations_array[$k]['expiry_date'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($other_vaccinations_array[$k]['valid_until']) {
                            $query .= "'" . $other_vaccinations_array[$k]['valid_until'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if (string_formating_for_sql($other_vaccinations_array[$k]['is_out_org']) == 1) {
                            $query .= "true,";
                        } else {
                            $query .= "false,";
                        }


                        $query .= "NOW()::timestamp(0),";#created_at
                        $query .= "" . $id_user . ",";#created_by
                        $query .= "NOW()::timestamp(0),";#updated_at
                        $query .= "" . $id_user . "";#updated_by
                        $query .= ') RETURNING id;';

                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $id_rec = $row[0];
                        pg_free_result($result);

                        array_push($not_delete_array, $id_rec);

                    } else {
                        $query = 'UPDATE pet_other_vaccinations SET ';

                        $query .= 'date=\'' . string_formating_for_sql($other_vaccinations_array[$k]['date']) . '\', ';

                        if ($other_vaccinations_array[$k]['drug']) {
                            $query .= "id_vaccine=" . string_formating_for_sql($other_vaccinations_array[$k]['drug']) . ",";
                            $query .= "drug_name='" . string_formating_for_sql($drug_name) . "',";
                            $query .= "producer_name='" . string_formating_for_sql($producer_name) . "',";
                            $query .= "type_tmc='" . string_formating_for_sql($type_tmc) . "',";
                        }

                        $query .= 'batch=\'' . string_formating_for_sql($other_vaccinations_array[$k]['batch']) . '\', ';
                        if ($other_vaccinations_array[$k]['valid_until']) {
                            $query .= "valid_until='" . $other_vaccinations_array[$k]['valid_until'] . "',";
                        } else {
                            $query .= "valid_until=NULL,";
                        }
                        if ($other_vaccinations_array[$k]['expiry_date']) {
                            $query .= "expiry_date='" . $other_vaccinations_array[$k]['expiry_date'] . "',";
                        } else {
                            $query .= "expiry_date=NULL,";
                        }
                        if ($other_vaccinations_array[$k]['specialist']) {
                            $query .= "id_specialist=" . string_formating_for_sql($other_vaccinations_array[$k]['specialist']) . ",";
                        } else {
                            $query .= "id_specialist=NULL,";
                        }

                        if (string_formating_for_sql($other_vaccinations_array[$k]['is_out_org']) == 1) {
                            $query .= "id_organization=";
                            if ($other_vaccinations_array[$k]['organization']) {
                                $query .= "" . string_formating_for_sql($other_vaccinations_array[$k]['organization']) . ",";
                            } else {
                                $query .= "NULL,";
                            }
                        } else {
                            $query .= "is_out_org=false,";
                            $query .= "id_organization=";
                            if ($id_org) {
                                $query .= "" . string_formating_for_sql($id_org) . ",";
                            } else {
                                $query .= "NULL,";
                            }
                        }

                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . ' AND id=' . string_formating_for_sql($other_vaccinations_array[$k]['id']) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);

                        array_push($not_delete_array, $other_vaccinations_array[$k]['id']);
                    }
                }
                //то что не в массиве удаляем
                //защищенные записи
                $query = 'SELECT pet_other_vaccinations.id, specialists.id_organization AS specialist_org FROM pet_other_vaccinations ';
                $query .= 'LEFT JOIN specialists ON pet_other_vaccinations.id_specialist=specialists.id ' . "\n";
                $query .= 'WHERE id_pet=' . string_formating_for_sql($pet) . ' ';
                $query .= 'ORDER BY pet_other_vaccinations.date ASC';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                while ($row = pg_fetch_assoc($result)) {
                    if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {
                        array_push($not_delete_array, $row['id']);
                    }
                }
                pg_free_result($result);
                //защищенные записи

                $delete_recs = '';
                for ($k = 0; $k < count($not_delete_array); $k++) {
                    $delete_recs .= ' AND id!=' . $not_delete_array[$k] . '';
                }
                $query_delete = 'DELETE FROM pet_other_vaccinations WHERE id_pet=' . string_formating_for_sql($pet) . ' ' . $delete_recs . '';
                $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
                //////////////////////////////////////////
                //////////////////////////////////////////
                //////////////////////////////////////////
                $ectoparasites_array = json_decode(urldecode("[" . $ectoparasites . "]"), true);
                //копим массив
                $not_delete_array = array();
                for ($k = 0; $k < count($ectoparasites_array); $k++) {
                    //drug_name,producer_name считываем из БД название
                    $query = 'SELECT type, name, produced FROM tmc.tmc WHERE id=\'' . string_formating_for_sql($ectoparasites_array[$k]['drug']) . '\' LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    $type_tmc = $row[0];
                    $drug_name = $row[1];
                    $producer_name = $row[2];
                    pg_free_result($result);
                    ///

                    if ($ectoparasites_array[$k]['id'] == '') {
                        //новая запись
                        $query = 'INSERT INTO pet_ectoparasites ';
                        $query .= '(id_pet, date, id_organization, id_drug, drug_name, producer_name, dose, id_specialist, date_exp, valid_until, type_tmc, is_out_org, created_at, created_by, updated_at, updated_by) ';
                        $query .= 'VALUES (';
                        $query .= "" . $pet . ",";
                        $query .= "'" . $ectoparasites_array[$k]['date'] . "',";
                        $query .= "" . $id_org . "," ?? 'NULL, ';

                        $query .= "" . string_formating_for_sql($ectoparasites_array[$k]['drug']) . ",";
                        $query .= "'" . string_formating_for_sql($drug_name) . "',";
                        $query .= "'" . string_formating_for_sql($producer_name) . "',";
                        $query .= "'" . string_formating_for_sql($ectoparasites_array[$k]['dose']) . "',";

                        if ($ectoparasites_array[$k]['specialist']) {
                            $query .= "" . string_formating_for_sql($ectoparasites_array[$k]['specialist']) . ",";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($ectoparasites_array[$k]['expiry_date']) {
                            $query .= "'" . $ectoparasites_array[$k]['expiry_date'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        if ($ectoparasites_array[$k]['valid_until']) {
                            $query .= "'" . $ectoparasites_array[$k]['valid_until'] . "',";
                        } else {
                            $query .= "NULL,";
                        }
                        $query .= "'" . $type_tmc . "',";

                        if (string_formating_for_sql($ectoparasites_array[$k]['is_out_org']) == 1) {
                            $query .= "true,";
                        } else {
                            $query .= "false,";
                        }


                        $query .= "NOW()::timestamp(0),";#created_at
                        $query .= "" . $id_user . ",";#created_by
                        $query .= "NOW()::timestamp(0),";#updated_at
                        $query .= "" . $id_user . "";#updated_by
                        $query .= ') RETURNING id;';

                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $id_rec = $row[0];
                        pg_free_result($result);

                        array_push($not_delete_array, $id_rec);

                    } else {
                        $query = 'UPDATE pet_ectoparasites SET ';

                        $query .= 'date=\'' . string_formating_for_sql($ectoparasites_array[$k]['date']) . '\', ';

                        if ($ectoparasites_array[$k]['drug']) {
                            $query .= "id_drug=" . string_formating_for_sql($ectoparasites_array[$k]['drug']) . ",";
                            $query .= "drug_name='" . string_formating_for_sql($drug_name) . "',";
                            $query .= "producer_name='" . string_formating_for_sql($producer_name) . "',";
                            $query .= "type_tmc='" . string_formating_for_sql($type_tmc) . "',";
                        }

                        $query .= 'dose=\'' . string_formating_for_sql($ectoparasites_array[$k]['dose']) . '\', ';
                        if ($ectoparasites_array[$k]['valid_until']) {
                            $query .= "valid_until='" . $ectoparasites_array[$k]['valid_until'] . "',";
                        } else {
                            $query .= "valid_until=NULL,";
                        }
                        if ($ectoparasites_array[$k]['expiry_date']) {
                            $query .= "date_exp='" . $ectoparasites_array[$k]['expiry_date'] . "',";
                        } else {
                            $query .= "date_exp=NULL,";
                        }
                        if ($ectoparasites_array[$k]['specialist']) {
                            $query .= "id_specialist=" . string_formating_for_sql($ectoparasites_array[$k]['specialist']) . ",";
                        } else {
                            $query .= "id_specialist=NULL,";
                        }

                        if (string_formating_for_sql($ectoparasites_array[$k]['is_out_org']) == 1) {
                            $query .= "is_out_org=true,";
                            $query .= "id_organization=" . string_formating_for_sql($ectoparasites_array[$k]['organization']) ?? 'NULL, ';
                        } else {
                            $query .= "is_out_org=false,";
                            $query .= "id_organization=" . string_formating_for_sql($id_org) . "," ?? 'NULL,';
                        }

                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($pet) . ' AND id=' . string_formating_for_sql($ectoparasites_array[$k]['id']) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);

                        array_push($not_delete_array, $ectoparasites_array[$k]['id']);
                    }
                }
                //то что не в массиве удаляем
                //защищенные записи
                $query = 'SELECT pet_ectoparasites.id, specialists.id_organization AS specialist_org FROM pet_ectoparasites ';
                $query .= 'LEFT JOIN specialists ON pet_ectoparasites.id_specialist=specialists.id ' . "\n";
                $query .= 'WHERE id_pet=' . string_formating_for_sql($pet) . ' ';
                $query .= 'ORDER BY pet_ectoparasites.date ASC';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                while ($row = pg_fetch_assoc($result)) {
                    if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {
                        array_push($not_delete_array, $row['id']);
                    }
                }
                pg_free_result($result);
                //защищенные записи

                $delete_recs = '';
                for ($k = 0; $k < count($not_delete_array); $k++) {
                    $delete_recs .= ' AND id!=' . $not_delete_array[$k] . '';
                }
                $query_delete = 'DELETE FROM pet_ectoparasites WHERE id_pet=' . string_formating_for_sql($pet) . ' ' . $delete_recs . '';
                $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
                pg_free_result($result);
                //////////////////////////////////////////
                //////////////////////////////////////////
                //////////////////////////////////////////

                $message = '<id_pet>' . $pet . '</id_pet>';
                $message .= '<message>pet_saved</message>';
                echo xml($message);
            }
        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_departure_pet($configuration)
{
    $id_pet = $_GET["pet"];

    //свой id
    $id_user = user_id($configuration);
    $org_id = 0;

    //сделать проверку на существование

    if ($id_user) {
        if ($id_pet) {
            $departure_reason = $_GET["departure_reason"];
            $departure_date = $_GET["departure_date"];
            $owner_id = $_GET["owner_id"];
            $reason_death = $_GET["reason_death"];
            $euthanasia_reason = $_GET["euthanasia_reason"];
            $euthanasia_specialist = $_GET["euthanasia_specialist"];

            //

            if ($departure_reason && $departure_date) {
                ///////////////////
                $query = 'UPDATE shelter_guests SET ';
                $query .= 'departure_date=\'' . $departure_date . '\', ';


                if ($departure_reason == 'DEATH') {
                    $departure_reason = $reason_death;

                    if ($reason_death == 'EUTHANASIA') {
                        $query .= 'departure_comment=\'' . $euthanasia_reason . '\', ';
                        $query .= 'departure_specialist=\'' . $euthanasia_specialist . '\', ';
                    } else {
                        if (isset($_GET["death_type"]) && $_GET["death_type"]) $query .= "death_reason_id = {$_GET['death_type']}, ";
                    }
                }
                $query .= 'departure_reason=\'' . $departure_reason . '\', ';

                if ($departure_reason == 'RETURNED_TO_OWNER' || $departure_reason == 'RETURNED_TO_NEW_OWNER') {
                    $query .= 'id_owner=\'' . $owner_id . '\', ';
                } else {
                    $query .= 'id_owner=NULL, ';
                }
                $query .= 'status=\'DEPARTURED\', ';
                $query .= 'is_quarantine=\'f\', ';

                $query .= 'quarantine_from=NULL, ';
                $query .= 'quarantine_to=NULL, ';

                $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($id_pet) . '';
                $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                $row = pg_fetch_row($result);
                pg_free_result($result);
                ////////////////////

                $message = '<id_pet>' . $id_pet . '</id_pet>';
                $message .= '<message>pet_departured</message>';

                $options_str = '';

                if ($departure_reason == 'RETURNED_TO_OWNER' || $departure_reason == 'RETURNED_TO_NEW_OWNER') {
                    //ищем имя владельца
                    $query = 'SELECT fullname FROM pet_owners WHERE id=\'' . $owner_id . '\' LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    $fullname = $row[0];
                    pg_free_result($result);

                    // проверка на то что животное уже возвращалось этому владельцу
                    $query = 'SELECT id FROM pets_to_owner WHERE id_owner_type = 1 AND id_owner=\'' . $owner_id . '\' AND id_pet=\'' . $id_pet . '\'LIMIT 1';
                    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    $row = pg_fetch_row($result);
                    $relation_exist = $row[0];
                    pg_free_result($result);

                    if (!$relation_exist) {
                        #СОЕДИНЯЕМ ЖИВОТНОЕ И ВЛАДЕЛЬЦА
                        $query = 'INSERT INTO pets_to_owner ';
                        $query .= '(id_pet, id_owner, id_owner_type) ';
                        $query .= 'VALUES (';
                        $query .= "" . $id_pet . ",";
                        $query .= "" . $owner_id . ",";
                        $query .= "1";#ВЛАДЕЛЕЦ/ПРЕДСТАВИТЕЛЬ
                        $query .= ');';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        pg_free_result($result);
                    }

                    if ($departure_reason == 'RETURNED_TO_OWNER') {
                        $options_str = 'Возврат прежнему владельцу ' . $fullname . '';
                    } else {
                        $options_str = 'Передача новому владельцу ' . $fullname . '';
                    }
                } else {
                    if ($departure_reason == 'ESCAPE') {
                        $options_str = 'Побег';
                    }
                    if ($departure_reason == 'DEATH' || $departure_reason == 'EUTHANASIA') {
                        if ($reason_death == 'DEATH') {
                            $options_str = 'Падёж';
                        } elseif ($reason_death == 'EUTHANASIA') {
                            $options_str = 'Эвтаназия';
                        }

                        $query = "SELECT id FROM reg_expire_reasons WHERE tech_name='DEATH' LIMIT 1";
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        $reg_expire_reason = $row[0];
                        pg_free_result($result);

                        $query = 'UPDATE pets SET ';
                        $query .= 'reg_expire_date=NOW()::timestamp::date, ';
                        $query .= 'id_reg_expire_reason=' . $reg_expire_reason . ', ';
                        $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id=' . string_formating_for_sql($id_pet) . '';
                        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                        $row = pg_fetch_row($result);
                        pg_free_result($result);
                    }
                }

                $id_org = user_org_id($configuration);
                shelters_history_event_create('DEPARTURE', $id_pet, $id_org, $id_user, '', $options_str);

                echo xml($message);
            } else {
                $message = '<message>empty_fields</message>';
                if (!$date) {
                    $message .= '<date>1</date>';
                }
                echo xml($message);
            }
        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_return_pet($configuration)
{
    $id_pet = $_GET["pet"];

    //свой id
    $id_user = user_id($configuration);

    if ($id_user) {
        if ($id_pet) {
            $query = 'UPDATE shelter_guests SET ';
            $query .= 'departure_date=NULL, ';
            $query .= 'departure_reason=NULL, ';
            $query .= 'status=\'QUARANTINE\', ';
            $query .= 'prev_status=\'DEPARTURED\', ';
            $query .= 'is_quarantine=\'t\', ';
            $query .= 'quarantine_from=NULL, ';//считаем новые даты?
            $query .= 'quarantine_to=NULL, ';//
            $query .= 'updated_by=' . $id_user . ', updated_at=NOW()::timestamp(0) WHERE id_pet=' . string_formating_for_sql($id_pet) . '';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            pg_free_result($result);
            ////////////////////
            $message = '<id_pet>' . $id_pet . '</id_pet>';
            $message .= '<message>pet_returned</message>';
            echo xml($message);

            $id_org = user_org_id($configuration);
            shelters_history_event_create('RETURN_PET', $id_pet, $id_org, $id_user, '', '');


            shelters_history_event_create('QUARANTINE', $id_pet, $id_org, $id_user, '', '', 1);
        } else {
            echo xml('<message>invalid_pet_id</message>');
        }
    } else {
        echo xml('<message>invalid_user_id</message>');
    }
}

function shelters_quarantine_create($pet, $org, $user, $date)
{
    $query = 'INSERT INTO pet_health ';
    $query .= '(id_pet, status, date, id_organization, created_at, created_by, updated_at, updated_by) ';
    $query .= 'VALUES (';
    $query .= "" . $pet . ",";
    $query .= "'QUARANTINE',";
    $query .= "'" . $date . "',";
    $query .= "" . $org . ",";
    $query .= "NOW()::timestamp(0),";#created_at
    $query .= "" . $user . ",";#created_by
    $query .= "NOW()::timestamp(0),";#updated_at
    $query .= "" . $user . "";#updated_by
    $query .= ') RETURNING id;';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_row($result);
    $id_rec = $row[0];
    pg_free_result($result);

    return $id_rec;
}

function shelters_history_event_create($code, $pet, $org, $user, $options = NULL, $options_str = NULL, $delay = NULL)
{
    if ($pet && $code) {
        $query = 'INSERT INTO pet_history ';
        $query .= '(id_pet, event, options, id_organization, options_str, created_at, created_by) ';
        $query .= 'VALUES (';
        $query .= "" . $pet . ",";
        $query .= "'" . $code . "',";
        if ($options) {
            $query .= "" . $options . ",";
        } else {
            $query .= "NULL,";
        }
        $query .= "" . $org . ",";
        if ($options_str) {
            $query .= "'" . $options_str . "',";
        } else {
            $query .= "NULL,";
        }
        if ($delay) {
            $query .= "NOW()::timestamp(0) + INTERVAL '" . $delay . "SECOND',";#created_at
        } else {
            $query .= "NOW()::timestamp(0),";#created_at
        }
        $query .= "" . $user . "";#created_by
        $query .= ') RETURNING id;';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $id_event = $row[0];
        pg_free_result($result);
        return $id_event;
    } else {
        return false;
    }
}

function shelters_history_event($code, $options = NULL, $options_str = NULL, $id_pet = NULL)
{
    if ($code == 'REGISTERED') {
        return 'Зарегистрировано в системе';
    } else if ($code == 'SHELTER') {


        $query = 'SELECT short_name FROM organizations WHERE id=\'' . $options . '\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $title = $row[0];
        pg_free_result($result);

        return 'Поступило в приют «' . $title . '»';

    } else if ($code == 'QUARANTINE') {
        return 'Помещено в карантин';

        // }else if($code == 'QUARANTINE'){
        // 	return 'Помещено в стационар';
        // }else if($code == 'QUARANTINE'){
        // 	return 'Помещено в изолятор';

    } else if ($code == 'QUARANTINE_OTHER') {
        return 'Карантин продлён';
    } else if ($code == 'QUARANTINE_END') {
        return 'Карантин закончен';
    } else if ($code == 'CASTRATED') {
        return 'Стерилизация / кастрация';
    } else if ($code == 'MAINTENANCE') {
        return 'Размещено в приюте на содержание';
    } else if ($code == 'AVIARY') {
        if ($options) {
            $query = 'SELECT title FROM aviary WHERE id=\'' . $options . '\' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $title = $row[0];
            pg_free_result($result);

            return 'Помещено в вольер «' . $title . '»';
        } else if ($options == 0) {
            return 'Выпущено из вольера';
        }
    } else if ($code == 'VACCINATION') {
        return 'Вакцинация';
    } else if ($code == 'TREATMENT') {
        return 'Обработка';
    } else if ($code == 'RETURN_PET') {
        return 'Возврат в приют';
    } else if ($code == 'NEW_OWNER') {
        return 'Новый хозяин';
    } else if ($code == 'EUTHANAZIA') {
        return 'Эвтаназия';
    } else if ($code == 'DEATH') {
        return 'Падёж';
    } else if ($code == 'ESCAPE') {
        return 'Побег';
    } else if ($code == 'DEPARTURE') {
        if ($options_str) {
            return 'Выбытие «' . $options_str . '»';
        } else {
            return 'Выбытие';
        }
    } else if ($code == 'DEREGISTERED') {
        return 'Снято с учёта';
    } else {
        return $code;
    }
}

function shelters_information_xml($configuration)
{
    $id = $_GET["id"];

    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<xml>';
    echo '<rec>';

    $query = 'SELECT ';

    $query .= 'chip.identification_code AS chip_title, ';
    $query .= 'label.identification_code AS label_title, ';
    //
    $query .= 'pet_ref_color.title AS color_title,';
    $query .= 'pet_ref_color.id AS color_id,';
    //
    $query .= 'pet_ref_wool_type.title AS wool_title,';
    $query .= 'pet_ref_wool_type.id AS wool_id,';
    //
    $query .= 'pet_ref_tail_type.title AS tail_title,';
    $query .= 'pet_ref_tail_type.id AS tail_id,';
    //
    $query .= 'pet_ref_size.title AS size_title,';
    $query .= 'pet_ref_size.id AS size_id,';
    //
    $query .= 'pet_ref_ear_type.title AS ear_title,';
    $query .= 'pet_ref_ear_type.id AS ear_id,';
    //

    //
    $query .= 'shelter_guests.arrival_reason AS arrival_reason, ';
    $query .= 'shelter_guests.arrival_date AS arrival_date, ';
    $query .= 'shelter_guests.arrival_act_number AS arrival_act_number, ';
    $query .= 'shelter_guests.arrival_act_number_date AS arrival_act_number_date, ';
    //
    $query .= 'shelter_guests.arrival_work_order AS arrival_work_order, ';
    $query .= 'shelter_guests.arrival_work_order_date AS arrival_work_order_date, ';
    //
    $query .= 'shelter_guests.catching_act_number AS catching_act_number, ';
    $query .= 'shelter_guests.catching_act_date AS catching_act_date, ';

    $query .= 'shelter_guests.catching_address AS catching_address, ';
    $query .= 'fias_addresses.full_address AS catching_address_, ';

    $query .= 'shelter_guests.is_catching_video AS is_catching_video, ';
    $query .= 'shelter_guests.catching_video AS catching_video, ';
    //
    $query .= 'shelter_guests.departure_reason AS departure_reason, ';
    $query .= 'shelter_guests.departure_date AS departure_date, ';
    $query .= 'shelter_guests.departure_comment AS departure_comment, ';
    $query .= 'shelter_guests.departure_specialist AS departure_specialist, ';
    //
    $query .= 'max(prv.valid_until) AS vac_date, shelter_guests.*, areas.name AS area, shelters.short_name AS shelter, managing.short_name AS managing, pets.id AS pet_id, ';
    $query .= 'shelter_guests.id_organization AS id_org, ';

    //вольер
    $query .= 'aviary.title AS aviary_title, ';
    $query .= 'aviary.id AS aviary_id, ';
    //вольер

    //порода/вид
    $query .= 'species.name AS species_name, ';
    $query .= 'species.id AS species_id, ';
    $query .= 'breeds.name AS breeds_name, ';
    $query .= 'breeds.id AS breeds_id, ';
    //порода/вид

    $query .= 'pets.birthday AS birthday, ';
    $query .= 'pets.characteristics AS characteristics, ';
    $query .= 'pets.early_castrated AS early_castrated, ';
    $query .= 'pets.castrated AS castrated, ';
    $query .= 'pets.sex AS sex, ';
    $query .= 'pets.character AS character, ';
    $query .= 'pets.castrated_date AS castrated_date, ';
    $query .= 'pets.name ';

    $query .= 'FROM shelter_guests ';
    // $query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';

    $query .= 'LEFT JOIN public.fias_addresses ON shelter_guests.catching_address=fias_addresses.id ';#адрес

    $query .= 'LEFT JOIN public.aviary ON aviary.id=shelter_guests.aviary_id ';
    $query .= 'LEFT JOIN public.pets ON pets.id=shelter_guests.id_pet ';
    $query .= 'LEFT JOIN public.species ON species.id=pets.id_species ';
    $query .= 'LEFT JOIN public.breeds ON breeds.id=pets.id_breed ';

    $query .= 'LEFT JOIN public.pet_ref_color ON pet_ref_color.id=pets.color_id ';
    $query .= 'LEFT JOIN public.pet_ref_wool_type ON pet_ref_wool_type.id=pets.wool_type_id ';
    $query .= 'LEFT JOIN public.pet_ref_tail_type ON pet_ref_tail_type.id=pets.tail_type_id ';
    $query .= 'LEFT JOIN public.pet_ref_size ON pet_ref_size.id=pets.size_id ';
    $query .= 'LEFT JOIN public.pet_ref_ear_type ON pet_ref_ear_type.id=pets.ear_type_id ';

    // $query.='LEFT JOIN public.pet_identification ON (pet_identification.id_pet=pets.id AND id_ident_type=8) ';
    $query .= 'LEFT JOIN public.pet_identification AS chip ON chip.id_pet=pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
    $query .= 'LEFT JOIN public.pet_identification AS label ON label.id_pet=pets.id AND label.id = (SELECT max(l1.id) FROM public.pet_identification l1 WHERE label.id_pet = l1.id_pet AND l1.id_ident_type=8) ';


    $query .= 'LEFT JOIN organizations AS shelters ON shelter_guests.id_organization=shelters.id ';
    $query .= 'LEFT JOIN areas AS areas ON areas.id=shelters.id_area ';
    $query .= 'LEFT JOIN organizations AS managing ON managing.id=shelters.managing_organization_id ';

    $query .= 'LEFT JOIN public.pet_rabies_vaccination AS prv ON prv.id_pet=pets.id ';

    $query .= 'WHERE pets.id=' . string_formating_for_sql($id) . ' ';

    $query .= 'GROUP BY ';

    $query .= 'fias_addresses.full_address, ';

    $query .= 'chip.identification_code, label.identification_code, ';

    $query .= 'pet_ref_color.title, pet_ref_color.id,';
    $query .= 'pet_ref_wool_type.title, pet_ref_wool_type.id,';
    $query .= 'pet_ref_tail_type.title, pet_ref_tail_type.id,';
    $query .= 'pet_ref_size.title, pet_ref_size.id,';
    $query .= 'pet_ref_ear_type.title, pet_ref_ear_type.id,';

    $query .= 'areas.name, shelter_guests.id, shelters.short_name, managing.short_name, ';
    $query .= 'pets.characteristics, pets.character, pets.castrated, pets.early_castrated, pets.sex, pets.id, ';
    $query .= 'aviary.title, aviary.id, ';
    $query .= 'species.name, breeds.name, species.id, breeds.id ';
    #echo $query;

    $id_shelters_guest = 0;

    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $row = pg_fetch_assoc($result);

    $id_shelters_guest = $row['id'];
    echo '<rec_id>' . $row['id'] . '</rec_id>';
    echo '<rec_organization>' . user_org_title($configuration) . '</rec_organization>';

    echo '<rec_name>' . $row['name'] . '</rec_name>';
    echo '<rec_status>' . $row['status'] . '</rec_status>';

    echo '<rec_managing>' . $row['managing'] . '</rec_managing>';
    echo '<rec_shelter>' . $row['shelter'] . '</rec_shelter>';
    echo '<rec_vac_date>' . $row['vac_date'] . '</rec_vac_date>';

    echo '<rec_aviary_title>' . $row['aviary_title'] . '</rec_aviary_title>';
    echo '<rec_aviary_id>' . $row['aviary_id'] . '</rec_aviary_id>';

    echo '<rec_species_title>' . $row['species_name'] . '</rec_species_title>';
    echo '<rec_species_id>' . $row['species_id'] . '</rec_species_id>';

    echo '<rec_breeds_title>' . $row['breeds_name'] . '</rec_breeds_title>';
    echo '<rec_breeds_id>' . $row['breeds_id'] . '</rec_breeds_id>';

    if ($row['birthday'] != '') {
        if ($row['birthday'] && $birthday = \DateTime::createFromFormat('Y-m-d', $row['birthday'])) {
            $birthday_interval = (new DateTime())->diff($birthday);
            echo '<rec_age>' . $birthday_interval->y . ' г. ' . $birthday_interval->m . ' мес.</rec_age>';
        }
        $birthday_ = \DateTime::createFromFormat('Y-m-d', $row['birthday']);
        echo '<rec_birthday>' . $birthday_->format('m.Y') . '</rec_birthday>';
    } else {
        echo '<rec_age></rec_age>';
        echo '<rec_birthday></rec_birthday>';
    }

    //
    echo '<rec_color_title>' . $row['color_title'] . '</rec_color_title>';
    echo '<rec_color_id>' . $row['color_id'] . '</rec_color_id>';
    echo '<rec_wool_title>' . $row['wool_title'] . '</rec_wool_title>';
    echo '<rec_wool_id>' . $row['wool_id'] . '</rec_wool_id>';
    echo '<rec_tail_title>' . $row['tail_title'] . '</rec_tail_title>';
    echo '<rec_tail_id>' . $row['tail_id'] . '</rec_tail_id>';

    echo '<rec_size_title>' . $row['size_title'] . '</rec_size_title>';
    echo '<rec_size_id>' . $row['size_id'] . '</rec_size_id>';

    echo '<rec_ear_title>' . $row['ear_title'] . '</rec_ear_title>';
    echo '<rec_ear_id>' . $row['ear_id'] . '</rec_ear_id>';
    //
    echo '<rec_chip>' . $row['chip_title'] . '</rec_chip>';
    echo '<rec_label>' . $row['label_title'] . '</rec_label>';
    //

    echo '<rec_characteristics>' . $row['characteristics'] . '</rec_characteristics>';
    echo '<rec_character>' . $row['character'] . '</rec_character>';
    echo '<rec_castrated_date>' . $row['castrated_date'] . '</rec_castrated_date>';
    echo '<rec_sex>' . $row['sex'] . '</rec_sex>';

    echo '<rec_castrated>' . $row['castrated'] . '</rec_castrated>';
    echo '<rec_early_castrated>' . $row['early_castrated'] . '</rec_early_castrated>';

    echo '<rec_arrival_reason>' . $row['arrival_reason'] . '</rec_arrival_reason>';
    echo '<rec_arrival_date>' . $row['arrival_date'] . '</rec_arrival_date>';

    echo '<rec_arrival_act_number>' . $row['arrival_act_number'] . '</rec_arrival_act_number>';
    echo '<rec_arrival_act_number_date>' . $row['arrival_act_number_date'] . '</rec_arrival_act_number_date>';

    echo '<rec_arrival_work_order>' . $row['arrival_work_order'] . '</rec_arrival_work_order>';
    echo '<rec_arrival_work_order_date>' . $row['arrival_work_order_date'] . '</rec_arrival_work_order_date>';

    echo '<rec_catching_act_number>' . $row['catching_act_number'] . '</rec_catching_act_number>';
    echo '<rec_catching_act_date>' . $row['catching_act_date'] . '</rec_catching_act_date>';
    echo '<rec_catching_address>' . $row['catching_address_'] . '</rec_catching_address>';
    echo '<rec_is_catching_video>' . $row['is_catching_video'] . '</rec_is_catching_video>';
    echo '<rec_catching_video>' . $row['catching_video'] . '</rec_catching_video>';

    echo '<rec_departure_date>' . $row['departure_date'] . '</rec_departure_date>';
    echo '<rec_departure_comment>' . $row['departure_comment'] . '</rec_departure_comment>';
    echo '<rec_departure_reason>' . $row['departure_reason'] . '</rec_departure_reason>';

    $not_editable = 0;
    if ($row['departure_reason'] != '') {
        $not_editable = 1;
    }

    echo '<rec_departure_specialist>' . $row['departure_specialist'] . '</rec_departure_specialist>';

    echo '<rec_socialized>' . $row['socialized'] . '</rec_socialized>';

    pg_free_result($result);


    $id_org = user_org_id($configuration);
    $title_org = user_org_title($configuration);
    echo '</rec>';

    //CHIPS
    echo '<chip_data>';
    $query = 'SELECT identification_code as chip, main_flag ';
    $query .= 'FROM pet_identification ';
    $query .= 'WHERE pet_identification.id_pet = ' . string_formating_for_sql($id) . ' ';
    $query .= 'AND pet_identification.id_ident_type = 1 ';
    $query .= 'ORDER BY main_flag DESC LIMIT 2 ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<chip>' . $row['chip'] . '</chip>';
        echo '<main_flag>' . $row['main_flag'] . '</main_flag>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</chip_data>';
    //CHIPS

    //RABIES
    echo '<rabies_vaccinations>';
    //, organizations.name AS organization, users.fullname
    $query = 'SELECT pet_rabies_vaccination.*, users.fullname AS specialist_name, ';
    $query .= 'pet_rabies_vaccination.id_specialist AS specialist_id, pet_rabies_vaccination.id_organization AS specialist_org, ';
    $query .= 'organizations.short_name AS organization, ';
    $query .= 'organizations.id AS organization_id, ';
    $query .= 'outside_org.name AS organization_out, ';
    $query .= 'outside_org.id AS organization_out_id ';
    $query .= 'FROM pet_rabies_vaccination ';
    $query .= 'LEFT JOIN specialists ON pet_rabies_vaccination.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'LEFT JOIN organizations ON pet_rabies_vaccination.id_organization=organizations.id ';
    $query .= 'LEFT JOIN outside_org ON pet_rabies_vaccination.id_organization=outside_org.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id) . ' ';
    $query .= 'ORDER BY pet_rabies_vaccination.date DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';

        echo '<id>' . $row['id'] . '</id>';

        echo '<drug_name>' . string_formating_for_xml($row['drug_name']) . '</drug_name>';
        echo '<drug_id>' . string_formating_for_xml($row['id_vaccine']) . '</drug_id>';
        echo '<producer_name>' . string_formating_for_xml($row['producer_name']) . '</producer_name>';
        echo '<batch>' . string_formating_for_xml($row['batch']) . '</batch>';
        echo '<production_date>' . string_formating_for_xml($row['production_date']) . '</production_date>';
        echo '<expiry_date>' . $row['expiry_date'] . '</expiry_date>';
        echo '<valid_until>' . $row['valid_until'] . '</valid_until>';
        echo '<date>' . $row['date'] . '</date>';

        echo '<organization_name>' . $title_org . '</organization_name>';

        if ($row['is_out_org'] == 't') {
            echo '<specialist_id></specialist_id>';
            echo '<specialist_name></specialist_name>';
            echo '<is_out_org>1</is_out_org>';
            echo '<protected>0</protected>';
            echo '<organization>' . $row['organization_out'] . '</organization>';
            echo '<organization_id>' . $row['organization_out_id'] . '</organization_id>';
        } else {
            echo '<specialist_id>' . $row['specialist_id'] . '</specialist_id>';
            echo '<specialist_name>' . $row['specialist_name'] . '</specialist_name>';
            echo '<specialist_org>' . $row['specialist_org'] . '</specialist_org>';

            echo '<is_out_org>0</is_out_org>';

            if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {

                echo '<protected>1</protected>';
            } else {
                echo '<protected>0</protected>';
            }

            echo '<organization>' . $row['organization'] . '</organization>';
            echo '<organization_id>' . $row['organization_id'] . '</organization_id>';
        }

        echo '</rec>';
    }
    pg_free_result($result);
    echo '</rabies_vaccinations>';
    //RABIES

    //OTHER
    echo '<other_vaccinations>';
    $query = 'SELECT pet_other_vaccinations.*, users.fullname AS specialist_name, ';
    $query .= 'pet_other_vaccinations.id_specialist AS specialist_id, pet_other_vaccinations.id_organization AS specialist_org, ';
    $query .= 'organizations.short_name AS organization, ';
    $query .= 'organizations.id AS organization_id, ';
    $query .= 'outside_org.name AS organization_out, ';
    $query .= 'outside_org.id AS organization_out_id ';
    $query .= 'FROM pet_other_vaccinations ';
    $query .= 'LEFT JOIN specialists ON pet_other_vaccinations.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'LEFT JOIN organizations ON pet_other_vaccinations.id_organization=organizations.id ';
    $query .= 'LEFT JOIN outside_org ON pet_other_vaccinations.id_organization=outside_org.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id) . ' ';
    $query .= 'ORDER BY pet_other_vaccinations.date DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<drug_name>' . string_formating_for_xml($row['drug_name']) . '</drug_name>';
        echo '<drug_id>' . $row['id_vaccine'] . '</drug_id>';
        echo '<producer_name>' . string_formating_for_xml($row['producer_name']) . '</producer_name>';
        echo '<batch>' . $row['batch'] . '</batch>';
        echo '<production_date>' . $row['production_date'] . '</production_date>';
        echo '<expiry_date>' . $row['expiry_date'] . '</expiry_date>';
        echo '<valid_until>' . $row['valid_until'] . '</valid_until>';
        echo '<date>' . $row['date'] . '</date>';

        echo '<organization_name>' . $title_org . '</organization_name>';

        if ($row['is_out_org'] == 't') {
            echo '<specialist_id></specialist_id>';
            echo '<specialist_name></specialist_name>';
            echo '<is_out_org>1</is_out_org>';
            echo '<protected>0</protected>';
            echo '<organization>' . $row['organization_out'] . '</organization>';
            echo '<organization_id>' . $row['organization_out_id'] . '</organization_id>';
        } else {
            echo '<specialist_id>' . $row['specialist_id'] . '</specialist_id>';
            echo '<specialist_name>' . $row['specialist_name'] . '</specialist_name>';
            echo '<specialist_org>' . $row['specialist_org'] . '</specialist_org>';

            echo '<is_out_org>0</is_out_org>';
            if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {
                echo '<protected>1</protected>';
            } else {
                echo '<protected>0</protected>';
            }

            echo '<organization>' . $row['organization'] . '</organization>';
            echo '<organization_id>' . $row['organization_id'] . '</organization_id>';
        }

        echo '</rec>';
    }
    pg_free_result($result);
    echo '</other_vaccinations>';
    //OTHER

    //skills
    echo '<skills>';
    $query = 'SELECT pet_ref_skill.id, pet_ref_skill.title FROM pet_to_skills ';
    $query .= 'LEFT JOIN pet_ref_skill ON pet_to_skills.id_skill=pet_ref_skill.id ';
    $query .= 'WHERE pet_to_skills.id_pet=' . string_formating_for_sql($id) . '';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<title>' . $row['title'] . '</title>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</skills>';
    //skills

    //ectoparasites
    echo '<ectoparasites>';
    $query = 'SELECT pet_ectoparasites.*, users.fullname AS specialist_name, ';
    $query .= 'organizations.short_name AS organization, ';
    $query .= 'organizations.id AS organization_id, ';
    $query .= 'outside_org.name AS organization_out, ';
    $query .= 'outside_org.id AS organization_out_id, ';
    $query .= 'pet_ectoparasites.id_specialist AS specialist_id, specialists.id_organization AS specialist_org ';
    $query .= 'FROM pet_ectoparasites ';

    $query .= 'LEFT JOIN organizations ON pet_ectoparasites.id_organization=organizations.id ';
    $query .= 'LEFT JOIN outside_org ON pet_ectoparasites.id_organization=outside_org.id ';

    $query .= 'LEFT JOIN specialists ON pet_ectoparasites.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id) . ' ';

    $query .= 'ORDER BY pet_ectoparasites.date DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<drug_name>' . string_formating_for_xml($row['drug_name']) . '</drug_name>';
        echo '<drug_id>' . $row['id_drug'] . '</drug_id>';
        echo '<producer_name>' . string_formating_for_xml($row['producer_name']) . '</producer_name>';
        echo '<dose>' . $row['dose'] . '</dose>';
        echo '<date_exp>' . $row['date_exp'] . '</date_exp>';
        echo '<date>' . $row['date'] . '</date>';
        echo '<valid_until>' . $row['valid_until'] . '</valid_until>';
        echo '<specialist_id>' . $row['specialist_id'] . '</specialist_id>';
        echo '<specialist_org>' . $row['specialist_org'] . '</specialist_org>';

        echo '<organization_name>' . $title_org . '</organization_name>';
        if ($row['is_out_org'] == 't') {
            echo '<protected>1</protected>';
            echo '<specialist_name></specialist_name>';
            echo '<organization>' . $row['organization_out'] . '</organization>';
            echo '<organization_id>' . $row['organization_out_id'] . '</organization_id>';
        } else {
            if ($row['organization_id'] && $row['organization_id'] != $id_org) {
                echo '<protected>1</protected>';
            } else {
                echo '<protected>0</protected>';
            }
            echo '<specialist_name>' . $row['specialist_name'] . '</specialist_name>';
            echo '<organization>' . $row['organization'] . '</organization>';
            echo '<organization_id>' . $row['organization_id'] . '</organization_id>';
        }
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</ectoparasites>';
    //ectoparasites

    //HISTORY
    echo '<history>';
    $query = 'SELECT pet_history.*, organizations.name AS organization, users.fullname AS user FROM pet_history ';
    $query .= 'LEFT JOIN users ON pet_history.created_by=users.id ';
    $query .= 'LEFT JOIN organizations ON pet_history.id_organization=organizations.id ';

    $query .= 'WHERE id_pet=' . string_formating_for_sql($id) . ' ';

    $query .= 'ORDER BY pet_history.created_at DESC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<event>';
        echo '<title>' . shelters_history_event($row['event'], $row['options'], $row['options_str']) . '</title>';
        echo '<datetime>' . $row['created_at'] . '</datetime>';
        echo '<user>' . $row['user'] . '</user>';
        echo '<organization>' . $row['organization'] . '</organization>';

        echo '</event>';
    }
    pg_free_result($result);
    echo '</history>';
    //HISTORY

    //HEALTH
    echo '<health>';
    //

    $anamnesis_array = array();
    $query = 'SELECT id, title FROM pet_ref_anamnesis ';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        array_push($anamnesis_array, array('id' => $row['id'], 'title' => $row['title']));
    }
    pg_free_result($result);

    $query = 'SELECT pet_health.*, users.fullname AS specialist_name, pet_health.id_specialist AS specialist_id, specialists.id_organization AS specialist_org FROM pet_health ';
    $query .= 'LEFT JOIN specialists ON pet_health.id_specialist=specialists.id ' . "\n";
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE id_pet=' . string_formating_for_sql($id) . ' ';
    $query .= 'ORDER BY pet_health.date ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';

        echo '<id>' . $row['id'] . '</id>';
        echo '<status>' . $row['status'] . '</status>';
        echo '<date>' . $row['date'] . '</date>';
        echo '<temperature>' . $row['temperature'] . '</temperature>';
        echo '<anamnesis>';

        if (strripos($row['anamnesis'], '[ ') >= 0) {
            $anamnesis_arr = [];
            $anamnesisArray = explode(" ],[ ", $row['anamnesis']);
            for ($i = 0; $i <= count($anamnesisArray); $i++) {
                if ($anamnesisArray[$i] != '') {
                    $id_ = str_replace("[ ", "", $anamnesisArray[$i]);
                    $id_ = str_replace(" ]", "", $id_);
                    if (strripos($id_, '``text``')) {
                        $id_ = str_replace('{``text``: ', "", $id_);
                        $id_ = str_replace("``", "", $id_);
                        $id_ = str_replace("{", "", $id_);
                        $id_ = str_replace("}", "", $id_);
                        $anamnesis_arr[] = $id_;
                    } else {
                        $anamnesis_arr[] = $id_;
                    }
                    echo '<anamnesis_rec>' . implode(', ', $anamnesis_arr) . '</anamnesis_rec>';
                }
            }
        } else {
            echo '<anamnesis_rec>' . $row['anamnesis'] . '</anamnesis_rec>';
        }
        //

        echo '</anamnesis>';
        echo '<weight>' . $row['weight'] . '</weight>';
        echo '<specialist_id>' . $row['specialist_id'] . '</specialist_id>';
        echo '<specialist_name>' . $row['specialist_name'] . '</specialist_name>';
        echo '<specialist_org>' . $row['specialist_org'] . '</specialist_org>';

        if ($row['specialist_org'] && $row['specialist_org'] != $id_org) {
            echo '<protected>1</protected>';
        } else {
            echo '<protected>0</protected>';
        }


        echo '</rec>';
    }
    pg_free_result($result);
    echo '</health>';
    //HEALTH

    //DRUGS
    echo '<drugs>';
    $query = 'SELECT tmc.tmc.id, tmc.tmc.name, tmc.tmc.produced FROM tmc.tmc ';
    $query .= 'WHERE type=\'drug\' AND is_deleted=\'false\'';
    $query .= 'ORDER BY tmc.tmc.name ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<name>' . string_formating_for_xml($row['name']) . '</name>';
        echo '<producer>' . string_formating_for_xml($row['produced']) . '</producer>';
        echo '</rec>' . "\n";
    }
    pg_free_result($result);
    echo '</drugs>';
    //DRUGS

    //VACCINES
    echo '<vaccines>';
    $query = 'SELECT tmc.tmc.id, tmc.tmc.name, tmc.tmc.produced FROM tmc.tmc ';
    $query .= 'WHERE type=\'vaccine\' AND is_deleted=\'false\'';
    $query .= 'ORDER BY tmc.tmc.name ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<name>' . string_formating_for_xml($row['name']) . '</name>';
        echo '<producer>' . string_formating_for_xml($row['produced']) . '</producer>';
        echo '</rec>' . "\n";
    }
    pg_free_result($result);
    echo '</vaccines>';
    //VACCINES

    //organizations
    echo '<organizations>';
    $query = 'SELECT id, name FROM outside_org ORDER BY name ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<name>' . string_formating_for_xml($row['name']) . '</name>';
        echo '</rec>' . "\n";
    }
    pg_free_result($result);
    echo '</organizations>';
    //organizations

    //SPECIALISTS
    echo '<specialists>';
    $query = 'SELECT specialists.id AS specialist_id, users.fullname AS specialist_name FROM specialists ';
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE specialists.id_organization=' . string_formating_for_sql($id_org) . ' ';
    $query .= 'ORDER BY users.fullname ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<rec>';
        echo '<id>' . $row['specialist_id'] . '</id>';
        echo '<name>' . string_formating_for_xml($row['specialist_name']) . '</name>';
        echo '</rec>';
    }
    pg_free_result($result);
    echo '</specialists>';
    //SPECIALISTS

    //FILES
    echo '<files>';
    $query = 'SELECT files.id AS id_file, files.path, documents.name, document_types.type, documents.id, documents.protected_at FROM files ';
    $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
    $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
    $query .= 'WHERE entity_id=' . string_formating_for_sql($id_shelters_guest) . ' AND entity_type=\'shelter_guests\'';

    $query .= 'ORDER BY documents.date ASC, documents.id ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<file>';
        echo '<id_file>' . $row['id_file'] . '</id_file>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<path>' . $row['path'] . '</path>';
        echo '<name>' . $row['name'] . '</name>';
        echo '<type>' . $row['type'] . '</type>';
        if ($row['protected_at']) {
            echo '<protected>1</protected>';
        } else {
            echo '<protected>0</protected>';
        }
        echo '</file>';
    }
    pg_free_result($result);
    echo '</files>';
    //FILES

    //IMAGES
    echo '<images>';
    $query = 'SELECT files.id AS id_file, files.path, files.entity_type, documents.name, document_types.type, documents.id, documents.protected_at FROM files ';
    $query .= 'LEFT JOIN documents ON files.id=documents.file_id ';
    $query .= 'LEFT JOIN document_types ON document_types.id=documents.type_id ';
    $query .= 'WHERE entity_id=' . string_formating_for_sql($id) . ' AND (entity_type=\'shelter\' OR entity_type=\'shelter_main\')';

    $query .= 'ORDER BY entity_type DESC, documents.id ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<image>';
        $path = $configuration['api_url'];
        echo '<id_file>' . $row['id_file'] . '</id_file>';
        echo '<id>' . $row['id'] . '</id>';
        echo '<type>' . $row['entity_type'] . '</type>';
        echo '<path>' . $path . "" . $row['path'] . '</path>';
        echo '<name>' . $row['name'] . '</name>';
        echo '<type>' . $row['type'] . '</type>';
        if ($row['protected_at']) {
            echo '<protected>1</protected>';
        } else {
            echo '<protected>0</protected>';
        }
        echo '</image>';
    }
    pg_free_result($result);
    echo '</images>';
    //IMAGES

    //Выключаем любое редактирование
    $org_user_id = user_org_id($configuration);

    $m_orgs_array = getManagingOrgs($org_user_id);
    if (count($m_orgs_array) > 0) {
        $not_editable = 1;
    }
    echo '<not_editable>' . $not_editable . '</not_editable>';
    //Выключаем любое редактирование

    echo '</xml>';
}

function viewSheltersPet($config)
{
    global $canEdit;
    $id = $_GET["id"];
    $nextDay = strtotime(date('Y-m-d')) + (60 * 60 * 24);
    ?>
    <div class="row main_row">
        <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
        <div class="col-xl-8 col-lg-10 main_row">
            <div class="information" id="main">...</div>
            <input type="hidden" id="pet_id" value="<?= $id ?>">
            <div class="tabs">
                <div class="item1 current" onclick="showSheltersTab(1);">Основная информация</div>
                <div class="item2" onclick="showSheltersTab(2);">Сведения о движении</div>
                <div class="item3" onclick="showSheltersTab(3);">Состояние здоровья</div>
                <div class="item4" onclick="showSheltersTab(4);">Вакцинация и обработка</div>
                <div class="item5" onclick="showSheltersTab(5);">История</div>
            </div>
            <div class="information_ message" id="message"></div>
            <div id="tab1" style="display: none;">
                <form method="POST" id="tab1_form" onsubmit="saveSheltersPet('tab1','<?= $id ?>'); return true;">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">
                            <div class="images w-100 main_row row">
                                <div class="col-1">
                                    <? if ($canEdit): ?>
                                        <div class="w-100 h-100 add" onclick="showSheltersAddImageWindow();"></div>
                                    <? endif; ?>
                                </div>
                                <div id="images" class="images_inner col-11 w-100"></div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">
                            <p class="required">Вид:
                                <select name="specie" id="specie"
                                        onchange="changeSheltersSpecie();" <?= $canEdit ? '' : 'disabled' ?>>
                                    <option value="25">Собаки</option>
                                    <option value="9">Кошки</option>
                                </select>
                            </p>
                            <p class="required">Пол:
                                <select class="sex" name="sex" id="sex" <?= $canEdit ? '' : 'disabled'?>>
                                    <option value="m">Мужской</option>
                                    <option value="f">Женский</option>
                                </select>
                            </p>
                            <p>Кличка: <input type="text" name="name" id="name"
                                              value="" <?= $canEdit ? '' : 'disabled' ?>></p>
                            <p>Порода: <?= show_breeds(!$canEdit) ?></p>
                            <p>Окрас: <?= showEntites('pet_ref_color', 'color', !$canEdit) ?></p>
                            <p>Особые приметы: <textarea name="characteristics" id="characteristics"
                                                         rows="5" <?= $canEdit ? '' : 'disabled' ?>></textarea></p>
                            <p>Характер: <textarea name="character" id="character"
                                                   rows="5" <?= $canEdit ? '' : 'disabled' ?>></textarea></p>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">
                            <p class="required">Дата рождения: <input type="text" name="birthday" id="birthday"
                                                                      class="birthday"
                                                                      value="" <?= $canEdit ? '' : 'disabled' ?>></p>
                            <p>Размер: <?= showEntites('pet_ref_size', 'size', !$canEdit) ?> </p>
                            <p>Тип шерсти: <?= showEntites('pet_ref_wool_type', 'wool', !$canEdit) ?></p>
                            <p>Тип ушей: <?= showEntites('pet_ref_ear_type', 'ear', !$canEdit) ?></p>
                            <p>Тип хвоста: <?= showEntites('pet_ref_tail_type', 'tail', !$canEdit) ?></p>
                            <p>
                                <label class="checkbox_container">Социализация
                                    <input type="checkbox" name="socialized" id="socialized" value="1"
                                           onchange="changeSheltersSocialized();" <?= $canEdit ? '' : 'disabled' ?> >
                                    <span class="checkbox_checkmark"></span>
                                </label>
                            </p>
                            <p>Навыки:
                                <textarea id="skill" name="skill"
                                          class="JxTag" <?= $canEdit ? '' : 'disabled' ?>></textarea>
                            </p>
                            <p>Номер чипа:

                            <div id="chip_data_values" class="table inner">
                                <table>
                                    <thead>
                                    <tr>
                                        <td>Способ идентификации</td>
                                        <td>Идентификационный номер</td>
                                        <td>Основной</td>
                                        <td></td>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">
                                    </tbody>
                                </table>
                            </div>
                            <? if ($canEdit):?>
                            <button id="chip_add_button" class="chip_add_button" onclick="addChipRow()">Добавить
                            </button>
                            <? endif;?>
                            </p>
                            <p>Номер метки: <input type="text" name="label" id="label"
                                                   value="" <?= $canEdit ? '' : 'disabled' ?>></p>
                        </div>
                        <? if ($canEdit):?>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12" id="tab1_buttons">
                            <button type="button" style="width: 150px;" class="button"
                                    onclick="saveSheltersPet('tab1','<?= $id ?>');">Сохранить
                            </button>
                            <button type="button" style="margin-left: 10px; width: 150px;" class="button"
                                    onclick="showSheltersInformation(<?= $id ?>s);">Отмена
                            </button>
                            </div>
                            <? endif;?>
                    </div>
                </form>
            </div>
            <div id="tab2" style="display: none;">
                <form method="POST" id="tab2_form">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="row" id="maintence_">
                                <div class="col-6 row-center-align">Дата поступления в приют на содержание:</div>
                                <div class="col-6 tabs_text" id="maintence">-</div>
                            </div>
                            <div class="row" style="height: 64px;">
                                <div class="col-3 row-center-align">Вольер:</div>
                                <div class="col-8 row-center-align tabs_text" id="aviary">-</div>
                                <? if ($canEdit): ?>
                                    <div class="col-1 row-center-align">
                                        <div id="aviary_edit" class="tabs_edit"
                                             onclick="showSheltersAviaryWindow();"></div>
                                    </div>
                                <? endif; ?>
                                <input type="hidden" id="aviary_" value="0">
                            </div>
                            <div class="row">
                                <div class="col-3 row-center-align">Причина прибытия:</div>
                                <div class="col-3 tabs_text" id="arrival_reason">-</div>
                                <div class="col-3 row-center-align">Дата прибытия:</div>
                                <div class="col-2 tabs_text row-center-align" id="arrival_date">-</div>
                                <? if ($canEdit): ?>
                                    <div class="col-1 row-center-align">
                                        <div id="aviary_edit" class="tabs_edit" onclick="showDateAviaryWindow();"></div>
                                    </div>
                                <? endif; ?>
                                <input type="hidden" id="aviary_" value="0">
                            </div>
                            <div class="row" id="departure_reason_">
                                <div class="col-3 row-center-align">Причина выбытия:</div>
                                <div class="col-3 tabs_text" id="departure_reason">-</div>
                                <div class="col-3 row-center-align">Дата выбытия:</div>
                                <div class="col-3 tabs_text row-center-align" id="departure_date">-</div>
                            </div>
                            <div class="row" id="arrival_act_">
                                <div class="col-3 row-center-align">№ акта приёма:</div>
                                <div class="col-3 tabs_text" id="arrival_act">-</div>
                            </div>
                            <div class="row" id="arrival_work_order_">
                                <div class="col-3 row-center-align">Заказ-наряд №:</div>
                                <div class="col-3 tabs_text" id="arrival_work_order">-</div>
                            </div>
                            <div class="row" id="catching_act_">
                                <div class="col-3 row-center-align">№ акта отлова:</div>
                                <div class="col-3 tabs_text" id="catching_act">-</div>
                            </div>
                            <div class="row" id="catching_address_">
                                <div class="col-3 row-center-align">Адрес места отлова:</div>
                                <div class="col-3 tabs_text" id="catching_address">-</div>
                            </div>
                            <div class="row" id="catching_video_">
                                <div class="col-3 row-center-align">Видео фиксация отлова:</div>
                                <div class="col-3 tabs_text" id="catching_video">-</div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <p class="required">Документы:</p>
                            <div class="documents w-100 main_row row">
                                <? if ($canEdit): ?>
                                    <div class="col-2">
                                        <div class="w-100 h-100 add"
                                             onclick="showSheltersAddDocumentWindow('save')"></div>
                                    </div>
                                <? endif; ?>
                                <div class="documents_inner col-10 w-100" id="documents"></div>
                                <input type="hidden" id="arrival_reason_id" name="arrival_reason_id" value="">
                            </div>
                        </div>
                </form>
            </div>
        </div>
        <div id="tab3" style="display: none;">
            <form method="POST" id="tab3_1_form" onsubmit="saveSheltersPet('tab3_1','<?= $id ?>'); return true;">
                <div class="row">
                    <div class="col-xl-6">
                        <p>
                            <label class="checkbox_container">Стерилизация/кастрация
                                <input type="checkbox" name="castrated" id="castrated" value="1"
                                       onchange="changeCastrated(1);" <?= $canEdit ? '' : 'disabled' ?>>
                                <span class="checkbox_checkmark"></span>
                            </label>
                        </p>
                        <p>Дата стерилизации:
                            <input type="date" name="castrated_date" id="castrated_date" class="castrated_date"
                                   max_date="' . $nextDay . '" style="width: 150px;" <?= $canEdit ? '' : 'disabled' ?>>
                        <div class="castrated_date_error" style="color: red; display: none;">Нельзя выбрать дату из
                            будущего
                        </div>
                        </p>
                    </div>
                    <div class="col-xl-6">
                        <p>
                            <label class="checkbox_container">Ранее стерилизован
                                <input type="checkbox" name="early_castrated" id="early_castrated" value="1"
                                       onchange="changeCastrated(2);" <?= $canEdit ? '' : 'disabled' ?>>
                                <span class="checkbox_checkmark"></span>
                            </label>
                        </p>
                    </div>
                    <div class="col-xl-6" id="tab3_2_buttons">
                        <? if ($canEdit): ?>
                            <button type="button" style="width: 150px;" class="button"
                                    onclick="saveSheltersPet('tab3_1','<?= $id ?>');">Сохранить
                            </button>
                            <button type="button" style="margin-left: 10px; width: 150px;" class="button"
                                    onclick="showSheltersInformation(' . $id . ');">Отмена
                            </button>
                        <? endif; ?>
                    </div>
                    <div class="col-xl-6 row-right-content" id="tab3_1_buttons"></div>
                </div>
            </form>
            <br><br>
            <form method="POST" id="tab3_2_form" onsubmit="saveSheltersPet('tab3_2','<?= $id ?>'); return true;"
                  style="width: 100%;">
                <div class="main_row col-xl-12">
                    <div id="health_values" class="table inner" style="margin-top: 15px;">
                        <table class="w-100">
                            <tr>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th>Температура</th>
                                <th>Вес</th>
                                <th>Анамнез</th>
                                <th>ФИО врача</th>
                                <th></th>
                            </tr>
                        </table>
                    </div>
                    <? if ($canEdit): ?>
                        <button type="button" class="tabs button addon"
                                onclick="addListItem('health', 'health_values');">Добавить запись
                        </button>
                        <input type="hidden" id="health" name="health" value="">
                    <? endif; ?>
                </div>
                <div class="main_row col-xl-12" style="margin-top: 15px;" id="tab3_3_buttons">
                    <? if ($canEdit): ?>
                        <button type="button" style="width: 150px;" class="button"
                                onclick="saveSheltersPet('tab3_2','<?= $id ?>');">Сохранить
                        </button>
                        <button type="button" style="margin-left: 10px; width: 150px;" class="button"
                                onclick="showSheltersInformation('<?= $id ?>');">Отмена
                        </button>
                    <? endif; ?>
                </div>
            </form>
        </div>
        <div id="tab4" style="display: none;">
            <form method="POST" id="tab4_form" onsubmit="saveSheltersPet('tab4','<?= $id ?>'); return true;">
                <div class="row">
                    <div class="col-xl-12">
                        <h2 style="padding-bottom: 15px;">Вакцинация против бешенства</h2>
                    </div>
                    <div class="col-xl-12">
                        <div id="rabies_vaccinations_values" class="table inner">
                            <table class="w-100">
                                <tr>
                                    <th>Дата вакцинации</th>
                                    <th>Наименование вакцины</th>
                                    <th>Производитель</th>
                                    <th>Организация</th>
                                    <th>ФИО врача</th>
                                    <th>Номер партии/серии</th>
                                    <th>Срок годности вакцины</th>
                                    <th>Действительно до</th>
                                    <th>Сторонняя организация</th>
                                    <th></th>
                                </tr>
                            </table>
                        </div>
                        <? if ($canEdit): ?>
                            <button type="button" class="tabs button addon"
                                    onclick="addListItem('rabies_vaccinations', 'rabies_vaccinations_values')">Добавить
                                запись
                            </button>
                            <input type="hidden" id="rabies_vaccinations" name="rabies_vaccinations" value="">
                        <? endif; ?>
                    </div>
                    <div class="col-xl-12">
                        <h2 style="padding-top: 15px;padding-bottom: 15px;">Обработка против эктопаразитов</h2>
                    </div>
                    <div class="col-xl-12">
                        <div id="ectoparasites_values" class="table inner">
                            <table class="w-100">
                                <tr>
                                    <th>Дата обработки</th>
                                    <th>Наименование препарата</th>
                                    <th>Производитель</th>
                                    <th>Организация</th>
                                    <th>ФИО врача</th>
                                    <th>Доза</th>
                                    <th>Срок годности вакцины</th>
                                    <th>Действительно до</th>
                                    <th></th>
                                </tr>
                            </table>
                        </div>
                        <? if ($canEdit): ?>
                            <button type="button" class="tabs button addon"
                                    onclick="addListItem('ectoparasites', 'ectoparasites_values')">Добавить запись
                            </button>
                            <input type="hidden" id="ectoparasites" name="ectoparasites" value="">
                        <? endif; ?>
                    </div>
                    <div class="col-xl-12">
                        <h2 style="padding-top: 15px;padding-bottom: 15px;">Другие вакцинации</h2>
                    </div>
                    <div class="col-xl-12">
                        <div id="other_vaccinations_values" class="table inner">
                            <table class="w-100">
                                <tr>
                                    <th>Дата вакцинации</th>
                                    <th>Наименование вакцины</th>
                                    <th>Производитель</th>
                                    <th>Организация</th>
                                    <th>ФИО врача</th>
                                    <th>Номер партии/серии</th>
                                    <th>Срок годности вакцины</th>
                                    <th>Действительно до</th>
                                    <th>Сторонняя организация</th>
                                    <th></th>
                                </tr>
                            </table>
                        </div>
                        <? if ($canEdit): ?>
                            <button type="button" class="tabs button addon"
                                    onclick="addListItem('other_vaccinations', 'other_vaccinations_values')">Добавить
                                запись
                            </button>
                            <input type="hidden" id="other_vaccinations" name="other_vaccinations" value="">
                        <? endif; ?>
                    </div>
                    <div class="col-xl-12" style="margin-top: 15px;" id="tab4_buttons">
                        <? if ($canEdit): ?>
                            <button type="button" style="width: 150px;" class="button"
                                    onclick="saveSheltersPet('tab4','<?= $id ?>');">Сохранить
                            </button>
                            <button type="button" style="margin-left: 10px; width: 150px;" class="button"
                                    onclick="showSheltersInformation('<?= $id ?>');">Отмена
                            </button>
                        <? endif; ?>
                    </div>
                </div>
            </form>
        </div>
        <div id="tab5" style="display: none;">История
        </div>
    </div>
    <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            showSheltersInformation(<?= $id?>);
            $('#specie').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать вид животного...'
            });
            $('#breed').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать породу животного...'
            });
            $('#sex').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать пол животного...'
            });
            $('#color').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать окрас животного...'
            });
            $('#wool').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать тип шерсти животного...'
            });
            $('#size').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать размер животного...'
            });
            $('#tail').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать тип ушей животного...'
            });
            $('#ear').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать тип хвоста животного...'
            });
            changeSheltersSpecie();
            var options ={
                name: 'skill',
                mode: 'normal',
                new_tags: 0,
                request_min: 2,
                request_title: 'title',
                response_id: 'rec_id',
                response_title: 'rec_title',
                request_time: 100,
                width: '100%',
                height: 250,
                max_tags_win: 10,
                url: "?action=skills&mode=xml"
            };
            skill_input = new JxTag(options);
            $('.chip').mask("999999999999999", {placeholder: "_______________"});
            $('.birthday').mask("AB.CD99",
                {
                    translation: {
                        'A': {
                            pattern: /[0-1]/, optional: false
                        },
                        'B': {
                            pattern: /[0-9]/, optional: false
                        },
                        'C': {
                            pattern: /[1-2]/, optional: false
                        },
                        'D': {
                            pattern: /[9|0]/, optional: false
                        }
                    },
                    placeholder: "__.____"
                }
            );
        });
    </script>
    <?
}

function viewSheltersPetsAdd($configuration)
{
    $id = $_GET["id"];
    $temp_id = time();
    global $canEdit;
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10 main_row form row">';
    echo '<form method="POST" id="add_form" class="main_row col-12">';
    echo '<h1 style="margin-top: 15px; margin-bottom: 15px;">Добавить животное</h1>';
    echo '<div class="information_ message" id="message"></div>';
    echo '<h2 style="margin-bottom: 15px;">Расположение животного</h2>';
    $user_id = user_id($configuration);
    $org_id = user_org_id($configuration);
    $not_managing_organization = false;
    $m_orgs_array = getManagingOrgs($org_id);
    if (count($m_orgs_array) > 0) {
        $query = 'SELECT * FROM organizations WHERE organization_type_const = \'shelter\'';
        $query .= ' AND (';
        for ($i = 0; $i <= count($m_orgs_array); $i++) {
            if ($m_orgs_array[$i]["id"]) {
                if ($i > 0) {
                    $query .= ' OR ';
                }
                $query .= 'id=' . $m_orgs_array[$i]["id"] . '';
            }
        }
        $query .= ')';
        echo '<p>Приют: ';
        echo '<select name="shelter">';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            echo '<option value="' . $row["managing_organization_id"] . '">' . $row["name"] . '</option>';
        }
        pg_free_result($result);

        echo '</select>';
        echo '</p>';
    } else {
        $query = 'SELECT name, managing_organization_id FROM organizations WHERE id=' . $org_id . ' AND organization_type_const=\'shelter\' LIMIT 1';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        $org_name = $row[0];
        $manag_id = $row[1];
        pg_free_result($result);

        if ($org_name) {
            echo '<div class="row">';
            echo '<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 row-center-align">Приют:</div>';
            echo '<div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-xs-12 col-12 tabs_text">' . $org_name . '</div>';
            echo '</div>';

            echo '<input type="hidden" name="shelter" value="' . $org_id . '">';
        } else {
            $not_managing_organization = true;
            echo '<div class="information_ message error" id="message" style="display: block;">Текущая организация не является приютом.</div>';
        }

        if ($manag_id) {
            $query = 'SELECT name FROM organizations WHERE id=' . $manag_id . ' LIMIT 1';
            $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
            $row = pg_fetch_row($result);
            $manag_name = $row[0];
            pg_free_result($result);

            if ($manag_name) {
                echo '<div class="row">';
                echo '<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 row-center-align">Управляющая организация:</div>';
                echo '<div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-xs-12 col-12 tabs_text">' . $manag_name . '</div>';
                echo '</div>';
            } else {
                echo '<div class="information_ message error" id="message" style="display: block;">У приюта отсутствует управляющая организация.</div>';
            }
        }
    }
    ///////////////////////////

    ///////////////////////////
    echo '<h2>Карантин</h2>';
    echo '<p>';


    echo '<label class="checkbox_container">установить карантин';
    echo '<input type="checkbox" name="is_quarantine" id="is_quarantine" value="1" checked="checked">';
    echo '<span class="checkbox_checkmark"></span>';
    echo '</label>';

    echo '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="' . date("Y-m-d") . '" min="2023-01-01" max="2050-01-01">';
    echo '&nbsp;—&nbsp;';
    $date_ = strtotime("+9 day");
    echo '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="' . date("Y-m-d", $date_) . '" min="2023-01-01" max="2050-01-01">';

    echo '</p>';

    ///////////////////////////
    echo '<h2>Основание прибытия</h2>';

    echo '<div class="row">';
    echo '<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';

    echo '<p class="required">Основание прибытия: ';
    echo '<select name="arrival_reason" id="arrival_reason" onchange="changeArrivalReason();">';
    echo '<option value="CATCH">Отлов</option>';
    echo '<option value="COURT_DECISION">Решение суда</option>';
    echo '<option value="FOUNDLING">Подкидыш</option>';
    echo '<option value="OWNER_REFUSAL">Отказ владельца</option>';
    echo '</select>';
    echo '</p>';

    echo '<p id="catching_addresss_">Адрес места отлова: ';
    echo '<textarea id="catching_address" name="catching_address" class="JxTag"></textarea>';
    echo '</p>';

    echo '<p id="catching_video_">Видео фиксация отлова: <input type="text" name="catching_video" id="catching_video" value="">';
    echo '</p>';


    echo '</div>';
    echo '<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';

    echo '<p class="required">Документы:</p>';

    ///
    echo '<div class="documents w-100 main_row row">';
    echo '<div class="col-2 "><div class="w-100 h-100 add" onclick="showSheltersAddDocumentWindow(\'add\');"></div></div>';
    echo '<div class="documents_inner col-10 w-100" id="temp_documents">';
    echo '</div>';
    echo '</div>';
    ///

    echo '</div>';
    echo '</div>';
    ///////////////////////////

    ///////////////////////////
    echo '<input type="hidden" id="pet" name="pet" value="">';
    echo '<h2>Данные об идентификации</h2>';

    echo '<div class="row">';
    echo '<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';

    echo '<p>Номер чипа: <input type="text" name="chip" id="chip" class="chip" value=""></p>';
    //добавить проверку на наличие животного по чипу
    echo '</div>';
    echo '<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
    echo '<p>Номер метки: <input type="text" name="label" id="label" value=""></p>';
    echo '</div>';
    echo '</div>';
    ///////////////////////////
    echo '<h2>Общие сведения о животном</h2>';

    echo '<div class="row">';
    echo '<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';

    echo '<p class="required">Вид: ';
    echo '<select name="specie" id="specie" onchange="changeSheltersSpecie();">';
    $species_array = array();
    array_push($species_array, array('id' => '25', 'title' => 'Собаки'));
    array_push($species_array, array('id' => '9', 'title' => 'Кошки'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="' . $species_array[$i]['id'] . '">' . $species_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</p>';

    echo '<p class="required">Пол: ';
    echo '<select class="sex" name="sex" id="sex">';
    $species_array = array();
    array_push($species_array, array('id' => 'm', 'title' => 'Мужской'));
    array_push($species_array, array('id' => 'f', 'title' => 'Женский'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="' . $species_array[$i]['id'] . '">' . $species_array[$i]['title'] . '</option>';
    }


    echo '</select>';
    echo '</p>';

    echo '<p>Кличка: <input type="text" name="name" id="name" value=""></p>';

    echo '<p class="required">Порода: ';
    show_breeds(!$canEdit);
    echo '</p>';

    echo '<p>Окрас: ';
    show_colors(!$canEdit);
    echo '</p>';

    echo '<p>Особые приметы: <textarea name="characteristics" id="characteristics" rows="5"></textarea></p>';

    echo '</div>';

    echo '<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
    echo '<p class="required">Дата рождения: <input type="text" name="birthday" id="birthday" class="birthday" value=""></p>';

    echo '<p>Размер: ';
    show_sizes(!$canEdit);
    echo '</p>';
    echo '<p>Тип шерсти: ';
    show_wools(!$canEdit);
    echo '</p>';
    echo '<p>Тип ушей: ';
    show_ears(!$canEdit);
    echo '</p>';
    echo '<p>Тип хвоста: ';
    show_tails(!$canEdit);
    echo '</p>';

    echo '<p>Характер: <textarea name="character" id="character" rows="5"></textarea></p>';
    echo '</div>';

    echo '</div>';
    ///////////////////////////

    if (!$not_managing_organization) {
        echo '<button style="width: 150px;" class="button" onclick="addSheltersPet();" type="button">Добавить</button>';
        echo '<button style="margin-left: 10px; width: 150px;" class="button" type="button">Отмена</button>';
        echo '<input type="hidden" name="temp_pet" id="temp_pet" value="' . $temp_id . '">';
        echo '</form>';
    }

    // echo '<div class="col-2"></div>';


    echo '</div>';


    //
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';


    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
						
			$('#specie').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать вид животного...'});
			$('#breed').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать породу животного...'});
			$('#sex').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать пол животного...'});
			$('#color').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать окрас животного...'});
			$('#wool').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать тип шерсти животного...'});
			$('#size').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать размер животного...'});
			$('#tail').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать тип ушей животного...'});
			$('#ear').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать тип хвоста животного...'});

			changeSheltersSpecie();

			if(fiasTokken == ''){
				getFiasTokken(1);
			}
			
			$('.chip').mask("999999999999999", {placeholder: "_______________"});
			$('.birthday').mask("AB.CD99",
				{
					translation: {
					'A': {
						pattern: /[0-1]/, optional: false
					},
					'B': {
						pattern: /[0-9]/, optional: false
					},
					'C': {
						pattern: /[1-2]/, optional: false
					},
					'D': {
						pattern: /[9|0]/, optional: false
					}
					},
					placeholder: "__.____"
				}
			);
		});
HTML;

    $html .= '</script>';

    echo $html;
}

function viewSheltersAviaries()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Вольеры</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowAviariesRecs(); return false;">';
    echo '<h2>Поиск вольеров</h2>';
    echo '<div class="row">';
    echo '<div class="col-3"><input type="text" id="title" name="title" class="w-100" placeholder="Название вольера." value=""></div>';
    echo '<div class="col-3"><input type="text" id="number" name="number" class="w-100" placeholder="Номер вольера." value=""></div>';
    echo '<div class="col-3"><input type="text" id="description" name="description" class="w-100" placeholder="Описание вольера." value=""></div>';
    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowAviariesRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersAnamnesiss()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Шаблоны анамнеза</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowAnamnesissRecs(); return false;">';
    echo '<h2>Поиск шаблонов анамнеза</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название шаблона анамнеза." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowAnamnesissRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersSkills()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Навыки</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowSkillsRecs(); return false;">';
    echo '<h2>Поиск навыков</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название навыка." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowSkillsRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersColors()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Окрасы</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowColorsRecs(); return false;">';
    echo '<h2>Поиск окрасов</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название окраса." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowColorsRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersWools()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Типы шерсти</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowWoolsRecs(); return false;">';
    echo '<h2>Поиск типов шерсти</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название типа шерсти." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowWoolsRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersSizes()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Размеры</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowSizesRecs(); return false;">';
    echo '<h2>Поиск размеров</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название размера." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowSizesRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersTails()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Типы хвостов</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowTailsRecs(); return false;">';
    echo '<h2>Поиск типов хвостов</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название типа хвоста." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowTailsRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersEars()
{
    echo '<div class="row main_row">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';

    echo '<h1>Типы ушей</h1>';

    echo '<form id="form_search_recs" onsubmit="listShowEarsRecs(); return false;">';
    echo '<h2>Поиск типов ушей</h2>';
    echo '<div class="row">';
    echo '<div class="col-9"><input type="text" id="title" name="title" class="w-100" placeholder="Название типа ушей." value=""></div>';

    echo '<div class="col-3"><button type="submit">Искать</button></div>';
    echo '</div>';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';

    echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			listShowEarsRecs();
		});
HTML;

    $html .= '</script>';
    echo $html;

    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
}

function viewSheltersPets($config)
{
    $id = $_GET["id"];

    echo '<div class="row main_row search shelters" id="shelters_search">';

    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10 col-md-12 col-sm-12 col-12">';
    echo '<form id="form_search_recs" onsubmit="listShowSheltersPets(); listShowSheltersInfo(); return false;">';
    echo '<div class="row search_sub">';

    //
    $org_user_id = user_org_id($config);
    $levels_array = array();
    $m_orgs_array = getManagingOrgs($org_user_id);
    if (count($m_orgs_array) > 0) {
        for ($i = 0; $i <= count($m_orgs_array); $i++) {
            if ($m_orgs_array[$i]) {
                if (!in_array($m_orgs_array[$i]['level'], $levels_array, true)) {
                    array_push($levels_array, $m_orgs_array[$i]['level']);
                }
            }
        }
    }
    //

    if (count($levels_array) >= 2) {
        //Доступно на уровне ДЖКХ
        echo '<div class="d-inline-flex row">';
        echo '<div class="label col-12" style="width: 50px;">Округ</div>';
        echo '<div class="input col-12">';
        show_areas($config, 2);
        echo '</div>';
        echo '</div>';

        echo '<div class="d-inline-flex row">';
        echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Управляющая организация</div>';
        echo '<div class="input col-12">';

        $query = 'SELECT * FROM organizations WHERE organization_type_const = \'operating\'';
        echo '<select class="operating" name="operating">';
        echo '<option value="">Все</option>';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
        }
        echo '</select>';
        pg_free_result($result);

        echo '</div>';
        echo '</div>';

        echo '<div class="d-inline-flex row">';
        echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Приют</div>';
        echo '<div class="input col-12">';

        $query = 'SELECT * FROM organizations WHERE organization_type_const = \'shelter\'';
        $query .= ' AND (';
        for ($i = 0; $i <= count($m_orgs_array); $i++) {
            if ($m_orgs_array[$i]["id"]) {
                if ($i > 0) {
                    $query .= ' OR ';
                }
                $query .= 'id=' . $m_orgs_array[$i]["id"] . '';
            }
        }
        $query .= ')';

        echo '<select class="shelter" name="shelter">';
        echo '<option value="">Все</option>';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
        }
        echo '</select>';
        pg_free_result($result);

        echo '</div>';
        echo '</div>';
        //Доступно на уровне ДЖКХ
    }

    if (count($levels_array) > 0 && count($levels_array) <= 1) {
        //Доступно на уровне УО
        echo '<div class="d-inline-flex row">';
        echo '<div class="label col-12" style="width: 50px;">Приют</div>';
        echo '<div class="input col-12">';

        $query = 'SELECT * FROM organizations WHERE organization_type_const = \'shelter\'';
        $query .= ' AND (';
        for ($i = 0; $i <= count($m_orgs_array); $i++) {
            if ($m_orgs_array[$i]["id"]) {
                if ($i > 0) {
                    $query .= ' OR ';
                }
                $query .= 'id=' . $m_orgs_array[$i]["id"] . '';
            }
        }
        $query .= ')';

        echo '<select class="shelter" name="shelter">';
        echo '<option value="">Все</option>';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
        }
        echo '</select>';
        pg_free_result($result);

        echo '</div>';
        echo '</div>';
        //Доступно на уровне УО
    }

    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Статус</div>';
    echo '<div class="input col-12 ">';
    echo '<select class="status" name="status" multiple="multiple">';
    $statuses_array = array();
    //array_push($statuses_array, array('status' => '','title' => 'Все'));
    array_push($statuses_array, array('status' => 'IN_SHELTER', 'title' => 'В приюте', 'selected' => true));
    array_push($statuses_array, array('status' => 'QUARANTINE', 'title' => 'Карантин', 'selected' => true));
    array_push($statuses_array, array('status' => 'QUARANTINE_OTHER', 'title' => 'Карантин (продлён)', 'selected' => true));
    array_push($statuses_array, array('status' => 'DEPARTURED', 'title' => 'Выбыло', 'selected' => false));
    array_push($statuses_array, array('status' => 'IN_ISOLATION', 'title' => 'В изоляторе', 'selected' => true));
    array_push($statuses_array, array('status' => 'IN_HOSPITAL', 'title' => 'В стационаре', 'selected' => true));

    for ($i = 0; $i < count($statuses_array); $i++) {
        echo '<option value="' . $statuses_array[$i]['status'] . '"';
        if ($statuses_array[$i]['selected'] == true) {
            echo ' selected';
        }
        echo '>' . $statuses_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Вид</div>';
    echo '<div class="input col-12">';
    echo '<select class="species" name="species">';
    $species_array = array();
    array_push($species_array, array('id' => '', 'title' => 'Все'));
    array_push($species_array, array('id' => '25', 'title' => 'Собаки'));
    array_push($species_array, array('id' => '9', 'title' => 'Кошки'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="' . $species_array[$i]['id'] . '">' . $species_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    ///

    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align">Кличка</div>';
    echo '<div class="input col-12">';
    echo '<input type="text" name="nick" style="width: calc(100% - 60px);">';
    echo '</div>';
    echo '</div>';

    /// Чип
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align">Чип</div>';
    echo '<div class="input col-12">';
    echo '<input type="text" name="chip" style="width: calc(100% - 60px);">';
    echo '</div>';
    echo '</div>';
    //
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Вакцинация</div>';
    echo '<div class="input col-12">';
    echo '<select class="vaccination" name="vaccination">';
    $vaccination_array = array();
    array_push($vaccination_array, array('id' => '', 'title' => 'Все'));
    array_push($vaccination_array, array('id' => '1', 'title' => 'Вакцинировано'));
    array_push($vaccination_array, array('id' => '2', 'title' => 'Не вакцинировано'));
    array_push($vaccination_array, array('id' => '3', 'title' => 'Заканчивается вакцинация'));
    for ($i = 0; $i < count($vaccination_array); $i++) {
        echo '<option value="' . $vaccination_array[$i]['id'] . '">' . $vaccination_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Социализация</div>';
    echo '<div class="input col-12">';
    echo '<select class="socialized" name="socialized">';
    $socialized_array = array();
    array_push($socialized_array, array('id' => '', 'title' => 'Все'));
    array_push($socialized_array, array('id' => 't', 'title' => 'Да'));
    array_push($socialized_array, array('id' => 'f', 'title' => 'Нет'));
    for ($i = 0; $i < count($socialized_array); $i++) {
        echo '<option value="' . $socialized_array[$i]['id'] . '">' . $socialized_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Стерилизовано</div>';
    echo '<div class="input col-12">';
    echo '<select class="castrated" name="castrated">';
    $castrated_array = array();
    array_push($castrated_array, array('id' => '', 'title' => 'Все'));
    array_push($castrated_array, array('id' => 't', 'title' => 'Да'));
    array_push($castrated_array, array('id' => 'f', 'title' => 'Нет'));
    for ($i = 0; $i < count($castrated_array); $i++) {
        echo '<option value="' . $castrated_array[$i]['id'] . '">' . $castrated_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';
    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Причина выбытия</div>';
    echo '<div class="input col-12">';
    echo '<select class="departure_reason" name="departure_reason">';
    $departure_reason_array = array();
    array_push($departure_reason_array, array('status' => '', 'title' => 'Все'));
    array_push($departure_reason_array, array('status' => 'RETURNED_TO_NEW_OWNER', 'title' => 'Передача новому владельцу'));
    array_push($departure_reason_array, array('status' => 'RETURNED_TO_OWNER', 'title' => 'Возврат прежнему владельцу'));
    array_push($departure_reason_array, array('status' => 'DEATH', 'title' => 'Естественная смерть'));
    array_push($departure_reason_array, array('status' => 'EUTHANASIA', 'title' => 'Эвтаназия'));
    array_push($departure_reason_array, array('status' => 'ESCAPE', 'title' => 'Побег'));
    for ($i = 0; $i < count($departure_reason_array); $i++) {
        echo '<option value="' . $departure_reason_array[$i]['status'] . '">' . $departure_reason_array[$i]['title'] . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align">Кем вакцинировано животное</div>';

    echo '<div class="col-1 out_org">';
    echo '<label class="checkbox_container" title="Сторонняя организация">';
    echo '<input type="checkbox" name="is_out_org" id="is_out_org" value="1" onclick="changeIsOutOrgList(this);">';
    echo '<span class="checkbox_checkmark"></span>';
    echo '</label>';
    echo '</div>';

    //
    echo '<div class="input col-11" id="organization_" style="display: none; max-width: 250px;">';
    echo '<select class="organization" name="organization">';
    $query = 'SELECT id, name FROM outside_org ORDER BY name ASC';
    echo '<option value="">Все</option>';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }
    pg_free_result($result);
    echo '</select>';
    echo '</div>';

    echo '<div class="input col-11" id="specialist_" style="max-width: 350px;">';
    $id_org = user_org_id($config);
    echo '<select class="specialist" name="specialist">';
    echo '<option value="">Все</option>';
    $query = 'SELECT specialists.id AS specialist_id, users.fullname AS specialist_name FROM specialists ';
    $query .= 'LEFT JOIN users ON specialists.id_user=users.id ';
    $query .= 'WHERE specialists.id_organization=' . string_formating_for_sql($id_org) . ' ';
    $query .= 'ORDER BY users.fullname ASC';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="' . $row['specialist_id'] . '">' . $row['specialist_name'] . '</option>';
    }
    echo '</select>';
    pg_free_result($result);
    echo '</div>';
    //

    echo '</div>';
    ///

    // //Вакцина
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Вакцина</div>';
    echo '<div class="input col-12">';

    $query = 'SELECT tmc.tmc.id, tmc.tmc.name, tmc.tmc.produced FROM tmc.tmc ';
    $query .= 'WHERE type=\'vaccine\' AND is_deleted=\'false\'';
    $query .= 'ORDER BY tmc.tmc.name ASC';

    echo '<select class="vaccine" name="vaccine">';
    echo '<option value="">Все</option>';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    while ($row = pg_fetch_assoc($result)) {
        echo '<option value="' . $row["id"] . '">' . $row["name"] . ' (' . $row['produced'] . ')</option>';
    }
    echo '</select>';
    pg_free_result($result);

    echo '</div>';
    echo '</div>';
    // //Вакцина

    // ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="label col-12 row-bottom-align" style="width: 50px;">Дата вакцинации</div>';
    echo '<div class="input col-12">';
    echo '<div class="input"><div class="label-from">с</div><input name="date_vac_from" class="date date-from" value="" autocomplete="off">';
    echo '<div class="label-to">по</div><input name="date_vac_to" class="date date-to" value="" autocomplete="off">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    // ///


    ///
    echo '<div class="d-inline-flex row">';
    echo '<div class="stat">';
    echo '<nobr>Найдено животных: <span id="all">0</span></nobr><br>';
    echo '<nobr>Заканчивается карантин: <span id="quarantine">0</span></nobr><br>';
    echo '<nobr>Вакцинировано: <span id="vaccination">0</span></nobr><br>';
    echo '<nobr>Заканчивается вакцинация: <span id="vaccination_end">0</span></nobr><br>';
    echo '<nobr>Не вакцинировано: <span id="not_vaccination">0</span></nobr>';
    echo '</div>';
    echo '</div>';
    ///

    echo '<div class="d-inline-flex buttons">';

    echo '<button tabindex="1" type="submit" style="margin-left: 15px; width: 120px; order: 2;" value="1" name="submit" id="submit">Поиск</button>';
    echo '<button tabindex="2" type="submit" style="width: 110px;" value="1" name="xls" id="xls">Скачать</button>';

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

    echo '<div class="table shelters" id="ListRecs">';
    echo '<div class="p-3">...</div>';
    echo '</div>';

    // echo '<div class="table" id="ListPages">';
    // echo '<div class="p-3">Пагинация</div>';
    // echo '</div>';

    echo '</div>';

    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

    echo '</div>';

    $html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {

            $(document).on('keyup keydown', function(e){
                //Нажатие клавиш
                shiftPress=e.shiftKey;
                altPress=e.altKey;
                ctrlPress=e.ctrlKey;


            });

            $(document).on('keyup', function(e){
                if(e.keyCode == '83' && altPress){//Нажатие Alt+S
                    listShowSheltersPets();
			        listShowSheltersInfo();
                }
            });

			listShowSheltersPets();
			listShowSheltersInfo();
		});
HTML;

    $html .= '</script>';

    echo $html;
}

if ($action == 'auth') {
    authUser($configuration);
} else if ($action == 'exit') {
    exitUser($configuration);
} else if ($action == 'recovery_password') {
    header_site(1, $configuration);
    viewRecoveryPassword('shelters');
    footer_site();
} else if ($action == 'recovery') {
    recoveryPasswordUser($configuration);
} else if ($action == 'get_pet') {
    if (vallidateToken($configuration)) {
        shelters_get_pet($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'add_pet') {
    if (vallidateToken($configuration)) {
        shelters_add_pet($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_pet') {//сохранение животного по вкладкам
    if (vallidateToken($configuration)) {
        shelters_save_pet($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'departure_pet') {//сохранение животного по вкладкам
    if (vallidateToken($configuration)) {
        shelters_departure_pet($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'return_pet') {//сохранение животного по вкладкам
    if (vallidateToken($configuration)) {
        shelters_return_pet($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'choose_aviary') {//смена вольера
    if (vallidateToken($configuration)) {
        shelters_choose_aviary($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'choose_date_aviary') {//смена даты прибытия в приют
    if (vallidateToken($configuration)) {
        choose_date_aviary($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'choose_health') {
    if (vallidateToken($configuration)) {
        shelters_choose_health($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'add_image') {
    if (vallidateToken($configuration)) {
        shelters_add_image($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'add_document') {
    if (vallidateToken($configuration)) {
        shelters_add_document($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_document') {
    if (vallidateToken($configuration)) {
        shelters_delete_document($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'download_document') {
    if (vallidateToken($configuration)) {
        shelters_download_document($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_image') {
    if (vallidateToken($configuration)) {
        shelters_delete_image($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'main_image') {
    if (vallidateToken($configuration)) {
        shelters_main_image($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'download_image') {
    if (vallidateToken($configuration)) {
        shelters_download_image($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'report') {
    if (vallidateToken($configuration)) {
        shelters_report($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'doc') {
    if (vallidateToken($configuration)) {
        shelters_pet_doc($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'pets' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_show_pets_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_vac_pets' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_save_vac_pets_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'vac_pets' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_show_vac_pets_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'temp_documents' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_show_temp_documents_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'departure_documents' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_show_departure_documents_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'pets_info' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_show_pets_xml($configuration, 1);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'information' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_information_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'document_types' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        show_document_types_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'documents' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        show_documents_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'aviaries' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_aviaries_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'add_new_owner') {
    if (vallidateToken($configuration)) {
        shelters_add_new_owner($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'wools' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_wools_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'wool' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_wool_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_wool') {
    if (vallidateToken($configuration)) {
        shelters_save_wool_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_wool') {
    if (vallidateToken($configuration)) {
        shelters_delete_wool_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'colors' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_colors_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'color' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_color_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_color') {
    if (vallidateToken($configuration)) {
        shelters_save_color_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_color') {
    if (vallidateToken($configuration)) {
        shelters_delete_color_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'ears' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_ears_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'ear' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_ear_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_ear') {
    if (vallidateToken($configuration)) {
        shelters_save_ear_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_ear') {
    if (vallidateToken($configuration)) {
        shelters_delete_ear_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'sizes' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_sizes_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'size' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_size_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_size') {
    if (vallidateToken($configuration)) {
        shelters_save_size_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_size') {
    if (vallidateToken($configuration)) {
        shelters_delete_size_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'tails' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_tails_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'tail' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_tail_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_tail') {
    if (vallidateToken($configuration)) {
        shelters_save_tail_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_tail') {
    if (vallidateToken($configuration)) {
        shelters_delete_tail_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'skills' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_skills_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'skill' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_skill_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_skill') {
    if (vallidateToken($configuration)) {
        shelters_save_skill_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_skill') {
    if (vallidateToken($configuration)) {
        shelters_delete_skill_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'anamnesiss' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_anamnesiss_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'anamnesis' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_anamnesis_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_anamnesis') {
    if (vallidateToken($configuration)) {
        shelters_save_anamnesis_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_anamnesis') {
    if (vallidateToken($configuration)) {
        shelters_delete_anamnesis_xml($configuration);
    } else {
        invalidToken($configuration);
    }

} else if ($action == 'aviary' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        shelters_aviary_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'save_aviary') {
    if (vallidateToken($configuration)) {
        shelters_save_aviary_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'delete_aviary') {
    if (vallidateToken($configuration)) {
        shelters_delete_aviary_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'addresses' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        show_addresses_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'fias_tokken' && $mode == 'xml') {
    if (vallidateToken($configuration)) {
        $fias_tokken = fias_request($configuration);
        header("Content-type: text/xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<xml>';
        echo '<fias_tokken>' . $fias_tokken->access_token . '</fias_tokken>';
        echo '</xml>';
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'specialists') {
    if (vallidateToken($configuration)) {
        show_specialists_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'notification_read') {
    if (vallidateToken($configuration)) {
        read_notification_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'notifications') {
    if (vallidateToken($configuration)) {
        show_notifications_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else if ($action == 'owners') {
    if (vallidateToken($configuration)) {
        show_owners_xml($configuration);
    } else {
        invalidToken($configuration);
    }
} else {
    if ($_COOKIE['token']) {
        if (vallidateToken($configuration)) {
            header_site(0, $configuration, 'Приюты', 'shelters');
            sub_header_site($configuration);
            menu_site($configuration, 'shelters', $action);
            informings_site($configuration);

            if ($action == 'add') {
                viewSheltersPetsAdd($configuration);
            } else if ($action == 'edit') {
                viewSheltersPet($configuration);
            } else if ($action == 'aviaries') {
                viewSheltersAviaries($configuration);
            } else if ($action == 'skills') {
                viewSheltersSkills($configuration);
            } else if ($action == 'anamnesiss') {
                viewSheltersAnamnesiss($configuration);
            } else if ($action == 'colors') {
                viewSheltersColors($configuration);
            } else if ($action == 'wools') {
                viewSheltersWools($configuration);
            } else if ($action == 'ears') {
                viewSheltersEars($configuration);
            } else if ($action == 'tails') {
                viewSheltersTails($configuration);
            } else if ($action == 'sizes') {
                viewSheltersSizes($configuration);
            } else {
                viewSheltersPets($configuration);
            }

            sub_footer_site();
            footer_site();
        } else {
            header_site(1, $configuration);
            viewAuthUser('shelters');
            footer_site();
        }
    } else {
        header_site(1, $configuration);
        viewAuthUser('shelters');
        footer_site();
    }
}

if (function_exists('pg_connect')) {
    pg_close($dbconn);
}
