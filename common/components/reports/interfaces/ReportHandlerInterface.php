<?php

namespace app\common\components\reports\interfaces;

/**
 * Определяющий интерфейс обработчика отчетов
 * Interface ReportHandlerInterface
 *
 * @package app\common\components\reports\interfaces
 * @author Aleksandr Roik
 */
interface ReportHandlerInterface
{

    /**
     * Возвращает id отчета
     *
     * @return int
     */
    public function getReportId(): int;

    /**
     * Возвращает версию отчета
     *
     * @return string
     */
    public function getVerion(): string;

}
