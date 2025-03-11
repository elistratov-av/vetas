<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.12.18
 * Time: 16:31
 */

namespace app\modules\adminv\controllers;

use app\models\db\ContactTypes;
use app\modules\admin\models\Contacts;
use app\modules\admin\models\forms\ContactForm;
use app\modules\admin\models\OwnerEditForm;
use app\modules\admin\models\Owners;
use app\modules\admin\models\OwnerSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class OwnersController
 * @package app\modules\adminv\controllers
 */
class OwnersController extends AdminController
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
                        'roles' => ['data.owners.manage'],
                        'actions' => ['index', 'profile'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['data.owners.manage.U'],
                        'actions' => ['edit', 'add-contact', 'edit-contact', 'remove-contact'],
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
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {
        if (!$owner = Owners::findOne(['id' => $id])) {
            throw new NotFoundHttpException('Владелец не найден');
        }
        $model = new OwnerEditForm($owner);

        if ($model->load(Yii::$app->request->post()) && $model->editOwner()) {
            return $this->redirect(['profile', 'id' => $id]);
        } else {
            return $this->render('edit', [
                'model' => $model,
                'owner' => $owner,
            ]);
        }
    }

    /**
     * @param int $owner
     * @return string|\yii\web\Response
     * @throws \Throwable
     */
    public function actionAddContact($owner)
    {
        $model = new ContactForm();
        $request = Yii::$app->request;

        if ($request->isPost && $model->load($request->post()) && $model->validate() && $model->save()) {
            return $this->redirect(['profile', 'id' => $owner]);
        } else {
            return $this->render('add-contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param int $id
     * @param int $owner
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionEditContact($id, $owner)
    {
        if (!$contact = Contacts::findOne(['id' => $id])) {
            throw new NotFoundHttpException();
        }

        $model = new ContactForm();
        $ownerModel = Owners::findOne(['id' => $owner]);

        $model->setAttributes($contact->getAttributes());
        $request = Yii::$app->request;

        if ($request->isPost && $model->load($request->post()) && $model->validate() && $model->edit($contact)) {
            return $this->redirect(['profile', 'id' => $owner]);
        } else {
            return $this->render('edit-contact', [
                'model' => $model,
                'contact' => $contact,
                'owner' => $ownerModel,
            ]);
        }
    }

    /**
     * @param int $id
     * @param int $owner
     * @return \yii\web\Response
     * @throws \Throwable
     */
    public function actionRemoveContact($id, $owner)
    {
        $contact = Contacts::findOne(['id' => $id]);
        $ownerModel = Owners::findOne(['id' => $owner]);
        if (($contact->regCertificatePhone) || ($contact->regCertificateMail)) {
            Yii::$app->session->addFlash('danger', 'Удаление невозможно, так как данный контакт указан в регистрационном удостоверении');
        } else {
            try {
                $contact->delete();
                Yii::$app->session->addFlash('info', 'Контакт успешно удален');
            } catch (\Exception $e) {
                Yii::$app->session->addFlash('danger', 'Не удалось удалить контакт, обратитесь к администратору');
            }
        }

        return $this->redirect(['profile', 'id' => $ownerModel->id]);
    }
}
