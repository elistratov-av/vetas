<?php

namespace app\common\components\reports\interfaces;

/**
 * Абстрактный класс для директора (руководителя) построения отчета на основании ранее подготовленных данных
 * Class AbstractPrintDirector
 *
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
abstract class AbstractPrintDirector
{
    /**
     * @var AbstractReportHandler
     */
    public $handler;

    /**
     * @var ReportDtoInterface
     */
    public $reportDto;

    /**
     * AbstractDirector constructor.
     *
     * @param ReportHandlerInterface $handler
     * @param ReportDtoInterface $reportDto
     */
    public function __construct(ReportHandlerInterface $handler, ReportDtoInterface $reportDto)
    {
        $this->handler = $handler;
        $this->reportDto = $reportDto;
    }

    /**
     * Создает обработчик файла отчета.
     * Надо реализовать создание списка обработчиков для разных типов файлов, что будут
     * работать на основе интерфейса AbstractPrintBuilder
     * Обработчик надо передать в свойство self::$printBuilder
     *
     * @param string $fileType
     * @param array $printBuilderConfig
     * @return AbstractPrintBuilder
     */
    abstract public function createBuilder(string $fileType, array $printBuilderConfig = []): AbstractPrintBuilder;
}
