<?php

namespace app\modules\soap\v2\models\etp\members;

use app\modules\soap\models\Breeds;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\Species;
use app\modules\soap\validators\CallToHomeVisitServicesValidator;
use app\modules\soap\validators\VisitServicesValidator;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ServiceProperties
 * @package app\modules\soap\v2\models\etp\members
 */
class ServiceProperties extends Model
{
    /**
     * @var \app\modules\soap\v2\models\etp\members\Service[]
     */
    public $ServiceList = [];
    /**
     * @var int
     */
    public $SpeciesId;
    /**
     * @var string
     */
    public $SpeciesName;
    /**
     * @var int
     */
    public $BreedId;
    /**
     * @var string
     */
    public $BreedName;
    /**
     * @var string
     */
    public $NicknameAnimal;
    /**
     * @var string
     */
    public $ChipAnimal;
    /**
     * @var string
     */
    public $BirthdateAnimal;
    /**
     * @var int
     */
    public $SexAnimal;
    /**
     * @var int
     */
    public $SpecialistId;
    /**
     * @var string
     */
    public $SpecialistName;
    /**
     * @var int
     */
    public $OrgId;
    /**
     * @var string
     */
    public $OrgName;
    /**
     * @var string
     */
    public $OrgAddressName;
    /**
     * @var float
     */
    public $OrgLatitude;
    /**
     * @var float
     */
    public $OrgLongitude;
    /**
     * @var string
     */
    public $OrgPhone;
    /**
     * @var string
     */
    public $Date;
    /**
     * @var string
     */
    public $Slot;
    /**
     * @var string
     */
    public $TicketNumber;
    /**
     * @var bool
     */
    public $CallToHome;
    /**
     * @var \app\modules\soap\v2\models\etp\members\AddressCall
     */
    public $AddressCall;
    /**
     * @var string
     */
    public $PetId;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (is_array($this->ServiceList) && !empty($this->ServiceList)) {
            $data = [];
            foreach ($this->ServiceList as $serviceData) {
                if (ArrayHelper::isAssociative($serviceData, false)) {
                    $data[] = new Service($serviceData);
                } else {
                    foreach ($serviceData as $entry) {
                        $data[] = new Service($entry);
                    }
                }
            }
            $this->ServiceList = $data;
        }
        if ($this->CallToHome === true && is_array($this->AddressCall) && !empty($this->AddressCall)) {
            $this->AddressCall = new AddressCall($this->AddressCall);
        }
        if (!empty($this->BirthdateAnimal)) {
            $date = new \DateTime($this->BirthdateAnimal);
            $this->BirthdateAnimal = $date->format('Y-m-d');
        }
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['SpeciesId', 'SpecialistId', 'OrgId', 'ServiceList', 'CallToHome'], 'required'],
            [['SpeciesId', 'BreedId', 'SpecialistId', 'OrgId'], 'integer'],
            [['Date', 'Slot', 'TicketNumber', 'PetId', 'NicknameAnimal', 'ChipAnimal'], 'string'],
            ['SpeciesId', 'exist', 'skipOnError' => true, 'targetClass' => Species::class, 'targetAttribute' => 'id'],
            [
                'BreedId',
                'exist',
                'skipOnError' => true,
                'targetClass' => Breeds::class,
                'targetAttribute' => 'id',
                'isEmpty' => function ($value) {
                    return $value === null || $value === [] || $value === '' || $value === 0 || $value === '0';
                },
            ],
            ['OrgId', 'exist', 'skipOnError' => true, 'targetClass' => MosruOrganizations::class, 'targetAttribute' => 'id'],
            ['SpecialistId', 'exist', 'skipOnError' => true, 'targetClass' => MosruSpecialists::class, 'targetAttribute' => 'id_user'],
            ['BirthdateAnimal', 'date', 'format' => 'php:Y-m-d'],
            ['SexAnimal', 'in', 'skipOnEmpty' => true, 'range' => [
                ETP::SEX_ANIMAL_EMPTY, ETP::SEX_ANIMAL_MALE, ETP::SEX_ANIMAL_FEMALE,
            ]],
            ['AddressCall', 'required', 'when' => function ($model) {
                /* @var $model $this */
                return $model->CallToHome === true;
            }],
            ['ServiceList', 'validateServiceList'],
        ];
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateServiceList($attribute, $params, $validator)
    {
        $ids = ArrayHelper::getColumn($this->ServiceList, 'ServiceId');

        $model = DynamicModel::validateData(['ids' => $ids], [
            ['ids', VisitServicesValidator::class]
        ]);
        if ($model->hasErrors()) {
            $this->addErrors($model->getErrors());
            return;
        }

        if ($this->CallToHome === true) {
            $model = DynamicModel::validateData(['ids' => $ids], [
                ['ids', CallToHomeVisitServicesValidator::class]
            ]);
            if ($model->hasErrors()) {
                $this->addErrors($model->getErrors());
            }
        }
    }
}
