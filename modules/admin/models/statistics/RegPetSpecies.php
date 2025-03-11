<?php

namespace app\modules\admin\models\statistics;

use app\modules\admin\models\Pets;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class RegPetSpecies
 * @package app\modules\admin\models\statistics
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $flag_mos_ru
 * @property integer $id_organization
 *
 * @property Pets[] $pets
 */
class RegPetSpecies extends ActiveRecord
{
    public static function tableName()
    {
        return 'statistic.pet_registrations_species';
    }

    /**
     * @return ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class, ['id' => 'id'])
            ->viaTable('statistic.pet_registrations', [
                'id_organization' => 'id_organization',
                'id_species' => 'id'
            ])
        ;
    }
}
