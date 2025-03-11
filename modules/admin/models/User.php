<?php
namespace app\modules\admin\models;

use app\models\db\Users;
use app\models\db\UsersSpecializations;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;

/**
 * Class User
 * @package app\modules\admin\models
 */
class User extends Users
{
    const STATUS_DELETED_OR_BLOCKED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @return ActiveQuery
     */
    public function getSpecialist(): ActiveQuery
    {
        return $this->hasMany(Specialist::class, ['id_user' => 'id'])
            ->where(['expel_date' => null]);
    }

    /**
     * @return ActiveQuery
     */
    public function getUsersSpecializations(): ActiveQuery
    {
        return $this->hasMany(UsersSpecializations::class, ['id_user' => 'id']);
    }

    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * @param $password
     * @throws Exception
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }
}
