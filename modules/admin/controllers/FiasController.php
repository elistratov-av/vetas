<?php

namespace app\modules\admin\controllers;

use app\common\components\address\FiasRemoteAdapter;
use app\models\db\fias\Version;
use yii\db\Connection;

/**
 * Class FiasController
 * @package app\modules\admin\controllers
 */
class FiasController extends AdminController
{
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
