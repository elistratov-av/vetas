<?php

namespace app\modules\mdm\models;

use app\common\components\inform\events\InitialIdentificationAndVaccinationEvent;
use app\common\components\inform\events\InitialIdentificationEvent;
use app\common\components\inform\events\InitialVaccinationEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\helpers\PhoneHelper;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\elk\ElkOwners;
use app\models\db\elk\ElkPets;
use app\models\db\PetIdentification;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Species;
use app\modules\elk\exceptions\ELKException;
use app\modules\elk\models\db\PetOwners;
use app\modules\elk\models\PetsHandler;
use app\modules\mdm\exceptions\MDMException;
use yii\base\Model;
use Yii;

/**
 * Class SaveHandler
 * @package app\modules\mdm\models
 */
class SaveHandler extends Model
{
    /**
     * @var \stdClass
     */
    public $data;

    /**
     * @var \app\modules\mdm\models\Owner
     */
    private $owner;
    /**
     * @var \app\modules\mdm\models\Pet[]
     */
    private $pets;
    /**
     * @var bool
     */
    private $isNewOwner = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->initOwner();
        $this->initPets();
    }

    private function initOwner()
    {
        $this->owner = new Owner();

        if (isset($this->data->ids)) {
            foreach ($this->data->ids as $type => $id) {
                if ($type == 'SSO') {
                    $this->owner->sso_id = $id[0]->value;
                }
            }
        }

        if (isset($this->data->documents)) {
            foreach ($this->data->documents as $type => $document) {
                if ($type == 'doc_snils') {
                    $this->owner->snils = $document[0]->value;
                }
            }
        }

        if (isset($this->data->contacts)) {
            foreach ($this->data->contacts as $type => $contact) {
                switch ($type) {
                    case 'contact_email':
                        $this->owner->email = $contact[0]->value;
                        break;

                    case 'contact_mobile_registration':
                        $this->owner->phone = PhoneHelper::extractMosRuPhoneNumber($contact[0]->value);
                        break;
                }
            }
        }

        $this->owner->last_name = (isset($this->data->last_name)) ? $this->data->last_name : null;
        $this->owner->first_name = (isset($this->data->first_name)) ? $this->data->first_name : null;
        $this->owner->middle_name = (isset($this->data->middle_name)) ? $this->data->middle_name : null;
        $this->owner->deleted = (isset($this->data->deleted)) ? $this->data->deleted : false;
    }

    private function initPets()
    {
        if (!isset($this->data->pets)) {
            return;
        }

        foreach ($this->data->pets as $pet) {
            $pet = new Pet([
                'pet_id' => isset($pet->mdm_obj_id) ? $pet->mdm_obj_id : null,
                'deleted' => isset($pet->deleted) ? $pet->deleted : false,
                'name' => isset($pet->name) ? $pet->name : null,
                'species' => isset($pet->species) ? $pet->species : null,
                'breed' => isset($pet->breed) ? $pet->breed : null,
                'gender' => isset($pet->gender) ? $pet->gender : null,
                'birth_date' => isset($pet->birth_date) ? $pet->birth_date : null,
                'chip_number' => isset($pet->chip_number) ? $pet->chip_number : null,
            ]);
            $this->pets[] = $pet;
        }
    }

    /**
     * @throws \Throwable
     */
    public function run()
    {
        $response = [];
        try {
            $errors = [];
            if (!$this->owner->validate()) {
                $errors = $this->owner->getErrorSummary(true);
            }

            foreach ($this->pets as $pet) {
                if (!$pet->validate()) {
                    $errors = array_merge($errors, $pet->getErrorSummary(true));
                }
            }

            if (!empty($errors)) {
                throw new MDMException($this->prepareErrors($errors));
            }

            $this->save();

            $response['result'] = 'OK';
            $response['error'] = 0;
        } catch (MdmException $e) {
            $response['result'] = 'Error';
            $response['error'] = 400;
            $response['error_message'] = $e->getMessage();
        } catch (\Throwable $e) {
            $response['result'] = 'Error';
            $response['error'] = 500;
            $response['error_message'] = 'Внутренняя ошибка сервера:' . $e->getMessage();
        }

        return $response;
    }

    /**
     * @throws \Throwable
     */
    private function save()
    {
        Yii::$app->db->transaction(function () {
            $elkOwner = $this->getElkOwner();

            if (!$owner = $elkOwner->owner) {
                if (!$owner = $this->findOwner()) {
                    $owner = $this->createOwner();
                }
            }

            // У нас getElkOwner мог в этот раз вернуть основного владельца
            // (ксли была склейка). Поэтому сохраним ему новый id_owner

            $elkOwner->id_owner = $owner->id;
            $elkOwner->save(false);

            $this->saveMobilePhone($owner);
            $this->saveEmail($owner);

            if (!$this->isNewOwner) {
                $this->saveSnils($owner);
            }

            foreach ($this->pets as $pet) {
                if ($elkPet = $this->findElkPet($pet->pet_id)) {
                    // заодно и обновим тут связку
                    // (если связаны с дублем, то перебьем на основного и будем далее использовать его)
                    $elkPet = $this->updateElkPetSetMainPet($elkPet);

                    // если у животного были приемы - данные не редактируем
                    if ($elkPet->hasVisits() || $elkPet->hasViolations()) {
                        continue;
                    }
                    if ($pet->deleted) {
                        $this->deletePet($pet);
                        continue;
                    }
                    $vetasPet = $elkPet->pet;
                    $this->updatePet($vetasPet, $pet);
                    $this->updateElkPet($elkPet, $pet);
                } else { // нет еще связки ЕЛК-Животное, или нет еще животного
                    if ($pet->deleted) {
                        continue;
                    }
                    $isNewPet = false;
                    $vetasPet = $this->findPet($pet);

                    if ($vetasPet === null) {
                        //ещё попытка поиска по кличке
                        $vetasPet = $this->findPetByName($pet, $owner->id);
                    }

                    if ($vetasPet === null) {
                        $vetasPet = $this->createPet($pet);
                        $isNewPet = true;
                    } else {
                        $this->updatePet($vetasPet, $pet);
                    }

                    $link = $this->linkPetAndOwner($vetasPet, $owner);
                    $this->saveElkPet($vetasPet, $pet, $elkOwner);

                    if ($link->id_owner_type == 1) {
                        // первичное уведомление владельца при внесении животного в ЕЛК
                        try {
                            $this->notifyOwner($owner, $vetasPet, $isNewPet);
                        } catch (\Throwable $e) {
                            \Yii::error($e->getMessage(), 'subscription_queue');
                        }
                    }
                }
            }
        });
    }

    /**
     * @return ElkOwners
     * @throws ELKException
     */
    private function getElkOwner()
    {
        if (!$elkOwner = ElkOwners::findOne(['sso_id' => $this->owner->sso_id])) {
            $elkOwner = new ElkOwners([
                'sso_id' => $this->owner->sso_id,
                'first_name' => $this->owner->first_name,
                'last_name' => $this->owner->last_name,
                'middle_name' => isset($this->owner->middle_name) ? $this->owner->middle_name : null,
                'snils' => isset($this->owner->snils) ? $this->owner->snils : null,
                'phone' => isset($this->owner->phone)
                    ? PhoneHelper::extractMosRuPhoneNumber($this->owner->phone) : null,
                'email' => isset($this->owner->email) ? $this->owner->email : null,
            ]);

            if (!$elkOwner->validate()) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $elkOwner->getErrorSummary(true),
                    "\nОшибка при сохранении профиля владельца из ЕЛК:"
                ));
            }

            $elkOwner->save(false);
        }

        return $elkOwner;
    }

    /**
     * @return PetOwners|bool
     * @throws \yii\base\InvalidConfigException
     */
    private function findOwner()
    {
        /** @var PetOwners $petOwner */
        if (!empty($this->owner->sso_id) &&
            $petOwner = PetOwners::find()->active()->bySsoId($this->owner->sso_id)->one()
        ) {
            return $petOwner->getMainIfExists();
        }

        if (!empty($this->owner->snils) &&
            $petOwner = PetOwners::find()->active()->bySnils($this->owner->snils)->one()
        ) {
            return $petOwner->getMainIfExists();
        }

        /** @var PetOwners $petOwner */
        $query = PetOwners::find()
            ->active()
            ->byFio(
                $this->owner->last_name,
                $this->owner->first_name,
                isset($this->owner->middle_name) ? $this->owner->middle_name : ''
            )
            ->withoutSsoId();

        if (!empty($this->owner->phone)) {
            $phone = PhoneHelper::extractMosRuPhoneNumber($this->owner->phone);
        }
        if (!empty($phone)) {
            $query->byMobilePhone($phone);
        } elseif (!empty($this->owner->email)) {
            $query->byEmail($this->owner->email);
        } else {
            return false;
        }

        // Если в заявке передан СНИЛС и пользователя по нему не найдено (см. выше),
        // то ищем пользователя у которого СНИЛС не заполнен
        if (!empty($this->owner->snils)) {
            $query->withoutSnils();
        }

        if ($petOwner = $query->one()) {
            return $petOwner->getMainIfExists();
        }

        return false;
    }

    /**
     * @return PetOwners
     * @throws ELKException
     */
    private function createOwner()
    {
        $owner = new PetOwners([
            'f_fio' => $this->owner->last_name,
            'i_fio' => $this->owner->first_name,
            'o_fio' => $this->owner->middle_name,
            'sso_id' => $this->owner->sso_id,
        ]);

        if (!empty($this->owner->snils)) {
            $owner->snils = $this->owner->snils;
        }

        if (!$owner->validate()) {
            throw new ELKException('Ошибка при сохранении владельца');
        }

        $owner->save();
        $this->isNewOwner = true;

        return $owner;
    }

    /**
     * Сохранение мобильного телефона
     * @param PetOwners $owner
     * @throws \Exception
     */
    private function saveMobilePhone($owner)
    {
        if (empty($this->owner->phone)) {
            return;
        }

        $phone = PhoneHelper::extractMosRuPhoneNumber($this->owner->phone);

        if (empty($phone)) {
            return;
        }

        $contactType = $this->contactTypePhone();

        // пытаемся найти любой телефон во избежание дублей
        $contact = $this->findContact(ContactTypes::TYPE_PHONE, $owner->id, $phone);

        if ($contact === null) {
            $contact = new Contacts();
            $contact->entity_type = 'pet_owner';
            $contact->entity_id = $owner->id;
            $contact->id_contact_type = $contactType->id;
            $contact->name = $phone;
            $contact->main_flag = $this->isNewOwner; // если новый пользователь, то считаем этот контакт основным
            $contact->confirmed = true;

            if (!$contact->save()) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $contact->getErrorSummary(true),
                    "\nОшибка сохранения телефона:"
                ));
            };
        } elseif ($contact->confirmed !== true || $contact->id_contact_type != $contactType->id) {
            $contact->confirmed = true;
            $contact->id_contact_type = $contactType->id;
            if (!$contact->save(true, ['confirmed', 'id_contact_type', 'updated_at'])) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $contact->getErrorSummary(true),
                    "\nОшибка сохранения телефона:"
                ));
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_PHONE, $owner->id, $phone);
    }

    /**
     * @return \app\models\db\ContactTypes|null
     */
    protected function contactTypePhone()
    {
        return ContactTypes::findOne([
            'name' => 'Мобильный телефон',
            'type' => ContactTypes::TYPE_PHONE,
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
        ]);
    }

    /**
     * Сохранение e-mail
     * @param PetOwners $owner
     * @throws \Exception
     */
    private function saveEmail($owner)
    {
        if (empty($this->owner->email)) {
            return;
        }

        $contactType = $this->contactTypeEmail();

        // пытаемся найти любой email во избежание дублей
        $contact = $this->findContact(ContactTypes::TYPE_EMAIL, $owner->id, $this->owner->email);
        if ($contact === null) {
            $contact = new Contacts();
            $contact->entity_type = 'pet_owner';
            $contact->entity_id = $owner->id;
            $contact->id_contact_type = $contactType->id;
            $contact->name = $this->owner->email;
            $contact->confirmed = true;

            if (!$contact->save()) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $contact->getErrorSummary(true),
                    "\nОшибка сохранения почты:"
                ));
            };
        } elseif ($contact->confirmed !== true) {
            $contact->confirmed = true;
            if (!$contact->save(true, ['confirmed', 'updated_at'])) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $contact->getErrorSummary(true),
                    "\nОшибка сохранения почты:"
                ));
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_EMAIL, $owner->id, $this->owner->email);
    }

    /**
     * @return \app\models\db\ContactTypes|null
     */
    protected function contactTypeEmail()
    {
        return ContactTypes::findOne([
            'name' => 'Электронная почта',
            'type' => ContactTypes::TYPE_EMAIL,
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
        ]);
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
     * Сохранение СНИЛС
     * @param PetOwners $owner
     */
    private function saveSnils($owner)
    {
        if (empty($owner->snils) && !empty($this->owner->snils)) {
            $owner->snils = $this->owner->snils;
            if ($owner->validate()) {
                $owner->save(false);
            }
        }
    }

    /**
     * @param string $type
     * @param int    $id_owner
     * @param string $contact
     * @return \app\models\db\Contacts
     */
    private function findContact($type, $id_owner, $contact)
    {
        return Contacts::find()
            ->alias('c')
            ->leftJoin(ContactTypes::tableName() . ' ct', 'c.id_contact_type = ct.id')
            ->where(['ct.type' => $type])
            ->andWhere(['c.entity_type' => 'pet_owner'])
            ->andWhere(['c.entity_id' => $id_owner])
            ->andWhere(['c.name' => $contact])
            ->one();
    }

    /**
     * @param Pet $pet
     * @return \app\models\db\Pets|null
     */
    private function findPet(Pet $pet)
    {
        if (empty($pet->chip_number)) {//если пустой чип, то животное автоматичеси не найдено
            return null;
        }

        $foundPet = Pets::find()
            ->joinWith('pet_identification', true, 'INNER JOIN')
            ->where([
                'pet_identification.identification_code' => $pet->chip_number,
                'pet_identification.id_ident_type' => 1 // чип
            ])
            ->one();
        //поиск по чипу, если ничего нет, то животное не найдено

        if ($foundPet != null) {
            return $foundPet->getMainIfExists();
        }
        return null;
    }

    /**
     * @param Pet $pet
     * @return \app\models\db\Pets|null
     */
    private function findPetByName(Pet $pet, $id_owner){
        $foundPet = Pets::find()
            ->leftJoin('pets_to_owner', 'pets_to_owner.id_pet = pets.id')
            ->where(['pets.name' => $pet->name])
            ->andWhere(['pets_to_owner.id_owner' => $id_owner])
            ->one();
        
        if ($foundPet != null) {
            return $foundPet->getMainIfExists();
        }
    }

    /**
     * @param Pet $pet
     * @return Pets
     * @throws ELKException
     */
    private function createPet(Pet $pet)
    {
        $newPet = new Pets();
        if (!$newPet->load($pet->getAnimalData(), '') || !$newPet->save()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $newPet->getErrorSummary(true),
                "\nОшибка создания животного:"
            ));
        }

        if (!empty($pet->chip_number)) {
            $this->savePetIdentification($newPet, $pet->chip_number);
        }

        return $newPet;
    }

    /**
     * @param Pets $pet
     * @param      $chip
     */
    private function savePetIdentification(Pets $pet, $chip)
    {
        $petIdentification = new PetIdentification();
        $petIdentification->id_pet = $pet->id;
        $petIdentification->id_ident_type = 1;
        $petIdentification->identification_code = $chip;
        $petIdentification->main_flag = true;
        $petIdentification->save(false);
    }

    /**
     * @param Pets      $pet
     * @param PetOwners $owner
     */
    private function linkPetAndOwner(Pets $pet, $owner)
    {
        $link = PetsToOwner::findOne([
            'id_pet' => $pet->id,
            'id_owner' => $owner->id,
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

        return $link;
    }

    /**
     * Найти животное.
     * Если оно связано с дублем - перебить на основное и основное
     * @param $ext_id
     * @return null|ElkPets
     */
    private function findElkPet($ext_id)
    {
        return ElkPets::findOne(['ext_id' => $ext_id]);
    }

    /**
     * @param ElkPets $elkPet
     * @throws ELKException
     */
    protected function updateElkPetSetMainPet($elkPet)
    {
        if ($elkPet->pet->is_main || !$elkPet->pet->id_main_pet) {
            return $elkPet;
        }

        $elkPet->id_pet = $elkPet->pet->id_main_pet;

        if (!$elkPet->save()) {
            throw new ELKException("Не удалось обновить данные по питомцу {$elkPet->name}");
        }

        return $this->findElkPet($elkPet->ext_id); // Перезапросим на всякий случай
    }
    /**
     * @param Pets $vetasPet
     * @param Pet  $pet
     * @throws ELKException
     */
    private function updatePet(Pets $vetasPet, Pet $pet)
    {
        if ($vetasPet->isReadOnly()) {
            // не обновляем снятых с учета
            return;
        }

        $vetasPet->setAttributes($pet->getAnimalData());
        if ($vetasPet->getDirtyAttributes()) {
            if (!$vetasPet->save()) {
                throw new ELKException("Не удалось обновить данные по питомцу {$vetasPet->name}");
            }
        }

        if (!empty($pet->chip_number)) {
            $identification = PetIdentification::findOne([
                'id_pet' => $vetasPet->id,
                'id_ident_type' => 1,
                'identification_code' => $pet->chip_number,
            ]);
            if (!$identification) {
                $this->savePetIdentification($vetasPet, $pet->chip_number);
            }
        }
    }

    /**
     * @param Pets      $vetasPet
     * @param Pet       $pet
     * @param ElkOwners $elkOwner
     * @throws ELKException
     */
    private function saveElkPet(Pets $vetasPet, Pet $pet, ElkOwners $elkOwner)
    {
        $data = [
            'id_pet' => $vetasPet->id,
            'ext_id' => $pet->pet_id,
            'id_elk_owner' => $elkOwner->id,
            'id_pet_owner' => $elkOwner->id_owner,
        ];
        $elkPet = new ElkPets(array_merge($data, $pet->getElkPetData()));

        if (!$elkPet->save()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $elkPet->getErrorSummary(true),
                "\nНе удалось сохранить профиль животного из ЕЛК:"
            ));
        }
    }

    /**
     * @param ElkPets $elkPet
     * @param Pet     $pet
     */
    private function updateElkPet(ElkPets $elkPet, Pet $pet)
    {
        $elkPet->setAttributes($pet->getElkPetData());
        if ($elkPet->getDirtyAttributes() && $pet->validate()) {
            $elkPet->save(false);
        }
    }

    /**
     * @param Pet $pet
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function deletePet(Pet $pet)
    {
        $elkPet = ElkPets::findOne(['ext_id' => $pet->pet_id]);
        if (!$elkPet->hasVisits() && !$elkPet->hasViolations()) {
            $this->deletePetToOwner($elkPet->id_pet_owner, $elkPet->id_pet);
            if (!$elkPet->hasAnothePetLinks()) {
                $elkPet->pet->delete();
            }
        }

        $elkPet->delete();
    }

    /**
     * @param int $id_owner
     * @param int $id_pet
     * @throws \yii\db\Exception
     */
    private function deletePetToOwner(int $id_owner, int $id_pet)
    {
        \Yii::$app->db->createCommand('delete from pets_to_owner where id_owner = :id_owner and id_pet = :id_pet', [
            ':id_owner' => $id_owner,
            ':id_pet' => $id_pet,
        ])->execute();
    }

    /**
     * @param array  $errors
     * @param string $message
     * @return string
     */
    public static function prepareErrors(array $errors, $message = '')
    {
        return "{$message}\n- " . implode("\n- ", $errors) . "\n";
    }

    /**
     * @param \app\models\db\PetOwners $owner
     * @param \app\models\db\Pets      $pet
     * @param bool                     $isNewPet
     * @throws \app\common\components\inform\InformException
     */
    protected function notifyOwner($owner, $pet, $isNewPet)
    {
        $speciesTechName = $pet->species->tech_name;

        if ($speciesTechName != Species::TECH_NAME_DOG && $speciesTechName != Species::TECH_NAME_CAT) {
            return;
        }

        $contactTypeEmail = $this->contactTypeEmail();
        $query = Contacts::find()
            ->where([
                'id_contact_type' => $contactTypeEmail->id,
                'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
                'entity_id' => $owner->id,
            ]);
        if (!empty($this->owner->email)) {
            $query->andWhere(['name' => $this->owner->email]);
        }
        /* @var $contact \app\models\db\Contacts */
        $contact = $query->one();

        if ($contact === null) {
            return;
        }

        /* @var $spkService \app\common\components\inform\SpkService */
        $spkService = \Yii::$app->spkService;
        if (!$spkService->checkIsEmailSubscribed($contact->name, $owner->sso_id)) {
            return;
        }

        $hasChip = PetIdentification::find()
            ->where([
                'id_pet' => $pet->id,
                'id_ident_type' => 1,
            ])->exists();

        // проверяем наличие вакцинации только для существующих животных,
        // для новых животных не имеет смысла
        $hasVaccination = ($isNewPet === true)
            ? false
            : PetRabiesVaccination::find()
                ->where(['id_pet' => $pet->id])
                ->andWhere(['>', 'date', (new \DateTime())->modify('-1 year')->format('Y-m-d')])
                ->exists();

        if ($hasChip === false && $hasVaccination === false) {
            // отсутствуют и чип и вакцинация
            \Yii::$app->trigger(
                SubscriptionEventInterface::EVENT_NAME,
                new InitialIdentificationAndVaccinationEvent([
                    'owner' => $owner,
                    'pet' => $pet,
                    'contacts' => [$contact],
                    'id_pet' => $pet->id
                ]));
        } elseif ($hasChip === false) {
            // отсутствует чип
            \Yii::$app->trigger(
                SubscriptionEventInterface::EVENT_NAME,
                new InitialIdentificationEvent([
                    'owner' => $owner,
                    'pet' => $pet,
                    'contacts' => [$contact],
                    'id_pet' => $pet->id
                ]));
        } elseif ($hasVaccination === false) {
            // отсутствует вакцинация
            \Yii::$app->trigger(
                SubscriptionEventInterface::EVENT_NAME,
                new InitialVaccinationEvent([
                    'owner' => $owner,
                    'pet' => $pet,
                    'contacts' => [$contact],
                    'id_pet' => $pet->id
                ]));
        }
    }
}