<?php

namespace app\modules\soap\models\etp;

use app\common\soap\XmlFormatter;
use app\models\db\MosRuServices;
use app\modules\soap\models\etp\response\CoordinateStatusMessageResponse;
use app\modules\soap\models\etp\response\serviceproperties;
use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\Visits;
use yii\base\Model;

/**
 * Class CoordinateStatusMessage
 * @package app\modules\soap\models\etp
 *
 * @property string $service_number
 * @property array $responsible
 * @property array $department
 * @property null|string $status_id
 *
 * @property Visits|array|null|\yii\db\ActiveRecord $visit
 * @property MosruSpecialists $specialist
 * @property MosRuServices[] $services
 */
abstract class CoordinateStatusMessage extends Model implements CoordinateMessageInterface
{
    /** @var string  */
    protected $errorStatus;

    /** @var array */
    protected $data;

    /** @var string */
    public $service_number;

    /** @var array */
    public $responsible;

    /** @var array */
    public $department;

    /** @var null|string */
    public $status_id = null;

    /** @var MosruSpecialists */
    protected $_specialist;

    /** @var MosRuServices[] */
    protected $_services;

    /** @var Visits|null */
    protected $_visit;

    /**
     * CoordinateMessage constructor.
     * @param array $data
     * @param array $config
     */
    public function __construct(array $data, $config = [])
    {
        $this->data = $data;
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getServiceNumber(): string
    {
        return $this->service_number;
    }

    /**
     * @return null|string
     */
    public function getStatusId(): ?string
    {
        return $this->status_id;
    }

    /**
     * @return array
     */
    public function getResponsible(): array
    {
        return $this->responsible;
    }

    /**
     * @return array
     */
    public function getDepartment(): array
    {
        return $this->department;
    }

    /**
     * @param string $date
     * @param string $slot
     * @return string
     * @throws \Exception
     */
    public function getVisitDate(string $date, string $slot)
    {
        $dateObj = new \DateTime($date);
        list($hour, $minute) = explode(':', $slot);
        $dateObj->setTimezone(new \DateTimeZone('Europe/Moscow'));
        $dateObj->setTime($hour, $minute);
        return $dateObj->format('Y-m-d H:i:s');
    }

    /**
     * @return Visits|array|null|\yii\db\ActiveRecord
     */
    public function getVisit()
    {
        if (!isset($this->_visit)) {
            $query = ETPMessage::find()
                ->select('visit_id')
                ->where(['service_number' => $this->service_number])
                ->orderBy(['id' => SORT_DESC]);

            if ($id = $query->scalar()) {
                $this->_visit = Visits::find()
                    ->where(['id' => $id])
                    ->one();
            } else {
                $this->_visit = null;
            }
        }

        return $this->_visit;
    }

    /**
     * @return MosruSpecialists|array|null|\yii\db\ActiveRecord
     */
    public function getSpecialist()
    {
        return $this->_specialist;
    }

    /**
     * Метод возвращает услуги ВетАИС по id услуг mos.ru полученных в запросе
     * @return MosRuServices[]|array|\yii\db\ActiveRecord[]
     */
    public function getServices()
    {
        if (!isset($this->_services)) {
            if (!isset($this->service_id) || empty($this->service_id)) {
                $this->_services = [];
            } else {
                $this->_services = MosRuServices::find()
                    ->join(
                        'INNER JOIN',
                        'species_services',
                        'species_services.id_service = mosru.services.id and species_services.id_species = :id_species',
                        [
                            'id_species' => $this->species_id
                        ]
                    )
                    ->andWhere(['mosru.services.id' => $this->service_id])
                    ->all();
            }
        }

        return $this->_services;
    }

    /**
     * @return serviceproperties|array
     * @throws \yii\base\InvalidConfigException
     */
    public function getCustomAttributes()
    {
        return new serviceproperties($this);
    }

    /**
     * @param StatusInterface $status
     * @return string
     */
    public function makeResponseData(StatusInterface $status) : string
    {
        $data = new CoordinateStatusMessageResponse($status, $this);

        $formatter = new XmlFormatter();
        return $formatter->makeXml(
            'CoordinateStatusMessage',
            'http://asguf.mos.ru/rkis_gu/coordinate/v6_1/',
            [
                'xmlns:ns1' => 'http://asguf.mos.ru/rkis_gu/coordinate/v6_1/',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance'
            ],
            [$data]
        );
    }

    /**
     * @param string $message
     */
    public function sendErrorMessage(string $message) : void
    {
        $this->sendError(new $this->errorStatus(), $message);
    }

    /**
     * @param StatusInterface $status
     * @param string $message
     */
    protected function sendError(StatusInterface $status, string $message) : void
    {
        \Yii::info("{$this->service_number}||{$status->getCode()}|{$message}", 'soap_queue');

        /** @var ETP $etp */
        $etp = \Yii::$app->getModule('soap')->etp;
        $etp->sendStatusMessage($status, $this);
    }
}
