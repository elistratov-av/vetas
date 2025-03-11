<?php

namespace app\common\components\rbac;

use app\common\models\UserModel;
use app\models\db\Specialists;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\db\Query;
use yii\rbac\Item;

/**
 * Class DbManager
 * @package app\common\components\rbac
 */
class DbManager extends \yii\rbac\DbManager
{
    /**
     * @var array
     */
    private $_checkAccessAssignments = [];

    /**
     * {@inheritdoc}
     */
    public function assign($role, $userId, $specialistId = null)
    {
        if (empty($userId)) {
            throw new InvalidArgumentException('Необходимо указать userId');
        }

        list($user, $specialist) = $this->findUserWithSpecialist($userId, $specialistId);
        if (!isset($specialistId)) {
            $specialistId = $specialist->id;
        }

        $assignment = new Assignment([
            'userId' => $userId,
            'specialistId' => $specialistId,
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);

        $this->db->createCommand()
            ->insert($this->assignmentTable, [
                'id_user' => $assignment->userId,
                'id_specialist' => $assignment->specialistId,
                'item_name' => $assignment->roleName,
                'created_at' => $assignment->createdAt,
            ])->execute();

        unset($this->_checkAccessAssignments[$userId][$specialistId]);

        return $assignment;
    }

    /**
     * {@inheritdoc}
     */
    public function revoke($role, $userId, $specialistId = null)
    {
        if (empty($userId)) {
            return false;
        }

        try {
            list($user, $specialist) = $this->findUserWithSpecialist($userId, $specialistId);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        /*
         * Проверка на наличие ТМЦ на балансе
         */
        $need_check_balance = false;

        /** @var Specialists $specialist */
        switch ($role->name) {
            case Role::ROLE_VET_SPECIALIST_GOS:
                $all_roles = $this->getRolesByUser($userId, $specialistId);
                // Если у него есть другая - то все ОК
                $need_check_balance = !array_key_exists(Role::ROLE_VET_SPECIALIST_GOS_AMB, $all_roles);
                break;
            case Role::ROLE_VET_SPECIALIST_GOS_AMB:
                $all_roles = $this->getRolesByUser($userId, $specialistId);
                // Если у него есть другая - то все ОК
                $need_check_balance = !array_key_exists(Role::ROLE_VET_SPECIALIST_GOS, $all_roles);
                break;
        }

        if ($need_check_balance && $specialist->balanceNotEmpty()) {
            throw new InvalidArgumentException (
                'У пользователя на балансе остались ТМЦ. Передайте их или спишите их перед отзывом ролей Вет специалиста '
            );
        }
        /*
         * END
         */

        if (!isset($specialistId)) {
            $specialistId = $specialist->id;
        }

        unset($this->_checkAccessAssignments[$userId][$specialistId]);

        return $this->db->createCommand()
                ->delete(
                    $this->assignmentTable,
                    [
                        'id_user' => $userId,
                        'id_specialist' => $specialistId,
                        'item_name' => $role->name,
                    ]
                )
                ->execute() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAll($userId, $specialistId = null)
    {
        if (empty($userId)) {
            return false;
        }

        $condition = ['id_user' => $userId];
        if (!empty($specialistId)) {
            $condition['id_specialist'] = $specialistId;
        }

        /*
         * Проверка на наличие ТМЦ на балансе
         */
        $this->checkBalanceBeforeRevokeAll($userId, $specialistId = null);

        if (!empty($specialistId)) {
            unset($this->_checkAccessAssignments[$userId][$specialistId]);
        } else {
            unset($this->_checkAccessAssignments[$userId]);
        }

        return $this->db->createCommand()
                ->delete($this->assignmentTable, $condition)
                ->execute() > 0;
    }

    /**
     * Если у пользователя были роли Вет. спец или Вет. специалист выездной бригады
     * надо проверить балансы
     *
     * @param $userId
     * @param null $specialistId
     */
    private function checkBalanceBeforeRevokeAll($userId, $specialistId = null)
    {
        $query = (new Query())
            ->select([
                'a.id_user',
                'a.id_specialist'
            ])
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where([
                'AND',
                '{{a}}.[[item_name]]={{b}}.[[name]]',
                ['a.id_user' => (int)$userId],
                ['b.type' => Item::TYPE_ROLE],
                ['IN', 'a.item_name', [
                    Role::ROLE_VET_SPECIALIST_GOS_AMB,
                    Role::ROLE_VET_SPECIALIST_GOS
                ]
                ]
            ])->distinct();

        if (!empty($specialistId)) {
            $query->andWhere(['a.id_specialist' => (int)$specialistId]);
        }

        foreach ($query->all($this->db) as $row) {
            list($user, $specialist) = $this->findUserWithSpecialist($row['id_user'], $row['id_specialist']);

            /** @var Specialists $specialist */
            if ($specialist->balanceNotEmpty()) {
                throw new InvalidArgumentException (
                    'У пользователя на балансе остались ТМЦ. Передайте их или спишите их перед отзывом ролей'
                );
            }
        }
    }

    /**
     * Checks if the user has the specified permission.
     * @param \app\common\models\UserModel $user
     * @param string                       $permissionName the name of the permission to be checked against
     * @param array                        $params         name-value pairs that will be passed to the rules associated
     *                                                     with the roles and permissions assigned to the user.
     * @return bool whether the user has the specified permission.
     */
    public function checkAccess($user, $permissionName, $params = [])
    {
        if ($user->specialist === null) {
            throw new InvalidCallException('Метод должен быть вызван через Yii::$app->user->can()');
        }

        $userId = $user->id;
        $specialistId = $user->specialist->id;

        if (isset($this->_checkAccessAssignments[$userId][$specialistId])) {
            $assignments = $this->_checkAccessAssignments[$userId][$specialistId];
        } else {
            $assignments = $this->getAssignments($userId, $specialistId);
            if (!isset($this->_checkAccessAssignments[$userId])) {
                $this->_checkAccessAssignments[$userId] = [];
            }
            $this->_checkAccessAssignments[$userId][$specialistId] = $assignments;
        }

        if ($this->hasNoAssignments($assignments)) {
            return false;
        }

        $this->loadFromCache();
        if ($this->items !== null) {
            return $this->checkAccessFromCache($user, $permissionName, $params, $assignments);
        }

        return $this->checkAccessRecursive($user, $permissionName, $params, $assignments);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignment($roleName, $userId, $specialistId = null)
    {
        if (empty($userId)) {
            return null;
        }

        try {
            list($user, $specialist) = $this->findUserWithSpecialist($userId, $specialistId);
        } catch (InvalidArgumentException $e) {
            return null;
        }

        if (!isset($specialistId)) {
            $specialistId = $specialist->id;
        }

        $row = (new Query())
            ->from($this->assignmentTable)
            ->where([
                'id_user' => $userId,
                'id_specialist' => $specialistId,
                'item_name' => $roleName,
            ])
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return new Assignment([
            'userId' => $row['id_user'],
            'specialistId' => $row['id_specialist'],
            'roleName' => $row['item_name'],
            'createdAt' => $row['created_at'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignments($userId, $specialistId = null)
    {
        if (empty($userId)) {
            return [];
        }

        try {
            list($user, $specialist) = $this->findUserWithSpecialist($userId, $specialistId);
        } catch (InvalidArgumentException $e) {
            return [];
        }

        if (!isset($specialistId)) {
            $specialistId = $specialist->id;
        }

        $query = (new Query())
            ->from($this->assignmentTable)
            ->where([
                'id_user' => $userId,
                'id_specialist' => $specialistId,
            ]);

        $assignments = [];
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['id_user'],
                'specialistId' => $row['id_specialist'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
        }

        return $assignments;
    }

    /**
     * {@inheritdoc}
     * The roles returned by this method include the roles assigned via [[$defaultRoles]].
     */
    public function getRolesByUser($userId, $specialistId = null)
    {
        if (empty($userId)) {
            return [];
        }

        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.id_user' => (int)$userId])
            ->andWhere(['b.type' => Item::TYPE_ROLE]);

        if (!empty($specialistId)) {
            $query->andWhere(['a.id_specialist' => (int)$specialistId]);
        }

        $roles = $this->getDefaultRoleInstances();
        foreach ($query->all($this->db) as $row) {
            $roles[$row['name']] = $this->populateItem($row);
        }

        return $roles;
    }

    /**
     * Returns all role assignment information for the specified role.
     * @param string $roleName
     * @return string[] the ids. An empty array will be
     * returned if role is not assigned to any user.
     * @since 2.0.7
     */
    public function getUserIdsByRole($roleName)
    {
        if (empty($roleName)) {
            return [];
        }

        return (new Query())
            ->select('[[id_user]], [[id_specialist]]')
            ->from($this->assignmentTable)
            ->where(['item_name' => $roleName])
            ->all($this->db);
    }

    /**
     * @param int $userId
     * @param int $specialistId
     * @return array
     */
    private function findUserWithSpecialist($userId, $specialistId = null)
    {
        $user = UserModel::findOne(['id' => $userId]);
        if ($user === null) {
            throw new InvalidArgumentException('Пользователь не найден');
        }

        if (!isset($specialistId)) {
            $specialists = $user->specialists;
            if (count($specialists) > 1) {
                throw new InvalidArgumentException('Пользователю назначено несколько специалистов, необходимо указать specialistId');
            } elseif (count($specialists) == 0) {
                throw new InvalidArgumentException('Пользователю не назначены специалисты');
            } else {
                $specialist = reset($specialists);
            }
        } else {
            $specialist = Specialists::findOne([
                'id' => $specialistId,
                'id_user' => $userId,
            ]);
            if ($specialist === null) {
                throw new InvalidArgumentException('Специалист не найден или неправильно указан specialistId');
            }
        }

        return [$user, $specialist];
    }

    /**
     * @inheritDoc
     */
    protected function getDirectPermissionsByUser($userId)
    {
        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->itemTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.id_user' => (int)$userId])
            ->andWhere(['b.type' => Item::TYPE_PERMISSION]);

        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    protected function getInheritedPermissionsByUser($userId)
    {
        $query = (new Query())->select('item_name')
            ->from($this->assignmentTable)
            ->where(['id_user' => (int)$userId]);

        $childrenList = $this->getChildrenList();
        $result = [];
        foreach ($query->column($this->db) as $roleName) {
            $this->getChildrenRecursive($roleName, $childrenList, $result);
        }

        if (empty($result)) {
            return [];
        }

        $query = (new Query())->from($this->itemTable)->where([
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }
}
