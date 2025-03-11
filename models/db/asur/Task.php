<?php

namespace app\models\db\asur;

use app\common\components\asurService\LocalDocumentStorage;
use app\models\db\ActiveRecord;
use app\models\db\PetOwners;

/**
 * This is the model class for table "asur.task".
 *
 * @property int $id
 * @property string $message_id
 * @property string $task_id
 * @property string $task_number
 * @property integer $number
 * @property integer $id_owner
 * @property string $created_at
 * @property string $updated_at
 * @property string $status
 * @property string $file
 * @property string $task_type
 * @property string $doc_id

 *
 * @property string $statusComment
 *
 * @property PetOwners $owner
 */
class Task extends ActiveRecord
{
    const
        STATUS_UNAVAILABLE = 'U',
        STATUS_NEW = 'N',
        STATUS_SENT = 'S',
        STATUS_IN_PROCESS = 'P',
        STATUS_FINISHED = 'F',
        STATUS_ERROR = 'E'
    ;

    const
        TYPE_FILE_REQUEST = 'file',
        TYPE_PASSPORT_REQUEST = 'passport'
    ;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asur.task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['message_id', 'task_id', 'task_number', 'task_type', 'doc_id'], 'string', 'max' => 255],
            [['message_id', 'task_id', 'task_number', 'id_owner', 'number', 'task_type'], 'required'],
            [['id_owner', 'number'], 'integer'],
            [['id_owner'], 'exist', 'skipOnError' => true, 'targetClass' => PetOwners::class, 'targetAttribute' => ['id_owner' => 'id']],
            [['status'], 'string', 'max' => 1],
            ['status', 'in', 'range' => [self::STATUS_NEW, self::STATUS_SENT, self::STATUS_FINISHED, self::STATUS_ERROR]],
            [['task_id', 'task_number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'task_id' => 'Task ID',
            'task_number' => 'Task Number',
            'number' => 'Number',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
            'task_type' => 'Task type',
            'doc_id' => 'doc_id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner']);
    }

    /**
     * @param string $status
     * @return string
     */
    public static function getStatusComment(string $status) :string
    {
        $text = '';
        switch ($status) {
            case self::STATUS_NEW:
                $text = "Создан запрос";
                break;

            case self::STATUS_SENT:
                $text = "Запрос отправлен";
                break;

            case self::STATUS_IN_PROCESS:
                $text = "В обработке";
                break;

            case self::STATUS_FINISHED:
                $text = "Выписка получена";
                break;

            case self::STATUS_ERROR:
                $text = "Ошибка запроса";
                break;

            case self::STATUS_UNAVAILABLE:
                $text = "Необходимо выполнить запрос на выписку";
                break;
        }

        return $text;
    }

    public function afterDelete()
    {
        parent::afterDelete();

        if ($this->file) {
            /** @var LocalDocumentStorage $storage */
            $storage = \Yii::$app->asurStorage;
            $storage->delete($this->file);
        }
    }
}
