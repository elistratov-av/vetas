<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.12.18
 * Time: 16:28
 */

namespace app\modules\admin\models;

use app\models\db\PetOwners;

/**
 * Class Owners
 * @package app\modules\admin\models
 *
 * @property FiasAddress $fiasAddress
 * @property Contacts $contact
 */
class Owners extends PetOwners
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiasAddress() {
        return $this->hasOne(FiasAddress::class, ['id' => 'id_fias_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets() {
        return $this
            ->hasMany(Pets::class, ['id' => 'id_pet'])
            ->viaTable('pets_to_owner', ['id_owner' => 'id']);
    }

    public function getContact()
    {
        return $this->hasOne(Contacts::class, [
                'entity_id' => 'id'
            ])
            ->andWhere(['entity_type' => 'pet_owner'])
            ->andWhere(['<>', 'entity_type', 6]) //не почта
        ;
    }
}
