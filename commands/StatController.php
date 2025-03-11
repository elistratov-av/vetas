<?php

namespace app\commands;

use app\common\components\stat\MosRuStat;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class StatController extends Controller
{
    /**
     * Подсчет статистики по записям с mos.ru на определенную дату
     * @param string $date Дата в формате Y-m-d
     * @return int
     * @throws \Throwable
     */
    public function actionMosRu($date)
    {
        try {
            /** @var MosRuStat $stat */
            $stat = new MosRuStat();
            $stat->date = $date;

            if (!$stat->validate()) {
                throw new \Exception("Неверный формат даты");
            }

            $stat->make();
        } catch (\Exception $e) {
            $this->stderr(Console::ansiFormat($e->getMessage(), [Console::FG_RED]) . PHP_EOL);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
