<?php
/**
 * @author Serge Postrash <jexy.ru@gmail.com>
 */

namespace app\common\behaviors;

use app\common\models\UserModel;
use yii\db\ActiveRecord;

/**
 * Class SpecialistsUpdateReasonBehavior
 * @package app\common\behaviors
 */
class SpecialistsUpdateReasonBehavior extends EntityBehavior
{
    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'additionalFields',
            ActiveRecord::EVENT_AFTER_INSERT => 'additionalFields',
            ActiveRecord::EVENT_AFTER_UPDATE => 'additionalFields',
        ];
    }
    /**
     * @param \yii\base\Event $event
     */
    public function additionalFields($event)
    {
        $this->owner->additionalFields['created_date'] = 'created_date';
        $this->owner->additionalFields['created_user'] = 'created_user';
        $this->owner->additionalFields['created_username'] = 'creator';
    }

    /**
     * @return string
     */
    public function getCreated_date()
    {
        return $this->owner->created_at;
    }

    /**
     * @return string
     */
    public function getCreated_user()
    {
        return $this->owner->created_by;
    }

    /**
     * @return string
     */
    public function getCreator()
    {
        if (empty($this->owner->created_by)) {
            return null;
        }

        /* @var $user \app\common\models\UserModel */
        $user = UserModel::find()
            ->andWhere(['id' => $this->owner->created_by])
            ->with('specialist')
            ->limit(1)
            ->one();

        if ($user === null) {
            return null;
        }

        $username = ($user->specialist === null)
            ? $user->login
            : trim($user->specialist->f_fio . ' ' . $user->specialist->i_fio . ' ' . $user->specialist->o_fio);

        return $username;
    }
}
