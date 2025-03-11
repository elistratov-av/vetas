<?php

namespace app\modules\elk\types;

use app\modules\soap\models\etp\ETP;
use yii\base\Model;

class Animal extends Model
{
    /**
     * @var string {nilable=false, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ID;

    /**
     * @var integer {nilable=false, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $SpeciesID;

    /**
     * @var integer {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $BreedID;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $NickName;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $Chip;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $BirthDate;

    /**
     * @var integer {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $Sex;

    /**
     * @return array
     */
    public function getAnimalData() : array
    {
        return [
            'id_breed' => $this->BreedID,
            'id_species' => $this->SpeciesID,
            'name' => $this->NickName,
            'sex' => $this->getMappedSex(),
            'birthday' => $this->getBirthday(),
        ];
    }

    /**
     * @return array
     */
    public function getElkPetData()
    {
        return [
            'id_breed' => $this->BreedID,
            'id_species' => $this->SpeciesID,
            'name' => $this->NickName,
            'sex' => $this->getMappedSex(),
            'birthday' => $this->getBirthday(),
            'chip' => $this->Chip
        ];
    }

    /**
     * @return null|string
     */
    protected function getMappedSex()
    {
        switch ($this->Sex) {
            case ETP::SEX_ANIMAL_MALE:
                $sex = 'm';
                break;

            case ETP::SEX_ANIMAL_FEMALE:
                $sex = 'f';
                break;

            default:
                $sex = null;
                break;
        }

        return $sex;
    }

    protected function getBirthday()
    {
        if (!empty($this->BirthDate)) {
            try {
                $date = new \DateTime($this->BirthDate);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }

        return null;
    }
}
