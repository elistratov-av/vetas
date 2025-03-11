<?php

namespace app\common\components\inform;

use app\models\db\subscription\SubscriptionLog;
use app\models\db\subscription\SubscriptionLogPets;
use app\modules\v2\modules\files\models\FilesModel;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\log\DbTarget;
use yii\log\LogRuntimeException;

class SubscriptionLogTarget extends DbTarget
{
    public $logTable = 'subscription.log';

    /**
     * @throws LogRuntimeException
     * @throws \yii\db\Exception
     */
    public function export()
    {
        if ($this->db->getTransaction()) {
            // create new database connection, if there is an open transaction
            // to ensure insert statement is not affected by a rollback
            $this->db = clone $this->db;
        }

        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    //На деле не логируем эксепшены
                    $text = (string)$text;
                }
            }
            if (is_string($text)) {
                //Старая реализация. На всякий случай оставил.
                @list(
                    $event_id, $event_code, $to, $is_success,
                    $params, $id_author, $id_violation,
                    $file_token, $attached_files_ids, $id_owner,
                    $id_initiator, $id_pet, $id_visit, $id_violation_history,
                    $error
                    ) = explode('|', $text);
                $attached_files_ids = unserialize($attached_files_ids);
            } elseif (is_array($text)) {
                //Новая реализация
                extract($text);
            } else {
                throw new LogRuntimeException('Unknown log message');
            }

            // Если передавался пароль в данных, закодим его перед сохранением лога
            if (!empty($params)
                && is_array($params)
                && array_key_exists('data', $params)
                && array_key_exists('password', $params['data'])) {

                $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
                if (empty($keyString)) {
                    throw new InvalidConfigException('You should specify passwordEncryptionKey');
                }
                $key = base64_decode($keyString);
                $params['data']['password'] = base64_encode(\Yii::$app->security->encryptByKey($params['data']['password'], $key));
            }

            if (!empty($params)) {
                $params = json_encode($params);
            }

            $log = (new SubscriptionLog());
            $log->log_time = date('Y-m-d H:i:s', $timestamp);
            $log->event_id = (!empty($event_id)) ? $event_id : null;
            $log->event_code = (!empty($event_code)) ? $event_code : null;
            $log->to = (!empty($to)) ? $to : null;
            $log->is_success = is_string($is_success) ? $is_success == 'true' : $is_success;
            $log->params = (!empty($params)) ? $params : null;
            $log->error = (!empty($error)) ? $error : null;
            $log->id_author = (!empty($id_author)) ? intval($id_author) : null;
            $log->id_violation = (!empty($id_violation)) ? intval($id_violation) : null;
            $log->attached_files_token = (!empty($files_token)) ? $files_token : null;
            $log->id_owner = (!empty($id_owner)) ? intval($id_owner) : null;
            $log->id_initiator = (!empty($id_initiator)) ? intval($id_initiator) : null;
            $log->id_pet = (!empty($id_pet)) ? intval($id_pet) : null;
            $log->id_visit = (!empty($id_visit)) ? intval($id_visit) : null;
            $log->id_violation_history = (!empty($id_violation_history)) ? intval($id_violation_history) : null;
            if ($log->save()) {
                if (!empty($attached_files_ids) && is_array($attached_files_ids)) {
                    $logWithSameTokenCount = SubscriptionLog::find()->where(['attached_files_token' => $files_token])->count();
                    if ($logWithSameTokenCount === 1) {
                        $filesModel = (new FilesModel());
                        foreach ($attached_files_ids as $fileId) {
                            $filesModel->attach($fileId, $log->id, 'subscription_log');
                        }
                    }
                }
                //ToDo Пока используется поле id_pet будем дублировать. Впоследствии можно убрать
                if (!empty($id_pet)) {
                    $pets[] = $id_pet;
                }
                if (!empty($pets)) {
                    foreach ($pets as $id_pet) {
                        $log_pets = new SubscriptionLogPets([
                            'id_log' => $log->id,
                            'id_pet' => $id_pet
                        ]);
                        $log_pets->save();
                    }
                }
                continue;
            }
            throw new LogRuntimeException('Unable to export log through database!');
        }
    }
}
