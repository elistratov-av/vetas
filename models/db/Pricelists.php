<?php

namespace app\models\db;

use Yii;

/**
 * This is the model class for table "pricelists".
 *
 * @property int $id
 * @property int $id_organization Связь с организацией
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 *
 * @property Organizations $organization
 * @property GovServices[] $services
 */
class Pricelists extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pricelists';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_organization'], 'required'],
            [['id_organization', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_organization', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_organization'], 'exist', 'skipOnError' => true, 'targetClass' => Organizations::className(), 'targetAttribute' => ['id_organization' => 'id']],
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
        return $this->hasOne(Organizations::className(), ['id' => 'id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(GovServices::class, ['id_pricelist' => 'id']);
    }
}
