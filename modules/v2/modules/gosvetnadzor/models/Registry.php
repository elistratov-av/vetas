<?php

namespace app\modules\v2\modules\gosvetnadzor\models;

use app\common\components\asurService\ASURService;
use app\common\components\asurService\LocalDocumentStorage;
use app\common\components\asurService\SendTaskJob;
use app\common\components\asurService\TaskNumber;
use app\models\db\asur\Task;
use app\models\db\asur\TaskLog;
use app\models\db\PetOwners;
use Ramsey\Uuid\Uuid;
use yii\queue\Queue;
use yii\web\BadRequestHttpException;

class Registry
{
    /**
     * @param $id_owner
     * @param string $paramType
     * @param string $taskType
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function createTask($id_owner, string $paramType, string $taskType = Task::TYPE_FILE_REQUEST, $doc_id=null)
    {
        if (!$owner = PetOwners::findOne(['id' => $id_owner])) {
            throw new BadRequestHttpException("Не найден владелец животного");
        }

        if ($taskType === Task::TYPE_FILE_REQUEST && !$owner->entrepreneur && !$owner->is_legal) {
            throw new BadRequestHttpException("Неверный тип владельца");
        }

        $task = $this->getLastTask($id_owner, $taskType);

        if ($task && !in_array($task->status, [Task::STATUS_FINISHED, Task::STATUS_ERROR])) {
            throw new BadRequestHttpException("Дождитесь выполнения предыдущей заявки");
        }

        switch ($paramType) {
            case ASURService::PARAM_INN:
                if (empty($owner->inn)) {
                    throw new BadRequestHttpException("У владельца не указан ИНН");
                }
                $value = $owner->inn;
                break;

            case ASURService::PARAM_OGRN:
                if (empty($owner->ogrn)) {
                    throw new BadRequestHttpException("У владельца не указан ОГРН");
                }
                $value = $owner->ogrn;
                break;

            case ASURService::PARAM_SNILS:
                if (empty($owner->snils)) {
                    throw new BadRequestHttpException('У владельца не указан СНИЛС');
                }
                $value = $owner->snils;
                break;

            default:
                throw new BadRequestHttpException("Неверное значение поля тип");
        }

        return \Yii::$app->db->transaction(function() use($owner, $paramType, $value, $taskType, $doc_id){
            $number = TaskNumber::getNumber();
            $task = new Task([
                'message_id' => Uuid::uuid4()->toString(),
                'task_id' => (string)Uuid::uuid4(),
                'task_number' => (string)(new TaskNumber($number)),
                'number' => $number,
                'status' => Task::STATUS_NEW,
                'id_owner' => $owner->id,
                'task_type' => $taskType,
                'doc_id' => $doc_id,
            ]);

            if (!$task->validate()) {
                throw new BadRequestHttpException('Ошибка отправки запроса на выписку');
            }
            $task->save(false);
            $this->pushTask($task, $paramType, $value);

            \Yii::info(
                "{$task->message_id}|{$task->task_id}|{$task->task_number}: отправлено в АС УР",
                'asur'
            );
            return true;
        });
    }

    /**
     * @param $id_owner
     * @return array
     * @throws BadRequestHttpException
     */
    public function getStatus($id_owner)
    {
        if (!$owner = PetOwners::findOne(['id' => $id_owner])) {
            throw new BadRequestHttpException('Не найден пользователь');
        }

        if (!$task = $this->getLastTask($id_owner, Task::TYPE_FILE_REQUEST)) {
            return [
                "status" => Task::STATUS_UNAVAILABLE,
                "comment" => Task::getStatusComment(Task::STATUS_UNAVAILABLE),
                "file" => null,
                "status_date" => null
            ];
        }

        if (!empty($task->file)) {
            /** @var LocalDocumentStorage $storage */
            $storage = \Yii::$app->asurStorage;
            $file = $storage->getFileUri($task->file);
        }  else {
            $file = null;
        }

        if ($task->status == Task::STATUS_ERROR) {
            $log = TaskLog::find()
                ->where(['task_id' => $task->id])
                ->andWhere([
                    'status_code' => [
                        TaskLog::STATUS_ERROR, TaskLog::STATUS_NOT_FOUND, TaskLog::STATUS_REQUEST_ERROR,
                        TaskLog::STATUS_EXPIRED, TaskLog::STATUS_FORBIDDEN
                    ]
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(1)
                ->one();

            if ($log) {
                $comment = $log->status_note;
            } else {
                $comment = Task::getStatusComment($task->status);
            }
        } else {
            $comment = Task::getStatusComment($task->status);
        }

        return [
            "status" => $task->status,
            "comment" => $comment,
            "file" => $file,
            "status_date" => \DateTime::createFromFormat('Y-m-d H:i:s', $task->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    protected function pushTask(Task $task, string $type, string $value)
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->asur_queue;
        $queue->push(new SendTaskJob([
            'task_id' => $task->task_id,
            'type' => $type,
            'value' => $value
        ]));
    }

    /**
     * @param integer $id_owner
     * @param string $task_type
     * @return Task|null
     */
    protected function getLastTask(string $id_owner, string $task_type)
    {
        return Task::find()
            ->where(['id_owner' => $id_owner])
            ->andWhere(['task_type' => $task_type])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }
}
