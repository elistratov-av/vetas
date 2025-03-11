<?php

namespace app\modules\soap\v2\models\etp;

use app\models\db\Breeds;
use app\models\db\MosRuServices;
use app\models\db\Species;
use app\modules\soap\models\etp\response\XmlItemInterface;
use app\modules\soap\models\etp\status\Status1053;
use app\modules\soap\models\etp\status\Status8021;
use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\etp\status\StatusReasonInterface;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use yii\base\Model;

/**
 * Class StatusResponse
 * @package app\modules\soap\v2\models\etp
 */
class StatusResponse extends Model implements XmlItemInterface
{
    /**
     * @var string
     */
    public $ResponseDate;
    /**
     * @var array
     */
    public $Status;
    /**
     * @var \app\modules\soap\v2\models\etp\members\ServiceProperties
     */
    public $ServiceProperties;
    /**
     * @var string
     */
    public $Note;
    /**
     * @var string
     */
    public $ServiceNumber;
    /**
     * @var string
     */
    public $Reason;
    /**
     * @var null|string
     */
    public $StatusId;

    /**
     * @param \app\modules\soap\models\etp\status\StatusInterface $status
     * @param \app\modules\soap\v2\models\etp\ApplicationMessage  $message
     */
    public function __construct(StatusInterface $status, ApplicationMessage $message)
    {
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone('Europe/Moscow'));
        $d = $date->format(DATE_RFC3339_EXTENDED);

        $this->ResponseDate = $d;

        $this->Status = [
            'StatusCode' => $status->getCode(),
            'StatusTitle' => $status->getName(),
            'StatusDate' => $d,
        ];

        $this->ServiceNumber = $message->ServiceNumber;
        $this->ServiceProperties = $this->prepareServiceProperties($message);

        $this->Reason = [
            'Code' => ($status instanceof StatusReasonInterface) ? $status->getReasonCode() : '',
            'Name' => ($status instanceof StatusReasonInterface) ? $status->getName() : '',
        ];

        // CoordinateMessageInterface typehint was removed
        // TODO - implement ApplicationMessage typehint instead of phpdoc in the end
        $this->Note = $status->getNote($message);
    }

    /**
     * @return string
     */
    public function getXmlTagName(): string
    {
        return 'ApplicationStatusData';
    }

    /**
     * @param string|null $status_id
     * @return array
     */
    public function prepareResponse($status_id = null)
    {
        if ($status_id !== null) {
            $this->StatusId = $status_id;
        }

        return $this->toArray();
    }

    /**
     * @param \app\modules\soap\v2\models\etp\ApplicationMessage $message
     * @return \app\modules\soap\v2\models\etp\members\ServiceProperties
     */
    protected function prepareServiceProperties($message)
    {
        $visit = $message->getVisit();

        $serviceProperties = $message->ServiceProperties;

        if (empty($serviceProperties->TicketNumber)) {
            if ($visit !== null) {
                $serviceProperties->TicketNumber = $visit->ticket_number;
            }
        }

        if (isset($serviceProperties->SpeciesId) && empty($serviceProperties->SpeciesName)) {
            $species = Species::findOne(['id' => $serviceProperties->SpeciesId]);
            if ($species !== null) {
                $serviceProperties->SpeciesName = $species->name;
            }
        }
        if (isset($serviceProperties->BreedId) && empty($serviceProperties->BreedName)) {
            $breeds = Breeds::findOne(['id' => $serviceProperties->BreedId]);
            if ($breeds !== null) {
                $serviceProperties->BreedName = $breeds->name;
            }
        }

        if (($this->Status['StatusCode'] == Status1053::CODE || $this->Status['StatusCode'] == Status8021::CODE) && $visit !== null) {
            // используем данные приема после переноса
            $serviceProperties->OrgId = $visit->id_organization;
            $specs = $visit->specialists;
            if (!empty($specs)) {
                $serviceProperties->SpecialistId = $specs[0]->id_user;
            }
            if (!empty($visit->start_dttm)) {
                $serviceProperties->Date = \DateTime::createFromFormat('Y-m-d H:i:s', $visit->start_dttm)->format('Y-m-d');
                $serviceProperties->Slot = \DateTime::createFromFormat('Y-m-d H:i:s', $visit->start_dttm)->format('H:i');
            }
        }

        if (isset($serviceProperties->OrgId)) {
            $organization = $this->getOrganization($serviceProperties->OrgId);
            if ($organization !== null) {
                if (empty($serviceProperties->OrgName)) {
                    $serviceProperties->OrgName = $organization->name;
                }
                if (empty($serviceProperties->OrgAddressName) && !empty($organization->address)) {
                    $serviceProperties->OrgAddressName = $organization->address->name;
                    $serviceProperties->OrgLatitude = $organization->address->latitude;
                    $serviceProperties->OrgLongitude = $organization->address->longitude;
                }
                if (empty($serviceProperties->OrgPhone)) {
                    $serviceProperties->OrgPhone = $organization->getTelephone();
                }
            }
        }
        if (isset($serviceProperties->SpecialistId) && empty($serviceProperties->SpecialistName)) {
            $specialist = $this->getSpecialist($serviceProperties->SpecialistId, $serviceProperties->OrgId);
            if ($specialist !== null) {
                $serviceProperties->SpecialistName = $specialist->name;
            }
        }

        if (!empty($serviceProperties->ServiceList)) {
            $serviceList = $serviceProperties->ServiceList;
            foreach ($serviceList as $key => $service) {
                if (!empty($service->ServiceId)) {
                    $mosruService = MosRuServices::findOne(['id' => $service->ServiceId]);
                    if ($mosruService === null) {
                        continue;
                    }
                    if (empty($service->Name)) {
                        $service->Name = $mosruService->name;
                    }
                    if (empty($service->TypeValue)) {
                        $service->TypeValue = $mosruService->serviceType->name;
                    }
                    $serviceList[$key] = $service;
                }
            }
            $serviceProperties->ServiceList = array_values($serviceList);
        }

        return $serviceProperties;
    }

    /**
     * @param int $OrgId
     * @return MosruOrganizations|null
     */
    private function getOrganization($OrgId = null)
    {
        if (empty($OrgId)) {
            return null;
        }

        return MosruOrganizations::find()
            ->where(['id' => $OrgId])
            ->one();
    }

    /**
     * @param int $SpecialistId
     * @param int $OrgId
     * @return \app\modules\soap\models\MosruSpecialists|null
     */
    private function getSpecialist($SpecialistId = null, $OrgId = null)
    {
        if (empty($SpecialistId) || empty($OrgId)) {
            return null;
        }

        return MosruSpecialists::find()
            ->where(['id_user' => (int)$SpecialistId])
            ->andWhere(['id_organization' => (int)$OrgId])
            ->one();
    }
}
