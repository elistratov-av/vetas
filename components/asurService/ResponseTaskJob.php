<?php

namespace app\common\components\asurService;

use app\common\soap\MessageParser;
use app\models\db\asur\Task;
use app\models\db\asur\TaskLog;
use app\models\db\PetOwners;
use yii\helpers\BaseFileHelper;
use yii\helpers\Console;
use yii\queue\JobInterface;
use app\common\websocket\asurWebsocketClient;

class ResponseTaskJob extends TaskJob implements JobInterface
{
    /** @var string */
    public $xml;

    /** @var Task */
    private $task;

    /** @var array */
    private $data;

    /** @var string */
    private $messageId;

    /** @var string */
    private $taskId;

    /** @var array */
    private $statusCode;

    /** @var string */
    private $statusNote;

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        try {
            $this->init();
            if (!$this->getTask()) {
                throw new \Exception('Не найдена запись с заданным TaskId ' . $this->taskId);
            }

            $this->log($this->task, "{$this->statusCode}: {$this->statusNote}");

            $this->process();
            \Yii::info("{$this->task->message_id}|{$this->task->task_id}|{$this->task->task_number}: {$this->statusNote}", 'asur');
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), 'asur');
            if (isset($this->task)) {
                $this->log($this->task, Console::ansiFormat($e->getMessage(), [Console::FG_RED, Console::BOLD]));
            } else {
                Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED, Console::BOLD]));
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->data = MessageParser::xmlAsArray($this->xml);
        if (!isset($this->data['CoordinateSendTaskStatusesMessage']) ||
            !isset($this->data['CoordinateSendTaskStatusesMessage']['CoordinateTaskStatusDataMessage'])
        ) {
            throw new \Exception('Невозможно обработать сообщение. Не найден блок CoordinateTaskStatusDataMessage');
        }

        $data = $this->data['CoordinateSendTaskStatusesMessage']['CoordinateTaskStatusDataMessage'];

        $this->messageId = (isset($data['MessageId'])) ? $data['MessageId'] : null;
        $this->taskId = (isset($data['TaskId'])) ? $data['TaskId'] : null;
        $this->statusCode = (isset($data['Status']) && isset($data['Status']['StatusCode']))
            ? $data['Status']['StatusCode'] : null;
        $this->statusNote = (isset($data['StatusNote'])) ? $data['StatusNote'] : '';
    }

    /**
     * @throws \Exception
     */
    public function process()
    {
        switch ($this->statusCode) {
            // ошибки
            case TaskLog::STATUS_NOT_FOUND:
                if ($this->task->task_type === Task::TYPE_PASSPORT_REQUEST) {
                    $this->returnDataNotFound();
                }
                $this->updateTaskStatus($this->task, Task::STATUS_ERROR);
                break;
            case TaskLog::STATUS_ERROR:
            case TaskLog::STATUS_REQUEST_ERROR:
            case TaskLog::STATUS_EXPIRED:
            case TaskLog::STATUS_FORBIDDEN:
                $this->updateTaskStatus($this->task, Task::STATUS_ERROR);
                break;

            // промежуточные статусы
            case TaskLog::STATUS_IN_PROCESS:
            case TaskLog::STATUS_SERVICE_UNAVAILABLE:
                $this->updateTaskStatus($this->task, Task::STATUS_IN_PROCESS);
                break;

            // результат
            case TaskLog::STATUS_RESULT:
                $taskType = $this->task->task_type;
                if ($taskType === Task::TYPE_FILE_REQUEST || $taskType === null) {
                    try {
                        $this->loadFile();
                    }  catch (\Exception $e) {
                        $this->updateTaskStatus($this->task, Task::STATUS_ERROR);
                        throw $e;
                    }
                }
                if ($taskType === Task::TYPE_PASSPORT_REQUEST) {
                    $this->returnPassportData();
                }
                $this->updateTaskStatus($this->task, Task::STATUS_FINISHED);
                break;

            default:
                throw new \Exception("Неизвестный статус ответа");
        }

        $this->logStatus();

        return;
    }

    /**
     *
     */
    protected function logStatus()
    {
        \Yii::info(
            "{$this->task->message_id}|{$this->task->task_id}|{$this->task->task_number}: {$this->statusCode}, {$this->statusNote}",
            'asur'
        );

        $log = new TaskLog([
            'task_id' => $this->task->id,
            'status_code' => $this->statusCode,
            'status_note' => $this->statusNote
        ]);
        $log->save(false);
    }

    /**
     * @return Task|null|static
     */
    protected function getTask()
    {
        if (!isset($this->task)) {
            $this->task = Task::findOne(['task_id' => $this->taskId]);
        }

        return $this->task;
    }

    /**
     * @throws \Exception
     */
    protected function loadFile()
    {
        if (!isset($this->data['CoordinateSendTaskStatusesMessage']['Files'])) {
            throw new \Exception('В ответе отсутствует блок Files');
        }

        $result = $this->data['CoordinateSendTaskStatusesMessage']['Files'];
        $documentId = $result['CoordinateFile']['FileIdInStore'];

        $remoteStorage = $this->getRemoteStorage();

        Console::output(Console::ansiFormat("Получение свойств документа"));
        $properties = $remoteStorage->getDocumentProperties($documentId);
        if (!isset($properties['MimeType'])) {
            throw new \Exception('Не удалось определить тип документа');
        }

        $extensions = BaseFileHelper::getExtensionsByMimeType($properties['MimeType']);
        if (empty($extensions)) {
            throw new \Exception('Неизвестный тип документа');
        }
        $ext = $extensions[0];
        $fileName = "{$documentId}.{$ext}";

        Console::output(Console::ansiFormat("Загрузка документа"));
        $document = $remoteStorage->getDocument($documentId);

        Console::output(Console::ansiFormat("Сохранение документа в локальное хранилище"));
        $path = $this->getLocalStorage()->save($fileName, $document);
        Console::output(Console::ansiFormat($path));

        $this->task->file = $fileName;
    }

    /**
     * @throws \WebSocket\BadOpcodeException
     */
    protected function returnPassportData()
    {
        $data = $this->data['CoordinateSendTaskStatusesMessage']['CoordinateTaskStatusDataMessage']['Result']['XmlView']['OutPassport'];
        (new asurWebsocketClient())->passDataToWebsocketServer($data);
    }

    /**
     * @throws \WebSocket\BadOpcodeException
     */
    protected function returnDataNotFound()
    {
        /** @var PetOwners $owner */
        $owner = PetOwners::find()->where(['id' => $this->task->id_owner])->one();
        (new asurWebsocketClient())->passNotExistToWebsocketServer($owner->snils);
    }

    /**
     * @return RemoteDocumentStorage
     */
    public function getRemoteStorage()
    {
        return \Yii::$app->fnsStorage;
    }

    /**
     * @return LocalDocumentStorage
     */
    public function getLocalStorage()
    {
        return \Yii::$app->asurStorage;
    }
}
