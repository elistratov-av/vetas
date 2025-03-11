<?php

namespace app\common\components\asurService;

use app\models\db\asur\Task;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;
use yii\queue\Queue;

class SendTaskJob extends TaskJob implements JobInterface
{
    /** @var string */
    public $task_id;

    /** @var string */
    public $type;

    /** @var string */
    public $value;

    /**
     * @param Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        try {
            if (!$task = Task::findOne(['task_id' => $this->task_id])) {
                Console::error(Console::ansiFormat("Не найден task #{$this->task_id}", [Console::FG_RED, Console::BOLD]));
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->sendTask($task);
            $this->updateTaskStatus($task, Task::STATUS_SENT);

            $this->log($task, "отправлено в АС УР");
            \Yii::info("{$task->message_id}|{$task->task_id}|{$task->task_number}: отправлено в АС УР", 'asur');
        } catch (\Exception $e) {
            \Yii::error(
                "{$task->message_id}|{$task->task_id}|{$task->task_number}: не удалось отправить сообщение: {$e->getMessage()}",
                'asur'
            );

            $this->updateTaskStatus($task, Task::STATUS_ERROR);

            $this->log($task, "не удалось отправить сообщение: {$e->getMessage()}");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * @return ASURService
     */
    protected function getAsurService()
    {
        return \Yii::$app->asurService;
    }

    /**
     * @param Task $task
     * @throws \Exception
     */
    protected function sendTask(Task $task)
    {
        $asurService = $this->getAsurService();

        switch ($this->type) {
            case ASURService::PARAM_INN:
                if (empty($task->owner->inn)) {
                    throw new \Exception("У владельца не указан ИНН");
                }

                if ($task->owner->entrepreneur) {
                    $DocumentTypeCode = ASURService::TYPE_EGRIP;
                } elseif ($task->owner->is_legal) {
                    $DocumentTypeCode = ASURService::TYPE_EGRUL;
                } else {
                    throw new \Exception("Неверный тип владельца. Владелец должен быть ИП или юр. лицом");
                }
                $serviceProperties = [
                    ASURService::PARAM_INN => $this->value
                ];
                break;

            case ASURService::PARAM_OGRN:
                if (!$task->owner->is_legal) {
                    throw new \Exception(
                        "Неверный тип владельца. Владелец должен быть юр. лицом, для запроса выписки из ЕГРЮЛ"
                    );
                }

                if (empty($task->owner->ogrn)) {
                     throw new \Exception("У владельца не указан ОГРН");
                }

                $DocumentTypeCode = ASURService::TYPE_EGRUL;
                $serviceProperties = [
                    ASURService::PARAM_OGRN => $this->value
                ];
                break;

            case ASURService::PARAM_SNILS:
                if (empty($task->owner->snils)) {
                    throw new \Exception("У владельца не указан СНИЛС");
                }

                $DocumentTypeCode = ASURService::TYPE_PASSPORT;
                $serviceProperties = [
                    ASURService::PARAM_SNILS => $this->value
                ];
                break;

            default:
                throw new \Exception("Неверный тип");
        }
	/**
		 * Тестовый контур требует дополнительные параметры
		 */
	
		if (!empty($task->doc_id)) {
			$serviceProperties['doc_id'] = $task->doc_id;
			$serviceProperties['testmsg'] = null;
		}
        
        $asurService->sendMessage([
            'Task' => [
                'MessageId' => $task->message_id,
                'TaskId' => $task->task_id,
                'TaskNumber' => $task->task_number,
                'TaskDate' => (new \DateTime($task->created_at))->format(DATE_RFC3339_EXTENDED),
                'Responsible' => $asurService->Responsible,
                'Department' => $asurService->Department,
                'FunctionTypeCode' => $asurService->FunctionTypeCode,
            ],
            'Data' => [
                // 13017 - Выписка из ЕГРЮЛ
                // 13018 - Выписка из ЕГРИП
                // 10209 - Паспорт
                'DocumentTypeCode' => $DocumentTypeCode,
                'Parameter' => [
                    'ServiceProperties' => $serviceProperties
                ],
                'IncludeXmlView' => true,
                'IncludeBinaryView' => true
            ]
        ]);
    }
}
