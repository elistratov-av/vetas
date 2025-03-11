<?php


namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\models\db\Pets;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use yii\validators\DateValidator;
use yii\web\BadRequestHttpException;

class ViolationController extends BaseController
{
    /**
     * Выдает список нарушений
     *
     * @param string $type
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param array $with_status_counts
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(string $type, int $page = 1, int $limit = 10, array $filter = [], array $with_status_counts = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationModel())->getList($type, $page, $limit, $filter, $with_status_counts)
        ];
    }

    /**
     * Метод для получения количества записей по фильтру, сигнатура фильтра идентична методу actionList()
     *
     * @param string $type
     * @param array $filter
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCountByStatus(string $type, array $filter)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationModel())->getViolationCounts($type, $filter)
        ];
    }

    /**
     * Выдаёт список логов оповещений по выбраному нарушению
     *
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionNotificationList(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationModel())->getNotificationLogList($id)
        ];
    }

    /**
     * Просмотр нарушения
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationModel())->getViolation($id, true)
        ];
    }

    /**
     * Редактирование нарушения
     *
     * @param $id
     * @param $id_type
     * @param $id_ARV
     * @param null $comment
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit($id, $id_type, $id_ARV, $comment = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationModel())->editViolation($id, $id_type, $id_ARV, $comment)
        ];
    }

    /**
     * Переключаем в состояние "В работе"
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionStart(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationChangeStateModel(['id_violation' => $id]))->start()
        ];
    }

    /**
     * Переключаем в состояние "Завершено"
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionFinish(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationChangeStateModel(['id_violation' => $id]))->finish()
        ];
    }

    /**
     * Переключаем в состояние "Подтверждено"
     *
     * @param int $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionVerify(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationChangeStateModel(['id_violation' => $id]))->verify()
        ];
    }

    /**
     * Переключаем в состояние "Отменено"
     *
     * @param int $id
     * @param int $id_cancellation
     * @param string|null $cancellation_details
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCancel(int $id, int $id_cancellation, string $cancellation_details = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' =>
                (new ViolationChangeStateModel(['id_violation' => $id]))
                ->cancel($id_cancellation, $cancellation_details)
        ];
    }

    /**
     * @param int $id
     * @param string $template_type
     * @param string|null $comment
     * @param string|null $exp_date
     * @param integer[]|null $file_ids
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionNotify(int $id, string $template_type, string $comment = null, string $exp_date = null, array $file_ids = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationModel())->notify($id, $template_type, $comment, $exp_date, $file_ids)
        ];
    }

    /**
     * @param int $id_pet
     * @param string $date_plan_identification
     * @param string $date_plan_rabies_vaccination
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionPlannedSave($id_pet, $date_plan_identification = null, $date_plan_rabies_vaccination = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $pet = Pets::findOne(['id' => $id_pet]);

        if ($pet === null) {
            throw new BadRequestHttpException('Животное не найдено');
        }

        if (!empty($date_plan_identification)) {
            $field = 'date_plan_identification';
            $pet->$field = $date_plan_identification;
        } elseif (!empty($date_plan_rabies_vaccination)) {
            $field = 'date_plan_rabies_vaccination';
            $pet->$field = $date_plan_rabies_vaccination;
        } else {
            throw new BadRequestHttpException('Не переданы обязательные параметры');
        }

        if (!$pet->save(true, [$field, 'updated_at', 'updated_by'])) {
            $errors = $pet->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении' : implode("\n", array_values($errors)));
        }

        return [
            'result' => true
        ];
    }
}
