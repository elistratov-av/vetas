<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\Visits;
use app\modules\v2\modules\emergency\models\EmergencyModel;
use yii\helpers\ArrayHelper;

/**
 * Trait VisitTrait
 * @package app\modules\v2\modules\visit\models
 *
 * @property \app\models\db\Visits $visit
 */
trait VisitTrait
{
    public static $CALL_TO_HOME_SERVICE_CODE = '0365';

    /**
     * Редактирование приема допускается в течение суток после завершения приема
     * @return bool
     */
    protected function canEditFinishedVisit()
    {
        if (!empty($this->visit->fact_end_dttm)) {
            $dateEnd = date_create_from_format('Y-m-d H:i:s', $this->visit->fact_end_dttm);
        } else {
            // fallback
            $dateStart = $this->visit->fact_start_dttm ?? $this->visit->start_dttm ?? $this->visit->created_at;
            if (empty($dateStart)) {
                return false;
            }
            $dateEnd = date_create_from_format('Y-m-d H:i:s', $dateStart);
            if ($dateEnd === false) {
                return false;
            }
            $dateEnd = empty($this->visit->duration) ? $dateEnd : $dateEnd->modify('+' . $this->visit->duration . ' minutes');
        }

        if ($dateEnd === false) {
            return false;
        }

        $dateEndPlus = $dateEnd->modify('+1 day');
        if ($dateEndPlus === false) {
            return false;
        }

        return time() < $dateEndPlus->getTimestamp();
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function isCallToHome(string $type = null): bool
    {
        if ($type === null) {
            $type = $this->visit->type;
        }

        return $type == Visits::TYPE_AT_HOME;
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function isAmbulanceVisit($type = null): bool
    {
        if ($type === null) {
            $type = $this->visit->type;
        }

        return $type == Visits::TYPE_AMBULANCE;
    }

    /**
     * проверка на экстренную ситуацию
     *
     * @param int    $id_organization
     * @param string $from
     * @param string $to
     * @param string $attributeName
     * @return bool
     */
    protected function checkVisitForEmergency(int $id_organization, string $from, string $to = null, string $attributeName = 'visit'): bool
    {
        $emergency = (new EmergencyModel())
            ->getOneOrganizationsEmergencyForPeriod($id_organization, $from, $to ?? $from);

        if ($emergency !== null) {
            $this->addError($attributeName, 'В указанной организации введена экстренная ситуация с ' . $emergency['date_from'] . ' по ' . $emergency['date_to']);

            return true;
        }

        return false;
    }
}
