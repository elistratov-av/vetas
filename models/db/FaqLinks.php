<?php


namespace app\models\db;

/**
 * This is the model class for table "faq".
 *
 * @property int $id
 * @property string $href URL ссылки
 * @property string $text тескт ссылки
 * @property integer $id_faq faq
 * @property boolean $target_blank Открывать в новой вкладке
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class FaqLinks extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'faq_links';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target_blank'], 'default', 'value' => false],
            [['text', 'id_faq', 'target_blank'], 'required'],
            [['text', 'href'], 'string'],
            [['target_blank'], 'boolean'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_at', 'updated_at'], 'safe'],
            [['id_faq', 'created_by', 'updated_by'], 'integer'],
            [['id_faq'], 'exist', 'skipOnError' => true, 'targetClass' => Faqs::class, 'targetAttribute' => ['id_faq' => 'id']],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'href' => 'URL ссылки',
            'text' => 'тескт ссылки',
            'id_faq' => 'faq',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFaq(){
        $this->hasOne(Faqs::class, ['id' => 'id_faq']);
    }
}
