<?php

namespace app\common\components\reports\handlers\UsedTmcInReception\v1;

use app\common\components\reports\handlers\UsedTmcInReception\v1\dto\MakeSingleRequestDto;
use app\common\components\reports\interfaces\AbctractDataProvider;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use yii\db\Exception;
use DateTime;

/**
 * Провайдер данных отчета по одному списанию.
 * Class DataProvider
 *
 * @property MakeSingleRequestDto $requestDto
 * @package app\common\components\reports\handlers\UsedTmcInReception\v1
 * @author Aleksandr Roik
 */
class SingleDataProvider extends AbctractDataProvider
{
    /**
     * @var Visits
     */
    private $visit;

    /**
     * @throws Exception
     */
    protected function init()
    {
        $this->visit = Visits::findOne($this->requestDto->idVisit);

        if (!$this->visit) {
            throw new Exception('Прием не найден');
        }
    }

    /**
     * @return void
     */
    protected function initHash()
    {
        $this->hash = null;
    }

    /**
     * @return Visits
     */
    public function getVisit(): Visits
    {
        return $this->visit;
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getDate(): ?DateTime
    {
        return $this->visit->fact_start_dttm ? new DateTime($this->visit->fact_start_dttm) : new DateTime($this->visit->created_at);
    }

    /**
     * @return string|null
     */
    public function getOrganizationName(): ?string
    {
        return $this->visit->organization->short_name;
    }

    /**
     * @return string|null
     */
    public function getOwnerName(): ?string
    {
        if ($this->visit->owner->is_legal) {
            $list = [
                $this->visit->owner->jur_name,
                '(' . $this->visit->owner->fullname . ')',
            ];
        } else {
            $list = [
                $this->visit->owner->fullname,
            ];
        }

        return implode(' ', $list);
    }

    /**
     * @return string|null
     */
    public function getOwnerAddress(): ?string
    {
        return $this->visit->owner->address ? $this->visit->owner->address->name : null;
    }

    /**
     * @return string|null
     */
    public function getOwnerPhone(): ?string
    {
        return $this->visit->owner->phoneMainContact ? $this->visit->owner->phoneMainContact->name : null;
    }

    /**
     * @return string
     */
    public function getSpecialistName(): ?string
    {
        return $this->visit->specialists->fullname;
    }

    /**
     * @return VisitServiceTmc[]|[]
     */
    public function getTmcs(): array
    {
        return $this
            ->visit
            ->getVisitServiceTmcs()
            ->andWhere(['is not', 'id_balance_tmc', null])
            ->all();
    }
}
