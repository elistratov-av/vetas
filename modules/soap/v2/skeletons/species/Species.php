<?php

namespace app\modules\soap\v2\skeletons\species;

/**
 * Класс-каркас ВИД животного.
 * Ответ.
 */
class Species
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

    /**
     * @var \app\modules\soap\v2\skeletons\species\BreedsList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $BreedsList;
}
