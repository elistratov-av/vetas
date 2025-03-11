<?php

namespace app\modules\soap\skeletons\registration_by_service_number;

use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\Visits;
use yii\base\Model;

class RegByServiceNumberRegistration extends Model
{
    /**
     * @var string service_number {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $service_number;

    /**
     * @var string ticket_number {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ticket_number;

    /**
     * @var boolean changes_available {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $changes_available;

    /**
     * @var boolean cancel_available {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $cancel_available;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $specialist_id;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $org_id;

    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberServiceWrapper {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $servicelist;

    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $date;

    /**
     * @var time {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $slot;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $duration;

    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $call_to_home;

    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberAddressCall {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $address_call;

    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberAnimal {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $animal;

    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberOwner {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $owner;

    /**
     * @var string
     */
    public $visit_date;

    /**
     * @var null|array
     */
    public $logs;

    public function init()
    {
        parent::init();

        $date = new \DateTime($this->visit_date);

        if ($this->call_to_home == true) {
            $date->add(new \DateInterval("PT" . Visits::CALL_TO_HOME_BEFORE_TIME . "M"));
            $this->slot = $date->format('H:i');

            $this->duration = $this->duration - Visits::CALL_TO_HOME_BEFORE_TIME - Visits::CALL_TO_HOME_AFTER_TIME;
        }

        $this->date = $date->format('Y-m-d');
        $this->slot = $date->format('H:i');

        $this->changes_available = $this->isChangesAvailable();
        $this->cancel_available = $this->isCancelAvailable();
    }

    /**
     * @return bool
     */
    protected function isChangesAvailable()
    {
        $time = ($this->call_to_home) ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;
        $date = new \DateTime($this->visit_date);
        if ($date->getTimestamp() < (time() + $time)) {
            return false;
        }

        if (is_array($this->logs)) {
            if (in_array(Status10191::CODE, $this->logs)) {
                return false;
            } elseif (in_array(Status10091::CODE, $this->logs)) {
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
        $date = new \DateTime($this->visit_date);
        if ($date->getTimestamp() < time()) {
            return false;
        }

        if (is_array($this->logs)) {
            if (in_array(Status10190::CODE, $this->logs)) {
                return false;
            } elseif (in_array(Status10090::CODE, $this->logs)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
