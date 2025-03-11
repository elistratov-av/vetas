<?php

namespace app\modules\soap\models\etp\response;

use app\modules\soap\models\etp\CoordinateMessage;
use app\modules\soap\models\etp\CoordinateMessageInterface;
use app\modules\soap\models\etp\CoordinateStatusMessage1068;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\Visits;

class serviceproperties
{
    /** @var  service[] */
    public $servicelist;

    /** @var  integer */
    public $species_id;

    /** @var  string */
    public $species_name;

    /** @var  integer */
    public $breed_id;

    /** @var  string */
    public $breed_name;

    /** @var  string */
    public $nickname_animal;

    /** @var  string|null */
    public $chip_animal;

    /** @var  string */
    public $birthdate_animal;

    /** @var  integer */
    public $sex_animal;

    /** @var  integer */
    public $specialist_id;

    /** @var  string */
    public $specialist_name;

    /** @var  integer */
    public $org_id;

    /** @var  string */
    public $org_name;

    /** @var  string */
    public $org_address_name;

    /** @var  float */
    public $org_latitude;

    /** @var  float */
    public $org_longitude;

    /** @var  string */
    public $org_phone;

    /** @var  string */
    public $date;

    /** @var  string */
    public $slot;

    /** @var  string */
    public $ticket_number;

    /** @var  boolean */
    public $call_to_home;

    /** @var  string */
    public $address_call;

    /** @var string */
    public $pet_id;

    protected $visit;

    /**
     * serviceproperties constructor.
     * @param CoordinateMessageInterface|CoordinateMessage|CoordinateStatusMessage1068|null $message
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(?CoordinateMessageInterface $message)
    {
        if (is_null($message)) {
            return;
        }

        /** @var Visits $visit */
        if (!$visit = $message->getVisit()) {
            return;
        }
        $pet = $visit->pet;
        $organization = $visit->organization;

        $servicelist = [];
        foreach ($visit->services as $service) {
            $serv = new service();
            $serv->service_id = $service->id;
            $serv->name = $service->name;
            $serv->type_value = $service->serviceType->name;
            $servicelist[] = $serv;
        }

        $this->servicelist = $servicelist;
        $this->species_id = $pet->species->id;
        $this->species_name = $pet->species->name;
        $this->breed_id = $pet->breed ? $pet->breed->id : '';
        $this->breed_name = $pet->breed ? $pet->breed->name : '';
        $this->nickname_animal = $pet->name ? $pet->name : '';
        $this->chip_animal = $message->chip_animal ? $message->chip_animal : '';
        $this->birthdate_animal = $pet->birthday ? \Yii::$app->formatter->asDatetime($pet->birthday) : '';
        if ($pet->sex) {
            $this->sex_animal = ($pet->sex == 'f') ? ETP::SEX_ANIMAL_FEMALE : ETP::SEX_ANIMAL_MALE;
        }
        if (!empty($visit->specialists) && isset($visit->specialists[0])) {
            $this->specialist_id = $visit->specialists[0]->user->id;
            $this->specialist_name = $visit->specialists[0]->user->fullname;
        }

        $this->org_id = $organization->id;
        $this->org_name = $organization->name;
        if ($organization->address) {
            $this->org_address_name = $organization->address->name;
            $this->org_latitude = $organization->address->latitude;
            $this->org_longitude = $organization->address->longitude;
        }
        //'id_contact_type' => 7 - видимо основной телефон организации
        // еще кандидат ид = 18 Круглосуточный многоканальный телефон
        $this->org_phone = $organization->getTelephone();

        $this->date = $visit->getDate($message->call_to_home);
        $this->slot = $visit->getSlot($message->call_to_home);
        $this->ticket_number = $visit->ticket_number;
        $this->call_to_home = $message->call_to_home;
        $this->address_call = $message->address_call;
        $this->pet_id = (isset($message->pet_id)) ? $message->pet_id : '';
    }
}
