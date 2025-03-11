<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "faq2_group".
 *
 * @property int $id
 * @property string $code Мнемоника группы
 * @property string $title Название группы
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Faq2Group extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'faq2_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'title'], 'required'],
            [['code', 'title'], FullTrimValidator::class],
            [['code'], 'unique'],
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
            'code' => 'Мнемоника',
            'title' => 'Название',
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
        return $this->hasMany(Faq2ToGroup::class, ['id_faq2_group' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFaq2()
    {
        return $this->hasMany(Faq2::class, ['id' => 'id_faq2'])
            ->via('faq2GroupLinks');
    }
}
