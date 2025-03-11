<?php

namespace app\modules\adminv\models\forms;

use app\models\db\Organizations;
use app\models\db\PetOwners;
use app\models\db\PetOwnersHistory;
use app\models\db\PetOwnerType;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\modules\v2\modules\petOwners\models\PetOwnersModel;
use yii\base\Model;
use yii\web\BadRequestHttpException;

class ShelterRepresentativeForm extends Model
{
    /**
     * @var String
     */
    public $i_fio;
    /**
     * @var String
     */
    public $f_fio;
    /**
     * @var String
     */
    public $o_fio;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['i_fio', 'f_fio'], 'required'],
            [['i_fio', 'f_fio', 'o_fio'], 'string'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'f_fio' => 'Фамилия представителя',
            'i_fio' => 'Имя представителя',
            'o_fio' => 'Отчество представителя',
        ];
    }

    /**
     * @param Organizations $organization
     * @return bool
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function save(Organizations $organization): bool
    {
        $transaction = \Yii::$app->db->beginTransaction();

        $owner = $organization->id_pet_owner
            ? $this->updateRepresentative($organization)
            : $this->createRepresentative($organization);

        if (!$owner) {
            $transaction->rollBack();
            return false;
        }

        $organization->id_pet_owner = $owner->id;
        if (!$organization->save()) {
            $transaction->rollBack();
            $errors = $organization->getErrorSummary(true);
            $errors = implode(";", array_values($errors));
            \Yii::$app->session->setFlash('error', "Ошибка при сохранении связки приюта: $errors");
            return false;
        }

        if (!$this->updateShelterAnimals($organization)) {
            $transaction->rollBack();
            return false;
        }

        $transaction->commit();
        return true;
    }

    /**
     * @param Organizations $organization
     * @return PetOwners|false
     */
    private function createRepresentative(Organizations $organization)
    {
        $name = $organization->name;
        try {
            return (new PetOwnersModel())->create(
                $this->f_fio,
                $this->i_fio,
                $this->o_fio,
                $organization->name,
                $organization->inn,
                $organization->ogrn,
                null,
                null,
                true,
                $organization->id_area,
                $organization->id_district,
                $organization->fias_addresses->toArray(),
                null,
                false,
                "Представитель Приюта ($name)",
                false
            );
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Yii::$app->session->setFlash('error', "Не удалось сохранить запись представителя: $error");
            return false;
        }
    }

    /**
     * @param Organizations $organization
     * @return PetOwners|false
     */
    private function updateRepresentative(Organizations $organization)
    {
        $name = $organization->name;
        /** @var PetOwners $owner */
        $owner = PetOwners::find()->where(['id' => $organization->id_pet_owner])->one();
        $owner->f_fio = $this->f_fio;
        $owner->i_fio = $this->i_fio;
        $owner->o_fio = $this->o_fio;
        $owner->jur_name = $organization->name;
        $owner->inn = $organization->inn;
        $owner->ogrn = $organization->ogrn;
        $owner->id_area = $organization->id_area;
        $owner->id_district = $organization->id_district;
        $owner->id_fias_address = $organization->id_fias_address;
        $owner->description = "Представитель Приюта ($name)";

        if (!$owner->save()) {
            $errors = $owner->getErrorSummary(true);
            $errors = implode(";", array_values($errors));
            \Yii::$app->session->setFlash('error', "Не удалось обновить запись представителя: $errors");
            return false;
        }

        return $owner;
    }

    /**
     * @param Organizations $organization
     * @return bool
     */
    public function updateShelterAnimals(Organizations $organization): bool
    {
        $representativeId = $organization->representative->id;

        /** @var Pets[] $pets */
        $pets = Pets::find()
            ->joinWith(['shelter_records'])
            ->leftJoin('pets_to_owner pto_owner', 'pto_owner.id_pet = pets.id')
            ->leftJoin('pets_to_owner pto_representative', "pto_representative.id_pet = pets.id AND pto_representative.id_owner = $representativeId")
            ->leftJoin('pet_owner_type owner',
                "(owner.id = pto_owner.id_owner_type AND owner.is_owner = true)".
                " OR (owner.id = pto_representative.id_owner_type AND owner.is_owner = false)"
            )
            ->where(['owner.id' => null])
            ->andWhere(['shelter_guests.id_organization' => $organization->id])
            ->andWhere(['shelter_guests.departure_date' => null])
            // Исключаем животных снятых с учёта
            ->andWhere(['id_reg_expire_reason' => null])
            ->andWhere(['reg_expire_date' => null])
            ->all()
        ;
        /** @var PetOwnerType $ownerType */
        $ownerType = PetOwnerType::find()->where(['is_owner' => false])->one();

        $user = \Yii::$app->user->getIdentity();
        $currentUserOrganizationName = null;
        if (!empty($user->specialist) && !empty($user->specialist->id_organization)) {
            $currentUserOrganizationName = Organizations::find()
                ->select('name')
                ->where(['id' => $user->specialist->id_organization])
                ->scalar();
        }

        // Работа через $model->save() для сотни записей занимает много времени
        // Сделаем подготовленные команды с bindValues
        $insertPetsToOwner = \Yii::$app->db->createCommand('INSERT into public.pets_to_owner
                                                                ("id_pet", "id_owner", "id_owner_type")
                                                                VALUES (:id_pet, :id_owner, :id_owner_type)');
        $insertPetsToOwnerHistory =
            \Yii::$app->db->createCommand('INSERT into public.pet_owners_history
                                ("id_pet", "id_owner", "owner_type_new", "owner_name", "created_by", "organization_name", "date")
                                VALUES (:id_pet, :id_owner, :owner_type_new, :owner_name, :created_by, :organization_name, CURRENT_TIMESTAMP)');

        try {
            foreach($pets as $pet) {
                // Проведём базовые валидации ручками
                if (!$pet->isReadOnly() && $ownerType) {
                    $insertPetsToOwner->bindValues([
                        ':id_pet' => $pet->id,
                        ':id_owner' => $organization->representative->id,
                        ':id_owner_type' => $ownerType->id,
                    ])->execute();

                    $insertPetsToOwnerHistory->bindValues([
                        ':id_pet' => $pet->id,
                        ':id_owner' => $organization->representative->id,
                        ':owner_type_new' => $ownerType->name,
                        ':owner_name' => $organization->representative->fullname,
                        ':created_by' => $user->login,
                        ':organization_name' => $currentUserOrganizationName,
                    ])->execute();
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Yii::$app->session->setFlash('error', "Не удалось связать представителя с питомцами в приюте: $error");
            return false;
        }
        return true;
    }

    /**
     * @param int|null $id_organization
     * @return $this
     */
    public function prepare(int $id_organization = null): self
    {
        if (!empty($id_organization)) {
            /** @var Organizations $organization */
            $organization = Organizations::find()->where(['id' => $id_organization])->one();
            if ($id = $organization->id_pet_owner) {
                /** @var PetOwners $owner */
                $owner = PetOwners::find()->where(['id' => $id])->one();
                $this->f_fio = $owner->f_fio;
                $this->i_fio = $owner->i_fio;
                $this->o_fio = $owner->o_fio;
            }
        }

        return $this;
    }
}
