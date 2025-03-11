<?php

namespace app\modules\v2\modules\administration\models;

use yii\db\Exception;
use yii\web\BadRequestHttpException;

class RolesModel
{
    /**
     * @param int $limit
     * @param int $page
     * @return array
     * @throws Exception
     */
    public function userRoleList(int $limit, int $page): array
    {
        $sql = 'SELECT name, description FROM auth_item WHERE type = 1 ORDER BY name ASC';

        $totalCount = \Yii::$app->db->createCommand($sql)
            ->query()
            ->count();

        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . ($page * $limit - $limit);

        return [
            'pages_count' => (int)(($totalCount + $limit - 1) / $limit),
            'total_count' => $totalCount,
            'roles' => \Yii::$app->db->createCommand($sql)->queryAll(),
        ];
    }

    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     * @throws Exception
     */
    public function all(array $filter, int $limit, int $page): array
    {
        $result = [];

        foreach ($this->getParentRole($filter, $limit, $page)['roles'] as $role) {
            $result[] = $this->getRolesInfo($role);
        }

        return $result;
    }

    /**
     * @param array $filter
     * @return string[]
     */
    private function getWhere(array $filter): array
    {
        $where = ['auth_item.type = 1 '];

        if (empty($filter['name']) === false) {
            $where = array_merge($where, ['LOWER(auth_item.name) LIKE \'%' . mb_strtolower($filter['name']) . '%\'']);
        }

        if (empty($filter['description']) === false) {
            $where = array_merge($where, ['LOWER(auth_item.description) LIKE \'%' . mb_strtolower($filter['description']) . '%\'']);
        }

        return $where;
    }

    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     * @throws Exception
     */
    private function getParentRole(array $filter, int $limit, int $page): array
    {
        $sql = 'SELECT
                auth_item.name as name,
                auth_item.description as description,
                (
                    SELECT array_to_string(array_agg(child), \',\') FROM auth_item_child WHERE auth_item_child.parent = auth_item.name
                ) as childs            
            FROM auth_item
            WHERE ' . implode(" AND ", $this->getWhere($filter)) . '
            ORDER BY name ASC';

        $totalCount = \Yii::$app->db->createCommand($sql)
            ->query()
            ->count();

        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . ($page * $limit - $limit);

        return [
            'pages_count' => (int)(($totalCount + $limit - 1) / $limit),
            'total_count' => $totalCount,
            'roles' => \Yii::$app->db->createCommand($sql)->queryAll(),
        ];
    }

    /**
     * @param array $role
     * @return array
     * @throws Exception
     */
    private function getRolesInfo(array $role): array
    {
        $elements = \Yii::$app->db
            ->createCommand('SELECT 
                    auth_item.name,
                    auth_item.description
                FROM  auth_item
                WHERE type <> 1 
                AND name IN (\'' . join("','", explode(",", $role['childs'])) . '\') 
                ORDER BY name ASC')
            ->queryAll();

        return [
            'name' => $role['name'],
            'description' => $role['description'],
            'elements' => $elements,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function elements(): array
    {
        return \Yii::$app->db
            ->createCommand('SELECT 
                    auth_item.name,
                    auth_item.description
                FROM  auth_item
                WHERE type <> 1
                ORDER BY name ASC')
            ->queryAll();
    }

    /**
     * @param array $data
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function addElements(array $data): bool
    {
        $auth = \Yii::$app->authManager;

        $role = $auth->getRole($data['role']);

        if (empty($role) === true) {
            throw new BadRequestHttpException(
                sprintf('Не найдена роль: %s', $data['role'])
            );
        }

        foreach ($data['elements'] as $element) {
            $permission = $auth->getPermission($element);

            if (empty($permission) === true) {
                throw new BadRequestHttpException(
                    sprintf('Не найден элемент: %s', $element)
                );
            }

            if ($auth->hasChild($role, $permission) === false) {
                $auth->addChild($role, $permission);
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @return bool|string
     * @throws BadRequestHttpException
     */
    public function deleteElements(array $data) {

        $auth = \Yii::$app->authManager;

        $permission = $auth->getPermission($data['elements']);

        if (empty($permission) === true) {
            throw new BadRequestHttpException(
                sprintf('Не найден элемент: %s', $data['elements'])
            );
        }

        $role = $auth->getRole($data['role']);

        if (empty($role) === true) {
            throw new BadRequestHttpException(
                sprintf('Не найдена роль: %s', $data['role'])
            );
        }

        try {
            $auth->removeChild($role, $permission);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }
}
