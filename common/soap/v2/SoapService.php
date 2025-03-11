<?php

namespace app\common\soap\v2;

use app\common\soap\SoapException;

/**
 * Class SoapService
 * @package app\common\soap\v2
 */
class SoapService extends \app\common\soap\SoapService
{
    /**
     * @inheritDoc
     */
    public function generateWsdl()
    {
        // вместо генератора будем возвращать статический xml
        $fileName = (\Yii::$app->request->get('fix') == '1') ? 'PetsServiceRpc.wsdl' : 'PetsService.wsdl';
        $xml = file_get_contents(\Yii::getAlias('@modules') . '/soap/config/' . $fileName);

        return strtr($xml, ['{{serviceUrl}}' => $this->serviceUrl]);
    }

    /**
     * @return \SoapServer
     */
    protected function createSoapServer()
    {
        $parsed = parse_url($this->wsdlUrl);
        $scheme = $parsed['scheme'] ?? 'http';

        if ('file' === $scheme) {
            return new \SoapServer($this->wsdlUrl, $this->getOptions());
        }

        $scheme .= '://';

        list(, $hash) = explode(' ', \Yii::$app->getRequest()->getHeaders()->get('authorization') . ' ');
        $auth = $hash ? base64_decode($hash) . '@' : '';

        return new \SoapServer(str_replace($scheme, $scheme . $auth, $this->wsdlUrl . '?fix=1'), $this->getOptions());
    }

    /**
     * Фикс для конвертации document/literal для \SoapServer
     * SoapServer does not support WSDL with literal/document
     * @return string
     */
    protected function getSoapRequest()
    {
        $xml = \Yii::$app->request->getRawBody();

        if (preg_match('#<(.{0,10}?):Envelope#isu', $xml, $matches)) {
            $prefix = $matches[1];
        } else {
            $prefix = 'soapenv';
        }

        $xml = preg_replace('#<(' . $prefix . ':)#isu', '<#', $xml);
        $xml = preg_replace('#</(' . $prefix . ':)#isu', '</#', $xml);

        $xml = preg_replace('#<([a-z0-9]+):#isu', '<', $xml);
        $xml = preg_replace('#</([a-z0-9]+):#isu', '</', $xml);

        $xml = preg_replace('#<\#Body>(.*?)<(.*?)Request(.*?)</\#Body>#isu', '<#Body>$1<$2><$2Request$3</$2></#Body>', $xml);

        $xml = preg_replace('#<\##isu', '<' . $prefix . ':', $xml);
        $xml = preg_replace('#</\##isu', '</' . $prefix . ':', $xml);

        // whenever preg_replace fails - it returns null and request died silently
        // added error for easier localization of issues
        if (!$xml) {
//            $error = array_flip(array_filter(get_defined_constants(true)['pcre'], function ($value) {
//                return substr($value, -6) === '_ERROR';
//            }, ARRAY_FILTER_USE_KEY))[preg_last_error()];

            throw new SoapException("error occurred during request parsing");
        }

        return trim($xml);
    }

    /**
     * Фикс ответа от \SoapServer (возвращает без ns и делает лишний SOAP-ENV)
     * @param string $xml
     * @return string
     */
    protected function processSoapResponse($xml)
    {
        $xml = (string)$xml;

        $xml = preg_replace('#<SOAP-ENV:Body>[\n\r\s]*<SOAP-ENV:[a-z]+>[\n\r\s]*<(.*?)>[\n\r\s]*</SOAP-ENV:[a-z]+>[\n\r\s]*</SOAP-ENV:Body>#isu', '<SOAP-ENV:Body><$1></SOAP-ENV:Body>', $xml);
        $xml = preg_replace('#<SOAP-ENV:Body>[\n\r\s]*<(.*?)>[\n\r\s]*<(.*)>[\n\r\s]*</(.*?)>[\n\r\s]*</SOAP-ENV:Body>#isu', '<SOAP-ENV:Body><ns1:$1><$2></ns1:$1></SOAP-ENV:Body>', $xml);

        // важно! так как после замены длина меняется, и может вернуться ошибка или вообще не вернуться ответ
        header('Content-Length: ' . mb_strlen($xml, '8bit'));

        return $xml;
    }
}
