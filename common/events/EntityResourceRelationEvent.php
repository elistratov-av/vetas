<?php


namespace app\common\events;

use yii\base\Event;

class EntityResourceRelationEvent extends Event
{
    public $parent_entity_name;
    public $slave_entity_name;
    public $parent_entity_id;
    public $slave_entity_id;
}
