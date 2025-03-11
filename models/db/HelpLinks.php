<?php


namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "help_links".
 *
 * @property int $id
 * @property string $href URL ссылки
 * @property string $text тескт ссылки
 * @property integer $id_help help
 * @property boolean $target_blank Открывать в новой вкладке
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class HelpLinks extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'help_links';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target_blank'], 'default', 'value' => false],
            [['text'], FullTrimValidator::class],
            [['text', 'id_help', 'target_blank'], 'required'],
            [['text', 'href'], 'string'],
            [['target_blank'], 'boolean'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_at', 'updated_at'], 'safe'],
            [['id_help', 'created_by', 'updated_by'], 'integer'],
            [['id_help'], 'exist', 'skipOnError' => true, 'targetClass' => Help::class, 'targetAttribute' => ['id_help' => 'id']],

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
            'id_help' => 'Справка',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelp()
    {
        return $this->hasOne(Help::class, ['id' => 'id_help']);
    }
}
