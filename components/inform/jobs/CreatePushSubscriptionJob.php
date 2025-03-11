<?php

namespace app\common\components\inform\jobs;

use app\common\components\inform\SpkService;
use app\models\db\subscription\Subscriptions;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;

class CreatePushSubscriptionJob extends BaseObject implements JobInterface
{
    /** @var integer */
    public $id_contact;

    /** @var string */
    public $phone;

    /** @var string */
    public $sso_id;

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        try {
            $msg = "Создание подписки для {$this->phone}" . PHP_EOL;
            Console::stdout(Console::ansiFormat($msg, [Console::FG_GREEN]));

            /** @var SpkService $service */
            $service = \Yii::$app->spkService;
            $subscription_id = $service->createSubscriptionByPhone($this->phone, $this->sso_id);

            if (!$subscription = Subscriptions::findOne(['id_contact' => $this->id_contact])) {
                $subscription = new Subscriptions();
            }

            $subscription->id_contact = $this->id_contact;
            $subscription->subscribed = true;
            $subscription->subscription_id = $subscription_id;
            $subscription->save(false);
        } catch (\Exception $e) {
            Console::stdout(Console::ansiFormat($e->getMessage() . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
