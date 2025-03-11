<?php

namespace app\modules\adminfstek\controllers;

use app\models\db\admin\AdminUser;
use app\models\db\audit\AuditLog;
use app\models\db\audit\LogUsersAccessChange;
use app\models\db\audit\LogUsersAuth;
use app\models\db\audit\LogUsersBlockAuto;
use app\models\db\audit\LogUsersBlockManual;
use app\models\db\audit\LogUsersChange;
use app\modules\adminfstek\models\search\LogAuditSearch;
use app\modules\adminfstek\models\search\LogUsersAccessChangeSearch;
use app\modules\adminfstek\models\search\LogUsersAuthSearch;
use app\modules\adminfstek\models\search\LogUsersBlockAutoSearch;
use app\modules\adminfstek\models\search\LogUsersBlockManualSearch;
use app\modules\adminfstek\models\search\LogUsersChangeSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class LogUsersController
 * @package app\modules\adminfstek\controllers
 */
class LogUsersController extends AdminController
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
        $searchModel = new LogUsersAuthSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $targetOptions = LogUsersAuth::targetOptions();
        $typeOptions = LogUsersAuth::typeOptions();
        $successOptions = LogUsersAuth::successOptions();

        $data = compact('dataProvider', 'searchModel', 'targetOptions', 'typeOptions', 'successOptions');

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_' . $this->viewName() . '_inner', $data);
        }

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionBlockManual()
    {
        $searchModel = new LogUsersBlockManualSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $targetOptions = LogUsersBlockManual::targetOptions();
        $typeOptions = LogUsersBlockManual::typeOptions();
        $successOptions = LogUsersBlockManual::successOptions();

        $data = compact('dataProvider', 'searchModel', 'targetOptions', 'typeOptions', 'successOptions');

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_' . $this->viewName() . '_inner', $data);
        }

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionBlockAuto()
    {
        $searchModel = new LogUsersBlockAutoSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $targetOptions = LogUsersBlockAuto::targetOptions();
        $typeOptions = LogUsersBlockAuto::typeOptions();
        $successOptions = LogUsersBlockAuto::successOptions();

        $data = compact('dataProvider', 'searchModel', 'targetOptions', 'typeOptions', 'successOptions');

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_' . $this->viewName() . '_inner', $data);
        }

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionGeneral()
    {
        $searchModel = new LogUsersChangeSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $targetOptions = LogUsersChange::targetOptions();
        $typeOptions = LogUsersChange::typeOptions();
        $successOptions = LogUsersChange::successOptions();

        $data = compact('dataProvider', 'searchModel', 'targetOptions', 'typeOptions', 'successOptions');

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_' . $this->viewName() . '_inner', $data);
        }

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionAccess()
    {
        $searchModel = new LogUsersAccessChangeSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $targetOptions = LogUsersAccessChange::targetOptions();
        $typeOptions = LogUsersAccessChange::typeOptions();
        $successOptions = LogUsersAccessChange::successOptions();

        $data = compact('dataProvider', 'searchModel', 'targetOptions', 'typeOptions', 'successOptions');

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_' . $this->viewName() . '_inner', $data);
        }

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionAudit()
    {
        $searchModel = new LogAuditSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        $typeOptions = LogAuditSearch::typeOptions();
        $successOptions = LogAuditSearch::successOptions();

        $data = compact('dataProvider', 'searchModel', 'typeOptions', 'successOptions');

        if (\Yii::$app->request->isAjax) {
            return $this->renderPartial('_' . $this->viewName() . '_inner', $data);
        }

        return $this->render($this->viewName(), $data);
    }

    /**
     * @return string
     */
    public function actionLogDetail()
    {
        $id = \Yii::$app->request->get('id');
        $model = AuditLog::findOne(['id' => $id]);

        if ($model === null) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            throw new NotFoundHttpException('Запись лога не найдена');
        }

        return $this->renderPartial('log-detail', [
            'log_row' => $model,
        ]);
    }

    /**
     * @return string
     */
    private function viewName()
    {
        return $this->action->id;
    }
}
