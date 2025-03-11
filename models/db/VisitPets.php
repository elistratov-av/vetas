<?php

namespace app\models\db;

/**
 * Class VisitPets
 * @package app\models\db
 *
 * @property int $id
 * @property int $id_pet
 * @property int $id_visit
 * @property int $id_fias_address
 * @property int $created_by
 * @property int $updated_by
 * @property string  $created_at
 * @property string  $updated_at
 *
 * @property FiasAddresses $fias_address
 */
class VisitPets extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'public.visit_pets';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFias_address()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }
}
