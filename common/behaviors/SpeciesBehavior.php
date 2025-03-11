<?php
namespace app\common\behaviors;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Class SpeciesBehavior
 * @package app\common\behaviors
 */
class SpeciesBehavior extends EntityBehavior
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
        $query = new Query();
        $service_count = $query->from('species_services')
            ->where(['id_species' => $this->owner->id])
            ->count();

        $query = new Query();
        $disease_count = $query->from('species_diseases')
            ->where(['id_species' => $this->owner->id])
            ->count();

        return [
            'service_count' => $service_count,
            'disease_count' => $disease_count
        ];
    }

}
