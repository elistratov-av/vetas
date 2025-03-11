<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\Visits;
use yii\base\Model;
use yii\base\UnknownPropertyException;

/**
 * Class RegByServiceNumberRegistration
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberRegistration extends Model
{
    /**
     * @var string service_number {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServiceNumber;
    /**
     * @var string ticket_number {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $TicketNumber;
    /**
     * @var boolean changes_available {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ChangesAvailable;
    /**
     * @var boolean cancel_available {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $CancelAvailable;
    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $SpecialistId;
    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $OrgId;
    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberServiceWrapper {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServiceList;
    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Date;
    /**
     * @var time {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Slot;
    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Duration;
    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $CallToHome;
    /**
     * @var \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberAddressCall {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $AddressCall;
    /**
     * @var \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberAnimal {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Animal;
    /**
     * @var \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberOwner {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $Owner;
    /**
     * @var string
     */
    public $VisitDate;
    /**
     * @var null|array
     */
    public $Logs;

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $e) {
            // просто игнорируем
        }
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $date = new \DateTime($this->VisitDate);

        if ($this->CallToHome == true) {
            $date->add(new \DateInterval("PT" . Visits::CALL_TO_HOME_BEFORE_TIME . "M"));
            $this->Slot = $date->format('H:i');

            $this->Duration = $this->Duration - Visits::CALL_TO_HOME_BEFORE_TIME - Visits::CALL_TO_HOME_AFTER_TIME;
        }

        $this->Date = $date->format('Y-m-d');
        $this->Slot = $date->format('H:i');

        $this->ChangesAvailable = $this->isChangesAvailable();
        $this->CancelAvailable = $this->isCancelAvailable();
    }

    /**
     * @return bool
     */
    protected function isChangesAvailable()
    {
        $time = ($this->CallToHome) ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;
        $date = new \DateTime($this->VisitDate);
        if ($date->getTimestamp() < (time() + $time)) {
            return false;
        }

        if (is_array($this->Logs)) {
            if (in_array(Status10191::CODE, $this->Logs)) {
                return false;
            } elseif (in_array(Status10091::CODE, $this->Logs)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function isCancelAvailable()
    {
        $date = new \DateTime($this->VisitDate);
        if ($date->getTimestamp() < time()) {
            return false;
        }

        if (is_array($this->Logs)) {
            if (in_array(Status10190::CODE, $this->Logs)) {
                return false;
            } elseif (in_array(Status10090::CODE, $this->Logs)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
