<?php

namespace app\modules\v2\modules\organization\controllers;

use app\common\validators\FullTrimValidator;
use app\models\db\Organizations;
use app\models\db\OwnerFeedback;
use app\models\db\Violation;
use app\modules\v2\modules\BaseController;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

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
     * @param int   $id_organization
     * @param array $filter
     *
     * @return array
     */
    public function actionTree($id_organization = null, $filter = [])
    {
        /* @var $user \app\common\models\UserModel */
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
        $query = $organization->prepareTreeQuery(true, true);
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
     * @param array               $filter
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
}
