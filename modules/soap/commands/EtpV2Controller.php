<?php

namespace app\modules\soap\commands;

use app\common\models\VisitStatus;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\Visits;
use app\modules\soap\v2\queue\MosruStatusSender;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Console;

/**
 * Class EtpV2Controller
 * @package app\modules\soap\commands
 */
class EtpV2Controller extends Controller
{
    /**
     * Sent status to mosru
     *
     * @param string|int $id ENO number
     * @param string[] ...$statuses Etp statues
     * @return int
     */
    public function actionTest($id, ...$statuses)
    {
        $query = (new \yii\db\Query())->select('visit_id')->from('etp.message_v2')->where(['service_number' => $id])->one();
        if (!is_array($query)) {
            $this->stdout("Message $id not found" . PHP_EOL, Console::FG_RED);

            return 1;
        }
        ['visit_id' => $vId] = $query;
        $this->stdout(sprintf('Send statuses "%s" for "%s"...', implode(', ', $statuses), $vId) . PHP_EOL, Console::FG_GREEN);
        /** @var \yii\queue\Queue $queue */
        $queue = \Yii::$app->get('soap_queue_v2');
        $queue->push(json_encode([
            'visit_id' => (int)$vId,
            'etp_status' => $statuses,
        ]));
        $this->stdout("Done." . PHP_EOL, Console::FG_GREEN);

        return 0;
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
inner join "etp"."message_v2" "etp_message" ON etp_message.visit_id = v.id 
left join etp.status_log as l on l.visit_id = v.id
where (v."status" IN (:status_new, :status_changed)) AND (v."start_dttm" < :start_time) 
group by v.id
having not array[:status10190::varchar, :status10191::varchar] <@ array_agg(l.etp_status)
SQL;

        /** @var Visits $visits */
        $visits = Visits::find()
            ->where(
                new Expression("id in ({$sql})", [
                    ':status_new' => VisitStatus::NEW,
                    ':status_changed' => VisitStatus::CHANGED,
                    ':start_time' => date('Y-m-d H:i:s', $time),
                    ':status10190' => Status10190::CODE,
                    ':status10191' => Status10191::CODE,
                ])
            )
            ->all();

        if ($visits) {
            $this->stdout("Отправка статусов 10190 10191" . PHP_EOL, Console::FG_GREEN);
            foreach ($visits as $visit) {
                $this->stdout("Прием #{$visit->id}: дата начала {$visit->start_dttm}" . PHP_EOL, Console::FG_GREEN);
                $this->getSendStatusService()->visitTimeStart($visit->id);
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
        /** @var Visits $visits */
        $visits = Visits::find()
            ->join(
                'INNER JOIN',
                'etp.message_v2 as etp_message',
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
                $this->getSendStatusService()->visitDelay($visit->id);
            }
        }
    }

    /**
     * @return \app\modules\soap\v2\queue\MosruStatusSender
     */
    protected function getSendStatusService()
    {
        return new MosruStatusSender();
    }
}
