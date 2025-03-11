<?php

namespace app\modules\v1\actions;

use yii\rest\Action;

/**
 * Class EntityAction
 * @package app\common\actions
 */
abstract class EntityAction extends Action
{
    use EntityActionTrait;
}
