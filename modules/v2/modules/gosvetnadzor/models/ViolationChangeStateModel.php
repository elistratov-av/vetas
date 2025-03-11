<?php


namespace app\modules\v2\modules\gosvetnadzor\models;


use app\models\db\OrderType;
use app\models\db\RegExpireReasons;
use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use yii\base\Model;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

class ViolationChangeStateModel extends Model
{
    /**
     * @var
     */
    public $id_violation;

    /**
     * @var Violation
     */
    protected $violation;

    /**
     * @var string
     */
    protected $old_state;

    /**
     * @var string
     */
    protected $new_state;


    /**
     * @throws BadRequestHttpException
     */
    public function init()
    {
        if (empty($this->id_violation) || !is_numeric($this->id_violation)) {
            throw new BadRequestHttpException('ID нарушения не указано');
        }

        $this->loadViolation();
        $this->old_state = $this->violation->state;
    }

    /**
     * Переключаем в состояние "В работе"
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function start()
    {
        $this->new_state = Violation::STATE_IN_WORK;

        if ($this->statusNotChanged()) {
            return true;
        }

        return $this->switchState();
    }

    /**
     * Переключаем в состояние "Завершено"
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function finish()
    {
        $this->new_state = Violation::STATE_FINISHED;

        if ($this->statusNotChanged()) {
            return true;
        }

        if (!$this->violation->hasExpiredOrders()) {
            throw new BadRequestHttpException('Отстутствует предписание с истёкшим сроком.');
        }

        return $this->switchState();
    }

    /**
     * Переключаем в состояние "Подтверждено"
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function verify()
    {
        $this->new_state = Violation::STATE_ACCEPTED;

        if ($this->statusNotChanged()) {
            return true;
        }

        if ((new ViolationModel())->hasPlannedToClose($this->violation)) {
            throw new BadRequestHttpException('Нарушение имеет запланированную дату закрытия');
        }

        return $this->switchState();
    }

    /**
     * Переключаем в состояние "Отменено"
     *
     * @param $id_cancellation
     * @param $cancellation_details
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function cancel($id_cancellation, $cancellation_details = null)
    {
        $this->new_state = Violation::STATE_CANCELED;

        if (
            $this->statusNotChanged()
            && $this->violation->id_cancellation == $id_cancellation
            && $this->violation->cancellation_details == $cancellation_details
        ) {
            return true;
        }

        if (count($this->violation->arvs) > 0) {
            throw new BadRequestHttpException('Нарушение имеет Административно Правовое Нарушение и не может быть отменено');
        }

        if (empty($id_cancellation)) {
            throw new BadRequestHttpException('Укажите причину отмены');
        }

        $this->violation->id_cancellation = $id_cancellation;
        $this->violation->cancellation_details = $cancellation_details;

        return $this->switchState();
    }


    /**
     * Переключаем состояние и создаем запись в истории нарушений
     *
     * @return bool
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    protected function switchState()
    {
        $this->violation->state = $this->new_state;

        Violation::getDb()->beginTransaction();

        if (!$this->violation->save()) {
            $errors = $this->violation->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении нарушения' : implode("\n", array_values($errors)));
        }

        if ($this->violation->isFinished()) {
            (new ViolationModel)->notifyClosedViolation($this->violation);
        }

        /*
         * Пишем в историю
         */
        (new ViolationHistoryModel)
            ->addRecordAboutSwitchState($this->violation);

        /*
         * Если отменено по причине "падеж" - автоматически снять животное с учёта
         */
        if ($this->violation->state === Violation::STATE_CANCELED
            && $this->violation->cancellation->tech_name === ViolationCancellation::CANCEL_BY_DEATH) {
            $reason = RegExpireReasons::find()->where(['tech_name' => RegExpireReasons::TECH_NAME_DEATH])->one();
            $pet = $this->violation->pet;
            $pet->reg_expire_date = date('Y-m-d');
            $pet->id_reg_expire_reason = $reason->id;
            if (!$pet->save()) {
                $errors = $pet->getErrorSummary(true);
                throw new Exception(empty($errors) ? 'Ошибка при снятии животного с учёта' : implode("\n", array_values($errors)));
            }
        }

        Violation::getDb()->transaction->commit();

        return true;
    }

    /**
     * На случай повторной отправки данных с фронта.
     * Если статус не изменился, не будем повторно сохранять прием, но и не будем возвращать ошибку.
     *
     * @return bool
     */
    private function statusNotChanged(): bool
    {
        return $this->new_state === $this->old_state;
    }

    /**
     * Подгружаем указанное нарушение
     * @throws BadRequestHttpException
     */
    protected function loadViolation()
    {
        $this->violation = Violation::findOne(['id_violation' => $this->id_violation]);
        if (empty($this->violation)) {
            throw new BadRequestHttpException('Указанное нарушение не найдено');
        }

        if ($this->violation->isReadOnly()) {
            throw new BadRequestHttpException('Указанное нарушение недоступно для редактирования');
        }
    }
}
