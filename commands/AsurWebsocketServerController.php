<?php

namespace app\commands;

use app\common\soap\MessageParser;
use app\common\websocket\asurWebsocketClient;
use yii\console\Controller;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;
use React\Socket\SecureServer;
use app\common\websocket\asurWebsocketServer;
use yii\helpers\Console;

class AsurWebsocketServerController extends Controller
{
    /**
     * Команда для запуска Вебсокета для передачи данных между АСУР и фронтом
     */
    public function actionStart($port = 8082)
    {
        $app = new HttpServer(
            new WsServer(
                new asurWebsocketServer()
            )
        );

        $loop = \React\EventLoop\Factory::create();
        $websockets = new Server("0.0.0.0:$port", $loop);

        $port = $this->ansiFormat($port, Console::FG_YELLOW);
        echo "Запущен AsurWebsocketServer на порте: $port \n";

        (new IoServer($app, $websockets, $loop))->run();
    }

    /**
     * Команда для передачи данных вебсокету по СНИЛС, имитация ответа от АС УР
     *
     * @throws \WebSocket\BadOpcodeException
     */
    public function actionMockedAsurResponse()
    {
        $xmlFilename = dirname (__DIR__) . '/modules/asur/_samples/passportExample.xml';
        $file = simplexml_load_file($xmlFilename);
        $fileAsArray = MessageParser::xmlAsArray($file->asXML());
        $outPassport = $fileAsArray['CoordinateSendTaskStatusesMessage']['CoordinateTaskStatusDataMessage']['Result']['XmlView']['OutPassport'];

        (new asurWebsocketClient())->passDataToWebsocketServer($outPassport);
    }
}
