<?php

namespace app\models\db;

use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "shift_type_invalid_intersections".
 *
 * @property int $id
 * @property int $id_type
 *
 */
class ShiftTypeInvalidIntersections extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'shift_type_invalid_intersections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id_type'], 'required'],
            [['id_type'], 'default', 'value' => null],
            [['id_type'], 'integer'],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => ShiftType::class, 'targetAttribute' => ['id_type' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'id_type' => 'Id Shift Type',
        ];
    }

    /**
     * Gets query for [[TypeFirst]].
     *
     * @return ActiveQuery
     */
    public function getType(): ActiveQuery
    {
        return $this->hasOne(ShiftType::class, ['id' => 'id_type']);
    }

    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function all(array $filter, int $limit, int $page): array
    {
        $query = self::find()
            ->select('shift_type.*')
            ->leftJoin('shift_type', 'shift_type.id = shift_type_invalid_intersections.id_type');

        if (empty($filter) === false && empty($filter['type_id']) === false) {
            $query->andFilterWhere(['in', 'shift_type_invalid_intersections.id_type', $filter['type_id']]);
        }

        $totalCount = $query->count();

        return [
            'pages_count' => (int)(($totalCount + $limit - 1) / $limit),
            'total_count' => $totalCount,
            'invalid_intersections' => $query
                ->limit($limit)
                ->offset($page * $limit - $limit)
                ->asArray()
                ->all(),
        ];
    }

    /**
     * @param int $id
     * @return ShiftTypeInvalidIntersections
     * @throws BadRequestHttpException
     */
    public function get(int $id): ShiftTypeInvalidIntersections
    {
        $shiftTypeInvalidIntersections = self::findOne(['id' => $id]);

        if (empty($shiftTypeInvalidIntersections)) {
            throw new BadRequestHttpException(
                sprintf('Указанный элемент ID: %d недопустимых пересечении типов смен не найден', $id)
            );
        }

        return $shiftTypeInvalidIntersections;
    }

    /**
     * @param int $idType
     * @return array
     * @throws BadRequestHttpException
     */
    public function getIdByIdType(int $idType): array
    {
        $shiftTypeInvalidIntersections = self::findOne(['id_type' => $idType]);

        if (empty($shiftTypeInvalidIntersections)) {
            throw new BadRequestHttpException(
                sprintf('Указанный элемент ID_SHIFT_TYPE: %d недопустимых пересечении типов смен не найден', $idType)
            );
        }

        return $shiftTypeInvalidIntersections->getType()
            ->asArray()
            ->one();
    }

    /**
     * @param int $id_type
     * @return ShiftTypeInvalidIntersections
     * @throws BadRequestHttpException
     */
    public function create(int $id_type): ShiftTypeInvalidIntersections
    {
        $this->exist($id_type);

        $shiftTypeInvalidIntersections = new self();

        $shiftTypeInvalidIntersections->id_type = $id_type;

        if ($shiftTypeInvalidIntersections->save() === false) {
            $errors = $shiftTypeInvalidIntersections->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании недопустимых пересечении типов смен' : implode("\n", array_values($errors)));
        }

        return $shiftTypeInvalidIntersections;
    }

    /**
     * @param int $id_type
     * @return void
     * @throws BadRequestHttpException
     */
    private function exist(int $id_type): void
    {
        $shiftTypeInvalidIntersections = self::find()->where(['id_type' => $id_type])->one();

        if (empty($shiftTypeInvalidIntersections) === false) {
            throw new BadRequestHttpException(
                sprintf('Указанный тип: %s уже существует', $id_type)
            );
        }

    }
}
