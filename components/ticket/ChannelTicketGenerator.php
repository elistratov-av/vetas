<?php

namespace app\common\components\ticket;
use app\models\db\Visits;
use yii\db\Expression;

/**
 * Класс для генерации номера талона для записи на прием через mos.ru, по телефону, по направлению
 * Шаблон номера талона ДДКННН, где
 * ДД - день месяца от 01 до 31
 * К - код канала
 * ННН - порядковый номер в этот день для этого канала
 *
 * Class ChannelTicketGenerator
 * @package app\common\components
 */
class ChannelTicketGenerator extends TicketGenerator
{
    /**
     * @return int
     */
    public function getVisitNumber() : int
    {
        return Visits::find()
            ->select(new Expression("COALESCE(MAX(number), 0) + 1"))
            ->where(['id_organization' => $this->visit->id_organization])
            ->andWhere(['channel' => $this->visit->channel])
            ->andWhere(new Expression("start_dttm::date = :date", [
                'date' => date('Y-m-d', strtotime($this->visit->start_dttm))
            ]))
            ->scalar();
    }

    /**
     * @return string
     */
    public function make(): string
    {
        return "{$this->getDay()}{$this->getChannel()}{$this->getNumber()}";
    }

    /**
     * @return string
     */
    protected function getDay() : string
    {
        return date('d', strtotime($this->visit->start_dttm));
    }

    /**
     * @return string
     */
    protected function getChannel() : string
    {
        return $this->visit->channel;
    }

    /**
     * @return string
     */
    protected function getNumber() : string
    {
        return sprintf("%'.03d", $this->visit->number);
    }

}
