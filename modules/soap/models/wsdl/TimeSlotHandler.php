<?php

namespace app\modules\soap\models\wsdl;

use app\common\soap\SoapException;
use app\modules\soap\models\MosruServices;
use app\modules\soap\models\Visits;
use app\modules\soap\skeletons\timeslots\TimeSlotDate;
use app\modules\soap\skeletons\timeslots\TimeSlotDates;
use app\modules\soap\skeletons\timeslots\TimeSlotList;
use app\modules\soap\skeletons\timeslots\TimeSlotOrg;
use app\modules\soap\skeletons\timeslots\TimeSlotRQ;
use app\modules\soap\skeletons\timeslots\TimeSlotSlot;
use app\modules\soap\skeletons\timeslots\TimeSlotsResponse;
use app\modules\soap\models\etp\ETPHelper;
use yii\db\Expression;
use yii\db\Query;
use yii\validators\DateValidator;

/**
 * Class TimeSlotHandler
 * @package app\modules\soap\models\wsdl
 */
class TimeSlotHandler
{
    /**
     * @param TimeSlotRQ $get_time_slot_list
     * @return TimeSlotsResponse
     * @throws SoapException
     * @throws \yii\db\Exception
     */
    public static function getTimeSlotList($get_time_slot_list)
    {
        if (empty($get_time_slot_list->services)) {
            throw new SoapException('Не указаны ID услуг для поиска');
        }
        if (!is_integer($get_time_slot_list->specialist->id)) {
            throw new SoapException('Не указаны ID специалиста');
        }

        $user_id = $get_time_slot_list->specialist->id;
        $service_ids = (array)$get_time_slot_list->services->id;
        $call_to_home = (boolean)$get_time_slot_list->call_to_home;
        $org_id = $get_time_slot_list->org_id ?? null;
        $date = $get_time_slot_list->date_hours->date ?? null;
        $hours_since = $get_time_slot_list->date_hours->hours_since ?? null;
        $hours_till = $get_time_slot_list->date_hours->hours_till ?? null;

        self::checkServices($service_ids, $call_to_home);

        if (!empty($get_time_slot_list->date_hours)) { // Запрос с датой
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
                throw new SoapException('Check time');
            }
        }

        $timeSlots = self::getSlots($user_id, $org_id, $service_ids, $call_to_home, $date, $hours_since, $hours_till);

        $orgs = [];
        foreach ($timeSlots as $id => $organization) {
            $dates = new TimeSlotDates();
            foreach ($organization['dates'] as $date => $slot_list) {
                $slots = new TimeSlotSlot();
                foreach ($slot_list as $time) { // Время
                    $slots->slot[] = $time;
                }

                $date_item = new TimeSlotDate();
                $date_item->value = $date;
                $date_item->slot_list = $slots;

                $dates->date[] = $date_item;
            }

            $org = new TimeSlotOrg();
            $org->org_id = $id;
            $org->dates = $dates;

            $orgs[] = $org;
        }

        $result = new TimeSlotsResponse();
        $result->get_time_slot_list = new TimeSlotList();
        $result->get_time_slot_list->duration = MosruServices::getServicesDuration($service_ids);
        $result->get_time_slot_list->org = $orgs;

        return $result;
    }

    /**
     * Выдает доступные для записи слоты
     * @param int    $user_id
     * @param int    $org_id
     * @param array  $service_ids
     * @param bool   $call_to_home
     * @param string $date
     * @param string $hours_since
     * @param string $hours_till
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getSlots(
        $user_id,
        $org_id,
        $service_ids,
        $call_to_home = false,
        $date = null,
        $hours_since = null,
        $hours_till = null
    )
    {
        // Date check
        if (!empty($date) || !empty($hours_since) || !empty($hours_till)) {
            if ($date == date('Y-m-d')) {
                list($hours_since, $hours_till) = ETPHelper::prepareDate(
                    $call_to_home, $date, $hours_since, $hours_till
                );
            }
        }

        $interval = ($call_to_home) ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;
        if (!empty($date) && !empty($hours_since) && !empty($hours_till)) {
            $date_from = trim($date) . ' ' . trim($hours_since) . ':00';
            $date_end = trim($date) . ' ' . trim($hours_till) . ':00';
        } else {
            $date_from = date('Y-m-d H:i:s', time() + $interval);
            $date_end = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 30 дней;
        }

        $start = (new \DateTime())->add(new \DateInterval("PT{$interval}S"));
        $startDate = new \DateTime($date_from);
        if ($start > $startDate) {
            $date_from = $start->format('Y-m-d H:i:s');
        }

        $query = (new Query())
            ->select(new Expression(1))
            ->from('services_specialists')
            ->where(['id_specialist' => new Expression('t.id_specialist')])
            ->andWhere(['id_organization' => new Expression('t.id_organization')]);

        if (!empty($org_id)) {
            $query->andWhere(['id_organization' => $org_id]);
        }

        $query->andWhere(['id_service' => $service_ids])
            ->having('count(*) = :count', [':count' => count($service_ids)]);

        $service_ids_all='';
        for($i=0; $i<count($service_ids); $i++){
            if($service_ids_all){$service_ids_all.=",";}
            $service_ids_all.=$service_ids[$i];
        }

        $sql = '';
        $bef=($call_to_home) ? Visits::CALL_TO_HOME_BEFORE_TIME : 0;
        $aff=($call_to_home) ? Visits::CALL_TO_HOME_AFTER_TIME : MosruServices::getServicesCooldown($service_ids);
        $dur=MosruServices::getServicesDuration($service_ids);
        if($org_id){$org=$org_id;}else{$org='NULL';}

        if($call_to_home){
            $sql="select * from mosru.get_user_timesheets_call_to_home_v2(".$user_id.",".$org.",'".$service_ids_all."','".$date_from."'::timestamp,'".$date_end."'::timestamp, ".$bef.",".$aff.",".$dur.")";
        }else{
            $sql="select * from mosru.get_user_timesheets_v1(".$user_id.",".$org.",'".$service_ids_all."','".$date_from."'::timestamp,'".$date_end."'::timestamp, ".$bef.",".$aff.",".$dur.")";
        }
        $command = \Yii::$app->db->createCommand($sql);
        $slots = $command->queryAll();

        $result = [];
        foreach ($slots as $slot) {
            if (!isset($result[$slot['id_organization']])) {
                $result[$slot['id_organization']] = [
                    'dates' => [],
                ];
            }

            if (!isset($result[$slot['id_organization']]['dates'][$slot['date']])) {
                $result[$slot['id_organization']]['dates'][$slot['date']] = [];
            }
            $result[$slot['id_organization']]['dates'][$slot['date']][] = $slot['time'];
        }

        return $result;
    }

    /**
     * @param array $service_ids
     * @param bool  $call_to_home
     * @throws SoapException
     */
    protected static function checkServices(array $service_ids, bool $call_to_home)
    {
        $check = MosruServices::find()
            ->select([
                new Expression("count(id) = :count_id", [':count_id' => count($service_ids)]),
            ])
            ->where(['IN', 'id', $service_ids])
            ->scalar();

        if (!$check) {
            throw new SoapException('Check service_ids');
        }

        // По умолчанию все услуги доступны в клинике, такой случай не проверяем
        if ($call_to_home) {
            /** @var MosruServices[] $services */
            $services = MosruServices::find()
                ->where(['id' => $service_ids])
                ->andWhere(['at_home' => false])
                ->all();

            if (!empty($services)) {
                $msg = "Следующие услуги не доступны для вызова на дом:";
                foreach ($services as $service) {
                    $msg .= "\n#{$service->id} {$service->name}";
                }
                throw new SoapException($msg);
            }
        }
    }
}
