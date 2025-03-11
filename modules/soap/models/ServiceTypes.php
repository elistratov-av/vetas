<?php

namespace app\modules\soap\models;

/**
 * This is the model class for table "service_types".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $sort_by
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 *
 * @property MosruServices[] $service_list
 */
class ServiceTypes extends \app\models\db\ServiceTypes
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService_list()
    {
        return $this->hasMany(MosruServices::class, ['id_service_type' => 'id']);
    }

}
