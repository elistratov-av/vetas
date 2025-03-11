<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * Class RbacController
 * @package app\modules\adminv\controllers
 */
class RbacController extends AdminController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['roles', 'permissions', 'table'],
                        'allow' => true,
                        'roles' => [Role::ROLE_SYSADMIN_GOS],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionRoles()
    {
        $auth = $this->authManager();
        $roles = $auth->getRoles();

        $roleNames = Role::gosOrgRoles();

        foreach ($roles as $key => $role) {
            if (!in_array($role->name, $roleNames, true)) {
                unset($roles[$key]);
            }
        }

        $dataProvider = $this->createDataProvider($roles);

        return $this->render('roles', compact('dataProvider', 'auth'));
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTable()
    {
        $auth = $this->authManager();
        $roles = $auth->getRoles();

        $roleNames = Role::gosOrgRoles();

        $all_permissions = [];

        foreach ($roles as $key => $role) {
            // только согласованные
            if (!in_array($role->name, $roleNames, true)) {
                unset($roles[$key]);
                continue;
            }

            $permissions = $auth->getPermissionsByRole($role->name);
            foreach ($permissions as $permission_name => $permission_info){
                if (!array_key_exists($permission_name, $all_permissions)){
                    $all_permissions[$permission_name]['description'] =
                        $permission_info->description . '<br><small>(' . $permission_name .')</small>';
                }
                $all_permissions[$permission_name]['roles'][] = $role->name;
            }
        }

        ArrayHelper::multisort($all_permissions, 'description');

        $dataProvider = new  ArrayDataProvider([
            'allModels' => $all_permissions,
            'pagination' => false,
        ]);


        return $this->render(
            'table', compact(
                'dataProvider', 'auth', 'roles'
            ));
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionPermissions()
    {
        $auth = $this->authManager();
        $permissions = $auth->getPermissions();

        $dataProvider = $this->createDataProvider($permissions);

        return $this->render('permissions', compact('dataProvider', 'auth'));
    }

    /**
     * @param \yii\rbac\Item[] $models
     * @return \yii\data\ArrayDataProvider
     */
    protected function createDataProvider($models)
    {
        $dataProvider = new ArrayDataProvider([
            'key' => function ($model) {
                /* @var $model \yii\rbac\Item */
                return $model->name;
            },
            'allModels' => $models,
        ]);

        return $dataProvider;
    }
}
