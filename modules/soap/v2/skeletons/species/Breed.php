<?php

namespace app\modules\soap\v2\skeletons\species;

/**
 * Класс-каркас ПОРОДА животного.
 * Ответ.
 */
class Breed
{
    /**
     * @var integer Id {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Id;

    /**
     * @var string Name {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Name;
}
