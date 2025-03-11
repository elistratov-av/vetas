<?php

namespace app\models\db;

/**
 * Class Dictionaries
 * @package app\models\db
 *
 * @property integer $id
 * @property string  $name
 * @property string  $type
 * @property integer $created_by
 * @property integer $updated_by
 * @property string  $created_at
 * @property string  $updated_at
 */
class Dictionaries extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.dictionaries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            ['name', 'string'],
            ['type', 'string', 'max' => 255],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * @param string $type
     * @param bool   $asArray
     * @param array  $orderBy
     * @return array|\app\models\db\Dictionaries[]
     */
    public static function findByType($type, $asArray = true, $orderBy = ['name' => SORT_ASC])
    {
        return static::find()
            ->where(['type' => $type])
            ->orderBy($orderBy)
            ->asArray($asArray)
            ->all();
    }
}
