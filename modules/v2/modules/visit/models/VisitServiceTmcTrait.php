<?php


namespace app\modules\v2\modules\visit\models;

use app\common\models\UserModel;
use app\common\models\VisitStatus;
use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

trait VisitServiceTmcTrait
{
    /**
     * @return UserModel
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function getUserIdentity()
    {
        if (!empty($this->_user)){
            return $this->_user;
        }

        /** @var UserModel $user */
        $this->_user = \Yii::$app->user->getIdentity();
        if ($this->_user->specialist->id_organization == null) {
            throw new ForbiddenHttpException('Пользователь должен состоять в организации');
        }

        return $this->_user;
    }

    /**
     * Валидация $id_visit и $id_visits_gov_service
     *
     * @param $id_visit
     * @param $id_visits_gov_service
     * @param bool $check_is_read_only
     * @param bool $is_equipments
     * @throws BadRequestHttpException
     */
    protected function validateVisit_AND_VisitsGovService($id_visit, $id_visits_gov_service, $check_is_read_only = TRUE, $is_equipments = FALSE)
    {
        $this->visits_gov_service = VisitsGovServices::findOne(['id' => $id_visits_gov_service]);

        if (empty($this->visits_gov_service)) {
            throw new BadRequestHttpException("Указанная услуга не найдена");
        }

        if ($this->visits_gov_service->id_visit !== $id_visit) {
            throw new BadRequestHttpException("Указанная услуга и id_visit не соответствуют друг другу");
        }

        $this->visit = Visits::findOne(['id' => $id_visit]);

        if (empty($this->visit)) {
            throw new BadRequestHttpException("Указанный визит не найден");
        }

        /*
         * Можно редактировать только:
         * - не оплаченные (исключение - можно редактировать оборудование)
         * - завершенные не далее чем 24 назад
         *
         */
        if ($check_is_read_only == FALSE) {
            return;
        }

        if ($this->visit->is_paid == TRUE && $is_equipments == FALSE) {
            throw new BadRequestHttpException("Нельзя редактировать ТМЦ оплаченного визита");
        }

        if ($this->visit->status == VisitStatus::FINISHED && !$this->canEditFinishedVisit()) {
            throw new BadRequestHttpException("Указанный визит нельзя редактировать");
        }

        if ($this->visit->status == VisitStatus::CANCELED || $this->visit->status == VisitStatus::TIMEOUT) {
            throw new BadRequestHttpException("Нельзя редактировать отмененный визит");
        }
    }
}
