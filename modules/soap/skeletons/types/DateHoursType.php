<?php

namespace app\modules\soap\skeletons\types;

class DateHoursType
{
    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $date;

    /**
     * @var time {nillable=1, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $hours_since;

    /**
     * @var time {nillable=1, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $hours_till;
}
