<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use app\models\db\elk\ElkPets;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "pets".
 *
 * @property int                         $id
 * @property string                      $reg_expire_date
 * @property string                      $birthday
 * @property string                      $name
 * @property string                      $sex
 * @property int                         $id_species
 * @property int                         $id_breed
 * @property int                         $id_reg_organization          ID организации, в которой было зарегистрировано животное (при взятии в работу приема)
 * @property int|null                    $id_reg_expire_reason
 * @property int                         $photo
 * @property int                         $created_by                   Автор добавления (id пользователя)
 * @property int                         $updated_by                   Автор последнего изменения (id пользователя)
 * @property string                      $created_at                   Дата создания
 * @property string                      $updated_at                   Дата изменения
 * @property string                      $reg_date
 * @property bool                        $guide_dog
 * @property bool                        $castrated
 * @property string                      $date_plan_rabies_vaccination Дата плановой вакцинации от бешенства
 * @property string                      $date_plan_identification     Дата плановой идентификации, указанной вручную
 * @property string                      $date_plan_lept_vaccination   Дата плановой вакцинации от лептоспироза (актуально для собак)
 * @property string                      $color                        Окрас
 * @property string                      $characteristics              Особые приметы
 * @property int                         $id_created_organization      ID организации, в которой было создано животное (не путать с $id_reg_organization)
 * @property bool                        $is_main                      Признак основной записи
 * @property int                         $id_main_pet                  ID основной записи
 * @property int                         $id_relocate                  Признак переноса записи - ID дублирующей
 * @property string                      $duble_validation             Дата проведения проверки на дубли
 * @property string                      $description
 * @property int                         $id_brood
 * @property int                         $id_fias_address              Адрес содержания животного
 * @property bool                        $is_address_pet_owners        Адрес содержания совпадает с адресом владельца
 * @property string|null                 $reg_number
 * @property int|null                    $size_id
 * @property int|null                    $id_pet_tmp
 * @property int|null                    $color_id
 * @property string|null                 $character
 * @property int|null                    $ear_type_id
 * @property int|null                    $tail_type_id
 * @property int|null                    $wool_type_id
 *
 * @property-read QuarantineDetourNonVisit[] $quarantine_detour_non_visit
 * @property-read PetIdentification[]    $pet_identification
 * @property-read PetOwnersHistory[]     $petOwnersHistories
 * @property-read PetDehelmintization[]  $pet_dehelmintizations
 * @property-read PetEctoparasites[]     $pet_ectoparasites
 * @property-read PetOtherVaccinations[] $pet_other_vaccinations
 * @property-read PetRabiesVaccination[] $pet_rabies_vaccinations
 * @property-read VisitsGovServices      $visitsGovServices
 * @property-read Breeds                 $breeds
 * @property-read Files                  $photo0
 * @property-read Organizations          $regOrganization
 * @property-read Organizations          $createdOrganization
 * @property-read RegExpireReasons       $reg_expire_reason
 * @property-read Species                $species
 * @property-read PetsToOwner[]          $pets_to_owner
 * @property-read PetOwners[]            $owners
 * @property-read PetOwners              $owner
 * @property-read RegCertificates        $reg_certificate
 * @property-read Visits[]               $visits
 * @property-read Visits                 $visit
 * @property-read ShelterGuests[]        $shelter_records
 * @property-read Pets[]                 $duplicates
 * @property-read Pets                   $mainPet
 * @property-read Violation[]            $violations
 * @property-read Brood[]                $brood
 * @property-read ElkPets                $elk_pet                      Запись животного в таблице животных мосру (elk.pets)
 * @property-read FiasAddresses          $fias_address
 * @property-read QuarantineFocus        $quarantineFocus
 * @property-read VisitServiceTmcPet     $visitServiceTmcPet
 * @property-read Visits                 $visitDetour
 * @property-read Visits                 $visitShelter
 * @property-read ShelterVaccineRejection[] $shelterRejections
 * @property-read PetRefColor $petRefColor
 * @property-read PetRefSize $petRefSize
 * @property-read PetRefEarType $petRefEarType
 * @property-read PetRefTailType $petRefTailType
 * @property-read PetRefWoolType $petRefWoolType
 * @property-read Pets                   $tmpPet
 */
class Pets extends ActiveRecord
{
    public const SEX_MALE = 'm';
    public const SEX_FEMALE = 'f';

    public const SIZE_SMALL = 0;
    public const SIZE_BIG = 10;

    public const SEX_TYPES = [
        self::SEX_MALE,
        self::SEX_FEMALE,
    ];

    public const SEX_NAMES = [
        self::SEX_MALE => 'мужской',
        self::SEX_FEMALE => 'женский',
    ];

    public const SIZES = [
        self::SIZE_SMALL,
        self::SIZE_BIG,
    ];

    public const SIZE_NAMES = [
        self::SIZE_SMALL => 'мелкое',
        self::SIZE_BIG => 'крупное',
    ];
    public const GENDER_MALE = 'm';
    public const GENDER_FEMALE = 'f';

    // const SIZE_SMALL = 0;
    // const SIZE_BIG = 10;

    public const GENDER_TYPES = [
        self::GENDER_MALE => 'мужской',
        self::GENDER_FEMALE => 'женский',
    ];

    /**
     * @event Event an event that is triggered after save vaccination.
     */
    const EVENT_AFTER_SAVE_VACCINATION = 'afterSaveVaccination';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.pets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        //TODO: с mos.ru поля не заполняются! ИГОРЬ!!!!!!!!
        //[['id_species', 'guide_dog', 'castrated'], 'required'],
        return [
            [['created_at', 'updated_at', 'reg_date', 'reg_number'], 'safe'],
            [
                ['id_breed', 'id_reg_organization', 'id_reg_expire_reason', 'photo', 'created_by', 'updated_by'],
                'default',
                'value' => null
            ],
            [['id_species'], 'required'],
            [['id_breed'], 'required', 'when' => function ($model) {
                return !$model->mosru;
            }],
            [['name', 'characteristics'], FullTrimValidator::class],
            [
                [
                    'id_species',
                    'id_breed',
                    'id_reg_organization',
                    'id_reg_expire_reason',
                    'photo',
                    'created_by',
                    'updated_by',
                    'color_id'
                ],
                'integer'
            ],
            [['guide_dog', 'castrated'], 'boolean'],
            [['name'], 'string', 'max' => 50],
            [['sex'], 'string', 'max' => 1],
            [['sex'], 'in', 'range' => self::SEX_TYPES, 'strict' => true],
            [['size_id', 'ear_type_id', 'tail_type_id', 'wool_type_id',], 'integer'],
            [
                ['id_breed'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Breeds::class,
                'targetAttribute' => ['id_breed' => 'id']
            ],
            [
                ['color_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PetRefColor::class,
                'targetAttribute' => ['color_id' => 'id']
            ],
            [
                ['size_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PetRefSize::class,
                'targetAttribute' => ['size_id' => 'id']
            ],
            [
                ['ear_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PetRefEarType::class,
                'targetAttribute' => ['ear_type_id' => 'id']
            ],
            [
                ['tail_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PetRefTailType::class,
                'targetAttribute' => ['tail_type_id' => 'id']
            ],
            [
                ['wool_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PetRefWoolType::class,
                'targetAttribute' => ['wool_type_id' => 'id']
            ],
            [
                ['photo'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Files::class,
                'targetAttribute' => ['photo' => 'id']
            ],
            [
                ['id_reg_organization'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Organizations::class,
                'targetAttribute' => ['id_reg_organization' => 'id']
            ],
            [
                ['id_reg_expire_reason'],
                'exist',
                'skipOnError' => true,
                'targetClass' => RegExpireReasons::class,
                'targetAttribute' => ['id_reg_expire_reason' => 'id']
            ],
            [
                ['id_species'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Species::class,
                'targetAttribute' => ['id_species' => 'id']
            ],
            [
                [
                    'reg_expire_date',
                    'birthday',
                    'date_plan_lept_vaccination',
                    'date_plan_rabies_vaccination',
                    'date_plan_identification'
                ],
                'date',
                'format' => 'php:Y-m-d'
            ],
            [
                'id_reg_expire_reason',
                'safe',
                'when' => function ($model) {
                    return !empty($model->reg_expire_date);
                },
                'enableClientValidation' => false,
            ],
            [
                'reg_expire_date',
                'safe',
                'when' => function ($model) {
                    return !empty($model->id_reg_expire_reason);
                },
                'enableClientValidation' => false,
            ],
            /**
             *  Соответсвие породы виду
             */
            [
                'id_breed',
                function ($attribute, $params, $validator) {
                    if (
                        $this->id_breed !== null
                        && $this->breeds->name !== Breeds::NOT_SELECTED_NAME
                    ) {

                        $check = Breeds::find()
                            ->where([
                                'id' => $this->id_breed,
                                'species_id' => $this->id_species,
                            ])->exists();

                        if ($check != true) {
                            $this->addError($attribute, "Выбранная порода не соответствует выбранному виду");
                        }
                    }
                }
            ],
            [
                ['date_plan_lept_vaccination'],
                function ($attribute, $params, $validator) {
                    /*
                     * Доступна только для собак
                     */
                    $check = Species::find()
                        ->where(
                            [
                                'AND',
                                ['id' => $this->id_species],
                                ['tech_name' => Species::TECH_NAME_DOG],
                            ]
                        )->exists();

                    if ($check != true) {
                        $this->addError(
                            $attribute,
                            "Указание даты плановой вакцинации от лептоспироза доступно только для собак"
                        );
                    }

                    $currentDate = new \DateTime();
                    $datePlanLept = date_create_from_format('Y-m-d', $this->date_plan_lept_vaccination);
                    if ($datePlanLept < $currentDate) {
                        $this->addError(
                            $attribute,
                            "Дата плановой вакцинации от лептоспироза не может быть меньше текущей даты"
                        );
                    }
                },
                'skipOnError' => true
            ],
            [['color', 'characteristics', 'character'], 'string'],
            [['color', 'characteristics', 'description', 'character'], 'filter', 'filter' => 'trim'],
            ['description', 'string', 'max' => 400],
            [['color', 'characteristics', 'character'], 'filter', 'filter' => 'strip_tags'],
            ['id_created_organization', 'integer', 'skipOnEmpty' => true],
            [
                'id_created_organization',
                'exist',
                'skipOnError' => true,
                'targetClass' => Organizations::class,
                'targetAttribute' => ['id_created_organization' => 'id']
            ],
            [['is_main', 'id_main_pet', 'id_relocate', 'duble_validation'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reg_expire_date' => 'Reg Expire Date',
            'birthday' => 'Birthday',
            'name' => 'Name',
            'sex' => 'Sex',
            'id_species' => 'Id Species',
            'id_breed' => 'Id Breed',
            'id_reg_organization' => 'Id Reg Organization',
            'id_reg_expire_reason' => 'Id Reg Expire Reason',
            'photo' => 'Photo',
            'created_by' => 'Автор добавления (id пользователя)',
            'updated_by' => 'Автор последнего изменения (id пользователя)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'reg_date' => 'Reg Date',
            'guide_dog' => 'Guide Dog',
            'castrated' => 'Castrated',
            'castrated_date' => 'Дата стерилизации',
            'castrated_specialist_id' => 'ФИО врача, который проводил стерилизацию',
            'castrated_org_id' => 'Клиника, в которой проводилась стерилизация',
            'early_castrated' => 'Ранее стерилизован',
            'date_plan_rabies_vaccination' => 'Дата плановой вакцинации от бешенства',
            'date_plan_identification' => 'Дата плановой идентификации',
            'date_plan_lept_vaccination' => 'Дата плановой вакцинации от лептоспироза',
            'description' => 'Описание',
        ];
    }

    public function extraFields()
    {
        return [
            'color' => 'colorRef',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getColor()
    {
        return $this->hasOne(PetRefColor::class, ['id' => 'color_id']);
    }

    /**
     * костыль для совместимости с коробочным API
     * @return ActiveQuery
     */
    public function getColorRef()
    {
        return $this->hasOne(PetRefColor::class, ['id' => 'color_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPetRefColor()
    {
        return $this->hasOne(PetRefColor::class, ['id' => 'color_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPetRefSize()
    {
        return $this->hasOne(PetRefSize::class, ['id' => 'size_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPetRefEarType()
    {
        return $this->hasOne(PetRefEarType::class, ['id' => 'ear_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPetRefTailType()
    {
        return $this->hasOne(PetRefTailType::class, ['id' => 'tail_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPetRefWoolType()
    {
        return $this->hasOne(PetRefWoolType::class, ['id' => 'wool_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(FileResource::class, ['entity_id' => 'id']);
    }

    /**
     * костыль для совместимости с коробочным API
     * @return ActiveQuery
     */
    public function getWoolTypeRef()
    {
        return $this->hasOne(PetRefWoolType::class, ['id' => 'wool_type_id']);
    }

    /**
     * @return PetsQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new PetsQuery(get_called_class());
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $isRegExpire = $this->id_reg_expire_reason !== null;
        $isDouble = $this->id_main_pet !== null;

        if ($isRegExpire || $isDouble) {
            /** @var Violation[] $violations */
            $violations = Violation::find()->where([
                'id_pet' => $this->id,
                'state' => Violation::ACTIVE_STATES
            ])->all();

            $cancellationDetails = $isRegExpire ? 'снятие с учёта' : 'животное отмечено как дубль';

            foreach ($violations as $violation) {
                if (!$violation->hasExpiredOrders()) {
                    /** @var ViolationCancellation $cancellation */
                    $cancellation = ViolationCancellation::find()->where(['is_need_cancellation_details' => true])->one();
                    (new ViolationChangeStateModel(['id_violation' => $violation->id_violation]))->cancel($cancellation->id_cancellation, $cancellationDetails);
                }
            }
        }

        return true;
    }

    /**
     * Животные снятые с учета не могут редактироваться
     */
    public function isReadOnly()
    {
        return (
            //            !$this->isNewRecord && (
            //                !empty($this->getOldAttribute('id_reg_expire_reason')) ||
            //                !empty($this->getOldAttribute('reg_expire_date'))
            //            )
            $this->getOldAttribute('id_reg_expire_reason') === 8
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet_identification()
    {
        return $this->hasMany(PetIdentification::class, ['id_pet' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescription_types()
    {
        return $this->hasMany(DescriptionTypes::class, ['id' => 'id_description_type'])
            ->viaTable(VisitDescriptions::tableName(), ['id_pet' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptions()
    {
        return $this->hasMany(VisitDescriptions::class, ['id_pet' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet_main_identification()
    {
        return $this->hasOne(PetIdentification::class, ['id_pet' => 'id'])
            ->where(['main_flag' => true]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetOwnersHistories()
    {
        return $this->hasMany(PetOwnersHistory::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet_dehelmintizations()
    {
        return $this->hasMany(PetDehelmintization::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet_ectoparasites()
    {
        return $this->hasMany(PetEctoparasites::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet_rabies_vaccinations()
    {
        return $this->hasMany(PetRabiesVaccination::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLast_pet_rabies_vaccinations()
    {
        return $this->hasOne(PetRabiesVaccination::class, ['id_pet' => 'id'])->orderBy(['date' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFindiagnosis()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreatment()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_SKHEMA_LECHENIYA_5']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommendations()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_REKOMENDATSII']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSymptoms()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_SIMPTOMY_2']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreDiagnosis()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_PREDVARITELNYJ_DIAGNOZ_3']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDateIllness()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_DATA_ZABOLEVANIYA']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdditionalResearch()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_DOPOLNITELNYE_ISSLEDOVANIYA']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClinicalSigns()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_KLINICHESKIE_PRIZNAKI']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssurance()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_LECHEBNAYA_POMOSHCH']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConclusion()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_ZAKLYUCHENIE']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDataClinic()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_CLINICAL_DATA']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet_other_vaccinations()
    {
        return $this->hasMany(PetOtherVaccinations::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBreeds()
    {
        return $this->hasOne(Breeds::class, ['id' => 'id_breed']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoto0()
    {
        return $this->hasOne(Files::class, ['id' => 'photo']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_reg_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizations()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_reg_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReg_expire_reason()
    {
        return $this->hasOne(RegExpireReasons::class, ['id' => 'id_reg_expire_reason']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecies()
    {
        return $this->hasOne(Species::class, ['id' => 'id_species']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets_to_owner()
    {
        return $this->hasMany(PetsToOwner::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwners()
    {
        return $this->hasMany(PetOwners::class, ['id' => 'id_owner'])->viaTable('pets_to_owner', ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReg_certificate()
    {
        return $this->hasOne(RegCertificates::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this->hasMany(Visits::class, ['id' => 'id_visit'])
            ->viaTable(VisitPets::tableName(), ['id_pet' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisit()
    {
        return $this->hasOne(Visits::class, ['id' => 'id_visit'])
            ->viaTable(VisitPets::tableName(), ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrood()
    {
        return $this->hasOne(Brood::class, ['id' => 'id_brood']);
    }

    /**
     * @return ActiveQuery
     */
    public function getShelter()
    {
        return $this->hasOne(ShelterGuests::class, ['id_pet' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getShelterGuests()
    {
        return $this->getShelter();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner'])
            ->viaTable('pets_to_owner', ['id_pet' => 'id'], function ($query) {
                /* @var $query \yii\db\ActiveQuery */
                return $query->andWhere([
                    'pets_to_owner.id_owner_type' => (new Query())
                        ->select('id')
                        ->from(PetOwnerType::tableName())
                        ->where(['is_owner' => true])
                        ->column(),
                ]);
            });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFias_address()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }

    /**
     * @param int|null $id_organization
     * @param bool     $active
     *
     * @return \app\models\db\ShelterGuests|null
     */
    public function getLastShelterRecord($id_organization = null, $active = false)
    {
        $query = ShelterGuests::find()
            ->where([
                'id_pet' => $this->id,
            ])
            ->orderBy([
                'arrival_date' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->limit(1);
        if (!empty($id_organization)) {
            $query->andWhere(['id_organization' => $id_organization]);
        }
        if ($active === true) {
            $query->andWhere(['departure_date' => null]);
        } else {
            $query->with('pet_owner');
        }

        return $query->one();
    }

    /**
     * @param int|null $id_organization
     *
     * @return \app\models\db\ShelterGuests|null
     */
    public function getLastActiveShelterRecord($id_organization = null)
    {
        return $this->getLastShelterRecord($id_organization, true);
    }

    /**
     * Записи о нахождении животного в приюте
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShelter_records()
    {
        return $this->hasMany(ShelterGuests::class, ['id_pet' => 'id'])
            ->orderBy(['arrival_date' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_created_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDuplicates()
    {
        return $this->hasMany(Pets::class, ['id_main_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_main_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViolations()
    {
        return $this->hasMany(Violation::class, ['id_pet' => 'id']);
    }

    /**
     * Pretty sex name
     *
     * @return string|null
     */
    public function getSexName(): ?string
    {
        return self::SEX_NAMES[$this->sex] ?? null;
    }

    /**
     * Pretty sex name
     *
     * @return string|null
     */
    public function getGenderLabel(): ?string
    {
        return self::GENDER_TYPES[$this->sex] ?? null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElk_pet()
    {
        return $this->hasOne(ElkPets::class, ['id_pet' => 'id']);
    }

    /**
     * Вовзращает PetsQuery с добавленным условием для выборки дублей.
     *
     * @param int|int[] $id_pet ID оригинальной(ых) записи(ей)
     *
     * @return PetsQuery
     */
    public static function findDuplicates($id_pet = [])
    {
        $where = [
            'is_main' => false,
        ];
        if (!empty($id_pet)) {
            $where['id_main_pet'] = $id_pet;
        }

        return (new PetsQuery(get_called_class()))->where($where);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits_descriptions()
    {
        return $this->hasMany(VisitDescriptions::class, ['id_pet' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getQuarantine_detour_non_visit()
    {
        return $this->hasMany(QuarantineDetourNonVisit::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreement_surg()
    {
        return $this->hasOne(Agreements::class, ['id_pet' => 'id'])   //здесь hasOne для того, чтобы в visit/get
            ->andOnCondition(['is_agree' => true])                          //дофильтровать имеющиеся согласия
            ->andOnCondition(['id_type' => AgreementTypes::SURGERY]);       //и вывести нужный объект, не массив
    }

    public function getQuarantineFocus(): ActiveQuery
    {
        return $this->hasOne(QuarantineFocus::class, ['id_pet' => 'id']);
    }

    public function getVisitDetour(): ActiveQuery
    {
        return $this->hasMany(Visits::class, ['id' => 'id_visit'])
            ->viaTable(VisitPets::tableName(), ['id_pet' => 'id'])
            ->where(['visits.type' => Visits::TYPE_VISIT_VC_DETOUR])
            ->orderBy(['visits.start_dttm' => SORT_DESC]);
    }

    public function getVisitShelter(): ActiveQuery
    {
        return $this->hasMany(Visits::class, ['id' => 'id_visit'])
            ->viaTable(VisitPets::tableName(), ['id_pet' => 'id'])
            ->where(['visits.type' => Visits::TYPE_VISIT_VC_SHELTER])
            ->orderBy(['visits.start_dttm' => SORT_DESC]);
    }

    public function getShelterRejections(): ActiveQuery
    {
        return $this->hasMany(ShelterVaccineRejection::class, ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisitServiceTmcPet()
    {
        return $this->hasOne(VisitServiceTmcPet::class, ['id_pet' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCastratedSpecialist()
    {
        return $this->hasOne(Specialists::class, ['id' => 'castrated_specialist_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCastratedOrg()
    {
        return $this->hasOne(Organizations::class, ['id' => 'castrated_org_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwners_history()
    {
        return $this->hasMany(PetOwnersHistory::class, ['id_pet' => 'id']);
    }

    public function getPetHealths()
    {
        return $this->hasMany(PetHealth::class, ['id_pet' => 'id']);
    }

    /**
     * @return Pets
     */
    public function getMainIfExists(): self
    {
        if ($this->is_main || !$this->id_main_pet) {
            return $this;
        }

        return static::findOne($this->id_main_pet) ?: $this;
    }

    /**
     * @param $diseaseId
     * @return bool
     */
    public function hasActiveVaccination($diseaseId): bool
    {
        $rabiesId = Diseases::find()->where(['name' => Diseases::NAME_RABIES])->one()->id;
        switch ($diseaseId) {
            case $rabiesId:
                $tableName = 'pet_rabies_vaccination';
                $query = PetRabiesVaccination::find();
                break;
            default:
                $tableName = 'pet_other_vaccinations';
                $query = PetOtherVaccinations::find();
        }
        $activeVaccine = $query
            ->innerJoin('tmc.tmc_to_diseases ttd', "ttd.id_tmc = $tableName.id_vaccine AND ttd.id_disease = $diseaseId")
            ->where(["$tableName.id_pet" => $this->id])
            ->andWhere("$tableName.valid_until > NOW()")
            ->one();

        return !!$activeVaccine;
    }

    public function getSkills()
    {
        return $this->hasMany(PetRefSkill::class, ['id' => 'id_skill'])
            ->viaTable('pet_to_skills', ['id_pet' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmpPet()
    {
        return $this->hasOne(TmpPets::class, ['id' => 'id_pet_tmp']);
    }

    /**
     * Переопределяет родительский метод для подмены имени в случае наличия временных данных
     *
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $maskedAttrs = [
            'name',
            'birthday',
        ];
        if (!in_array($name, $maskedAttrs, false)) {
            return parent::__get($name);
        }

        if (!$tmpPet = $this->tmpPet) {
            return parent::__get($name);
        }

        return $tmpPet->{$name};
    }

    public function create(
        $nickname,
        $id_species,
        $id_breed,
        $gender_male
    ): int {
        $pets = new Pets();

        $pets->name = $nickname;
        $pets->id_species = $id_species;
        $pets->id_breed = $id_breed;
        if (!is_null($gender_male)) {
            if ($gender_male) {
                $pets->sex = 'm';
            } else {
                $pets->sex = 'f';
            }
        }
        if (!$pets->save()) {
            $errors = $pets->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании питомца' : implode("\n", array_values($errors)));
        }

        return $pets->id;
    }

    /**
     *  Метод для получения номера телефона владельца животного
     *
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function getPhoneContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id_owner'])
            ->viaTable('pets_to_owner', ['id_pet' => 'id'])
            ->where([
                'and',
                ['contacts.entity_type' => 'pet_owner'],
                ['in', 'id_contact_type', (new Query())->select('id')->from(ContactTypes::tableName())->where(['type' => ContactTypes::TYPE_PHONE])],
            ]);
    }

    /**
     * @throws BadRequestHttpException
     */
    public function edit(
        $id_pet,
        $nickname = null,
        $id_species = null,
        $id_breed = null,
        $gender_male = null
    ) {
        $pet = Pets::findOne(['id' => $id_pet]);
        if (!empty($pet)) {
            $pet->name = $nickname ?? $pet->name;
            $pet->id_species = $id_species ?? $pet->id_species;
            $pet->id_breed = $id_breed ?? $pet->id_breed;
            if (!is_null($gender_male)) {
                if ($gender_male) {
                    $pet->sex = 'm';
                } else {
                    $pet->sex = 'f';
                }
            }
            if (!$pet->save()) {
                $errors = $pet->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при модификации питомца' : implode("\n", array_values($errors)));
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFias_addresses()
    {
        return $this->hasOne(FiasAddresses::class, ['id' => 'id_fias_address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */

    public function getHealth()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_CLINICAL_DATA'])->orderBy('id_visit desc');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnamnesis()
    {
        return $this->hasOne(VisitDescriptions::class, ['id_pet' => 'id'])
            ->leftJoin('description_types dt', 'dt.id = visit_descriptions.id_description_type')
            ->leftJoin('visit_pets vp', 'vp.id_visit = visit_descriptions.id_visit')
            ->andWhere(['dt.tech_name' => 'VISIT_ANAMNEZ_1'])->orderBy('id_visit desc');
    }
}
