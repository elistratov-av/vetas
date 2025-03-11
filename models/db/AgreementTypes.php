<?php

namespace app\models\db;

use Yii;

/**
 * Модель типа согласия
 *
 * @property int $id
 * @property string $name
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class AgreementTypes extends \yii\db\ActiveRecord
{
    /**
     * @var int Обработка персональных данных
     */
    const PD_PROCESSING = 1;

    /**
     * @var int Хирургическое вмешательство
     */
    const SURGERY = 2;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.agreement_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'created_at' => 'Дата создания',
            'created_by' => 'Создал (ID пользователя)',
            'updated_at' => 'Дата изменения',
            'updated_by' => 'Изменил (ID пользователя)',
        ];
    }
}