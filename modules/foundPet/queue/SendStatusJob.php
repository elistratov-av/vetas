<?php

namespace app\modules\foundPet\queue;

use app\modules\foundPet\Module;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;

/**
 * Class SendStatusJob
 * @package app\modules\foundPet\queue
 */
class SendStatusJob extends BaseObject implements JobInterface
{
    /**
     * Список статусов для отправки в ЕТП
     * @var array
     */
    public $statusCodes;
    /**
     * Данные статусов для отправки в ЕТП
     * @var array
     */
    public $data;
    /**
     * @var string
     */
    public $serviceNumber;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        try {
            $sender = new StatusSender($this->serviceNumber);

            $msg = Console::ansiFormat(date('Y-m-d H:i:s'), [Console::FG_YELLOW]);
            $msg .= " ";
            $msg .= Console::ansiFormat($this->serviceNumber, [Console::FG_RED]);
            Console::output($msg);
            foreach ($this->statusCodes as $statusCode) {
                // По просьбе ЕТП, чтобы разделить по времени статусы, чтобы они точно обработались в нужном порядке
                sleep(1);

                Console::stdout(Console::ansiFormat('Отправка статуса ' . $statusCode . PHP_EOL, [Console::FG_GREEN]));
                if (!isset($this->data[$statusCode])) {
                    $errorMessage = 'Отсутствуют данные для отправки статуса ' . $statusCode . ' для ' . $this->serviceNumber;
                    Console::stdout(Console::ansiFormat($errorMessage . PHP_EOL, [Console::FG_RED, Console::BOLD]));
                    \Yii::error($errorMessage, Module::LOG_CATEGORY);

                    return ExitCode::UNSPECIFIED_ERROR;
                }

                $sender->sendStatus($statusCode, $this->data[$statusCode]);

                // TODO
                // $code = $status->getCode() . (($status instanceof StatusReasonInterface) ? $status->getReasonCode() : '');
                // \Yii::info("{$sender->getServiceNumber()}|{$this->visit_id}|{$code}|{$this->error_message}", $this->log_category);
            }
        } catch (\Throwable $e) {
            Console::stdout(Console::ansiFormat($e->getMessage() . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            \Yii::error($e->getTraceAsString(), Module::LOG_CATEGORY);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
