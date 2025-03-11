<?php


namespace app\modules\v2\modules\gosvetnadzor\models;


use app\models\db\Users;
use app\models\db\Violation;
use app\models\db\ViolationCancellation;
use app\models\db\ViolationHistory;
use Yii;
use yii\db\Expression;
use yii\web\BadRequestHttpException;

class ViolationHistoryModel
{
    /**
     * Возвращает историю работы над нарушением
     *
     * @param $id_violation
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     */
    public function getHistory($id_violation)
    {
        // Проверяем - сущестует ли такое нарушение вообще
        self::findViolation($id_violation);

        return ViolationHistory::find()
            ->where(['id_violation' => $id_violation])
            ->with(['inspector' => function ($query) {
                /** @var $query \yii\db\ActiveQuery */
                $query->select([
                    'id',
                    'f_fio',
                    'i_fio',
                    'o_fio',
                    'fullname',
                    'birthday',
                    'sex',
                    'photo'
                ]);
            }])
            ->orderBy('date DESC')
            ->asArray()
            ->all();
    }

    /**
     * Запись о редактировании
     *
     * @param Violation $violation
     * @throws BadRequestHttpException
     */
    public function addRecordAboutEdit($violation)
    {
        $history_record = new ViolationHistory([
            'id_violation' => $violation->id_violation,
            'id_inspector' => Yii::$app->user->id,
            'date' => date('Y-m-d H:i:s'),
            'description' => 'Нарушение отредактировано'
        ]);

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Запись об отправке сообщения
     *
     * @param Violation $violation
     * @param string $description
     * @throws BadRequestHttpException
     */
    public function addRecordAboutSendNotify($violation, $description): ViolationHistory
    {
        $history_record = new ViolationHistory([
            'id_violation' => $violation->id_violation,
            'id_inspector' => Yii::$app->user->id,
            'date' => date('Y-m-d H:i:s'),
            'description' => $description
        ]);

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }

        return $history_record;
    }

    /**
     * Запись об отправке уведомления о закрытом нарушении
     *
     * @param $violation
     * @param $description
     * @param $inspector_id
     * @return ViolationHistory
     * @throws BadRequestHttpException
     */
    public function addRecordAboutSendViolationClosedNotify($violation, $description, $inspector_id): ViolationHistory
    {
        $history_record = new ViolationHistory([
            'id_violation' => $violation->id_violation,
            'id_inspector' => $inspector_id,
            'date' => date('Y-m-d H:i:s'),
            'description' => $description,
        ]);

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }

        return $history_record;
    }

    /**
     * Запись о смене статуса
     *
     * @param Violation $violation
     * @throws BadRequestHttpException
     */
    public function addRecordAboutSwitchState($violation)
    {
        // Если violationCancellation имеет tech_name, значит она производится приложением, а не человеком
        // соответственно меняются описание в истории и автор изменения на "системного" пользователя (используемого приложением)
        $cancellation = $violation->cancellation;
        $isSystemStateChange = $cancellation && !!$cancellation->tech_name && !in_array($cancellation->tech_name, ViolationCancellation::PUBLIC_TECH_NAMES);
        $userId = $isSystemStateChange ? Users::find()->where('is_system_user IS TRUE')->one()->id : Yii::$app->user->id;

        $history_record = new ViolationHistory([
            'id_violation' => $violation->id_violation,
            'id_inspector' => $userId,
            'date' => date('Y-m-d H:i:s'),
        ]);

        switch ($violation->state) {
            case Violation::STATE_IN_WORK:
                $history_record->description = 'Взято в работу';
                break;
            case Violation::STATE_CANCELED:
                $history_record->description = $isSystemStateChange ? $cancellation->description : 'Отменено';
                break;
            case Violation::STATE_FINISHED:
                $history_record->description = 'Завершено';
                break;
            case Violation::STATE_ACCEPTED:
                $history_record->description = 'Подтверждено';
                break;
            default:
                throw new BadRequestHttpException('Системная ошибка: непредусмотренный переход состояний нарушения');
        }

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Запись о прикреплении файла
     *
     * @param int $id_violation
     * @param string $file_name
     * @throws BadRequestHttpException
     */
    public static function addRecordAboutFileAttach(int $id_violation, string $file_name)
    {
        $history_record = new ViolationHistory([
            'id_violation' => $id_violation,
            'id_inspector' => Yii::$app->user->id,
            'date' => date('Y-m-d H:i:s'),
            'description' => 'Прикреплен файл: ' . $file_name,
        ]);

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Запись о прикреплении файла
     *
     * @param int $id_violation
     * @param string $file_name
     * @throws BadRequestHttpException
     */
    public static function addRecordAboutFileDelete(int $id_violation, string $file_name)
    {
        $history_record = new ViolationHistory([
            'id_violation' => $id_violation,
            'id_inspector' => Yii::$app->user->id,
            'date' => date('Y-m-d H:i:s'),
            'description' => 'Удален файл: ' . $file_name,
        ]);

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Запись об отказе владельца животного предоставить данные по нарушению с причиной отказа
     *
     * @param int $id_violation
     * @param string $reason
     * @throws BadRequestHttpException
     */
    public static function addRecordAboutOwnerDeclined(int $id_violation, string $reason)
    {
        $history_record = new ViolationHistory([
            'id_violation' => $id_violation,
            'id_inspector' => Users::find()->where(['is_system_user' => true])->one()->id,
            'date' => date('Y-m-d H:i:s'),
            'description' => "Отказ владельца: $reason",
        ]);

        if (!$history_record->save()) {
            $errors = $history_record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении истории нарушения' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Возвращает нарушение по его id или генерирует ошибку если не оно не нашлось
     *
     * @param $id_violation
     * @return Violation|null
     * @throws BadRequestHttpException
     */
    protected static function findViolation($id_violation)
    {
        $violation = Violation::findOne(['id_violation' => $id_violation]);

        if (empty($violation)) {
            throw new BadRequestHttpException('Указанное нарушение не найдено');
        }

        return $violation;
    }
}
