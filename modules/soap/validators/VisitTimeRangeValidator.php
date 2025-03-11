<?php

namespace app\modules\soap\validators;

use app\modules\soap\models\etp\CoordinateStatusMessage;
use app\modules\soap\models\MosruServices;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\Visits;
use yii\validators\Validator;

/**
 * Приверка доступности времени приема для записи
 *
 * В случае записи в клинике, проверяем доступность промежутка времени:
 * время начала = времени начала указанному в запросе
 * время окончания = время начала + сумма продолжительностей и перерывов всех услуг указанных в запроса
 *
 *
 * В случае вызова на дом, ко времени визита добавляется + 2 часа (час до и час после)
 * Временные рамки приема соответственно сдвигаются на час до времени записи.
 * время начала = времени начала указанному в запросе - Visits::CALL_TO_HOME_BEFORE_TIME(1 час)
 * время окончания = время начала + сумма продолжительностей всех услуг указанных в запроса + Visits::CALL_TO_HOME_AFTER_TIME(1 час)
 * !!!В случае вызова на дом, перерыв после услуг не учитыаются!!!
 *
 * Проверка доступности слотов осуществляется согласно описанному выше
 *
 * Class VisitDateValidator
 * @package app\common\validators
 */
class VisitTimeRangeValidator extends Validator
{
    /** @var MosruServices[] */
    public $services;

    /** @var MosruSpecialists */
    public $specialist;

    /** @var  boolean */
    public $callToHome;

    /**
     * @param CoordinateStatusMessage $model
     * @param string $attribute
     * @throws \yii\db\Exception
     */
    public function validateAttribute($model, $attribute)
    {
        $visit_date = $model->$attribute;
        $duration = $cooldown = 0;
        foreach ($this->services as $service) {
            $duration += $service->duration;
            $cooldown += $service->cooldown;
        }

        if ($this->callToHome) {
            $minutes = Visits::CALL_TO_HOME_BEFORE_TIME;
            $interval = new \DateInterval("PT{$minutes}M");
            $interval->invert = 1;
            $from = (new \DateTime($visit_date))
                ->add($interval)
                ->format('Y-m-d H:i:s');

            $minutes = $duration + Visits::CALL_TO_HOME_AFTER_TIME;
            $interval = $duration + Visits::CALL_TO_HOME_BEFORE_TIME + Visits::CALL_TO_HOME_AFTER_TIME;
            $to = (new \DateTime($visit_date))
                ->add(new \DateInterval("PT{$minutes}M"))
                ->format('Y-m-d H:i:s');

            $message = "Запрашиваемый промежуток времени {$from} - {$to} недоступен для вызова врача на дом";
        } else {
            $interval = $duration + $cooldown;
            $from = $visit_date;
            $data = new \DateTime($from);
            $data->add(new \DateInterval("PT{$interval}M"));
            $to = $data->format('Y-m-d H:i:s');
            $message = "Запрашиваемый промежуток времени {$from} - {$to} недоступен для записи в клинике";
        }

//         $sql = <<<SQL
// select 
//     1 
// from (
//     select 
//         slots.*,
//         lead(upper(slots.slot_range), :countSlots-1) over w - slot as duration
//     from mosru.get_slots(:id_user, :call_to_home) as slots
//     where slots.id_organization = :id_organization
//           and slots.slot between (:date_from::timestamp) and (:date_to::timestamp)
//     window w as (partition by slots.slots_number)
//     order by slots.slot
// ) as t
// where 
//     t.duration = make_interval(mins => :visitDuration)
//     and lower(t.slot_range) = :slot_date
// SQL;
//         $command = \Yii::$app->db->createCommand($sql, [
//             'call_to_home' => $this->callToHome,
//             'date_from' => $from,
//             'slot_date' => $from,
//             'date_to' => $to,
//             'visitDuration' => $interval,
//             'countSlots' => $interval/10,
//             'id_user' => $this->specialist->id_user,
//             'id_organization' => $this->specialist->id_organization
//         ]);
        
        $sql = '';
        if($this->callToHome){
            $sql="select * from mosru.check_timesheet_slot_call_to_home(".$this->specialist->id_user.",".$this->specialist->id_organization.",'".$from."'::timestamp,'".$to."'::timestamp, ".$interval.",'".$from."'::timestamp,".($interval/10).")";
        }else{
            $sql="select * from mosru.check_timesheet_slot(".$this->specialist->id_user.",".$this->specialist->id_organization.",'".$from."'::timestamp,'".$to."'::timestamp, ".$interval.",'".$from."'::timestamp,".($interval/10).")";
        }
        $command = \Yii::$app->db->createCommand($sql);

        if (!(bool)$command->queryScalar()) {
            $this->addError($model, $attribute, $message);
        }
    }

}
