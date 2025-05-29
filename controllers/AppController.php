<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;

class AppController extends Controller
{
    public function init()
    {
        parent::init();
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
    }

    protected function oldDbConnect()
    {
        if (!function_exists('pg_connect')) {
            echo xml('<message>Функция pg_connect отсутсвует!</message>');
            exit;
        }

        ['DB_HOST' => $host, 'DB_PORT' => $port, 'DB_NAME' => $dbname, 'DB_USER' => $dbuser, 'DB_PASSWORD' => $dbpassword] = $_ENV;
        $dsn = "host=".$host." port=".$port." dbname=".$dbname." user=".$dbuser." password=".$dbpassword;
        $dbconn = pg_connect($dsn) or die('Не удалось соединиться: ' . pg_last_error());
        $result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());
        pg_free_result($result);
        return $dbconn;
    }

    protected function getCurrentUserId()
    {
        return $this->getUserId($_COOKIE['login']);
    }

    protected function getUserId($login)
    {
        $query = (new Query())
            ->select(['id'])
            ->from('users')
            ->where(['login' => $login]);
        $u = $query->one();
        if ($u !== false) {
            return $u['id'];
        }
        return null;
    }
}