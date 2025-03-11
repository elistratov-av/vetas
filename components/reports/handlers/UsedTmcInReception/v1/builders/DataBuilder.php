<?php

namespace app\common\components\reports\handlers\UsedTmcInReception\v1\builders;

use app\common\components\reports\handlers\UsedTmcInReception\v1\dto\ReportTmcDto;
use app\common\components\reports\handlers\UsedTmcInReception\v1\SingleDataProvider;
use app\common\components\reports\handlers\UsedTmcInReception\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\common\helpers\MoneyHelper;
use app\models\db\VisitServiceTmc;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 *
 * @property ReportDto $reportDto
 * @property SingleDataProvider $provider
 * @package app\common\components\reports\handlers\UsedTmcInReception\v1\builders
 * @author Aleksandr Roik
 */
class DataBuilder extends AbctractDataBuilder
{
    /**
     * Запуск построения
     *
     * @return self
     */
    public function build(string $reportDtoClass)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $provider = $this->provider;
            $this->reportDto = new $reportDtoClass([
                    'organizationName' => $provider->getOrganizationName(),
                    'ownerName'        => $provider->getOwnerName(),
                    'ownerAddress'     => $provider->getOwnerAddress(),
                    'ownerPhone'       => $provider->getOwnerPhone(),
                    'visitId'          => $provider->getVisit()->id,
                    'date'             => $provider->getDate(),
                    'specialistName'   => $provider->getSpecialistName(),
                    'tmc'              => array_map(function (VisitServiceTmc $serviceTmc) {
                        return new ReportTmcDto([
                            'name'            => $serviceTmc->tmc->name,
                            'inventoryNumber' => $serviceTmc->balance->inventory_number,
                            'expirationDate'  => $serviceTmc->balance->expiration_date ? new \DateTime($serviceTmc->balance->expiration_date) : null,
                            'count'           => $this->getCount($serviceTmc, 'count'),
                            'countUtilize'    => $this->getCount($serviceTmc, 'count_utilize'),
                        ]);
                    }, $provider->getTmcs()),
                ]
            );
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }

    /**
     * @param VisitServiceTmc $serviceTmc
     * @param string $fieldName
     * @return string|null
     */
    private function getCount(VisitServiceTmc $serviceTmc, string $fieldName)
    {
        $count = $this->formatCount($serviceTmc->$fieldName);
        if (!(float)$count) {
            return null;
        }
        $measuse = $this->getMeasureByServiceTmc($serviceTmc);

        return $measuse ? $count . ' ' . $measuse : $count;
    }

    /**
     * @return string
     */
    private function formatCount($count)
    {
        return MoneyHelper::removeDecimalVeros($count);
    }

    /**
     * @param VisitServiceTmc $serviceTmc
     * @return string|null
     */
    private function getMeasureByServiceTmc(VisitServiceTmc $serviceTmc)
    {
        if ($serviceTmc->dosage && $serviceTmc->dosage->measure) {
            return $serviceTmc->dosage->measure->name;
        }

        return $serviceTmc->tmc->measure ? $serviceTmc->tmc->measure->name : null;
    }

}
