<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.12.18
 * Time: 16:31
 */

namespace app\modules\admin\controllers;


use app\models\db\ContactTypes;
use app\modules\admin\models\Contacts;
use app\modules\admin\models\forms\ContactForm;
use app\modules\admin\models\OwnerEditForm;
use app\modules\admin\models\Owners;
use app\modules\admin\models\OwnerSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Class OwnersController
 * @package app\modules\admin\controllers
 */
class OwnersController extends AdminController
{

    /**
     * @return string
     */
    public function actionIndex()
    {

        $searchModel = new OwnerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfile($id)
    {
        if (!$model = Owners::find()->where(['id' => $id])->one()) {
            throw new NotFoundHttpException('Владелец не найден');
        }

        $query = Contacts::find()->andWhere([
            'entity_id' => $id,
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER]);
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $contactTypes = ContactTypes::typeOptions(ContactTypes::ENTITY_TYPE_PET_OWNER);

        return $this->render('profile', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'contactTypes' => $contactTypes,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit()
    {
        if (!$owner = Owners::findOne(Yii::$app->request->get('id'))) {
            throw new NotFoundHttpException('Владелец не найден');
        }
        $model = new OwnerEditForm($owner);

        if ($model->load(Yii::$app->request->post()) && $model->editOwner()) {
            return $this->redirect('/admin/owners');
        } else {
            return $this->render('edit', [
                'model' => $model,
                'owner' => $owner,
            ]);
        }
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Throwable
     */
    public function actionAddContact()
    {
        $model = new ContactForm();
        $request = Yii::$app->request;

        if ($request->isPost && $model->load($request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['/admin/owners/profile', 'id' => Yii::$app->request->get()['owner']]);
        } else {
            return $this->render('add-contact', [
                'model' => $model,
            ]);
        }
    }


    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionEditContact()
    {
        if (!$contact = Contacts::findOne(Yii::$app->request->get('id'))) {
            throw new NotFoundHttpException();
        }

        $model = new ContactForm();
        $owner = Owners::findOne(Yii::$app->request->get()['owner']);


        $model->setAttributes($contact->getAttributes());
        $request = Yii::$app->request;

        if ($request->isPost && $model->load($request->post()) && $model->validate() && $model->edit($contact)) {
            return $this->redirect(['/admin/owners/profile', 'id' => Yii::$app->request->get()['owner']]);
        } else {
            return $this->render('edit-contact', [
                'model' => $model,
                'contact' => $contact,
                'owner' => $owner,
            ]);
        }
    }

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionRemoveContact()
    {
        $contact = Contacts::findOne(Yii::$app->request->get('id'));
        $owner = Owners::findOne(Yii::$app->request->get('owner'));
        if (($contact->regCertificatePhone) || ($contact->regCertificateMail)) {
            Yii::$app->session->addFlash('danger', 'Удаление невозможно, так как данный контакт указан в регистрационном удостоверении');
            return $this->redirect(['/admin/owners/profile', 'id' => $owner->id]);
        } else {
            try {
                $contact->delete();
                Yii::$app->session->addFlash('info', 'Контакт успешно удален');
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('danger', 'Не удалось удалить контакт, обратитесь к администратору');
            }
            return $this->redirect(['/admin/owners/profile', 'id' => $owner->id]);
        }
    }
}
