<?php

namespace app\common\behaviors;

use app\common\components\ticket\TicketGeneratorInterface;
use app\common\models\VisitStatus;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Class TicketNumberBehavior
 * @package app\common\behaviors
 *
 * @property \app\models\db\Visits $owner
 */
class TicketNumberBehavior extends Behavior
{
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => function($event) {
                $this->generate();
            },
            BaseActiveRecord::EVENT_BEFORE_UPDATE => function($event) {
                if (($this->owner->getDirtyAttributes(['id_organization', 'start_dttm', 'channel']) &&
                    in_array($this->owner->status, [VisitStatus::NEW, VisitStatus::CHANGED],  true)
                    || ($this->owner->isAttributeChanged('id_organization') && $this->owner->status == VisitStatus::IN_WORK)
                )
                ) {
                    $this->generate();
                }
            },
        ];
    }

    /**
     * @return TicketGeneratorInterface
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getTicketGenerator()
    {
        return \Yii::$container->get('app\common\components\ticket\TicketGeneratorInterface', [], [
            'visit' => $this->owner
        ]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function generate()
    {
        $generator = $this->getTicketGenerator();
        $this->owner->number = $generator->getVisitNumber();
        $this->owner->ticket_number = $generator->make();
    }
}
