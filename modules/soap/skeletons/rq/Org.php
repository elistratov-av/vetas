<?php

namespace app\modules\soap\skeletons\rq;

class Org
{
    /**
     * @var integer
     * @soap
     */
    public $id;

    /**
     * @var \app\modules\soap\skeletons\rq\SpecialistIdList[] {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $specialists;
}
