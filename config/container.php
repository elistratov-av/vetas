<?php

use app\common\components\entity\EntityManager;
use \app\common\components\entity\EntityConfigManager;

return [
    'definitions' => [
        'entityRules' => 'app\common\components\entity\EntityRules',
        'entityResource' => 'app\modules\v1\models\EntityResource',
        'entityNestedCollection' => 'app\common\components\entity\EntityNestedCollection',
        'entityMigration' => 'app\common\components\entity\EntityMigration',
        'app\common\components\media\MediaRepositoryInterface' => 'app\common\components\media\UploadFileRepository',
        'resourceFileRepository' => 'app\common\components\media\ResourceFileRepository',
        \app\common\components\ticket\TicketGeneratorInterface::class => function($container, $params, $config) {
            /** @var \app\models\db\Visits|\app\modules\soap\models\Visits $visit */
            $visit = $config['visit'];
            if ($visit->isLiveQueue()) {
                return new \app\common\components\ticket\LiveQueueTicketGenerator($config);
            } else {
                return new \app\common\components\ticket\ChannelTicketGenerator($config);
            }
        },
        'sign' => \app\common\components\jcpSign\JcpSignService::class
    ],
    'singletons' => [
        'entityManager' => function () {
            /** @var \app\common\components\entity\EntityNestedCollection $nestedCollection */
            $nestedCollection = Yii::$container->get('entityNestedCollection');

            $config_manager = new EntityConfigManager();

            return new EntityManager($config_manager , $nestedCollection);
        },
    ]
];
