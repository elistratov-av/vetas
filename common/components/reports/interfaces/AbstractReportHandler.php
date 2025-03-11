<?php

namespace app\common\components\reports\interfaces;

use app\common\components\reports\ReportService;

/**
 * Абстрактный класс обработчика отчета
 * Class AbstractReportHandler
 *
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
abstract class AbstractReportHandler implements ReportHandlerInterface
{
    /**
     * @var ReportService
     */
    private $service;

    /**
     * id отчета
     *
     * @var int
     */
    private $reportId;

    /**
     * Версия отчета
     *
     * @var string
     */
    private $version;

    /**
     * AbstractReportHandler constructor.
     *
     * @param ReportService $service
     * @param int $report
     * @param string $version
     */
    public function __construct(ReportService $service, int $report, string $version)
    {
        $this->service = $service;
        $this->reportId = $report;
        $this->version = $version;
    }

    /**
     * @return ReportService
     */
    public function getService(): ReportService
    {
        return $this->service;
    }

    /**
     * @return int
     */
    public function getReportId(): int
    {
        return $this->reportId;
    }

    /**
     * @return string
     */
    public function getVerion(): string
    {
        return $this->version;
    }
}
