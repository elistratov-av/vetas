<?php

namespace app\modules\mdm\models;

use app\modules\soap\models\Breeds;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\Species;
use yii\base\Model;

class Pet extends Model
{
    /**
     * Идентификатор животного в ЕЛК
     * @var string
     */
    public $pet_id;

    /**
     * Кличка питомца
     * @var string
     */
    public $name;

    /**
     * Вид животного питомца
     * @var string
     */
    public $species;

    /**
     * Порода питомца
     * @var string
     */
    public $breed;

    /**
     * Дата рождения питомца
     * @var string
     */
    public $birth_date;

    /**
     * Пол питомца
     * @var string
     */
    public $gender;

    /**
     * Номер чипа питомца
     * @var string
     */
    public $chip_number;

    /**
     * Признак удаления животного
     * @var bool
     */
    public $deleted = false;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['pet_id', 'species'], 'required', 'message' => 'Не передан параметр {attribute}'],
            [
                'species', 'exist', 'skipOnError' => true, 'targetClass' => Species::class,
                'targetAttribute' => ['species' => 'id']
            ],
            [
                'breed', 'exist', 'skipOnError' => true, 'targetClass' => Breeds::class,
                'targetAttribute' => ['breed' => 'id']
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAnimalData() : array
    {
        return [
            'id_breed' => $this->breed,
            'id_species' => $this->species,
            'name' => $this->name,
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
            'id_breed' => $this->breed,
            'id_species' => $this->species,
            'name' => $this->name,
            'sex' => $this->getMappedSex(),
            'birthday' => $this->getBirthday(),
            'chip' => $this->chip_number
        ];
    }

    /**
     * @return null|string
     */
    protected function getMappedSex()
    {
        switch (strtolower($this->gender)) {
            case ETP::SEX_ANIMAL_MALE:
            case 'm':
                $sex = 'm';
                break;

            case ETP::SEX_ANIMAL_FEMALE:
            case 'f':
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
        if (!empty($this->birth_date)) {
            try {
                $date = new \DateTime($this->birth_date);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }

        return null;
    }
}
