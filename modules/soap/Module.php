<?php

namespace app\modules\soap;

use yii\helpers;
use yii\log\Logger;

use app\modules\soap\models\etp\CoordinateMessage;
use app\modules\soap\models\etp\CoordinateMessageUnauthorized;
use app\modules\soap\models\etp\CoordinateStatusMessage1068;
use app\modules\soap\models\etp\CoordinateStatusMessage1069;
use app\modules\soap\models\etp\monitoring\CoordinateMessage as CoordinateMessageMonitoring;
use app\modules\soap\models\etp\monitoring\CoordinateStatusMessage1068 as CoordinateStatusMessage1068Monitoring;
use app\modules\soap\models\etp\monitoring\CoordinateStatusMessage1069 as CoordinateStatusMessage1069Monitoring;
use app\modules\soap\models\etp\ETPException;
use app\modules\soap\models\etp\status\Status1068;
use app\modules\soap\models\etp\status\Status1069;
use yii\base\BootstrapInterface;
use yii\base\Module as YiiModule;

/**
 * Class Module
 * @package app\modules\soap
 */
class Module extends YiiModule implements BootstrapInterface
{
    /** @var string */
    public const LOG_CATEGORY = 'soap_module';

    /** @var string Метод SOAP (если применимо). Для логирования */
    public $soapMethod;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Инициализируем компоненты
        if (\Yii::$app instanceof \yii\web\Application) {
            \Yii::configure($this, require(__DIR__ . '/config/web.php'));
        }

        if (\Yii::$app instanceof \yii\console\Application) {
            \Yii::configure($this, require(__DIR__ . '/config/console.php'));
        }

        \Yii::$container->setSingleton('coordinateMessage', function($container, $params, $config){
            \Yii::debug(sprintf(
                "[class: %s] Prepare CoordinateMessage\n%s",
                static::class,
                json_encode($config)
            ), self::LOG_CATEGORY);
            $type = array_keys($config['message'])[0];
            switch ($type) {
                case 'CoordinateMessage':
                    $data = $config['message']['CoordinateMessage']['CoordinateDataMessage'];
                    if (isset($data['SignService']['Contacts']['BaseDeclarant']['SsoId']) &&
                        $data['SignService']['Contacts']['BaseDeclarant']['SsoId'] === $this->params['monitoring']['SsoId']
                    ) {
                        return new CoordinateMessageMonitoring($data);
                    } else {
                        $baseDeclarant = $data['SignService']['Contacts']['BaseDeclarant'];
                        if (isset($baseDeclarant['LastName']) && isset($baseDeclarant['FirstName']) &&
                            $baseDeclarant['LastName'] === $this->params['unauthorized_message']['LastName'] &&
                            $baseDeclarant['FirstName'] === $this->params['unauthorized_message']['FirstName']
                        ) {
                            return new CoordinateMessageUnauthorized($data);
                        } else {
                            return new CoordinateMessage($data);
                        }

                    }

                case 'CoordinateStatusMessage':
                    $data = $config['message']['CoordinateStatusMessage']['CoordinateStatusDataMessage'];

                    $is_monitoring = (isset($data['Contacts']['BaseDeclarant']['SsoId']) &&
                        $data['Contacts']['BaseDeclarant']['SsoId'] === $this->params['monitoring']['SsoId']
                    );

                    switch ($data['Status']['StatusCode']) {
                        case Status1068::CODE:
                            return $is_monitoring
                                ? new CoordinateStatusMessage1068Monitoring($data)
                                : new CoordinateStatusMessage1068($data)
                            ;

                        case Status1069::CODE:
                            return $is_monitoring
                                ? new CoordinateStatusMessage1069Monitoring($data)
                                : new CoordinateStatusMessage1069($data)
                            ;

                        default:
                            throw new ETPException("Передан неверный код", 400);
                    }

                default:
                    throw new ETPException("Неизвестный тип сообщения", 400);
            }
        });
    }

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\soap\commands';
        }
    }

    private $requestId;

    /**
     * @inheritDoc
     */
    public function beforeAction($action): bool
    {
        $return = parent::beforeAction($action);

        $rq = \Yii::$app->getRequest();
        if ($rq instanceof \yii\console\Request) {
            return $return;
        }

        $this->logHttpMessage(
            'SOAP request',
            $this->flattenYiiHeaders($rq->getHeaders()->toArray()),
            $rq->getRawBody()
        );

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function afterAction($action, $result)
    {
        $return = parent::afterAction($action, $result);

        $rq = \Yii::$app->getRequest();
        if ($rq instanceof \yii\console\Request) {
            return $return;
        }

        $rs = \Yii::$app->getResponse();
        // Soap requires POST request, hide wsdl content from logs
        if ($rq->isGet && !array_key_exists('ws', $rq->getQueryParams())) {
            $payload = '[MASKED WSDL CONTENT]';
        } else {
            $payload = is_string($result) ? trim($result) : helpers\VarDumper::dumpAsString($result);
        }

        if (empty($headers = $rs->getHeaders()->toArray())) {
            // \SoapServer отправляет заголовки самостоятельно
            $headers = $this->fetchSentHeaders();
        }

        $this->logHttpMessage(
            'SOAP response',
            $this->flattenYiiHeaders($headers),
            $payload
        );

        return $return;
    }

    protected function getRequestId(): string
    {
        if (!$this->requestId) {
            $this->requestId = str_replace('.', '', uniqid(''));
        }

        return $this->requestId;
    }

    protected function logHttpMessage(string $msg, array $headers, string $body): void
    {
        $extraData = [
            'headers' => $headers,
            'body' => empty($body) ? '[EMPTY BODY]' : $body
        ];

        $this->log($msg, Logger::LEVEL_TRACE, $extraData);
    }

    public function log($msg, string $level, $extraData = null)
    {
        $params = array_filter([
            'url' => \Yii::$app->getRequest()->getUrl(),
            'request-id' => $this->getRequestId(),
            'method' => $this->soapMethod,
        ]);
        $paramsStr = implode(', ', array_map(
            function($v, $k) { return $k .'='. $v; }, 
            $params,
            array_keys($params)
        ));

        $msg .= " ({$paramsStr})";
        if (!empty($extraData)) {
            $msg .= "\n" . helpers\Json::encode($extraData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        \Yii::getLogger()->log(
            $msg,
            $level,
            static::class
        );
    }

    /**
     * Yii хранит значение каждого заголовка как массив. Функция
     * заменяет значение на скаляр, если оно единственное.
     * 
     * @param array $headers
     * 
     * @return array
     */
    protected function flattenYiiHeaders(array $headers): array
    {
        array_walk(
            $headers,
            function(&$v, string $k) {
                $v = (is_array($v) && 1 === count($v))
                    ? reset($v)
                    : $v
                ;
            }
        );

        return $headers;
    }

    protected function fetchSentHeaders(): array
    {
        $headers = [];
        foreach (headers_list() as $headerStr) {
            list($name, $value) = explode(':', $headerStr, 2);
            $headers[$name] = $value;
        }

        return $headers;
    }
}
