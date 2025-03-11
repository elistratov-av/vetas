<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1\builders;

use app\common\components\reports\handlers\ActInventoryWriteOff\v1\definitions\UnitsDefinition;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\PerionDataProvider;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\SingleDataProvider;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\ReportDto;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\ReportTmcDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\TmcBase;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 *
 * @property ReportDto $reportDto
 * @property SingleDataProvider|PerionDataProvider $provider
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1\builders
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
                    'acceptorDate'            => $provider->getAcceptorDate(),
                    'number'                  => $provider->getNumber(),
                    'fromOrganizationName'    => $provider->getFromOrganizationName(),
                    'initiatorSpecialistName' => $provider->getInitiatorSpecialistName(),
                    'tmc'                     => $this->getTmc(),
                    'sumTotal'                => $provider->getSumTotal(),
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
     * @return array
     */
    private function getTmc(): array
    {
        return array_map(function ($tmcList) {
            $tmc = $tmcList->balance->tmc;
            $dosage = $this->getDosage($tmc);

            /* @var BalanceActionTmcList $tmcList */
            return new ReportTmcDto([
                //Рабикан (серийный номер 56578), годен до 01.02.2022
                'name'             =>
                    $tmcList->balance->tmc->name . ' ' .
                    '(' . $tmcList->balance->inventory_number . ')' .
                    ($tmcList->balance->expiration_date ? ', годен до ' . date('d.m.Y', strtotime($tmcList->balance->expiration_date)) : ''),
                'measure'          => $this->getMeasure($tmc, $dosage),
                'count'            => $dosage ? $tmcList->count / $dosage->dosage : $tmcList->count,
                'price'            => $tmcList->balance->price,
                'sum'              => $tmcList->count * $tmcList->balance->price,
                'initiatorComment' => $tmcList->balanceAction->initiator_comment,
            ]);

        }, $this->provider->getActionTmcList());
    }

    /**
     * Возвращает название дозировки
     *
     * @param TmcBase $tmc
     * @param Dosages|null $dosage
     * @return string|null
     */
    private function getMeasure(TmcBase $tmc, ?Dosages $dosage)
    {
        /*@var Dosages $dosage*/
        if ($this->provider->requestDto->units == UnitsDefinition::DOSAGE) {
            if ($dosage) {
                return $dosage->measure->name;
            }
        }

        return $tmc->measure ? $tmc->measure->name : null;
    }

    /**
     * Возвращает дозировку "для акта", если запрошено отчет в дозах
     *
     * @param TmcBase $tmc
     * @return Dosages|null
     */
    private function getDosage(TmcBase $tmc)
    {
        if ($this->provider->requestDto->units != UnitsDefinition::DOSAGE) {
            return null;
        }

        return $tmc
            ->getDosages()
            ->joinWith('flag', false)
            ->andWhere(['dosages_flags.for_act' => true])
            ->one();
    }
}
