<?php

namespace app\modules\soap\skeletons\services;


class Service
{
    /**
     * @var integer service_id {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $service_id;

    /**
     * @var string service value {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $service_value;

    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $at_home;

    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $at_clinic;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $rating;

    /**
     * @var string {nilable=true, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $hint;
}
