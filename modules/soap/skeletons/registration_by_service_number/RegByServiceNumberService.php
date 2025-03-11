<?php


namespace app\modules\soap\skeletons\registration_by_service_number;


class RegByServiceNumberService
{
    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $id;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $name;
}
