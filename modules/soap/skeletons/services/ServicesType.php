<?php

namespace app\modules\soap\skeletons\services;


class ServicesType
{

    /**
     * @var integer ype_id {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $type_id;

    /**
     * @var string type_value; {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $type_value;

    /**
     * @var \app\modules\soap\skeletons\services\ServicesList[] service_list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $service_list;

}
