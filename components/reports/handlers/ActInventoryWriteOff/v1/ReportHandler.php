<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1;

use app\common\components\reports\dto\ResponseFileDto;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\builders\DataBuilder;
use app\common\components\reports\handlers\ActInventoryWriteOff\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractReportHandler;
use app\common\components\reports\interfaces\RequestDtoInterface;

/**
 * Главный обработчик отчета
 * Class ReportHandler
 *
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1
 * @author Aleksandr Roik
 */
class ReportHandler extends AbstractReportHandler
{
   /**
     * Создает отчет по ID действия балансовой операции
     *
     * @param RequestDtoInterface $requestDto
     * @param string $fileType
     * @return ResponseFileDto
     */
    public function makeById(RequestDtoInterface $requestDto, string $fileType)
    {
        $reportDto = (new DataBuilder(
            new SingleDataProvider($this, $requestDto)
        ))
            ->build(ReportDto::class)
            ->getReportDto();

        return (new PrintDirector(
            $this,
            $reportDto
        ))
            ->createBuilder($fileType)
            ->make()
            ->toFile();
    }

    /**
     * Создает отчет за определенный период
     *
     * @param RequestDtoInterface $requestDto
     * @param string $fileType
     * @return ResponseFileDto
     */
    public function makeByPeriod(RequestDtoInterface $requestDto, string $fileType)
    {
        $reportDto = (new DataBuilder(
            new PerionDataProvider($this, $requestDto)
        ))
            ->build(ReportDto::class)
            ->getReportDto();

        return (new PrintDirector(
            $this,
            $reportDto
        ))
            ->createBuilder($fileType)
            ->make()
            ->toFile();
    }
}
