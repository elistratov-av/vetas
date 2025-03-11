<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders;

use app\common\components\reports\handlers\ActVaccineWriteOff\v1\ByTmcDataProvider;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportSpecialistDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportTmcDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\TmcDataProvider;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\PetOwners;
use app\models\db\tmc\Balance;
use app\models\db\tmc\TmcBase;
use app\models\db\VisitServiceTmc;

/**
 * Билдер для обработки данных для отчета
 * Class PeriodDataBuilder
 *
 * @property-read  ReportDto $reportDto
 * @property-read TmcDataProvider $provider
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders
 * @author  Aleksandr Roik
 */
class TmcDataBuilder extends AbctractDataBuilder
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
        return array_map(function ($tmc) {
            return $this->getReportTmcDtoByData($tmc);
        }, $this->provider->getTmcs());
    }

    /**
     * ТМЦ с баланса
     *
     * @param TmcBase $tmc
     * @param VisitServiceTmc[] $visitServiceTmcs
     * @param Balance $balance
     * @return ReportTmcDto
     */
    private function getReportTmcDtoByData(array $tmc)
    {
        return new ReportTmcDto(
            array_merge(
                array_filter($tmc, function ($key) {
                    return !in_array($key, ['specialists', 'petOwners']);
                }, ARRAY_FILTER_USE_KEY),
                [
                    'petOwners'   => !empty($tmc['petOwners']) ? PetOwners::find()->where(['id' => $tmc['petOwners']])->all() : [],
                    'specialists' => array_map(function ($specialist) {
                        return new ReportSpecialistDto([
                            'specialistName'   => $this->provider->getSpecialistBySpecialist($specialist)->getFullnameInitials(),
                            'organizationName' => $this->provider->getOrganizationBySpecialist($specialist)->short_name,
                        ]);
                    }, $this->provider->getSpecialistsByTmc($tmc))
                ]
            )
        );
    }
}
