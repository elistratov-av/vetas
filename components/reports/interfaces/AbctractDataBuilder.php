<?php

namespace app\common\components\reports\interfaces;

/**
 * Абстрактный метод для билдера, обрабатываючего данные для отчета
 * Class AbctractDataBuilder
 *
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
abstract class AbctractDataBuilder
{
    /**
     * @var AbctractDataProvider
     */
    public $provider;

    /**
     * @var ReportDtoInterface
     */
    public $reportDto;

    /***
     * AbstractDirector constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param ReportDtoInterface $reportDto
     */
    public function __construct(AbctractDataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Запуск построения
     *
     * @param string $reportDtoClass
     * @return self
     */
    abstract protected function build(string $reportDtoClass);

    /**
     * @return ReportDtoInterface
     */
    public function getReportDto(): ReportDtoInterface
    {
        return $this->reportDto;
    }
}
