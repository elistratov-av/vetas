<?php

namespace app\modules\soap\skeletons\types;

class OrgSpecListType
{
    /**
     * @var boolean {nilable=true, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $call_to_home;

    /**
     * @var \app\modules\soap\skeletons\types\ServicesIdType[] {minOccurs=1, maxOccurs=unbounded}
     * @soap
     */
    public $services;

}
