<?php

namespace app\modules\soap\models\etp\monitoring;

use app\modules\soap\models\Breeds;
use app\modules\soap\models\etp\response\service;
use app\modules\soap\models\etp\response\serviceproperties;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\Species;

trait CustomAttributesTrait
{
    /**
     * @return MosruOrganizations|array|null|\yii\db\ActiveRecord
     */
    public function getOrganization()
    {
        return MosruOrganizations::find()
            ->where(['id' => $this->organization_id])
            ->one();
    }

    /**
     * @return Species|array|null|\yii\db\ActiveRecord
     */
    public function getSpecies()
    {
        return Species::find()
            ->where(['id' => $this->species_id])
            ->one();
    }

    /**
     * @return Breeds|array|null|\yii\db\ActiveRecord
     */
    public function getBreed()
    {
        return Breeds::find()
            ->where(['id' => $this->breed_id])
            ->one();
    }

    /**
     * Возвращаем визит заглушку для мониторинга
     * @return Visit
     */
    public function getVisit()
    {
        $visit = new Visit();
        $visit->start_dttm = $this->visit_date;
        return $visit;
    }

    /**
     * @return serviceproperties
     */
    public function getCustomAttributes()
    {
        /** @var MosruSpecialists $specialist */
        $specialist = $this->specialist;
        $organization = $this->getOrganization();
        $visit = $this->getVisit();

        $serviceProperties = new serviceproperties(null);

        $servicelist = [];
        foreach ($this->services as $service) {
            $serv = new service();
            $serv->service_id = $service->id;
            $serv->name = $service->name;
            $serv->type_value = $service->serviceType->name;
            $servicelist[] = $serv;
        }

        $serviceProperties->servicelist = $servicelist;
        if ($species = $this->getSpecies()) {
            $serviceProperties->species_id = $species->id;
            $serviceProperties->species_name = $species->name;
        }

        if ($breed = $this->getBreed()) {
            $serviceProperties->breed_id = $breed->id;
            $serviceProperties->breed_name = $breed->name;
        }

        $serviceProperties->nickname_animal = $this->nickname_animal ? $this->nickname_animal : '';
        $serviceProperties->chip_animal = $this->chip_animal ? $this->chip_animal : '';
        $serviceProperties->birthdate_animal = $this->birthdate_animal;
        $serviceProperties->sex_animal = $this->sex_animal;

        $serviceProperties->specialist_id = $specialist->id_specialist;
        $serviceProperties->specialist_name = $specialist->name;

        $serviceProperties->org_id = $organization->id;
        $serviceProperties->org_name = $organization->name;
        if ($organization->address) {
            $serviceProperties->org_address_name = $organization->address->name;
            $serviceProperties->org_latitude = $organization->address->latitude;
            $serviceProperties->org_longitude = $organization->address->longitude;
        }

        //'id_contact_type' => 7 - видимо основной телефон организации
        // еще кандидат ид = 18 Круглосуточный многоканальный телефон
        $serviceProperties->org_phone = $organization->getTelephone();

        $serviceProperties->date = $visit->getDate($this->call_to_home);
        $serviceProperties->slot = $visit->getSlot($this->call_to_home);
        $serviceProperties->ticket_number = $visit->ticket_number;
        $serviceProperties->call_to_home = $this->call_to_home;
        $serviceProperties->address_call = $this->address_call;
        return $serviceProperties;
    }
}
