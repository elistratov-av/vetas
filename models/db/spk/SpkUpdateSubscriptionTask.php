<?php

namespace app\models\db\spk;

use Yii;

/**
 * This is the model class for table "spk.update_subscription_task".
 *
 * @property int $id
 * @property string $status
 * @property string $date_start Дата начала получения данных из ИС ПК
 * @property string $date_end Дата окончания получения данных из ИС ПК
 * @property int $current_offset Текущий offset для работающего задания, последний - для заверщенного
 * @property string $error_msg Описание ошибки (если есть)
 */
class SpkUpdateSubscriptionTask extends \yii\db\ActiveRecord
{
    const STATUS_PROGRESS = 'P'; // Идет обновление
    const STATUS_DONE = 'D'; // Обновление завершено
    const STATUS_FAIL = 'F'; // Ошибка
    const STATUS_OUTDATED = 'O'; // Старые задания, по которым списки подписок уже удалили

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'spk.update_subscription_task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_start', 'date_end'], 'safe'],
            [['current_offset'], 'default', 'value' => null],
            [['current_offset'], 'integer'],
            [['error_msg'], 'string'],
            [['status'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'current_offset' => 'Current Offset',
            'error_msg' => 'Error Msg',
        ];
    }
}
