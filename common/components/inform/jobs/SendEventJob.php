<?php

namespace app\common\components\inform\jobs;

use app\common\components\inform\events\ConfirmEmailEvent;
use app\common\components\inform\events\NewsletterInfoEvent;
use app\common\components\inform\events\NewsletterReceptionEvent;
use app\common\components\inform\events\OneTimePasswordEvent;
use app\common\components\inform\events\VisitTransferedEvent;
use app\common\components\inform\SpkService;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\queue\JobInterface;

class SendEventJob extends BaseObject implements JobInterface
{
    /** @var string */
    public $event_id;

    /** @var  string */
    public $event_code;

    /** @var  string */
    public $date_time;

    /** @var array */
    public $to;

    /** @var array */
    public $data;

    /** @var integer */
    public $id_violation;

    /** @var integer */
    public $id_author;

    /** @var array */
    public $attached_files_ids;

    /** @var string */
    public $files_token;

    /** @var integer */
    public $id_owner;

    /** @var integer */
    public $id_initiator;

    /** @var integer */
    public $id_pet;

    /** @var array */
    public $pets;

    /** @var integer */
    public $id_visit;

    /** @var integer */
    public $id_violation_history;

    /** @var bool */
    public $is_admin_notification;

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        try {
            $params = [
                'event_id' => $this->event_id,
                'event_code' => $this->event_code,
                'date_time' => $this->date_time,
                'to' => $this->to,
                'data' => $this->data,
                'id_violation' => $this->id_violation,
                'id_author' => $this->id_author,
                'attached_files_ids' => $this->attached_files_ids,
                'files_token' => $this->files_token,
                'id_owner' => $this->id_owner,
                'id_initiator' => $this->id_initiator,
                'id_pet' => $this->id_pet,
                'id_visit' => $this->id_visit,
                'id_violation_history' => $this->id_violation_history,
                'pets' => $this->pets
            ];

            $msg  = Console::ansiFormat(date('Y-m-d H:i:s'), [Console::FG_YELLOW]);
            $msg .= " ";
            $msg .= Console::ansiFormat(json_encode($params['to']), [Console::FG_GREEN]);
            $msg .= " ";
            $msg .= Console::ansiFormat("$this->event_code", [Console::FG_CYAN]);

            Console::stdout($msg . PHP_EOL);

            /** @var SpkService $service */
            $service = \Yii::$app->spkService;

            if ($this->event_code != ConfirmEmailEvent::EVENT_CODE
                && $this->event_code != OneTimePasswordEvent::EVENT_CODE
                && $this->event_code != VisitTransferedEvent::EVENT_CODE
                && $this->event_code != NewsletterInfoEvent::EVENT_CODE
                && $this->event_code != NewsletterReceptionEvent::EVENT_CODE
                && !$this->is_admin_notification) {
                $service->checkSubscription($params);
            }

            // Перед отправкой в СПК расшифруем пароль
            if ($this->event_code == OneTimePasswordEvent::EVENT_CODE) {
                $params['data']['password'] = $this->decryptPassword();
            }

            $service->sendEvent($params);
        } catch (\Exception $e) {
            //print_r($e->getTraceAsString());
            Console::stdout(Console::ansiFormat($e->getMessage() . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * @return bool|string
     * @throws InvalidConfigException
     */
    private function decryptPassword()
    {
        $keyString = ArrayHelper::getValue(\Yii::$app->params, 'passwordEncryptionKey');
        if (empty($keyString)) {
            throw new InvalidConfigException('You should specify passwordEncryptionKey');
        }
        $key = base64_decode($keyString);
        return \Yii::$app->security->decryptByKey(base64_decode($this->data['password']), $key);
    }
}
