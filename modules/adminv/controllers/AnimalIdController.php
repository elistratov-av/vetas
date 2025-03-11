<?php

namespace app\modules\adminv\controllers;

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
 * @package app\modules\adminv\controllers
 */
class AnimalIdController extends AdminController
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
        \Yii::info("Конфликт №$id разрешен: сущность $model->type:$model->our_id обновленна от внешней $model->type:$model->data['id']", 'animalid_input');

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
        \Yii::info("Конфликт №$id разрешен: пропуск", 'animalid_input');

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
