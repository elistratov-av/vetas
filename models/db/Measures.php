<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;

/**
 * This is the model class for table "measures".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 *
 * @property ContentOfActiveSubstances[] $contentOfActiveSubstances
 * @property ExpMaterials[] $expMaterials
 */
class Measures extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'measures';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            [['description'], FullTrimValidator::class],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 255],
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
            'description' => 'Description',
            'created_by' => 'Автор добавления (id пользователя)',
            'updated_by' => 'Автор последнего изменения (id пользователя)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentOfActiveSubstances()
    {
        return $this->hasMany(ContentOfActiveSubstances::className(), ['id_measure' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpMaterials()
    {
        return $this->hasMany(ExpMaterials::className(), ['id_measure' => 'id']);
    }
}
