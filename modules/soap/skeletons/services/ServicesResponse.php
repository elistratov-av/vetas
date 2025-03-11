<?php


namespace app\modules\soap\skeletons\services;


class ServicesResponse
{
    /**
     * @var \app\modules\soap\skeletons\services\ServicesTypeList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $services_type_list;
}
