<?php

namespace app\common\soap\v2;

/**
 * Class SoapAction
 * @package app\common\soap\v2
 */
class SoapAction extends \app\common\soap\SoapAction
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        ini_set("soap.wsdl_cache_enabled", 0);

        parent::init();
    }

    /**
     * @param mixed $provider the web service provider class name or object
     * @param string $wsdlUrl the URL for WSDL.
     * @param string $serviceUrl the URL for the Web service.
     * @return \app\common\soap\v2\SoapService the Web service instance
     */
    protected function createWebService($provider, $wsdlUrl, $serviceUrl)
    {
        return new SoapService($provider, $wsdlUrl, $serviceUrl, $this->wsdlOptions);
    }
}
