<?php

namespace app\common\components\ticket;

use app\models\db\Visits;
use yii\base\Component;

abstract class TicketGenerator extends Component implements TicketGeneratorInterface
{
    /** @var Visits */
    public $visit;

}
