<?php

namespace app\commands;

use \PhpAmqpLib\Channel\AMQPChannel;
use \PhpAmqpLib\Connection\AMQPSSLConnection;
use \PhpAmqpLib\Connection\AMQPStreamConnection;
use app\modules\animalid\models\ConverterRulesCSV2PHP;
use app\modules\animalid\models\DataConsumer;
use app\modules\animalid\models\DataProvider;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

/**
 * Class AnimalIdController
 * @package app\commands
 */
class AnimalIdController extends Controller
{
    /**
     * Конвертирует CSV со списком правил конвертации в PHP код
     *
     * @param string $file     Полный путь до файла CSV
     * @param string $savePath Путь для сохранения файлов
     * @return int
     * @throws Exception
     */
    public function actionConvertRules($file, $savePath)
    {
        $converter = new ConverterRulesCSV2PHP();
        $result = $converter->convert($file);

        if (!FileHelper::createDirectory($savePath)) {
            throw new Exception('Path for save result not exist! ' . $savePath);
        }

        if (!is_writable($savePath)) {
            throw new Exception('Path for save read only (or check permissions)! ' . $savePath);
        }

        $prependText = '<?php' . PHP_EOL . 'return ';
        $appendText = ' ;' . PHP_EOL;

        file_put_contents(
            $savePath . '/filter_in.php',
            $prependText . var_export($result['filter']['in'], true) . $appendText
        );

        file_put_contents(
            $savePath . '/filter_out.php',
            $prependText . var_export($result['filter']['out'], true) . $appendText
        );

        file_put_contents(
            $savePath . '/convert_in.php',
            $prependText . var_export($result['convert']['in'], true) . $appendText
        );

        file_put_contents(
            $savePath . '/convert_out.php',
            $prependText . var_export($result['convert']['out'], true) . $appendText
        );

        return ExitCode::OK;
    }

    /**
     * @return int
     * @throws \Throwable
     * @throws \app\modules\animalid\skeletons\exceptions\IntegrationException
     * @throws \yii\db\StaleObjectException
     */
    public function actionSend()
    {
        $logCategory = 'animalid_output';
        $itemsCount = 0;
        $start = microtime(true);

        $connection = $this->connectToRabbit('remote_rabbit', $logCategory);
        if (!$connection) {
            \Yii::error('Не удалось установить соединение с удаленным RabbitMQ', $logCategory);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $channel = $connection->channel();
        $exchangeName = 'animalid_exchange';
        $channel->exchange_declare(
            $exchangeName,
            AMQPExchangeType::FANOUT
        );

        // Перебираем типы моделей для отправки
        foreach (DataProvider::MODELS_TO_EXPORT as $modelName) {
            $dataProvider = new DataProvider($modelName);

            // Все модели
            while ($item = $dataProvider->getNext()) {
                $result = $this->sendItemToRabbit($channel, $exchangeName, $item, $logCategory);

                if ($result) {
                    $dataProvider->mappingStore($item); // Сохраняем маппинг (пока не знаем какой id с их стороны)
                    $itemsCount++;
                }
            }
        }

        \Yii::info("Отгружено записей: $itemsCount. Время выполнения: " . round(microtime(true) - $start, 4) . " сек.\n", $logCategory);

        $channel->close();
        $connection->close();

        return ExitCode::OK;
    }

    /**
     * @return int
     * @throws \ErrorException
     */
    public function actionReceive()
    {
        $logCategory = 'animalid_input';

        $connection = $this->connectToRabbit('local_rabbit', $logCategory);
        if (!$connection) {
            \Yii::error('Не удалось установить соединение с локальным RabbitMQ', $logCategory);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $channel = $connection->channel();
        $exchangeName = 'animalid_exchange_in';
        $channel->exchange_declare(
            $exchangeName,
            AMQPExchangeType::DIRECT,
            false,
            true,
            false
        );
        $queueName = 'animalid_inbox';
        // @see https://github.com/php-amqplib/php-amqplib/issues/405
        $arguments = new AMQPTable([
            'x-max-priority' => 0,
            'x-queue-type' => 'classic',
        ]);
        $channel->queue_declare(
            $queueName,
            false,
            true,
            false,
            false,
            false,
            $arguments
        );

        $channel->queue_bind($queueName, $exchangeName);

        $callback = function ($message) use ($logCategory) {
            /* @var $message \PhpAmqpLib\Message\AMQPMessage */
            $body = $message->getBody();
            if (empty($body)) {
                return;
            }
            \Yii::info('Получено сообщение: ' . $body, $logCategory);

            $integrator = new DataConsumer();
            $result = $integrator->consume($body);

            \Yii::info('Сообщение обработано. Результат: ' . implode("\n", $result), $logCategory);
            $message->delivery_info['channel']->basic_ack($message->getDeliveryTag());  // см. комментарий к $no_ack ниже
        };

        $channel->basic_consume(
            $queueName,
            '',
            false,
            false,   // $no_ack - если true - ошибка 'PRECONDITION_FAILED - unknown delivery tag 1' (@see https://stackoverflow.com/questions/9392478/error-unknown-delivery-tag-occurs-when-i-try-ack-messages-to-rabbitmq-using-pi)
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return ExitCode::OK;
    }

    /**
     * @param string $param
     * @param string $logCategory
     * @return AMQPSSLConnection|AMQPStreamConnection|null
     */
    private function connectToRabbit($param, $logCategory)
    {
        $rabbit = \Yii::$app->params[$param];
        $host = $rabbit['host'];
        $port = $rabbit['port'];

        try {
            if (!empty($rabbit['cacert']) && !empty($rabbit['cert']) && !empty($rabbit['key'])) {
                $ssl_options = [
                    'cacert' => empty($rabbit['cacert']) ? '' : \Yii::getAlias($rabbit['cacert']),
                    'cert' => empty($rabbit['cert']) ? '' : \Yii::getAlias($rabbit['cert']),
                    'key' => empty($rabbit['key']) ? '' : \Yii::getAlias($rabbit['key']),
                    'verify' => false,
                ];
                $connection = new AMQPSSLConnection(
                    $host,
                    $port,
                    $rabbit['user'],
                    $rabbit['password'],
                    '/',
                    $ssl_options
                );
            } else {
                $connection = new AMQPStreamConnection(
                    $host,
                    $port,
                    $rabbit['user'],
                    $rabbit['password'],
                    '/'
                );
            }

            if ($connection->isConnected()) {
                \Yii::info("Подключились к $host:$port\n", $logCategory);

                return $connection;
            } else {
                \Yii::error("Ошибка подключения к $host:$port\n", $logCategory);
                unset($connection);
            }
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), $logCategory);
        }

        return null;
    }

    /**
     * @param AMQPChannel $channel
     * @param string      $exchangeName
     * @param array       $item
     * @param string      $logCategory
     * @return bool
     */
    private function sendItemToRabbit($channel, $exchangeName, $item, $logCategory)
    {
        try {
            $data = json_encode(
                $item,
                JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            $message = new AMQPMessage($data, ['content_type' => 'application/json']);
            $channel->basic_publish($message, $exchangeName);
            \Yii::info("Отправлен $item[type]:$item[id]", $logCategory);

            return true;
        } catch (\Throwable  $e) {
            \Yii::error($e->getMessage(), $logCategory);

            return false;
        }
    }

    /**
     * @return int
     */
    public function actionPingLocal()
    {
        $connection = $this->connectToRabbit('local_rabbit', 'animalid_input');
        if (!$connection) {
            Console::output('Не удалось установить соединение с локальным RabbitMQ');
        } else {
            Console::output('OK');
            $connection->close();
        }

        return ExitCode::OK;
    }

    /**
     * @return int
     */
    public function actionPingRemote()
    {
        $connection = $this->connectToRabbit('remote_rabbit', 'animalid_output');
        if (!$connection) {
            Console::output('Не удалось установить соединение с удаленным RabbitMQ');
        } else {
            Console::output('OK');
            $connection->close();
        }

        return ExitCode::OK;
    }

    /**
     * Выводит список на отправку
     * @param int $toFile
     * @return int
     * @throws \app\modules\animalid\skeletons\exceptions\IntegrationException
     */
    public function actionDumpSend($toFile = 1)
    {
        if ($toFile == 1) {
            $path = \Yii::getAlias('@runtime') . '/logs/animalid';
            if (!FileHelper::createDirectory($path)) {
                Console::output('Ошибка при создании лога');
                return ExitCode::IOERR;
            }

            $path = $path . '/dump_send_' . microtime(true) . '.log';
        }

        foreach (DataProvider::MODELS_TO_EXPORT as $model_name) {
            $dataProvider = new DataProvider($model_name);

            // Все модели
            while ($item = $dataProvider->getNext()) {
                $text = VarDumper::export($item);
                if ($toFile == 1) {
                    file_put_contents($path, date('Y-m-d H:i:s') . ' [' . time() . ']' . PHP_EOL . $text . PHP_EOL, FILE_APPEND | FILE_TEXT | LOCK_EX);
                } else {
                    Console::output($text);
                }
            }
        }

        return ExitCode::OK;
    }
}
