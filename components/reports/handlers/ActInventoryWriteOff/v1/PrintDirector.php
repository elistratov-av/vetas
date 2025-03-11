<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1;

use app\common\components\reports\definitions\FileTypeDefinition;
use app\common\components\reports\dto\FileOptionsXlsDto;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\builders\PrintExcelBuilder;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\components\reports\interfaces\AbstractPrintDirector;
use LogicException;

/**
 * Директор (руководитель) построения отчета на основании ранее подготовленных данных
 * Class PrintDirector
 *
 * @property ReportDto $reportDto
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1
 * @author Aleksandr Roik
 */
class PrintDirector extends AbstractPrintDirector
{
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
                    new FileOptionsXlsDto(['name' => 'make-act-inventory-write-off_' . date('Ymd_His') . '.xls']),
                    ...$printBuilderConfig
                );
            default:
                throw new LogicException('Указаный формат "' . $fileType . '" не потдерживается');
        }
    }
}
