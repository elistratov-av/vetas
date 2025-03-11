<?php

namespace app\modules\soap\v2\skeletons\services;

/**
 * Class Service
 * @package app\modules\soap\v2\skeletons\services
 */
class Service
{
    /**
     * @var integer ServiceId {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServiceId;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServiceValue;

    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $AtHome;

    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $AtClinic;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Rating;

    /**
     * @var string {nilable=true, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Hint;
}
