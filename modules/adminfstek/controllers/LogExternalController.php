<?php

namespace app\modules\adminfstek\controllers;

use app\models\db\admin\AdminUser;
use app\models\db\audit\LogExternalAuth;
use app\modules\adminfstek\models\search\LogExternalAuthSearch;
use app\modules\adminfstek\models\search\LogExternalDataSearch;
use yii\filters\AccessControl;

/**
 * Class LogExternalController
 * @package app\modules\adminfstek\controllers
 */
class LogExternalController extends AdminController
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
                        'roles' => [AdminUser::ROLE_SECURITY],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionAuthorization()
    {
        $searchModel = new LogExternalAuthSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $successOptions = LogExternalAuth::successOptions();

        error_reporting(0);
        $data = compact('dataProvider', 'searchModel', 'successOptions');

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionDataExchange()
    {
        $searchModel = new LogExternalDataSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        error_reporting(0);
        $data = compact('dataProvider', 'searchModel', 'successOptions');

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string
     */
    private function viewName()
    {
        return $this->action->id;
    }
}
