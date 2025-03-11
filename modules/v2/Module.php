<?php

namespace app\modules\v2;

use yii\base\Module as YiiModule;
use yii\web\Request;

/**
 * Class Module
 * @package app\modules\v2
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
                'application/json' => \yii\web\JsonParser::class,
            ],
            'ipHeaders' => [
                'X-Real-Ip'
            ],
        ],
    ];

    public function init(): void
    {
        parent::init();

        \Yii::$app->setComponents($this->moduleComponents);

        $this->modules = [
            'administration' => modules\administration\Module::class,
            'android' => modules\android\Module::class,
            'change-request' => modules\changeRequest\Module::class,
            'discount' => modules\discount\Module::class,
            'emergency' => modules\emergency\Module::class,
            'faq' => modules\faq\Module::class,
            'faq2' => modules\faq2\Module::class,
            'fias' => modules\fias\Module::class,
            'files' => modules\files\Module::class,
            'found' => modules\found\Module::class,
            'gosvetnadzor' => modules\gosvetnadzor\Module::class,
            'help' => modules\help\Module::class,
            'newsletter' => modules\newsletter\Module::class,
            'notification' => modules\notification\Module::class,
            'organization' => modules\organization\Module::class,
            'payment' => modules\payment\Module::class,
            'pet-hotel' => modules\pethotels\Module::class,
            'pet-owners' => modules\petOwners\Module::class,
            'pets' => modules\pets\Module::class,
            'pricelist' => modules\pricelist\Module::class,
            'quarantine' => modules\quarantine\Module::class,
            'reception' => modules\reception\Module::class,
            'reports' => modules\reports\Module::class,
            'services' => modules\services\Module::class,
            'shelter' => modules\shelter\Module::class,
            'shift-type-ref' => modules\shiftTypeRef\Module::class,
            'specialist' => modules\specialist\Module::class,
            'specializations' => modules\specializations\Module::class,
            'subscriptions' => modules\subscriptions\Module::class,
            'timesheet' => modules\timesheet\Module::class,
            'tmc' => modules\tmc\Module::class,
            'user' => modules\user\Module::class,
            'vaccination-journal' => modules\vaccinationJournal\Module::class,
            'vaccination-station' => modules\vaccinationStation\Module::class,
            'visit' => modules\visit\Module::class,
            'recovery-password' => modules\recoveryPassword\Module::class,
        ];
    }
}
