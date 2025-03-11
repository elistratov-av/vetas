<?php

namespace app\modules\soap\skeletons\species;

/**
 * Класс-каркас ВИД животного.
 * Ответ.
 */
class Species
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

    /**
     * @var \app\modules\soap\skeletons\species\BreedsList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $breeds_list;
}
