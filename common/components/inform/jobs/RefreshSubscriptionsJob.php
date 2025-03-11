<?php

namespace app\common\components\inform\jobs;

use app\common\components\inform\SpkService;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\queue\JobInterface;
use yii\queue\Queue;

class RefreshSubscriptionsJob extends BaseObject implements JobInterface
{
    /** @var string */
    public $sso_id;

    /** @var string */
    public  $id_owner;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return int
     */
    public function execute($queue)
    {
        try {
            // Находим пользователя по id а не по sso_id, т.к. sso_id может быть не уникален
            $msg  = Console::ansiFormat(date('Y-m-d H:i:s'), [Console::FG_YELLOW]);
            $msg .= " ";
            $msg .= Console::ansiFormat("Обновление контактов для пользователя #{$this->id_owner} ", [Console::FG_GREEN]);
            Console::stdout($msg);

            if (!$owner = PetOwners::findOne(['id' => $this->id_owner])) {
                throw new \Exception("Пользователь не найден");
            }

            // обрабатываем только e-mail адреса
            $contacts = Contacts::find()
                ->where(['entity_type' => 'pet_owner'])
                ->andWhere(['entity_id' => $owner->id])
                ->andWhere(['id_contact_type' => ContactTypes::findOne(['type' => ContactTypes::TYPE_EMAIL])->id])
                ->all();

            if (empty($contacts)) {
                throw new \Exception("У пользователя не найдены e-mail контакты для обновления подписок");
            }

            /** @var SpkService $service */
            $service = \Yii::$app->spkService;

            foreach ($contacts as $contact) {
                $msg = "\nКонтакт {$contact->name}: ";
                $subscriptions = $service->getSubscriptionsForContact($contact->getTypeForInformation(), $contact->name, null);
                foreach ($subscriptions as $subscription) {
                    if (!$subscription) {
                        $msg .= "подписки не найдено";
                    } else {
                        if (!isset($subscription->options->ssoid) || $subscription->options->ssoid != $this->sso_id) {
                            $service->editSubscriptionByEmail($subscription->id, $contact->name, $this->sso_id);
                            $msg .= "обновлено sso_id ({$this->sso_id})";
                        } else {
                            $msg .= "обновление sso_id не требуется";
                        }
                    }
                }
                Console::stdout(Console::ansiFormat($msg . PHP_EOL, [Console::FG_YELLOW]));
            }
        } catch (\Exception $e) {
            Console::stdout(Console::ansiFormat($e->getMessage() . PHP_EOL, [Console::FG_RED, Console::BOLD]));
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
