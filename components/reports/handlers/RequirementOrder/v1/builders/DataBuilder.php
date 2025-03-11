<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1\builders;

use app\common\components\reports\handlers\ActInventoryWriteOff\v1\definitions\UnitsDefinition;
use app\common\components\reports\handlers\RequirementOrder\v1\DataProvider;
use app\common\components\reports\handlers\RequirementOrder\v1\dto\ReportDto;
use app\common\components\reports\handlers\RequirementOrder\v1\dto\TmcDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\Dosages;
use app\models\db\tmc\TmcBase;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 * * @property ReportDto $reportDto
 *
 * @property DataProvider $provider
 * @package app\common\components\reports\handlers\RequirementOrder\v1\builders
 * @author Aleksandr Roik
 */
class DataBuilder extends AbctractDataBuilder
{
    /**
     * Запуск построения
     *
     * @return $this|AbctractDataBuilder
     */
    public function build(string $reportDtoClass)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $provider = $this->provider;
            $this->reportDto = new $reportDtoClass([
                    'initiator_date'  => $provider->getInitiatorDate(),
                    'organization_to' => $provider->getToOrganizationName(),
                    'tmc'             => array_map(function ($tmcList) {
                        $tmc = $tmcList->balance->tmc;
                        $dosage = $this->getDosage($tmc);

                        /* @var BalanceActionTmcList $tmcList */
                        return new TmcDto([
                            //Рабикан (серийный номер 56578), годен до 01.02.2022
                            'name'    =>
                                $tmcList->balance->tmc->name . ' ' .
                                '(' . $tmcList->balance->inventory_number . ')' .
                                (', годен до ' . date('d.m.Y', strtotime($tmcList->balance->expiration_date))),
                            'measure' => $this->getMeasure($tmc, $dosage),
                            'count'   => $dosage ? $tmcList->count / $dosage->dosage : $tmcList->count,
                        ]);

                    }, $provider->getActionTmcList()),
                    'initiator_name'  => $provider->getInitiatorSpecialistName(),
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
