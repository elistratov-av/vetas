<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * This is the model class for table "descriptions_templates".
 *
 * @property int $id
 * @property int $id_organization
 * @property int $id_user
 * @property string $caption
 * @property string $template
 * @property string $template_type
 * @property string $param_tech_name
 * @property bool $public
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organizations $organization
 * @property Users $user
 */
class DescriptionsTemplates extends ActiveRecord
{
    /**
     * диагноз
     */
    const TYPE_DIAGNOSIS = 'diagnosis';

    /**
     * рекомендации
     */
    const TYPE_RECOMMENDATION = 'recommendation';

    /**
     * результаты исследований
     */
    const TYPE_TESTRESULT = 'testresult';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'descriptions_templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization', 'id_user', 'caption', 'template', 'template_type'], 'required'],
            [['id_organization', 'id_user', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_organization', 'id_user', 'created_by', 'updated_by'], 'integer'],
            [['public'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['caption', 'template'], 'string', 'max' => 255],
            [['caption', 'template'], FullTrimValidator::class],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['id_user' => 'id']],
            [['template_type'], 'string', 'max' => 50],
            [['template_type'], 'in', 'range' => [
                self::TYPE_DIAGNOSIS, self::TYPE_RECOMMENDATION, self::TYPE_TESTRESULT]
            ],
            [['param_tech_name'], 'string', 'max' => 100],
            [
                'param_tech_name',
                'required',
                'when' => function ($model) {
                    /* @var $model \app\models\db\DescriptionsTemplates */
                    return $model->template_type == self::TYPE_TESTRESULT;
                },
            ],
            /*
             * Для уникальности в рамках организации публичных шаблонов
             */
            [
                ['caption'],
                'unique',
                'targetAttribute' => ['id_organization', 'caption', 'public'],
                'message' => 'В данной организации уже есть публичный шаблон с таким именем',
                'when' => function($model){
                    /* @var $model DescriptionsTemplates */
                    return $model->public == TRUE;
                }
            ],
            /*
             * Для уникальности в рамках списка приватных шаблонов пользователя
             */
            [
                ['caption'],
                'unique',
                'targetAttribute' => ['id_user', 'caption', 'public'],
                'message' => 'У вас уже есть личный шаблон с таким названием',
                'when' => function($model){
                    /* @var $model DescriptionsTemplates */
                    return $model->public == FALSE;
                }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_organization' => 'Id Organization',
            'id_user' => 'Id User',
            'caption' => 'Caption',
            'template' => 'Template',
            'template_type' => 'Template Type',
            'param_tech_name' => 'Param Tech Name',
            'public' => 'Public',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'id_user']);
    }
}
