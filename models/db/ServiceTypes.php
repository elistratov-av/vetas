<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "public.service_types".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $sort_by
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class ServiceTypes extends ActiveRecord
{
    const TYPE_GENERAL = 0;
    const TYPE_VACCINATION = 2;     //Вакцинация
    const TYPE_IDENTIFICATION = 6;  //Чипирование
    const TYPE_VSD = 12;            //Оформление ветеринарных сопроводительных документов
    const TYPE_TELE_VETERINARY = 18; //Телеветеренария

    /**
     * Список именованых типов
     *
     * @var int[]
     */
    private $typeNamedCollection = [
        self::TYPE_VACCINATION,
        self::TYPE_IDENTIFICATION,
        self::TYPE_VSD,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.service_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], FullTrimValidator::class],
            [['sort_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'description'], 'string', 'max' => 255],
            [['name', 'description'], FullTrimValidator::class],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => 'Name',
            'description' => 'Description',
            'sort_by'     => 'Sort By',
            'created_by'  => 'Created By',
            'updated_by'  => 'Updated By',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
        ];
    }

    /**
     * Подтверднает сущность но ее типу.
     * Значение типа = id сущности. См. константы вверху класса.
     *
     * @param $typeId
     */
    public function isType($typeId): bool
    {
        return $this->id == $typeId;
    }

    /**
     * @return int
     */
    public function getTypeNamed()
    {
        return in_array($this->id, $this->typeNamedCollection) ? $this->id : self::TYPE_GENERAL;
    }
}
