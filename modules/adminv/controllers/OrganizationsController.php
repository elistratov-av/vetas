<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\common\efsp\EfspWrapper;
use app\models\db\odopm\OdopmOrganizationsActions;
use app\models\db\Organizations;
use app\modules\adminv\models\forms\OrganizationDeleteReasonForm;
use app\modules\adminv\models\search\OrganizationSearch;
use app\modules\v2\common\rbac\AccessTrait;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class OrganizationsController
 * @package app\modules\adminv\controllers
 */
class OrganizationsController extends AdminController
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
                        'roles' => [Role::ROLE_SYSADMIN_GOS, Role::ROLE_MANAGEMENT_GOS],
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
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Organizations();

        if ($model->load(\Yii::$app->request->post())) {
            $parent = empty($model->parent_id) ? null : Organizations::findOne(['id' => $model->parent_id]);
            if (!\Yii::$app->user->can('data.organizations.manage.W', ['model' => $parent])) {
                throw new ForbiddenHttpException('Недостаточно прав доступа');
            }
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        $organizationsOptions = $this->organizationsOptions();
        $districts = $this->getMoscowDistrictCodes();

        return $this->render('create', compact('model', 'organizationsOptions', 'districts'));
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }

        if (!\Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])) {
            throw new ForbiddenHttpException('Недостаточно прав доступа');
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        $organizationsOptions = $this->organizationsOptions();
        if (!empty($model->parent_id) && !array_key_exists($model->parent_id, $organizationsOptions)) {
            // фикс при редактировании организации администратором, если она является дочерней к вышестоящей
            $parent = Organizations::findOne(['id' => $model->parent_id]);
            if ($parent !== null) {
                $organizationsOptions[$model->parent_id] = $parent->short_name;
            }
        }

        $districts = $this->getMoscowDistrictCodes();

        return $this->render('update', compact('model', 'organizationsOptions', 'districts'));
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

        if (!\Yii::$app->user->can('data.organizations.manage.W', ['model' => $model])) {
            throw new ForbiddenHttpException('Недостаточно прав доступа');
        }

        $reasonModel = new OrganizationDeleteReasonForm();
        if (!$reasonModel->load(\Yii::$app->request->post()) || !$reasonModel->validate()) {
            \Yii::$app->session->setFlash(
                'error',
                'Ошибка при удалении организации' . (empty($reasonModel->errors) ? '' : (': ' . implode(', ', $reasonModel->getErrorSummary(true))))
            );
        }

        try {
            $result = $model->delete();
        } catch (\Throwable $e) {
            $result = false;
        }

        if ($result === false) {
            \Yii::$app->session->setFlash('error', 'Ошибка при удалении организации');
        } else {
            $this->saveOdopmAction($model, $reasonModel);
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
     * @param \app\models\db\Organizations                                  $organizationModel
     * @param \app\modules\adminv\models\forms\OrganizationDeleteReasonForm $reasonModel
     */
    private function saveOdopmAction(Organizations $organizationModel, OrganizationDeleteReasonForm $reasonModel)
    {
        $model = new OdopmOrganizationsActions([
            'id_organization' => $organizationModel->id,
            'action' => OdopmOrganizationsActions::ACTION_DELETE,
            'entry_deleted_reason' => $reasonModel->reason,
        ]);

        $model->save();
    }

    /**
     * @return array|false
     */
    private function getMoscowDistrictCodes()
    {
        /**
        return [
        "0400" => "Восточный Административный округ",
        "0800" => "Западный Административный округ",
        "1000" => "Зеленоградский Административный округ",
        "0200" => "Северный Административный округ",
        "0300" => "Северо-Восточный Административный округ",
        "0900" => "Северо-Западный Административный округ",
        "0100" => "Центральный Административный округ",
        "0500" => "Юго-Восточный Административный округ",
        "0700" => "Юго-Западный Административный округ",
        "0600" => "Южный Административный округ",
        "1100" => "Новомосковский Административный округ",
        "1200" => "Троицкий Административный округ"
        ];
         */
        $result = false;
        try {
            $result =  (new EfspWrapper())->getMoscowAdmDistrictsArray();
        } catch (\Throwable $e) {

        }

        return $result;
    }
}
