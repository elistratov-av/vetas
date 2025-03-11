<?php

namespace app\commands;

use app\common\components\inform\cron\IdentificationInformation;
use app\common\components\inform\cron\LeptospirosisInformation;
use app\common\components\inform\cron\RabiesInformation;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class InformationController extends Controller
{
    /**
     * Напоминание о вакцинации от бешенства
     * @return int
     */
    public function actionRabiesVaccination()
    {
        try {
            $information = new RabiesInformation($this->getBatchSize());
            $information->handle();

            return ExitCode::OK;
        } catch (\Exception $e) {
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Напоминание о вакцинации от лептоспироза
     * @return int
     */
    public function actionLeptospirosisVaccination()
    {
        try {
            $information = new LeptospirosisInformation($this->getBatchSize());
            $information->handle();

            return ExitCode::OK;
        } catch (\Exception $e) {
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Напоминание об идентификации
     * @return int
     */
    public function actionIdentification()
    {
        try {
            $information = new IdentificationInformation($this->getBatchSize());
            $information->handle();

            return ExitCode::OK;
        } catch (\Exception $e) {
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @return int
     */
    protected function getBatchSize(): int
    {
        return (int)$this->module->params['identification']['batch_size'];
    }
}
