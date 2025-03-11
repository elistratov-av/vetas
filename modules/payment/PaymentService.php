<?php

namespace app\modules\payment;

use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\Request;

class PaymentService
{
    // Метод ИС Ветас по которому принимается колбэк от ЕПШ
    CONST GATEWAY_CALLBACK_URL = 'v2/payment/payment/payment-callback';
    CONST TELEVET_SERVICE_GUID = 'AAAA3ee6720000005406';
    private $apiUrl;
    /** @var Request */
    private $request;

    public function __construct()
    {
        $config = \Yii::$app->params['payment_gateway_config'];
        $this->apiUrl = $config['gateway_url'];
        $login = $config['login'];
        $password = $config['password'];
        $auth = base64_encode("$login:$password");

        $this->request = (new Client())
            ->createRequest()
            ->addHeaders(['Authorization' => "Basic $auth"])
        ;
    }

    /**
     * @param string $serviceCode код услуги из каталога ИС ЕПШ
     * @param string $amount сумма платежа в рублях (в документации указано в копейках, но когда уточнили - сказали приводить к рублям, пример '100.50'
     * @param string $narrative
     * @param string $idNumber айди документа, указаного в idType запроса (предположительно СНИЛС)
     * @return mixed
     * @throws \yii\httpclient\Exception
     */
    public function registerPayment(string $serviceCode, string $amount, string $narrative, string $idNumber = null)
    {
        $this->setRequest('POST', '/payment/register');
        $response = $this->request
            ->addData([
                'portal' => [
                    'portalId' => 'vetas',
                    'paymentResultUrl' => Url::home('https') . self::GATEWAY_CALLBACK_URL,
                ],
                'service' => [
                    'serviceCode' => $serviceCode,
                ],
                'amount' => $amount,
                'serviceParams' => [
                    /* TODO: по СНИЛСу ЕПШ находит услугу (при оплате в интерфейсе ЕПШ пишет, что "Найдено по СНИЛС"
                        соответственно здесь однозначно некорректно передавать id пользователя и либо надо передавать
                        снилс организации в которой записывается пользователь, либо зарегестрировашей услугу в ЕПШ
                    */

                    //'idType' => 'SNILS',
                    //'idNumber' => $idNumber,
                    'Narrative' => $narrative,
                ],
            ])
            ->send()
        ;

        if ($response->statusCode != 202) {
            throw new \Exception("Произошла ошибка при обращении к api ЕПШ: $response->content");
        }

        return $response->getData();
    }

    /**
     * При регистрации платежа в ЕПШ передаётся адрес колбека для принятия статуса платежей, но на всякий случай добавил
     * метод для ручной проверки статуса
     *
     * @param string $uuid
     * @return mixed
     * @throws \yii\httpclient\Exception
     */
    public function getPaymentStatusByUid(string $uuid)
    {
        $this->setRequest('GET', '/history/uid_status');
        $response = $this->request
            ->addData([
                'uuid' => $uuid
            ])
            ->send()
        ;

        if ($response->statusCode != 200) {
            throw new \Exception("Произошла ошибка при обращении к api ЕПШ: $response->content");
        }

        return $response->getData();
    }

    public function getServiceParams(string $serviceCode = 'AAAA3c6cb70000002849')
    {
        $this->setRequest('POST', '/charges/service_params/basic');
        $response = $this->request
            ->addData([
                'serviceCode' => $serviceCode,
            ])
            ->send()
        ;

        if ($response->statusCode != 200) {
            throw new \Exception("Произошла ошибка при обращении к api ЕПШ: $response->content");
        }

        return $response->getData();
    }

    private function setRequest(string $requestType, string $endpoint)
    {
        $this->request
            ->setMethod($requestType)
            ->setUrl($this->apiUrl . $endpoint);

        if ($requestType === 'POST') {
            $this->request->setFormat(Client::FORMAT_JSON);
        }
    }
}
