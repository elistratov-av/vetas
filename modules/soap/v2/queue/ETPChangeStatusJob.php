<?php

namespace app\modules\soap\v2\queue;

use app\modules\soap\models\etp\ETPHelper;
use app\modules\soap\models\etp\status\Status1050_2;
use app\modules\soap\models\etp\status\Status1168;
use app\modules\soap\models\etp\status\StatusReasonInterface;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Класс для обработки событий в очереди для отправки статусов в ЕТП
 *
 * Примеры отправляемых статусов (порядок важен!)
 * Создание приема - статусы 1050 10090 10091
 * Прием взяли в работу - 10190 10191 1075
 * Прием изменили в клинике - 8021
 * Прием изменили по инициативе заявителя через mos.ru - 1053
 * Отмена приема в клинике - 10190 101901 1080.1
 * Отмена приема по инициативе заявителя через mos.ru - 10190 10191 1090
 *
 * Class ETPChangeStatusJob
 * @package app\modules\soap\v2\queue
 */
class ETPChangeStatusJob extends BaseObject implements JobInterface
{
    /**
     * ID приема в БД
     * @var int
     */
    public $visit_id;
    /**
     * Список статусов для отправки в ЕТП
     * @var array
     */
    public $etp_status;
    /**
     * Uuid сообщения (встречается не везде)
     * @var string|null
     */
    public $status_id;
    /**
     * @var string
     */
    public $error_message;
    /**
     * @var string
     */
    public $note;
    /**
     * @var string
     */
    public $payment_url;

    /**
     *
     * @var string
     */
    private $log_category = 'soap_queue_v2';

    /**
     * @param Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        sleep(1);
        try {
            $sender = new ETPVisitStatusSender($this->visit_id);

            $msg = Console::ansiFormat(date('Y-m-d H:i:s'), [Console::FG_YELLOW]);
            $msg .= " ";
            $msg .= Console::ansiFormat($sender->getServiceNumber(), [Console::FG_RED]);
            $msg .= " ";
            $msg .= Console::ansiFormat("прием #{$this->visit_id}", [Console::FG_CYAN]);
            Console::stdout($msg . PHP_EOL);
            foreach ($this->etp_status as $etp_status_code) {
                $status = ETPHelper::getStatus($etp_status_code);
                if ($etp_status_code == Status1168::CODE && !empty($this->note)) {
                    $status->setNote($this->note);
                }
                if ($etp_status_code == Status1050_2::CODE) {
                    $status->setPaymentUrl($this->payment_url);
                }
                Console::stdout(Console::ansiFormat("Отправка статуса {$status->getCode()}" . PHP_EOL, [Console::FG_GREEN]));

                $sender->sendStatus($status, $this->status_id);

                $code = $status->getCode() . (($status instanceof StatusReasonInterface) ? $status->getReasonCode() : '');
                \Yii::info("{$sender->getServiceNumber()}|{$this->visit_id}|{$code}|{$this->error_message}", $this->log_category);

                // По просьбе ЕТП, чтобы разделить по времени статусы, чтобы они точно обработались в нужном порядке
                sleep(1);
            }
        } catch (\Exception $e) {
            Console::stdout(Console::ansiFormat($e->getMessage() . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            \Yii::error($e->getTraceAsString(), $this->log_category);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
