<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\Organization;
use app\modules\admin\models\OrgEditForm;
use app\modules\admin\models\OrgSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class OrganizationController
 * @package app\modules\admin\controllers
 */
class OrganizationController extends AdminController
{

    /**
     * @return string
     */
    public function actionIndex()
    {

        $searchModel = new OrgSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit()
    {
        $organization = Organization::findOne(Yii::$app->request->get('id'));


        if (isset($organization->id)) {
            $model = new OrgEditForm($organization);
            if ($model->load(Yii::$app->request->post()) && $model->editOrg()) {
                return $this->redirect('/admin/organization');
            } else {
                return $this->render('edit', ['model' => $model]);
            }
        } else {
            throw new NotFoundHttpException();
        }

    }

}
