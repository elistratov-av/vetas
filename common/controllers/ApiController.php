<?php

namespace app\common\controllers;

use app\common\components\JwtHttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;

abstract class ApiController extends Controller
{
    public $serializer = [
        'class'     => 'app\common\components\Serializer',
        'pluralize' => false,
    ];

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    protected function verbs()
    {
        return [
            'index'  => ['GET', 'HEAD'],
            'view'   => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['cors'] = [
            'class' => Cors::class,
        ];

        $behaviors['http_authenticator'] = [
            'class'  => JwtHttpBearerAuth::class,
            'except' => ['options'],
        ];

        $behaviors['contentNegotiator']['formats'] = [
            'application/json'         => Response::FORMAT_JSON,
            'application/vnd.api+json' => Response::FORMAT_JSON,
        ];

        return $behaviors;
    }
}
