<?php

namespace app\common\components\asurService;

use app\common\components\jcpSign\JcpException;
use app\common\components\jcpSign\JcpSignService;
use app\common\soap\XmlFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use yii\base\Component;

class ASURService extends Component
{
    const
        PARAM_INN = 'inn',
        PARAM_OGRN = 'ogrn',
        PARAM_SNILS = 'snils',

        FUNCTION_TYPE_CODE = '010215',

        TYPE_EGRIP = 13018, // Выписка из ЕГРИП
        TYPE_EGRUL = 13017, // Выписка из ЕГРЮЛ
        TYPE_PASSPORT = 10209, // Паспорт
        TYPE_EJD = 10777, // Единый Жилищный Документ

        EGRIP_TEST_INN = '500907235960',
        EGRIP_TEST_OGRN = '305500910900012',

        EGRUL_TEST_INN = '7730592673',
        EGRUL_TEST_OGRN = '5087746429843',

        TEST_SNILS = '69614499457',

        EJD_ROOM = '26',
        EJD_ADDRESS_BTI = '30620'
    ;

    /** @var string */
    public $url;

    /** @var string */
    public $signUrl;

    /** @var array */
    public $Responsible;

    /** @var array */
    public $Department;

    /** @var string */
    public $FunctionTypeCode;

    /** @var string */
    public $TaskNumberMask;

    /** @var boolean */
    public $test;

    /**
     * @return JcpSignService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getSignService()
    {
        /** @var JcpSignService $service */
        $service = \Yii::$container->get('sign', [], [
            'uri' => $this->signUrl
        ]);
        return $service;
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function sendMessage(array $data)
    {
        if ($this->test) {
            $this->setTestData($data);
        }

        $formatter = new XmlFormatter();
        $message = $formatter->makeXml(
            'CoordinateTaskDataMessage',
            'http://asguf.mos.ru/rkis_gu/coordinate/v6_1/',
            [],
            $data
        );
        $message = $this->prepareXml($message);
        $message = $this->signMessage($message);

        $this->send($message);
    }

    /**
     * @param array $data
     */
    protected function setTestData(array &$data)
    {
        if (isset($data['Data']['Parameter']['ServiceProperties'][self::PARAM_INN])) {
            switch ($data['Data']['DocumentTypeCode']) {
                case self::TYPE_EGRIP:
                    $data['Data']['Parameter']['ServiceProperties']['testmsg'] = '';
                    break;

                case self::TYPE_EGRUL:
                    $data['Data']['Parameter']['ServiceProperties']['testmsg'] = '';
                    break;

                case self::TYPE_PASSPORT:
                    break;
            }
        } elseif (isset($data['Data']['Parameter']['ServiceProperties'][self::PARAM_OGRN])) {
            $data['Data']['Parameter']['ServiceProperties']['testmsg'] = '';
        }
    }

    /**
     * @param string $message
     * @return string
     * @throws JcpException
     */
    protected function prepareXml(string $message) :string
    {
        $dom = new \DOMDocument();
        if (!$dom->loadXML($message)) {
            throw new JcpException('Не удалось прочитать сообщение с подписью');
        }

        $serviceProperties = $dom->getElementsByTagName('ServiceProperties')->item(0);
        $serviceProperties->setAttributeNS("", "xmlns", "");
        return $dom->C14N();
    }

    /**
     * @param string $message
     * @throws JcpException
     * @throws \Exception
     */
    protected function send(string $message)
    {
        try {
            $client = new Client();
            $request = new Request(
                'POST',
                $this->url,
                [
                    'Content-Type' => 'text/xml'
                ],
                $message
            );
            $response = $client->send($request);

            if ($response->getStatusCode() != 200) {
                throw new JcpException("ошибка отправки сообщения В АС УР");
            }
        } catch (ConnectException $e) {
            throw new \Exception("ошибка подключения к сервису отправки сообщения");
        }
    }

    /**
     * @param $message
     * @return string
     * @throws \Exception
     */
    public function signMessage($message)
    {
        try {
            $signService = $this->getSignService();
            $sign = $signService->getSign($message);
            $dom = new \DOMDocument();
            if (!$dom->loadXML($sign)) {
                throw new JcpException('Не удалось прочитать сообщение с подписью');
            }

            $signature = $dom->getElementsByTagName('Signature')->item(0);
            $task = $dom->getElementsByTagName('CoordinateTaskDataMessage')->item(0);

            $xml  = '<CoordinateTaskMessage xmlns="http://asguf.mos.ru/rkis_gu/coordinate/v6_1/">';
            $xml .= $signature->C14N(true, false, null, ['ds']);
            $xml .= $task->C14N(true);
            $xml .= '</CoordinateTaskMessage>';
            return $xml;
        } catch (ConnectException $e) {
            \Yii::error($e, 'asur');
            throw new \Exception("ошибка подключения к сервису подписи сообщения");
        } catch (\Exception $e) {
            \Yii::error($e, 'asur');
            throw new \Exception('Ошибка при подписании сообщения');
        }
    }
}
