<?php


namespace app\modules\soap\skeletons\registration_by_service_number;


class RegByServiceNumberOwner
{
    /**
     * @soap
     * @var string {minOccurs=1, maxOccurs=1}
     */
    public $first_name;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $last_name;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $middle_name;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $mobile_phone;

}
