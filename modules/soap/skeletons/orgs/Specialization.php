<?php
/**
 * Created by PhpStorm.
 * Users: user
 * Date: 05.07.18
 * Time: 19:46
 */

namespace app\modules\soap\skeletons\orgs;


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
