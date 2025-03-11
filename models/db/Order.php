<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "public.order"
 *
 * @property integer $id_order
 * @property string $number
 * @property integer $id_violation
 * @property integer $id_type
 * @property string $date_order
 * @property string $date_to
 *
 * @property OrderType $order_type
 * @property Violation $violation
 * @property boolean $is_primary
 */
class Order extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['number', 'id_violation', 'id_type', 'date_order', 'date_to'], 'required'],
//            [['number'], 'string', 'length' => 11],
            [['number'], 'unique'],
            [['number'], FullTrimValidator::class],
            [['id_violation', 'id_type'], 'integer'],
            [['date_order', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
            // TODO: вернуть валидацию даты "устранить до" после сдачи
//            [['date_to'], 'compare', 'compareValue' => date("Y-m-d"), 'operator' => '>'],
            [['date_order'], 'compare', 'compareValue' => date("Y-m-d"), 'operator' => '<='],
            [['id_violation'], 'exist', 'skipOnError' => true, 'targetClass' => Violation::class, 'targetAttribute' => ['id_violation' => 'id_violation']],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => OrderType::class, 'targetAttribute' => ['id_type' => 'id_type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_order' => 'ID',
            'number' => 'Номер предписания',
            'date_order' => 'Дата предписания',
            'date_to' => 'Устранить до',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolation()
    {
        return $this->hasOne(Violation::class, ['id_violation' => 'id_violation']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder_type()
    {
        return $this->hasOne(OrderType::class, ['id_type' => 'id_type']);
    }

    public function getArv()
    {
        return $this->hasOne(ViolationToARV::class, ['id_order' => 'id_order']);
    }

    public function getIs_primary()
    {
        return $this->order_type === OrderType::TYPE_PRIMARY;
    }
}
