<?php

namespace app\modules\soap\v2\models\etp;

use app\models\db\Agreements;
use app\models\db\AgreementTypes;
use app\models\db\elk\ElkOwners;
use app\models\db\Files;
use app\models\db\ServiceTypes;
use app\modules\payment\PaymentService;
use app\modules\soap\models\Booking;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\log\Logger;

use app\common\components\inform\jobs\RefreshSubscriptionsJob;
use app\common\models\VisitStatus;
use app\common\validators\VisitEmergencyValidator;
use app\models\db\ContactTypes;
use app\models\db\elk\ElkPets;
use app\models\db\PetOwnerType;
use app\models\db\PetsToOwner;
use app\models\db\PetOwners as POwners;
use app\models\db\ShiftType;
use app\models\db\VisitPets;
use app\models\db\TmpPets;
use app\models\db\TmpPetOwners;
use app\models\db\TmpPetsToOwner;
use app\modules\soap\models\Contacts;
use app\modules\soap\models\etp\ETP;
use app\modules\soap\models\etp\ETPException;
use app\modules\soap\models\etp\monitoring\Visit;
use app\modules\soap\models\PetIdentification;
use app\modules\soap\models\PetOwners;
use app\modules\soap\models\Pets;
use app\modules\soap\models\Visits;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\v2\models\etp\members\AddressCall;
use app\modules\soap\validators\CallToHomeVisitDateValidator;
use app\modules\soap\validators\VisitDateValidator;
use Ramsey\Uuid\Uuid;

/**
 * Class ApplicationMessage
 * @package app\modules\soap\v2\models\etp
 */
class ApplicationMessage extends Message
{
    /**
     * @var string
     */
    public $Id;
    /**
     * @var string
     */
    public $RegNum;
    /**
     * @var string
     */
    public $RegDate;
    /**
     * @var string
     */
    public $ServiceNumber;
    /**
     * @var string
     */
    public $GUID;
    /**
     * @var string
     */
    public $Description;
    /**
     * @var string|null
     */
    public $SystemId;
    /**
     * @var string|null
     */
    public $MessageId;
    /**
     * @var string
     */
    public $PrepareFactDate;
    /**
     * @var \app\modules\soap\v2\models\etp\members\ServiceType
     */
    public $ServiceType;
    /**
     * @var \app\modules\soap\v2\models\etp\members\Declarant
     */
    public $Declarant;
    /**
     * @var \app\modules\soap\v2\models\etp\members\ServiceProperties
     */
    public $ServiceProperties;
    /**
     * @var null|string
     */
    public $VisitDate;
    /**
     * @var Booking
     */
    public $Booking;

    /**
     * @var string[]|null
     */
    public $Files;

    /**
     * @var bool
     */
    protected $isNewOwner = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (empty($this->requestData['ApplicationData'])) {
            throw new ETPException('Empty request data');
        }

        foreach (['Id', 'RegNum', 'RegDate', 'ServiceNumber', 'PrepareFactDate', 'Description'] as $prop) {
            $this->$prop = ArrayHelper::getValue($this->requestData['ApplicationData'], $prop);
        }

        if (isset($this->requestData['ApplicationData']['Files'])) {
            $files = ArrayHelper::getValue($this->requestData['ApplicationData']['Files'], 'File');

            $this->Files = is_array($files) ? $files : [$files];
        }

        foreach (['ServiceType', 'Declarant', 'ServiceProperties'] as $prop) {
            $data = ArrayHelper::getValue($this->requestData['ApplicationData'], $prop);
            if (is_array($data)) {
                $data['class'] = '\\app\\modules\\soap\\v2\\models\\etp\\members\\' . $prop;
                $this->$prop = \Yii::createObject($data);
            }
        }
        if ($this->ServiceProperties->CallToHome === true && empty($this->ServiceProperties->AddressCall)) {
            if (!empty($this->Declarant->FactAddress->POBox)) {
                $AddressCall = new AddressCall();
                $AddressCall->AddressName = $this->Declarant->FactAddress->POBox;
                $AddressCall->FiasCode = $this->Declarant->FactAddress->FiasCode;
                $this->ServiceProperties->AddressCall = $AddressCall;
            }
        }

        $this->VisitDate = $this->getVisitDate($this->ServiceProperties->Date, $this->ServiceProperties->Slot);
        // $this->Booking = Booking::find()
        //     ->where(['service_number' => $this->ServiceNumber])
        //     ->orderBy(['created_at' => SORT_DESC])
        //     ->one();

        // $this->Booking = Booking::find()
        //     ->where(['service_number' => '0000-0000000-000000-0000000/00'])
        //     ->orderBy(['created_at' => SORT_DESC])
        //     ->one();
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['Id', 'RegNum', 'RegDate', 'PrepareFactDate', 'ServiceType'], 'safe'],
            [['ServiceNumber', 'Declarant', 'ServiceProperties'], 'required'],
            ['Declarant', 'validateNestedMember'],
            ['ServiceProperties', 'validateNestedMember'],
            ['Files', 'validateArrayLength', 'params' => ['max' => 5]],
            [
                'VisitDate',
                VisitDateValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === false;
                },
            ],
            [
                'VisitDate',
                CallToHomeVisitDateValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === true;
                },
            ],
            [
                'VisitDate',
                VisitEmergencyValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === false;
                },
                'organization_id' => $this->ServiceProperties->OrgId,
                'message' => 'Невозможно осуществить запись на данное время в связи с экстренной ситуацией в клинике',
            ],
            [
                'VisitDate', 'validateVisitTimeRange',
                /*
                 * При наличии букинга, если запрашиваемые слоты в запросе на запись соответствуют тем, что в букинге -
                 * игнорируем валидацию, потому-что слоты врача не доступны до истечения букинга.
                 * В противном случаем проводим стандартную валидацию на доступность слотов врача.
                */
                'when' => function ($model) {
                    $mosRuServices = $this->getServices();
                    $isCallToHome = $this->ServiceProperties->CallToHome;
                    $visitTotalLengthMinutes = Visits::getTotalVisitLengthMinutes($mosRuServices, $isCallToHome);

                    return !$model->Booking
                        || !$model->Booking->isActive()
                        || !$model->Booking->isSameSlots($this->VisitDate, $visitTotalLengthMinutes);
                }
            ],
        ];
    }

    /**
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     * @throws \Throwable
     */
    public function process()
    {
        if ($this->isMonitoring()) {
            $this->log('DIT monitoring request: sending OK, omit processing', Logger::LEVEL_INFO);
            return $this->sendMonitoringResponse();
        }

        if (empty($this->ServiceNumber)) {
            return $this->errorResponse($this->formatErrors($this->getErrorSummary(true)));
        }

        if (!$this->validate()) {
            $errors = $this->getErrorSummary(true);
            $errSum = $this->formatErrors($errors);
            $this->sendErrorMessage($errSum);
            $etpMessage = $this->saveEtpMessage();
            $this->log(
                "Request is not valid. Record {$etpMessage->id} in etp.message_v2 table can contain more details. See errors in extra data",
                Logger::LEVEL_WARNING,
                $errors
            );

            return $this->errorResponse($this->formatErrors($this->getErrorSummary(true)));
        }

        if (($visit = $this->getVisit()) !== null) {
            // на случай получения повторного запроса по уже созданному приему
            $this->getSendStatusService()->visitCreate($visit->id);

            $this->log(
                sprintf('Found an existing visit by provided data (id=%s). Sending OK, omit processing', $visit->id),
                Logger::LEVEL_INFO
            );
//            return $this->successResponse();
        }
        if ($this->haveVisits()) {
            $this->sendErrorMessage($this->formatErrors($this->getErrorSummary(true)));
            $etpMessage = $this->saveEtpMessage();
            $this->log(
                sprintf(
                    "Found an existing 'to be duplicated' visit. Record %s in etp.message_v2 table can contain more details.",
                    $etpMessage->id
                ),
                Logger::LEVEL_INFO
            );

            return $this->successResponse();
        }

        try {
            \Yii::$app->db->transaction(function () {
                $pet = $this->getPet();
                $attributes = [];
                if ($this->Declarant->isAuthorized()) {
                    $petOwner = $this->getPetOwner();
                    if (!$this->isNewOwner) {
                        $this->updatePetOwner($petOwner);
                    }
                    $this->saveMobilePhone($petOwner);
                    $this->saveEmail($petOwner);
                } else {
                    $petOwner = $this->createPetOwner();
                    if (!empty($this->Declarant->MobilePhone)) {
                        // для 'Инкогнито' мы не можем сохранять мобильный телефон в контакты из-за ограничения уникальности
                        // (велика вероятность, что пользователь 'Инкогнито' часто будет использовать при записи один и тот же номер,
                        // 'склейки' для 'Инкогнито' на данный момент не предусмотрено)
                        $attributes['description'] = 'Мобильный телефон: ' . $this->Declarant->MobilePhone;
                    }
                }
                $this->linkPetAndOwner($pet, $petOwner);

                $petOwnerDataDeclarant = $this->Declarant;
                $specialist_id = $this->ServiceProperties['SpecialistId'];
                $id_pet_owner =$petOwner->id;
                $Elkowner = ElkOwners::find()->where(['id_owner' => $id_pet_owner])->one();

                if (empty($Elkowner)) {
                    $Elkowner = new ElkOwners([
                        'sso_id' => $petOwnerDataDeclarant['SsoId'] ?? '',
                        'id_owner' => $id_pet_owner ?? '',
                        'first_name' => $petOwnerDataDeclarant['FirstName'] ?? '',
                        'last_name' => $petOwnerDataDeclarant['LastName'] ?? '',
                        'middle_name' => $petOwnerDataDeclarant['MiddleName'] ?? '',
                        'snils' => $petOwnerDataDeclarant['Snils'] ?? '',
                        'phone' => $petOwnerDataDeclarant['MobilePhone'] ?? '',
                        'email' =>$petOwnerDataDeclarant['EMail'] ?? ''
                    ]);

                    if (!$Elkowner->validate()) {
                        return 'Ошибка при записи (elk.owners)';
                    }
                    $Elkowner->save(false);
                }

                $agreements = Agreements::find()
                    ->where([
                        'id_pet_owner' => $id_pet_owner,
                        'id_type' => 1
                    ])
                    ->one() ?? 'empty';

                if ($agreements == 'empty') {
                    $agreement = new Agreements([
                        'id_type' => 1,
                        'id_pet_owner' => $id_pet_owner,
                        'id_visit' => null,
                        'id_organization' => null,
                        'is_agree' => 1,
                        'created_by' => $specialist_id ?? null,
                        'updated_by' => $specialist_id ?? null,
                    ]);

                    // Сохранение созданного экземпляра в базе данных
                    if ($agreement->save()) {
                        echo 'Данные успешно сохранены!';
                    } else {
                        return 'Ошибка при сохранении данных agreements';
                    }
                } else {
                    $agreements->is_agree = true;
//             Сохранение созданного экземпляра в базе данных
                    if ($agreements->save()) {
                        echo 'Данные успешно сохранены!';
                    } else {
                        return 'Ошибка при сохранении данных agreements';
                    }
                }


                $visit = $this->createVisit($pet, $petOwner, $attributes);
                $this->saveEtpMessage($visit);

                // push status message in queue
                $this->getSendStatusService()->visitCreate($visit->id);

                if ($visit->isOnlineVisit()) {
                    $paymentService = new PaymentService();
                    $organizationName = $visit->organization->name;
                    $service = array_filter($this->getServices(), function ($govService) {
                        return $govService->serviceType->id === ServiceTypes::TYPE_TELE_VETERINARY;
                    });
                    $registeredPayment = $paymentService->registerPayment(
                        PaymentService::TELEVET_SERVICE_GUID,
                        $service[0]->price,
                        "Оплата услуги телеветеринарии [ПОДРАЗДЕЛЕНИЕ ($organizationName)]",

                    /*
                     TODO: по СНИЛСу ЕПШ находит услугу (при оплате в интерфейсе ЕПШ пишет, что "Найдено по СНИЛС"
                        соответственно здесь однозначно некорректно передавать id пользователя и либо надо передавать
                        снилс организации в которой записывается пользователь, либо зарегестрировашей услугу в ЕПШ
                    */
                        // $petOwner->snils
                    );
                    $visit->payment_request_uid = $registeredPayment['requestUID'];
                    $visit->payment_link = $registeredPayment['paymentUrl'];
                    $visit->payment_request_date = (new \DateTime())->format('Y-m-d H:i:s');
                    $visit->save();
                    $this->getSendStatusService()->visitRequirePayment($visit->id, $registeredPayment['paymentUrl']);
                }
            });
        } catch (\Throwable $e) {
            \Yii::error($e);
            $this->log($e, Logger::LEVEL_ERROR);
            $visit = (isset($visit) && $visit instanceof Visits) ? $visit : null;
            $this->saveEtpMessage($visit);

            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse();
    }

    /**
     * @param string $errorMessage
     */
    protected
    function sendErrorMessage(string $errorMessage)
    {
        $this->getSendStatusService()->visitCreateError($this->ServiceNumber, $errorMessage);
    }

    /**
     * @return Pets|bool|null|static
     * @throws \Exception
     */
    protected
    function getPet()
    {
        if (empty($this->Declarant->SsoId)) {
            return $this->createPet();
        }

        $pet = null;

        if (!empty($chip = $this->ServiceProperties->ChipAnimal)) {
            $pet = $this->getPetByChipAnimal($chip);

            if (null === $pet) {
                $this->log(
                    sprintf(
                        "Coundn't find a pet with the chip number %s",
                        $chip
                    ),
                    Logger::LEVEL_TRACE
                );
            }
        }

        if ($pet === null && !empty($extPetId = $this->ServiceProperties->PetId)) {
            $pet = $this->getPetFromElk($extPetId);

            if (null === $pet) {
                $this->log(
                    sprintf(
                        "Coundn't find a pet with the external ID %s",
                        $extPetId
                    ),
                    Logger::LEVEL_TRACE
                );
            }
        }

        if ($pet === null) {
            $pet = $this->createPet();
        }

        return $pet->getMainIfExists();
    }

    /**
     * @param $chip_animal
     * @return \app\modules\soap\models\Pets|null
     */
    protected
    function getPetByChipAnimal($chip_animal)
    {
        return Pets::find()
            ->joinWith('pet_identification', true, 'INNER JOIN')
            ->where([
                'pet_identification.identification_code' => $chip_animal,
                'pet_identification.id_ident_type' => 1 // чип
            ])
            ->one();
    }

    /**
     * @param $ext_id
     * @return \app\modules\soap\models\Pets|null
     */
    protected
    function getPetFromElk($ext_id)
    {
        return Pets::find()
            ->innerJoin('elk.pets as elk_pets', 'elk_pets.id_pet = pets.id')
            ->where(['elk_pets.ext_id' => $ext_id])
            ->one();
    }

    /**
     * @return \app\modules\soap\models\Pets
     * @throws \Exception
     */
    protected
    function createPet()
    {
        $this->log(
            'Creating a new pet',
            Logger::LEVEL_INFO
        );

        $pet = new Pets();
        $petData = $this->getPetData();

        $this->log(
            'New pet data:',
            Logger::LEVEL_TRACE,
            $petData
        );

        $pet->load($petData, '');

        if (!$pet->validate()) {
            throw new \Exception("Невалидные данные животного:\n{$this->formatErrors($pet->getErrorSummary(true))}");
        }

        if (empty($this->Declarant->SsoId)) {
            // https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/188
            $this->log('No SSO_ID, creating new TmpPets', Logger::LEVEL_INFO,);

            // Используем валидированные/фильтрованные данные основной модели
            $attrNames = array_diff(array_keys($petData), ['ext_id']);
            $tmpData = $pet->getAttributes($attrNames);
            $tmpPet = new TmpPets();
            if (!$tmpPet->load($tmpData, '') || !$tmpPet->save()) {
                throw new \Exception("Ошибка создания животного (TmpPets):\n{$this->formatErrors($tmpPet->getErrorSummary(true))}");
            }

            $pet->id_pet_tmp = $tmpPet->id;

            // Не сохраняем имя в основную таблицу
            // https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/284
            $maskedAttrs = [
                'name' => "mosru-unauth-{$this->RegNum}",
                'birthday' => null,
            ];
            $pet->setAttributes($maskedAttrs);
        }

        if (!$pet->save()) {
            throw new \Exception("Ошибка создания животного:\n{$this->formatErrors($pet->getErrorSummary(true))}");
        }

        if (!empty($petData['ext_id'])) {
            $elkPet = new ElkPets();
            $elkPet->id_pet = $pet->id;
            if (!$elkPet->load($petData, '') || !$elkPet->save()) {
                throw new \Exception("Ошибка создания животного (elk):\n{$this->formatErrors($elkPet->getErrorSummary(true))}");
            }
        }

        if (!empty($petData['chip'])) {
            $petIdentification = new PetIdentification();
            $petIdentification->id_pet = $pet->id;
            $petIdentification->id_ident_type = PetIdentification::IDENT_TYPE_CHIP;
            $petIdentification->identification_code = $petData['chip'];
            $petIdentification->main_flag = true;
            $petIdentification->save(false);
        }

        return $pet;
    }

    /**
     * @return array
     */
    protected
    function getPetData(): array
    {
        switch ($this->ServiceProperties->SexAnimal) {
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

        $breed = 0;
        if(empty($this->ServiceProperties->BreedId)){
            $breed = 1171;
        }else{
            $breed = $this->ServiceProperties->BreedId;
        }

        $data = [
            'id_species' => $this->ServiceProperties->SpeciesId,
            'id_breed' => $breed,
            'name' => $this->ServiceProperties->NicknameAnimal,
            'sex' => $sex,
            'birthday' => $this->ServiceProperties->BirthdateAnimal,
            'ext_id' => $this->ServiceProperties->PetId,
        ];

        if (!empty($this->ServiceProperties->ChipAnimal)) {
            $data['chip'] = $this->ServiceProperties->ChipAnimal;
        }

        return $data;
    }

    /**
     * @return PetOwners
     * @throws \Exception
     */
    protected
    function getPetOwner()
    {
        if (empty($this->Declarant->SsoId)) {
            return $this->createPetOwner();
        }

        if (!empty($this->Declarant->SsoId) && $petOwner = PetOwners::find()->bySsoId($this->Declarant->SsoId)->one()) {
            // TODO - не решен вопрос, что делать если ФИО не совпадают
            // TODO - проверять ли снилс?
            return $petOwner->getMainIfExists();
        }

        if (!empty($this->Declarant->Snils) && $petOwner = PetOwners::find()->bySnils($this->Declarant->Snils)->one()) {
            // TODO - не решен вопрос, что делать если ФИО не совпадают
            return $petOwner->getMainIfExists();
        }

        if (!empty($this->Declarant->MobilePhone) && $petOwner = PetOwners::find()->byMobilePhone($this->Declarant->MobilePhone)->one()) {
            return $petOwner->getMainIfExists();
        }

        /** @var PetOwners $petOwner */
        $query = PetOwners::find()
            ->byFio(
                $this->Declarant->LastName,
                $this->Declarant->FirstName,
                $this->Declarant->MiddleName
            )
            ->byMobilePhone($this->Declarant->MobilePhone)
            ->withoutSsoId();

        // Если в заявке передан СНИЛС и пользователя по нему не найдено (см. выше),
        // то ищем пользователя у которого СНИЛС не заполнен
        if (!empty($this->Declarant->Snils)) {
            $query->withoutSnils();
        }

        if ($petOwner = $query->one()) {
            return $petOwner->getMainIfExists();
        }

        return $this->createPetOwner();
    }

    /**
     * @return PetOwners
     * @throws \Exception
     */
    protected
    function createPetOwner()
    {
        $data = [
            'f_fio' => $this->Declarant->LastName,
            'i_fio' => $this->Declarant->FirstName,
            'o_fio' => $this->Declarant->MiddleName,
            'snils' => $this->Declarant->Snils,
            'birthday' => $this->Declarant->BirthDate,
            'sso_id' => $this->Declarant->SsoId,
        ];

        $petOwner = new PetOwners();
        $petOwner->load($data, '');
        if (!$petOwner->validate()) {
            throw new \Exception("Невалидные данные владельца животного:\n{$this->formatErrors($petOwner->getErrorSummary(true))}");
        }

        if (empty($this->Declarant->SsoId)) {
            // https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/188
            $this->log('No SSO_ID, creating new TmpPetOwners', Logger::LEVEL_INFO,);

            $tmpPetOwner = new TmpPetOwners();
            // Используем валидированные/фильтрованные данные основной модели
            $tmpData = $petOwner->getAttributes(array_keys($data));
            $tmpData['fullname'] = join(' ', array_filter([$tmpData['f_fio'], $tmpData['i_fio'], $tmpData['o_fio']]));
            if (!$tmpPetOwner->load($tmpData, '') || !$tmpPetOwner->save()) {
                throw new \Exception("Ошибка создания владельца животного (TmpPetOwners):\n{$this->formatErrors($tmpPetOwner->getErrorSummary(true))}");
            }

            $petOwner->id_pet_owner_tmp = $tmpPetOwner->id;

            // Не сохраняем имя в основную таблицу
            // https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/284
            $maskedAttrs = [
                'f_fio' => "mosru-unauth-{$this->RegNum}",
                'i_fio' => "mosru-unauth-{$this->RegNum}",
                'o_fio' => null,
                'snils' => null,
                'birthday' => null,
            ];
            $petOwner->setAttributes($maskedAttrs);
        };

        if (!$petOwner->save()) {
            throw new \Exception("Ошибка создания владельца животного:\n{$this->formatErrors($petOwner->getErrorSummary(true))}");
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
    protected
    function updatePetOwner(PetOwners $owner)
    {
        $attributes = [];
        if (empty($owner->snils) && !empty($this->Declarant->Snils)) {
            //на поле snils стоит ограничение unique. Если имеется владелец с таким СНИЛС, то затираем его значение
            if ($snilsDouble = PetOwners::find()->bySnils($this->Declarant->Snils)->one()) {
                $snilsDouble->snils = null;
                $snilsDouble->save();
            }
            $owner->snils = $this->Declarant->Snils;
            $attributes[] = 'snils';
        }
        if (empty($owner->birthday) && !empty($this->Declarant->BirthDate)) {
            $owner->birthday = $this->Declarant->BirthDate;
            $attributes[] = 'birthday';
        }

        if (!empty($attributes) && $owner->save(true, $attributes)) {
            if (in_array('sso_id', $attributes, true)) {
                \Yii::$app->subscription_queue->push(new RefreshSubscriptionsJob([
                    'id_owner' => $owner->id,
                    'sso_id' => $this->Declarant->SsoId,
                ]));
            }
        }
    }

    /**
     * Сохранение мобильного телефона
     * @param PetOwners $owner
     * @throws \Exception
     */
    protected
    function saveMobilePhone(PetOwners $owner)
    {
        if (empty($this->Declarant->MobilePhone)) {
            return;
        }

        $contactType = ContactTypes::findOne(['name' => 'Мобильный телефон', 'type' => ContactTypes::TYPE_PHONE]);

        if (!$this->isContactExists($contactType->id, $owner->id, $this->Declarant->MobilePhone)) {
            $contact = new Contacts();
            $contact->entity_type = 'pet_owner';
            $contact->entity_id = $owner->id;
            $contact->id_contact_type = $contactType->id;
            $contact->name = $this->Declarant->MobilePhone;
            $contact->main_flag = $this->isNewOwner; // если новый пользователь, то считаем этот контакт основным
            $contact->confirmed = true;

            if (!$contact->save()) {
                throw new \Exception("Ошибка сохранения телефона:\n{$this->formatErrors($contact->getErrorSummary(true))}");
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_PHONE, $owner->id, $this->Declarant->MobilePhone);
    }

    /**
     * Сохранение e-mail
     * @param PetOwners $owner
     * @throws \Exception
     */
    protected
    function saveEmail(PetOwners $owner)
    {
        if (empty($this->Declarant->EMail)) {
            return;
        }

        $contactTypeEmail = ContactTypes::findOne(['name' => 'Электронная почта', 'type' => ContactTypes::TYPE_EMAIL]);

        if (!$this->isContactExists($contactTypeEmail->id, $owner->id, $this->Declarant->EMail)) {
            $contactEmail = new Contacts();
            $contactEmail->entity_type = 'pet_owner';
            $contactEmail->entity_id = $owner->id;
            $contactEmail->id_contact_type = $contactTypeEmail->id;
            $contactEmail->name = $this->Declarant->EMail;
            $contactEmail->confirmed = true;

            if (!$contactEmail->save()) {
                throw new \Exception("Ошибка сохранения почты:\n{$this->formatErrors($contactEmail->getErrorSummary(true))}");
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_EMAIL, $owner->id, $this->Declarant->EMail);
    }

    /**
     * Удаление дублей контактов у других владельцев - в ЕЛК контакты должны быть уникальными.
     * Мы не будем отменять подписки (так как данные пришли от ЕЛК, подписки уже отменены на их стороне).
     * @param string $type
     * @param int $id_owner
     * @param string $contact
     * @see https://jira.altarix.ru/browse/VETAIS-2245:
     */
    private
    function removeOtherContacts($type, $id_owner, $contact)
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
    protected
    function isContactExists($id_contact_type, $id_owner, $contact)
    {
        return (new Query())->from('contacts')
            ->where(['id_contact_type' => $id_contact_type])
            ->andWhere(['entity_type' => 'pet_owner'])
            ->andWhere(['entity_id' => $id_owner])
            ->andWhere(['name' => $contact])
            ->exists();
    }

    /**
     * @param Pets $pet
     * @param PetOwners $owner
     */
    protected
    function linkPetAndOwner($pet, $owner)
    {
        $link = null;

        if (!$this->isNewOwner) {
            $link = PetsToOwner::findOne([
                'id_pet' => $pet->id,
                'id_owner' => $owner->id,
            ]);
        }

        if (!$link) {
            $link = new PetsToOwner();
            $link->id_pet = $pet->id;
            $link->id_owner = $owner->id;
            /**
             * Если нет связи с другими pet_owners то считаем что это владелец(1), иначе - представитель(2)
             * @TODO: значения 1 и 2 надо как-то переделать. Сейчас однозначно можно определить только id для типа владелец
             */
            $link->id_owner_type = (!$pet->owners)
                ? PetOwnerType::TYPE_OWNER
                : PetOwnerType::TYPE_AGENT;
            $link->save(false);

            if ($owner->id_pet_owner_tmp && $pet->id_pet_tmp) {
                // https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/188
                $tmpLink = new TmpPetsToOwner();
                $tmpLink->id_pet_tmp = $pet->id_pet_tmp;
                $tmpLink->id_owner_tmp = $owner->id_pet_owner_tmp;
                $tmpLink->id_owner_type = $link->id_owner_type;
                $tmpLink->save(false);
            }
        }
    }

    /**
     * @param Pets $pet
     * @param PetOwners $petOwner
     * @param array $attributes дополнительные атрибуты приема
     * @return Visits
     * @throws \Exception
     */
    protected
    function createVisit(Pets $pet, PetOwners $petOwner, array $attributes = [])
    {
        $visit = new Visits();
        $visit->status = VisitStatus::NEW;
        $visit->id_organization = $this->ServiceProperties->OrgId;
        $visit->id_owner = $petOwner->id;
        $visit->id_pet = $pet->id;
        $visit->start_dttm = $this->VisitDate;
        $visit->variety = \app\models\db\Visits::VISIT_SINGLE;
        if ($petOwner->id_pet_owner_tmp) {
            // https://azuredevops.starlink-soft.ru/Starlink/VetAs/_workitems/edit/188
            $visit->is_for_unauth_client = true;
        }

        if ($this->ServiceProperties->CallToHome) {
            $visit->type = \app\models\db\Visits::TYPE_AT_HOME;
            $visit->visit_to_address = $this->ServiceProperties->AddressCall->AddressName;
            $visit->channel = ShiftType::findOne(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME])->id;
        } else {
            $visit->channel = ShiftType::findOne(['type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])->id;
        }

        if ($this->Description) {
            $visit->description = $this->Description;
        }

        $visit->call_to_home = $this->ServiceProperties->CallToHome;

        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attribute => $value) {
                if ($visit->canSetProperty($attribute)) {
                    $visit->$attribute = $value;
                }
            }
        }

        /** @var \app\models\db\MosRuServices[] $mosRuServices */
        $mosRuServices = $this->getServices();
        $visit->setServices($mosRuServices);
        $visit->setSpecialist($this->getSpecialist());

        foreach ($mosRuServices as $service) {
            if ($service->serviceType->id === ServiceTypes::TYPE_TELE_VETERINARY) {
                $visit->type = \app\models\db\Visits::TYPE_ONLINE;
                $visit->guid_video = (Uuid::uuid4())->toString();
                break;
            }
        }

        if (!$visit->save()) {
            throw new \Exception("Ошибка создания записи на прием:\n{$this->formatErrors($visit->getErrorSummary(true))}");
        }

        if (!empty($this->Files)) {
            foreach($this->Files as $File) {
                $now = new \DateTime();
                $file = new Files();
                $file->created = $now->format('Y-m-d H:i:s');
                $file->hash = $File;
                $file->path = '/';
                $file->entity_id = $visit->id;
                $file->entity_type = 'visit-mos-ru';
                $file->created_at = $now->format('Y-m-d H:i:s');
                $file->updated_at = $now->format('Y-m-d H:i:s');
                if (!$file->save()) {
                    throw new \Exception("Ошибка прикрепления файла к приёму :\n{$this->formatErrors($file->getErrorSummary(true))}");
                }
            }
        }

        return $visit;
    }

    /**
     * @param \app\modules\soap\models\Visits $visit
     * @throws ETPException
     */
    protected
    function saveEtpMessage(Visits $visit = null): ETPMessage
    {
        $message = new ETPMessage();

        $message->visit_id = isset($visit) ? $visit->id : 0;
        $message->service_number = $this->ServiceNumber;
        $message->message = $this->requestData;
        if (!empty($this->Declarant)) {
            $message->last_name = $this->Declarant->LastName;
            $message->first_name = $this->Declarant->FirstName;
            $message->middle_name = $this->Declarant->MiddleName;
            $message->phone = $this->Declarant->MobilePhone;
            $message->email = $this->Declarant->EMail;
            $message->sso_id = $this->Declarant->SsoId;
        }
        if ($this->SystemId && $this->MessageId) {
            $message->system_id = $this->SystemId;
            $message->message_id = $this->MessageId;
        }

        if (!$message->save(false)) {
            throw new ETPException("Ошибка обработки сообщения", 422);
        }

        return $message;
    }

    /**
     * Проверка на наличие "дублируемого" приёма
     *
     * На некоторые услуги (serviceIds) запрещена повторная запись
     * в тот же день, с тем же животным, в ту же клинику
     *
     * @see https://jira.altarix.ru/browse/VETAIS-3270
     *
     * @throws \Exception
     */
    protected
    function haveVisits()
    {
        $pet = null;
        if (!empty($this->ServiceProperties->ChipAnimal)) {
            $pet = $this->getPetByChipAnimal($this->ServiceProperties->ChipAnimal);
        }
        if ($pet === null && !empty($this->ServiceProperties->PetId)) {
            $pet = $this->getPetFromElk($this->ServiceProperties->PetId);
        }
        // Если животное в записях мосру не найдено, значит будет создаваться новая запись.
        if ($pet == null) {
            return false;
        }

        $passedIds = $this->getServiceIds();
        $serviceIds = \Yii::$app->getModule('soap')->params['serviceIds'];

        $ids = [];
        foreach ($passedIds as $passedId) {
            if (in_array($passedId, $serviceIds)) {
                $ids[] = $passedId;
            }
        }

        $date = date('Y-m-d', strtotime($this->VisitDate));
        /** @var Visit[] $visits */
        $visits = Visits::find()
            ->alias('v')
            ->leftJoin(VisitPets::tableName() . ' vp', 'v.id = vp.id_visit')
            ->joinWith('services')
            ->where(['date(v.start_dttm)' => $date])
            ->andWhere(['vp.id_pet' => $pet->id])
            ->andWhere(['not', ['v.status' => VisitStatus::CANCELED]])
            ->andWhere(['v.id_organization' => $this->ServiceProperties->OrgId])
            ->andWhere(['in', 'services.id', $ids])
            ->all();

        if (!empty($visits)) {
            $service_name = $visits[0]->services[0]->name;
            $this->addError('visit', "Невозможно произвести запись животного на повторный прием $date " .
                "по оказанию услуги '$service_name'. В эту дату у животного уже есть запись на прием в данной орг-ции с данной услугой!");
            return true;
        }
        return false;
    }
}
