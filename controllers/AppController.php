<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;

include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';

class AppController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return $this->validateToken();
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->request->isAjax) {
                        throw new UnauthorizedHttpException('Вы не аутентифицированы в системе');
                    }
                    $this->redirect('index.php');
                },
            ],
        ];
    }

    function validateToken($token_time = 3600) {
        if ($_COOKIE['login'] && $_COOKIE['token']) {
            error_reporting(0); // отключаем ошибки

            if (Yii::$app->getSecurity()->validatePassword($_COOKIE['login'], $_COOKIE['token'])) {
                // всё ок - обвновляем время токена
                setcookie('token', $_COOKIE['token'], time() + $token_time, '/');
                setcookie('login', $_COOKIE['login'], time() + $token_time, '/');
                setcookie('organization', $_COOKIE['organization'], time() + $token_time, '/');

                return true;
            }

            setcookie('token', '', time() + $token_time, '/');
            setcookie('login', '', time() + $token_time, '/');
        }

        return false;
    }

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
