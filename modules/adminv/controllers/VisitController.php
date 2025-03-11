<?php

namespace app\modules\adminv\controllers;

use app\models\db\ShiftType;
use Yii;
use app\common\components\rbac\Role;
use app\models\db\Visits;
use app\modules\adminv\models\search\VisitSearch;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class VisitController
 * @package app\modules\adminv\controllers
 */
class VisitController extends AdminController
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
    public function actionIndex()
    {
        $searchModel = new VisitSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInfo($id)
    {
        $model = Visits::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Прием не найден');
        }

        $mosruChannel = (new Query())
            ->select('id')
            ->from(ShiftType::tableName())
            ->where(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])
            ->scalar();

        return $this->render('info', [
            'model' => $model,
            'mosruChannel' => $mosruChannel,
        ]);
    }

}
