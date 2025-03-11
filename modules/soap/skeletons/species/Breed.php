<?php

namespace app\modules\soap\skeletons\species;

/**
 * Класс-каркас ПОРОДА животного.
 * Ответ.
 */
class Breed
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
