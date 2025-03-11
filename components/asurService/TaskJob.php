<?php

namespace app\common\components\asurService;

use app\models\db\asur\Task;
use yii\base\BaseObject;
use yii\helpers\Console;

abstract class TaskJob extends BaseObject
{
    /**
     * @param Task $task
     * @param string $status
     */
    protected function updateTaskStatus(Task $task, string $status)
    {
        $task->status = $status;
        $task->save(false);
    }

    /**
     * @param Task $task
     * @param null|string $message
     */
    protected function log(Task $task, ?string $message = null)
    {
        $msg  = Console::ansiFormat(date('Y-m-d H:i:s'), [Console::FG_YELLOW]);
        $msg .= " ";
        $msg .= Console::ansiFormat($task->message_id, [Console::FG_GREEN]);
        $msg .= " ";
        $msg .= Console::ansiFormat($task->task_id, [Console::FG_CYAN]);
        $msg .= " ";
        $msg .= Console::ansiFormat($task->task_number, [Console::FG_PURPLE]);

        if (!is_null($message)) {
            $msg .= ": {$message}";
        }

        Console::output($msg);
    }
}
