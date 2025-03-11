<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "help".
 *
 * @property int $id
 * @property string $caption Заголовок
 * @property string $text Текст
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Help extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'help';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['caption', 'text'], 'required'],
            [['caption', 'text'], FullTrimValidator::class],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question' => 'Заголовок',
            'answer' => 'Текст',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelp_links()
    {
        return $this->hasMany(HelpLinks::class, ['id_help' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->where(['entity_type' => static::tableName()]);
    }
}
