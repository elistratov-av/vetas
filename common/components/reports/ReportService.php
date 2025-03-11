<?php

namespace app\common\components\reports;

use app\common\components\reports\definitions\ReportMapHandlerDefinition;
use app\common\components\reports\interfaces\ReportHandlerInterface;
use Yii;
use yii\base\Component;

/**
 * Сервис построения отчетов
 * Class ReportService
 *
 * @author Aleksandr Roik
 */
class ReportService extends Component
{
    /**
     * @var Config
     */
    public $config;

    /**
     * ReportService constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * Создает и возвращает обработчик отчетов
     *
     * @param int $reportId
     * @param string $version
     * @return ReportHandlerInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function createHandler(int $reportId, string $version): ReportHandlerInterface
    {
        /* @var ReportHandlerInterface $handler */
        $handlerClass = ReportMapHandlerDefinition::getHandler($reportId, $version);

        return new $handlerClass($this, $reportId, $version);
    }
}
