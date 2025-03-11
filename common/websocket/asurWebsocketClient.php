<?php

namespace app\common\websocket;

use app\models\db\PetOwners;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use WebSocket\Client;

class asurWebsocketClient
{
    private $host;
    private $port;
    private $client;

    public function __construct()
    {
        $this->host = \Yii::$app->params['asur_websocket']['server_host'];
        $this->port = \Yii::$app->params['asur_websocket']['server_port'];
        $this->client = new Client("ws://$this->host:$this->port");
    }

    /**
     * @param string $snils
     * @return mixed
     * @throws \WebSocket\BadOpcodeException
     * @throws \WebSocket\ConnectionException
     */
    public function requestDataFromAsur(string $snils)
    {
        /** @var PetOwners $owner */
        $owner = PetOwners::find()->where(['snils' => $snils])->one();
        if (!$owner) {
            throw new BadRequestException('На найден владелец с переданным СНИЛС');
        }

        $this->client->send(json_encode([
            'command' => asurWebsocketServer::REQUEST_DATA,
            'id_owner' => $owner->id,
            'snils' => $snils,
        ]));

        return json_decode($this->client->receive());
    }

    /**
     * @param array $data данные из тэга OutPassport из ответа от АС УР по документу 10209
     * @throws \WebSocket\BadOpcodeException
     */
    public function passDataToWebsocketServer($data)
    {
        $this->client->send(json_encode([
            'command' => asurWebsocketServer::RESPONSE_DATA,
            'response' => [
                'snils' => $data['SNILS'],
                'passport' => $data
            ]
        ]));
    }

    /**
     * @param string $snils
     * @throws \WebSocket\BadOpcodeException
     */
    public function passNotExistToWebsocketServer(string $snils)
    {
        $this->client->send(json_encode([
            'command' => asurWebsocketServer::RESPONSE_NOT_FOUND,
            'snils' => $snils,
        ]));
    }
}
