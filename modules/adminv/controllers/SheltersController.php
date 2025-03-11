<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\common\efsp\EfspWrapper;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\ShelterGuests;
use app\modules\adminv\models\forms\OrganizationAddressForm;
use app\modules\adminv\models\forms\ShelterContactsForm;
use app\modules\adminv\models\forms\ShelterRepresentativeForm;
use app\modules\adminv\models\search\OrganizationSearch;
use app\modules\v2\common\rbac\AccessTrait;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class SheltersController
 * @package app\modules\adminv\controllers
 */
class SheltersController extends AdminController
{
    use AccessTrait;

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
    public function actionIndex()
    {
        $searchModel = new OrganizationSearch();
        $params = \Yii::$app->request->get();
        if (!isset($params[$searchModel->formName()])) {
            $params[$searchModel->formName()] = [];
        }
        $params[$searchModel->formName()]['id_org_type'] = $this->findShelterOrgTypeId();
        $dataProvider = $searchModel->search($params, true);

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreate()
    {
        $model = new Organizations([
            'id_org_type' => $this->findShelterOrgTypeId(),
        ]);
        $contactForm = new ShelterContactsForm();
        $addressForm = new OrganizationAddressForm();
        $representativeForm = new ShelterRepresentativeForm();

        $addressResult = $addressForm->load(\Yii::$app->request->post()) ? $addressForm->validate() : true;
        $addressForm->saveFiasAddress();

        if ($model->load(\Yii::$app->request->post())
            && $representativeForm->load(\Yii::$app->request->post())
            && $addressResult
            && ($addressForm->hasErrors() === false)
            && $model->load($addressForm->toArray(), '')
            && $representativeForm->save($model)
            && $model->save()) {
            if ($contactForm->load(\Yii::$app->request->post())) {
                if (!$contactForm->save($model->id)) {
                    \Yii::$app->session->setFlash('error', 'Ошибка при сохранении контактов приюта');
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            }
            return $this->redirect(['index']);
        }

        $organizationsOptions = $this->organizationsOptions();

        return $this->render('create', compact('model', 'contactForm', 'addressForm', 'representativeForm', 'organizationsOptions'));
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }
        $contactForm = (new ShelterContactsForm())->prepare($id);
        $addressForm = new OrganizationAddressForm();
        $addressForm->load($model->toArray(), '');
        $addressForm->loadFiasAddress();
        $representativeForm = (new ShelterRepresentativeForm())->prepare($id);

        $addressResult = $addressForm->load(\Yii::$app->request->post()) ? $addressForm->validate() : true;

        if ($addressForm->load(\Yii::$app->request->post())) {
            $addressForm->saveFiasAddress();
        }

        if ($model->load(\Yii::$app->request->post())
            && $representativeForm->load(\Yii::$app->request->post())
            && $addressResult
            && ($addressForm->hasErrors() === false)
            && $model->load($addressForm->toArray(), '')
            && $representativeForm->save($model)
            && $model->save()) {
            if ($contactForm->load(\Yii::$app->request->post())) {
                if (!$contactForm->save($model->id)) {
                    \Yii::$app->session->setFlash('error', 'Ошибка при сохранении контактов приюта');
                } else {
                    return $this->redirect(['index']);
                }
            } else {
                return $this->redirect(['index']);
            }
        }

        $organizationsOptions = $this->organizationsOptions();

        return $this->render('update', compact('model', 'contactForm', 'addressForm', 'representativeForm', 'organizationsOptions'));
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }

        $records = ShelterGuests::find()
            ->where(['id_organization' => $model->id])
            ->all();

        if (!empty($records)) {
            \Yii::$app->session->setFlash('error', 'Организация не может быть удалена - есть записи о пребывании животных в приюте');

            return $this->redirect(['index']);
        }

        try {
            $model->delete();
        } catch (\Throwable $e) {
            \Yii::$app->session->setFlash('error', 'Ошибка при удалении организации');
        }

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return \app\models\db\Organizations|null
     */
    private function findModel($id)
    {
        return Organizations::findOne(['id' => $id]);
    }

    /**
     * @return array
     * @throws \Throwable
     */
    private function organizationsOptions()
    {
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();

        $organizations = \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
            ? Organizations::find()->all()
            : $user->specialist->getAllOrganizations();

        $options = ArrayHelper::map($organizations, 'id', 'short_name');

        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            $options = [0 => '- организация будет головной -'] + $options;
        }

        return $options;
    }

    /**
     * @return int
     */
    private function findShelterOrgTypeId(): int
    {
        return OrgTypes::findOne(['is_tech' => true])->id;
    }

    /**
     * @param $bound
     * @param $query
     * @param null $locationType
     * @param null $locationFias
     * @return false|string
     */
    public function actionEfspList($bound, $query, $locationType = null, $locationFias = null)
    {
        return json_encode((new EfspWrapper())->searchAllBound($bound, $query, $locationType, $locationFias));
    }

    /**
     * @param $houseFias
     * @param $query
     * @return false|string
     */
    public function actionEfspRooms($houseFias, $query)
    {
        return json_encode((new EfspWrapper())->searchRoom($houseFias, $query));
    }
}
