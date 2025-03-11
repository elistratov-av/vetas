<?php

namespace app\modules\soap\models\etp\monitoring;

use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\etp\status\Status10090;
use app\modules\soap\models\etp\status\Status10091;
use app\modules\soap\models\etp\status\Status1050;

/**
 * Класс для обработки статусного сообщения 1010 от ЕТП - "Запись на прием" посланного от мониторинга mos.ru
 * Считаем что сообщение послано от мониторинга если параметр SsoId в BaseDeclarant соответствует значению указанному
 * в параметре $params['monitoring']['SsoId'] в конфиге модуля
 *
 * Class CoordinateMessageMonitoring
 * @package app\modules\soap\models\etp
 */
class CoordinateMessage extends \app\modules\soap\models\etp\CoordinateMessage
{
    use CustomAttributesTrait;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            /** @var ETP $etp */
            $etp = \Yii::$app->getModule('soap')->etp;
            $etp->sendStatusMessage(new Status1050(), $this);
            sleep(1);
            $etp->sendStatusMessage(new Status10090(), $this);
            sleep(1);
            $etp->sendStatusMessage(new Status10091(), $this);
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }

}
