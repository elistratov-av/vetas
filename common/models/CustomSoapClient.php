<?php

namespace app\common\models;

use SoapClient;
use SoapServer;

class CustomSoapClient extends SoapClient
{
    public $server;

    function __construct($wsdl, array $options)
    {
        parent::__construct($wsdl, $options);
        $this->server = new SoapServer($wsdl, $options);
    }

    function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $result = parent::__doRequest($request, $location, $action, $version);
        return $result;
    }

    function __myDoRequest($request) {
        $request = trim($request);
        $location = 'https://er4.mos.ru/EHDWS/soap';
        $action = 'setDataInRequest';
        $version = '1';
        $result = $this->__doRequest($request, $location, $action, $version);
        return $result;
    }
}
