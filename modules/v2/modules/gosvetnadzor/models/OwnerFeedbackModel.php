<?php

namespace app\modules\v2\modules\gosvetnadzor\models;

use app\common\components\inform\events\FeedbackDeclinedEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\SubscriptionService;
use app\models\db\ContactTypes;
use app\models\db\Diseases;
use app\models\db\OwnerFeedback;
use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use app\models\db\ViolationType;
use app\modules\v2\modules\pets\models\VaccinationModel;
use app\models\db\PetIdentification;
use yii\web\BadRequestHttpException;

class OwnerFeedbackModel
{
    /**
     * Название заболевания БЕШЕНСТВО в справочнике
     */
    const QUERY_CONSTANTS_DISEASES_NAME_RABIES = 'Бешенство (Rabies)';

    /**
     * Название заболевания ЛЕПТОСПИРОЗ в справочнике
     */
    const QUERY_CONSTANTS_DISEASES_NAME_LEPTOSPIROZ = 'Лептоспироз';

    /**
     * Отказ хозяином предоставить документы о закрытии нарушения
     * Формируется запись в истории Нарушение, а OwnerFeedback не формируется
     *
     * @param $feedbackFormToken
     * @throws BadRequestHttpException
     */
    public function declineProvide($feedbackFormToken, $reason)
    {
        /** @var Violation $violation */
        if (!$violation = Violation::find()->where(['feedback_token' => $feedbackFormToken])->one()) {
            throw new BadRequestHttpException('Не найдено нарушения с переданным токеном обратной связи');
        }
        if (OwnerFeedback::find()->where(['id_violation' => $violation->id_violation, 'is_processed' => false])->one()) {
            throw new BadRequestHttpException('Уже сформирована запись обратной связи по данному нарушению');
        }

        (new ViolationHistoryModel())->addRecordAboutOwnerDeclined($violation->id_violation, $reason);

        return true;
    }

    /**
     * Универсальный метод для обартной связи Хозяина животного по Нарушениям идентификации и вакцинации
     * При получении невалидного набора свойств валидация на уровне Модели OwnerFeedback
     *
     * @param $feedbackFormToken
     * @param null $plannedCloseDate
     * @param null $identificationTypeId
     * @param null $identificationNumber
     * @param null $vaccineId
     * @param null $batch
     * @param null $organizationId
     * @param null $vaccinationDate
     * @param null $expireDate
     * @return OwnerFeedback
     * @throws BadRequestHttpException
     */
    public function create(
        $feedbackFormToken,
        $plannedCloseDate,
        $identificationTypeId,
        $identificationNumber,
        $vaccineId,
        $batch,
        $organizationId,
        $isOutOrg,
        $vaccinationDate,
        $expireDate,
        $productionDate,
        $validUntil)
    {
        /** @var Violation $violation */
        if (!$violation = Violation::find()->where(['feedback_token' => $feedbackFormToken])->one()) {
            throw new BadRequestHttpException('Не найдено нарушения с переданным токеном обратной связи');
        }
        if (OwnerFeedback::find()->where(['id_violation' => $violation->id_violation, 'is_processed' => false])->one()) {
            throw new BadRequestHttpException('Уже сформирована запись обратной связи по данному нарушению');
        }

        $ownerFeedback = new OwnerFeedback();

        $ownerFeedback->id_violation = $violation->id_violation;
        $ownerFeedback->planned_close_date = $plannedCloseDate ?? null;
        $ownerFeedback->id_ident_type = $identificationTypeId ?? null;
        $ownerFeedback->identification_code = $identificationNumber ?? null;
        $ownerFeedback->id_tmc = $vaccineId ?? null;
        $ownerFeedback->batch = $batch ?? null;
        $ownerFeedback->id_organization = $organizationId ?? null;
        $ownerFeedback->is_out_org = $isOutOrg ?? null;
        $ownerFeedback->vaccine_date = $vaccinationDate ?? null;
        $ownerFeedback->expiry_date = $expireDate ?? null;
        $ownerFeedback->production_date = $productionDate ?? null;
        $ownerFeedback->valid_until = $validUntil ?? null;

        if (!$ownerFeedback->save()) {
            $errors = $ownerFeedback->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании предписания' : implode("\n", array_unique(array_values($errors))));
        }

        return $ownerFeedback;
    }

    /**
     * Подтверждение инспектором предоставленных владельцом животного данных
     * Создаём запись в соответствующей таблице о вакцинации/идентификации, закрываем нарушение
     * и отправляем владельцу оповещение о закрытии нарушения при необходимости
     *
     * @param $feedbackId
     * @return bool
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function approve($feedbackId)
    {
        /** @var OwnerFeedback $ownerFeedback */
        if (!$ownerFeedback = OwnerFeedback::find()->where(['id' => $feedbackId])->one()) {
            throw new BadRequestHttpException('Не найдена запись обратной связи с указаным id');
        }
        if ($ownerFeedback->is_processed === true) {
            throw new BadRequestHttpException('Данная запись обратной связи уже обработана');
        }
        /** @var Violation $violation */
        $violation = Violation::find()->where(['id_violation' => $ownerFeedback->id_violation])->one();
        switch ($violation->type->type) {
            case ViolationType::TYPE_IDENT_VIOLATION:
                if ($ownerFeedback->identification_code === null) break;
                $this->createIdentification($violation, $ownerFeedback);
                break;
            case ViolationType::TYPE_VACCINATION_VIOLATION:
                if (!$ownerFeedback->validateVaccineFeedback()) break;
                $this->createVaccination($violation, $ownerFeedback);
                break;
        }

        $this->setPlannedDate($violation, $ownerFeedback);
        $ownerFeedback->is_processed = true;
        $ownerFeedback->save();

        return true;
    }

    /**
     * Отклонение инспектором предоставленных владельцом животного данных
     * OwnerFeedback помечается как обработанный, а владельцу отправляется соответствующее оповещение
     *
     * @param $feedbackId
     * @param $text
     * @return bool
     * @throws BadRequestHttpException
     */
    public function decline($feedbackId, $text)
    {
        /** @var OwnerFeedback $ownerFeedback */
        if (!$ownerFeedback = OwnerFeedback::find()->where(['id' => $feedbackId])->one()) {
            throw new BadRequestHttpException('Не найдена запись обратной связи с указаным id');
        }
        if ($ownerFeedback->is_processed === true) {
            throw new BadRequestHttpException('Данная запись обратной связи уже обработана');
        }
        /** @var Violation $violation */
        $violation = Violation::find()->where(['id_violation' => $ownerFeedback->id_violation])->one();

        $ownerFeedback->is_processed = true;
        $ownerFeedback->save();

        $contacts = SubscriptionService::getOwnerSubscriptions($violation->owner, [ ContactTypes::TYPE_EMAIL ]);
        if (!empty($contacts)) {
            \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new FeedbackDeclinedEvent([
                'text' => $text,
                'violation' => $violation,
                'id_pet' => $violation->id_pet,
                'id_visit' => $violation->id_visit,
                'owner_feedback' => $ownerFeedback,
                'contacts' => $contacts,
            ]));
        }

        return true;
    }

    /**
     * @param Violation $violation
     * @param OwnerFeedback $ownerFeedback
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function createVaccination(Violation $violation, OwnerFeedback $ownerFeedback)
    {
        /** @var Diseases $rabies */
        $rabies = Diseases::find()->where(['name' => self::QUERY_CONSTANTS_DISEASES_NAME_RABIES])->one();

        switch ($violation->disease->id) {
            case $rabies->id:
                $vaccineType = VaccinationModel::ATTR_PET_RABIES_VACCINATION;
                break;
            default:
                $vaccineType = VaccinationModel::ATTR_PET_OTHER_VACCINATIONS;
                break;
        }

        (new VaccinationModel())->createByOwnerFeedback($violation->id_pet, $ownerFeedback, $vaccineType);
        $cancelReason = ViolationCancellation::find()->where(['tech_name' => ViolationCancellation::CANCEL_BY_VACCINATION])->one();
        $violation->hasExpiredOrders()
            ? (new ViolationChangeStateModel(['id_violation' => $violation->id_violation]))->finish()
            : (new ViolationChangeStateModel(['id_violation' => $violation->id_violation]))->cancel($cancelReason->id_cancellation);
    }

    /**
     * @param Violation $violation
     * @param OwnerFeedback $ownerFeedback
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    private function createIdentification(Violation $violation, OwnerFeedback $ownerFeedback)
    {
        if (PetIdentification::find()->where(['identification_code' => $ownerFeedback->identification_code])->one()) {
            throw new BadRequestHttpException('Уже существует запись идентификации с переданным identification_code');
        }

        $identification = new PetIdentification();

        $identification->id_pet = $violation->pet->id;
        $identification->identification_code = $ownerFeedback->identification_code;
        $identification->main_flag = true;
        $identification->created_at = time();
        $identification->id_ident_type = $ownerFeedback->id_ident_type;
        $identification->save();

        (new ViolationChangeStateModel(['id_violation' => $violation->id_violation]))->finish();
    }

    /**
     * @param Violation $violation
     * @param OwnerFeedback $feedback
     */
    private function setPlannedDate(Violation $violation, OwnerFeedback $feedback)
    {
        $pet = $violation->pet;
        /** @var Diseases $rabies */
        $rabies = Diseases::find()->where(['name' => self::QUERY_CONSTANTS_DISEASES_NAME_RABIES])->one();
        /** @var Diseases $leptospiroz */
        $leptospiroz = Diseases::find()->where(['name' => self::QUERY_CONSTANTS_DISEASES_NAME_LEPTOSPIROZ])->one();

        if ($plannedDate = $feedback->planned_close_date) {
            $violation->date_plan = $plannedDate;
            if ($violation->disease) {
                switch ($violation->disease->id) {
                    case $rabies->id:
                        $pet->date_plan_rabies_vaccination = $plannedDate;
                        $pet->save();
                        break;
                    case $leptospiroz->id:
                        $pet->date_plan_lept_vaccination = $plannedDate;
                        $pet->save();
                        break;
                }
            }
        }
    }
}
