<?php

namespace app\modules\v2\modules\visit\controllers;

use app\common\components\rbac\Role;
use app\models\db\BrigadesSpecialists;
use app\models\db\Visits;
use app\models\db\VisitsSpecialists;
use app\modules\v2\modules\specialist\models\AmbulanceDatelistModel;
use app\modules\v2\modules\specialist\models\AmbulanceTimelistModel;
use app\modules\v2\modules\visit\models\AmbulanceModel;
use app\modules\v2\modules\visit\models\VisitSaveModel;
use app\modules\v2\modules\visit\skeletons\visit\Lists;
use yii\web\BadRequestHttpException;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class AmbulanceController
 * @package app\modules\v2\modules\visit\controllers
 *
 * @method actionGet(int $id_visit): array
 * @method actionReferrals(int $id_visit): array
 * @method actionStart(int $id): array
 * @method actionFinish(int $id): array
 * @method actionFinishUnpayed(int $id): array
 * @method actionCancel(int $id, string $cancel_initiator, string $change_reason = null): array
 * @method actionConfirmPayment(int $id, int $id_discount = null, $applied_discounts_balance_tmc = null, $applied_discounts_services = null): array
 */
class AmbulanceController extends GenericVisitController
{
    /**
     * @param int $id_organization
     * @param string $date_from
     * @param array|int|null $id_shift_type
     * @param int $days_count
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return Lists
     */
    public function actionList(
        $id_organization = null,
        string $date_from = null,
        $id_shift_type = null,
        int $days_count = 1,
        int $page = 1,
        int $limit = 10,
        array $filter = []): Lists
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new AmbulanceModel();
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        $id_organization = \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) ? null : $user->specialist->id_organization;

        return $model->list($id_organization, $date_from, $days_count, $page, $limit, $filter);
    }

    public function actionGetAmbulance(
        int $id_visit = null
    )
    {
        $model = new AmbulanceModel();

        return $model->getAmbulance($id_visit);
    }

    /**
     * @param int $id_owner
     * @param int|array $id_pet
     * @param int|null $id_organization
     * @param int|null $channel
     * @param string|null $start_dttm
     * @param int|null $id_specialist
     * @param int|null $source
     * @param int|null $author
     * @param array|null $services
     * @param bool $is_veteran
     * @param bool $is_disabled
     * @param bool $is_blind
     * @param bool $is_orphan
     * @param bool $is_large_family
     * @param bool $is_veteran_of_labour
     * @param string|null $preferences_document
     * @param string|null $visit_to_address
     * @param string|null $description
     * @return array
     */
    public function actionCreate(
        int    $id_owner,
               $id_pet,
        int    $id_organization = null,
        int    $channel = null,
        string $start_dttm,
        int    $id_specialist,
        int    $source = null,
        int    $author = null,
        array  $services = null,
        bool   $is_veteran = null,
        bool   $is_disabled = null,
        bool   $is_blind = null,
        bool   $is_orphan,
        bool   $is_large_family,
        bool   $is_veteran_of_labour,
        string $preferences_document = null,
               $visit_to_address = null,
               $description = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_CREATE_VISIT,
            'type' => Visits::TYPE_AMBULANCE,
            'variety' => Visits::VISIT_SINGLE,
        ]);

        $data = $this->actionParams;
        foreach (['id_organization', 'channel', 'source', 'author'] as $attribute) {
            unset($data[$attribute]);
        }
        $model->load($data, '');

        if (!$model->createVisit()) {
            $this->errorResponse($model);
        }

        return [
            'result' => $this->findVisit($model->visit->id, true),
        ];
    }

    /**
     * @param int $id
     * @param int $id_owner
     * @param int|array $id_pet
     * @param int|null $id_organization
     * @param string|null $start_dttm
     * @param int|null $id_specialist
     * @param array|null $services
     * @param string|null $visit_to_address
     * @param string|null $description
     * @return array
     */
    public function actionEdit(
        int    $id,
        int    $id_owner,
               $id_pet,
        int    $id_organization = null,
        string $start_dttm = null,
        int    $id_specialist = null,
        array  $services = null,
               $visit_to_address = null,
               $description = null
    )
    {
        $visit = $this->findVisit($id);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        $model = new VisitSaveModel([
            'scenario' => VisitSaveModel::SCENARIO_UPDATE_VISIT,
            'visit' => $visit,
            'type' => Visits::TYPE_AMBULANCE,
        ]);

        $data = $this->actionParams;
        unset($data['id_organization']);
        $model->load($data, '');

        if (!$model->updateVisit()) {
            $this->errorResponse($model);
        }

        return [
            'result' => $this->findVisit($id, true),
        ];
    }

    /**
     * Метод для страниц 'Расписание приема'
     *
     * @param string $date_from
     * @param int $days_count
     * @param int $page
     * @param int $limit
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionDatelist(
        string $date_from,
        int    $days_count = 14,
        int    $page = 1,
        int    $limit = 10
    ): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new AmbulanceDatelistModel([
            'id_organization' => $this->extractIdOrganization(),
            'date_from' => $date_from,
            'days_count' => $days_count,
            'page' => $page,
            'limit' => $limit,
        ]);

        if (!$model->validate()) {
            $this->errorResponse($model);
        }

        return [
            'result' => $model->list()
        ];
    }

    /**
     * Метод выбора таймслотов специалиста
     *
     * @param string $date_from
     * @param int $id_specialist
     * @param array $services
     * @return array
     */
    public function actionTimelist(
        string $date_from,
        int    $id_specialist,
        array  $services = []
    ): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new AmbulanceTimelistModel([
            'id_organization' => $this->extractIdOrganization(),
            'date_from' => $date_from,
            'id_specialist' => $id_specialist,
            'services' => $services,
        ]);

        if (!$model->validate()) {
            $this->errorResponse($model);
        }

        return [
            'result' => $model->timelist()
        ];
    }

    /**
     * @return int|null
     * @throws \Throwable
     */
    private function extractIdOrganization()
    {
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();

        return \Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) ? null : $user->specialist->id_organization;
    }

    public function actionAccept(
        int $id_request
        //        $id_request = 6656;
    )
    {
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        $id_user = $user->id;
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        //При наличии у бригады заявки в статусе «Принято» бригада не должна иметь возможности принять вызов по следующей заявке.
        //При попытке принять вызов, не взяв в работу предыдущую заявку, должно отображаться уведомление «Для принятия вызова по новой заявке необходимо взять в работу предыдущую».
        //В правом верхнем углу уведомления необходимо реализовать кнопку "крест" для закрытия уведомления.

        if ($id_user) {
            if ($id_request) {
                //узнать номер бригады
                try {
                    $query = Visits::find()->select(['id_brigade', 'id'])->where(['id_request' => $id_request]);
                    $result = $query->one();
                    $id_brigade = $result->id_brigade;
                    $id_visit = $result->id;
                    // обработка полученных данных
                } catch (\Exception $e) {
                    die('Ошибка запроса: ' . $e);
                }

                //
                try {
                    $notFinishedBrigadeVisit = Visits::find()
                        ->select('id')
                        ->where(['id_brigade' => $id_brigade, 'status' => 'P'])
                        ->limit(1)
                        ->scalar();

                    // обработка полученных данных
                } catch (\Exception $e) {
                    die('Ошибка запроса: ' . $e);
                }

                if ($notFinishedBrigadeVisit and $notFinishedBrigadeVisit !== $id_visit) {
                    die("На бригаде есть не завершенная заявка $id_visit");
                }

                //узнать состав бригады
                try {
                    $specialists = BrigadesSpecialists::find()
                        ->select('id_specialist')
                        ->where(['id_brigade' => $id_brigade])
                        ->asArray()
                        ->column();
                    // обработка полученных данных
                } catch (\Exception $e) {
                    die('Ошибка запроса: ' . $e);
                }

                //состав бригады пишем в данные о приеме (специалисты приема)
                foreach ($specialists as $id_specialist) {
                    $visitSpecialistExists = VisitsSpecialists::find()
                        ->where(['id_visit' => $id_visit, 'id_specialist' => $id_specialist])->one();

                    if ($visitSpecialistExists) {
                        try {
                            $visitSpecialistExists->updated_by = $id_user;
                            $visitSpecialistExists->updated_at = time();
                            $visitSpecialistExists->save();
                        } catch (\Exception $e) {
                            die('Ошибка запроса: ' . $e);
                        }
                    } else {
                        try {
                            $visitSpecialists = new VisitsSpecialists();
                            $visitSpecialists->id_specialist = $id_specialist;
                            $visitSpecialists->id_visit = $id_visit;
                            $visitSpecialists->created_by = $id_user;
                            $visitSpecialists->updated_by = $id_user;
                            $visitSpecialists->created_at = time();
                            $visitSpecialists->updated_at = time();
                            $visitSpecialists->save();
                        } catch (\Exception $e) {
                            die('Ошибка запроса: ' . $e);
                        }
                    }


                }
                //меняем статус визита
                $visit = Visits::findOne(['id_request' => $id_request]);
                if ($visit) {
                    $visit->status = "P";
                    $visit->updated_by = $id_user;
                    $visit->updated_at = time();
                    $visit->save(false);
                }
                return true;
            } else {
                echo xml('<message>invalid_request_id</message>');
            }
        } else {
            echo xml('<message>invalid_user_id</message>');
        }
    }
}
