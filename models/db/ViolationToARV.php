<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "public.violation_ARV"
 *
 * @property integer $id
 * @property integer $id_ARV
 * @property integer $id_violation
 * @property Order $id_order
 * @property string $number
 * @property string $date_ARV
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Order $order
 * @property Violation $violation
 * @property ViolationAdminRights $violation_admin_rights
 */
class ViolationToARV extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'violation_ARV';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_ARV', 'id_violation', 'id_order', 'number', 'date_ARV'], 'required'],
            [['id_ARV', 'id_violation', 'id_order'], 'integer'],
//            [['number'], 'string', 'length' => 11],
            [['number'], 'unique'],
            [['number'], FullTrimValidator::class],
            [['date_ARV'], 'date', 'format' => 'php:Y-m-d'],
            [['date_ARV'], 'compare', 'compareValue' => date("Y-m-d"), 'operator' => '<='],
            [['id_violation'], 'exist', 'skipOnError' => true, 'targetClass' => Violation::class, 'targetAttribute' => ['id_violation' => 'id_violation']],
            [['id_order'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['id_order' => 'id_order']],
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
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id_order' => 'id_order']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolation_admin_rights()
    {
        return $this->hasOne(ViolationAdminRights::class, ['id_ARV' => 'id_ARV']);
    }
}
