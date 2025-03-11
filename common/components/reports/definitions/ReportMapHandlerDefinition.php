<?php

namespace app\common\components\reports\definitions;

use app\common\definitions\AbstractDefinition;
use  LogicException;

/**
 * Справочник обработчиков отчетов.
 * Class ReportMapHandlerDefinition
 *
 * @package app\common\components\reports\definitions
 * @author Aleksandr Roik
 */
class ReportMapHandlerDefinition extends AbstractDefinition
{
    /**
     * Мапинг для отчетов и их обработчиков
     *
     * @var array[]
     */
    private static $mapping = [
        ReportDefinition::ACT_INVENTORY_WRITE_OFF => [
            'alias'    => 'ActInventoryWriteOff',
            'versions' => [
                VersionDefinition::V1,
            ],
        ],
        ReportDefinition::INVOICE_MATERIALS_LEAVE_TO_SIDE => [
            'alias' => 'InvoiceMaterialsLeaveToSide',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::REQUIREMENT_INVOICE => [
            'alias' => 'RequirementInvoice',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::ACT_ACCEPTANCE_TRANSFER => [
            'alias'    => 'ActAcceptanceTransfer',
            'versions' => [
                VersionDefinition::V1,
            ],
        ],
        ReportDefinition::MONTHLY_SPENDING_REPORT => [
            'alias' => 'MonthlySpendingReport',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::DAILY_SPENDING_REPORT=> [
            'alias' => 'DailySpendingReport',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::ACT_VACCINE_WRITE_OFF => [
            'alias'    => 'ActVaccineWriteOff',
            'versions' => [
                VersionDefinition::V1,
            ],
        ],
        ReportDefinition::REQUIREMENT_ORDER => [
            'alias' => 'RequirementOrder',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::ORGANIZATION_REPORT => [
            'alias' => 'OrganizationReport',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::USED_TMC_IN_RECEPTION => [
            'alias' => 'UsedTmcInReception',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
        ReportDefinition::PET_HOTEL_STAY_REPORT => [
            'alias' => 'PetHotelStayReport',
            'versions' => [
                VersionDefinition::V1,
            ]
        ],
    ];

    /**
     * Проверка
     *
     * @param int $reportId
     * @param string $versionId
     * @return void
     */
    private static function validateVesion(int $reportId, string $version)
    {
        if (!ReportDefinition::hasId($reportId)) {
            throw new LogicException("Отчет #$version отсутсвует");
        }
        if (!array_key_exists($reportId, self::$mapping)) {
            throw new LogicException("Для отчета #$version отсутсвует мапинг");
        }
        if (!in_array($version, self::$mapping[$reportId]['versions'])) {
            throw new LogicException("Для отчета #$reportId отсутствует версия $version");
        }
    }

    /**
     * Возвращает название класса-обработкика отчета исходя из его ID и версии
     *
     * @param $reportId
     * @param string $versionId
     * @return string
     */
    public static function getHandler(int $reportId, string $version = VersionDefinition::V1): string
    {
        self::validateVesion($reportId, $version);
        $mapData = self::$mapping[$reportId];

        $handler = 'app\\common\\components\\reports\\handlers\\' . $mapData['alias'] . '\\' . $version . '\\' . 'ReportHandler';

        if (!class_exists($handler)) {
            throw new LogicException("Обработчик $handler для отчета #$reportId версии $version не найден");
        }

        return $handler;
    }

}
