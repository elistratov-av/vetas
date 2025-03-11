<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.12.18
 * Time: 18:36
 */

namespace app\modules\admin\models;

/**
 * Class Pets
 * @package app\modules\admin\models
 *
 * @property PetIdentification $mainIdentification
 * @property Owners $mainOwner
 */
class Pets extends \app\models\db\Pets
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwners()
    {
        return $this->hasMany(Owners::class, ['id' => 'id_owner'])->viaTable('pets_to_owner', ['id_pet' => 'id'])
            ->joinWith('petsToOwners pown', true)->where(['pown.id_owner_type' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainOwner()
    {
        return $this->hasOne(Owners::class, ['id' => 'id_owner'])->viaTable('pets_to_owner', ['id_pet' => 'id'])
            ->joinWith('petsToOwners pown', true)->where(['pown.id_owner_type' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainIdentification()
    {
        return $this->hasOne(PetIdentification::class, ['id_pet' => 'id'])->andWhere(['pet_identification.main_flag' => true]);
    }
}
