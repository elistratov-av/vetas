<?php

namespace app\common\soap;

class SoapAction extends \subdee\soapserver\SoapAction
{
    /**
     * Creates a {@link CWebService} instance.
     * You may override this method to customize the created instance.
     *
     * @param mixed $provider the web service provider class name or object
     * @param string $wsdlUrl the URL for WSDL.
     * @param string $serviceUrl the URL for the Web service.
     * @return SoapService the Web service instance
     */
    protected function createWebService($provider, $wsdlUrl, $serviceUrl)
    {
        return new SoapService($provider, $wsdlUrl, $serviceUrl, $this->wsdlOptions);
    }
}
