<?php

/* @var $this \yii\web\View */
/* @var $content string */

$configuration = require_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';

include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';

if (!function_exists('pg_connect')) {
    echo xml('<message>Функция pg_connect отсутсвует!</message>');
    exit;
} else {
    $dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());
    $result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());
    pg_free_result($result);
}

define('PAGE_TITLE', $this->title ?? 'Контактный центр');
define('ACTION', $this->context->action->id ?? '');
define('ENTRY_POINT', 'callcenter');

function getUserId($login) {
    $query = 'SELECT id FROM users WHERE login=\''.string_formating_for_sql($login).'\' LIMIT 1';
    $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    $id_user = pg_fetch_result($result, 0);
    pg_free_result($result);
    return $id_user;
}

if ($_COOKIE['token']) {
    if (vallidateTokenYii($configuration)) {
        $this->beginPage();
        header_site(0, $configuration, PAGE_TITLE,ENTRY_POINT, $this);
        sub_header_site($configuration);
        menu_site($configuration, ENTRY_POINT, ACTION);

        informings_site($configuration);

        if (userCan($configuration, 'sysAdminGos')) {
            // пользователь авторизован
            echo $content;
        }

        sub_footer_site();
        footer_site($this);
        $this->endPage();
    } else {
        header_site(1, $configuration, PAGE_TITLE, ENTRY_POINT);
        viewAuthUser(ENTRY_POINT);
        footer_site();
    }
} else {
    header_site(1, $configuration);
    viewAuthUser(ENTRY_POINT);
    footer_site();
}

if (isset($dbconn)) {
    pg_close($dbconn);
}
?>
