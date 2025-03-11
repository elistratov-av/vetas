<?php

namespace app\modules\admin\controllers;

use app\common\components\rbac\Role;
use app\modules\animalid\models\db\ConflictsModel;
use app\modules\animalid\models\db\ConverterModel;
use app\modules\animalid\models\db\ErrorsModel;
use app\modules\animalid\models\db\FilterModel;
use app\modules\animalid\models\db\Logs;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class FiasController
 * @package app\modules\admin\controllers
 */
class AnimalIdController extends AdminController
{

    /**
 * @return string|\yii\web\Response
 */
    public function actionConflicts()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ConflictsModel::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('conflicts', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionMerge(int $id)
    {
        if(!($model = ConflictsModel::findOne($id)))
            throw new NotFoundHttpException('Не найден');

        $model->merge();

        return $this->redirect('conflicts');
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSkip(int $id)
    {
        if(!($model = ConflictsModel::findOne($id)))
            throw new NotFoundHttpException('Не найден');

        $model->delete();

        return $this->redirect('conflicts');
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionErrors()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ErrorsModel::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('errors', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionLogs()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Logs::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('logs', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionFilters()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => FilterModel::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('filters', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionConverters()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ConverterModel::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('converters', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
