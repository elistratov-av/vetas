<?php

namespace app\models\db;

use yii\db\ActiveQuery;

/**
 * Class Specialist2VaccinationStation
 *
 * @property int                      $id
 * @property int                      $specialist_id
 * @property int                      $vaccination_station_id
 * @property int                      $organization_id
 *
 * @property-read  Organizations      $organization
 * @property-read  Specialists        $specialist
 * @property-read  VaccinationStation $vaccinationStation
 *
 * @package app\models\db
 */
class Specialist2VaccinationStation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'public.specialist2vaccination_station';
    }

    public function getOrganization(): ActiveQuery
    {
        return $this->hasOne(Organizations::class, ['id' => 'organization_id']);
    }

    public function getSpecialist(): ActiveQuery
    {
        return $this->hasOne(Specialists::class, ['id' => 'specialist_id']);
    }

    public function getVaccinationStation(): ActiveQuery
    {
        return $this->hasOne(Organizations::class, ['id' => 'vaccination_station_id']);
    }
}
