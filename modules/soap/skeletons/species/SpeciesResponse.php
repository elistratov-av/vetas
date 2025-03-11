<?php

namespace app\modules\soap\skeletons\species;


class SpeciesResponse
{
    /**
     * @var \app\modules\soap\skeletons\species\SpeciesList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $species_list;
}
