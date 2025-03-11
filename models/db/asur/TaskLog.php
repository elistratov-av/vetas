<?php

namespace app\models\db\asur;

use app\models\db\ActiveRecord;

/**
 * This is the model class for table "asur.task_log".
 *
 * @property int $id
 * @property int $task_id
 * @property int $status_code
 * @property string $status_note
 * @property string $created_at
 * @property string $updated_at
 */
class TaskLog extends ActiveRecord
{
    const
        STATUS_ERROR = 1002,
        STATUS_IN_PROCESS = 1003,
        STATUS_RESULT = 1004,
        STATUS_NOT_FOUND = 1005,
        STATUS_REQUEST_ERROR = 1006,
        STATUS_SERVICE_UNAVAILABLE = 1007,
        STATUS_EXPIRED = 1008,
        STATUS_FORBIDDEN = 1009
    ;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asur.task_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'status_code'], 'default', 'value' => null],
            [['task_id', 'status_code'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['status_note'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'status_code' => 'Status Code',
            'status_note' => 'Status Note',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
