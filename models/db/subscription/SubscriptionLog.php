<?php

namespace app\models\db\subscription;

use app\models\db\ActiveRecord;
use app\models\db\Files;

/**
 * @property integer $id
 * @property string $log_time
 * @property string $event_id
 * @property string $event_code
 * @property string $to
 * @property string $params
 * @property string $error
 * @property string $status_push
 * @property string $status_email
 * @property string $attached_files_token
 * @property bool $is_success
 * @property bool $is_unsubscribed_email
 * @property bool $is_unsubscribed_push
 * @property integer $click_count_email
 * @property integer $click_count_push
 * @property integer $id_author
 * @property integer $id_violation
 * @property integer $id_owner
 * @property integer $id_initiator
 * @property integer $id_pet
 * @property integer $id_visit
 * @property integer $id_violation_history
 *
 * @property Files[] $files
 */
class SubscriptionLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription.log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['log_time'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['event_id', 'event_code', 'event_code', 'to', 'params', 'error', 'status_push', 'status_email', 'attached_files_token'], 'string'],
            [['is_success', 'is_unsubscribed_email', 'is_unsubscribed_push'], 'boolean'],
            [[
                'click_count_email',
                'click_count_push',
                'id_author',
                'id_violation',
                'id_owner',
                'id_initiator',
                'id_pet',
                'id_visit',
                'id_violation_history'
            ], 'integer']
        ];
    }

    /**
     * @return false
     */
    public function isReadOnly()
    {
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::class, ['entity_id' => 'id'])
            ->where(['entity_type' => 'subscription_log']);
    }
}
