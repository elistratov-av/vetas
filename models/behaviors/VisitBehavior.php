<?php

namespace app\models\behaviors;

use app\common\exceptions\DeleteForbiddenHttpException;
use app\common\exceptions\UpdateForbiddenHttpException;
use app\common\models\UserModel;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;

/**
 * Class VisitBehavior
 * @package app\modules\v2\behaviors
 */
class VisitBehavior extends Behavior
{
    /**
     * @var \app\models\db\Visits
     */
    public $owner;

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => function($event) {
//                $this->setAuthor();
            },
            ActiveRecord::EVENT_BEFORE_DELETE => function($event) {
//                $this->checkAccess($event);
            }
        ];
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
        /** @var UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        if (!$user->specialist) {
            return;
        }

        if ($user->specialist->id_organization != $this->owner->id_organization) {
            switch ($event->name) {
                case ActiveRecord::EVENT_BEFORE_UPDATE:
                    throw new UpdateForbiddenHttpException();

                case ActiveRecord::EVENT_BEFORE_DELETE:
                    throw new DeleteForbiddenHttpException();

                default:
                    throw new ForbiddenHttpException('Нет прав доступа к ресурсу');
            }
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function setAuthor()
    {
        if (!empty($this->owner->author)) {
            return;
        }

        /** @var UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        if (!$user->specialist) {
            return;
        }

        $this->owner->author = $user->specialist->id;
    }
}
