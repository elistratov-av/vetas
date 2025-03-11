<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

use yii\base\Model;
use yii\base\UnknownPropertyException;

/**
 * Class RegByServiceNumberAnimal
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberAnimal extends Model
{
    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $PetId;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $SpeciesId;

    /**
     * @soap
     * @var string {minOccurs=1, maxOccurs=1}
     */
    public $SpeciesName;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $BreedId;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $BreedName;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $NicknameAnimal;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ChipAnimal;

    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $BirthdateAnimal;

    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $SexAnimal;

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $e) {
            // просто игнорируем
        }
    }
}
