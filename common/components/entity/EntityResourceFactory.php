<?php

namespace app\common\components\entity;


use app\modules\v1\models\EntityResource;
use yii\di\Container;

class EntityResourceFactory
{

    /**
     * @param $name
     * @return EntityResource
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public static function getResource($name)
    {
        \Yii::$container->set('entityInstance', function(Container $container) use ($name) {
            /** @var EntityManager $manager */
            $manager = $container->get('entityManager');
            $entity = $manager->getEntity($name);
            $entity->getTableName();

            return $entity;
        });

        /** @var EntityResource $entityInstance */
        $entityInstance = \Yii::$container->get('entityInstance');
        if (!($entityInstance instanceof EntityInstance)) {
            return $entityInstance;
        }

        /** @var EntityResource $resource */
        $resource = \Yii::$container->get('entityResource');

        return $resource;
    }
}
