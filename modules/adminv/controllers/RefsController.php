<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\common\efsp\EfspWrapper;
use app\models\db\ActiveRecord;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\PetRefColor;
use app\models\db\PetRefSkill;
use app\models\db\PetRefEarType;
use app\models\db\PetRefBasisOfDisposal;
use app\models\db\PetRefSize;
use app\models\db\PetRefTailType;
use app\models\db\PetRefWoolType;
use app\models\db\ShelterGuests;
use app\modules\adminv\models\forms\OrganizationAddressForm;
use app\modules\adminv\models\forms\ShelterContactsForm;
use app\modules\adminv\models\forms\ShelterRepresentativeForm;
use app\modules\adminv\models\search\OrganizationSearch;
use app\modules\adminv\models\search\RefsSearch;
use app\modules\v2\common\rbac\AccessTrait;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class SheltersController
 * @package app\modules\adminv\controllers
 */
class RefsController extends AdminController
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

    protected function getClassname($id)
    {
        $classname = 'app\models\db\\' . Inflector::id2camel($id);

        $refs = [
            PetRefColor::class,
            PetRefSkill::class,
            PetRefEarType::class,
            PetRefTailType::class,
            PetRefWoolType::class,
            PetRefSize::class,
            PetRefBasisOfDisposal::class,
        ];

        if (!in_array($classname, $refs)) {
            throw new NotFoundHttpException('There is no such Ref');
        }

        return $classname;
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex($crud_id)
    {
        $classname = static::getClassname($crud_id);

        switch ($classname) {
            case PetRefColor::class:
                $params = [
                    'title' => 'Окрас'
                ];
                break;
            case PetRefEarType::class:
                $params = [
                    'title' => 'Тип ушей'
                ];
                break;
            case PetRefSkill::class:
                    $params = [
                        'title' => 'Навыки'
                    ];
                    break;
            case PetRefTailType::class:
                $params = [
                    'title' => 'Тип хвоста'
                ];
                break;
            case PetRefWoolType::class:
                $params = [
                    'title' => 'Шерсти'
                ];
                break;
            case PetRefSize::class:
                $params = [
                    'title' => 'Размер'
                ];
                break;
            case PetRefBasisOfDisposal::class:
                $params = [
                    'title' => 'Основание выбытия'
                ];
                break;
        }

        $searchModel = new RefsSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get(), $classname);

        return $this->render('index', array_merge([
            'crud_id' => $crud_id,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ], $params));
    }

    /**
     * @param int $crud_id
     * @return string|\yii\web\Response
     */
    public function actionCreate($crud_id)
    {
        $classname = static::getClassname($crud_id);

        switch ($classname) {
            case PetRefColor::class:
                $params = [
                    'title' => 'Добавить новый окрас'
                ];
                break;
            case PetRefSkill::class:
                $params = [
                    'title' => 'Добавить новый навык'
                ];
                break;
            case PetRefEarType::class:
                $params = [
                    'title' => 'Добавить новый тип ушей'
                ];
                break;
            case PetRefTailType::class:
                $params = [
                    'title' => 'Добавить новый тип хвоста'
                ];
                break;
            case PetRefWoolType::class:
                $params = [
                    'title' => 'Добавить новый тип шерсти'
                ];
                break;
            case PetRefSize::class:
                $params = [
                    'title' => 'Добавить новый размер'
                ];
                break;
            case PetRefBasisOfDisposal::class:
                $params = [
                    'title' => 'Добавить нового основания выбытия'
                ];
                break;
        }

        $model = new $classname();

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'crud_id' => $crud_id]);
        }

        return $this->render('create', array_merge([
            'model' => $model,
            'crud_id' => $crud_id,
        ], $params));
    }

    /**
     * @param int $id
     * @param int $crud_id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id, $crud_id)
    {
        $classname = static::getClassname($crud_id);

        switch ($classname) {
            case PetRefColor::class:
                $params = [
                    'title' => 'Редактировать окрас'
                ];
                break;
            case PetRefColor::class:
                    $params = [
                        'title' => 'Редактировать навык'
                    ];
                    break;
            case PetRefEarType::class:
                $params = [
                    'title' => 'Редактировать тип ушей'
                ];
                break;
            case PetRefTailType::class:
                $params = [
                    'title' => 'Редактировать тип хвоста'
                ];
                break;
            case PetRefWoolType::class:
                $params = [
                    'title' => 'Редактировать тип шерсти'
                ];
                break;
            case PetRefSize::class:
                $params = [
                    'title' => 'Редактировать размер'
                ];
                break;
            case PetRefBasisOfDisposal::class:
                $params = [
                    'title' => 'Редактировать основание выбытия'
                ];
                break;
        }

        /* @var $classname ActiveRecord */
        if(!($model = $classname::findOne($id))){
            throw new NotFoundHttpException();
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'crud_id' => $crud_id]);
        }

        return $this->render('edit', array_merge([
            'model' => $model,
            'crud_id' => $crud_id,
        ], $params));
    }

    /**
     * @param int $id
     * @param string $crud_id
     * @return \yii\web\Response
     */
    public function actionDelete($id, $crud_id)
    {
        $classname = static::getClassname($crud_id);

        /* @var $classname ActiveRecord */
        if($model = $classname::findOne($id)){
            try {
                $model->delete();
            } catch (\Throwable $e) {
                \Yii::$app->session->setFlash('error', 'Ошибка при удалении записи');
            }
        } else {
            throw new NotFoundHttpException();
        }

        return $this->redirect(['index', 'crud_id' => $crud_id]);
    }
}
