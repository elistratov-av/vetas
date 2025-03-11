<?php

namespace app\modules\soap\skeletons\species;

/**
 * Класс-каркас список ВИДОВ животных.
 * Ответ.
 */
class SpeciesList
{
    /**
     * @var \app\modules\soap\skeletons\species\Species[] list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $species;
}
