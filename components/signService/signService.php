<?php

namespace app\common\components\signService;

use app\common\components\signService\models\ValidateAdapter;
use Exception;
use GuzzleHttp\Client;
use LibXMLError;
use Yii;
use yii\base\Component;

class signService extends Component
{
    // URI файла wsdl
    public $wsdl = '';
    //идентификатор клиента
    public $user_agent = 'PHPSoapClient';
    //таймаут установки соединения
    public $connection_timeout = 100;
    //логин для доступа к сервису
    public $login = '';
    //пароль для доступа к сервису
    public $password = '';
    //флаг - генерация эксепшенов во время работы сервиса
    public $exceptions = false;

    //клиент для обращения к сервису
    public $guzzleClient = null;

    //флаг для мока ЭЦП
    public $skip = true;


    /**
     * Пытается распарсить строку $xmlText XML формата в объект.
     * Вернет SimpleXMLElement в случае успеха и false если возникли ошибки.
     * @param string $xmlText
     * @return \SimpleXMLElement | false
     * @throws Exception
     */
    private function getXmlFromString($xmlText)
    {
        $oldValue = libxml_use_internal_errors(true);
        //чистим буфер ошибок от чужих ошибок
        libxml_clear_errors();
        $xml = simplexml_load_string($xmlText);
        if (false === $xml) {
            $error = libxml_get_last_error();
            if ($error instanceof libXMLError) {
                if ($this->exceptions) {
                    throw new Exception("[{$error->code}] В xml файле (строка: {$error->line}, столбец: {$error->column}) ошибка «{$error->message}»");
                } else {
                    Yii::error("[{$error->code}] В xml файле (строка: {$error->line}, столбец: {$error->column}) ошибка «{$error->message}»");
                }
            } else {
                if ($this->exceptions) {
                    throw new Exception('При разборе XML возникла неизвестная ошибка');
                } else {
                    Yii::error('При разборе XML возникла неизвестная ошибка');
                }
            }
            // Подчищаем за собой буфер ошибок
            libxml_clear_errors();
        }
        libxml_use_internal_errors($oldValue);

        return $xml;
    }

    /**
     * Инициализация клиента
     * @return boolean
     */
    protected function autoInitGuzzleClient()
    {
        if (is_null($this->guzzleClient)) {
            $config = [
                'headers' => [
                    'Content-Type' => 'text/xml;charset=UTF-8'
                ],
            ];

            $guzzleClient = new Client($config);
            if ($this->exceptions) {
                $wsdlResponse = $guzzleClient->get($this->wsdl);
            } else {
                try {
                    $wsdlResponse = $guzzleClient->get($this->wsdl);
                } catch (\Exception $e) {
                    return false;
                }
            }
            $wsdlXml = $wsdlResponse->getBody()->getContents();
            $xml = $this->getXmlFromString($wsdlXml);
            if ($xml instanceof \SimpleXMLElement) {
                //заполняем неймспейс
                $xml->registerXPathNamespace('nsd', 'http://schemas.xmlsoap.org/wsdl/');
                $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/wsdl/soap/');
                $serviceLocation = $xml->xpath('//nsd:port[@name="ValidationPort"]/soap:address/@location');
                if (!empty($serviceLocation)) {
                    $config['base_uri'] = (string)array_shift($serviceLocation);
                    $this->guzzleClient = new Client($config);
                } else {
                    if ($this->exceptions) {
                        throw new Exception('Не удалось извлечить из WSDL адрес сервиса');
                    } else {
                        Yii::error('Не удалось извлечить из WSDL адрес сервиса');
                    }
                }
            }
        }
        return $this->guzzleClient instanceof Client;
    }

    /**
     * использовать при кейсе личной подписи через токен - файл подписывается на фронте.
     * Проверяем валидность подписанного документа. Вернет false если сервис не доступен.
     * @param string $sign ЭЦП
     * @param string $base64 Валидируемый документ
     * @return ValidateAdapter|bool
     * @throws Exception
     */
    public function validateSign($sign, $base64)
    {
        if ($this->skip) {
            $params = require(Yii::getAlias('@app') . '/common/components/signService/ecp_params_mocking.php');
            $xml = $this->getXmlFromString($params['xml']);
            return new ValidateAdapter(['xml' => $xml]);
        }

        if (!$this->autoInitGuzzleClient()) {
            return false;
        }

        $config = [];
        $config['headers'] = [
            'SOAPAction' => 'http://www.roskazna.ru/eb/sign/types/sgv/Validate'
        ];
        $config['body'] =
            '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.roskazna.ru/eb/sign/types/sgv"><SOAP-ENV:Body><ns1:ValidationRequestType>
                        <ns1:signedData>' . $sign . '</ns1:signedData>
                        <ns1:externalData>' . $base64 . '</ns1:externalData>
                            </ns1:ValidationRequestType></SOAP-ENV:Body></SOAP-ENV:Envelope>';

        if ($this->exceptions) {
            $soapResponse = $this->guzzleClient->post('', $config);
        } else {
            try {
                $soapResponse = $this->guzzleClient->post('', $config);
            } catch (\Exception $e) {
                return false;
            }
        }
        $responseXml = $soapResponse->getBody()->getContents();
        //пофиксить мусор из guzzle
        preg_match('~(<soapenv:Envelope(?:.+)</soapenv:Envelope>)~ui', $responseXml, $matches);
        $responseXml = '<?xml version="1.0" encoding="UTF-8"?>' . $matches[0];
        $xml = $this->getXmlFromString($responseXml);
        if ($xml instanceof \SimpleXMLElement) {
            return new ValidateAdapter(['xml' => $xml]);
        }

        return false;
    }
}
