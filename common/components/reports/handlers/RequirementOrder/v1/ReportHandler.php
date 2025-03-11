<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1;

use app\common\components\reports\dto\ResponseFileDto;
use app\common\components\reports\handlers\RequirementOrder\v1\builders\DataBuilder;
use app\common\components\reports\handlers\RequirementOrder\v1\dto\MakeRequestDto;
use app\common\components\reports\handlers\RequirementOrder\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractReportHandler;
use app\common\components\reports\interfaces\RequestDtoInterface;

/**
 * Главный обработчик отчета
 * Class ReportHandler
 *
 * @package app\common\components\reports\handlers\RequirementOrder\v1
 */
class ReportHandler extends AbstractReportHandler
{
    /**
     * Создает отчет
     *
     * @param MakeRequestDto $requestDto
     * @return ResponseFileDto
     */
    public function make(RequestDtoInterface $requestDto, string $fileType)
    {
        $reportDto = (new DataBuilder(
            new DataProvider($this, $requestDto)
        ))
            ->build(ReportDto::class)
            ->getReportDto();

        return (new PrintDirector(
            $this,
            $reportDto
        ))->createBuilder($fileType)
        ->make()
        ->toFile();
    }
}
