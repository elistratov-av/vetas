<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class ReasonVc
 *
 * @property integer $id
 * @property string  $name
 * @property string  $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @package app\models\db
 */
class ReasonVc extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'description'], FullTrimValidator::class],
            [['name'], 'string', 'max' => 255],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'description' => 'Описание',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'created_by' => 'Автор',
            'updated_by' => 'Автор последнего изменения',
        ];
    }
}
