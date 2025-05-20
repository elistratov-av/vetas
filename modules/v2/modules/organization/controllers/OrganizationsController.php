<?php

namespace app\modules\v2\modules\organization\controllers;

use app\common\models\UserModel;
use app\common\validators\FullTrimValidator;
use app\components\Tree;
use app\models\db\Areas;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\OwnerFeedback;
use app\models\db\Violation;
use app\modules\v2\modules\BaseController;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\db\Query;
use yii\web\ServerErrorHttpException;

/**
 * Class OrganizationsController
 *
 * @package app\modules\v2\modules\organization\controllers
 */
class OrganizationsController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'http_authenticator' => [
                    'except' => [
                        'list-by-token',
                    ],
                ],
            ]
        );
    }

    /**
     * @param int $id_organization
     * @param array $filter
     * @param ?string $org_status
     *
     * @return array
     */
    public function actionTree(
        $id_organization = null,
        $filter = [],
        ?string $org_status = null)
    {
        $org_status = strtolower(trim($org_status));
        $orgStatusIsHidden = false;
        $orgStatusAll = false;
        if (strlen($org_status)) {
            if ($org_status == 'hidden') {
                $orgStatusIsHidden = true;
            } else if($org_status == 'visible') {
                $orgStatusIsHidden = false;
            } else if($org_status == 'all') {
                $orgStatusAll = true;
            }
        }

        /* @var $user UserModel */
        $user = \Yii::$app->user->getIdentity();

        if (!isset($id_organization)) {
            if ($user->specialist === null || empty($user->specialist->id_organization) || $user->specialist->organization === null) {
                throw new BadRequestHttpException('Не передан id организации');
            }
            $organization = $user->specialist->organization;
        } else {
            $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
            $organization = Organizations::findOne(['id' => $id_organization]);
        }

        if ($organization === null) {
            throw new BadRequestHttpException('Не найдена организация для переданного id');
        }

        $organizations = [];
        $query = $organization->prepareTreeQuery(true, false, $orgStatusIsHidden, $orgStatusAll);
        if (!empty($filter)) {
            $this->prepareFilter($filter, $query);
        }
        $models = $query->all();
        foreach ($models as $model) {
            $organization = $model->toArray();
            $organization['full_adress'] = ($model->fias_addresses === null) ? null : $model->fias_addresses->full_address;
            $organizations[] = $organization;
        }

        return [
            'result' => [
                'organizations' => $organizations,
            ],
        ];
    }

    public function actionManagingOrganizationsList()
    {
        $ids = array_unique(
            array_merge(
                Organizations::find()->select('managing_organization_id')->distinct()->where(['not', ['managing_organization_id' => null]])->column(),
                Organizations::find()->select('id')->where(['is_managing_organization' => true])->column()
            )
        );

        $items = [];
        foreach (Organizations::find()->with('fias_addresses')->all() as $model) {
            $items[] = (object) [
                'id' => $model->id,
                'parent_id' => $model->managing_organization_id,
                'model' => $model,
            ];
        }
        $pointers = Tree::getPointers($items);

        $result = [];
        foreach ($ids as $id) {
            $model = $pointers[$id]['model'];

            $shelters = [];
            foreach (Tree::getNestedChildren($pointers[$id]['children']) as $child_id => $child) {
                if ($child['model']->organization_type_const === OrgTypes::SYSTEM_TYPE_SHELTER) {
                    $shelters[$child_id] = [
                        'id' => $child_id,
                        'name' => $child['model']->short_name,
                    ];
                }
            }
            $manageName = '';
            $manageShortName = '';
            $manageId = $model->managing_organization_id;
            if ($manageId > 0) {

                $manageNameList = Organizations::find()->select('name')->where(['id' => $manageId])->all();
                foreach ($manageNameList as $ml) {
                    $manageName = $ml['name'];
                    $manageShortName = $ml['short_name'];
                    break;
                }
            } else {
                $manageId = $id;
                $manageName = $model->name;
                $manageShortName = $model->short_name;
            }

            $result[$id] = [
                'id' => $id,
                'name' => $model->short_name,
                'address' => $model->fias_addresses->full_address ?? '',
                'parent_id' => $model->parent_id,
                'parent_name' => $pointers[$id]['model']->name,
                'shelters' => $shelters,
                'managing_organization_id' => $manageId,
                'managing_organization_name' => $manageName,
                'managing_organization_short_name' => $manageShortName
            ];
        }

        return ['result' => $result];
    }

    public function actionUserSheltersList()
    {
        /* @var $user UserModel */
        $user = \Yii::$app->user->getIdentity();

        if ($user->specialist === null || empty($user->specialist->id_organization) || $user->specialist->organization === null) {
            throw new ForbiddenHttpException('Forbidden');
        }

        $items = [];
        foreach (Organizations::find()->with('fias_addresses')->all() as $model) {
            $items[] = (object) [
                'id' => $model->id,
                'parent_id' => $model->managing_organization_id,
                'model' => $model,
            ];
        }
        $pointers = Tree::getPointers($items);

        if ($organization = $pointers[$user->specialist->id_organization]) {
            $shelters = [];

            if ($organization['model']->organization_type_const === OrgTypes::SYSTEM_TYPE_SHELTER) {
                $shelters[] = $organization['model']->getAttributes();
            } else {
                foreach (Tree::getNestedChildren($organization['children']) as $child_id => $child) {
                    if ($child['model']->organization_type_const === OrgTypes::SYSTEM_TYPE_SHELTER) {
                        $shelters[] = $child['model']->getAttributes();
                    }
                }
            }

            return ['result' => ['organizations' => $shelters]];
        }

        throw new NotFoundHttpException('Приюты для данного пользователя не найдены.');
    }

    public function actionSheltersList()
    {
        $organizations = Organizations::find()->with(['fias_addresses', 'area', 'contacts'])->indexBy('id')->all();

        $result = [];
        foreach ($organizations as $id => $model) {
            if ($model->organization_type_const === OrgTypes::SYSTEM_TYPE_SHELTER) {
                if ($area = Areas::findOne(['id' => $model->id_area])) {
                    $area_label = $area->name;
                }
                //'area' => $model->area->short_name ?? '',

                $result[$id] = [
                    'id' => $id,
                    'name' => $model->short_name,
                    'address' => $model->fias_addresses->full_address ?? '',
                    'contacts' => $model->contacts ?? [],
                    'description' => $model->comment,
                    'area' => $area_label ?? '',
                    'managing_organization_id' => $model->managing_organization_id,
                    'managing_organization_name' => $organizations[$model->managing_organization_id]->name ?? '',
                ];
            }
        }

        return ['result' => $result];
    }

    /**
     * @param string $feedbackFormToken
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionListByToken(string $feedbackFormToken, int $page = 1, int $limit = 10, array $filter = [])
    {
        /** @var Violation $violation */
        if (!$violation = Violation::find()->where(['feedback_token' => $feedbackFormToken])->one()) {
            throw new BadRequestHttpException('Не найдено нарушения с переданным токеном обратной связи');
        }
        /** @var OwnerFeedback $feedback */
        if (OwnerFeedback::find()->where(['id_violation' => $violation->id_violation, 'is_processed' => false])->one()) {
            throw new BadRequestHttpException('Форма обратной связи по токену уже заполнена и находится в обработке');
        }

        $publicData = ['id', 'name', 'short_name'];
        $query = Organizations::find()->select($publicData)
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        if (!empty($filter['name'])) {
            $query->where([
                'OR',
                ['ilike', 'name', $filter['name']],
                ['ilike', 'short_name', $filter['name']]
            ]);
        }

        return [
            'result' => $query->asArray()->all()
        ];
    }

    /**
     * @param array $filter
     * @param \yii\db\ActiveQuery $query
     */
    private function prepareFilter($filter, \yii\db\ActiveQuery &$query): void
    {
        $fullTrimValidator = new FullTrimValidator();
        if (!empty($filter['name'])) {
            $name = $fullTrimValidator->validateValue($filter['name']);
            $query->andWhere([
                'or',
                ['ilike', 'organizations.name', $name],
                ['ilike', 'organizations.short_name', $name],
            ]);
        }
        if (!empty($filter['parent_id'])) {
            $query->andWhere(['organizations.parent_id' => $filter['parent_id']]);
        }
        foreach (['inn', 'kpp', 'ogrn'] as $attribute) {
            if (!empty($filter[$attribute])) {
                $query->andWhere(['ilike', 'organizations.' . $attribute, $filter[$attribute]]);
            }
        }
    }

    public function actionCheckNotificationsForOrganization($data)
    {
        try {
            $now = date('Y-m-d H:i:s');
            $query = (new Query())
                ->select('informings.*')
                ->from('informings')
                ->leftJoin('informings_organizations', 'informings.id = informings_organizations.id_informing')
                ->leftJoin('organizations', 'informings_organizations.id_organization=organizations.id')
                ->where("informings.date @> TIMESTAMP '{$now}'")
                ->andWhere(['organizations.id' => $data['id_organisation']]);
            $rows = $query->all();

            return [
                'response' => $rows,
            ];

        } catch (\Throwable $e) {

            throw new ServerErrorHttpException('Ошибка получения сообщенияй для организации');
        }
    }

    /**
     * @param int $id_organization
     * @return array
     */
    public function actionCabinets(int $id_organization): array
    {
        $query = (new Query())
            ->select('cabinet_types.id, cabinet_types.name, cabinet_types.description')
            ->from('cabinet_types')
            ->leftJoin('organization_cabinets', 'cabinet_types.id = organization_cabinets.id_cabinet_type')
            ->where('organization_cabinets.id_organization = :id_organization')
            ->andWhere('organization_cabinets.cabinet_count > 0')
            ->addParams(
                [':id_organization' => $id_organization]
            );

        $rows = $query->all();

        return [
            'response' => $rows,
        ];
    }
}
