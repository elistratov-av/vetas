<?php

namespace app\common\components\reports\handlers\ActAcceptanceTransfer\v1\builders;

use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto\ReportTmcDto;
use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\SingleDataProvider;
use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\tmc\BalanceActionTmcList;
use app\models\db\tmc\TmcBase;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 *
 * @property ReportDto $reportDto
 * @property SingleDataProvider $provider
 * @package app\common\components\reports\handlers\ActAcceptanceTransfer\v1\builders
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
                    'acceptorDate'               => $provider->getAcceptorDate(),
                    'number'                     => $provider->getNumber(),
                    'fromOrganizationNameHeader' => $provider->getFromOrganizationNameHeader(),
                    'fromOrganizationName'       => $provider->getFromOrganizationName(),
                    'fromSpecialistName'         => $provider->getFromSpecialistName(),
                    'toOrganizationName'         => $provider->getToOrganizationName(),
                    'toSpecialistName'           => $provider->getToSpecialistName(),
                    'tmc'                        => array_map(function ($tmcList) {
                        /* @var BalanceActionTmcList $tmcList */
                        return new ReportTmcDto([
                            'name'  => $this->formatTmcName($tmcList),
                            'count' => $this->formatTmcCount($tmcList),
                        ]);

                    }, $provider->getActionTmcList()),
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
     * Собираем название ТМЦ
     *
     * @param BalanceActionTmcList $tmcList
     */
    private function formatTmcName(BalanceActionTmcList $tmcList)
    {
        //Рабикан (серийный номер 56578), годен до 01.02.2022

        $data = $tmcList->balance->tmc->name;
        if ($tmcList->balance->inventory_number) {
            $data .= '(' . $tmcList->balance->inventory_number . ')';
        }

        if ($tmcList->balance->expiration_date) {
            $data .= ', годен до ' . date('d.m.Y', strtotime($tmcList->balance->expiration_date));
        }

        return $data;
    }

    /**
     * Определяем и форматируем количество ТМЦ
     *
     * @param BalanceActionTmcList $tmcList
     * @return string
     */
    private function formatTmcCount(BalanceActionTmcList $tmcList)
    {
        $count = $this->roundCount($tmcList->count);
        $data[] = $count;

        if ($tmcList->id_dosage) {
            $data[] = $tmcList->dosage->measure->name . '.';
            $dosageCount = $this->roundCount($count / $tmcList->dosage->dosage);
            $data[] = '(' . $dosageCount . ' ' . $this->getDosageStr($dosageCount) . ')';
        } else {
            if ($tmcList->balance->tmc->measure) {
                $data[] = $tmcList->balance->tmc->measure->name . '.';
            } else {
                if ($tmcList->balance->type_tmc == TmcBase::TYPE_EXP_MATERIAL) {
                    $data[] = 'шт.';
                }
            }
        }

        return implode(' ', $data);
    }

    /**
     * Убирает нули из количества, где это нужно
     *
     * @param $count
     */
    private function roundCount($count)
    {
        if (!$count) {
            return 0;
        }

        if (fmod($count, 1) > 0) {
            return $count;
        } else {
            return round($count);
        }
    }

    /**
     * Склоняем название дозировки ТМЦ
     *
     * @param $dosageCount
     */
    private function getDosageStr($dosageCount)
    {
        if ($dosageCount === null) {
            return;
        }

        if ($dosageCount >= 11 && $dosageCount <= 19) {
            return 'доз';
        }

        switch (strlen((string)round($dosageCount))) {
            case 1 :
                $index = $dosageCount;
                break;
            case 2 :
                $index = $dosageCount % 10;
                break;
            case 3 :
                $index = $dosageCount % 100;
                break;
            case 4 :
                $index = $dosageCount % 1000;
                break;
            case 5 :
                $index = $dosageCount % 10000;
                break;
            case 6 :
                $index = $dosageCount % 100000;
                break;
            case 7 :
                $index = $dosageCount % 1000000;
                break;
            case 8 :
                $index = $dosageCount % 10000000;
                break;
            case 9 :
                $index = $dosageCount % 100000000;
                break;
        }

        if ((int)$index === 1) {
            return 'доза'; //1,21,
        } else {
            if ($index <= 2 && $index >= 4) {
                return 'дозы'; //2,3,4,22,23,24,
            }
        }

        return 'доз'; //0,5,6,7,8,9,10, 11,12,13,14,15,16,17,18,19,20,25...
    }
}
