<?php

namespace app\modules\elk\models;

use app\common\components\inform\events\InitialIdentificationAndVaccinationEvent;
use app\common\components\inform\events\InitialIdentificationEvent;
use app\common\components\inform\events\InitialVaccinationEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\components\inform\jobs\RefreshSubscriptionsJob;
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
use app\modules\elk\types\Animal;
use app\modules\elk\types\Owner;
use yii\base\Model;
use yii\db\Query;

class SavePet extends Model
{
    /** @var Owner */
    public $owner;

    /** @var Animal[] */
    public $animals;

    /** @var bool  */
    protected $isNewOwner = false;

    public function rules()
    {
        return [
            [['owner', 'animals'], 'required'],
            ['owner', function ($attribute, $params, $validator) {
                if (empty($this->owner->SsoId)) {
                    $this->addError($attribute, 'Не указан SsoId владельца животного');
                }

                if (empty($this->owner->FirstName)) {
                    $this->addError($attribute, 'Не указано имя владельца животного');
                }

                if (empty($this->owner->LastName)) {
                    $this->addError($attribute, 'Не указана фамилия владельца животного');
                }
            }],
            ['animals', function ($attribute, $params, $validator) {
                $c = 0;
                foreach ($this->animals as $animal) {
                    $c++;
                    if (empty($animal->ID)) {
                        $this->addError($attribute, "Не передан идентификатор животного #{$c}");
                    }

                    if (empty($animal->SpeciesID)) {
                        $this->addError($attribute, "Не указан вид животного #{$c}");
                    }
                }
            }]
        ];
    }

    /**
     * @throws ELKException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function handle()
    {
        $elkOwner = $this->getElkOwner();

        if (!$owner = $elkOwner->owner) {
            if (!$owner = $this->findOwner()) {
                $owner = $this->createOwner();
            } elseif ($owner->sso_id != $this->owner->SsoId) {
                $owner->sso_id = $this->owner->SsoId;
                $owner->save(false);

                \Yii::$app->subscription_queue->push(new RefreshSubscriptionsJob([
                    'id_owner' => $owner->id,
                    'sso_id' => $this->owner->SsoId
                ]));
            }


            $elkOwner->id_owner = $owner->id;
            $elkOwner->save(false);
        }

        $this->saveMobilePhone($owner);
        $this->saveEmail($owner);

        if (!$this->isNewOwner) {
            $this->saveSnils($owner);
        }

        foreach ($this->animals as $animal) {
            if ($elkPet = $this->findElkPet($animal->ID)) {
                // если у животного были приемы - данные не редактируем
                if ($elkPet->hasVisits() || $elkPet->hasViolations()) {
                    continue;
                }
                $pet = $elkPet->pet;
                $this->updatePet($pet, $animal);
                $this->updateElkPet($elkPet, $animal);
            } else {
                $isNewPet = false;
                $pet = $this->findPet($animal);
                if ($pet === null) {
                    $pet = $this->createPet($animal);
                    $isNewPet = true;
                }
                $link = $this->linkPetAndOwner($pet, $owner);
                $this->saveElkPet($pet, $animal, $elkOwner);

                if ($link->id_owner_type == 1) {
                    // первичное уведомление владельца при внесении животного в ЕЛК
                    try {
                        $this->notifyOwner($owner, $pet, $isNewPet);
                    } catch (\Throwable $e) {
                        \Yii::error($e->getMessage(), 'subscription_queue');
                    }
                }
            }
        }
    }

    /**
     * @return ElkOwners
     * @throws ELKException
     */
    protected function getElkOwner()
    {
        if (!$elkOwner = ElkOwners::findOne(['sso_id' => $this->owner->SsoId])) {
            $elkOwner = new ElkOwners([
                'sso_id' => $this->owner->SsoId,
                'first_name' => $this->owner->FirstName,
                'last_name' => $this->owner->LastName,
                'middle_name' => isset($this->owner->MiddleName) ? $this->owner->MiddleName : null,
                'snils' => isset($this->owner->Snils) ? $this->owner->Snils : null,
                'phone' => isset($this->owner->Phone) ? PhoneHelper::extractMosRuPhoneNumber($this->owner->Phone) : null,
                'email' => isset($this->owner->Email) ? $this->owner->Email : null
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
     * @return PetOwners|array|bool|null|\yii\db\ActiveRecord
     * @throws \yii\base\InvalidConfigException
     */
    protected function findOwner()
    {
        if (!empty($this->owner->SsoId) &&
            $petOwner = PetOwners::find()->active()->bySsoId($this->owner->SsoId)->one()
        ) {
            return $petOwner;
        }

        if (!empty($this->owner->Snils) &&
            $petOwner = PetOwners::find()->active()->bySnils($this->owner->Snils)->one()
        ) {
            return $petOwner;
        }

        /** @var PetOwners $petOwner */
        $query = PetOwners::find()
            ->active()
            ->byFio(
                $this->owner->LastName,
                $this->owner->FirstName,
                isset($this->owner->MiddleName) ? $this->owner->MiddleName : ''
            )
            ->withoutSsoId();

        if (!empty($this->owner->Phone)) {
            $phone = PhoneHelper::extractMosRuPhoneNumber($this->owner->Phone);
        }
        if (!empty($phone)) {
            $query->byMobilePhone($phone);
        } elseif (!empty($this->owner->Email)) {
            $query->byEmail($this->owner->Email);
        } else {
            return false;
        }

        // Если в заявке передан СНИЛС и пользователя по нему не найдено (см. выше),
        // то ищем пользователя у которого СНИЛС не заполнен
        if (!empty($this->owner->Snils)) {
            $query->withoutSnils();
        }

        if ($petOwner = $query->one()) {
            return $petOwner;
        }

        return false;
    }

    /**
     * @return PetOwners
     * @throws ELKException
     */
    protected function createOwner()
    {
        $owner = new PetOwners([
            'f_fio' => $this->owner->LastName,
            'i_fio' => $this->owner->FirstName,
            'o_fio' => $this->owner->MiddleName ?? null,
            'sso_id' => $this->owner->SsoId
        ]);

        if (!empty($this->owner->Snils)) {
            $owner->snils = $this->owner->Snils;
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
    protected function saveMobilePhone($owner)
    {
        if (empty($this->owner->Phone)) {
            return;
        }

        $phone = PhoneHelper::extractMosRuPhoneNumber($this->owner->Phone);
        if (empty($phone)) {
            return;
        }

        $contactType = $this->contactTypePhone();

        if (!$this->isContactExists($contactType->id, $owner->id, $phone)) {
            $contact = new Contacts();
            $contact->entity_type = ContactTypes::ENTITY_TYPE_PET_OWNER;
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
    protected function saveEmail($owner)
    {
        if (empty($this->owner->Email)) {
            return;
        }

        $contactTypeEmail = $this->contactTypeEmail();

        if (!$this->isContactExists($contactTypeEmail->id, $owner->id, $this->owner->Email)) {
            $contactEmail = new Contacts();
            $contactEmail->entity_type = ContactTypes::ENTITY_TYPE_PET_OWNER;
            $contactEmail->entity_id = $owner->id;
            $contactEmail->id_contact_type = $contactTypeEmail->id;
            $contactEmail->name = $this->owner->Email;
            $contactEmail->confirmed = true;

            if (!$contactEmail->save()) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $contactEmail->getErrorSummary(true),
                    "\nОшибка сохранения почты:"
                ));
            };
        }

        $this->removeOtherContacts(ContactTypes::TYPE_EMAIL, $owner->id, $this->owner->Email);
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
    protected function saveSnils($owner)
    {
        if (empty($owner->snils) && !empty($this->owner->Snils)) {
            $owner->snils = $this->owner->Snils;
            if ($owner->validate()) {
                $owner->save(false);
            }
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
        return (new Query())
            ->from('contacts')
            ->where(['id_contact_type' => $id_contact_type])
            ->andWhere(['entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER])
            ->andWhere(['entity_id' => $id_owner])
            ->andWhere(['name' => $contact])
            ->exists();
    }

    /**
     * @param Animal $animal
     * @return Pets|null
     */
    protected function findPet(Animal $animal)
    {
        return empty($animal->Chip)
            ? null
            : Pets::find()
                ->joinWith('pet_identification', true, 'INNER JOIN')
                ->where([
                    'pet_identification.identification_code' => $animal->Chip,
                    'pet_identification.id_ident_type' => 1 // чип
                ])
                ->one();
    }

    /**
     * @param Animal $animal
     * @return Pets
     * @throws ELKException
     */
    protected function createPet(Animal $animal)
    {
        $pet = new Pets();
        if (!$pet->load($animal->getAnimalData(), '') || !$pet->save()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $pet->getErrorSummary(true),
                "\nОшибка создания животного:"
            ));
        }

        if (!empty($animal->Chip)) {
            $this->savePetIdentification($pet, $animal->Chip);
        }

        return $pet;
    }

    /**
     * @param Pets $pet
     * @param $chip
     */
    protected function savePetIdentification(Pets $pet, $chip)
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
     * @return \app\models\db\PetsToOwner
     */
    protected function linkPetAndOwner(Pets $pet, $owner)
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

        return $link;
    }

    /**
     * @param $ext_id
     * @return null|ElkPets
     */
    protected function findElkPet($ext_id)
    {
        return ElkPets::findOne(['ext_id' => $ext_id]);
    }

    /**
     * @param Pets $pet
     * @param Animal $animal
     * @throws ELKException
     */
    protected function updatePet(Pets $pet, Animal $animal)
    {
        $pet->setAttributes($animal->getAnimalData());
        if ($pet->getDirtyAttributes()) {
            if (!$pet->save()) {
                throw new ELKException("Не удалось обновить данные по питомцу {$pet->name}");
            }
        }

        if (!empty($animal->Chip)) {
            $identification = PetIdentification::findOne([
                'id_pet' => $pet->id,
                'id_ident_type' => 1,
                'identification_code' => $animal->Chip
            ]);
            if (!$identification) {
                $this->savePetIdentification($pet, $animal->Chip);
            }
        }
    }

    /**
     * @param Pets $pet
     * @param Animal $animal
     * @param ElkOwners $elkOwner
     * @throws ELKException
     */
    protected function saveElkPet(Pets $pet, Animal $animal, ElkOwners $elkOwner)
    {
        $data = [
            'id_pet' => $pet->id,
            'ext_id' => $animal->ID,
            'id_elk_owner' => $elkOwner->id,
            'id_pet_owner' => $elkOwner->id_owner
        ];
        $elkPet = new ElkPets(array_merge($data, $animal->getElkPetData()));

        if (!$elkPet->save()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $elkPet->getErrorSummary(true),
                "\nНе удалось сохранить профиль животного из ЕЛК:"
            ));
        }
    }

    /**
     * @param ElkPets $pet
     * @param Animal $animal
     */
    protected function updateElkPet(ElkPets $pet, Animal $animal)
    {
        $pet->setAttributes($animal->getElkPetData());
        if ($pet->getDirtyAttributes() && $pet->validate()) {
            $pet->save(false);
        }
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
        if (!empty($this->owner->Email)) {
            $query->andWhere(['name' => $this->owner->Email]);
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
