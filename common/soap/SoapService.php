<?php

namespace app\common\soap;

use app\common\models\ExternalApiLogs;
use Yii;

class SoapService extends \subdee\soapserver\SoapService
{
    /**
     * Handles the web service request.
     */
    public function run()
    {
        header('Content-Type: text/xml;charset=' . $this->encoding);
        if (YII_DEBUG) {
            ini_set("soap.wsdl_cache_enabled", 0);
        }

        $server = $this->createSoapServer();
        $log = new ExternalApiLogs();
        try {
            if ($this->persistence !== null) {
                $server->setPersistence($this->persistence);
            }
            if (is_string($this->provider)) {
                $provider = $this->provider;
                $provider = new $provider();
            } else {
                $provider = $this->provider;
            }
            $server->setObject($provider);
            ob_start();
            try {
                $log->request_type = Yii::$app->request->getMethod();
                $log->request_ip = Yii::$app->request->userIP;
                $log->request_url = Yii::$app->request->absoluteUrl;
                $log->request_headers = json_encode(Yii::$app->request->getHeaders()->toArray());
                $log->request_body = file_get_contents('php://input');
                $server->handle($this->getSoapRequest());
            } catch (SoapException $e) {
                $log->response_body = $e->getMessage();
                $log->save();
                $server->fault($e->getCode(), $e->getMessage());
            } catch (\Throwable $e) {
                $log->response_body = $e->getMessage();
                $log->save();
                throw $e;
            }
            $soapXml = ob_get_contents();
            $log->response_body = $soapXml;
            $log->save();
            ob_end_clean();
            return $this->processSoapResponse($soapXml);
        } catch (\Throwable $e) {
            $log->response_body = $e->getMessage();
            $log->save();
            throw $e;
        }
    }

    /**
     * @return \SoapServer
     */
    protected function createSoapServer()
    {
        $parsed = parse_url($this->wsdlUrl);
        $scheme = ((isset($parsed['scheme'])) ? $parsed['scheme'] : 'http') . '://';
        list(, $hash) = explode(' ', \Yii::$app->getRequest()->getHeaders()->get('authorization') . ' ');
        $auth = $hash ? base64_decode($hash) . '@' : '';

        return new \SoapServer(str_replace($scheme, $scheme . $auth, $this->wsdlUrl), $this->getOptions());
    }

    /**
     * @return string
     */
    protected function getSoapRequest()
    {
        return \Yii::$app->request->getRawBody();
    }

    /**
     * @param string $xml
     * @return string
     */
    protected function processSoapResponse($xml)
    {
        return $xml;
    }
}
