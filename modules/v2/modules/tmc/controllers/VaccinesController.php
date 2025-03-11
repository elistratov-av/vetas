<?php

namespace app\modules\v2\modules\tmc\controllers;

use app\models\db\OwnerFeedback;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcVaccine;
use app\models\db\Violation;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\VaccinesModel;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class VaccinesController extends BaseController
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
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new VaccinesModel())->getVaccine($id)
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $vaccines = new VaccinesModel();

        return [
            'result' => $vaccines->list($page, $limit, $filter)
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

        $publicData = ['id', 'name', 'dealer'];
        $query = TmcVaccine::find()->select($publicData)
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        if (!empty($filter['name'])) {
            $query->where(['ilike', 'name', $filter['name']]);
        }

        return [
            'result' => $query->asArray()->all(),
        ];
    }

    /**
     * Создание вакцины
     *
     * @param string $name
     * @param string $registered
     * @param string $produced
     * @param null $dealer
     * @param null $form_description
     * @param null $unit
     * @param null $packaging
     * @param null $id_measure
     * @param array $category_ids
     * @param array $diseases_ids
     * @param array $species_ids
     * @param null $excipients
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate(
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $packaging = null,
        $id_measure = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = [],
        $excipients = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new VaccinesModel();

        $vaccine = $model
            ->create(
                $name,
                $registered,
                $produced,
                $dealer,
                $form_description,
                $unit,
                $id_measure,
                $packaging,
                $category_ids,
                $diseases_ids,
                $species_ids,
                $excipients
            );

        return [
            'result' => true,
            'id' => $vaccine->id,
        ];
    }

    public function actionAdd($up_to, $id)
    {
        $balance = Balance::findOne([
            'id' => $id
        ]);

        if (empty($balance)) {
            return [
                'result' => false,
            ];
        }

        if (!is_numeric($up_to)) {
            return [
                'result' => false,
            ];
        }

        if ($up_to < 0) {
            return [
                'result' => false,
            ];
        }

        $balance->count_in_production_form = $balance->count_in_production_form + $up_to;
        $balance->save(false);

        return [
            'result' => true,
        ];
    }

    /**
     * Редактирование вакцины
     *
     * @param int $id
     * @param string $name
     * @param $registered
     * @param $produced
     * @param null $dealer
     * @param null $form_description
     * @param null $unit
     * @param null $packaging
     * @param null $id_measure
     * @param array $category_ids
     * @param array $diseases_ids
     * @param array $species_ids
     * @param $excipients
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit(
        $id,
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $packaging = null,
        $id_measure = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = [],
        $excipients
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new VaccinesModel())
            ->edit(
                $id,
                $name,
                $registered,
                $produced,
                $dealer,
                $form_description,
                $unit,
                $id_measure,
                $packaging,
                $category_ids,
                $diseases_ids,
                $species_ids,
                $excipients
            );

        return [
            'result' => true,
        ];
    }

    /**
     * Удаление вакцины
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        (new VaccinesModel())->delete($id);

        return [
            'result' => true,
        ];
    }
}
