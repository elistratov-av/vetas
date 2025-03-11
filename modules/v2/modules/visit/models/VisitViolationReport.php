<?php

namespace app\modules\v2\modules\visit\models;

use app\common\models\VisitStatus;
use app\models\db\Diseases;
use app\models\db\Violation;
use app\models\db\ViolationType;
use app\models\db\Visits;
use Exception;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class VisitViolationReport
{

    /**
     * Сообщают (на данный момент) только об отсутсвии вакцинации от бешенства
     */
    private const VACCINATION_REJECTION_DISEASE = 'Бешенство (Rabies)';

    /**
     * Список разрешенных типов (по tech_name) которые мы можем обрабатывать в приеме
     * @var array
     */
    protected $_allowed_types = [
        ViolationType::V02_IDENT_REJECTION,
        ViolationType::V04_VACCINATION_REJECTION,

        ViolationType::V05_OTHER_QUARANTINE_OR_VETERINARY_RULES,
        ViolationType::V06_OTHER_CONCEALMENT_DEATH_OR_MASS_DISEASE,
        ViolationType::V07_OTHER_TRANSPORTATION_RULES,
        ViolationType::V08_OTHER_RULES_OF_BIOLOGICAL_WASTE,
    ];

    /**
     * Добавляет сообщение о нарушении в рамках визита
     *
     * @param Visits $visit
     * @param int    $id_type
     * @param array  $comments
     * @param array  $ids_pet
     * @param bool   $set_visit
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function report(Visits $visit, int $id_type, array $comments, array $ids_pet, bool $set_visit): array
    {
        $violation_type = $this->getAndCheckViolationType($id_type);
        $this->checkVisit($visit, $ids_pet);

        if ($visit->variety === Visits::VISIT_SINGLE) {
            $ids_pet = [$visit->pets[0]->id];
        }

        /*
         * Сообщают (на данный момент) только об отсутсвии вакцинации от бешенства
         */
        if ($violation_type->tech_name === ViolationType::TYPE_VACCINATION_VIOLATION) {
            $disease = Diseases::find()
                ->where([
                    'name' => self::VACCINATION_REJECTION_DISEASE,
                ])->one();

            if (empty($disease->id)) {
                throw new BadRequestHttpException(
                    'Не удалось найти запись в справочнике [' . self::VACCINATION_REJECTION_DISEASE . ']. Обратитесь к администратору'
                );
            }

            $id_disease = $disease->id;
        } else {
            $id_disease = null;
        }

        $result = [];
        foreach ($ids_pet as $id_pet) {
            try {
                if (array_key_exists($id_pet, $comments) === false) {
                    throw new Exception('Отсутствует комментарий');
                }
                $this->createViolation(
                    $visit,
                    $id_pet,
                    $id_type,
                    $id_disease,
                    $violation_type,
                    $comments[$id_pet],
                    $set_visit
                );
                $result[$id_pet] = true;
            } catch (Exception $e) {
                $result[$id_pet] = $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param Visits        $visit
     * @param int           $id_pet
     * @param int           $id_type
     * @param int           $id_disease
     * @param ViolationType $violation_type
     * @param string        $comment
     *
     * @throws Exception
     */
    private function createViolation(
        Visits $visit,
        int $id_pet,
        int $id_type,
        ?int $id_disease,
        ViolationType $violation_type,
        string $comment,
        bool $set_visit
    ): void
    {
        $violation = new Violation([
            'state' => Violation::STATE_ACCEPTED,
            'id_owner' => $visit->id_owner,
            'id_pet' => $visit->variety === Visits::VISIT_SINGLE ? $visit->pets[0]->id : $id_pet,
            'id_type' => $id_type,
            'id_disease' => $id_disease,
            'id_veterinarian' => Yii::$app->user->id,
            'id_visit' => $set_visit ? $visit->id : null,
            'date_violation' => new Expression('NOW()'),
        ]);

        /*
         * Для отказов заполняем соответствующее поле
         */
        if (in_array($violation_type->tech_name, [ViolationType::V04_VACCINATION_REJECTION, ViolationType::V02_IDENT_REJECTION], true)) {
            $violation->rejection_reason = $comment;
        } else {
            $violation->comment = $comment;
        }

        /*
         * Такое же есть?
         */
        if ($violation->isPetHasActiveViolation()) {
            throw new Exception('На это животное уже заведено такое же нарушение');
        }

        if (!$violation->save()) {
            $errors = $violation->getErrorSummary(true);
            throw new Exception(empty($errors) ? 'Ошибка при создании нарушения' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Получает и валидирует тип нарушения
     *
     * @param $id_type
     * @return ViolationType
     * @throws BadRequestHttpException
     */
    protected function getAndCheckViolationType($id_type): ViolationType
    {
        $violation_type = ViolationType::findOne(['id_type' => $id_type]);

        if (is_null($violation_type)) {
            throw new BadRequestHttpException('Указанный тип не найден');
        }

        if (!in_array($violation_type->tech_name, $this->_allowed_types)) {
            throw new BadRequestHttpException('Передан недопустимый тип');
        }

        return $violation_type;
    }

    /**
     * Получает и валидирует визит
     *
     * @param Visits $visit
     * @param array  $ids_pet
     *
     * @throws BadRequestHttpException
     */
    protected function checkVisit(Visits $visit, array $ids_pet): void
    {
        if ($visit->status !== VisitStatus::IN_WORK) {
            throw new BadRequestHttpException('Добавлять нарушение можно только для приема взятого в работу');
        }
        if ($visit->variety !== Visits::VISIT_SINGLE) {
            if (empty($ids_pet)) {
                throw new BadRequestHttpException('Не выбрано ни одного животного');
            }
            if (array_intersect($ids_pet, ArrayHelper::getColumn($visit->pets, 'id')) !== $ids_pet) {
                throw new BadRequestHttpException('Переданный список животных не соответствует животным участвующим в приеме');
            }
        }
    }
}
