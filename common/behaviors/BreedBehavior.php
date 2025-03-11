<?php
namespace app\common\behaviors;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Class BreedBehavior
 * @package app\common\behaviors
 */
class BreedBehavior extends EntityBehavior
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
        $disease_count = $query->from('breeds_diseases')
            ->where(['id_breed' => $this->owner->id]
            )->count();

        return [
            'disease_count' => $disease_count
        ];
    }
}
