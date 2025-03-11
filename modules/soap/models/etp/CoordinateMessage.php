<?php

namespace app\modules\soap\models\etp;

use app\common\components\inform\jobs\RefreshSubscriptionsJob;
use app\common\helpers\PhoneHelper;
use app\common\models\VisitStatus;
use app\common\validators\VisitEmergencyValidator;
use app\models\db\ContactTypes;
use app\models\db\PetsToOwner;
use app\models\db\ShiftType;
use app\models\MosruNotification;
use app\modules\soap\models\Breeds;
use app\modules\soap\models\Contacts;
use app\modules\soap\models\etp\status\Status103099;
use app\modules\soap\models\MosruOrganizations;
use app\modules\soap\models\MosruSpecialists;
use app\modules\soap\models\PetIdentification;
use app\modules\soap\models\PetOwners;
use app\modules\soap\models\Pets;
use app\modules\soap\models\Species;
use app\modules\soap\models\Visits;
use app\modules\soap\validators\CallToHomeVisitDateValidator;
use app\modules\soap\validators\CallToHomeVisitServicesValidator;
use app\modules\soap\validators\ServicesSpecialistValidator;
use app\modules\soap\validators\VisitDateValidator;
use app\modules\soap\validators\VisitServicesValidator;
use app\modules\soap\validators\VisitTimeRangeValidator;
use yii\db\Query;

/**
 * Класс для обработки статусного сообщения 1010 от ЕТП - "Запись на прием"
 *
 * Class CoordinateMessage
 * @package app\modules\soap\models\etp
 *
 * @property string $mobile_phone
 * @property string $email
 * @property string|null  $last_name
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $snils
 * @property string|null $sso_id
 * @property string|null $birthdate
 * @property mixed $organization_id
 * @property string $visit_date
 * @property string|null $chip_animal
 * @property integer|null $breed_id
 * @property integer|null $species_id
 * @property string|null $nickname_animal
 * @property string|null $sex_animal
 * @property mixed $birthdate_animal
 * @property int $user_id
 * @property bool $call_to_home
 * @property string|null $address_call
 * @property string|null $pet_id
 * @property array $service_id
 *
 * @property Visits|array|null|\yii\db\ActiveRecord $visit
 * @property MosruSpecialists|array|null|\yii\db\ActiveRecord $specialist
 * @property MosruOrganizations[]|array|\yii\db\ActiveRecord[] $services
 */
class CoordinateMessage extends CoordinateStatusMessage implements CoordinateMessageInterface
{
    /** @var string  */
    protected $errorStatus = Status103099::class;

    /** @var string */
    public $mobile_phone;

    /** @var string */
    public $email;

    /** @var string|null */
    public $last_name;

    /** @var string|null */
    public $first_name;

    /** @var string|null  */
    public $middle_name;

    /** @var string|null  */
    public $snils;

    /** @var string|null  */
    public $sso_id;

    /** @var string|null */
    public $birthdate;

    /** @var mixed */
    public $organization_id;

    /** @var string */
    public $visit_date;

    /** @var string|null  */
    public $chip_animal;

    /** @var integer|null  */
    public $breed_id;

    /** @var integer|null  */
    public $species_id;

    /** @var  string|null */
    public $nickname_animal;

    /** @var string|null */
    public $sex_animal;

    /** @var string|null */
    public $birthdate_animal;

    /** @var int */
    public $user_id;

    /** @var bool */
    public $call_to_home;

    /** @var string|null  */
    public $address_call;

    /** @var array */
    public $service_id;

    /** @var string|null */
    public $pet_id;

    /** @var bool  */
    protected $isNewOwner = false;

    /**
     * @throws \Exception
     */
    public function initAttributes() : void
    {
        $this->initUserAttributes();
        $this->initServiceProperties();
    }

    /**
     * @throws \Exception
     */
    public function initUserAttributes()
    {
        $baseDeclarant = $this->data['SignService']['Contacts']['BaseDeclarant'];

        $this->service_number = $this->data['Service']['ServiceNumber'];
        $this->responsible = $this->data['Service']['Responsible'];
        $this->department = $this->data['Service']['Department'];
        $this->mobile_phone = (isset($baseDeclarant['MobilePhone']) && !empty($baseDeclarant['MobilePhone']))
            ? PhoneHelper::extractMosRuPhoneNumber($baseDeclarant['MobilePhone']) : null;
        $this->email = (isset($baseDeclarant['EMail']) && !empty($baseDeclarant['EMail']))
            ? $baseDeclarant['EMail'] : null;
        $this->last_name = isset($baseDeclarant['LastName']) ? $baseDeclarant['LastName'] : null;
        $this->first_name = isset($baseDeclarant['FirstName']) ? $baseDeclarant['FirstName'] : null;
        $this->middle_name = (!empty($baseDeclarant['MiddleName'])) ? $baseDeclarant['MiddleName'] : null;
        $this->snils = (!empty($baseDeclarant['Snils'])) ? $baseDeclarant['Snils'] : null;

        $this->birthdate = (!empty($baseDeclarant['BirthDate']) && is_string($baseDeclarant['BirthDate'])) ? $baseDeclarant['BirthDate'] : null;
        if (isset($baseDeclarant['SsoId']) && !empty($baseDeclarant['SsoId']) && $baseDeclarant['SsoId'] != 'unauthorized') {
            $this->sso_id = $baseDeclarant['SsoId'];
        }

        if (!is_null($this->birthdate)) {
            $date = new \DateTime($this->birthdate);
            $this->birthdate = $date->format('Y-m-d');
        }

        $this->address_call = !empty($baseDeclarant['FactAddress']['POBox'])
            ? $baseDeclarant['FactAddress']['POBox']
            : null;
    }

    /**
     * @throws \Exception
     */
    public function initServiceProperties()
    {
        $serviceProperties = $this->data['SignService']['CustomAttributes']['ServiceProperties'];

        $this->organization_id = $serviceProperties['org_id'];

        if (!empty($serviceProperties['date']) && !empty($serviceProperties['slot'])) {
            $this->visit_date = $this->getVisitDate($serviceProperties['date'], $serviceProperties['slot']);
        }

        $this->chip_animal = (!empty($serviceProperties['chip_animal'])) ? $serviceProperties['chip_animal'] : null;

        $this->species_id = $serviceProperties['species_id'] ? $serviceProperties['species_id'] : null;
        $this->breed_id = !empty($serviceProperties['breed_id']) ? $serviceProperties['breed_id'] : null;
        $this->nickname_animal = !empty($serviceProperties['nickname_animal']) ? $serviceProperties['nickname_animal'] : null;
        $this->sex_animal = (isset($serviceProperties['sex_animal'])) ? $serviceProperties['sex_animal'] : null;
        $this->birthdate_animal = !empty($serviceProperties['birthdate_animal']) ? $serviceProperties['birthdate_animal'] : null;
        if (!is_null($this->birthdate_animal)) {
            $date = new \DateTime($this->birthdate_animal);
            $this->birthdate_animal = $date->format('Y-m-d');
        }

        $this->pet_id = isset($serviceProperties['pet_id']) ? $serviceProperties['pet_id'] : null;

        $this->user_id = (int)$serviceProperties['specialist_id'];
        if (!empty($this->user_id) && !empty($this->organization_id)) {
            $specialist = MosruSpecialists::find()
                ->where(['id_user' => $this->user_id])
                ->andWhere(['id_organization' => $this->organization_id])
                ->one();

            if ($specialist) {
                $this->_specialist = $specialist;
            }
        }

        $this->service_id = [];
        if (isset($serviceProperties['servicelist']['service']['service_id'])) {
            $this->service_id[] = $serviceProperties['servicelist']['service']['service_id'];
        } else {
            foreach($serviceProperties['servicelist']['service'] as $r) {
                $this->service_id[] = $r['service_id'];
            }
        }

        $callToHome = !empty($serviceProperties['call_to_home']) ? $serviceProperties['call_to_home'] : null;
        // TODO: сделать нормальную xsd которая будет валидировать кастомные поля и отдавать в правильном типе
        // пока хак
        $this->call_to_home = ($callToHome == 'true' || $callToHome == 1) ? true : false;
    }

    public function rules()
    {
        return [
            'required' => [
                [
                    'service_number', 'department', 'responsible', // общие данные
                    'mobile_phone', 'last_name', 'first_name', // владелец
                    'species_id', // животное
                    'user_id', 'service_id', 'visit_date', 'organization_id', 'call_to_home' // прием
                ],
                'required'
            ],
            [['sex_animal', 'species_id', 'breed_id', 'user_id', 'organization_id'], 'integer'],
            [['call_to_home'], 'boolean'],
            ['sex_animal', 'in', 'skipOnEmpty' => true, 'range' => [
                ETP::SEX_ANIMAL_EMPTY, ETP::SEX_ANIMAL_MALE, ETP::SEX_ANIMAL_FEMALE
            ]],
            ['email', 'email'],
            [['birthdate', 'birthdate_animal'], 'date', 'format' => 'php:Y-m-d'],
            ['species_id', 'exist', 'skipOnError' => true, 'targetClass' => Species::class, 'targetAttribute' => 'id'],
            ['breed_id', 'exist', 'skipOnError' => true, 'targetClass' => Breeds::class, 'targetAttribute' => 'id'],
            ['organization_id', 'exist', 'skipOnError' => true, 'targetClass' => MosruOrganizations::class, 'targetAttribute' => 'id'],
            ['visit_date', VisitDateValidator::class, 'when' => function($model) {
                return $model->call_to_home === false;
            }],
            ['visit_date', CallToHomeVisitDateValidator::class, 'when' => function($model) {
                return $model->call_to_home === true;
            }],
            [
                'visit_date',
                VisitEmergencyValidator::class,
                'when' => function($model) {
                    return $model->call_to_home === false;
                },
                'organization_id' => $this->organization_id,
                'message' => 'Невозможно осуществить запись на данное время в связи с экстренной ситуацией в клинике'
            ],
            [
                'service_id', ServicesSpecialistValidator::class,
                'specialist' => $this->specialist,
                'when' => function($model) {
                    return $model->specialist instanceof MosruSpecialists;
                }
            ],
            ['service_id', VisitServicesValidator::class],
            ['service_id', CallToHomeVisitServicesValidator::class, 'when' => function($model) {
                return $model->call_to_home;
            }],
            [
                'visit_date',
                VisitTimeRangeValidator::class,
                'callToHome' => $this->call_to_home,
                'services' => $this->services,
                'specialist' => $this->specialist,
                'when' => function($model) {
                    return $model->specialist instanceof MosruSpecialists;
                }
            ],
            ['address_call', 'required', 'when' => function(){
                return $this->call_to_home === true;
            }]
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            \Yii::$app->db->transaction(function () {
                $pet = $this->getPet();
                $petOwner = $this->getPetOwner();
                if (!$this->isNewOwner) {
                    $this->updatePetOwner($petOwner);
                }
                $this->saveMobilePhone($petOwner);
                $this->saveEmail($petOwner);
                $this->linkPetAndOwner($pet, $petOwner);
                $visit = $this->createVisit($pet, $petOwner);
                $this->saveMessage($visit);

                MosruNotification::visitCreate($visit->id);
            });
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }

    /**
     * @param Visits $visit
     * @throws ETPException
     */
    protected function saveMessage(Visits $visit)
    {
        $message = new ETPMessage([
            'scenario' => ETPMessage::SCENARIO_CREATE,
            'coordinateMessage' => $this
        ]);

        $message->message = $this->getRequestData();
        $message->service_number = $this->getServiceNumber();
        $message->phone = $this->mobile_phone;
        $message->last_name = $this->last_name;
        $message->first_name = $this->first_name;
        $message->middle_name = $this->middle_name;
        $message->visit_id = $visit->id;

        if (!$message->save()) {
            throw new ETPException("Ошибка обработки сообщения", 422);
        }
    }

    /**
     * @param array $errors
     * @return string
     */
    protected function processErrors(array $errors)
    {
        $msg = '';
        foreach ($errors as $error) {
            $msg .= implode("\r\n", $error);
        }
        return $msg;
    }

    /**
     * @param Pets      $pet
     * @param PetOwners $petOwner
     * @param array     $attributes дополнительные атрибуты приема
     * @return Visits
     * @throws \Exception
     */
    protected function createVisit(Pets $pet, PetOwners $petOwner, array $attributes = [])
    {
        $visit = new Visits();
        $visit->status = VisitStatus::NEW;
        $visit->id_organization = $this->organization_id;
        $visit->id_owner = $petOwner->id;
        $visit->id_pet = $pet->id;
        $visit->channel = ShiftType::findOne(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])->id;
        $visit->start_dttm = $this->visit_date;
        if ($this->call_to_home) {
            $visit->type = \app\models\db\Visits::TYPE_AT_HOME;
            $visit->visit_to_address = $this->address_call;
        }

        $visit->call_to_home = $this->call_to_home;

        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if ($visit->canSetProperty($attribute)) {
                    $visit->$attribute = $value;
                }
            }
        }

        $visit->setServices($this->services);
        $visit->setSpecialist($this->specialist);

        if (!$visit->save()) {
            throw new \Exception("Ошибка создания записи на прием:\n{$this->processErrors($visit->getErrors())}");
        }

        return $visit;
    }

    /**
     * @return PetOwners
     * @throws \Exception
     */
    protected function getPetOwner()
    {
        $petOwner = null;

        if (!empty($this->sso_id) && $petOwner = PetOwners::find()->bySsoId($this->sso_id)->one()) {
            // TODO - не решен вопрос, что делать если ФИО не совпадают
            // TODO - проверять ли снилс?
            return $petOwner;
        }

        if (!empty($this->snils) && $petOwner = PetOwners::find()->bySnils($this->snils)->one()) {
            // TODO - не решен вопрос, что делать если ФИО не совпадают
            return $petOwner;
        }

        /** @var PetOwners $petOwner */
        $query = PetOwners::find()
            ->byFio(
                $this->last_name,
                $this->first_name,
                $this->middle_name
            )
            ->byMobilePhone($this->mobile_phone)
            ->withoutSsoId();

        // Если в заявке передан СНИЛС и пользователя по нему не найдено (см. выше),
        // то ищем пользователя у которого СНИЛС не заполнен
        if (!empty($this->snils)) {
            $query->withoutSnils();
        }

        if ($petOwner = $query->one()) {
            return $petOwner;
        }

        return $this->createPetOwner();
    }

    /**
     * @return PetOwners
     * @throws \Exception
     */
    protected function createPetOwner()
    {
        $petOwner = new PetOwners();
        $petOwner->f_fio = $this->last_name;
        $petOwner->i_fio = $this->first_name;
        $petOwner->o_fio = $this->middle_name;
        $petOwner->snils = $this->snils;
        $petOwner->birthday = $this->birthdate;
        $petOwner->sso_id = $this->sso_id;

        if (!$petOwner->save()) {
            throw new \Exception("Ошибка создания владельца животного:\n{$this->processErrors($petOwner->getErrors())}");
        }

        $this->isNewOwner = true;

        return $petOwner;
    }

    /**
     * Обновление владельца (пока что СНИЛС, дата рождения, sso_id)
     * TODO: не решен вопрос, что делать если ФИО не совпадают!
     * TODO: СНИЛС и дата рождения обновляются только если переданы непустые, а были пустые!
     * @param PetOwners $owner
     */
    protected function updatePetOwner(PetOwners $owner)
    {
        $attributes = [];

        if (empty($owner->snils) && !empty($this->snils)) {
            $owner->snils = $this->snils;
            $attributes[] = 'snils';
        }
        if (empty($owner->birthday) && !empty($this->birthdate)) {
            $owner->birthday = $this->birthdate;
            $attributes[] = 'birthday';
        }
        if ($owner->sso_id != $this->sso_id) {
            $owner->sso_id = $this->sso_id;
            $attributes[] = 'sso_id';
        }

        if (!empty($attributes) && $owner->save(true, $attributes)) {
            if (in_array('sso_id', $attributes, true)) {
                \Yii::$app->subscription_queue->push(new RefreshSubscriptionsJob([
                    'id_owner' => $owner->id,
                    'sso_id' => $this->sso_id
                ]));
            }
        }
    }

    /**
     * Сохранение мобильного телефона
     * @param PetOwners $owner
     * @throws \Exception
     */
    protected function saveMobilePhone(PetOwners $owner)
    {
        if (empty($this->mobile_phone)) {
            return;
        }

        $contactType = ContactTypes::findOne(['name' => 'Мобильный телефон', 'type' => ContactTypes::TYPE_PHONE]);

        if (!$this->isContactExists($contactType->id, $owner->id, $this->mobile_phone)) {
            $contact = new Contacts();
            $contact->entity_type = 'pet_owner';
            $contact->entity_id = $owner->id;
            $contact->id_contact_type = $contactType->id;
            $contact->name = $this->mobile_phone;
            $contact->main_flag = $this->isNewOwner; // если новый пользователь, то считаем этот контакт основным
            $contact->confirmed = true;

            if (!$contact->save()) {
                throw new \Exception("Ошибка сохранения телефона:\n{$this->processErrors($contact->getErrors())}");
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_PHONE, $owner->id, $this->mobile_phone);
    }

    /**
     * Сохранение e-mail
     * @param PetOwners $owner
     * @throws \Exception
     */
    protected function saveEmail(PetOwners $owner)
    {
        if (empty($this->email)) {
            return;
        }

        $contactTypeEmail = ContactTypes::findOne(['name' => 'Электронная почта', 'type' => ContactTypes::TYPE_EMAIL]);

        if (!$this->isContactExists($contactTypeEmail->id, $owner->id, $this->email)) {
            $contactEmail = new Contacts();
            $contactEmail->entity_type = 'pet_owner';
            $contactEmail->entity_id = $owner->id;
            $contactEmail->id_contact_type = $contactTypeEmail->id;
            $contactEmail->name = $this->email;
            $contactEmail->confirmed = true;

            if (!$contactEmail->save()) {
                throw new \Exception("Ошибка сохранения почты:\n{$this->processErrors($contactEmail->getErrors())}");
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_EMAIL, $owner->id, $this->email);
    }

    /**
     * Удаление дублей контактов у других владельцев - в ЕЛК контакты должны быть уникальными.
     * Мы не будем отменять подписки (так как данные пришли от ЕЛК, подписки уже отменены на их стороне).
     * @param string $type
     * @param int    $id_owner
     * @param string $contact
     * @see https://jira.altarix.ru/browse/VETAIS-2245:
     */
    private function removeOtherContacts($type, $id_owner, $contact)
    {
        // чтобы триггерить события ActiveRecord после удаления (например, запись в audit.log),
        // мы не будем использовать для удаления простой \yii\db\Query, хотя он был бы быстрее
        $records = Contacts::find()
            ->alias('c')
            ->leftJoin(ContactTypes::tableName() . ' ct', 'c.id_contact_type = ct.id')
            ->where(['ct.type' => $type])
            ->andWhere(['c.entity_type' => 'pet_owner'])
            ->andWhere(['!=', 'c.entity_id', $id_owner])
            ->andWhere(['c.name' => $contact])
            ->all();

        foreach ($records as $record) {
            $record->delete();
        }
    }

    /**
     * @param $id_contact_type
     * @param $id_owner
     * @param $contact
     * @return bool
     */
    protected function isContactExists($id_contact_type, $id_owner, $contact)
    {
        return (new Query())->from('contacts')
            ->where(['id_contact_type' => $id_contact_type])
            ->andWhere(['entity_type' => 'pet_owner'])
            ->andWhere(['entity_id' => $id_owner])
            ->andWhere(['name' => $contact])
            ->exists();
    }

    /**
     * @return Pets
     * @throws \Exception
     */
    protected function getPet()
    {
        $pet = null;

        if (!$pet && $this->chip_animal) {
            $pet = $this->getPetByChipAnimal($this->chip_animal);
        }

        if (!$pet && !empty($this->pet_id)) {
            $pet = $this->getPetFromElk($this->pet_id);
        }

        if ($pet !== null && $pet->is_main === false) {
            return $pet->mainPet;
        }

        if ($pet === null) {
            $pet = $this->createPet();
        }

        return $pet;
    }

    /**
     * @param $ext_id
     * @return Pets|null
     */
    protected function getPetFromElk($ext_id)
    {
        return Pets::find()
            ->innerJoin('elk.pets as elk_pets', 'elk_pets.id_pet = pets.id')
            ->where(['elk_pets.ext_id' => $ext_id])
            ->one();
    }

    /**
     * @param $chip_animal
     * @return Pets|null
     */
    protected function getPetByChipAnimal($chip_animal)
    {
        return Pets::find()
            ->joinWith('pet_identification', true, 'INNER JOIN')
            ->where([
                'pet_identification.identification_code' => $chip_animal,
                'pet_identification.id_ident_type' => 1 // чип
            ])
            ->one()
        ;
    }

    /**
     * @return Pets
     * @throws \Exception
     */
    protected function createPet()
    {
        $pet = new Pets();
        if (!$pet->load($this->getAnimalData(), '') || !$pet->save()) {
            throw new \Exception("Ошибка создания животного:\n{$this->processErrors($pet->getErrors())}");
        }

        if (!empty($this->chip_animal)) {
            $petIdentification = new PetIdentification();
            $petIdentification->id_pet = $pet->id;
            $petIdentification->id_ident_type = 1;
            $petIdentification->identification_code = $this->chip_animal;
            $petIdentification->main_flag = true;
            $petIdentification->save(false);
        }

        return $pet;
    }

    /**
     * @param Pets $pet
     * @param PetOwners $owner
     */
    protected function linkPetAndOwner(Pets $pet, PetOwners $owner)
    {
        $link = PetsToOwner::findOne([
            'id_pet' => $pet->id,
            'id_owner' => $owner->id
        ]);

        if (!$link) {
            $link = new PetsToOwner();
            $link->id_pet = $pet->id;
            $link->id_owner = $owner->id;
            /**
             * Если нет связи с другими pet_owners то считаем что это владелец(1), иначе - представитель(2)
             * @TODO: значения 1 и 2 надо как-то переделать. Сейчас однозначно можно определить только id для типа владелец
             */
            $link->id_owner_type = (!$pet->owners) ? 1 : 2;
            $link->save(false);
        }
    }

    /**
     * @return array
     */
    protected function getAnimalData() : array
    {
        switch ($this->sex_animal) {
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

        return [
            'id_breed' => $this->breed_id,
            'id_species' => $this->species_id,
            'name' => $this->nickname_animal,
            'sex' => $sex,
            'birthday' => $this->birthdate_animal
        ];
    }

    /**
     * @return array
     */
    public function getRequestData() : array
    {
        return [
            'xml' => [
                'CoordinateDataMessage' => $this->data
            ]
        ];
    }

}
