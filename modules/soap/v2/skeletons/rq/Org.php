<?php

namespace app\modules\soap\v2\skeletons\rq;

/**
 * Class Org
 * @package app\modules\soap\v2\skeletons\rq
 */
class Org
{
    /**
     * @var integer
     * @soap
     */
    public $Id;

    /**
     * @var \app\modules\soap\v2\skeletons\rq\SpecialistIdList[] {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Specialists;
}
