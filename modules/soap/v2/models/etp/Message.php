<?php

namespace app\modules\soap\v2\models\etp;

use yii\base\DynamicModel;
use yii\base\Model;
use yii\helpers\Json;

use app\common\soap\XmlFormatter;
use app\models\db\MosRuServices;
use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\Visits;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\v2\queue\MosruStatusSender;
use app\modules\soap\validators\VisitTimeRangeValidator;
use app\modules\soap\log as SoapLog;

/**
 * Class Message
 * @package app\modules\soap\v2\models\etp
 */
abstract class Message extends Model implements SoapLog\LoggerAwareInterface
{
    use SoapLog\LoggerAwareTrait;
    
    /**
     * @var array
     */
    protected $requestData;
    /**
     * @var \app\modules\soap\models\Visits
     */
    protected $visit;

    /**
     * @param array|\stdClass $requestData
     */
    public function setRequestData($requestData)
    {
        if ($requestData instanceof \stdClass) {
            $requestData = Json::decode(Json::encode($requestData));
        }
        if (is_array($requestData)) {
            $this->requestData = $requestData;
        }
    }

    /**
     * @param \app\modules\soap\models\etp\status\StatusInterface $status
     * @param string|null                                         $status_id
     * @param string[]                                            $headers
     * @return string
     */
    public function makeResponseData(StatusInterface $status, ?string $status_id, array $headers = [])
    {
        $response = new StatusResponse($status, $this);
        $data = $response->prepareResponse($status_id);

        $formatter = new XmlFormatter();
        $formatter->itemTag = 'Service';

        $str = $formatter->makeXml(
            'putStatusRequest',
            'http://vetas.mos.ru',
            [
                // 'xmlns:ns1' => 'http://vetas.mos.ru',
                // 'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance'
            ],
            ['ApplicationStatusData' => $data]
        );

        $parts = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">'
        ];
        if (count($headers)) {
            $parts[] = '<SOAP-ENV:Header>';
            foreach ($headers as $name => $value) {
                $parts[] = "<$name>$value</$name>";
            }
            $parts[] = '</SOAP-ENV:Header>';
        } else {
            $parts[] = '<SOAP-ENV:Header/>';
        }
        $parts[] = '<SOAP-ENV:Body>';
        $parts[] = $str;
        $parts[] = '</SOAP-ENV:Body>';
        $parts[] = '</SOAP-ENV:Envelope>';

        return implode('', $parts);
    }

    /**
     * @return \app\modules\soap\models\Visits
     */
    public function getVisit()
    {
        if ($this->visit === null) {
            $id = ETPMessage::find()
                ->select('visit_id')
                ->where(['service_number' => $this->ServiceNumber])
                ->andWhere(['!=', 'visit_id', 0])
                ->andWhere(['not', ['visit_id' => null]])
                ->orderBy(['id' => SORT_DESC])
                ->scalar();

            if (!empty($id)) {
                $this->visit = Visits::find()
                    ->where(['id' => $id])
                    ->one();
            }
        }

        return $this->visit;
    }

    /**
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     */
    protected function successResponse()
    {
        $response = new InstantResponse();
        $response->Status = 'OK';

        return $response;
    }

    /**
     * @param string $errorMessage
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     */
    protected function errorResponse(string $errorMessage)
    {
        $response = new InstantResponse();
        $response->Status = 'Error';
        $response->Note = $errorMessage;

        return $response;
    }

    /**
     * @param array $errors
     * @return string
     */
    protected function formatErrors(array $errors)
    {
        return implode("\r\n", $errors);
    }

    /**
     * @return \app\modules\soap\v2\queue\MosruStatusSender
     */
    protected function getSendStatusService()
    {
        return new MosruStatusSender();
    }

    /**
     * @param string $date
     * @param string $slot
     * @return string
     * @throws \Exception
     */
    protected function getVisitDate(string $date, string $slot)
    {
        $dateObj = new \DateTime($date);
        list($hour, $minute) = explode(':', $slot);
        $dateObj->setTimezone(new \DateTimeZone('Europe/Moscow'));
        $dateObj->setTime($hour, $minute);

        return $dateObj->format('Y-m-d H:i:s');
    }

    /**
     * @return MosruOrganizations|null
     */
    public function getOrganization()
    {
        if (empty($this->ServiceProperties->OrgId)) {
            return null;
        }

        return MosruOrganizations::find()
            ->where(['id' => $this->ServiceProperties->OrgId])
            ->one();
    }

    /**
     * @return \app\modules\soap\models\MosruSpecialists|null
     */
    public function getSpecialist()
    {
        if (empty($this->ServiceProperties->SpecialistId) || empty($this->ServiceProperties->OrgId)) {
            return null;
        }

        return MosruSpecialists::find()
            ->where(['id_user' => (int)$this->ServiceProperties->SpecialistId])
            ->andWhere(['id_organization' => (int)$this->ServiceProperties->OrgId])
            ->one();
    }

    /**
     * @return array|\app\models\db\MosRuServices[]
     */
    public function getServices()
    {
        $serviceIds = $this->getServiceIds();

        return (empty($serviceIds) || empty($this->ServiceProperties->SpeciesId))
            ? []
            : MosRuServices::find()
                ->join(
                    'INNER JOIN',
                    'species_services',
                    'species_services.id_service = mosru.services.id and species_services.id_species = :id_species',
                    [
                        'id_species' => $this->ServiceProperties->SpeciesId
                    ]
                )
                ->andWhere(['mosru.services.id' => $serviceIds])
                ->all();
    }

    /**
     * @return array
     */
    protected function getServiceIds()
    {
        $result = [];

        if (!empty($this->ServiceProperties->ServiceList)) {
            foreach ($this->ServiceProperties->ServiceList as $service) {
                $result[] = $service->ServiceId;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isMonitoring()
    {
        return isset($this->Declarant) && $this->Declarant->isMonitoring() === true;
    }

    /**
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     */
    protected function sendMonitoringResponse()
    {
        // TODO

        return $this->successResponse();
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateNestedMember($attribute, $params, $validator)
    {
        if (isset($this->$attribute) && !$this->$attribute->validate()) {
            $this->addErrors($this->$attribute->getErrors());
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateVisitTimeRange($attribute, $params, $validator)
    {
        $specialist = $this->getSpecialist();

        if ($specialist instanceof MosruSpecialists) {
            $model = DynamicModel::validateData(['VisitDate' => $this->VisitDate], [
                [
                    'VisitDate', VisitTimeRangeValidator::class,
                    'callToHome' => $this->ServiceProperties->CallToHome,
                    'services' => $this->getServices(),
                    'specialist' => $specialist,
                ],
            ]);
            if ($model->hasErrors()) {
                $this->addErrors($model->getErrors());
            }
        }
    }

    public function validateArrayLength($attribute, $params, $validator)
    {
        $max = $params['max'];
        if (!$max) return;

        if (count($this->$attribute) > $max) {
            $this->addError($attribute, "number of entries exceeded the limit of $max");
        }
    }
}
