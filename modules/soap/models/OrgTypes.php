<?php

namespace app\modules\soap\models;

/**
 * This is the model class for table "org_types".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 *
 * @property MosruOrganizations[] $organizations
 */
class OrgTypes extends \app\models\db\OrgTypes
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizations()
    {
        return $this->hasMany(MosruOrganizations::class, ['id_org_type' => 'id']);
    }
}
