<?php

namespace app\modules\soap\v2\skeletons\types;

/**
 * Class DateHoursType
 * @package app\modules\soap\v2\skeletons\types
 */
class DateHoursType
{
    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Date;

    /**
     * @var time {nillable=1, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $HoursSince;

    /**
     * @var time {nillable=1, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $HoursTill;
}
