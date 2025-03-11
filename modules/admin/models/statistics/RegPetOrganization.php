<?php

namespace app\modules\admin\models\statistics;

use app\modules\admin\models\Organization;
use yii\db\ActiveQuery;

/**
 * Class RegPetOrganization
 * @package app\modules\admin\models\statistics
 *
 * @property RegPetSpecies[] $species
 * @property RegPetExpireSpecies[] $expireSpecies
 */
class RegPetOrganization extends Organization
{
    /**
     * @return ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasMany(RegPetSpecies::class, ['id_organization' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getExpireSpecies()
    {
        return $this->hasMany(RegPetExpireSpecies::class, ['id_organization' => 'id']);
    }
}
