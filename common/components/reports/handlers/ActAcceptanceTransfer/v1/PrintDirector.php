<?php

namespace app\common\components\reports\handlers\ActAcceptanceTransfer\v1;

use app\common\components\reports\definitions\FileTypeDefinition;
use app\common\components\reports\dto\FileOptionsPdfDto;
use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\builders\PrintPdfBuilder;
use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\components\reports\interfaces\AbstractPrintDirector;
use LogicException;

/**
 * Директор (руководитель) построения отчета на основании ранее подготовленных данных
 * Class PrintDirector
 *
 * @property ReportDto $reportDto
 * @package app\common\components\reports\handlers\ActAcceptanceTransfer\v1
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
            case FileTypeDefinition::PDF:
                return new PrintPdfBuilder(
                    $this,
                    new FileOptionsPdfDto(['name' => 'make-act-acceptance-transfer_' . date('Ymd_His') . '.pdf']),
                    ...$printBuilderConfig
                );
            default:
                throw new LogicException('Указаный формат "' . $fileType . '" не потдерживается');
        }
    }
}
