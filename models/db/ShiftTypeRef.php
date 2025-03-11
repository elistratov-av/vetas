<?php

namespace app\models\db;

use app\modules\v2\modules\timesheet\models\ShiftsModel;
use DateTime;
use Throwable;
use yii\db\ActiveQuery;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * This is the model class for table "shift_type_ref".
 *
 * @property int $id
 * @property string $title Наименовании смены
 * @property string $beginning_of_shift Начало смены
 * @property string $end_of_shift Окончание смены
 * @property int $shift_type Тип смены
 *
 * @property ShiftType $shiftType
 */
class ShiftTypeRef extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'shift_type_ref';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'beginning_of_shift', 'end_of_shift', 'shift_type'], 'required'],
            [['shift_type'], 'default', 'value' => null],
            [['shift_type'], 'integer'],
            [['beginning_of_shift', 'end_of_shift'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['shift_type'], 'exist', 'skipOnError' => true, 'targetClass' => ShiftType::class, 'targetAttribute' => ['shift_type' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'beginning_of_shift' => 'Beginning Of Shift',
            'end_of_shift' => 'End Of Shift',
            'shift_type' => 'Shift Type',
        ];
    }

    /**
     * Gets query for [[ShiftType]].
     *
     * @return ActiveQuery
     */
    public function getShiftType(): ActiveQuery
    {
        return $this->hasOne(ShiftType::class, ['id' => 'shift_type']);
    }

    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function all(array $filter, int $limit, int $page): array
    {
        $query = self::find();

        if (empty($filter) === false) {

            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'title':
                        $query->andFilterWhere(['ilike', 'title', mb_strtolower($value)]);
                        break;
                    case 'type_id':
                        $query->andFilterWhere(['in', 'shift_type', $value]);
                        break;
                }
            }
        }

        $totalCount = $query->count();

        return [
            'pages_count' => (int) (($totalCount + $limit - 1) / $limit),
            'total_count' => $totalCount,
            'shift_type_ref' => $query
                ->limit($limit)
                ->offset($page * $limit - $limit)
                ->all(),
        ];
    }

    /**
     * @param int $id
     * @return ShiftTypeRef
     * @throws BadRequestHttpException
     */
    public function get(int $id): ShiftTypeRef
    {
        $shiftTypeRef = self::findOne(['id' => $id]);

        if (empty($shiftTypeRef)) {
            throw new BadRequestHttpException(
                sprintf('Не найдено типа смен ID: %d', $id)
            );
        }

        return $shiftTypeRef;
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws Throwable
     */
    public function create(array $data): array
    {
        $beginningOFShift = new DateTime($data['beginning_of_shift']);
        $endOfShift = new DateTime($data['end_of_shift']);

        $shiftTypeRef = new self();

        $shiftTypeRef->title = $data['title'];
        $shiftTypeRef->beginning_of_shift = $beginningOFShift->format('H:i:s');
        $shiftTypeRef->end_of_shift = $endOfShift->format('H:i:s');
        $shiftTypeRef->shift_type = $data['shift_type'];

        if ($shiftTypeRef->save() === false) {
            $errors = $shiftTypeRef->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании типов смен' : implode("\n", array_values($errors)));
        }

        $diff = $beginningOFShift->diff($endOfShift);
        $duration = $diff->h * 60 + $diff->i;

        $errors = $this->createShifts($shiftTypeRef, $duration);

        return [
            'shiftTypeRef' => $shiftTypeRef,
            'errors' => $errors,
        ];
    }

    /**
     * @param ShiftTypeRef $shiftTypeRef
     * @param int $duration
     * @return array
     * @throws ForbiddenHttpException
     * @throws Throwable
     */
    private function createShifts(self $shiftTypeRef, int $duration): array
    {
        $errors = [];
        $organizations = Organizations::find()
            ->all();

        foreach ($organizations as $organization) {
            try {
                (new ShiftsModel())
                    ->save(
                        $shiftTypeRef->title,
                        $shiftTypeRef->beginning_of_shift,
                        $duration,
                        $shiftTypeRef->shift_type,
                        $organization->id,
                        null,
                        null,
                        $shiftTypeRef->id,
                    );
            } catch (BadRequestHttpException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    /**
     * @param int $id
     * @return void
     * @throws BadRequestHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function deleteById(int $id)
    {
        $shiftTypeRef = $this->get($id);

        if ($shiftTypeRef->delete() === false) {
            $errors = $shiftTypeRef->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении типов смен' : implode("\n", array_values($errors)));
        }

        Shifts::deleteAll(['shift_type_ref_id' => $shiftTypeRef->id]);
    }
}
