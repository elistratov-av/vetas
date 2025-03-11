<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "faq2".
 *
 * @property int $id
 * @property string $question Вопрос
 * @property string $answer Ответ
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Faq2 extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'faq2';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['question', 'answer'], 'required'],
            [['question', 'answer'], FullTrimValidator::class],
            [['question'], 'unique'],
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
            'question' => 'Вопрос',
            'answer' => 'Ответ',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFaq2GroupLinks()
    {
        return $this->hasMany(Faq2ToGroup::class, ['id_faq2' => 'id']);
    }

}
