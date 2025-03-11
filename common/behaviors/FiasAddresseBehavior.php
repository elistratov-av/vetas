<?php

namespace app\common\behaviors;

use app\common\components\entity\EntityResourceFactory;
use app\models\db\Districts;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class DrugBehavior
 * @package app\common\behaviors
 */
class FiasAddresseBehavior extends EntityBehavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => function($event) {
                $this->setRelations();
            },
            ActiveRecord::EVENT_BEFORE_UPDATE => function($event) {
                $this->setRelations();
            },
        ];
    }


    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function setRelations()
    {
        if($this->owner->id_area || $this->owner->id_district)
            return true;

        if($oktmo = $this->owner->oktmo)
        {
            $this->owner->id_district   = Districts::findOne(['oktmo' => $oktmo]) ? Districts::findOne(['oktmo' => $oktmo])->id : null;
            $this->owner->id_area       = $this->owner->id_district ? Districts::findOne(['oktmo' => $oktmo])->id_area : null;
        }

        EntityResourceFactory::getResource('fias-addresses');

        return true;
    }
}
