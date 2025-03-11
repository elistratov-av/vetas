<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.12.18
 * Time: 15:47
 */

namespace app\modules\admin\controllers;


use app\modules\admin\models\ChipoldSearch;
use app\modules\admin\models\ChipsSearch;
use app\modules\admin\models\forms\ChipoldEditForm;
use app\modules\admin\models\forms\ChipsEditForm;
use app\modules\admin\models\PetIdentification;
use app\modules\admin\models\Pets;
use app\modules\admin\models\PetSearch;
use app\modules\admin\models\PetsOldIdentification;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class PetsController
 * @package app\modules\admin\controllers
 */
class PetsController extends AdminController
{

    /**
     * @return string
     */
    public function actionIndex()
    {

        $searchModel = new PetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }


    /**
     * @return string
     */
    public function actionChipold()
    {
        $searchModel = new ChipoldSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('chipold',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }


    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionChips()
    {
        $searchModel = new ChipsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('chips',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }


    /**
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionChipoldRemove()
    {
        $chipold = PetsOldIdentification::findOne(Yii::$app->request->get('id'));
        try {
            $chipold->delete();
            Yii::$app->session->addFlash('info', 'Запись успешно удалена');
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('danger', 'Не удалось удалить запись, обратитесь к администратору');
        }
        return $this->redirect('/admin/pets/chipold');
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionChipoldEdit()
    {
        if (!$chipold = PetsOldIdentification::findOne(Yii::$app->request->get('id'))) {
            throw new NotFoundHttpException();
        }

        $model = new ChipoldEditForm();
        $model->setAttributes($chipold->getAttributes());
        $request = Yii::$app->request;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($request->isPost && $model->load($request->post()) && $model->edit($chipold)) {
            return $this->redirect('/admin/pets/chipold');
        } else {
            return $this->render('edit-old', [
                'model' => $model,
                'chipold' => $chipold
            ]);
        }
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfile($id)
    {
        if (!$model = Pets::find()->where(['id' => $id])->one()) {
            throw new NotFoundHttpException('Питомец не найден');
        }



        $query = PetsOldIdentification::find()->andWhere([
            'id_pet' => $id,
        ]);

        if (!$query) {
            $query = PetIdentification::find()->andWhere([
                'id_pet' => $id,
            ]);
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        return $this->render('profile', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionChipRemove()
    {
        $chip = PetIdentification::findOne(Yii::$app->request->get('id'));
        try {
            $chip->delete();
            Yii::$app->session->addFlash('info', 'Запись успешно удалена');
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('danger', 'Не удалось удалить запись, обратитесь к администратору');
        }
        return $this->redirect('/admin/pets/chips');
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionChipEdit()
    {
        if (!$chip = PetIdentification::findOne(Yii::$app->request->get('id'))) {
            throw new NotFoundHttpException();
        }

        $model = new ChipsEditForm();
        $model->setAttributes($chip->getAttributes());
        $request = Yii::$app->request;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($request->isPost && $model->load($request->post()) && $model->edit($chip)) {
            return $this->redirect('/admin/pets/chips');
        } else {
            return $this->render('chip-edit', [
                'model' => $model,
                'chip' => $chip
            ]);
        }
    }

//    public function actionValidateForm()
//    {
//        if (Yii::$app->request->isAjax) {
//            Yii::$app->response->format = Response::FORMAT_JSON;
//
//            $model = new ChipsEditForm();
//            if($model->load(Yii::$app->request->post()))
//                return ActiveForm::validate($model);
//        }
//        throw new \yii\web\BadRequestHttpException('Bad request!');
//    }

//    public function actionAjaxValidation()
//    {
//        $post = Yii::$app->request->post();
//        $model = new ChipsEditForm();
//
//        if (!$model->load($post)) {
//            throw new HttpException(403, 'Cannot load model');
//        }
//
//        $array = ActiveForm::validate($model);
//
////        var_dump(json_encode($array)); die();
//        return json_encode($array);
//    }
}