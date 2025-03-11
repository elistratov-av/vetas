<?php

namespace app\models\db;

/**
 * Class DescriptionTypes
 * @package app\models\db
 *
 * @property int    $id
 * @property string $name
 * @property string $entity_type
 * @property int    $sort_by
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $tech_name Текстовая константа
 */
class DescriptionTypes extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.description_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'entity_type', 'tech_name'], 'required'],
            [['name', 'entity_type'], 'filter', 'filter' => 'trim'],
            [['name', 'entity_type'], 'filter', 'filter' => 'strip_tags'],
            [['name', 'entity_type'], 'string', 'max' => 255],
            [['tech_name'], 'string', 'max' => 128],
            [['name', 'entity_type'], 'unique', 'targetAttribute' => ['name', 'entity_type']],
            [['tech_name'], 'unique'],
            ['sort_by', 'integer'],
        ];
    }
}
