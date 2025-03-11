<?php

namespace app\common\behaviors;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Class TmcTypeBehavior
 * @package app\common\behaviors
 */
class TmcTypeBehavior extends EntityBehavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'setCounters'
        ];
    }

    public function setCounters($event)
    {
        $this->owner->additionalFields['counters'] = 'counters';
    }

    public function getCounters()
    {
        // $query = new Query();
        // $services_count = $query->from('service_tmcs')
        //    ->where(['id_tmc_type' => $this->owner->id]
        //    )->count();
        $services_count = 0;

        $query2 = new Query();
        $tmcs_count = $query2->from('tmc')
            ->where(['id_tmc_type' => $this->owner->id]
            )->count();

        return [
            'services_count' => $services_count,
            'tmcs_count' => $tmcs_count,
        ];
    }
}
