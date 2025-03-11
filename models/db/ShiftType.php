<?php

namespace app\models\db;

use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "public.shift_type".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string  $type
 * @property boolean $idle
 * @property string  $description
 * @property string  $colour
 * @property string  $overlap_category Категория. Используется для правил пересечений смен
 */
class ShiftType extends \yii\db\ActiveRecord
{

    /**
     * Содержит перечень типов приёма, которые могут иметь детей под собой
     */
    const HAVE_CHILDRENS = ['WORKDAY'];

    /**
     * Содержит перечень типов приёма, которые могут быть исплоьзованы, как родительские таймшиты
     */
    const PARENTS = ['WORKDAY', 'SICK_LEAVE', 'VACATION', 'HOLIDAY'];

    /**
     * Содержит перечень типов приёма, которые НЕ могут быть детьми
     */
    const NOT_CHILDRENS = ['WORKDAY', 'SICK_LEAVE', 'VACATION', 'HOLIDAY'];

    /**
     * Содержит перечень типов приёма, которые относятся к живой очереди
     */
    const WITH_TIME_LIVE_QUEUE = ['LIVE_QUEUE', 'BREAK'];

    /**
     * Содержит тип приёма, который является живой очередью
     */
    const ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE = 'LIVE_QUEUE';

    /**
     * Содержит тип приёма для записи по телефону
     */
    const ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT = 'PHONE_APPOINTMENT';

    /**
     * Содержит тип приёма для записи по направлению
     */
    const ASSIGN_SHIFT_TYPE_FOR_WORKDAY = 'WORKDAY';

    /**
     * Содержит тип приёма для записи с mos.ru
     */
    const ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT = 'MOSRU_APPOINTMENT';

    /**
     * Содержит тип приёма для записи с mos.ru
     */
    const ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME = 'MOSRU_CALL_TO_HOME';

    /**
     * Содержит тип приёма для выезда на дом
     */
    const ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME = 'CALL_TO_HOME';

    /**
     * Содержит тип приёма для выезда на дом бригадой НВП
     */
    const ASSIGN_SHIFT_TYPE_FOR_AMBULANCE = 'AMBULANCE';

    /**
     * Содержит тип приёма Прививочный пункт
     */
    const ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION = 'VACCINATION_STATION';

    /**
     * Содержит тип приёма Обход
     */
    const ASSIGN_SHIFT_TYPE_FOR_DETOUR = 'DETOUR';

    /**
     * Содержит тип приёма Выезд в приют
     */
    const ASSIGN_SHIFT_TYPE_FOR_SHELTER = 'SHELTER';

    /**
     * Увеодмления не отправляются на записи с mos.ru(уходят через ЕТП) и для живой очереди
     */
    const NOTIFY_DISABLED = [
        self::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT,
        self::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME,
        self::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE,
        self::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE
    ];

    /**
     * Категории (для правил пересечения смен)
     */
    const OVERLAP_CATEGORY_CLINIC = 'CLINIC';
    const OVERLAP_CATEGORY_AT_HOME = 'AT_HOME';
    const OVERLAP_CATEGORY_EVENT = 'EVENT';
    const OVERLAP_CATEGORY_IDLE = 'IDLE';
    const OVERLAP_CATEGORY_TOP_LEVEL = 'TOP_LEVEL';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.shift_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
            [['type', 'description', 'colour', 'overlap_category'], 'required'],
            [['idle'], 'boolean'],
            [['type'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 256],
            [['colour'], 'string', 'max' => 255],
            [['type'], 'unique'],
            [
                ['parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ShiftType::class,
                'targetAttribute' => ['parent_id' => 'id']
            ],
            [['overlap_category'], 'string', 'max' => 32],
            [
                ['overlap_category'],
                'in',
                'range' => [
                    self::OVERLAP_CATEGORY_AT_HOME,
                    self::OVERLAP_CATEGORY_CLINIC,
                    self::OVERLAP_CATEGORY_EVENT,
                    self::OVERLAP_CATEGORY_IDLE,
                    self::OVERLAP_CATEGORY_TOP_LEVEL,
                ],
                'strict' => true
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'type' => 'Type',
            'idle' => 'Idle',
            'description' => 'Description',
            'colour' => 'Colour',
            'overlap_category' => 'Категория. Используется для правил пересечений смен',
        ];
    }

    public function getShift(): ActiveQuery
    {
        return $this->hasOne(Shifts::class, ['id_type' => 'id']);
    }

    /**
     * @return array
     */
    public function getGroupedTypes(): array
    {
        return ArrayHelper::index(self::find()->asArray()->all(), null, 'id');
    }

    /**
     * Возвращает истину, если текущий тип приёма может быть родителем
     *
     * @return bool
     */
    public function canBeParent(): bool
    {
        return in_array($this->type, self::PARENTS);
    }

    /**
     * Возвращает истину, если текущий тип приёма может иметь детей
     *
     * @return bool
     */
    public function canHaveChilds(): bool
    {
        return in_array($this->type, self::HAVE_CHILDRENS);
    }

    /**
     * Возвращает истину, если текущий тип приёма может быть ребёнком
     *
     * @return bool
     */
    public function canBeChild(): bool
    {
        return !in_array($this->type, self::NOT_CHILDRENS);
    }

    /**
     * Возвращает id сущности живой очереди
     *
     * @return int
     */
    public static function getTypesIdWithTimeLiveQueue(): int
    {
        return self::find()
            ->select(['id'])
            ->where(['type' => self::WITH_TIME_LIVE_QUEUE])
            ->scalar();
    }

    /**
     * Возвращает id сущности вызова на дом
     *
     * @return int|false
     */
    public static function getTypesIdWithCallToHome()
    {
        $shiftType = ShiftType::find()
            ->select(['id'])
            ->where(['type' => self::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME])
            ->one();
        return $shiftType->id ?? false;
    }

    /**
     * Возвращает id типа для выезда на дом бригады НВП
     *
     * @return int|false
     */
    public static function getTypesIdWithAmbulance()
    {
        $shiftType = ShiftType::find()
            ->select(['id'])
            ->where(['type' => self::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE])
            ->one();

        return $shiftType->id ?? false;
    }
}
