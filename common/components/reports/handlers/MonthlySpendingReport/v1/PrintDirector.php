<?php

namespace app\common\components\reports\handlers\MonthlySpendingReport\v1;

use app\common\components\reports\definitions\FileTypeDefinition;
use app\common\components\reports\dto\FileOptionsXlsDto;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\builders\PrintExcelBuilder;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\components\reports\interfaces\AbstractPrintDirector;
use app\common\components\reports\interfaces\ReportDtoInterface;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use LogicException;

/**
 * Директор (руководитель) построения отчета на основании ранее подготовленных данных
 * Class PrintDirector
 *
 * @property ReportDto $reportDto
 * @package app\common\components\reports\handlers\MonthlySpendingReport\v1
 * @author Aleksandr Roik
 */
class PrintDirector extends AbstractPrintDirector
{
    public function __construct(ReportHandlerInterface $handler, ReportDtoInterface $reportDto)
    {
        parent::__construct($handler, $reportDto);
    }

    /**
     * Создает отчет
     *
     * @param string $fileType
     * @param array $printBuilderConfig
     * @return AbstractPrintBuilder
     */
    public function createBuilder(string $fileType, array $printBuilderConfig = []): AbstractPrintBuilder
    {
        switch ($fileType) {
            case FileTypeDefinition::EXCEL:
                return new PrintExcelBuilder(
                    $this,
                    new FileOptionsXlsDto(['name' => 'make-monthly-spending-report_'. date('Ymd_His') . '.xls']),
                    ...$printBuilderConfig
                );
            default:
                throw new LogicException('Отчета не потдерживает создание отчета в указаном формате');
        }

    }
}
