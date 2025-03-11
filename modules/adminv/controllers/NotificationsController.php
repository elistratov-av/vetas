<?php


namespace app\modules\adminv\controllers;


use app\common\components\rbac\Role;
use app\modules\adminv\models\search\NotificationsSearch;
use yii\filters\AccessControl;

class NotificationsController extends AdminController
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
                        'allow' => true,
                        'roles' => [Role::ROLE_SYSADMIN_GOS],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionLog(){
        $searchModel = new NotificationsSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        return $this->render('log', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
}