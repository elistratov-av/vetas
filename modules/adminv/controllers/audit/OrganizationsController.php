<?php

namespace app\modules\adminv\controllers\audit;

use app\common\components\rbac\Role;
use app\models\db\Organizations;
use app\modules\adminv\controllers\AdminController;
use app\modules\adminv\models\search\BalanceFlowSearch;
use app\modules\adminv\models\search\OrganizationSearch;
use app\modules\v2\common\rbac\AccessTrait;
use Yii;
use yii\filters\AccessControl;
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
     * Список организаций
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new OrganizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }

    /**
     * История баланса организации
     *
     * @param $id_organization
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionBalanceFlow($id_organization)
    {
        $searchModel = new BalanceFlowSearch();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams, $id_organization
        );

        $organization = Organizations::findOne(['id' => $id_organization]);

        if (empty($organization)){
            throw new NotFoundHttpException('Указанная организация не найдена');
        }

        return $this->render('balance-flow', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'organization' => $organization
        ]);
    }
}
