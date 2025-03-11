<?php


namespace app\modules\soap\skeletons\registration_by_service_number;


class RegByServiceNumberAddressCall
{
    /**
     * @var string {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $fias_code;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $address_name;
}
