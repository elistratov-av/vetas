<?php

namespace app\common\components\inform;

use linslin\yii2\curl\Curl;
use yii\base\Component;

class SpkService extends Component
{
    const
        CHANNEL_EMAIL = 'email',
        CHANNEL_MSISDN = 'msisdn',
        CHANNEL_SSO_ID = 'sso_id'
    ;

    /** @var string */
    public $eventUrl;

    /** * @var string */
    public $subscriptionsUrl;

    /** @var string */
    public $eventAccessToken;

    /** @var string */
    public $subscriptionsAccessToken;

    /** @var string */
    public $serviceName;

    /** @var int */
    public $tokenTtl;

    /** @var string */
    public $linkToSubscribe;

    /** @var string */
    public $linkToUnsubscribe;

    /** @var string */
    public $linkRegistration;

    /** @var string */
    public $fileRepositoryDomain;

    /** @var string */
    public $linkToVaccineFeedbackForm;

    /** @var string */
    public $linkToFilePage;

    /** @var string */
    public $linkToLoginPage;

    /** @var string */
    public $linkToVisit;

    /** @var string */
    public $enableELK = false;

    /**
     * @throws \yii\db\Exception
     */
    public function clearOldConfirms()
    {
        \Yii::$app->db->createCommand(
            "delete from subscription.confirm where (created_at + make_interval(secs => :ttl)) < now()",
            [
                'ttl' => $this->tokenTtl
            ]
        )->execute();
    }

    /**
     * @param array $params
     * @throws InformException
     */
    public function checkSubscription(array $params)
    {
        $to = $params['to'];
        $sso_id = isset($to['sso_id']) ? $to['sso_id'] : null;
        if (isset($to['email'])) {
            if (!$this->checkIsEmailSubscribed($to['email'], $sso_id)) {
                $this->thrownError("У почты {$to['email']} {$sso_id} нет подписки", $params, json_encode($to));
            }
        } elseif(isset($to['msisdn'])) {
            if (!$this->checkIsPhoneSubscribed($to['msisdn'], $sso_id)) {
                $this->thrownError("У телефона {$to['msisdn']} {$sso_id} нет подписки", $params, json_encode($to));
            }
        } elseif(isset($to['sso_id'])) {
            if (!$this->checkIsSsoIdSubscribed($to['sso_id'])) {
                $this->thrownError("У sso_id {$to['sso_id']} нет подписки по телефону или почте", $params, json_encode($to));
            }
        } else {
            $this->thrownError("Неизвестный получатель", $params);
        }
    }

    /**
     * Отправка события  в ИС ПК
     *
     * @param array $params
     * @throws InformException
     */
    public function sendEvent(array $params)
    {
        $params['access_token'] = $this->eventAccessToken;
        $contact = json_encode($params['to']);

        $curl = new Curl();
        $curl->setRawPostData(json_encode($params))
            ->post($this->eventUrl);

        $this->processCurlError($curl);

        $response = json_decode($curl->response);
        if ($response->errorCode != 0) {
            $this->thrownError("Не удалось отправить событие: {$response->errorMessage}", $params, $contact);
        } else {
            $event_id = isset($params['event_id']) ? $params['event_id'] : '';
            $event_code = isset($params['event_code']) ? $params['event_code'] : '';

            // Дополнительные свойства для событий по Нарушениям (Violations)
            $id_author = isset($params['id_author']) ? $params['id_author'] : '';
            $id_violation = isset($params['id_violation']) ? $params['id_violation'] : '';
            $id_violation_history = isset($params['id_violation_history']) ? $params['id_violation_history'] : '';

            // Идентификатор для доступа к прикреплённым к сообщению файлам
            $attached_files_ids = isset($params['attached_files_ids']) ? $params['attached_files_ids'] : '';
            $files_token = isset($params['files_token']) ? $params['files_token'] : '';

            $id_owner = $params['id_owner'] ?? '';
            $id_initiator = $params['id_initiator'] ?? '';
            $id_pet = $params['id_pet'] ?? '';
            $id_visit = $params['id_visit'] ?? '';

            $pets = $params['pets'] ?? [];

            $log = compact('event_id', 'event_code','contact',
                'params', 'id_author','id_violation',
                'files_token','attached_files_ids',
                'id_owner','id_initiator','id_pet','id_visit', 'id_violation_history', 'pets');
            $log = array_merge($log, ['is_success' => true]);
            \Yii::info($log, 'subscription_queue');
        }
    }

    /**
     * @param $message
     * @param $params
     * @param string $to
     * @throws InformException
     */
    protected function thrownError($message, $params, $to = '')
    {
        $event_id = isset($params['event_id']) ? $params['event_id'] : '';
        $event_code = isset($params['event_code']) ? $params['event_code'] : '';
        $params = json_encode($params);

        \Yii::info([
            'event_id'   => $event_id,
            'event_code' => $event_code,
            'to'         => $to,
            'params'     => $params,
            'error'      => $message,
            'is_success' => false,
        ], 'subscription_queue');
        throw new InformException($message);
    }

    /**
     * Создание подписки
     *
     * @param string $email
     * @param string|null $sso_id
     * @return |null
     * @throws InformException
     */
    public function createSubscriptionByEmail(string $email, ?string $sso_id = null)
    {
        $options = null;
        if (!empty($sso_id)) {
            $options = [
                'ssoid' => $sso_id
            ];
        }
        $params = json_encode([
            'service' =>  $this->serviceName,
            'msisdn' => '',
            'email' => $email,
            'stream' => 'email',
            'options' => $options
        ]);

        $curl = new Curl();
        $curl->setHeaders([
                'Content-Type' => 'application/json',
                'x-auth-token' => $this->subscriptionsAccessToken
            ])
            ->setRawPostData($params)
            ->post($this->subscriptionsUrl . '/subscribe/item');

        $this->processCurlError($curl);

        $response = json_decode($curl->response);
        if ($response->errorCode != 0) {
            $message = "Не удалось создать подписку: {$response->errorMessage}";
            \Yii::info([
                'event_code' => 'create_subscription',
                'is_success' => false,
                'params'     => $params,
                'error'      => $message
            ], 'subscription_queue');
            throw new InformException($message);
        } else {
            $message = "Успешно создана подписка для: {$email}";
            \Yii::info([
                'event_code' => 'create_subscription',
                'is_success' => true,
                'params'     => $params,
                'error'      => $message
            ], 'subscription_queue');
        }

        if (is_array($response->result)) {
            // Т.к. возвращается массив доступных подписок, ищем ту на которую подписались
            $id = null;
            foreach ($response->result as $subscriptions) {
                if ($subscriptions->stream == 'email' && $subscriptions->email == $email) {
                    $id = $subscriptions->id;
                    break;
                }
            }

            if (is_null($id)) {
                $message = "Не удалось создать подписку: неверный ответ сервера";
                \Yii::info([
                    'event_code' => 'create_subscription',
                    'is_success' => false,
                    'params'     => $params,
                    'error'      => $message
                ], 'subscription_queue');
                throw new InformException($message);
            }
            return $id;
        } else {
            return $response->result->id;
        }
    }

    /**
     * Создание подписки
     *
     * @param string      $phone
     * @param string|null $sso_id
     * @return |null
     * @throws InformException
     */
    public function createSubscriptionByPhone(string $phone, ?string $sso_id = null)
    {
        $phone = ltrim($phone, '+');

        $options = null;
        if (!empty($sso_id)) {
            $options = [
                'ssoid' => $sso_id
            ];
        }
        $params = json_encode([
            'service' =>  $this->serviceName,
            'msisdn' => $phone,
            'email' => '',
            'stream' => 'push',
            'options' => $options
        ]);

        $curl = new Curl();
        $curl->setHeaders([
                'Content-Type' => 'application/json',
                'x-auth-token' => $this->subscriptionsAccessToken
            ])
            ->setRawPostData($params)
            ->post($this->subscriptionsUrl . '/subscribe/item');

        $this->processCurlError($curl);

        $response = json_decode($curl->response);
        if ($response->errorCode != 0) {
            $message = "Не удалось создать подписку: {$response->errorMessage}";
            \Yii::info([
                'event_code' => 'create_subscription',
                'is_success' => false,
                'params'     => $params,
                'error'      => $message
            ], 'subscription_queue');
            throw new InformException($message);
        } else {
            $message = "Успешно создана подписка для: {$phone}";
            \Yii::info([
                'event_code' => 'create_subscription',
                'is_success' => true,
                'params'     => $params,
                'error'      => $message
            ], 'subscription_queue');
        }

        if (is_array($response->result)) {
            // Т.к. возвращается массив доступных подписок, ищем ту на которую подписались
            $id = null;
            foreach ($response->result as $subscriptions) {
                if ($subscriptions->stream == 'push' && $subscriptions->msisdn == $phone) {
                    $id = $subscriptions->id;
                    break;
                }
            }

            if (is_null($id)) {
                $message = "Не удалось создать подписку: неверный ответ сервера";
                \Yii::info([
                    'event_code' => 'create_subscription',
                    'is_success' => false,
                    'params'     => $params,
                    'error'      => $message
                ], 'subscription_queue');
                throw new InformException($message);
            }
            return $id;
        } else {
            return $response->result->id;
        }
    }

    /**
     * Редактирование подписки
     *
     * @param $id
     * @param string $email
     * @param string|null $sso_id
     * @throws InformException
     */
    public function editSubscriptionByEmail($id, string $email, ?string $sso_id = null)
    {
        $this->deleteSubscription($id);
        $this->createSubscriptionByEmail($email, $sso_id);
    }

    /**
     * Удаление подписки
     *
     * @param $id
     * @throws InformException
     */
    public function deleteSubscription($id)
    {
        $curl = new Curl();
        $curl->setHeaders([
                'Content-Type' => 'application/json',
                'x-auth-token' => $this->subscriptionsAccessToken
            ])
            ->delete($this->subscriptionsUrl . '/subscribe/item/' . $id);

        $this->processCurlError($curl);

        $response = json_decode($curl->response);
        if ($response->errorCode != 0) {
            throw new InformException("Не удалось удалить подписку: {$response->errorMessage}");
        }
    }

    /**
     * Получение списка подписок
     *
     * @param string|null $params
     * @return mixed
     * @throws InformException
     */
    protected function getSubscribeList(?string $params)
    {
        $curl = new Curl();
        $curl->setHeaders([
                'Content-Type' => 'application/json',
                'x-auth-token' => $this->subscriptionsAccessToken
            ])
            ->get($this->subscriptionsUrl . '/subscribe/list?' . (!empty($params) ? $params : ''));

        $this->processCurlError($curl);

        $response = json_decode($curl->response);
        if (empty($response)) {
            throw new InformException("Сервис подписок недоступен или выдал неизвестный формат ответа");
        }

        if (!property_exists($response, 'errorCode')) {
            throw new InformException("Неизвестный формат ответа сервиса подписок");
        }

        if ($response->errorCode != 0) {
            throw new InformException("Не удалось получить данные: {$response->errorMessage}");
        }

        return isset($response->result) ? $response->result : null;
    }

    /**
     * @return mixed
     * @throws InformException
     */
    public function getSubscriptions()
    {
        return $this->getSubscribeList(null);
    }

    /**
     * @param array $emails
     * @return array
     * @throws InformException
     */
    public function getSubscribedEmails(array $emails)
    {
        if (empty($emails)) {
            throw new InformException("Пустой список email");
        }

        $result = $this->getSubscribeList("q=email:" . implode(';', $emails));

        $subscribed = [];
        foreach ($result as $item) {
            $subscribed[] = $item->email;
        }

        return $subscribed;
    }

    /**
     * @param array $phones
     * @return array
     * @throws InformException
     */
    public function getSubscribedPhones(array $phones)
    {
        if (empty($phones)) {
            throw new InformException("Пустой список phones");
        }

        $result = $this->getSubscribeList("q=msisdn:" . implode(';', $phones));

        $subscribed = [];
        foreach ($result as $item) {
            $subscribed[] = $item->msisdn;
        }

        return $subscribed;
    }

    /**
     * Получить список всех наших подписчиков
     *
     * @param int $offset
     * @param int $limit
     * @return mixed|null
     * @throws InformException
     */
    public function listAllSubscribers(int $offset = 0, int $limit = 100)
    {
        $params = http_build_query([
            'q' => 'service:' . $this->serviceName,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        //$url = $this->subscriptionsUrl . '/subscribe/list?' . (!empty($params) ? $params : ''); var_dump($url);

        return $this->getSubscribeList($params);
    }

    /**
     * @param string $email
     * @param string|null $sso_id
     * @return bool
     * @throws InformException
     */
    public function checkIsEmailSubscribed(string $email, ?string $sso_id = null)
    {
        $query = "q=email:{$email}";
        if (!empty($sso_id)) {
            $query .= ",options.ssoid:{$sso_id}";
        }
        $subscriptions = $this->getSubscribeList($query);

        foreach ($subscriptions as $subscription) {
            if ($email == $subscription->email && $this->serviceName == $subscription->service) {
                return true;
            }
        }

        return true;
    }

    /**
     * @param string $phone
     * @param string|null $sso_id
     * @return bool
     * @throws InformException
     */
    public function checkIsPhoneSubscribed(string $phone, ?string $sso_id = null)
    {
        $phone = trim($phone, '+');
        $query = "q=msisdn:{$phone}";
        if (!empty($sso_id)) {
            $query .= ",options.ssoid:{$sso_id}";
        }
        $subscriptions = $this->getSubscribeList($query);

        foreach ($subscriptions as $subscription) {
            if ($phone == $subscription->msisdn && $this->serviceName == $subscription->service) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $sso_id
     * @return bool
     * @throws InformException
     */
    public function checkIsSsoIdSubscribed(string $sso_id)
    {
        $query = "q=options.ssoid:{$sso_id}";
        $subscriptions = $this->getSubscribeList($query);

        return (bool)count($subscriptions);
    }

    /**
     * @param string $type
     * @param string $contact
     * @param string|null $sso_id
     * @return mixed
     * @throws InformException
     */
    public function getSubscriptionsForContact(string $type, string $contact, ?string $sso_id = null)
    {
        if (!in_array($type, ['email', 'msisdn'])) {
            throw new InformException('Неизвестный тип контакта');
        }

        if ($type == 'msisdn') {
            $contact = trim($contact, '+');
        }

        $query = "q={$type}:{$contact}";
        if (!empty($sso_id)) {
            $query .= ",options.ssoid:{$sso_id}";
        }

        $subscriptions = $this->getSubscribeList($query);

        return $subscriptions;
    }

    /**
     * @param Curl $curl
     * @throws InformException
     */
    private function processCurlError(Curl $curl)
    {
        if ($curl->response === null) {
            throw new InformException("Ошибка curl: {$curl->errorCode} {$curl->errorText}");
        }
    }

    /**
     * @param $path
     * @return string
     */
    public function makeFileUrl($path)
    {
        return $this->fileRepositoryDomain . $path;
    }
}
