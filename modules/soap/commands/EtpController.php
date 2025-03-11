<?php

namespace app\modules\soap\commands;

use app\common\models\VisitStatus;
use app\models\MosruNotification;
use app\modules\soap\models\etp\ETPVisitStatusSender;
use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\etp\status\Status103099;
use app\modules\soap\models\etp\status\Status1050;
use app\modules\soap\models\etp\status\Status1075;
use app\modules\soap\models\etp\status\Status1080;
use app\modules\soap\models\etp\status\Status1080_1;
use app\modules\soap\models\etp\status\Status1080_2;
use app\modules\soap\models\etp\status\Status1169;
use app\modules\soap\models\Visits;
use app\modules\soap\Module;
use app\modules\soap\queue\ETPChangeStatusJob;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\helpers\Console;

/**
 * Отправка статусных сообщений в ЕТП при смене статуса приема
 *
 * Class EtpController
 * @package app\commands
 */
class EtpController extends Controller
{
    /**
     * @return Module
     */
    protected function getSoapModule()
    {
        return \Yii::$app->getModule('soap');
    }

    /**
     * Проверка записей на прием с ЕТП - если время приема наступило, отправляем статус 10190 10191
     */
    public function actionCheckVisitsStart()
    {
        $time = time();
        $this->stdout("Текущее время: " . date('Y-m-d H:i:s', $time) . PHP_EOL,
            Console::FG_GREEN, Console::BOLD);

        // приемы время которых началось
        $sql = <<<SQL
select 
    v.id
from "visits" as v
inner join "etp"."message" "etp_message" ON etp_message.visit_id = v.id 
left join etp.status_log as l on l.visit_id = v.id
where (v."status" IN (:status_new, :status_changed)) AND (v."start_dttm" < :start_time) 
group by v.id
having not array[:status10190::varchar, :status10191::varchar] <@ array_agg(l.etp_status)
SQL;

        $visits = Visits::find()
            ->where(
                new Expression("id in ({$sql})", [
                    ':status_new' => VisitStatus::NEW,
                    ':status_changed' => VisitStatus::CHANGED,
                    ':start_time' => date('Y-m-d H:i:s', $time),
                    ':status10190' => Status10190::CODE,
                    ':status10191' => Status10191::CODE
                ])
            )
            ->all();

        if ($visits) {
            $this->stdout("Отправка статусов 10190 10191" . PHP_EOL, Console::FG_GREEN);
            foreach ($visits as $visit) {
                $this->stdout("Прием #{$visit->id}: дата начала {$visit->start_dttm}" . PHP_EOL, Console::FG_GREEN);
                MosruNotification::visitTimeStart($visit->id);
            }
        }
    }

    /**
     * Проверка записей на прием с ЕТП - по прошествии 24 часов с момента начала приема отправляем статус 1080.2
     */
    public function actionCheckVisitsTimeout()
    {
        $time = time();
        $this->stdout("Текущее время: " . date('Y-m-d H:i:s', $time) . PHP_EOL,
            Console::FG_GREEN, Console::BOLD);

        // необработанные приемы, с даты начала которых прошло более 24 часов
        $visits = Visits::find()
            ->join(
                'INNER JOIN',
                'etp.message as etp_message',
                'etp_message.visit_id = visits.id')
            ->where(['status' => [VisitStatus::NEW, VisitStatus::CHANGED]])
            ->andWhere(['<', 'start_dttm', date('Y-m-d H:i:s', $time - 60 * 60 * 24)])
            ->all();

        if ($visits) {
            $this->stdout("Отправка статуса 1080.2" . PHP_EOL, Console::FG_GREEN);
            foreach ($visits as $visit) {
                $visit->status = VisitStatus::TIMEOUT;
                $visit->save(false);
                $this->stdout("Прием #{$visit->id}: дата начала {$visit->start_dttm}" . PHP_EOL, Console::FG_GREEN);
                MosruNotification::visitDelay($visit->id);
            }
        }
    }

    /**
     * @param $id
     * @return int
     */
    public function actionTest($id)
    {
        if (!$visit = Visits::findOne(['id' => $id])) {
            $this->stdout(Console::ansiFormat("Прием #{$id} не найден" . PHP_EOL, [Console::FG_RED]));
            return ExitCode::UNSPECIFIED_ERROR;
        }
        MosruNotification::visitStart($visit->id);
        MosruNotification::visitCancelInClinic($visit->id);
    }

    public function actionCancelVisit()
    {

    }

    public function actionChangeVisit()
    {

    }

    /**
     * @param $visit_id
     * @return int
     */
    public function actionTriggerNewVisit($visit_id)
    {
        if (!$visit = Visits::findOne(['id' => $visit_id])) {
            $this->stdout(Console::ansiFormat("Прием #{$visit_id} не найден" . PHP_EOL, [Console::FG_RED]));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        MosruNotification::visitCreate($visit->id);

        return ExitCode::OK;
    }

    public function actionSendErrorStatus(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $this->stdout("Отправка статуса " . Status103099::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status103099(), Status103099::CODE);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Прием взят в работу до времени начала (отправляются сообщения со статусами: 10190 10191 1075)
     * @param int $visit_id
     * @return int
     */
    public function actionSendStartVisit(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $this->stdout("Отправка статуса " . Status10190::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status10190(), Status10190::CODE);

            $this->stdout("Отправка статуса " . Status10191::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status10191(),Status10191::CODE);

            $this->stdout("Отправка статуса " . Status1075::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status1075(), Status1075::CODE);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Наступило время начала приема (отправляются сообщения со статусами: 10190 10191)
     * @param int $visit_id
     * @return int
     */
    public function actionSendStartVisitTime(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $this->stdout("Отправка статуса " . Status10190::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status10190(), Status10190::CODE);

            $this->stdout("Отправка статуса " . Status10191::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status10191(), Status10191::CODE);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Прием взят в работу (отправляется сообщение со статусом 1075)
     * @param int $visit_id
     * @return int
     */
    public function actionSendFinishVisit(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $this->stdout("Отправка статуса " . Status1075::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status1075(), Status1075::CODE);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Пользователь не пришел на прием в течение 2х недель (отправляются сообщения со статусом: 1080.2)
     * @param int $visit_id
     * @return int
     */
    public function actionSetTimeoutStatus(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $visit = $sender->getVisit();
            $visit->status = VisitStatus::TIMEOUT;
            $visit->save(false);

            $this->stdout("Отправка статуса " . Status1080_2::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status1080_2(), Status1080_2::CODE);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Пользователь не пришел на прием в течение 2х недель (отправляются сообщения со статусом: 1080.2)
     * @param int $visit_id
     * @return int
     */
    public function actionSendTimeoutStatus(int $visit_id)
    {
        try {
            $this->stdout("Отправка статуса " . Status1080_2::CODE . PHP_EOL, Console::FG_GREEN);
            MosruNotification::visitDelay($visit_id);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Прием взят в работу (отправляется сообщение со статусом 1075)
     * @param int $visit_id
     * @return int
     */
    public function actionSendFinishVisitStatus(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $this->stdout("Отправка статуса " . Status1075::CODE . PHP_EOL, Console::FG_GREEN);
            MosruNotification::visitStart($visit_id);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Отправка статуса 1169
     * @param int $visit_id
     * @return int
     */
    public function actionSendStatus1169(int $visit_id)
    {
        try {
            $sender = new ETPVisitStatusSender($visit_id);

            $this->stdout("Отправка статуса " . Status1169::CODE . PHP_EOL, Console::FG_GREEN);
            $sender->sendStatus(new Status1169(), Status1169::CODE);
        } catch (\Exception $e) {
            $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED, Console::BOLD);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
