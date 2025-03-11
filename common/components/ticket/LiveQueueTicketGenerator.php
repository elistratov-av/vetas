<?php

namespace app\common\components\ticket;
use app\models\db\Visits;
use yii\db\Expression;

/**
 * Класс для генерации номера талона для записи на прием через mos.ru, по телефону, по направлению
 * Шаблон номера талона ДДССОНН, где
 * ДД - день месяца от 01 до 31
 * СС - код специализации
 * O - 0 - общая очередь, 1 - к специалисту
 * ННН - порядковый номер в этот день для этого канала
 *
 * Class LiveQueueTicketGenerator
 * @package app\common\components
 */
class LiveQueueTicketGenerator extends TicketGenerator
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
            ->andWhere(new Expression("created_at::date = :date", [
                'date' => date('Y-m-d')
            ]))
            ->scalar();
    }

    /**
     * @return string
     */
    public function make(): string
    {
        return "{$this->getDay()}{$this->getType()}{$this->getNumber()}";
    }

    /**
     * @return string
     */
    protected function getDay() : string
    {
        return (new \DateTime())->format('d');
    }

    /**
     * @return string
     */
    protected function getType() : string
    {
        return $this->visit->getFlagVisitToSpecialist() ? 1 : 0;
    }

    /**
     * @return string
     */
    protected function getNumber() : string
    {
        return sprintf("%'.03d", $this->visit->number);
    }
}
