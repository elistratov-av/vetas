<?php

namespace app\modules\soap\controllers;

use yii\base\Controller;

class BaseController extends Controller
{
    /**
     * Сохраняет название метода SOAP текущего запроса в экземпляре модуля,
     * используется для логирования.
     * 
     * @param string $name
     */
    protected function reportSoapMethod(string $name): void
    {
        $this->module->soapMethod = $name;
    }
}
