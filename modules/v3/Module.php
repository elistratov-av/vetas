<?php
namespace app\modules\v3;

use yii\base\Module as YiiModule;
use yii\web\Request;

class Module extends YiiModule
{
    protected $moduleComponents = [
        'request' => [
            'class' => Request::class,
            'enableCsrfValidation' => false,
            'enableCookieValidation' => false,
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
            'ipHeaders' => [
                'X-Real-Ip',
            ],
        ],
    ];

    public function init()
    {
        parent::init();

        \Yii::$app->setComponents($this->moduleComponents);

        $this->modules = [
            'analytics' => [
                'class' => modules\analytics\Module::class,
            ],
            'shelters' => [
                'class' => modules\shelters\Module::class,
            ],
            'external-services' => [
                'class' => modules\externalServices\Module::class,
            ],
        ];
    }
}
