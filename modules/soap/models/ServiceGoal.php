<?php

namespace app\modules\soap\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "service_goal".
 *
 * @property int $id id
 * @property string $name Наименование
 * @property int $sort_by Сортировка
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 *
 * @property MosruServices[] $mosruServices
 */
class ServiceGoal extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service_goal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['sort_by', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['sort_by', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
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
            'sort_by' => 'Sort By',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMosruServices()
    {
        return $this->hasMany(MosruServices::class, ['id_service_goal' => 'id']);
    }

    public function getServices_types_list()
    {
        return $this->hasMany(ServiceTypes::class, ['id' => 'id_service_type'])
            ->viaTable('mosru.services', ['id_service_goal' => 'id']);
    }
}
