<?php

namespace app\common\websocket;

use app\common\components\rbac\Role;
use app\models\db\asur\Task;
use Yii;
use app\common\components\asurService\ASURService;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use app\modules\v2\modules\gosvetnadzor\models\Registry;
use app\common\components\Jwt;
use yii\di\Instance;

class asurWebsocketServer implements MessageComponentInterface
{
    const REQUEST_SENT_MESSAGE = 'Запрос направлен на обработку в АС УР';
    const REQUEST_IN_PROCESS_MESSAGE = 'Запрос всё ещё в обработке АС УР';
    const REQUEST_DATA_RECEIVED_MESSAGE = 'Получен ответ от АС УР';
    const REQUEST_DATA_NOT_FOUND_MESSAGE = 'Не найдены данные по указаному СНИЛС в АС УР';
    const REQUEST_INVALID_MESSAGE = 'Неверным формат запроса';

    const REQUEST_DATA = 'request';
    const RESPONSE_DATA = 'response';
    const RESPONSE_NOT_FOUND = 'not_found';

    /** @var array $userRequests */
    private $userRequests;
    /** @var array $asurData */
    private $asurData;
    /** @var array $notFoundData */
    private $notFoundData;
    /** @var \SplObjectStorage $clients */
    protected $clients;

    public $requiredParams = [
        self::REQUEST_DATA => ['id_owner', 'snils'],
        self::RESPONSE_DATA => ['response'],
        self::RESPONSE_NOT_FOUND => ['snils'],
    ];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userRequests = [];
        $this->notFoundData = [];
        $this->asurData = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg);

            if (!$this->requestIsValid($data)) {
                $from->send(json_encode([
                    'result' => false,
                    'message' => self::REQUEST_INVALID_MESSAGE
                ]));
                $this->onClose($from);
            } else {
                switch ($data->command) {
                    case self::REQUEST_DATA:
                        $this->requestDocumentData($from, $data->id_owner, $data->snils);
                        break;
                    case self::RESPONSE_DATA:
                        $this->sendPassportData($from, $data->response);
                        break;
                    case self::RESPONSE_NOT_FOUND:
                        $this->storeDataNotFound($from, $data->snils);
                        break;
                }
            }
        } catch (\Exception $exception) {
            $from->send(json_encode([
                'result' => false,
                'message' => $exception->getMessage(),
            ]));
            $this->onClose($from);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * Запросы на получение данных в АСУР. На каждый запрос переправляем в АСУР только для уникальных СНИЛС
     *
     * @param ConnectionInterface $from
     * @param $id_owner
     * @param $snils
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    private function requestDocumentData(ConnectionInterface $from, $id_owner, $snils){
        $snils = $this->snilsToNumber($snils);

        // Проверим, что документ ещё не запрошен, во избежание повторных запросов в АСУР
        if (isset($this->userRequests[$snils])) {
            // Ответ от АСУР получен, но данные по документу не найдены
            if (isset($this->notFoundData[$snils])) {
                $from->send(json_encode([
                    'result' => true,
                    'message' => self::REQUEST_DATA_NOT_FOUND_MESSAGE
                ]));
            }
            // Если получены данные от АСУР - передаём их пользователю
            else if (isset($this->asurData[$snils])) {
                $from->send(json_encode([
                    'result' => true,
                    'message' => self::REQUEST_DATA_RECEIVED_MESSAGE,
                    'data' => (array)$this->asurData[$snils],
                ]));
            }
            // Запрос всё ещё в обработке в АСУР
            else {
                $from->send(json_encode([
                    'result' => true,
                    'message' => self::REQUEST_IN_PROCESS_MESSAGE,
                ]));
            }

            return;
        }

        if (!isset($this->userRequests[$snils])) {
            (new Registry())->createTask($id_owner, ASURService::PARAM_SNILS, Task::TYPE_PASSPORT_REQUEST);

            $from->send(json_encode([
                'result' => true,
                'message' => self::REQUEST_SENT_MESSAGE,
            ]));
        }

        $this->clients->detach($from);
        $this->userRequests[$snils] = $from->resourceId;
        echo "Added {$id_owner} id_owner to requests\n";
    }

    /**
     * Получение ответа от АСУР и держим данных в процессе вебсокета до повторного востребования
     *
     * @param ConnectionInterface $responseClient
     * @param $data
     */
    private function sendPassportData(ConnectionInterface $responseClient, $data) {
        $this->clients->detach($responseClient);
        unset($this->users[$responseClient->resourceId]);

        echo "API-Connection {$responseClient->resourceId} has disconnected\n";

        $snils = $this->snilsToNumber($data->snils);
        $this->asurData[$snils] = $data;
    }

    /**
     * Сохраняем ответ от АС УР о том, что данные не найдены в АС УР
     *
     * @param ConnectionInterface $responseClient
     * @param $snils
     */
    private function storeDataNotFound(ConnectionInterface  $responseClient, $snils) {
        $this->clients->detach($responseClient);
        unset($this->users[$responseClient->resourceId]);

        echo "API-Connection {$responseClient->resourceId} has disconnected\n";

        $snils = $this->snilsToNumber($snils);
        $this->notFoundData[$snils] = true;
    }

    /**
     * @param $data
     * @return bool
     */
    private function requestIsValid($data)
    {
        if (!isset($data->command) || !$this->requiredParams[$data->command]) {
            return false;
        }
        foreach ($this->requiredParams[$data->command] as $param) {
            if (!$data->$param) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $snils
     * @return string
     */
    private function snilsToNumber(string $snils) {
        return preg_replace('/[^0-9.]+/', '', $snils);
    }
}
