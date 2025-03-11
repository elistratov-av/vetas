<?php

namespace app\modules\soap\models\etp\monitoring;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\etp\status\Status1090;


/**
 * Класс для обработки статусного сообщения 1069 от ЕТП - "Отмена приема по инициативе пользователя" посланного от
 * мониторинга mos.ru
 * Считаем что сообщение послано от мониторинга если параметр SsoId в BaseDeclarant соответствует значению указанному
 * в параметре $params['monitoring']['SsoId'] в конфиге модуля
 *
 * Class CoordinateStatusMessage1069
 * @package app\modules\soap\models\etp
 */
class CoordinateStatusMessage1069 extends \app\modules\soap\models\etp\CoordinateStatusMessage1069
{
    use CustomAttributesTrait;

    /** @var string */
    public $visit_date;

    public function initAttributes() : void
    {
        parent::initAttributes();
        $this->visit_date = (new \DateTime())->format('Y-m-d H:i:s');
    }


    public function rules()
    {
        return [
            [['service_number', 'department'], 'required']
        ];
    }

    /**
     * Обработка входящего сообщения
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            /** @var ETP $etp */
            $etp = \Yii::$app->getModule('soap')->etp;
            $etp->sendStatusMessage(new Status10190(), $this);
            sleep(1);
            $etp->sendStatusMessage(new Status10191(), $this);
            sleep(1);
            $etp->sendStatusMessage(new Status1090(), $this);
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }

    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return [];
    }
}
