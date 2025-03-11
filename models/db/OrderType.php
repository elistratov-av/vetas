<?php

namespace app\models\db;

/**
 * @property string $name
 */
class OrderType extends ActiveRecord
{
    const TYPE_PRIMARY = 'Primary';
    const TYPE_SECONDARY = 'Secondary';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_type', 'name'], 'required'],
            [['id_type'], 'integer'],
            [['name'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            ['name' => 'Наименование']
        ];
    }
}
