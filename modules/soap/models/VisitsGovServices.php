<?php

namespace app\modules\soap\models;

/**
 * This is the model class for table "visits_gov_services".
 *
 * @property int $id_visit Ссылка на запись приема
 * @property int $id_service Ссылка на услугу
 * @property int $count
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 * @property int $id
 *
 * @property MosruServices $service
 * @property Visits $visit
 */
class VisitsGovServices extends \app\models\db\VisitsGovServices
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(MosruServices::class, ['id' => 'id_service']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit']);
    }
}
