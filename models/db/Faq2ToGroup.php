<?php


namespace app\models\db;

/**
 * This is the model class for table "faq2_to_group".
 *
 * @property int $id
 * @property integer $id_faq2 faq2
 * @property integer $id_faq2_group faq2_group
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Faq2ToGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'faq2_to_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_faq2_group', 'id_faq2'], 'required'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_at', 'updated_at'], 'safe'],
            [['id_faq2', 'id_faq2_group', 'created_by', 'updated_by'], 'integer'],
            [['id_faq2'], 'exist', 'skipOnError' => true, 'targetClass' => Faq2::class, 'targetAttribute' => ['id_faq2' => 'id']],
            [['id_faq2_group'], 'exist', 'skipOnError' => true, 'targetClass' => Faq2Group::class, 'targetAttribute' => ['id_faq2_group' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_faq2' => 'faq2',
            'id_faq2_group' => 'faq2_group',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFaq2()
    {
        return $this->hasMany(Faqs::class, ['id' => 'id_faq2']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFaq2Group()
    {
        return $this->hasMany(Faq2Group::class, ['id' => 'id_faq2_group']);
    }
}
