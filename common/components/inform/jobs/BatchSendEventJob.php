<?php

namespace app\common\components\inform\jobs;

use app\common\components\inform\SubscriptionService;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\models\db\subscription\Subscriptions;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;

/**
 * Class BatchSendEventJob
 * @package app\common\components\inform\jobs
 */
class BatchSendEventJob extends BaseObject implements JobInterface
{
    /**
     * @var string
     */
    public $eventClass;
    /**
     * @var array
     */
    public $data;
    /**
     * @var int
     */
    public $batchSize = 100;
    /**
     * @var array|\yii\db\Query|\yii\db\ActiveQuery
     */
    public $to;
    /**
     * @var string (IN | INNER JOIN)
     */
    public $operator = 'IN';

    /**
     * @var \yii\queue\db\Queue
     */
    protected $subscriptionQueue;

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function execute($queue)
    {
        $this->subscriptionQueue = \Yii::$app->subscription_queue;

        if (is_array($this->to)) {
            // передан массив ID владельцев
            $query = $this->prepareContactsQuery();
            $query->andWhere(['in', 'po.id', $this->to]);
        } elseif ($this->to instanceof Query) {
            if (!in_array($this->operator, ['IN', 'INNER JOIN'])) {
                throw new InvalidConfigException('Некорректный параметр subqueryType');
            }
            $query = $this->prepareContactsQuery();
            if ($this->operator == 'IN') {
                // передан объект Query, возвращающий только ID владельцев
                $query->andWhere(['IN', 'po.id', $this->to]);
            } else {
                // передан объект Query, возвращающий в том числе ID владельцев как id_owner
                $query->innerJoin(
                    ['owners_subquery' => $this->to],
                    'owners_subquery.id_owner = po.id'
                );
            }
        } else {
            throw new InvalidConfigException('В качестве получателей должен быть передан массив ID владельцев или объект \yii\db\Query|\yii\db\ActiveQuery');
        }

        foreach ($query->batch($this->batchSize) as $rows) {
            foreach ($rows as $row) {
                $this->prepareSingleJob($row);
            }
        }

        return ExitCode::OK;
    }

    /**
     * @param array|\yii\db\ActiveRecord $row
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    protected function prepareSingleJob($row)
    {
        $email = ArrayHelper::getValue($row, 'email');
        if (empty($email)) {
            return;
        }

        $sso_id = ArrayHelper::getValue($row, 'sso_id');
        $id_contact = ArrayHelper::getValue($row, 'id_contact');
        $id_owner = ArrayHelper::getValue($row, 'id_owner');
        $token = SubscriptionService::encodeUnsubscribeToken($id_contact, $email, $sso_id);
        $ownerName = ArrayHelper::getValue($row, 'owner_name', '');

        //Найдем животных владельца и соберем в один массив
        $pets = array_column($this->to->select('id_pet')->where(['po.id' => $id_owner])->all(), 'id_pet');

        // создаем экземляр события, так как в отдельных событиях
        // возвращаемые в getEventData() параметры не совпадают со свойствами объекта
        // поэтому вынуждены использовать $event->getJob()

        /** @var \app\common\components\inform\events\SubscriptionEvent $event */
        $config = array_merge(
            ['class' => $this->eventClass],
            $this->data,
            compact('email', 'ownerName', 'token', 'pets')
        );
        $event = \Yii::createObject($config);

        /** @var Contacts $contact */
        $contact = Contacts::findOne($id_contact);
        $this->subscriptionQueue->push($event->getJob($contact->getTypeForInformation(), $contact, $token));
        unset($event);
    }

    /**
     * @return \yii\db\Query
     * @see \app\modules\v2\modules\subscriptions\models\Subscriptions::apply
     */
    protected function prepareContactsQuery()
    {
        $id_contact_type = (new Query())
            ->select('id')
            ->from(ContactTypes::tableName())
            ->where([
                'type' => ContactTypes::TYPE_EMAIL,
                'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
            ])
            ->scalar();

        $query = (new Query())
            ->select('po.id AS id_owner')
            ->distinct()
            ->addSelect(new Expression('CONCAT_WS(\' \', po.i_fio, po.o_fio) AS owner_name'))
            ->addSelect('c.id AS id_contact, c.name AS email, po.sso_id AS sso_id')
            ->from(PetOwners::tableName() . ' po')
            ->innerJoin(
                Contacts::tableName() . ' c',
                'c.entity_type = \'' . ContactTypes::ENTITY_TYPE_PET_OWNER . '\' AND c.id_contact_type = ' . $id_contact_type . ' AND c.entity_id = po.id'
            )
            ->innerJoin(Subscriptions::tableName() . ' s', 's.id_contact = c.id AND s.subscribed = true');

        return $query;
    }
}
