<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "visit_price".
 *
 * @property int $id
 * @property int $id_visit
 * @property int $id_discount
 * @property string $night_mark_up_ratio
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $price Общая сумма без скидок
 * @property string $price_with_discount Общая сумма со скидками (результат)
 * @property string $balance_tmc_total Сумма за ТМЦ
 * @property string $balance_tmc_discount Скидка за ТМЦ
 * @property string $service_total Сумма за услуги
 * @property string $service_discount Скидка за услуги
 * @property string $bill_num Номер квитации
 *
 * @property Discount $discount
 * @property Visits $visit
 */
class VisitPrice extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visit_price';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_visit'], 'required'],
            [['id_visit', 'id_discount', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_visit', 'id_discount', 'created_by', 'updated_by'], 'integer'],
            [['night_mark_up_ratio', 'price', 'price_with_discount', 'balance_tmc_total', 'balance_tmc_discount', 'service_total', 'service_discount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['bill_num'], 'string'],
            [['id_visit'], 'unique'],
            [['id_discount'], 'exist', 'skipOnError' => true, 'targetClass' => Discount::class, 'targetAttribute' => ['id_discount' => 'id']],
            [['id_visit'], 'exist', 'skipOnError' => true, 'targetClass' => Visits::class, 'targetAttribute' => ['id_visit' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_visit' => 'Id Visit',
            'id_discount' => 'Id Discount',
            'night_mark_up_ratio' => 'Night Mark Up Ratio',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'price' => 'Общая сумма без скидок',
            'price_with_discount' => 'Общая сумма со скидками (результат)',
            'balance_tmc_total' => 'Сумма за ТМЦ',
            'balance_tmc_discount' => 'Скидка за ТМЦ',
            'service_total' => 'Сумма за услуги',
            'service_discount' => 'Скидка за услуги',
            'bill_num' => 'Номер квитации',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscount()
    {
        return $this->hasOne(Discount::class, ['id' => 'id_discount']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit']);
    }
}
