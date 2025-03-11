<?php
namespace app\common\behaviors;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Class DiseaseBehavior
 * @package app\common\behaviors
 */
class DiseaseBehavior extends EntityBehavior
{
    public function init()
    {
        parent::init();
    }

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
        $species_count = $query->from('species_diseases')
            ->where(['id_disease' => $this->owner->id])
            ->count();

        return [
            'species_count' => $species_count
        ];
    }

}
