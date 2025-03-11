<?php

namespace app\modules\v2\modules\pets\models;

use app\models\db\Diseases;
use app\models\db\OrderType;
use app\models\db\OutsideOrg;
use app\models\db\OwnerFeedback;
use app\models\db\PetDehelmintization;
use app\models\db\PetEctoparasites;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\tmc\TmcDrug;
use app\models\db\tmc\TmcVaccine;
use app\models\db\Violation;
use app\models\db\ViolationType;
use app\models\db\ViolationCancellation;
use app\modules\v2\modules\gosvetnadzor\models\ViolationChangeStateModel;
use app\modules\v2\modules\gosvetnadzor\models\ViolationModel;
use yii\base\ModelEvent;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class VaccinationModel
{
    const ATTR_PET_RABIES_VACCINATION = 'pet_rabies_vaccination';
    const ATTR_PET_DEHELMINTIZATION = 'pet_dehelmintization';
    const ATTR_PET_ECTOPARASITES = 'pet_ectoparasites';
    const ATTR_PET_OTHER_VACCINATIONS = 'pet_other_vaccinations';

    /**
     * Возвращает все вакцинации животного
     *
     * @param $id_pet
     * @return array
     * @throws BadRequestHttpException
     */
    public function getAll($id_pet)
    {
        $pet = $this->validatePet($id_pet, false);

        $result = [
            self::ATTR_PET_RABIES_VACCINATION => $this->findPetRabiesVaccinations($id_pet),
            self::ATTR_PET_DEHELMINTIZATION => $this->findPetDehelmintizations($id_pet),
            self::ATTR_PET_ECTOPARASITES => $this->findPetEctoparasites($id_pet),
            self::ATTR_PET_OTHER_VACCINATIONS => $this->findPetOtherVaccinations($id_pet),
        ];

        if ($pet->is_main === true && !empty($pet->duplicates)) {
            $ids = ArrayHelper::getColumn($pet->duplicates, 'id', false);
            $result['duplicates'] = [
                self::ATTR_PET_RABIES_VACCINATION => $this->findPetRabiesVaccinations($ids),
                self::ATTR_PET_DEHELMINTIZATION => $this->findPetDehelmintizations($ids),
                self::ATTR_PET_ECTOPARASITES => $this->findPetEctoparasites($ids),
                self::ATTR_PET_OTHER_VACCINATIONS => $this->findPetOtherVaccinations($ids),
            ];
        }

        foreach ($result['pet_ectoparasites'] as $pet_ectoparasite_drug) {
            // Частный случай отрисовки данных вакцины, заполненных пользователем суперсервиса
            // if($pet_ectoparasite_drug["id_drug"] == null){
            //     $pet_ectoparasite_drug["id_drug"] = 1;
            // }

            if ($pet_ectoparasite_drug["id_organization"] == null) {
                $pet_ectoparasite_drug["id_organization"] = 1;
            }
        }

        //проверяем на наличие и добавляем ссылки guid на файлы из ЦХЭД
        foreach ($result as &$item) {
            if(is_array($item) && count($item) > 0){
                foreach ($item as &$vaccine_or_drug) {
                    if(isset($vaccine_or_drug['id'])){
                        $rows = (new \yii\db\Query())
                            ->select(['hash'])
                            ->from('files')
                            ->where([
                                'entity_id' => $vaccine_or_drug['id'],
                                'entity_type' => 'vac-other-mos-ru',
                            ])
                            ->orWhere([
                                'entity_id' => $vaccine_or_drug['id'],
                                'entity_type' => 'vac-rab-mos-ru',
                            ])
                            ->orWhere([
                                'entity_id' => $vaccine_or_drug['id'],
                                'entity_type' => 'visits-mos-ru',
                            ])
                            ->all();

                        if (count($rows) > 0) {
                            $vaccine_or_drug["guid_file_superservice"] = $rows[0]['hash'];
                        }
                    }
                }
            }

        }
        return $result;
    }

    /**
     * Сохранение
     *
     * @param $id_pet
     * @param $vaccinations
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function save($id_pet, $vaccinations)
    {
        $id_pet = (int)$id_pet;
        $pet = $this->validatePet($id_pet);
        $this->validateVaccinationsArray($vaccinations);

        /*
         * Разбиваем входной массив на два:
         * 1. Те которые трогать не надо - от них только id нужен
         * 2. Отредактированные или обновленные(все равно перезаписывать) - для сохранения
         */
        $separated_by_action_vaccinations = $this->separateVaccinationsByAction($vaccinations);

        PetRabiesVaccination::getDb()->beginTransaction();

        /*
         * Удаляем старые/обновленные
         */
        $this->removeFromTablesDeletedOrEditedVaccinations(
            $id_pet, $separated_by_action_vaccinations
        );

        /*
         * Валидируем и сохраняем новые/отредактированные
         */
        foreach ($separated_by_action_vaccinations['new_or_edited'] as $vaccination_type => $vaccinations_array) {
            foreach ($vaccinations_array as $vaccination) {
                $vaccination = $this->saveVaccination($id_pet, $vaccination_type, $vaccination);
            }
        }

        PetRabiesVaccination::getDb()->transaction->commit();

        // Закрываем нарушения по вакцинации от бешенства, если внесены соответствующие вакцины
        (new ViolationModel())->checkAndCancelPetsRabiesViolation([$id_pet]);

        /*
         * Событие для аудита
         */
        $event = new ModelEvent();
        $pet->trigger(Pets::EVENT_AFTER_SAVE_VACCINATION, $event);
    }

    /**
     * Сохраняем в БД
     *
     * @param $id_pet
     * @param $type
     * @param $vaccination
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveVaccination($id_pet, $type, $vaccination)
    {
        /*
         * Получаем связанную вакцину или препарат
         */
        switch ($type) {
            case self::ATTR_PET_RABIES_VACCINATION:
            case self::ATTR_PET_OTHER_VACCINATIONS:
                if (empty($vaccination['id_vaccine'])) {
                    throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат - не указан id_vaccine');
                }
                $drug_or_vaccine = $this->findVaccine($vaccination['id_vaccine']);
                break;
            case self::ATTR_PET_DEHELMINTIZATION:
            case self::ATTR_PET_ECTOPARASITES:
                if (empty($vaccination['id_drug'])) {
                    throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат - не указан id_drug');
                }
                $drug_or_vaccine = $this->findDrug($vaccination['id_drug']);
                break;
            default:
                throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат');
        }

        /*
         * Создаем новую запись на вставку
         */
        switch ($type) {
            case self::ATTR_PET_RABIES_VACCINATION:
                $record = new PetRabiesVaccination($vaccination);
                break;

            case self::ATTR_PET_DEHELMINTIZATION:
                $record = new PetDehelmintization($vaccination);
                break;

            case self::ATTR_PET_ECTOPARASITES:
                $record = new PetEctoparasites($vaccination);
                break;

            case self::ATTR_PET_OTHER_VACCINATIONS:
                $record = new PetOtherVaccinations($vaccination);
                break;

            default:
                throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат');
        }

        $specialist = $this->getCurrentSpecialist();

        $record->id_pet = $id_pet;
        $record->id_specialist = $specialist->id;
        $record->id_organization = $record->id_organization ?? $specialist->id_organization;
        $record->type_tmc = $drug_or_vaccine->type;
        $record->drug_name = $drug_or_vaccine->name;
        $record->producer_name = $drug_or_vaccine->produced;

        if (!$record->save()) {
            // Откатываем изменения
            if (PetRabiesVaccination::getDb()->transaction->isActive) {
                PetRabiesVaccination::getDb()->transaction->rollBack();
            }

            $errors = $record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении ' : implode("\n", array_values($errors)));
        }

        return $record;
    }

    /**
     * Создание записи вакцинации по обработаному инспектором OwnerFeedback
     *
     * @param $id_pet
     * @param $ownerFeedback OwnerFeedback
     * @param $type
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function createByOwnerFeedback($id_pet, OwnerFeedback $ownerFeedback, $type)
    {
        if (empty($ownerFeedback->id_tmc)) {
            throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат - не указан id_vaccine');
        }
        $drug_or_vaccine = $this->findVaccine($ownerFeedback->id_tmc);

        switch ($type) {
            case self::ATTR_PET_RABIES_VACCINATION:
                $record = new PetRabiesVaccination();
                break;

            case self::ATTR_PET_OTHER_VACCINATIONS:
                $record = new PetOtherVaccinations();
                break;

            default:
                throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат');
        }

        $specialist = $this->getCurrentSpecialist();

        $record->id_pet = $id_pet;
        $record->id_specialist = $specialist->id;
        $record->id_organization = $ownerFeedback->id_organization;
        $record->is_out_org = $ownerFeedback->is_out_org;
        $record->type_tmc = $drug_or_vaccine->type;
        $record->drug_name = $drug_or_vaccine->name;
        $record->producer_name = $drug_or_vaccine->produced;
        $record->date = $ownerFeedback->vaccine_date;
        $record->id_vaccine = $ownerFeedback->id_tmc;
        $record->batch = $ownerFeedback->batch;
        $record->production_date = $ownerFeedback->production_date;
        $record->expiry_date = $ownerFeedback->expiry_date;
        $record->valid_until = $ownerFeedback->valid_until;

        if (!$record->save()) {
            // Откатываем изменения
//            if (PetRabiesVaccination::getDb()->transaction->isActive) {
//                PetRabiesVaccination::getDb()->transaction->rollBack();
//            }

            $errors = $record->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении ' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Удаляем из таблиц все по этому животному кроме нетронутых записей
     *
     * @param $id_pet
     * @param $separated_by_action_vaccinations
     */
    protected function removeFromTablesDeletedOrEditedVaccinations($id_pet, $separated_by_action_vaccinations)
    {
        $do_not_touch = $separated_by_action_vaccinations['do_not_touch'];

        /*
         * PetRabiesVaccination
         */
        $condition_RABVAC = (!empty($do_not_touch[self::ATTR_PET_RABIES_VACCINATION])) ?
            [
                'AND',
                ['id_pet' => $id_pet],
                ['NOT IN', 'id', $do_not_touch[self::ATTR_PET_RABIES_VACCINATION]]
            ] : [
                'id_pet' => $id_pet
            ];

        PetRabiesVaccination::deleteAll($condition_RABVAC);

        /*
         * PetDehelmintization
         */
        $condition_DEHEL = (!empty($do_not_touch[self::ATTR_PET_DEHELMINTIZATION])) ?
            [
                'AND',
                ['id_pet' => $id_pet],
                ['NOT IN', 'id', $do_not_touch[self::ATTR_PET_DEHELMINTIZATION]]
            ] : [
                'id_pet' => $id_pet
            ];

        PetDehelmintization::deleteAll($condition_DEHEL);

        /*
         * PetEctoparasites
         */
        $condition_ECTOP = (!empty($do_not_touch[self::ATTR_PET_ECTOPARASITES])) ?
            [
                'AND',
                ['id_pet' => $id_pet],
                ['NOT IN', 'id', $do_not_touch[self::ATTR_PET_ECTOPARASITES]]
            ] : [
                'id_pet' => $id_pet
            ];

        PetEctoparasites::deleteAll($condition_ECTOP);

        /*
         * PetOtherVaccinations
         */
        $condition_OTHVAC = (!empty($do_not_touch[self::ATTR_PET_OTHER_VACCINATIONS])) ?
            [
                'AND',
                ['id_pet' => $id_pet],
                ['NOT IN', 'id', $do_not_touch[self::ATTR_PET_OTHER_VACCINATIONS]]
            ] : [
                'id_pet' => $id_pet
            ];

        PetOtherVaccinations::deleteAll($condition_OTHVAC);
    }

    /**
     * Разбивает входной массив $vaccinations на два с такой же структурой
     * В первом, do_not_touch, будут ТОЛЬКО id от тех записей, которые удалять в бд не надо
     * Во втором, new_or_edited, будут измененные/новые записи целиком
     *
     * @param $vaccinations
     * @return array
     * @throws BadRequestHttpException
     */
    protected function separateVaccinationsByAction($vaccinations)
    {
        /*
         * По уговору с фронтом в ДАННОМ методе с id будут приходить только не измененные вакцины
         * Остальные - новые или отредактированные
         */

        $result = ['do_not_touch' => [],
            'new_or_edited' => [],];

        foreach ($vaccinations as $vaccination_type => $vaccinations_array) {
            foreach ($vaccinations_array as $vaccination) {
                if (array_key_exists('id', $vaccination) && !is_numeric($vaccination['id'])) {
                    throw new BadRequestHttpException('id вакцины/препарата должен быть числом');
                } elseif
                (array_key_exists('id', $vaccination)) {
                    $result['do_not_touch'][$vaccination_type][] = $vaccination['id'];
                } else {
                    $result['new_or_edited'][$vaccination_type][] = $vaccination;
                }
            }
        }

        return $result;
    }

    /**
     * Валидирует структуру массива $vaccinations
     *
     * @param $vaccinations
     * @throws BadRequestHttpException
     */
    protected function validateVaccinationsArray($vaccinations)
    {
        if (!is_array($vaccinations)) {
            throw new BadRequestHttpException('Параметр vaccinations должен быть массивом');
        }

        if (count(array_keys($vaccinations)) != 4) {
            throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат');
        }

        if (
            !array_key_exists(self::ATTR_PET_RABIES_VACCINATION, $vaccinations) ||
            !array_key_exists(self::ATTR_PET_DEHELMINTIZATION, $vaccinations) ||
            !array_key_exists(self::ATTR_PET_ECTOPARASITES, $vaccinations) ||
            !array_key_exists(self::ATTR_PET_OTHER_VACCINATIONS, $vaccinations)
        ) {
            throw new BadRequestHttpException('Параметр vaccinations имеет ошибочный формат');
        }
    }

    /**
     * Возвращает животное или генерирует ошибку
     * ЕСЛИ оно не найдено или ЕСЛИ оно не подлежит редактированию
     *
     * @param $id_pet
     * @param $check_read_only
     * @return Pets|null
     * @throws BadRequestHttpException
     */
    protected function validatePet($id_pet, $check_read_only = true)
    {
        if (!self::isIdPetValid($id_pet)) {
            throw new BadRequestHttpException('id_pet имеет неверный формат');
        }

        $pet = Pets::findOne(['id' => $id_pet]);

        if (empty($pet)) {
            throw new BadRequestHttpException('Указанное животное не найдено');
        }

        if ($check_read_only && $pet->isReadOnly()) {
            throw new BadRequestHttpException('Снятое с учета животное не подлежит редактированию');
        }

        return $pet;
    }

    /**
     * Проверяет, является ли переданное значение корректным ID животного.
     * - целое число (или его строковое представление)
     * - больше 0
     * - меньше 2147483647 (PostgreSQL INTEGER)
     *
     * @param int $id_pet
     * @return boolean
     */
    public static function isIdPetValid($id_pet)
    {
        if (empty($id_pet) || !(is_int($id_pet) || is_string($id_pet))) {
            return false;
        }

        $int_casted = (int)$id_pet;
        if (is_string($id_pet) && (string)$int_casted !== $id_pet) {
            return false;
        }

        if ($int_casted > 2147483647) { // PSQL INTEGER	4 bytes
            return false;
        }

        return true;
    }

    /**
     * Находит и возвращает вакцину или генерирует ошибку если не найдена
     *
     * @param $id
     * @return TmcVaccine
     * @throws BadRequestHttpException
     */
    protected function findVaccine($id)
    {
        if (empty($id) || !is_numeric($id)) {
            throw new BadRequestHttpException('id_vaccine имеет неверный формат');
        }

        $vaccine = TmcVaccine::findOne(['id' => $id]);

        if (empty($vaccine)) {
            throw new BadRequestHttpException('Вакцина c id=' . $id . ' не найдена');
        }

        return $vaccine;
    }

    /**
     * Находит и возвращает препарат или генерирует ошибку если не найден
     *
     * @param $id
     * @return TmcDrug
     * @throws BadRequestHttpException
     */
    protected function findDrug($id)
    {
        if (empty($id) || !is_numeric($id)) {
            throw new BadRequestHttpException('id_drug имеет неверный формат');
        }

        $drug = TmcDrug::findOne(['id' => $id]);

        if (empty($drug)) {
            throw new BadRequestHttpException('Препарат c id=' . $id . ' не найден');
        }

        return $drug;
    }

    /**
     * Возвращает текущего спеца
     *
     * @return \app\models\db\Specialists
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function getCurrentSpecialist()
    {
        /** @var \app\common\models\UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        if (empty($user->specialist)) {
            throw new ForbiddenHttpException('Недостаточно прав доступа');
        }

        return $user->specialist;
    }

    /**
     * @param int|int[] $id_pet
     * @return \app\models\db\PetRabiesVaccination[]
     */
    private function findPetRabiesVaccinations($id_pet)
    {
        $pet_rabies_vaccination = PetRabiesVaccination::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $pet_rabies_vaccination;
    }

    /**
     * @param int|int[] $id_pet
     * @return \app\models\db\PetDehelmintization[]
     */
    private function findPetDehelmintizations($id_pet)
    {
        $pet_dehelmintization = PetDehelmintization::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $pet_dehelmintization;
    }

    /**
     * @param int|int[] $id_pet
     * @return \app\models\db\PetEctoparasites[]
     */
    private function findPetEctoparasites($id_pet)
    {
        $pet_ectoparasites = PetEctoparasites::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $pet_ectoparasites;
    }

    /**
     * @param int|int[] $id_pet
     * @return \app\models\db\PetOtherVaccinations[]
     */
    private function findPetOtherVaccinations($id_pet)
    {
        $pet_other_vaccinations = PetOtherVaccinations::find()
            ->where(['id_pet' => $id_pet])
            ->orderBy('id')
            ->asArray()
            ->all();

        return $pet_other_vaccinations;
    }

    /**
     * Возвращает выборку объектов животных с вложенными объектами вакцинаций и дублей
     * (также с вложенными объектами вакцинаций) по переданному(ным) ID.
     * Возвращаемый массив имиеет следующую структуру:
     * ```php
     * [
     *     '403918' => [ // <- ID животного
     *         'id' => 403918,
     *         'pet_rabies_vaccinations' => [
     *             [
     *                 // ... объект вакцинации
     *             ],
     *             // ...
     *         ],
     *         'pet_dehelmintizations' => [], // ...
     *         'pet_ectoparasites' => [], // ...
     *         'pet_other_vaccinations' => [], // ...
     *         'duplicates' => [ // <- при запросе дублей ($withDuplicates == true)
     *             '403919' => [ // <- ID животного
     *                 'id' => 403919,
     *                 'id_main_pet' => 403918,
     *                 // ... объекты вакцинаций в точности как для объектов животных верхнего уровня
     *             ]
     *         ]
     *     ],
     *     // ...
     * ];
     * ```
     *
     * @param int|int[] $id_pet
     * @param boolean $withDuplicates Включить в ответ данные по дублям животных
     * @return array см. структуру в описании
     */
    public static function getAllByIdPet($id_pet = [], $withDuplicates = true)
    {
        if (empty($id_pet)) {
            throw new \BadMethodCallException('$id_pet не может быть пустым');
        }

        $vaccinationsRelations = [
            'pet_rabies_vaccinations',
            'pet_dehelmintizations',
            'pet_ectoparasites',
            'pet_other_vaccinations',
        ];

        $withRelations = $vaccinationsRelations;
        if ($withDuplicates) {
            $withRelations += [
                'duplicates' => function (\yii\db\ActiveQuery $q) use ($vaccinationsRelations) {
                    $q
                        ->select([
                            'id',
                            'id_main_pet',
                        ])
                        ->indexBy('id')
                        ->with($vaccinationsRelations);
                }
            ];
        }

        $pets = Pets::find()
            ->select([
                'id',
            ])
            ->indexBy('id')
            ->with($withRelations)
            ->where(['id' => $id_pet])
            ->asArray()
            ->all();

        if (!$withDuplicates) {
            return $pets;
        }

        // Сгруппируем все вакцинации дублей
        foreach ($pets as &$pet) {
            $petDupesVaccinations = array_fill_keys($vaccinationsRelations, []);

            foreach ($pet['duplicates'] as $dupeId => &$duplicate) {
                foreach ($vaccinationsRelations as $relName) {
                    $petDupesVaccinations[$relName] = array_merge(
                        $petDupesVaccinations[$relName],
                        $duplicate[$relName]
                    );
                }

                // unset ($duplicate);
                unset ($pet['duplicates'][$dupeId]);
            }

            $pet['duplicates'] += $petDupesVaccinations;
        }

        return $pets;
    }
}
