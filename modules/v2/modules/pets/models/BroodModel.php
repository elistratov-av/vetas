<?php

namespace app\modules\v2\modules\pets\models;

use app\models\db\Breeds;
use app\models\db\Brood;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Species;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class BroodModel
 * @package app\modules\v2\modules\pets\models
 */
class BroodModel
{
    // TODO:dedupe-broods-constants избавиться от дублирования констант
    const DOG_BROOD_LIFETIME = Brood::DOG_TIME_MONTHS;
    const CAT_BROOD_LIFETIME = Brood::CAT_TIME_MONTHS;

    const MIN_BROOD_COUNT = Brood::MIN_PETS_COUNT;
    const MAX_BROOD_COUNT = Brood::MAX_PETS_COUNT;

    /**
     * @param int|null $id
     * @return array
     */
    public function findBrood($id)
    {
        $result = Brood::find()
            ->where(['id' => $id])
            ->with('owner')
            ->with('species')
            ->with('breed')
            ->with(['pets' => function ($query) {
                $this->preparePetsQuery($query);
                $query->asArray();
            }])
            ->asArray()
            ->one();

        return $result;
    }

    /**
     * @param int      $id_species
     * @param int|null $id_breed
     * @param string   $birthday
     * @param int      $id_owner
     * @param int      $id_owner_type
     * @param int      $pet_count
     * @return array|null
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function createBrood($id_species, $id_breed = null, $birthday, $id_owner, $id_owner_type, $pet_count)
    {
        $this->validateSpecies($id_species);
        $this->validateBreeds($id_species, $id_breed);
        $this->validateBirthday($birthday, $id_species);
        $this->validateCount($pet_count);

        $brood = new Brood();
        $brood->pet_count = $pet_count;
        $brood->id_breed = $id_breed;
        $brood->id_species = $id_species;
        $brood->id_owner = $id_owner;
        $brood->id_owner_type = $id_owner_type;
        $brood->birthday = $birthday;
        $brood->is_active = true;

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($brood->save()) {
                $id_created_organization = null;
                /** @var $user \app\common\models\UserModel */
                $user = \Yii::$app->user->getIdentity();
                if ($user->specialist !== null) {
                    $id_created_organization = $user->specialist->id_organization;
                }
                for ($i = 0; $i < $pet_count; $i++) {
                    $pet = new Pets();
                    $pet->id_species = $id_species;
                    $pet->id_breed = $id_breed;
                    $pet->birthday = $birthday;
                    $pet->id_brood = $brood->id;
                    $pet->id_created_organization = $id_created_organization;

                    if ($pet->save()) {
                        $petToOwner = new PetToOwnerModel();
                        $petToOwner->create($pet->id, $id_owner, $id_owner_type);
                    } else {
                        $errors = $pet->getErrorSummary(true);
                        throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании животного в выводке' : implode("\n", array_values($errors)));
                    }
                }
            } else {
                $errors = $brood->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании выводка' : implode("\n", array_values($errors)));
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }

        return $this->findBrood($brood->id);
    }

    /**
     * @param int      $id
     * @param int      $id_species
     * @param int|null $id_breed
     * @param string   $birthday
     * @param int      $id_owner
     * @param int      $id_owner_type
     * @return array|null
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function editBrood($id, $id_species, $id_breed = null, $birthday, $id_owner, $id_owner_type)
    {
        $brood = Brood::findOne(['id' => $id]);

        if ($brood === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        if ($brood->is_active === false) {
            throw new BadRequestHttpException('Редактирование неактивного выводка запрещено');
        }

        $this->validateSpecies($id_species);
        $this->validateBreeds($id_species, $id_breed);
        $this->validateBirthday($birthday, $id_species);

        /* @var $pets \app\models\db\Pets */
        $pets = $brood->getPets()
            ->andWhere(['id_reg_expire_reason' => null])
            ->all();
        $pet_count = count($pets);
        $this->validateCount($pet_count);

        if (!$this->validateVisits($pets)) {
            throw new BadRequestHttpException('Редактирование выводка, у которого есть приемы, запрещено');
        }

        $columns = [];

        foreach (compact('id_species', 'id_breed', 'birthday', 'id_owner', 'id_owner_type') as $column => $value) {
            if ($brood->$column != $value) {
                $columns[$column] = $value;
            }
        }

        if (!empty($columns)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $new_id_owner = null;
                $new_id_owner_type = null;
                $old_id_owner = $brood->id_owner;
                $old_id_owner_type = $brood->id_owner_type;
                $brood->setAttributes($columns);
                if ($brood->pet_count != $pet_count) {
                    $brood->pet_count = $pet_count;
                }
                if (!$brood->update()) {
                    $errors = $brood->getErrorSummary(true);
                    throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании выводка' : implode("\n", array_values($errors)));
                }
                if (array_key_exists('id_owner', $columns)) {
                    $new_id_owner = ArrayHelper::remove($columns, 'id_owner');
                }
                if (array_key_exists('id_owner_type', $columns)) {
                    $new_id_owner_type = ArrayHelper::remove($columns, 'id_owner_type');
                }
                foreach ($pets as $pet) {
                    if (!empty($columns)) {
                        $pet->setAttributes($columns);
                        if (!$pet->update()) {
                            $errors = $pet->getErrorSummary(true);
                            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при обновлении животного в выводке' : implode("\n", array_values($errors)));
                        }
                    }
                    if ($new_id_owner !== null || $new_id_owner_type !== null) {
                        /* @var $link \app\models\db\PetsToOwner */
                        $link = PetsToOwner::find()
                            ->where([
                                'id_pet' => $pet->id,
                                'id_owner' => $old_id_owner,
                                'id_owner_type' => $old_id_owner_type
                            ])
                            ->one();
                        if ($link === null) {
                            $link = new PetsToOwner();
                        }
                        $link->id_pet = $pet->id;
                        $link->id_owner = $brood->id_owner;
                        $link->id_owner_type = $brood->id_owner_type;
                        if (!$link->save()) {
                            $errors = $link->getErrorSummary(true);
                            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при обновлении животного в выводке' : implode("\n", array_values($errors)));
                        }
                    }
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollback();
                throw $e;
            }
        }

        return $this->findBrood($brood->id);
    }

    /**
     * @param int $id_brood
     * @return array
     */
    public function addPet($id_brood)
    {
        $brood = Brood::findOne(['id' => $id_brood]);

        if ($brood === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        if ($brood->is_active === false) {
            throw new BadRequestHttpException('Редактирование неактивного выводка запрещено');
        }

        /* @var $pets \app\models\db\Pets */
        $pets = $brood->getPets()
            ->andWhere(['id_reg_expire_reason' => null])
            ->all();
        $pet_count = count($pets) + 1;
        $this->validateCount($pet_count);

        if (!$this->validateVisits($pets)) {
            throw new BadRequestHttpException('Редактирование выводка, у которого есть приемы, запрещено');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $id_created_organization = null;
            /** @var $user \app\common\models\UserModel */
            $user = \Yii::$app->user->getIdentity();
            if ($user->specialist !== null) {
                $id_created_organization = $user->specialist->id_organization;
            }
            $pet = new Pets();
            $pet->id_species = $brood->id_species;
            $pet->id_breed = $brood->id_breed;
            $pet->birthday = $brood->birthday;
            $pet->id_brood = $brood->id;
            $pet->id_created_organization = $id_created_organization;
            if ($pet->save()) {
                $petToOwner = new PetToOwnerModel();
                $petToOwner->create($pet->id, $brood->id_owner, $brood->id_owner_type);
            } else {
                $errors = $pet->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании животного в выводке' : implode("\n", array_values($errors)));
            }
            $brood->pet_count = $pet_count;
            if (!$brood->save()) {
                $errors = $brood->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании выводка' : implode("\n", array_values($errors)));
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }

        return $this->findBrood($brood->id);
    }

    /**
     * @param int $id_brood
     * @param int $id_pet
     * @return array
     */
    public function removePet($id_brood, $id_pet)
    {
        $brood = Brood::findOne(['id' => $id_brood]);

        if ($brood === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        if ($brood->is_active === false) {
            throw new BadRequestHttpException('Редактирование неактивного выводка запрещено');
        }

        $pet = Pets::findOne(['id' => $id_pet]);

        if ($pet === null) {
            throw new BadRequestHttpException('Животное не найдено');
        }
        if ($pet->id_brood != $brood->id) {
            throw new BadRequestHttpException('Животное не принадлежит данному выводку');
        }

        /* @var $pets \app\models\db\Pets */
        $pets = $brood->getPets()
            ->andWhere(['id_reg_expire_reason' => null])
            ->all();
        $pet_count = count($pets) - 1;
        $this->validateCount($pet_count);

        if (!$this->validateVisits($pets)) {
            throw new BadRequestHttpException('Редактирование выводка, у которого есть приемы, запрещено');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$pet->delete()) {
                $errors = $pet->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении животного в выводке' : implode("\n", array_values($errors)));
            }
            $brood->pet_count = $pet_count;
            if (!$brood->save()) {
                $errors = $brood->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании выводка' : implode("\n", array_values($errors)));
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollback();
            throw $e;
        }

        return $this->findBrood($brood->id);
    }

    public function remove(int $id_brood): bool
    {
        $brood = Brood::findOne(['id' => $id_brood]);

        if ($brood === null) {
            throw new BadRequestHttpException('Выводок не найден');
        }

        /* @var $pets \app\models\db\Pets */
        $pets = $brood->getPets()
            ->andWhere(['id_reg_expire_reason' => null])
            ->all();

        if (!$this->validateVisits($pets)) {
            throw new BadRequestHttpException('Удаление выводка, у которого есть приемы, запрещено');
        }

        $brood->delete();

        return true;
    }

    /**
     * @param int $id_brood
     * @param int $id_owner Если указано показывает только доступные для записи на прием животные
     * @return array|null
     */
    public function listPets($id_brood, $id_owner)
    {
        $brood = Brood::findOne(['id' => $id_brood]);

        if ($brood === null) {
            return null;
        }

        $query = Pets::find()
            ->andWhere(['pets.id_brood' => $id_brood])
            ->asArray();
        $this->preparePetsQuery($query);

        if (!empty($id_owner)) {
            $query->leftJoin('pets_to_owner', 'pets_to_owner.id_pet = pets.id')
                ->andWhere([
                    'AND',
                    ['pets_to_owner.id_owner'  => $id_owner],
                    ['IS', 'pets.reg_expire_date', null]
                ]);
        }

        return $query->all();
    }

    /**
     * @param int $id_owner
     * @param bool $has_active_brood Если TRUE показывает только доступные для записи на прием выводки
     * @return array
     */
    public function listBroods($id_owner, $has_active_brood)
    {
        $query = Brood::find()
            ->select([
                'broods.id',
                'broods.pet_count',
                'broods.id_breed',
                'broods.id_species',
                'broods.id_owner',
                'broods.id_owner_type',
                'broods.birthday',
                'broods.is_active',
            ])
            ->with('owner')
            ->with('species')
            ->with('breed')
            ->where(['broods.id_owner' => $id_owner])
            ->orderBy(['broods.id' => SORT_ASC])
            ->asArray();

        if ($has_active_brood){
            /*
             * Только те, что доступны для записи на прием
             * 1. Активные выводки
             * 2. в нём есть минимум 2 животных принадлежащих владельцу
             * 3. и они не сняты с учета
             */
            $sub_query = (new Query())
                ->select(['pets.id_brood'])
                ->from('pets_to_owner')
                ->leftJoin('pets', 'pets_to_owner.id_pet = pets.id')
                ->where([
                    'AND',
                    ['pets_to_owner.id_owner'  => $id_owner],
                    ['IS', 'pets.reg_expire_date', null]
                ])
                ->groupBy('pets.id_brood')
                ->having((new Expression('count(pets.id_brood) > 1')))
            ;
            $query
                ->andWhere(['is_active' => true])
                ->andWhere(['IN', 'broods.id', $sub_query])
                ;
        }

        return $query->all();
    }

    /**
     * @param int $id_species
     */
    private function validateSpecies($id_species)
    {
        $species = Species::findOne(['id' => $id_species]);

        if ($species === null || ($species->tech_name !== Species::TECH_NAME_DOG && $species->tech_name !== Species::TECH_NAME_CAT)) {
            throw new BadRequestHttpException('Передан некорректный вид животного');
        }
    }

    /**
     * @param int      $id_species
     * @param int|null $id_breed
     */
    private function validateBreeds($id_species, $id_breed = null)
    {
        if (empty($id_breed)) {
            return;
        }

        $breeds = Breeds::findOne(['id' => $id_breed]);

        if ($breeds === null || $breeds->species_id != $id_species) {
            throw new BadRequestHttpException('Передана некорректная порода животного');
        }
    }

    /**
     * @param string $birthday
     * @param int    $id_species
     * @throws \yii\web\BadRequestHttpException
     */
    private function validateBirthday($birthday, $id_species)
    {
        $currentDate = new \DateTime('NOW');
        $birthdayDate = \DateTime::createFromFormat('Y-m-d', $birthday);

        $interval = $birthdayDate->diff($currentDate)->m;

        if (
            ($id_species == self::getDogSpeciesId() && $interval > self::DOG_BROOD_LIFETIME) ||
            ($id_species == self::getCatSpeciesId() && $interval > self::CAT_BROOD_LIFETIME)
        ) {
            throw new BadRequestHttpException('Указанный возраст выводка превышает максимальны допустимый!');
        }

        if ($birthdayDate > $currentDate) {
            throw new BadRequestHttpException('Дата рождения не может быть больше текущей даты');
        }
    }

    /**
     * @param int $pet_count
     * @throws \yii\web\BadRequestHttpException
     */
    private function validateCount($pet_count)
    {
        if ($pet_count < self::MIN_BROOD_COUNT) {
            throw new BadRequestHttpException('Количество животных выводка не может быть меньше 2 особей');
        }

        if ($pet_count > self::MAX_BROOD_COUNT) {
            throw new BadRequestHttpException('Количество животных выводка не может быть больше 15 особей');
        }
    }

    /**
     * @return int
     */
    private static function getDogSpeciesId()
    {
        return Species::find()
            ->select('id')
            ->where(['tech_name' => Species::TECH_NAME_DOG])
            ->scalar();
    }

    /**
     * @return int
     */
    private static function getCatSpeciesId()
    {
        return Species::find()
            ->select('id')
            ->where(['tech_name' => Species::TECH_NAME_CAT])
            ->scalar();
    }

    /**
     * @param $query \app\models\db\PetsQuery|\yii\db\ActiveQuery
     */
    private function preparePetsQuery(&$query)
    {
        $query->with('reg_certificate')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
            ->with('brood')
            ->with('reg_expire_reason')
            ->with('organizations')
            ->with(['pets_to_owner' => function ($q) {
                /* @var $q \yii\db\ActiveQuery */
                $q->orderBy([
                    'id_owner_type' => SORT_ASC,
                ]);
            }])
            ->with('pets_to_owner.owner_type')
            ->with('pets_to_owner.owner')
            ->with('pets_to_owner.owner.fias_addresses')
            ->orderBy(['pets.id' => SORT_ASC]);
    }

    /**
     * @param \app\models\db\Pets[] $pets
     * @return bool
     */
    private function validateVisits($pets)
    {
        foreach ($pets as $pet) {
            if (!empty($pet->visits)) {
                return false;
            }
        }

        return true;
    }
}
