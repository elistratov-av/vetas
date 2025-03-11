<?php

namespace app\modules\soap\v2\skeletons\species;

/**
 * Класс-каркас список ВИДОВ животных.
 * Ответ.
 */
class SpeciesList
{
    /**
     * @var \app\modules\soap\v2\skeletons\species\Species[] list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Species;
}
