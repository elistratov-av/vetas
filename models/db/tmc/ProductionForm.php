<?php

namespace app\models\db\tmc;

use Yii;
use app\models\db\ActiveRecord;

/**
 * This is the model class for table "tmc.production_form".
 *
 * @property int $id id
 * @property int $id_tmc
 * @property string $type_tmc Тип тмц
 * @property string $name Наименование
 * @property string $volume Объем
 * @property bool $is_utilize Переиначено: списывать целиком (формой выпуска)
 * @property bool $is_deleted Удалено
 * @property int $created_by Автор добавления
 * @property int $updated_by Автор последнего изменения
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 */
class ProductionForm extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.production_form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_tmc', 'type_tmc', 'name'], 'required'],
            [['id_tmc', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_tmc', 'created_by', 'updated_by'], 'integer'],
            [['type_tmc'], 'string'],
            [
                ['type_tmc'], 'in',
                'range' => [
                    TmcBase::TYPE_VACCINE, TmcBase::TYPE_DRUG, TmcBase::TYPE_EXP_MATERIAL,
                ],
                'strict' => true, 'skipOnEmpty' => false, 'skipOnError' => false
            ],
            [['volume'], 'number'],
            [['is_utilize', 'is_deleted'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['id_tmc', 'type_tmc'], 'exist', 'skipOnError' => true, 'targetClass' => TmcBase::class, 'targetAttribute' => ['id_tmc' => 'id', 'type_tmc' => 'type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'id_tmc' => 'Id Tmc',
            'type_tmc' => 'Тип тмц',
            'name' => 'Наименование',
            'volume' => 'Объем',
            'is_utilize' => 'Автоматически утилизировать остаток после использования',
            'is_deleted' => 'Удалено',
            'created_by' => 'Автор добавления',
            'updated_by' => 'Автор последнего изменения',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
        ];
    }
}
