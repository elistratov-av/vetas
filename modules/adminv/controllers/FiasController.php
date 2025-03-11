<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\models\db\fias\Version;
use yii\db\Connection;
use yii\filters\AccessControl;

/**
 * Class FiasController
 * @package app\modules\adminv\controllers
 */
class FiasController extends AdminController
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

    /** @var $addrSrv FiasRemoteAdapter */
    public $addrSrv;
    /**
     * @param $action
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->addrSrv = \Yii::$app->addressService;
        return parent::beforeAction($action);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionUpdates()
    {
        $history = $this->addrSrv->getHistory();
        return $this->render('updates', [
            'history' => $history
        ]);
    }

}
