<?php

namespace app\modules\soap\models\etp\response;

use app\modules\soap\models\etp\CoordinateMessageInterface;
use app\modules\soap\models\etp\CoordinateStatusMessage1068;
use app\modules\soap\models\etp\CoordinateStatusMessage1069;
use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\etp\status\StatusReasonInterface;

class CoordinateStatusMessageResponse implements XmlItemInterface
{
    /** @var string */
    public $ResponseDate;

    /** @var array  */
    public $Status;

    /** @var array  */
    public $Responsible;

    /** @var array  */
    public $Documents;

    /** @var string */
    public $Note;

    /** @var string  */
    public $ServiceNumber;

    /** @var string */
    public $Reason;

    /** @var Department  */
    public $Department;

    /** @var null|string */
    public $StatusId;

    /**
     * CoordinateStatusMessageResponse constructor.
     * @param StatusInterface $status
     * @param CoordinateMessageInterface $message
     */
    public function __construct(StatusInterface $status, CoordinateMessageInterface $message)
    {
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone('Europe/Moscow'));
        $d = $date->format(DATE_RFC3339_EXTENDED);

        $this->ResponseDate = $d;
        $this->ServiceNumber = $message->getServiceNumber();

        // Важен порядок отдаваемых элементов, поэтму получаем правильный порядок через метод toArray
        $this->Responsible = (new Responsible($message->getResponsible()))->toArray();
        $this->Department = (new Department($message->getDepartment()))->toArray();

        $this->Status = [
            'StatusCode' => $status->getCode(),
            'StatusTitle' => $status->getName(),
            'StatusDate' => $d,
        ];

        $this->Reason = [
            'Code' => ($status instanceof StatusReasonInterface) ? $status->getReasonCode() : ''
        ];

        $this->Documents = [
            'ServiceDocument' => [
                'DocKind' => [
                    'Code' => 7709,
                    'Name' => 'Иной документ'
                ],
                'DocDate' => $d,
                //'ValidityPeriod' => new nillable(),
                //'ListCount' => new nillable(),
                'CopyCount' => 1,
                //'DocCode' => 'vatas',
            ],
        ];

        if ($customAttributes = $message->getCustomAttributes()) {
            $this->Documents['ServiceDocument']['CustomAttributes'] = $customAttributes;
        }

        $this->Note = (string)(new Note($status, $message));

        $this->StatusId = $message->getStatusId();
    }

    public function getXmlTagName(): string
    {
        return 'CoordinateStatusDataMessage';
    }

}
