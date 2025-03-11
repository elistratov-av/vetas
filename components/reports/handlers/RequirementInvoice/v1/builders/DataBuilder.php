<?php

namespace app\common\components\reports\handlers\RequirementInvoice\v1\builders;

use app\common\components\reports\handlers\RequirementInvoice\v1\DataProvider;
use app\common\components\reports\handlers\RequirementInvoice\v1\dto\ReportDto;
use app\common\components\reports\handlers\RequirementInvoice\v1\dto\TmcDto;
use app\common\components\reports\interfaces\AbctractDataBuilder;
use app\models\db\tmc\BalanceActionTmcList;

/**
 * Билдер для обработки данных для отчета
 * Class DataBuilder
 * * @property ReportDto $reportDto
 *
 * @property DataProvider $provider
 * @package app\common\components\reports\handlers\RequirementInvoice\v1\builders
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
                    'acceptor_date'        => $provider->getAcceptorDate(),
                    'initiator_date'       => $provider->getInitiatorDate(),
                    'number'               => $provider->getNumber(),
                    'sender'               => $provider->getSenderName(),
                    'recipient'            => $provider->getRecipientName(),
                    'sender_org'           => $provider->getFromOrganizationName(),
                    'recipient_org'        => $provider->getToOrganizationName(),
                    'tmc'                  => array_map(function ($tmc_list) {
                        /* @var BalanceActionTmcList $tmc_list */
                        return new TmcDto([
                            //Рабикан (серийный номер 56578), годен до 01.02.2022
                            'name'              =>
                                $tmc_list->balance->tmc->name . ' ' .
                                '(' . $tmc_list->balance->inventory_number . ')' .
                                (', годен до ' . date('d.m.Y', strtotime($tmc_list->balance->expiration_date))),
                            'form_name'         => $tmc_list->balance->production_form->name,
                            'measure'           => $tmc_list->balance->tmc->measure ? $tmc_list->balance->tmc->measure->name : null,
                            'count'             => $tmc_list->count,
                            'price'             => $tmc_list->balance->price,
                            'sum'               => $tmc_list->count * $tmc_list->balance->price,
                            'sum_outNDS'        => $tmc_list->count * $tmc_list->balance->price / 1.20,
                            'NDS'               => $tmc_list->count * $tmc_list->balance->price / 120 * 20,
                            'initiator_comment' => $tmc_list->balanceAction->initiator_comment,
                            'acceptor_comment'  => $tmc_list->balanceAction->acceptor_comment
                        ]);

                    }, $provider->getActionTmcList()),
                    'acceptor_specialist'  => $provider->getAcceptorSpecialist(),
                    'initiator_specialist' => $provider->getInitiatorSpecialist(),
                    'total_sum_outNDS'     => ($provider->getTotalSum() / 1.20),
                ]
            );
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();

        return $this;
    }

}
