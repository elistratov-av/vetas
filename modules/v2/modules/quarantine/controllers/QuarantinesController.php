<?php

namespace app\modules\v2\modules\quarantine\controllers;

use app\common\components\inform\events\QuarantineEvent;
use app\common\components\inform\jobs\BatchSendEventJob;
use app\models\db\QuarantineFocus;
use app\models\db\QuarantineLocality;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\quarantine\models\QuarantineModel;
use app\modules\v2\modules\quarantine\models\QuarantineSearchModel;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class QuarantinesController
 * @package app\modules\v2\modules\quarantine\controllers
 */
class QuarantinesController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException*
     */
    public function actionGet(int $id)
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        return [
            'result' => $model->prepareOutput(),
        ];
    }

    /**
     * @param int    $id_disease
     * @param string $threatened_area
     * @param string $start_date
     * @param string $end_date
     * @param string $comments
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException*
     */
    public function actionAdd(
        int $id_disease,
        string $threatened_area,
        string $start_date,
        string $end_date = null,
        string $comments = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new QuarantineModel();
        $model->load($this->actionParams, '');

        return $model->create()
            ? [
                'result' => $model->prepareOutput(),
            ]
            : $this->errorResponse($model);
    }

    /**
     * @param int    $id
     * @param int    $id_disease
     * @param string $threatened_area
     * @param string $start_date
     * @param string $end_date
     * @param string $comments
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(
        int $id,
        int $id_disease,
        string $threatened_area,
        string $start_date,
        string $end_date = null,
        string $comments = null
    )
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        $model->load($this->actionParams, '');

        return $model->update()
            ? [
                'result' => $model->prepareOutput(),
            ]
            : $this->errorResponse($model);
    }

    /**
     * @param int   $id_quarantine
     * @param array $focuses
     * @param array $locality
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAddTerritory(
        int $id_quarantine,
        array $focuses,
        array $locality = null
    )
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id_quarantine);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        $model->setScenario(QuarantineModel::SCENARIO_ADD_TERRITORY);
        $model->load($this->actionParams, '');

        return $model->createTerritory()
            ? [
                'result' => $model->prepareOutput(),
            ]
            : $this->errorResponse($model);
    }

    /**
     * @param int   $id_quarantine
     * @param array $focuses
     * @param array $locality
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEditTerritory(
        int $id_quarantine,
        array $focuses,
        array $locality = null
    )
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id_quarantine);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        $model->setScenario(QuarantineModel::SCENARIO_EDIT_TERRITORY);
        $model->load($this->actionParams, '');

        return $model->updateTerritory()
            ? [
                'result' => $model->prepareOutput(),
            ]
            : $this->errorResponse($model);
    }

    /**
     * @param int       $id_quarantine
     * @param int|array $id_focus
     * @param int       $id_locality
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRemoveTerritory(int $id_quarantine, $id_focus = null, int $id_locality = null)
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id_quarantine);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        if (!empty($quarantine->fact_end_date)) {
            $this->errorResponse($model, 'Редактирование завершенного карантина не допускается');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        if (empty($id_focus) && empty($id_locality)) {
            $this->errorResponse($model, 'Необходимо указать либо id эпизоотического очага, либо id неблагополучного пункта');
        }
        if (!empty($id_focus)) {
            QuarantineFocus::deleteAll(['id' => $id_focus, 'id_quarantine' => $id_quarantine]);
        }
        if (!empty($id_locality)) {
            QuarantineLocality::deleteAll(['id' => $id_locality, 'id_quarantine' => $id_quarantine]);
        }

        return [
            'result' => $model->prepareOutput(),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionFinish(int $id)
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        $body = json_decode(\Yii::$app->getRequest()->getRawBody(), true);

        $endDate = '';
        if (!empty($body['end_date'])) {
            $endDate = $body['end_date'];
        }

        return $model->finish($endDate)
            ? [
                'result' => $model->prepareOutput(),
            ]
            : $this->errorResponse($model);
    }

    /**
     * @param int       $page
     * @param int|false $limit
     * @param array     $filter
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(
        int $page = 1,
        $limit = 10,
        array $filter = []
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $searchModel = new QuarantineSearchModel();

        return $searchModel->list($page, $limit, $filter);
    }

    /**
     * @param int   $id_quarantine
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAnimals(
        int $id_quarantine,
        int $page = 1,
        int $limit = 10,
        array $filter = []
    )
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id_quarantine);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        $searchModel = new QuarantineSearchModel();

        return $searchModel->listAnimals($quarantine, $page, $limit, $filter);
    }

    /**
     * @param string $point
     * @return array
     */
    public function actionSuggestAnimals($point)
    {
        $searchModel = new QuarantineSearchModel();

        return $searchModel->suggestAnimals($point);
    }

    /**
     * @param int[] $ids_pet
     * @param int $id_owner
     * @param int $id_organization
     * @return array|void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCheck(array $ids_pet, int $id_owner, int $id_organization)
    {
        $searchModel = new QuarantineSearchModel();
        $result = $searchModel->check($ids_pet, $id_owner, $id_organization);

        return ($result !== false)
            ? [
                'result' => $result,
            ]
            : $this->errorResponse($searchModel);
    }

    /**
     * @param int $id_quarantine
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionNotify(int $id_quarantine)
    {
        $model = new QuarantineModel();
        $quarantine = $model->find($id_quarantine);
        if ($quarantine === null) {
            throw new NotFoundHttpException('Карантин с указанным id не найден');
        }

        $this->checkAccess($this->action->getUniqueId(), $quarantine, $this->actionParams);

        $searchModel = new QuarantineSearchModel();
        $query = $searchModel->prepareOwnersQuery($quarantine);

        if ($query === false) {
            return [
                'result' => [
                    'success' => false,
                ],
            ];
        }

        /** @var \yii\queue\db\Queue $queue */
        $queue = \Yii::$app->subscription_queue;

        $job = new BatchSendEventJob([
            'eventClass' => QuarantineEvent::class,
            'data' => [
                'area' => $quarantine->threatened_area,
                'startDate' => date_create_from_format('Y-m-d', $quarantine->start_date)->format('d.m.Y'),
            ],
            'to' => $query,
            'operator' => 'INNER JOIN',
        ]);

        $result = $queue->push($job);

        return [
            'result' => [
                'success' => ($result !== null),
            ],
        ];
    }
}
