<?php

namespace app\common\components\reports\handlers\PetHotelStayReport\v1;

use app\common\components\reports\handlers\PetHotelStayReport\v1\builders\DataBuilder;
use app\common\components\reports\handlers\PetHotelStayReport\v1\dto\ReportDto;
use app\common\components\reports\interfaces\AbstractReportHandler;
use app\common\components\reports\interfaces\RequestDtoInterface;
use app\common\components\reports\dto\ResponseFileDto;

/**
 * Главный обработчик отчета
 * Class ReportHandler
 *
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
            ->toStream();
    }
}
