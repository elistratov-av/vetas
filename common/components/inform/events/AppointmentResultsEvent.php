<?php

namespace app\common\components\inform\events;

use app\models\db\Visits;

class AppointmentResultsEvent extends SubscriptionEvent
{
    // Ограничение на стороне ИСПК
    const ADMISSION_NUMBER_LIMIT = 5;

    const EVENT_CODE = 'appointment_results';

    /** @var Visits */
    public $visit;

    public function getEventData($token): array
    {
        $io = $this->visit->owner->i_fio.' '.$this->visit->owner->o_fio;
        $startDatetime = new \DateTime($this->visit->fact_start_dttm);
        $endDatetime = new \DateTime($this->visit->fact_end_dttm);
        $specialist = $this->visit->getVisitsSpecialists()->all()[0]->idSpecialist;
        // У телевета должна быть 1 услуга
        $service = $this->visit->services[0];
        // Возможно надо будет выводить это в info, уточним
//        foreach($this->visit->visitDescriptions) {
//        }

        $eventData = [
            'io' => $io,
            'vetclinic_name' => $this->visit->organization->name,
            'start_date' => $startDatetime->format('d.m.Y'),
            'start_time' => $startDatetime->format('H:i'),
            'start_vet_name' => $specialist->getFullnameInitials(),
            'end_date' => $endDatetime->format('d.m.Y'),
            'end_time' => $endDatetime->format('H:i'),
            'end_vet_name' => $specialist->getFullnameInitials(),
            'service_name' => $service->name,
            'info' => $this->visit->description,
        ];

        $admissionNumber = 1;
        $admissionEventData = [];
        foreach ($this->visit->child_visits as $admission) {
            if ($admissionNumber > self::ADMISSION_NUMBER_LIMIT) break;

            $admission_service = '';
            $i = 1;
            foreach($admission->services as $service) {
                $admission_service .= $service->name;
                if ($i < count($admission->services)) {
                    $admission_service .= ', ';
                }
                $i++;
            }

            $admissionStartDatetime = new \DateTime($admission->start_dttm);
            $admissionEventData['admission_service'.$admissionNumber] = $admission_service;
            $admissionEventData['admission_vetclinic'.$admissionNumber] = $admission->organization->name;
            $admissionEventData['admission_specialist'.$admissionNumber] = $admission->getVisitsSpecialists()->all()[0]->idSpecialist->getFullnameInitials();
            $admissionEventData['admission_date'.$admissionNumber] = $admissionStartDatetime->format('d.m.Y');
            $admissionEventData['admission_time'.$admissionNumber] = $admissionStartDatetime->format('H:i');

            $admissionNumber++;
        }

        return array_merge($eventData, $admissionEventData);
    }
}
