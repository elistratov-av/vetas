<?php

namespace app\modules\soap\skeletons\species;


class BreedsList
{
    /**
     * @var \app\modules\soap\skeletons\species\Breed[] list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $breed;
}
