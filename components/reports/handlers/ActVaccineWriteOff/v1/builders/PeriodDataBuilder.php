<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders;

use app\common\components\reports\handlers\ActVaccineWriteOff\v1\ByTmcDataProvider;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportSpecialistDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportTmcDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\PerionDataProvider;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\Specialists;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitServiceTmcPet;
use Exception;

/**
 * Билдер для обработки данных для отчета
 * Class PeriodDataBuilder
 *
 * @property-read  ReportDto $reportDto
 * @property-read PerionDataProvider $provider
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders
 * @author  Aleksandr Roik
 */
class PeriodDataBuilder extends AbctractDataBuilder
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
            $this->reportDto = new $reportDtoClass([
                'acceptorDate'     => $this->provider->getAcceptorDate(),
                'organizationName' => $this->provider->getOrganizationName(),
                'number'           => $this->provider->getNumber(),
                'tmc'              => $this->getTmcs(),
            ]);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }

    /**
     * Собираем ТМЦ
     *
     * @return array
     */
    private function getTmcs(): array
    {
        $result = [];

        foreach ($this->provider->findTmc() as $tmc) {
            $visitServiceTmcIds = array_map(
                function (VisitServiceTmc $visitServiceTmc) {
                    return $visitServiceTmc->id;
                }, $tmc->visitServiceTmc);

            $balanceIds = array_values(
                array_unique(
                    array_map(
                        function (VisitServiceTmc $visitServiceTmc) {
                            return $visitServiceTmc->id_balance_tmc ? $visitServiceTmc->id_balance_tmc : null;
                        }, $tmc->visitServiceTmc)
                )
            );

            foreach ($balanceIds as $balanceId) {
                if ($balanceId === null) {
                    continue;
                }

                $visitServiceTmc = $this->provider->findVisitServiceTmcByIds(
                    $visitServiceTmcIds,
                    $tmc->id,
                    $balanceId
                );

                // Только балансовые
                if ($balanceId) {
                    $result[] = $this->getReportTmcDtoByBalance(
                        $tmc,
                        $visitServiceTmc,
                        Balance::findOne($balanceId)
                    );
                }
            }
        }

        if (!$result) {
            throw new Exception('Данные для отчета не найдены');
        }

        return $result;
    }

    /**
     * ТМЦ с баланса
     *
     * @param TmcBase $tmc
     * @param VisitServiceTmc[] $visitServiceTmcs
     * @param Balance $balance
     * @return ReportTmcDto
     */
    private function getReportTmcDtoByBalance(TmcBase $tmc, array $visitServiceTmcs, Balance $balance)
    {
        $visitServiceTmcPets = $this->getVisitServiceTmcPets($visitServiceTmcs);

        return new ReportTmcDto([
            'tmcName'            => $tmc->name,
            'tmcProduced'        => $tmc->produced,
            'tmcExpirationDate'  => $balance->expiration_date ? new \DateTime($balance->expiration_date) : null,
            'tmcInventoryNumber' => $balance->inventory_number,
            'dogCount'           => $this->provider->getDogCount($visitServiceTmcPets),
            'dogIsSmallCount'    => $this->provider->getDogIsSmallCount($visitServiceTmcPets),
            'catCount'           => $this->provider->getCatCount($visitServiceTmcPets),
            'totalCount'         => $this->provider->getDogCount($visitServiceTmcPets) + $this->provider->getCatCount($visitServiceTmcPets),
            'ownerNames'          => $this->provider->getOwnerNames($visitServiceTmcs),
            'specialists'        => array_map(function (Specialists $specialist) {
                return new ReportSpecialistDto([
                    'specialistName'   => $specialist->getFullnameInitials(),
                    'organizationName' => $specialist->organization->short_name,
                ]);
            }, $this->provider->getSpecialists($tmc))
        ]);
    }

    /**
     * Созвращает список visitServiceTmcPet из всех $visitServiceTmcs в списке
     *
     * @param VisitServiceTmc[] $visitServiceTmc
     * @return VisitServiceTmcPet[]
     */
    private function getVisitServiceTmcPets(array $visitServiceTmcs): array
    {
        $result = [];
        foreach ($visitServiceTmcs as $visitServiceTmc) {
            $result = array_merge(
                $result,
                $visitServiceTmc->visitServiceTmcPet
            );
        }

        return $result;
    }
}
