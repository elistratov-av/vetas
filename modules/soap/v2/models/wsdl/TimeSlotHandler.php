<?php

namespace app\modules\soap\v2\models\wsdl;

use app\common\soap\SoapException;
use app\modules\soap\models\MosruServices;
use app\modules\soap\models\wsdl\TimeSlotHandler as TimeSlotHandlerV1;
use app\modules\soap\v2\skeletons\timeslots\TimeSlotDate;
use app\modules\soap\v2\skeletons\timeslots\TimeSlotDates;
use app\modules\soap\v2\skeletons\timeslots\TimeSlotList;
use app\modules\soap\v2\skeletons\timeslots\TimeSlotOrg;
use app\modules\soap\v2\skeletons\timeslots\TimeSlotSlot;
use app\modules\soap\v2\skeletons\timeslots\TimeSlotsResponse;
use yii\validators\DateValidator;

/**
 * Class TimeSlotHandler
 * @package app\modules\soap\v2\models\wsdl
 */
class TimeSlotHandler extends TimeSlotHandlerV1
{
    use HandlerTrait;

    /**
     * @param \app\modules\soap\v2\skeletons\timeslots\TimeSlotRQServicesIdType   $Services
     * @param \app\modules\soap\v2\skeletons\timeslots\TimeSlotRQSpecialistIdType $Specialist
     * @param int                                                                 $OrgId
     * @param \app\modules\soap\v2\skeletons\timeslots\TimeSlotRQDateHoursType    $DateHours
     * @param bool                                                                $CallToHome
     * @return \app\modules\soap\v2\skeletons\timeslots\TimeSlotsResponse
     * @throws \app\common\soap\SoapException
     * @throws \yii\db\Exception
     */
    public static function getTimeSlotList($Services, $Specialist = null, $OrgId = null, $DateHours = null, $CallToHome = false)
    {
        if (empty($Services)) {
            throw new SoapException('Не указаны ID услуг для поиска');
        }
        if (empty($Specialist) || !is_integer($Specialist->Id)) {
            throw new SoapException('Не указаны ID специалиста');
        }

        $user_id = $Specialist->Id;
        $service_ids = (array)$Services->Id;

        $date = $DateHours->Date ?? null;
        $hours_since = $DateHours->HoursSince ?? null;
        $hours_till = $DateHours->HoursTill ?? null;

        self::checkServices($service_ids, $CallToHome);

        if (!empty($DateHours)) { // Запрос с датой
            if (empty($date) || empty($hours_since) || empty($hours_till)) {
                throw new SoapException('Некорректный формат даты');
            }

            $validator = new DateValidator();

            $validator->format = "php:Y-m-d";
            if (!$validator->validate($date)) {
                throw new SoapException('Check date');
            }

            $validator->format = "php:H:i";
            if (!$validator->validate($hours_since) || !$validator->validate($hours_till)) {
                $validator->format = "php:H:i:s";
                if (!$validator->validate($hours_since) || !$validator->validate($hours_till)) {
                    throw new SoapException('Check time');
                } else {
                    $hours_since = substr($hours_since, 0, 5);
                    $hours_till = substr($hours_till, 0, 5);
                }
            }
        }

        $timeSlots = self::getSlots($user_id, $OrgId, $service_ids, $CallToHome, $date, $hours_since, $hours_till);

        $orgs = [];
        foreach ($timeSlots as $id => $organization) {
            $dates = new TimeSlotDates();
            foreach ($organization['dates'] as $date => $slot_list) {
                $slots = new TimeSlotSlot();
                foreach ($slot_list as $time) { // Время
                    $slots->Slot[] = $time;
                }

                $date_item = new TimeSlotDate();
                $date_item->Value = $date;
                $date_item->SlotList = $slots;

                $dates->Date[] = $date_item;
            }

            $org = new TimeSlotOrg();
            $org->OrgId = $id;
            $org->Dates = $dates;

            $orgs[] = $org;
        }

        $orgs = self::convertKeyCase($orgs);

        $TimeSlotList = new TimeSlotList();
        $TimeSlotList->Duration = MosruServices::getServicesDuration($service_ids);
        $TimeSlotList->Org = $orgs;

        $result = new TimeSlotsResponse();
        $result->TimeSlotList = $TimeSlotList;

        return $result;
    }
}
