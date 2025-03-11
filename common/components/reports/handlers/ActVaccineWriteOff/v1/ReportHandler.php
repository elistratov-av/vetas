<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1;

use app\common\components\reports\dto\ResponseFileDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders\PeriodDataBuilder;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders\TmcDataBuilder;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\MakeTmcRequestDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractReportHandler;
use app\common\components\reports\interfaces\RequestDtoInterface;

/**
 * Главный обработчик отчета
 * Class ReportHandler
 *
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1
 * @author  Aleksandr Roik
 */
class ReportHandler extends AbstractReportHandler
{
    /**
     * Создает отчет за определенный период
     *
     * @param RequestDtoInterface $requestDto
     * @param string              $fileType
     *
     * @return ResponseFileDto
     */
    public function makeByPeriod(RequestDtoInterface $requestDto, string $fileType)
    {
        $reportDto = (new PeriodDataBuilder(
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

    /**
     * Создает отчет по викцинации из прив. пунктов на основании данных ТМЦ, переданых из фронта
     *
     * @param MakeTmcRequestDto $requestDto
     * @param string $fileType
     * @return ResponseFileDto
     */
    public function makeByTmc(MakeTmcRequestDto $requestDto, string $fileType)
    {
        $reportDto = (new TmcDataBuilder(
            new TmcDataProvider($this, $requestDto)
        ))
            ->build(ReportDto::class)
            ->getReportDto();

        return (new PrintDirector(
            $this,
            $reportDto
        ))
            ->createBuilder($fileType, [$requestDto->template])
            ->make()
            ->toFile();
    }
}
