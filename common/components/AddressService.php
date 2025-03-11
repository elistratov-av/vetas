<?php

namespace app\common\components;


use app\common\components\address\AdapterInterface;
use yii\base\Component;
use yii\base\UnknownMethodException;

class AddressService extends Component
{

    public $adapter;

    public function init()
    {
        /**
         * @throws \Exception
         * @throws \yii\base\InvalidConfigException
         */
        parent::init();
        $this->adapter = \Yii::createObject($this->adapter);
        if (!$this->adapter instanceof AdapterInterface) {
            throw new Exception('`' . get_class($this) . '::adapter` should be an instance of `' . address\AdapterInterface::class . '` or its DI compatible configuration.');
        }


    }

    public function __call($name, $arguments)
    {
        if ($this->adapter->hasMethod($name)) {
            return call_user_func_array([$this->adapter, $name], $arguments);
        }
        throw new UnknownMethodException('Calling unknown method: ' . get_class(AdapterInterface::class) . "::$name()");
    }
}
