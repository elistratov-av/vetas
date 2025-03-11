<?php

namespace app\modules\soap\models\etp\monitoring;

use app\common\validators\VisitEmergencyValidator;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\validators\CallToHomeVisitDateValidator;
use app\modules\soap\validators\VisitDateValidator;
use app\modules\soap\validators\VisitTimeRangeValidator;

/**
 * Класс для обработки статусного сообщения 1068 от ЕТП - "Перенос приема по инициативе пользователя" посланного от
 * мониторинга mos.ru
 * Считаем что сообщение послано от мониторинга если параметр SsoId в BaseDeclarant соответствует значению указанному
 * в параметре $params['monitoring']['SsoId'] в конфиге модуля
 *
 * Class CoordinateStatusMessage
 * @package app\modules\soap\models\etp
 *
 */
class CoordinateStatusMessage1068 extends \app\modules\soap\models\etp\CoordinateStatusMessage1068
{
    use CustomAttributesTrait;

    /** @var string|null  */
    public $chip_animal;

    /** @var integer|null  */
    public $breed_id;

    /** @var integer|null  */
    public $species_id;

    /** @var  string|null */
    public $nickname_animal;

    /** @var string|null */
    public $sex_animal;

    /** @var string|null */
    public $birthdate_animal;

    /** @var array */
    public $service_id;

    public function rules()
    {
        return [
            [['service_number', 'department', /*'mobile_phone',*/], 'required'],
            [['organization_id', 'user_id'], 'integer'],
            [['call_to_home'], 'boolean'],
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
                'services' => $this->getServices(),
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

    public function initAttributes() : void
    {
        parent::initAttributes();

        // информацию по животному и услугам в родительско классе определяется через созданные ранее прием
        // для мониторинга извлекаем из данных сообщения
        $serviceProperties = $this->data['Documents']['ServiceDocument']['CustomAttributes']['ServiceProperties'];
        $this->chip_animal = (!empty($serviceProperties['chip_animal'])) ? $serviceProperties['chip_animal'] : null;

        $this->species_id = $serviceProperties['species_id'] ? $serviceProperties['species_id'] : null;
        $this->breed_id = !empty($serviceProperties['breed_id']) ? $serviceProperties['breed_id'] : null;
        $this->nickname_animal = !empty($serviceProperties['nickname_animal']) ? $serviceProperties['nickname_animal'] : null;
        $this->sex_animal = (isset($serviceProperties['sex_animal'])) ? $serviceProperties['sex_animal'] : null;
        $this->birthdate_animal = !empty($serviceProperties['birthdate_animal']) ? $serviceProperties['birthdate_animal'] : null;

        $this->service_id = [];
        if (isset($serviceProperties['servicelist']['service']['service_id'])) {
            $this->service_id[] = $serviceProperties['servicelist']['service']['service_id'];
        } else {
            foreach($serviceProperties['servicelist']['service'] as $r) {
                $this->service_id[] = $r['service_id'];
            }
        }
    }

    /**
     * Обработка входящего сообщения
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            /** @var ETP $etp */
            $etp = \Yii::$app->getModule('soap')->etp;
            $etp->sendStatusMessage(new Status1053(), $this);
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }
}
