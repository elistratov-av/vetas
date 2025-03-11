<?php

namespace app\commands;

use app\common\toolkit\SpecialistToolkit;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Контроллер для работы с данными для нагрузочного тестирование (добавление/удаление)
 * см. задачу 2228
 * Class HighloadTestController
 * @package app\commands
 */
class ToolkitController extends Controller
{
    /**
     * Добавление специалистов, с расписанием для организации
     * Организация и ее рабочие смены добавляются вручную
     *
     * @param integer $id_organization ID организации
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionAddSpecialists($id_organization, $limit)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $toolkit = new SpecialistToolkit();
            $toolkit->add($id_organization, $limit);

            $transaction->commit();
            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Удаление специалистов/пользователей из организации
     * @param $id_organization
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionDeleteSpecialists($id_organization)
    {
        $db = \Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $toolkit = new SpecialistToolkit();
            $toolkit->delete($id_organization);

            $transaction->commit();
            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Удаление специалистов/пользователей из организации
     * @param $id_organization
     * @return int
     */
    public function actionLoginUsers($id_organization)
    {
        try {
            $toolkit = new SpecialistToolkit();
            $toolkit->loginUsers($id_organization);

            return ExitCode::OK;
        } catch (\Exception $e) {
            Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    public function actionGen()
    {
        $bytes = \Yii::$app->security->generateRandomKey();
        echo PHP_EOL . base64_encode($bytes) . PHP_EOL;
    }

}
