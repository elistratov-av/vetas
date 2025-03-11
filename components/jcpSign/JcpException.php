<?php

namespace app\common\components\jcpSign;

use yii\base\Exception;

class JcpException extends Exception
{
    public function getName()
    {
        return 'Ошибка получения подписи';
    }
}
