<?php

namespace app\modules\soap\models\etp;

use app\modules\soap\models\etp\response\serviceproperties;
use app\modules\soap\models\etp\status\StatusInterface;
use app\modules\soap\models\Visits;

interface CoordinateMessageInterface
{
    /**
     * Инициализация данных
     */
    public function initAttributes() : void;

    /**
     * Обработка входящего сообщения
     */
    public function process() : void;

    /**
     * Формирование ответа для отправки в ЕТП
     * @param StatusInterface $status
     * @return string
     */
    public function makeResponseData(StatusInterface $status) : string;

    /**
     * @return string
     */
    public function getServiceNumber() : string;

    /**
     * @return null|string
     */
    public function getStatusId() : ?string;

    /**
     * @return array
     */
    public function getResponsible() : array;

    /**
     * @return array
     */
    public function getDepartment() : array;

    /**
     * @return Visits
     */
    public function getVisit();

    /**
     * @param string $message
     */
    public function sendErrorMessage(string $message) : void;

    /**
     * @return serviceproperties|array
     */
    public function getCustomAttributes();
}
