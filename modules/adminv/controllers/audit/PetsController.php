<?php

namespace app\modules\adminv\controllers\audit;

use app\common\components\rbac\Role;
use app\models\db\Pets;
use app\modules\adminv\controllers\AdminController;
use app\modules\adminv\models\search\PetSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * PetsController implements the CRUD actions for Pets model.
 */
class PetsController extends AdminController
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
                        'roles' => [Role::ROLE_SYSADMIN_GOS, Role::ROLE_MANAGEMENT_GOS],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Pets models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pets model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Pets model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Pets the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pets::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
