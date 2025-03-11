<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class Specialization
 * @package app\modules\soap\v2\skeletons\orgs
 */
class Specialization
{
    /**
     * @var integer id {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $id;

    /**
     * @var string Name {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $name;
}
