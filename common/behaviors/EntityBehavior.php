<?php

namespace app\common\behaviors;

use app\common\components\entity\EntityAccessChecker;
use app\common\exceptions\CreateForbiddenHttpException;
use app\common\exceptions\DeleteForbiddenHttpException;
use app\common\exceptions\UpdateForbiddenHttpException;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;

/**
 * Class EntityBehavior
 * @package app\common\behaviors
 *
 * @property \app\modules\v1\models\EntityResource $owner
 */
abstract class EntityBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $this->owner->on(ActiveRecord::EVENT_BEFORE_INSERT, [$this, 'checkAccess'], null, false);
        $this->owner->on(ActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'checkAccess'], null, false);
        $this->owner->on(ActiveRecord::EVENT_BEFORE_DELETE, [$this, 'checkAccess'], null, false);
    }

    /**
     * @param ModelEvent $event
     * @throws DeleteForbiddenHttpException
     * @throws ForbiddenHttpException
     * @throws UpdateForbiddenHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function checkAccess($event)
    {
        $checker = new EntityAccessChecker();
        $params = [
            'model' => $this->owner,
        ];
        $allow = $checker->checkAccess($this->owner->getAliasName(), $event->name, $params, $this);

        if ($allow === true) {
            return;
        }

        switch ($event->name) {
            case ActiveRecord::EVENT_BEFORE_INSERT:
                throw new CreateForbiddenHttpException();

            case ActiveRecord::EVENT_BEFORE_UPDATE:
                throw new UpdateForbiddenHttpException();

            case ActiveRecord::EVENT_BEFORE_DELETE:
                throw new DeleteForbiddenHttpException();

            default:
                throw new ForbiddenHttpException('Нет прав доступа к ресурсу');
        }
    }
}
