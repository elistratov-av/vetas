<?php


namespace app\modules\v2\modules\pets\models;

use app\models\db\PetOwnerType;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use yii\web\BadRequestHttpException;
use app\models\db\Visits;
use app\models\db\VisitPets;
use app\models\db\FiasAddresses;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\models\db\IdentificationTypes;
use app\models\db\PetIdentification;
use app\models\db\VisitsGovServices;

class PetToOwnerModel
{
    /**
     * Возвращает указанный объект связи жифотное-владелец
     *
     * @param int $id
     * @return array|null
     */
    public function getPetsToOwner($id)
    {
        return PetsToOwner::find()
            ->where(['id' => $id])
            ->asArray()
            ->one();
    }

    /**
     * Создает связь животное-владелец/представитель
     *
     * @param int $id_pet
     * @param int $id_owner
     * @param int $id_owner_type
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function create($id_pet, $id_owner, $id_owner_type)
    {
        $pets_to_owner = new PetsToOwner([
            'id_pet' => $id_pet,
            'id_owner' => $id_owner,
            'id_owner_type' => $id_owner_type,
        ]);

        $this->validate($pets_to_owner);


        PetsToOwner::getDb()->beginTransaction();

        if (!$pets_to_owner->save()){
            $errors = $pets_to_owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при связи' : implode("\n", array_unique(array_values($errors))));
        }

        (new PetOwnersHistoryModel())->saveHistory($id_pet, $id_owner, null, $id_owner_type);

        PetsToOwner::getDb()->transaction->commit();


        return $pets_to_owner;
    }

    /**
     * Создает связь приема с животным и владелецем
     *
     * @param int $id_pet
     * @param int $id_owner
     * @param bool $is_owner
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function addToVisit($id_pet, $id_owner, $is_owner, $id_visit, $id_tmp_pet)
    {
        $id_owner_type = $is_owner ? PetOwnerType::findOwnerTypeId() : PetOwnerType::findRepresentativeTypeId();

        $pets_to_owner = PetsToOwner::find()
        ->where(['AND',['id_pet' => $id_pet], ['id_owner' => $id_owner], ['id_owner_type' => $id_owner_type]])
        ->asArray()
        ->one();
        
        if (empty($pets_to_owner)) {
            if($is_owner) {
                $pets_to_owner_owner = PetsToOwner::find()
                ->where(['AND',['id_pet' => $id_pet], ['id_owner_type' => $id_owner_type]])
                ->asArray()
                ->one();

                if(!empty($pets_to_owner_owner)) {
                    $this->edit($pets_to_owner_owner['id'],  PetOwnerType::findRepresentativeTypeId(), $pets_to_owner_owner['id_owner']);
                }
            }
            $pets_to_owner = PetsToOwner::find()
            ->where(['AND',['id_pet' => $id_pet], ['id_owner_type' => $id_owner_type]])
            ->asArray()
            ->one();
            if (empty($pets_to_owner)) {
                $pets_to_owner = $this->create($id_pet, $id_owner, $id_owner_type);
            }
            else {
                $pets_to_owner = $this->edit($pets_to_owner['id'], $id_owner_type, $id_owner);
            }
        }

        Visits::updateAll(['id_pet' => $id_pet, 'id_owner' => $id_owner, 'is_for_unauth_client' => false], ['id' => $id_visit]); 
        VisitPets::updateAll(['id_pet' => $id_pet], ['AND', ['id_visit' => $id_visit],['id_pet' => $id_tmp_pet]]);  
        VisitsGovServices::updateAll(['id_pet' => $id_pet], ['id_visit' => $id_visit]); 

        return $pets_to_owner;
    }

    /**
     * Создает животное и связывает его с владельцем и приемом
     *
     * @param int $id_species
     * @param string $sex
     * @param string $name
     * @param int $id_owner
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function createPetForVisit($id_species, $sex, $name, $id_owner, $id_visit, $id_tmp_pet){
        $pet = new Pets();
        $pet->id_species = $id_species;
        $pet->sex = $sex;
        $pet->name = $name;
        $pet->save();

        return $this->addToVisit($pet->id, $id_owner, true, $id_visit, $id_tmp_pet);
    }

     /**
     * Создает владельца и связывает его с животным и приемом
     *
     * @param int $id_pet
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $phone
     * @param string $email
     * @param string $fias_address
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function createOwnerForVisit($id_pet, $f_fio, $i_fio, $o_fio, $phone, $email="", $fias_address, $id_visit, $id_tmp_pet, $is_owner) {

        $pet_owner = new PetOwners();

        $pet_owner->f_fio = $f_fio;
        $pet_owner->i_fio = $i_fio;
        $pet_owner->o_fio = $o_fio;
        $pet_owner->addresses_is_equal = true;

        PetOwners::getDb()->beginTransaction();

        $pet_owner->id_fias_address = FiasAddresses::findOrCreateFiasAddress($fias_address);
        if (!$pet_owner->id_area) {
            $pet_owner->id_area = FiasAddresses::findOne($pet_owner->id_fias_address)->id_area;
        }
        if (!$pet_owner->id_district) {
            $pet_owner->id_district = FiasAddresses::findOne($pet_owner->id_fias_address)->id_district;
        }
        $pet_owner->id_fact_fias_address = FiasAddresses::findOrCreateFiasAddress($fias_address);
        if (!$pet_owner->id_area) {
            $pet_owner->id_area = FiasAddresses::findOne($pet_owner->id_fact_fias_address)->id_area;
        }
        if (!$pet_owner->id_district) {
            $pet_owner->id_district = FiasAddresses::findOne($pet_owner->id_fact_fias_address)->id_district;
        }
        if (!$pet_owner->save()) {
            $errors = $pet_owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании владельца/представителя' : implode("\n", array_values($errors)));
        }

        $phone_contact = new Contacts([
            'id_contact_type' => ContactTypes::findContactTypeId('Мобильный телефон', 'pet_owner'),
            'entity_type' => 'pet_owner',
            'entity_id' => $pet_owner->id,
            'name' => '+' . str_replace('+', '', $phone),
            'main_flag' => false,
        ]);

        if (!$phone_contact->save()) {
            $errors = $phone_contact->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании контакта' : implode("\n", array_values($errors)));
        }

        if($email) {
            $email_contact = new Contacts([
                'id_contact_type' => ContactTypes::findContactTypeId('Электронная почта', 'pet_owner'),
                'entity_type' => 'pet_owner',
                'entity_id' => $pet_owner->id,
                'name' => $email,
                'main_flag' => false,
            ]);

            if (!$email_contact->save()) {
                $errors = $email_contact->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании контакта' : implode("\n", array_values($errors)));
            }
        }

        $pets_to_owner = $this->addToVisit($id_pet, $pet_owner->id, $is_owner, $id_visit, $id_tmp_pet);

        PetOwners::getDb()->transaction->commit();

        return $pets_to_owner;
    }

    /**
     * Создает владельца и животное, связывает их друг с другом и с приемом
     *
     * @param int $id_species
     * @param string $sex
     * @param string $name
     * @param string $chip_number
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $phone
     * @param string $email
     * @param string $fias_address
     * @param int $id_visit
     * @param int $id_tmp_pet
     * @return PetsToOwner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function createPetAndOwnerForVisit($id_species, $sex, $name, $chip_number, $f_fio, $i_fio, $o_fio, $phone, $email, $fias_address, $id_visit, $id_tmp_pet) {

        $pet = new Pets();
        $pet->id_species = $id_species;
        $pet->sex = $sex;
        $pet->name = $name;
        $pet->save();

        if($chip_number) {
            $id_ident_type = IdentificationTypes::findIdentificationTypeId('чип');
            $identification_code = strval($chip_number);
            $identModel = new PetIdentification(compact('id_ident_type', 'identification_code'));
            $identModel->id_pet = $pet->id;
            $identModel->save();
        }

        $pets_to_owner = $this->createOwnerForVisit($pet->id, $f_fio, $i_fio, $o_fio, $phone, $email, $fias_address, $id_visit, $id_tmp_pet, true);

        return $pets_to_owner;
    }

    /**
     * Редактирование связи
     *
     * @param int $id
     * @param int $id_owner_type
     * @param int $id_owner
     * @return PetsToOwner|null
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function edit($id, $id_owner_type, $id_owner = null)
    {
        $pets_to_owner = PetsToOwner::findOne($id);

        if (empty($pets_to_owner)){
            throw new BadRequestHttpException('Указанная связь не найдена');
        }

        $pets_to_owner->id_owner_type = $id_owner_type;

        /*
         * Смена владельца
         */
        if (!empty($id_owner)){
            $pets_to_owner->id_owner = $id_owner;
        }

        $this->validate($pets_to_owner);


        PetsToOwner::getDb()->beginTransaction();

        /*
         * Записи в истории
         */
        if (empty($id_owner) || $pets_to_owner->isAttributeChanged('id_owner') == false){
            $this->addHistoryChangeOwnerType($pets_to_owner);
        }else{
            $this->addHistoryChangeOwner($pets_to_owner);
        }

        if (!$pets_to_owner->save()){
            $errors = $pets_to_owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при связи' : implode("\n", array_values($errors)));
        }

        PetsToOwner::getDb()->transaction->commit();

        return $pets_to_owner;
    }

    /**
     * Смена пользователя должна порождать две записи в БД
     * 1. Пользователь старый - больше не владелец/представитель
     * 2. Новый пользователь - стал представителем/владельцем
     *
     * @param PetsToOwner $pets_to_owner
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    protected function addHistoryChangeOwner($pets_to_owner)
    {
        // ничего не изменилось - выходим
        if ($pets_to_owner->isAttributeChanged('id_owner_type') == false
            && $pets_to_owner->isAttributeChanged('id_owner') == false){
            return;
        }

        // Старый ушел
        (new PetOwnersHistoryModel())->saveHistory(
            $pets_to_owner->id_pet,
            $pets_to_owner->oldAttributes['id_owner'],
            $pets_to_owner->oldAttributes['id_owner_type'],
            null
        );

        // новый пришел
        (new PetOwnersHistoryModel())->saveHistory(
            $pets_to_owner->id_pet,
            $pets_to_owner->id_owner,
            null,
            $pets_to_owner->id_owner_type
        );

    }

    /**
     * Смена типа владельца порождает одну запись в истории
     * @param PetsToOwner $pets_to_owner
     * @throws BadRequestHttpException
     */
    public function addHistoryChangeOwnerType($pets_to_owner)
    {
        if ($pets_to_owner->isAttributeChanged('id_owner_type')) {
            (new PetOwnersHistoryModel())->saveHistory(
                $pets_to_owner->id_pet,
                $pets_to_owner->id_owner,
                $pets_to_owner->oldAttributes['id_owner_type'],
                $pets_to_owner->id_owner_type
            );
        }
    }

    /**
     * Удаляет связь владелец/животное
     *
     * @param int $id
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete($id)
    {
        $pets_to_owner = PetsToOwner::findOne($id);

        if (empty($pets_to_owner)){
            throw new BadRequestHttpException('Указанная связь не найдена');
        }

        // Проверяем животное
        $this->validatePet($pets_to_owner->id_pet);

        PetsToOwner::getDb()->beginTransaction();

        // Нельзя удалять последнего владельца/представителя (#1330)
        $count_owners = (int) PetsToOwner::find()
            ->where([
                'id_pet' => $pets_to_owner->id_pet,
            ])->count()
        ;

        if ($count_owners < 2){
            throw new BadRequestHttpException('У животного должен быть хотя бы один представитель/владелец');
        }

        if ($pets_to_owner->delete() === FALSE){
            throw new BadRequestHttpException('Неизвестная ошибка при удалении связи');
        }

        (new PetOwnersHistoryModel())->saveHistory(
            $pets_to_owner->id_pet,
            $pets_to_owner->id_owner,
            $pets_to_owner->id_owner_type,
            NULL
        );

        PetsToOwner::getDb()->transaction->commit();
    }


    /**
     * Возвращает список типов владельцев
     *
     * @return PetOwnerType[]
     */
    public function getOwnerTypes()
    {
        return PetOwnerType::find()->all();
    }

    /**
     * @param PetsToOwner $pets_to_owner
     * @throws BadRequestHttpException
     */
    protected function validate($pets_to_owner)
    {
        if (empty($pets_to_owner->id_owner_type) || !is_int($pets_to_owner->id_owner_type)){
            throw new BadRequestHttpException('id_owner_type должен быть числом');
        }

        if (empty($pets_to_owner->id_pet) || !is_int($pets_to_owner->id_pet)){
            throw new BadRequestHttpException('id_pet должен быть числом');
        }

        // Животное (сгенерирует ошибку если не найдено или не подлежит редактированию)
        $this->validatePet($pets_to_owner->id_pet);

        // Владельцы/представители
        $owner_type = PetOwnerType::findOne(['id' => $pets_to_owner->id_owner_type]);

        if (empty($owner_type)){
            throw new BadRequestHttpException('Указан неизвестный id_owner_type');
        }

        // Владелец может быть один
        if ($owner_type->is_owner == TRUE){
            $query = PetsToOwner::find()
                ->where([
                    'AND',
                    ['id_pet' => $pets_to_owner->id_pet],
                    ['id_owner_type' => $owner_type->id]
                ]);

            // Обновление существующей записи
            if ($pets_to_owner->isNewRecord !== true){
                $query->andWhere(
                    ['NOT', ['id' => $pets_to_owner->id]]
                );
            }

            $have_owner = $query->exists();

            if ($have_owner){
                throw new BadRequestHttpException('У указанного животного уже есть владелец');
            }
        }
    }

    /**
     * Возвращает животное или генерирует ошибку
     * ЕСЛИ оно не найдено или ЕСЛИ оно не подлежит редактированию
     *
     * @param $id_pet
     * @return Pets|null
     * @throws BadRequestHttpException
     */
    protected function validatePet($id_pet)
    {
        $pet = Pets::findOne(['id' => $id_pet]);

        if (empty($pet)) {
            throw new BadRequestHttpException('Указанное животное не найдено');
        }

        if ($pet->isReadOnly()) {
            throw new BadRequestHttpException('Снятое с учета животное не подлежит редактированию');
        }

        return $pet;
    }
}
