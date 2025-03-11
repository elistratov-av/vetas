<?php

namespace app\modules\soap\v2\skeletons\species;


class SpeciesResponse
{
    /**
     * @var \app\modules\soap\v2\skeletons\species\SpeciesList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $SpeciesList;
}
