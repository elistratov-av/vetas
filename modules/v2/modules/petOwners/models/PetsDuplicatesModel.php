<?php

namespace app\modules\v2\modules\petOwners\models;

use app\common\models\VisitStatus;
use app\models\db\IdentificationTypes;
use app\models\db\Organizations;
use app\models\db\PetIdentification;
use app\models\db\PetIdentificationTransferLog;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\PetsLinkHistory;
use app\models\db\PetsToOwner;
use app\models\db\PetsToOwnerTransferLog;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use app\modules\v2\modules\pets\models\PetToOwnerModel;
use yii\base\Exception;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class PetsDuplicatesModel
 * @package app\modules\v2\modules\petOwners\models
 */
class PetsDuplicatesModel extends Model
{
    /**
     * Метод подбора дублирующих записей животных (второй шаг объединения дублей)
     * (п.1.4 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916)
     *
     * @param int $id_main_owner
     * @return array
     */
    public function check($id_main_owner)
    {
        $result = [
            'processed_pets' => [],
            'unprocessed_pets' => [
                'main_owner_pets' => [],
                'duplicate_owners_pets' => [],
            ],
        ];

        $mainOwner = (new PetOwnersModel())->getPetOwner($id_main_owner, true);

        if ($mainOwner === null || $mainOwner['is_main'] !== true) {
            return $result;
        }

        $result['processed_pets'] = $this->preparePetsQuery($id_main_owner, true)
            ->andWhere([
                'p.is_main' => true,
            ])
            ->all();
        $result['unprocessed_pets']['main_owner_pets'] = $this->preparePetsQuery($id_main_owner, false)
            ->andWhere([
                'p.is_main' => null,
            ])
            ->all();

        if (!empty($mainOwner['duplicates'])) {
            $duplicate_owners_ids = ArrayHelper::getColumn($mainOwner['duplicates'], 'id');
            $duplicatesQuery = $this->preparePetsQuery($duplicate_owners_ids, false)
                ->andWhere([
                    'p.is_main' => null,
                ]);
            if (!empty($result['unprocessed_pets']['main_owner_pets'])) {
                // возможно повторное попадание в выборку животных у владельцев и представителей
                $pets_ids = ArrayHelper::getColumn($result['unprocessed_pets']['main_owner_pets'], 'id');
                if (!empty($pets_ids)) {
                    $duplicatesQuery->andWhere(['not in', 'p.id', $pets_ids]);
                }
            }
            $result['unprocessed_pets']['duplicate_owners_pets'] = $duplicatesQuery->all();
        }

        return $result;
    }

    /**
     * Метод выбора основного животного
     * (п.1.4.1 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Основная запись")
     *
     * @param int   $id_main_owner
     * @param int   $id_main_pet
     * @return bool
     * @throws \Throwable
     */
    public function makeMain($id_main_owner, $id_main_pet)
    {
        $mainOwner = PetOwners::findOne(['id' => $id_main_owner]);

        if ($mainOwner->is_main !== true) {
            $this->addError('id_main_owner', 'Владелец должен иметь признак "Основной"');

            return false;
        }

        $pet = Pets::findOne(['id' => $id_main_pet]);

        if ($pet === null) {
            $this->addError('id_main_pet', 'Животное не найдено');

            return false;
        }

        if ($pet->is_main === false) {
            $this->addError('id_main_pet', 'Животное является дублем');

            return false;
        }

        if ($pet->is_main === true) {
            $this->addError('id_main_pet', 'Животное уже имеет признак "Основное"');

            return false;
        }

        $link = PetsToOwner::findOne([
            'id_owner' => $id_main_owner,
            'id_pet' => $id_main_pet,
        ]);

        $transaction = \Yii::$app->db->beginTransaction();

        if ($link !== null) {
            // если животное с $id_main_pet принадлежит основному владельцу - делаем его основным,
            try {
                $pet->is_main = true;
                $pet->id_main_pet = null;
                $pet->duble_validation = date('Y-m-d H:i:s');
                $pet->id_relocate = null;

                if (!$pet->update(true, ['is_main', 'id_main_pet', 'duble_validation', 'id_relocate', 'updated_at', 'updated_by'])) {
                    $this->addErrors($pet->getErrors());
                    $transaction->rollBack();

                    return false;
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                \Yii::error($e);

                return false;
            }
        } else {
            // если нет - сначала создаем копию записи-дубля у основного владельца
            try {
                $mainPet = new Pets();
                $mainPet->load($pet->toArray(), '');
                $mainPet->is_main = true;
                $mainPet->duble_validation = date('Y-m-d H:i:s');
                $mainPet->id_relocate = $pet->id;

                if (!$mainPet->save()) {
                    $this->addErrors($mainPet->getErrors());
                    $transaction->rollBack();

                    return false;
                }

                $pet->is_main = false;
                $pet->id_main_pet = $mainPet->id;
                $pet->duble_validation = date('Y-m-d H:i:s');

                if (!$pet->update(true, ['is_main', 'id_main_pet', 'duble_validation', 'updated_at', 'updated_by'])) {
                    $this->addErrors($pet->getErrors());
                    $transaction->rollBack();

                    return false;
                }

                $link = new PetsToOwner();
                $link->id_pet = $mainPet->id;
                $link->id_owner = $id_main_owner;
                $link->id_owner_type = 1;

                if (!$link->save()) {
                    $this->addErrors($link->getErrors());
                    $transaction->rollBack();

                    return false;
                }

                // переносим представителей от дубля основному животному
                $this->transferPetRepresentatives($mainPet, $pet);

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                \Yii::error($e);

                return false;
            }
        }

        return true;
    }

    /**
     * Метод объединения животных
     * (п.1.4.2 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Объединить выбранные записи")
     *
     * @param int   $id_main_owner
     * @param int   $id_main_pet
     * @param array $duplicates_ids
     * @return bool
     * @throws \Throwable
     */
    public function linkPets($id_main_owner, $id_main_pet, $duplicates_ids, $merge_data, $isShelterPets = false)
    {
        $mainOwner = PetOwners::findOne(['id' => $id_main_owner]);

        if ($mainOwner->is_main !== true) {
            $this->addError('id_main_owner', 'Владелец должен иметь признак "Основной"');

            return false;
        }

        $mainPet = Pets::findOne(['id' => $id_main_pet]);

        if ($mainPet === null) {
            $this->addError('id_main_pet', 'Животное не найдено');

            return false;
        }

        if ($mainPet->is_main !== true) {
            $this->addError('id_main_pet', 'Животное должно иметь признак "Основное"');

            return false;
        }

        $duplicates = [];
        $unequalSpecies = [];

        foreach ($duplicates_ids as $id_duplicate) {
            $pet = Pets::findOne(['id' => $id_duplicate]);

            if ($pet->id_species !== $mainPet->id_species) {
//                $unequalSpecies[] = $id_duplicate;
                continue;
            }

            if (!empty($pet->reg_certificate)){
                $this->addError('reg_certificate', 'Животное с рег. удостоверением нельзя сделать дублем');

                return false;
            }

            if ($pet === null) {
                $this->addError('duplicates_ids', 'Животное не найдено');

                return false;
            }
            $duplicates[] = $pet;

            $visitIds = [];
            $visits = $this->getVisits($pet);
            foreach ($visits as $visit) {
                $visitIds[] = $visit->id;
            }

            Visits::updateAll(['id_owner' => $id_main_owner, 'id_pet' => $id_main_pet], ['IN', 'id', $visitIds]);

        }

        if (!empty($unequalSpecies)) {
            $this->addError('json', json_encode([
                'text' => 'У объединяемых записей вид животного различается.',
                'data' => $unequalSpecies
            ], JSON_UNESCAPED_UNICODE));

            return false;
        }

        $copyFieldsNames = ['birthday', 'photo', 'id_breed', 'name', 'sex', 'guide_dog', 'castrated'];
        $emptyFields = [];

        //находим незаполненные поля
        foreach ($copyFieldsNames as $copyFieldsName) {
            if (empty($mainPet->$copyFieldsName) || in_array($copyFieldsName, array_keys($merge_data))) {
                $emptyFields[$copyFieldsName] = ['time' => false, 'pet_id' => null, 'value' => null];
            }
        }

        $transaction = \Yii::$app->db->beginTransaction();

        if ($duplicates) {


            try {
                foreach ($duplicates as $pet) {
                    foreach ($emptyFields as $emptyFieldName => $updateData) {
                        if (empty($pet->$emptyFieldName)) {
                            continue;
                        }
                        if (!$updateData['time'] || strtotime($pet->updated_at) > $updateData['time']) {
                            $emptyFields[$emptyFieldName]['time'] = strtotime($pet->updated_at);
                            $emptyFields[$emptyFieldName]['pet_id'] = $pet->id;
                            $emptyFields[$emptyFieldName]['value'] = $pet->$emptyFieldName;
                            $emptyFields[$emptyFieldName]['old_value'] = $mainPet->$emptyFieldName;

                            if (in_array($emptyFieldName, array_keys($merge_data)))
                            {
                                $mainPet->$emptyFieldName = $merge_data[$emptyFieldName];
                            }
                            else
                                $mainPet->$emptyFieldName = $pet->$emptyFieldName;
                        }
                    }

                    $pet->is_main = false;
                    $pet->id_main_pet = $id_main_pet;
                    $pet->id_relocate = null;
                    $pet->duble_validation = date('Y-m-d H:i:s');
                    if (!$pet->update(true, ['is_main', 'id_main_pet', 'id_relocate', 'duble_validation', 'updated_at', 'updated_by'])) {
                        $this->addErrors($pet->getErrors());
                        $transaction->rollBack();

                        return false;
                    }
                    // переносим идентификаторы дубля основному животному
                    $this->transferPetIdentifications($mainPet, $pet);
                    // переносим представителей от дубля основному животному
                    $this->transferPetRepresentatives($mainPet, $pet);

                }

                // проверяем, что у животного есть нарушение и закрываем, если у дубля есть вакцины
                (new ViolationModel())->checkAndCancelPetsRabiesViolation([$mainPet->id]);

                try {
                    $mainPet->update(false, $copyFieldsNames);
                } catch (\Throwable $e) {
                    $this->addErrors($mainPet->getErrors());
                    $transaction->rollBack();
                    \Yii::error($e);

                    return false;

                }


                $historyData = [];
                foreach ($emptyFields as $emptyFieldName => $emptyFieldData) {
                    if (empty($emptyFieldData['pet_id'])) {
                        continue;
                    }
                    $historyData[$emptyFieldData['pet_id']]['values'][$emptyFieldName] = $emptyFieldData;
                }

                foreach ($historyData as $id => $data) {
                    $ownerHistory = new PetsLinkHistory();
                    $ownerHistory->id_pet_main = $mainPet->id;
                    $ownerHistory->id_pet_duplicate = $id;
                    $ownerHistory->values = $data['values'];
                    $ownerHistory->save();
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                \Yii::error($e);

                return false;
            }

            return true;
        } else {
            $this->addError('id_main_pet', 'Нет подходящего животного для склейки');

            return false;
        }
    }

    /**
     * Метод открепления дублирующих записей животных
     * (п.1.4.3 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     *
     * @param int $id_main_owner
     * @param int $id_pet
     * @return bool
     * @throws \Throwable
     */
    public function unlinkPet($id_main_owner, $id_pet)
    {
        $mainOwner = PetOwners::findOne(['id' => $id_main_owner]);

        if ($mainOwner->is_main !== true) {
            $this->addError('id_main_owner', 'Владелец должен иметь признак "Основной"');

            return false;
        }

        $pet = Pets::findOne(['id' => $id_pet]);

        if ($pet === null) {
            $this->addError('id_pet', 'Животное не найдено');

            return false;
        }

        if (empty($pet->id_main_pet)) {
            $this->addError('id_pet', 'Животное не прикреплено к основной записи');

            return false;
        }

        $id_main_pet = $pet->id_main_pet;

        $pet->is_main = null;
        $pet->id_main_pet = null;
        $pet->duble_validation = null;

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if (!$pet->update(true, ['is_main', 'id_main_pet', 'duble_validation', 'updated_at', 'updated_by'])) {
                $this->addErrors($pet->getErrors());
                $transaction->rollBack();

                return false;
            }
            if ($id_main_pet !== null) {
                $mainPet = Pets::findOne(['id' => $id_main_pet]);
                if ($mainPet->id_relocate !== null) {
                    // Если открепленная основная запись была перенесена из дубля (pets.id_relocate !== null)
                    // и у нее нет связанных сущностей, то необходимо удалить эту запись. (Если связей нет, то только открепляем).
                    $duplicates = $mainPet->duplicates;
                    $duplicates = ArrayHelper::index($duplicates, 'id');
                    ArrayHelper::remove($duplicates, $pet->id);
                    if (empty($duplicates)) {
                        if (!empty($mainPet->visits)) {
                            $this->addError('id_main_pet', 'У основной записи имеются приемы, отмена объединения невозможна');
                            $transaction->rollBack();

                            return false;
                        }
                        if (!empty($mainPet->violations)) {
                            $this->addError('id_main_pet', 'У основной записи имеются записи о нарушениях, отмена объединения невозможна');
                            $transaction->rollBack();

                            return false;
                        }
                        if (!$mainPet->delete()) {
                            $this->addErrors($mainPet->getErrors());
                            $transaction->rollBack();

                            return false;
                        }
                    } else {
                        if ($mainPet->id_relocate == $pet->id) {
                            $this->addError('id_main_pet', 'Сначала необходимо открепить другие записи-дубли');
                            $transaction->rollBack();

                            return false;
                        }
                    }
                }
                // возвращаем идентификаторы от основного животного дублю
                $this->restorePetIdentifications($mainPet, $pet);
                // возвращаем представителей от основного животного дублю
                $this->restorePetRepresentatives($mainPet, $pet);
                // возвращаем атрибуты основному животному
                $this->restorePetAttributes($mainPet, $pet);
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            \Yii::error($e);

            return false;
        }

        return true;
    }

    /**
     * Метод снятия признака "основная запись" у животного
     * (п.1.4.4 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     *
     * @param int $id_main_owner
     * @param int $id_main_pet
     * @return bool
     * @throws \Throwable
     */
    public function undoMakeMain($id_main_owner, $id_main_pet)
    {
        $mainOwner = PetOwners::findOne(['id' => $id_main_owner]);

        if ($mainOwner->is_main !== true) {
            $this->addError('id_main_owner', 'Владелец должен иметь признак "Основной"');

            return false;
        }

        $mainPet = Pets::findOne(['id' => $id_main_pet]);

        if ($mainPet === null) {
            $this->addError('id_main_pet', 'Животное не найдено');

            return false;
        }

        if ($mainPet->is_main !== true) {
            $this->addError('id_main_pet', 'Животное должно иметь признак "Основное"');

            return false;
        }

        $duplicates = $mainPet->duplicates;
        if (!empty($duplicates) && empty($mainPet->id_relocate)) {
            $this->addError('id_main_pet', 'Сначала необходимо открепить от основной записи все связанные с ней записи-дубли');

            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $mainPet->is_main = null;
            $mainPet->id_main_pet = null;
            $mainPet->duble_validation = null;

            if ($mainPet->id_relocate !== null) {
                if (!empty($mainPet->visits)) {
                    $this->addError('id_main_pet', 'У основной записи имеются приемы, отмена объединения невозможна');
                    $transaction->rollBack();

                    return false;
                }
                // Если открепленная основная запись была перенесена из дубля (pets.id_relocate !== null) и у нее нет связанных сущностей, то необходимо удалить эту запись. (Если связей нет, то только открепляем).
                foreach ($duplicates as $key => $pet) {
                    if ($pet->id == $mainPet->id_relocate) {
                        unset($duplicates[$key]);
                        break;
                    }
                }
                if (empty($duplicates)) {
                    if (!$mainPet->delete()) {
                        $this->addErrors($mainPet->getErrors());
                        $transaction->rollBack();

                        return false;
                    } else {
                        $transaction->commit();

                        return true;
                    }
                }
            }

            if (!$mainPet->update(true, ['is_main', 'id_main_pet', 'duble_validation', 'updated_at', 'updated_by'])) {
                $this->addErrors($mainPet->getErrors());
                $transaction->rollBack();

                return false;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            \Yii::error($e);

            return false;
        }

        return true;
    }

    /**
     * @param int|int[] $id_owner
     * @param bool      $withDuplicates
     * @return \app\models\db\PetsQuery|\yii\db\ActiveQuery
     */
    private function preparePetsQuery($id_owner = null, $withDuplicates = false)
    {
        $query = Pets::find()
            ->alias('p')
            ->leftJoin(PetsToOwner::tableName() . ' pto', 'pto.id_pet = p.id')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
            ->with('elk_pet')
            ->where([
                'AND',
                ['p.id_reg_expire_reason' => null],
                ['p.reg_expire_date' => null],
            ])
            ->orderBy([
                'pto.id_owner' => SORT_ASC,
                'p.id_species' => SORT_ASC,
                'p.id' => SORT_ASC,
            ])
            ->asArray();

        if (!empty($id_owner)) {
            $query->andWhere(['pto.id_owner' => $id_owner]);
        }

        if ($withDuplicates === true) {
            $query->with(['duplicates' => function ($q) {
                /* @var $q \app\models\db\PetsQuery */
                $q->with('pet_identification')
                    ->with('pet_identification.ident_type')
                    ->with('species')
                    ->with('breeds')
                    ->with('elk_pet')
                    ->where([
                        'AND',
                        ['id_reg_expire_reason' => null],
                        ['reg_expire_date' => null],
                    ])
                    ->orderBy([
                        'id_species' => SORT_ASC,
                        'id' => SORT_ASC,
                    ])
                    ->asArray();
            }]);
        }

        return $query;
    }

    /**
     * @param \app\models\db\Pets $mainPet
     * @param \app\models\db\Pets $pet
     */
    private function transferPetIdentifications(Pets $mainPet, Pets $pet)
    {
        if (empty($pet->pet_identification)) {
            return;
        }

        // проверим, нет ли уже у основного животного идентификаций с main_flag === true
        $exists = $this->mainIdentificationExists($mainPet->id);

        foreach ($pet->pet_identification as $identification) {
            $record = new PetIdentificationTransferLog();
            $record->id_pet_from = $pet->id;
            $record->id_pet_to = $mainPet->id;
            $record->id_ident_type = $identification->id_ident_type;
            $record->dehydrateData($identification);
            if (!$record->save()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }
            $copy = new PetIdentification();
            $record->hydrateData($copy);
            $copy->id_pet = $mainPet->id;
            $copy->created_at = null;
            $copy->updated_at = null;
            $copy->created_by = null;
            $copy->updated_by = null;
            if ($copy->main_flag === true && $exists === true) {
                $copy->main_flag = false;
            }
            if (!$identification->delete()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }
            if (!$copy->save()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }
        }
    }

    /**
     * @param \app\models\db\Pets $mainPet
     * @param \app\models\db\Pets $pet
     */
    private function restorePetIdentifications(Pets $mainPet, Pets $pet)
    {
        /** @var \app\models\db\PetIdentificationTransferLog[] $records */
        $records = PetIdentificationTransferLog::find()
            ->where([
                'id_pet_from' => $pet->id,
                'id_pet_to' => $mainPet->id,
            ])
            ->all();

        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            $identification = PetIdentification::findOne([
                'id_pet' => $mainPet->id,
                'id_ident_type' => $record->id_ident_type,
                'identification_code' => $record->identification_data['identification_code'],
            ]);
            if ($identification !== null) {
                if (!$identification->delete()) {
                    throw new Exception('Ошибка при переносе данных идентификации');
                }
            }
            $copy = new PetIdentification();
            $record->hydrateData($copy);
            if (!$copy->save()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }
            if (!$record->delete()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }
        }

        // если после переноса у основного животного не осталось ни одного идентификатора с признаком "основной":
        // 1) если есть один чип - он будет основным
        // 2) если есть несколько чипов - будет основным чип с более поздней датой
        // 3) если нет чипов - основным будет любой другой тип идентификатора с более поздней датой

        if ($this->mainIdentificationExists($mainPet->id)) {
            return;
        }

        /* @var $chip \app\models\db\PetIdentification */
        $chip = PetIdentification::find()
            ->where(['id_pet' => $mainPet->id])
            ->andWhere([
                'id_ident_type' => (new Query())
                    ->select('id')
                    ->from(IdentificationTypes::tableName())
                    ->where(['name' => 'чип']),
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($chip !== null) {
            $chip->main_flag = true;
            if (!$chip->save()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }

            return;
        }

        /* @var $ident \app\models\db\PetIdentification */
        $ident = PetIdentification::find()
            ->where(['id_pet' => $mainPet->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($ident !== null) {
            $ident->main_flag = true;
            if (!$ident->save()) {
                throw new Exception('Ошибка при переносе данных идентификации');
            }
        }
    }

    /**
     * @param \app\models\db\Pets $pet
     * @return array
     */
    private function getVisits(Pets $pet)
    {
        return Visits::find()
            ->joinWith('pets')
            ->where(['visit_pets.id_pet' => $pet->id])
            ->all();
    }

    /**
     * @param \app\models\db\Pets $pet
     * @return \app\models\db\PetIdentification|null
     */
    private function findIdentification(Pets $pet)
    {
        if (empty($pet->pet_identification)) {
            return null;
        }
        $chip = null;
        foreach ($pet->pet_identification as $identification) {
            if ($identification->main_flag === true) {
                return $identification;
            }
            if ($identification->ident_type->name == 'чип') {
                $chip = $identification;
            }
        }
        if ($chip !== null) {
            return $chip;
        }

        return $pet->pet_identification[0];
    }

    /**
     * @param int $id_pet
     * @return bool
     */
    private function mainIdentificationExists($id_pet)
    {
        return PetIdentification::find()
            ->where([
                'id_pet' => $id_pet,
                'main_flag' => true,
            ])
            ->exists();
    }

    /**
     * @param \app\models\db\Pets $mainPet
     * @param \app\models\db\Pets $pet
     */
    private function transferPetRepresentatives(Pets $mainPet, Pets $pet)
    {
        if (empty($pet->pets_to_owner)) {
            return;
        }

        $existingMain = empty($mainPet->pets_to_owner) ? [] : ArrayHelper::index($mainPet->pets_to_owner, 'id_owner');

        $new = [];
        $old = [];
        foreach ($pet->pets_to_owner as $link) {
            if ($link->id_owner_type == 1) {
                continue;
            }
            $old[] = $link->toArray();
            // решили не удалять представителей из дубля, т.к. вызывает ошибку, если не останется ни одного владельца/представителя
            // (new PetToOwnerModel())->delete($link->id);
            if (!array_key_exists($link->id_owner, $existingMain)) {
                $newLink = (new PetToOwnerModel())->create($mainPet->id, $link->id_owner, $link->id_owner_type);
                $new[] = $newLink->toArray();
            }
        }

        if (empty($old)) {
            return;
        }

        $record = new PetsToOwnerTransferLog([
            'id_pet_from' => $pet->id,
            'id_pet_to' => $mainPet->id,
            'links_pet_from' => $old,
            'links_pet_to' => $new,
        ]);

        if (!$record->save()) {
            throw new Exception('Ошибка при переносе данных представителей');
        }

        $pet->refresh();
        $mainPet->refresh();
    }

    /**
     * @param \app\models\db\Pets $mainPet
     * @param \app\models\db\Pets $pet
     */
    private function restorePetRepresentatives(Pets $mainPet, Pets $pet)
    {
        /* @var $record \app\models\db\PetsToOwnerTransferLog */
        $record = PetsToOwnerTransferLog::find()
            ->where([
                'id_pet_from' => $pet->id,
                'id_pet_to' => $mainPet->id,
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($record === null) {
            return;
        }

        foreach ($record->links_pet_from as $item) {
            $exists = PetsToOwner::findOne([
                'id_pet' => $item['id_pet'],
                'id_owner' => $item['id_owner'],
                'id_owner_type' => $item['id_owner_type'],
            ]);
            if ($exists === null) {
                // тут оставляем как есть, восстанавливаем только если отсутствует
                $newLink = (new PetToOwnerModel())->create($item['id_pet'], $item['id_owner'], $item['id_owner_type']);
            }
        }

        if (!empty($record->links_pet_to)) {
            foreach ($record->links_pet_to as $item) {
                $exists = PetsToOwner::findOne(['id' => $item['id']]);
                if ($exists !== null) {
                    (new PetToOwnerModel())->delete($item['id']);
                }
            }
        }

        if (!$record->delete()) {
            throw new Exception('Ошибка при переносе данных представителей');
        }

        $pet->refresh();
        $mainPet->refresh();
    }

    /**
     * @param \app\models\db\Pets $main_pet
     * @param \app\models\db\Pets $duplicate_pet
     */
    private function restorePetAttributes(Pets $main_pet, Pets $duplicate_pet)
    {
        $petHistory = PetsLinkHistory::findOne([
            'id_pet_main' => $main_pet->id,
            'id_pet_duplicate' => $duplicate_pet->id,
            'enabled' => true
        ]);
        if ($petHistory) {
            foreach ($petHistory->values as $field => $value) {
                $main_pet->$field = $value['old_value'];
            }
            if (!$main_pet->save(false)) {
                $this->addErrors($main_pet->getErrors());
                throw new Exception('Ошибка при переносе атрибутов животного');
            }
            $petHistory->enabled = false;
            $petHistory->save();
        }
    }
}
