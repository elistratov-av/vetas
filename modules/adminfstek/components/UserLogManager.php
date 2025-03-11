<?php

namespace app\modules\adminfstek\components;

use app\models\db\admin\AdminUser;
use app\models\db\audit\LogUsersAccessChange;
use app\models\db\audit\LogUsersAuth;
use app\models\db\audit\LogUsersBlockAuto;
use app\models\db\audit\LogUsersBlockManual;
use app\models\db\audit\LogUsersChange;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\Request;

/**
 * Class UserLogManager
 * @package app\modules\adminfstek\components
 */
class UserLogManager extends Component
{
    use LogNotificationsTrait;

    /**
     * @param \app\common\models\UserModel|string $user
     */
    public static function successApiLogin($user)
    {
        if (\Yii::$app->controller->action->id != 'logout') {
            // иначе при получении токена появляется лишняя запись о логине
            try {
                $data = self::prepareData($user, LogUsersAuth::TARGET_FRONTEND, LogUsersAuth::TYPE_LOGIN, true);
                $model = new LogUsersAuth($data);
                $model->save();
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage());
                self::notifyLogFailed(LogUsersAuth::tableName());
            }
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     */
    public static function errorApiLogin($user)
    {
        try {
            $data = self::prepareData($user, LogUsersAuth::TARGET_FRONTEND, LogUsersAuth::TYPE_LOGIN, false);
            $model = new LogUsersAuth($data);
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAuth::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     */
    public static function successApiLogout($user)
    {
        try {
            $data = self::prepareData($user, LogUsersAuth::TARGET_FRONTEND, LogUsersAuth::TYPE_LOGOUT, true);
            $model = new LogUsersAuth($data);
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAuth::tableName());
        }
    }

    /**
     * @param \yii\web\UserEvent $event
     * @return bool
     */
    public static function successAdminLogin($event)
    {
        /* @var $user \app\models\db\admin\AdminUser|\app\common\models\UserModel */
        $user = $event->identity;

        try {
            $target = ($user instanceof AdminUser) ? LogUsersAuth::TARGET_ADMIN : LogUsersAuth::TARGET_VETADMIN;
            $data = self::prepareData($user, $target, LogUsersAuth::TYPE_LOGIN, true);
            $model = new LogUsersAuth($data);
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAuth::tableName());
        }

        return $event->isValid;
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param int                                   $target
     */
    public static function errorAdminLogin($user, $target = null)
    {
        try {
            $target = $target ?? LogUsersAuth::TARGET_ADMIN;
            $data = self::prepareData($user, $target, LogUsersAuth::TYPE_LOGIN, false);
            $model = new LogUsersAuth($data);
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAuth::tableName());
        }
    }

    /**
     * @param \yii\web\UserEvent $event
     * @return bool
     */
    public static function successAdminLogout($event)
    {
        /* @var $user \app\models\db\admin\AdminUser|\app\common\models\UserModel */
        $user = $event->identity;

        try {
            $target = ($user instanceof AdminUser) ? LogUsersAuth::TARGET_ADMIN : LogUsersAuth::TARGET_VETADMIN;
            $data = self::prepareData($user, $target, LogUsersAuth::TYPE_LOGOUT, true);
            $model = new LogUsersAuth($data);
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAuth::tableName());
        }

        return $event->isValid;
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     */
    public static function manualApiUserBlock($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockManual::TARGET_FRONTEND, LogUsersBlockManual::TYPE_BLOCK, $is_success);
            $model = new LogUsersBlockManual();
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockManual::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     */
    public static function manualApiUserUnblock($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockManual::TARGET_FRONTEND, LogUsersBlockManual::TYPE_UNBLOCK, $is_success);
            $model = new LogUsersBlockManual();
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockManual::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function manualAdminUserBlock($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockManual::TARGET_ADMIN, LogUsersBlockManual::TYPE_BLOCK, $is_success);
            $model = new LogUsersBlockManual();
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockManual::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function manualAdminUserUnblock($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockManual::TARGET_ADMIN, LogUsersBlockManual::TYPE_UNBLOCK, $is_success);
            $model = new LogUsersBlockManual();
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockManual::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     * @param string $block_until
     */
    public static function autoAuthErrorApiUserBlock($user, $is_success, $block_until = null)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockAuto::TARGET_FRONTEND, LogUsersBlockAuto::TYPE_AUTH_ERROR, $is_success);
            $data['block_until'] = $block_until;
            $model = new LogUsersBlockAuto();
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockAuto::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     * @param string $block_until
     */
    public static function autoInactiveApiUserBlock($user, $is_success, $block_until = null)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockAuto::TARGET_FRONTEND, LogUsersBlockAuto::TYPE_INACTIVITY, $is_success);
            $data['block_until'] = $block_until;
            $model = new LogUsersBlockAuto();
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockAuto::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     * @param string $block_until
     */
    public static function autoAuthErrorAdminUserBlock($user, $is_success, $block_until = null)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockAuto::TARGET_ADMIN, LogUsersBlockAuto::TYPE_AUTH_ERROR, $is_success);
            $data['block_until'] = $block_until;
            $model = new LogUsersBlockAuto();
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockAuto::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     * @param string $block_until
     */
    public static function autoInactiveAdminUserBlock($user, $is_success, $block_until = null)
    {
        try {
            $data = self::prepareData($user, LogUsersBlockAuto::TARGET_ADMIN, LogUsersBlockAuto::TYPE_INACTIVITY, $is_success);
            $data['block_until'] = $block_until;
            $model = new LogUsersBlockAuto();
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersBlockAuto::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function createAdminUserAccess($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersAccessChange::TARGET_ADMIN, LogUsersAccessChange::TYPE_CREATE, $is_success);
            $model = new LogUsersAccessChange();
            list($before, $after) = self::prepareDiff($user, 'access', 'create');
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAccessChange::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     * @param array $roles
     */
    public static function createApiUserAccess($user, $is_success, $roles = null)
    {
        try {
            $data = self::prepareData($user, LogUsersAccessChange::TARGET_FRONTEND, LogUsersAccessChange::TYPE_CREATE, $is_success);
            $model = new LogUsersAccessChange();
            list($before, $after) = self::prepareDiff($user, 'access', 'create', $roles);
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAccessChange::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function changeAdminUserAccess($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersAccessChange::TARGET_ADMIN, LogUsersAccessChange::TYPE_EDIT, $is_success);
            $model = new LogUsersAccessChange();
            list($before, $after) = self::prepareDiff($user, 'access', 'edit');
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAccessChange::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     * @param array $roles
     */
    public static function changeApiUserAccess($user, $is_success, $roles = null)
    {
        try {
            $data = self::prepareData($user, LogUsersAccessChange::TARGET_FRONTEND, LogUsersAccessChange::TYPE_EDIT, $is_success);
            $model = new LogUsersAccessChange();
            list($before, $after) = self::prepareDiff($user, 'access', 'edit', $roles);
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersAccessChange::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function createAdminUser($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersChange::TARGET_ADMIN, LogUsersChange::TYPE_CREATE, $is_success);
            $model = new LogUsersChange();
            list($before, $after) = self::prepareDiff($user, 'general', 'create');
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersChange::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     */
    public static function createApiUser($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersChange::TARGET_FRONTEND, LogUsersChange::TYPE_CREATE, $is_success);
            $model = new LogUsersChange();
            list($before, $after) = self::prepareDiff($user, 'general', 'create');
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersChange::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function changeAdminUser($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersChange::TARGET_ADMIN, LogUsersChange::TYPE_EDIT, $is_success);
            $model = new LogUsersChange();
            list($before, $after) = self::prepareDiff($user, 'general', 'edit');
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersChange::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     */
    public static function changeApiUser($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersChange::TARGET_FRONTEND, LogUsersChange::TYPE_EDIT, $is_success);
            $model = new LogUsersChange();
            list($before, $after) = self::prepareDiff($user, 'general', 'edit');
            if (empty($before) && empty($after)) {
                return;
            }
            $data['before'] = $before;
            $data['after'] = $after;
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersChange::tableName());
        }
    }

    /**
     * @param \app\models\db\admin\AdminUser|string $user
     * @param bool $is_success
     */
    public static function deleteAdminUser($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersChange::TARGET_ADMIN, LogUsersChange::TYPE_DELETE, $is_success);
            $model = new LogUsersChange();
            $data['before'] = ['is_deleted' => false];
            $data['after'] = ['is_deleted' => $user->is_deleted];
            $model->load($data, '');
            $model->save();
            self::updateRecipientsList();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersChange::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|string $user
     * @param bool $is_success
     */
    public static function deleteApiUser($user, $is_success)
    {
        try {
            $data = self::prepareData($user, LogUsersChange::TARGET_FRONTEND, LogUsersChange::TYPE_DELETE, $is_success);
            $model = new LogUsersChange();
            $data['before'] = ['is_deleted' => false];
            $data['after'] = ['is_deleted' => $user->is_deleted];
            $model->load($data, '');
            $model->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage());
            self::notifyLogFailed(LogUsersChange::tableName());
        }
    }

    /**
     * @param \app\common\models\UserModel|\app\models\db\admin\AdminUser|string $user
     * @param int $target
     * @param int $type
     * @param bool $success
     * @return array
     */
    private static function prepareData($user, $target, $type, $success)
    {
        $data = [
            'login' => (is_string($user) ? $user : $user->login),
            'id_user' => (is_string($user) ? $user : $user->id),
            'target' => $target,
            'type' => $type,
            'is_success' => $success,
        ];

        $request = \Yii::$app->request;
        if ($request instanceof Request) {
            $data['ip'] = $request->getUserIP();
            $userAgent = $request->getUserAgent();
            $data['ua'] = $userAgent;
            if (!empty($userAgent) && $target == LogUsersAuth::TARGET_FRONTEND
                && ($type == LogUsersAuth::TYPE_LOGIN || $type == LogUsersAuth::TYPE_LOGOUT)) {
                // запросы к API из приложения?
                if (strpos($userAgent, 'VetasApp') !== false) {
                    $data['target'] = LogUsersAuth::TARGET_ANDROID;
                }
            }
        }

        return $data;
    }

    /**
     * @param \app\models\db\admin\AdminUser|\app\common\models\UserModel $user
     * @param string $scope
     * @param string $type
     * @param array $roles только для специалистов
     * @return array
     */
    private static function prepareDiff($user, $scope, $type, $roles = null)
    {
        $isAdmin = $user instanceof AdminUser;
        $before = null;
        $after = null;

        switch ($scope) {
            case 'access':
                if ($isAdmin) {
                    if ($type == 'edit') {
                        if ($user->isAttributeChanged('role', false)) {
                            $old = $user->getOldAttribute('role');
                            $before = ['role' => ArrayHelper::getValue(AdminUser::roleOptions(), $old)];
                        } else {
                            return [$before, $after];
                        }
                    }
                    $after = ['role' => ArrayHelper::getValue(AdminUser::roleOptions(), $user->role)];
                } else {
                    if (!empty($roles) && is_array($roles)) {
                        $roles = array_filter($roles);
                        $before = ArrayHelper::getValue($roles, 'before');
                        $after = ArrayHelper::getValue($roles, 'after');
                    }
                }
                break;
            case 'general':
                if ($type == 'edit') {
                    $after = $user->getDirtyAttributes();
                    unset($after['role']);
                    unset($after['is_blocked']);
                    if (empty($after)) {
                        return [$before, $after];
                    }
                    $before = [];
                    foreach ($after as $key => $value) {
                        $before[$key] = $user->getOldAttribute($key);
                    }
                } else {
                    $after = $user->getAttributes(null, ['role', 'password', 'last_login', 'ip', 'is_blocked', 'created_at', 'updated_at', 'created_by', 'updated_by', 'auth_key']);
                }
                break;
            default:
                break;
        }

        return [$before, $after];
    }
}
