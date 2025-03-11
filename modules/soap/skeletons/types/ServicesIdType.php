<?php

namespace app\modules\soap\skeletons\types;

class ServicesIdType
{
    /**
     * @var integer {minOccurs=1, maxOccurs=unbounded}
     * @soap
     */
    public $id;
}
