<?php

namespace app\modules\v1;

use yii\base\Module as YiiModule;
use yii\web\Request;

/**
 * Class Module
 * @package app\modules\v1
 */
class Module extends YiiModule
{
    /**
     * @var array
     */
    protected $moduleComponents = [
        'request' => [
            'class' => Request::class,
            'enableCsrfValidation' => false,
            'enableCookieValidation' => false,
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/vnd.api+json' => 'tuyakhov\jsonapi\JsonApiParser',
                'application/json' => 'tuyakhov\jsonapi\JsonApiParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ],
        ],
    ];

    public function init(): void
    {
        parent::init();

        \Yii::$app->setComponents($this->moduleComponents);
    }
}
