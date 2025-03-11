<?php

namespace app\modules\soap\v2\queue;

use app\modules\soap\models\etp\ETPHelper;
use app\modules\soap\models\etp\status\StatusReasonInterface;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class ETPGeneralErrorJob
 * @package app\modules\soap\v2\queue
 */
class ETPGeneralErrorJob extends BaseObject implements JobInterface
{
    /**
     * @var string
     */
    public $service_number;
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
            $sender = new ETPVisitStatusSender(null, $this->service_number);

            $msg = Console::ansiFormat(date('Y-m-d H:i:s'), [Console::FG_YELLOW]);
            $msg .= " ";
            $msg .= Console::ansiFormat($sender->getServiceNumber(), [Console::FG_RED]);
            Console::stdout($msg . PHP_EOL);
            foreach ($this->etp_status as $etp_status_code) {
                $status = ETPHelper::getStatus($etp_status_code);
                Console::stdout(Console::ansiFormat("Отправка статуса {$status->getCode()}" . PHP_EOL, [Console::FG_GREEN]));

                $sender->sendStatus($status, $this->status_id);

                $code = $status->getCode() . (($status instanceof StatusReasonInterface) ? $status->getReasonCode() : '');
                \Yii::info("{$sender->getServiceNumber()}||{$code}|{$this->error_message}", $this->log_category);

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
