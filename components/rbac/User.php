<?php

namespace app\common\components\rbac;

/**
 * Class User
 * @package app\common\components\rbac
 */
class User extends \yii\web\User
{
    /**
     * @var array
     */
    private $_access = [];

    /**
     * {@inheritdoc}
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if (($accessChecker = $this->getAccessChecker()) === null) {
            return false;
        }
        /* @var \app\common\models\UserModel $user */
        $user = $this->getIdentity();
        if ($user === null) {
            return false;
        }
        if ($user->is_blocked === true) {
            return false;
        }
        if (!empty($user->block_until)) {
            try {
                $time = (new \DateTime($user->block_until))->getTimestamp();
            } catch (\Throwable $e) {
                $time = false;
            }
            if ($time === false || $time >= time()) {
                return false;
            }
        }
        if ($user->specialist === null) {
            return false;
        }
        if (empty($user->specialist->id_organization) || $user->specialist->organization === null) {
            return false;
        }
        if ($user->specialist->isExpelledAtDate()) {
            return false;
        }
        if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
            return $this->_access[$permissionName];
        }
        $access = $accessChecker->checkAccess($user, $permissionName, $params);
        if ($allowCaching && empty($params)) {
            $this->_access[$permissionName] = $access;
        }

        return $access;
    }

}
