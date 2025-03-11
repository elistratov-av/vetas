<?php

namespace app\modules\soap\models\etp;

use app\common\models\VisitStatus;
use app\common\validators\VisitEmergencyValidator;
use app\models\MosruNotification;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status1168;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\Visits;
use app\modules\soap\Module;
use app\modules\soap\validators\CallToHomeVisitDateValidator;
use app\modules\soap\validators\VisitDateValidator;
use app\modules\soap\validators\VisitTimeRangeValidator;

/**
 * Класс для обработки статусного сообщения 1068 от ЕТП - "Перенос приема по инициативе пользователя"
 * Пользователь может поменять только дату/время, клинику и врача
 * Данные о животном и владельце не изменяются
 * При вызове на дом так же нельзя сменить адрес
 *
 * Class CoordinateStatusMessage
 * @package app\modules\soap\models\etp
 *
 * @property int $visit_date
 * @property bool $call_to_home
 * @property string|null $address_call
 * @property int $user_id
 * @property mixed $organization_id
 * @property string|null $chip_animal
 * @property string|null $status_id
 *
 * @property MosruSpecialists|array|null|\yii\db\ActiveRecord $specialist
 */
class CoordinateStatusMessage1068 extends CoordinateStatusMessage
{
    /** @var string  */
    protected $errorStatus = Status1168::class;

    /** @var string */
    public $visit_date;

    /** @var bool  */
    public $call_to_home;

    /** @var string|null  */
    public $address_call;

    /** @var int  */
    public $user_id;

    /** @var mixed */
    public $organization_id;

    /** @var string|null  */
    public $chip_animal;

    public function initAttributes() : void
    {
        $this->service_number = $this->data['ServiceNumber'];
        $this->responsible = $this->data['Responsible'];
        $this->department = $this->data['Department'];
        $this->status_id = (isset($this->data['StatusId']) && !empty($this->data['StatusId']))
            ? $this->data['StatusId'] : null;

        $serviceProperties = $this->data['Documents']['ServiceDocument']['CustomAttributes']['ServiceProperties'];

        $this->organization_id = (int)$serviceProperties['org_id'];

        if (!empty($serviceProperties['date']) && !empty($serviceProperties['slot'])) {
            $this->visit_date = $this->getVisitDate($serviceProperties['date'], $serviceProperties['slot']);
        }

        $this->user_id = (int)$serviceProperties['specialist_id'];
        if (!empty($this->user_id) && !empty($this->organization_id)) {
            $specialist = MosruSpecialists::find()
                ->where(['id_user' => $this->user_id])
                ->andWhere(['id_organization' => $this->organization_id])
                ->one();

            if ($specialist) {
                $this->_specialist = $specialist;
            }
        }

        $this->chip_animal = (!empty($serviceProperties['chip_animal'])) ? $serviceProperties['chip_animal'] : null;

        $callToHome = $serviceProperties['call_to_home'];
        // TODO: сделать нормальную xsd которая будет валидировать кастомные поля и отдавать в правильном типе
        // пока хак
        $this->call_to_home = ($callToHome == 'true' || $callToHome == 1) ? true : false;

        $this->address_call = !empty($this->data['Contacts']['BaseDeclarant']['FactAddress']['POBox'])
            ? $this->data['Contacts']['BaseDeclarant']['FactAddress']['POBox']
            : null;
    }

    public function rules()
    {
        return [
            [['service_number', 'department', /*'mobile_phone',*/], 'required'],
            [
                'service_number',
                function($attribute, $params, $validator) {
                    if (!$visit = $this->getVisit()) {
                        $this->addError($attribute, 'Не найден прием по переданному ServiceNumber');
                        return;
                    }

                    $time = ($this->call_to_home) ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;
                    $error = ($this->call_to_home)
                        ? "Изменение времени вызова на дом возможно за 6 часов до начала"
                        : "Изменение времени начала приема возвожно за 2 часа до начала";

                    if (strtotime($visit->start_dttm) < (time() + $time)) {
                        $this->addError($error);
                        return;
                    }

                    if ($visit->status == VisitStatus::CANCELED) {
                        $this->addError($attribute, 'Нельзя переносить прием в статусе "Отменен"');
                        return;
                    }

                    if ($visit->status == VisitStatus::FINISHED) {
                        $this->addError($attribute, 'Нельзя переносить прием в статусе "Завершен"');
                        return;
                    }

                    if ($visit->status == VisitStatus::IN_WORK) {
                        $this->addError($attribute, 'Нельзя переносить прием в статусе "В работе"');
                        return;
                    }
                }
            ],
            [['organization_id', 'user_id'], 'integer'],
            [['call_to_home'], 'boolean'],
            ['call_to_home', function($attribute, $params, $validator){
                $visit = $this->getVisit();
                if (!empty($visit)) {
                    if (($this->call_to_home == true && $visit->type != \app\models\db\Visits::TYPE_AT_HOME) ||
                        ($this->call_to_home == false && $visit->type != \app\models\db\Visits::TYPE_VISIT)
                    ) {
                        $this->addError($attribute, 'Изменения типа записи(В клинике/На дом) не допустимо');
                        return;
                    }
                }
            }],
            ['organization_id', 'exist', 'skipOnError' => true, 'targetClass' => MosruOrganizations::class, 'targetAttribute' => 'id'],
            ['visit_date', VisitDateValidator::class, 'when' => function($model) {
                return $model->call_to_home === false;
            }, 'message' => "Изменение времени приема в клинике возможно за 2 часа до начала"],
            ['visit_date', CallToHomeVisitDateValidator::class, 'when' => function($model) {
                return $model->call_to_home === true;
            }, 'message' => "Изменение времени вызова врача на дом возможно за 6 часа до начала"],
            [
                'visit_date',
                VisitEmergencyValidator::class,
                'when' => function($model) {
                    return $model->call_to_home === false;
                },
                'organization_id' => $this->organization_id,
                'message' => 'Невозможно осуществить запись на данное время в связи с экстренной ситуацией в клинике'
            ],
            [
                'visit_date',
                VisitTimeRangeValidator::class,
                'callToHome' => $this->call_to_home,
                'services' => $this->visit->services,
                'specialist' => $this->specialist,
                'when' => function($model) {
                    return $model->specialist instanceof MosruSpecialists;
                }
            ],
            ['address_call', 'required', 'when' => function(){
                return $this->call_to_home === true;
            }]
        ];
    }

    /**
     * Обработка входящего сообщения
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            $this->moveVisit();
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }

    /**
     * Перенос визита
     * Посылаем в очередь статус 1053 после успешного изменения записи, 1168 - в случае невозможности перенести
     */
    protected function moveVisit() : void
    {
        $visit = $this->getVisit();
        $visit->id_organization = $this->organization_id;
        $visit->start_dttm = $this->visit_date;
        $visit->status = VisitStatus::CHANGED;

        $visit->call_to_home = $this->call_to_home;
        $visit->setServices($visit->services); //чтобы правильно определить время
        $visit->setSpecialist($this->specialist);

        if (!$visit->save()) {
            $this->sendErrorMessage('Не удалось осуществить запись в БД');
        }

        MosruNotification::visitChangeInMosru($this->visit->id, $this->getStatusId());
    }

}
