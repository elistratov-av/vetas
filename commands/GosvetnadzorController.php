<?php

namespace app\commands;

use app\modules\v2\modules\gosvetnadzor\cron\ViolationCheckIdentification;
use app\modules\v2\modules\gosvetnadzor\cron\ViolationCheckVaccination;
use app\modules\v2\modules\gosvetnadzor\cron\ViolationCheckClosed;
use yii\console\Controller;

/**
 * Автоматическая проверка нарушений и заведение их в системе
 * @package app\commands
 */
class GosvetnadzorController extends Controller
{

    /**
     * Автоматическое создание нарушений по вакцинации от бешенства
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     */
    public function actionAutoCheckRabiesVaccination()
    {
        $check = new ViolationCheckVaccination();

        $check->checkRabiesVaccinationAndCreateViolations();
    }

    /**
     * Автоматическое создание нарушений по вакцинации от лептоспироза (для собак)
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     */
    public function actionAutoCheckLeptospirosisVaccination()
    {
        $check = new ViolationCheckVaccination();

        $check->checkLeptospirosisVaccinationAndCreateViolations();
    }

    /**
     * Автоматическое создание нарушений по идетификации
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     */
    public function actionAutoCheckIdentification()
    {
        $check = new ViolationCheckIdentification();

        $check->checkIdentificationAndCreateViolations();
    }

    /**
     * Автоматическая отмена закрытых нарушений
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     */
    public function actionCheckAndCloseViolations()
    {
        $check = new ViolationCheckClosed();

        $check->checkAndCloseViolations();
    }

    /**
     * Автоматическая отмена закрытых нарушений по вакцинации
     */
    public function actionCheckAndCloseVaccinationViolations()
    {
        $check = new ViolationCheckClosed();

        $check->checkAndCloseVaccinationViolations();
    }

    /**
     * Автоматическая отмена закрытых нарушений по идентификации
     */
    public function actionCheckAndCloseIdentificationViolations()
    {
        $check = new ViolationCheckClosed();

        $check->checkAndCloseIdentificationViolations();
    }

    /**
     * Автоматическая отмена закрытых нарушений с истекшим сроком обработки идентификации
     */
    public function actionCheckAndCloseExpiredViolations()
    {
        $check = new ViolationCheckClosed();

        $check->checkAndCloseExpiredViolations();
    }
}
