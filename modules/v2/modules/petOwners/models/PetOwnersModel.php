<?php

namespace app\modules\v2\modules\petOwners\models;

use app\common\validators\FullTrimValidator;
use app\common\validators\PGFilterEscapesValidator;
use app\common\validators\PGFilterQuotesValidator;
use app\models\db\Brood;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\FiasAddresses;
use app\models\db\PetOwners;
use app\models\db\PetOwnersLinkHistory;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Species;
use app\modules\v2\modules\petOwners\skeletons\petOwners\PetOwnersList;
use yii\db\Exception;
use function GuzzleHttp\Psr7\str;
use SebastianBergmann\CodeCoverage\Report\PHP;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class PetOwnersModel
 * @package app\modules\v2\modules\petOwners\models
 */
class PetOwnersModel extends Model
{
    /**
     * Возвращает владельца по id
     *
     * @param int $id
     * @param bool $withDuplicates
     * @param bool $asArray
     * @param bool $petsCount
     * @return array|\app\models\db\PetOwners
     */
    public function getPetOwner($id, $withDuplicates = false, $asArray = true, $petsCount = false, bool $isDeleted = null)
    {
        $query = PetOwners::find()
            ->where(['pet_owners.id' => $id])
            ->with('contacts')
            ->with('agreement_pers')
            ->with('files')
            ->asArray($asArray)
            ->select([
                'pet_owners.*',
                // Подменяем имя Владельца для данных неавторизованного пользователя mosru
                new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.f_fio ELSE pet_owners.f_fio END AS "f_fio"'),
                new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.i_fio ELSE pet_owners.i_fio END AS "i_fio"'),
                new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.o_fio ELSE pet_owners.o_fio END AS "o_fio"'),
                new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.fullname ELSE pet_owners.fullname END AS "fullname"'),
            ])
            ->joinWith(['tmpOwner' => function ($query) {
                /** @var ActiveQuery $query */
                $query->alias('tmpo');
            }]);

        if (!$asArray) {
            // из-за массива в bti_city_area_code придется выбирать отдельно объектами
            $query
                ->with('fact_fias_addresses')
                ->with('fias_addresses');
        }

        if ($petsCount === true) {
            $query
                ->addSelect(new Expression('COALESCE(pc.pets_count, 0) AS pets_count'))
                ->leftJoin(
                    ['pc' => $this->getPetsCountSubQuery()],
                    'pet_owners.id = pc.id_owner'
                );
        }

        if ($withDuplicates === true) {
            if (is_null($isDeleted))
                $is_deleted = ['in', 'is_deleted', [true, false]];
            else
                $is_deleted = ['is_deleted' => $isDeleted];
            $query->with(['duplicates' => function ($q) use ($asArray, $petsCount, $is_deleted) {
                /* @var $q \yii\db\ActiveQuery */
                $q->with('fact_fias_addresses')
                    ->where($is_deleted)
                    ->with('fias_addresses')
                    ->with(['contacts' => function ($q2) {
                        $q2->with('contact_type');
                    }])
                    ->asArray($asArray);
                if ($petsCount === true) {
                    $q->select('*')
                        ->addSelect(new Expression('COALESCE(pc.pets_count, 0) AS pets_count'))
                        ->leftJoin(
                            ['pc' => $this->getPetsCountSubQuery()],
                            'pet_owners.id = pc.id_owner'
                        );
                }
            }]);
            $query->with(['contacts' => function ($q3) {
                $q3->with('contact_type');
            }]);
        }

        $result = $query->one();

        if ($asArray) {
            // из-за массива в bti_city_area_code придется выбирать отдельно объектами
            $result['fias_addresses'] = !empty($result['id_fias_address']) ?
                FiasAddresses::findOne(['id' => $result['id_fias_address']]) : null;

            $result['fact_fias_addresses'] = !empty($result['id_fact_fias_address']) ?
                FiasAddresses::findOne(['id' => $result['id_fact_fias_address']]) : null;
        }

        return $result;
    }

    /**
     * Создание владельца/представителя
     *
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $jur_name
     * @param string $inn
     * @param string $ogrn
     * @param string $birthday
     * @param string $snils
     * @param string $passport
     * @param bool $is_legal
     * @param int $id_area
     * @param int $id_district
     * @param string $fias_address
     * @param string $fact_fias_address
     * @param bool $entrepreneur
     * @param string $description
     * @param bool $addresses_is_equal
     * @return PetOwners
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function create(
        $f_fio,
        $i_fio,
        $o_fio,
        $jur_name,
        $inn,
        $ogrn,
        $birthday,
        $snils,
        $is_legal,
        $id_area,
        $id_district,
        $fias_address,
        $fact_fias_address,
        $entrepreneur,
        $description,
        $addresses_is_equal,
        $passport_number,
        $passport_series,
        $passport_issue_date,
        $passport_issuer
    ) {
        $pet_owner = new PetOwners();

        $pet_owner->f_fio = $f_fio;
        $pet_owner->i_fio = $i_fio;
        $pet_owner->o_fio = $o_fio;
        $pet_owner->jur_name = $jur_name;
        $pet_owner->inn = $inn;
        $pet_owner->ogrn = $ogrn;
        $pet_owner->birthday = $birthday;
        $pet_owner->snils = $snils;
        $pet_owner->is_legal = $is_legal;
        $pet_owner->id_area = $id_area;
        $pet_owner->id_district = $id_district;
        $pet_owner->entrepreneur = $entrepreneur;
        $pet_owner->description = $description;
        $pet_owner->addresses_is_equal = $addresses_is_equal;
        $pet_owner->passport_number = $passport_number;
        $pet_owner->passport_series = $passport_series;
        $pet_owner->passport_issue_date = $passport_issue_date;
        $pet_owner->passport_issuer = $passport_issuer;

        PetOwners::getDb()->beginTransaction();

        // fias_address
        if (!empty($fias_address)) {
            if ($addresses_is_equal) {
                $fact_fias_address = $fias_address;
            }

            $pet_owner->id_fias_address = FiasAddresses::findOrCreateFiasAddress($fias_address);
            if (!$pet_owner->id_area) {
                $pet_owner->id_area = FiasAddresses::findOne($pet_owner->id_fias_address)->id_area;
            }
            if (!$pet_owner->id_district) {
                $pet_owner->id_district = FiasAddresses::findOne($pet_owner->id_fias_address)->id_district;
            }
        } else {
            $pet_owner->id_fias_address = null;
        }

        // fact_fias_address
        if (!empty($fact_fias_address)) {
            $pet_owner->id_fact_fias_address = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);
            if (!$pet_owner->id_area) {
                $pet_owner->id_area = FiasAddresses::findOne($pet_owner->id_fact_fias_address)->id_area;
            }
            if (!$pet_owner->id_district) {
                $pet_owner->id_district = FiasAddresses::findOne($pet_owner->id_fact_fias_address)->id_district;
            }
        } else {
            $pet_owner->id_fact_fias_address = null;
        }

        if (!$pet_owner->save()) {

            $errors = $pet_owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании владельца/представителя' : implode("\n", array_values($errors)));
        }

        $this->updateFiasAddressForPets($pet_owner);

        PetOwners::getDb()->transaction->commit();

        return $pet_owner;
    }

    /**
     * Редактирование владельца/представителя
     *
     * @param int $id
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $jur_name
     * @param string $inn
     * @param string $ogrn
     * @param string $birthday
     * @param string $snils
     * @param bool $is_legal
     * @param int $id_area
     * @param int $id_district
     * @param string $fias_address
     * @param string $fact_fias_address
     * @param int $entrepreneur
     * @param string $description
     * @param bool $addresses_is_equal
     * @return PetOwners|null
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function edit(
        $id,
        $f_fio,
        $i_fio,
        $o_fio,
        $jur_name,
        $inn,
        $ogrn,
        $birthday,
        $snils,
        $is_legal,
        $id_area,
        $id_district,
        $fias_address,
        $fact_fias_address,
        $entrepreneur,
        $description,
        $addresses_is_equal,
        $passport_number,
        $passport_series,
        $passport_issue_date,
        $passport_issuer
    ) {
        $pet_owner = $this->findPetOwner($id);

        $pet_owner->f_fio = $f_fio;
        $pet_owner->i_fio = $i_fio;
        $pet_owner->o_fio = $o_fio;
        $pet_owner->jur_name = $jur_name;
        $pet_owner->inn = $inn;
        $pet_owner->ogrn = $ogrn;
        $pet_owner->birthday = $birthday;
        $pet_owner->snils = $snils;
        $pet_owner->is_legal = $is_legal;
        $pet_owner->id_area = $id_area;
        $pet_owner->id_district = $id_district;
        $pet_owner->entrepreneur = $entrepreneur;
        $pet_owner->description = $description;
        $pet_owner->addresses_is_equal = $addresses_is_equal;
        $pet_owner->passport_number = $passport_number;
        $pet_owner->passport_series = $passport_series;
        $pet_owner->passport_issue_date = $passport_issue_date;
        $pet_owner->passport_issuer = $passport_issuer;

        PetOwners::getDb()->beginTransaction();

        // fias_address
        if (!empty($fias_address)) {
            if ($addresses_is_equal) {
                $fact_fias_address = $fias_address;
            }

            $pet_owner->id_fias_address = FiasAddresses::findOrCreateFiasAddress($fias_address);
            if (!$pet_owner->id_area) {
                $pet_owner->id_area = FiasAddresses::findOne($pet_owner->id_fias_address)->id_area;
            }
            if (!$pet_owner->id_district) {
                $pet_owner->id_district = FiasAddresses::findOne($pet_owner->id_fias_address)->id_district;
            }
        } else {
            $pet_owner->id_fias_address = null;
        }

        // fact_fias_address
        if (!empty($fact_fias_address)) {
            $pet_owner->id_fact_fias_address = FiasAddresses::findOrCreateFiasAddress($fact_fias_address);
            if (!$pet_owner->id_area) {
                $pet_owner->id_area = FiasAddresses::findOne($pet_owner->id_fact_fias_address)->id_area;
            }
            if (!$pet_owner->id_district) {
                $pet_owner->id_district = FiasAddresses::findOne($pet_owner->id_fact_fias_address)->id_district;
            }
        } else {
            $pet_owner->id_fact_fias_address = null;
        }


        if (!$pet_owner->save()) {
            $errors = $pet_owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании владельца/представителя' : implode("\n", array_values($errors)));
        }

        $this->updateFiasAddressForPets($pet_owner);

        PetOwners::getDb()->transaction->commit();
        //file_put_contents('11.txt', $query->createCommand()->getRawSql(), FILE_APPEND);
        // file_put_contents('11.txt', $pet_owner, FILE_APPEND);
        return $pet_owner;
    }

    /**
     * Удаление владельца
     *
     * @param int $id
     * @throws BadRequestHttpException
     */
    public function delete($id)
    {
        /**
         * Мы можем помечать владельца как удаленного
         * только если у него нет животных или они все они сняты с учета
         */

        $check = PetsToOwner::find()
            ->joinWith('pet')
            ->where([
                'AND',
                ['pets_to_owner.id_owner' => $id],
                [
                    'OR',
                    ['pets.id_reg_expire_reason' => null],
                    ['pets.reg_expire_date' => null],
                ],
            ])->exists();

        if ($check) {
            throw new BadRequestHttpException('Нельзя удалять владельца/представителя');
        }

        $pet_owner = $this->findPetOwner($id);

        $pet_owner->is_deleted = true;

        if (!$pet_owner->save()) {
            $errors = $pet_owner->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении владельца/представителя' : implode("\n", array_values($errors)));
        }
    }

    /**
     * Поиск владельца
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return PetOwnersList
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */


    public function listPetOwners1($page, $limit, $filter = null, $isAsc, $typeQwery)
    {
        $filter = $this->validateFilter($filter);

        $query = PetOwners::find()
            ->select([
                'pet_owners.*',
                'pc.pets_count',
                "public.fias_addresses.full_address AS pet_owner_adress"
            ])
            ->with(['fias_addresses' => function ($fias_query) {
                /** @var ActiveQuery $fias_query * */
                $fias_query
                    ->select(['id', 'full_address']);
            }])
            ->leftJoin(
                ['pc' => $this->getPetsCountSubQuery()],
                'pet_owners.id = pc.id_owner'
            )
            ->leftJoin("public.fias_addresses", "public.pet_owners.id_fias_address=public.fias_addresses.id")


            // ->orderBy([new Expression("NULLIF(pet_owners.fullname, '') {$isAsc} NULLS LAST")])
            // ->orderBy([new Expression("pc.pets_count {$isAsc} NULLS LAST")]);
            // ->orderBy([new Expression("pc.pets_count {$isAsc} NULLS LAST")]);
            // ->orderBy([new Expression("pet_owner_adress {$isAsc} NULLS LAST")]);
            ->orderBy([new Expression("{$typeQwery} {$isAsc} NULLS LAST")])
            ->where(['id_pet_owner_tmp' => null]);

        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetOwnersList(
            $query
                ->orderBy([
                    'pet_owners.fullname' => SORT_ASC,
                    'pet_owners.jur_name' => SORT_ASC,
                ])
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query->count()
        );

        $result->customPagination($page, $limit);

        return $result;
    }


    public function listPetOwners($page, $limit, $filter = null)
    {
        $filter = $this->validateFilter($filter);

        $query = PetOwners::find()
            ->select([
                'pet_owners.*',
                'pc.pets_count',
            ])
            ->with(['fias_addresses' => function ($fias_query) {
                /** @var ActiveQuery $fias_query * */
                $fias_query
                    ->select(['id', 'full_address']);
            }])
            ->with(['phoneContacts' => function ($phoneContacts) {
                /** @var ActiveQuery $phoneContacts * */
                $phoneContacts
                    ->select(['*']);
            }])
            ->leftJoin(
                ['pc' => $this->getPetsCountSubQuery()],
                'pet_owners.id = pc.id_owner'
            )
            ->where(['pet_owners.id_pet_owner_tmp' => null]);
        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetOwnersList(
            $query
                ->orderBy([
                    'pet_owners.fullname' => SORT_ASC,
                    'pet_owners.jur_name' => SORT_ASC,
                ])
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query->count()
        );

        $result->customPagination($page, $limit);

        return $result;
    }

    public function listPetOwnersWithPets($page, $limit, $filter = null, $typeQwery)
    {
        $filter = $this->validateFilter($filter);

        $query = PetOwners::find()
            ->select([
                'pet_owners.*',
                "public.fias_addresses.full_address AS pet_owner_address",
                "phones.name AS pet_owner_phone"
            ])
            ->with(['fias_addresses' => function ($fias_query) {
                /** @var ActiveQuery $fias_query * */
                $fias_query
                    ->select(['id', 'full_address']);
            }])
            ->with(['pets' => function ($pets) {
                /** @var ActiveQuery $fias_query * */
                $pets
                    ->select(['pets.id', new Expression('pets.name || \', \' || COALESCE(public.species.name, \'\') || \', \' || CASE WHEN pets.sex = \'m\' THEN \'мужской\' WHEN pets.sex = \'f\' THEN \'женский\' END || \', \' || COALESCE(public.breeds.name, \'\') AS pets_info')])
                    ->leftJoin("public.species", "pets.id_species =  species.id")
                    ->leftJoin("public.breeds", "pets.id_breed=breeds.id");
            }])
            ->leftJoin("public.fias_addresses", "public.pet_owners.id_fact_fias_address=public.fias_addresses.id")
            ->leftJoin(
                ['phones' => new Expression('(select ph.name, ph.entity_id
                    from contacts ph 
                    join contact_types phone_types on ph.id_contact_type = phone_types.id AND phone_types.type = \'phone\' 
                    where ph.entity_type = \'pet_owner\')')],
                'pet_owners.id = phones.entity_id'
            )
            ->where(['id_pet_owner_tmp' => null]);

        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetOwnersList(
            $query
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query->count()
        );

        $result->customPagination($page, $limit);

        return $result;
    }

    /**
     * Возвращает подзапрос на выборку кол-ва животных у владельца
     * для v2/pet-owners/pet-owner/list
     *
     * @return Query
     */
    protected function getPetsCountSubQuery()
    {
        return (new Query())
            ->select([
                new Expression('count(*) AS pets_count'),
                'pto.id_owner',
            ])
            ->from('pets')
            ->leftJoin('pets_to_owner AS pto', 'pto.id_pet = pets.id')
            ->where([
                'AND',
                ['pets.id_reg_expire_reason' => null],
                ['pets.reg_expire_date' => null],
                [
                    'or',
                    ['pets.is_main' => true],
                    ['pets.is_main' => null]
                ]
            ])
            ->groupBy('pto.id_owner');
    }


    /**
     * Применяет фильтры для v2/pet-owners/pet-owner/list и джоинит нужные таблицы
     *
     * @param array $filter
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    protected function applyFilter($filter, $query)
    {
        if (empty($filter)) {
            $query->andWhere([
                'or',
                ['pet_owners.is_main' => true],
                ['pet_owners.is_main' => null],
            ]);

            return $query;
        }

        // при необходимости исключаем владельцев с определенными ID (нужно при склейке)
        if (!empty($filter['except'])) {
            $query->andWhere(['not in', 'pet_owners.id', $filter['except']]);
        }

        //        /**
        //         * Фильтрация по адресу владельца
        //         * Нужна таблица fias_addresses
        //         */
        //        if (!empty($filter['address']) ) {
        //            $query->innerJoin('fias_addresses', 'pet_owners.id_fact_fias_address = fias_addresses.id');
        //            $query->andWhere(['LIKE', 'fias_addresses.full_address', '% ' . $filter['address'] . '%', false]);
        //        }

        /**
         * Фильтрация по адресу владельца
         * Нужна таблица fias_addresses
         */
        if (
            !empty($filter['houseGUID']) || !empty($filter['regionGUID']) || !empty($filter['streetGUID'])
            || !empty($filter['cityGUID']) || !empty($filter['roomGUID']) || !empty($filter['address'])
        ) {
            $query->innerJoin('fias_addresses', 'pet_owners.id_fact_fias_address = fias_addresses.id');
            if (!empty($filter['regionGUID'])) {
                $query->andWhere(['fias_addresses.regionguid' => $filter['regionGUID']]);
            }
            if (!empty($filter['streetGUID'])) {
                $query->andWhere(['fias_addresses.streetguid' => $filter['streetGUID']]);
            }
            if (!empty($filter['cityGUID'])) {
                $query->andWhere(['fias_addresses.cityguid' => $filter['cityGUID']]);
            }
            if (!empty($filter['houseGUID'])) {
                $query->andWhere(['fias_addresses.houseguid' => $filter['houseGUID']]);
            }
            if (!empty($filter['roomGUID'])) {
                $query->andWhere(['fias_addresses.roomguid' => $filter['roomGUID']]);
            }
            if (!empty($filter['address'])) {
                if (!function_exists('mb_ucfirst')) {
                    function mb_ucfirst($string, $enc = 'UTF-8')
                    {
                        return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) .
                            mb_substr($string, 1, mb_strlen($string, $enc), $enc);
                    }
                }
                $new_address = str_replace('Дом', ' Дом', $filter['address']);

                // перевод первых букв в словах в адресе в верхний регистр
                $input = $filter['address'];
                $parts = explode(' ', $input); // Разделяем строку на массив частей по пробелам
                $all_upper_address = [];
                foreach ($parts as $part) {
                    $uppercasedPart = mb_ucfirst($part); // Преобразуем первую часть в верхний регистр
                    $all_upper_address[] = $uppercasedPart;
                }
                $all_upper_address = implode(' ', $all_upper_address);

                $query->andWhere(
                    [
                        'OR',
                        ['LIKE', 'fias_addresses.full_address', '%' . $new_address . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . $filter['address'] . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . mb_ucfirst($filter['address']) . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . preg_quote($new_address) . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . preg_quote($filter['address']) . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . mb_ucfirst(preg_quote($filter['address'])) . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . preg_quote($new_address) . '%', false],
                        ['LIKE', 'fias_addresses.full_address', '%' . $all_upper_address . '%', false],
                    ]
                );
            }
        }

        if (!empty($filter['ogrn'])) {
            $query->andWhere(['pet_owners.ogrn' => $filter['ogrn']]);
        }

        if (!empty($filter['pers_data'])) {

            // Да
            if ($filter['pers_data'] == 2) {
                $query->leftJoin('files', 'pet_owners.id = files.entity_id');
                $query->andWhere(['files.entity_type' => 'owner_agreement']);
            }

            // Нет
            if ($filter['pers_data'] == 3) {
                // Подзапрос для получения идентификаторов владельцев с entity_type = owner_agreement
                $excludedIdsSubquery = (new \yii\db\Query())
                    ->select('pet_owners.id')
                    ->from('pet_owners')
                    ->leftJoin('files', 'pet_owners.id = files.entity_id')
                    ->where(['files.entity_type' => 'owner_agreement']);
                $excludedIds = $excludedIdsSubquery->column();

                // Добавляем условие NOT IN к основному запросу
                $query->andWhere(['not in', 'pet_owners.id', $excludedIds]);
            }
        }

        if (!empty($filter['snils'])) {
            $query->leftJoin('pet_owners as po2', 'pet_owners.id = po2.id_main_owner');
            $query->andWhere([
                'or',
                ['pet_owners.snils' => $filter['snils']],
                ['po2.snils' => $filter['snils']]
            ]);
        }

        if (!empty($filter['inn'])) {
            $query->andWhere(['pet_owners.inn' => $filter['inn']]);
        }

        if (!empty($filter['id_area'])) {
            $query->andWhere(['pet_owners.id_area' => $filter['id_area']]);
        }

        if (!empty($filter['id_district'])) {
            $query->andWhere(['pet_owners.id_district' => $filter['id_district']]);
        }

        if (array_key_exists('is_legal', $filter) && $filter['is_legal'] !== null) {
            $query->andWhere(['pet_owners.is_legal' => $filter['is_legal']]);
        }

        if (array_key_exists('entrepreneur', $filter) && $filter['entrepreneur'] !== null) {
            $query->andWhere(['pet_owners.entrepreneur' => $filter['entrepreneur']]);
        }

        if (!empty($filter['name'])) {
            $filter['name'] = trim($filter['name']);
            // Проверяем наличие буквы "ё" и заменяем её на "е"
            if (strpos($filter['name'], 'ё') !== false) {
                $filtered_name = str_replace('ё', 'е', $filter['name']);
            }

            if (isset($filtered_name)) {
                // Проводим поиск с измененной строкой
                $query->andWhere([
                    'OR',
                    ['ILIKE', 'pet_owners.fullname', '%' . $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.jur_name', '%' . $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.fullname', '%' . $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.jur_name', '%' . $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.fullname', $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.jur_name', $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.fullname', $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.jur_name', $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.f_fio', '%' . $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.i_fio', '%' . $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.o_fio', '%' . $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.f_fio', $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.i_fio', $filtered_name . '%', false],
                    ['ILIKE', 'pet_owners.o_fio', $filter['name'] . '%', false],
                ]);
            } else {
                $query->andWhere([
                    'OR',
                    ['ILIKE', 'pet_owners.fullname', '%' . $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.jur_name', '%' . $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.fullname', $filter['name'] . '%', false],
                    ['ILIKE', 'pet_owners.jur_name', $filter['name'] . '%', false],
                ]);
            }
        }

        if (!empty($filter['f_fio'])) {
            $query->andWhere(
                [
                    'OR',
                    ['ILIKE', 'pet_owners.f_fio', $filter['name'], false],
                    ['ILIKE', 'pet_owners.f_fio', $filter['f_fio']],
                ]
            );
        }

        if (!empty($filter['i_fio'])) {
            $query->andWhere(
                [
                    'OR',
                    ['ILIKE', 'pet_owners.i_fio', $filter['name'], false],
                    ['ILIKE', 'pet_owners.i_fio', $filter['i_fio']],
                ]
            );
        }

        if (!empty($filter['o_fio'])) {
            $query->andWhere(
                [
                    'OR',
                    ['ILIKE', 'pet_owners.o_fio', $filter['name'], false],
                    ['ILIKE', 'pet_owners.o_fio', $filter['o_fio']],
                ]
            );
        }

        if (!empty($filter['id_pet'])) {
            $query->innerJoin(
                PetsToOwner::tableName() . ' pets_to_owner',
                'pet_owners.id = pets_to_owner.id_owner AND pets_to_owner.id_pet = :id_pet',
                ['id_pet' => (int)$filter['id_pet']]
            );
        }

        if (!empty($filter['phone'])) {
            if (mb_strlen($filter['phone']) >= 4) {
                $query->innerJoin(
                    'contacts',
                    'contacts.entity_type = :entity_type AND contacts.entity_id = pet_owners.id',
                    [':entity_type' => Contacts::ENTITY_TYPE_PET_OWNER]
                )->innerJoin(
                    'contact_types',
                    'contacts.id_contact_type = contact_types.id AND contact_types.type = :contact_types_type',
                    [':contact_types_type' => ContactTypes::TYPE_PHONE]
                );

                $query
                    ->andWhere([
                        'OR',
                        ['ILIKE', 'contacts.name', '+' . $filter['phone'] . '%', false],
                        ['ILIKE', 'contacts.name', $filter['phone'] . '%', false]
                    ]);
            } else {
                throw new BadRequestHttpException('Номер телефона должен содержать не менее трех цифр');
            }
        }

        if (isset($filter['only_duplicates']) && $filter['only_duplicates'] === true) {
            $query->andWhere(['pet_owners.is_main' => false]);
        } else {
            $query->andWhere([
                'or',
                ['pet_owners.is_main' => true],
                ['pet_owners.is_main' => null],
            ]);
        }

        if (isset($filter['has_active_brood']) && $filter['has_active_brood'] === true) {
            $query->innerJoin(
                Brood::tableName(),
                'pet_owners.id = broods.id_owner AND broods.is_active = true AND broods.pet_count > 1'
            );
        }

        if (!empty($filter['passport_number'])) {
            $query->andWhere(['ilike', 'pet_owners.passport_number', $filter['passport_number']]);
        }

        if (!empty($filter['passport_series'])) {
            $query->andWhere(['ilike', 'pet_owners.passport_series', $filter['passport_series']]);
        }

        if (!empty($filter['passport_issue_date'])) {
            $query->andWhere(['pet_owners.passport_issue_date' => $filter['passport_issue_date']]);
        }

        if (!empty($filter['passport_issuer'])) {
            $query->andWhere(['ilike', 'pet_owners.passport_issuer', $filter['passport_issuer']]);
        }

        if (isset($filter['only_deleted']) && $filter['only_deleted'] === true) {
            $query->andWhere(['pet_owners.is_deleted' => true]);
        } else {
            $query->andWhere(['pet_owners.is_deleted' => false]);
        }
        return $query;
    }

    /**
     * Валидирует фильтр
     *
     * @param $filter
     * @return array|void
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateFilter($filter)
    {
        if (empty($filter)) {
            return;
        }

        $empty_filter = [
            'name' => null,
            'inn' => null,
            'ogrn' => null,
            'snils' => null,
            'address' => null,
            'id_area' => null,
            'id_district' => null,
            'is_legal' => null,
            'entrepreneur' => null,
            'id_pet' => null,
            'phone' => null,
            'except' => null,
            'only_duplicates' => null,
            'has_active_brood' => null,
            'only_deleted' => null,
            'passport_number' => null,
            'passport_series' => null,
            'passport_issue_date' => null,
            'passport_issuer' => null,
        ];

        $filter = array_merge($empty_filter, $filter);

        if (empty($filter['except']) || !is_array($filter['except'])) {
            $filter['except'] = [];
        }

        $rules = [
            [['id_area', 'id_district', 'id_pet'], 'integer'],
            [['name', 'address', 'ogrn', 'snils', 'inn', 'passport_number', 'passport_series', 'passport_issue_date', 'passport_issuer'], 'string'],
            [['name', 'address'], FullTrimValidator::class],
            [['is_legal', 'entrepreneur', 'only_duplicates', 'has_active_brood', 'only_deleted'], 'boolean'],
            ['except', 'each', 'rule' => ['integer']],
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $model->attributes;
    }


    /**
     * Обновляем адрес у животного, если установен флаг "Адрес содержания совпадает с адресом владельца"
     *
     * @param PetOwners $petOwner
     */
    private function updateFiasAddressForPets(PetOwners $petOwner)
    {
        foreach ($petOwner->pets as $pet) {
            if (!$pet->is_address_pet_owners) {
                continue;
            }

            $fistAddress = FiasAddresses::createOrUpdateFiasAddress(
                $pet->id_fias_address,
                $petOwner->fact_fias_addresses->getAttributes(null, ['id', 'text_hash']),
                ['text_hash']
            );
            if (!$pet->id_fias_address) {
                $pet->setAttribute('id_fias_address', $fistAddress->getPrimaryKey())->save();
            }
        }
    }


    /**
     * Возвращает модель указанного владельца/представителя
     * или генерирует ошибку
     *
     * @param int $id
     * @return PetOwners|null
     * @throws BadRequestHttpException
     */
    protected function findPetOwner($id)
    {
        $pet_owner = PetOwners::findOne(['id' => $id]);

        if (empty($pet_owner)) {
            throw new BadRequestHttpException('Указанный владелец/представитель не найден');
        }

        return $pet_owner;
    }


    /**
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $jur_name
     * @param string $inn
     * @param string $ogrn
     * @param string $snils
     * @param bool $is_legal
     * @param bool $entrepreneur
     * @param int $id
     * @param bool $pets_count
     * @return array
     * @throws \yii\db\Exception
     * @throws BadRequestHttpException
     */
    public function suggestDuplicates(
        $f_fio,
        $i_fio,
        $o_fio = null,
        $jur_name = null,
        $inn = null,
        $ogrn = null,
        $snils = null,
        $is_legal = false,
        $entrepreneur = false,
        $id = null,
        $pets_count = false,
        $birthday = null,
        $addresses_is_equal = false,
        $fias_address = null,
        $fact_fias_address = null,
        $contacts = null
        //        $passport = null
    ) {
        //        $findByPassport = PetOwners::find()
        //            ->select('*')
        //            ->where(['passport' => $passport])
        //            ->all();
        //
        //        if ($findByPassport) {
        //            throw new BadRequestHttpException('Указанные паспортные данные уже есть в системе');
        //        }

        $filterEscapes = new PGFilterEscapesValidator();
        $filterQuotes = new PGFilterQuotesValidator();

        $f_fio = $filterEscapes->validate($f_fio);
        $i_fio = $filterEscapes->validate($i_fio);
        $o_fio = $filterEscapes->validate($o_fio);
        $jur_name = $filterEscapes->validate($jur_name);

        if (($f_fio == 'Инкогнито' || $i_fio == 'Инкогнито' || $o_fio == 'Инкогнито')
            && empty($inn) && empty($ogrn) && empty($snils)
        ) {
            return [];
        }

        $query = PetOwners::find()
            ->select('*')
            ->with('fact_fias_addresses')
            ->with('fias_addresses')
            ->with('contacts')
            ->asArray()
            ->where(['is_deleted' => false]);


        // условие по is_main будет зависеть от того, где будет применяться функция
        if (empty($id)) {
            // создание владельца
            // "основные из объединенных" ('is_main' === true) и "необработанные" ('is_main' === null)
            $query->andWhere([
                'or',
                ['is_main' => true],
                ['is_main' => null],
            ]);
        } else {
            // 1) предложения по объединению владельцев
            // 2) редактирование владельца (пока убрали из требований и в контроллере)
            $owner = $this->findPetOwner($id);
            if ($owner->is_main === false) {
                // это дубль, не предлагаем ничего
                return [];
            } elseif ($owner->is_main === true) {
                // это основная запись - предлагаем только "необработанные"
                $query->andWhere(['is_main' => null]);
            } else {
                // еще не участвовал в объединении
                // "основные из объединенных" ('is_main' === true) и "необработанные" ('is_main' === null)
                $query->andWhere([
                    'or',
                    ['is_main' => true],
                    ['is_main' => null],
                ]);
            }
            $query->andWhere(['!=', 'pet_owners.id', $id]);
        }

        if ($is_legal === false) {
            if ($entrepreneur === false) {
                // физ.лицо (условие по снилс ниже)
                $query->andWhere([
                    'is_legal' => false,
                    'entrepreneur' => false,
                ]);
            } else {
                // ИП (условие по ИНН ниже)
                $query->andWhere([
                    'is_legal' => false,
                    'entrepreneur' => true,
                ]);
            }

            if ($f_fio == 'Инкогнито' || $i_fio == 'Инкогнито' || $o_fio == 'Инкогнито') {
                $condition = [];
            } else {
                // При длине слова до 3 символов:
                // - для фамилии используем точное совпадение (без учета регистра) - позволит учесть китайцев,
                // - для имени и отчества - ilike с начала строки (без учета регистра), учитываем также инициалы
                // При длине слова от 4 символов - trgm
                if (mb_strlen($f_fio) <= 3) {
                    $f_fio_condition = ['ilike', 'f_fio', $f_fio, false];
                    $query->addOrderBy(['f_fio' => SORT_ASC]);
                } else {
                    $ef_fio = $filterQuotes->validate($f_fio);
                    $f_fio_condition = new Expression('f_fio % \'' . $ef_fio . '\'');
                    $query->addSelect(new Expression('f_fio <-> \'' . $ef_fio . '\' AS f_dist'))
                        ->addOrderBy(['f_dist' => SORT_ASC]);
                }

                if (mb_strlen($i_fio) <= 3) {
                    $i_fio_expression = ['ilike', 'i_fio', $i_fio . '%', false];
                    $query->addOrderBy(['i_fio' => SORT_ASC]);
                } else {
                    $ei_fio = $filterQuotes->validate($i_fio);
                    $i_fio_expression = new Expression('i_fio % \'' . $ei_fio . '\'');
                    $query->addSelect(new Expression('i_fio <-> \'' . $ei_fio . '\' AS i_dist'))
                        ->addOrderBy(['i_dist' => SORT_ASC]);
                }
                // учитываем также инициалы
                $i_i = mb_substr($i_fio, 0, 1);
                $i_fio_condition = [
                    'or',
                    $i_fio_expression,
                    ['ilike', 'i_fio', $i_i . '.', false],
                    ['ilike', 'i_fio', $i_i, false]
                ];

                $condition = [
                    'and',
                    $f_fio_condition,
                    $i_fio_condition,
                ];

                if (!empty($o_fio)) {
                    if (mb_strlen($o_fio) <= 3) {
                        $o_fio_expression = ['ilike', 'o_fio', $o_fio . '%', false];
                        $query->addOrderBy(['o_fio' => SORT_ASC]);
                    } else {
                        $eo_fio = $filterQuotes->validate($o_fio);
                        $o_fio_expression = new Expression('o_fio % \'' . $eo_fio . '\'');
                        $query->addSelect(new Expression('o_fio <-> \'' . $eo_fio . '\' AS o_dist'))
                            ->addOrderBy(['o_dist' => SORT_ASC]);
                    }
                    // учитываем также инициалы
                    $o_i = mb_substr($o_fio, 0, 1);
                    $o_fio_condition = [
                        'or',
                        $o_fio_expression,
                        ['ilike', 'o_fio', $o_i . '.', false],
                        ['ilike', 'o_fio', $o_i, false],
                    ];
                    $condition[] = [
                        'or',
                        $o_fio_condition,
                        ['o_fio' => null],
                        ['o_fio' => ''],
                    ];
                }

                if (!empty($snils)) {
                    $condition[] = ['snils' => null];
                }
            }

            if (!empty($snils) || ($entrepreneur === true && !empty($inn))) {
                $condition = empty($condition)
                    ? ['or']
                    : [
                        'or',
                        $condition,
                    ];
                if (!empty($snils)) {
                    $condition[] = ['snils' => $snils];
                }
                if ($entrepreneur === true && !empty($inn)) {
                    $condition[] = ['inn' => $inn];
                }
            }

            if (count($condition) > 1) {
                $query->andWhere($condition);
            }
        } else {
            // юр.лицо
            $query->andWhere([
                'is_legal' => true,
            ]);
            // при длине слова до 3 символов используем точное совпадение (без учета регистра),
            // от 4 символов - trgm
            $jur_name_condition = (mb_strlen($jur_name) <= 3)
                ? ['ilike', 'jur_name', $jur_name, false]
                : new Expression('jur_name % \'' . $filterQuotes->validate($jur_name) . '\'');
            $query->andWhere([
                'and',
                $jur_name_condition,
                ['inn' => $inn],
                ['ogrn' => $ogrn]
            ]);
        }

        if ($pets_count === true) {
            $query->addSelect(new Expression('COALESCE(pc.pets_count, 0) AS pets_count'))
                ->leftJoin(
                    ['pc' => $this->getPetsCountSubQuery()],
                    'pet_owners.id = pc.id_owner'
                );
        }

        $query->addOrderBy(new Expression('is_main desc nulls last'));
        $query->addOrderBy(['pet_owners.id' => SORT_ASC]);

        \Yii::$app->db
            ->createCommand('SET pg_trgm.similarity_threshold = 0.5')
            ->execute();

        !$birthday ? $query->andWhere(['IS', 'birthday', NULL]) : $query->andWhere(['=', 'birthday', $birthday]);
        !$snils ? $query->andWhere(['IS', 'snils', NULL]) : $query->andWhere(['=', 'snils', $snils]);


        // id_fact_fias_address связанн с таблицей FiasAddresses,
        // а id_fias_address нет - так что проверяем по fact_fias_address
        $fact_fias_address = ['region' => 'Город Москва']; //без этого всё валится
        foreach ($fact_fias_address as $i => $v) {
            if (null === $v) {
                unset($fact_fias_address[$i]);
            }
        };

        $factFiasAddresses = FiasAddresses::find();
        if (count($fact_fias_address) > 0) {
            if (isset($fact_fias_address['region'])) {
                $factFiasAddresses->andWhere(['region' => $fact_fias_address['region']]);
            } else {
                $factFiasAddresses->andWhere(
                    ['IS', 'region', NULL]
                );
            };
            if (isset($fact_fias_address['regionguid'])) {
                $factFiasAddresses->andWhere(['regionguid' => $fact_fias_address['regionguid']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'regionguid', NULL]);
            };
            if (isset($fact_fias_address['street'])) {
                $factFiasAddresses->andWhere(['street' => $fact_fias_address['street']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'street', NULL]);
            };
            if (isset($fact_fias_address['house'])) {
                $factFiasAddresses->andWhere(['house' => $fact_fias_address['house']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'house', NULL]);
            };
            if (isset($fact_fias_address['room'])) {
                $factFiasAddresses->andWhere(['room' => $fact_fias_address['room']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'room', NULL]);
            };
            if (isset($fact_fias_address['houseguid'])) {
                $factFiasAddresses->andWhere(['houseguid' => $fact_fias_address['houseguid']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'houseguid', NULL]);
            };
            if (isset($fact_fias_address['roomguid'])) {
                $factFiasAddresses->andWhere(['roomguid' => $fact_fias_address['roomguid']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'roomguid', NULL]);
            };
            if (isset($fact_fias_address['cityguid'])) {
                $factFiasAddresses->andWhere(['cityguid' => $fact_fias_address['cityguid']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'cityguid', NULL]);
            };
            if (isset($fact_fias_address['streetguid'])) {
                $factFiasAddresses->andWhere(['streetguid' => $fact_fias_address['streetguid']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'streetguid', NULL]);
            };
            if (isset($fact_fias_address['description'])) {
                $factFiasAddresses->andWhere(['description' => $fact_fias_address['description']]);
            } else {
                $factFiasAddresses->andWhere(['IS', 'description', NULL]);
            };
        } else {
            // если как то пришло пустое значение фактического адреса
            //тогда точно не найдется дубликат
            $factFiasAddresses->andWhere(['text_hash' => 000000000000000000000]);
        }

        // Все похожие адресса: массив
        $res = $factFiasAddresses->all();

        $factFiasAdressesIds = [];
        foreach ($res as $k) {
            $factFiasAdressesIds[] = $k['id'];
        };

        if ($addresses_is_equal == true) {
            $query->andWhere(['=', 'addresses_is_equal', $addresses_is_equal]);
            if ($factFiasAdressesIds) {
                $query->andWhere(['in', 'id_fact_fias_address', $factFiasAdressesIds]);
            } else {
                // если не нашлись похожие адреса то -  точно нет дубликата
                $query->andWhere(['id_fact_fias_address' => 0000000000000]);
            }
            //            непонятно почему id не сработало...
            //$id_fact_fias_address = FiasAddresses::findOne(['text_hash' => $hashFromEntry])->id;
        }
        if (!$addresses_is_equal) {
            if (count($factFiasAdressesIds) > 0) {
                $query->andWhere(['in', 'id_fact_fias_address', $factFiasAdressesIds]);
            }
            if (count($factFiasAdressesIds) == 0) {
                // если не нашлись похожие адреса то -  точно нет дубликата
                $query->andWhere(['id_fact_fias_address' => 0000000000000]);
            }
        }

        $contactsDuplicates = [];
        if ($contacts) {
            $contactsDuplicates = PetOwners::find()
            ->select('pet_owners.*')
            ->with('fact_fias_addresses')
            ->with('fias_addresses')
            ->with('contacts')
            ->joinWith('contacts', false)
            ->where(['is_deleted' => false])
            ->andWhere(['contacts.name' => array_column($contacts, 'name')])
            ->andWhere(['!=', 'pet_owners.id', $id]);

            if ($pets_count === true) {
                $contactsDuplicates->addSelect(new Expression('COALESCE(pc.pets_count, 0) AS pets_count'))
                    ->leftJoin(
                        ['pc' => $this->getPetsCountSubQuery()],
                        'pet_owners.id = pc.id_owner'
                    );
            }

            return $contactsDuplicates->asArray()->all();
        }

        $duplicates = $query->all();
        $duplicates = $duplicates ?? [];
        
        foreach($contactsDuplicates as $contactsDuplicate) {
            $found = false;
            foreach($duplicates as $duplicate) {
                if ($duplicate['id'] == $contactsDuplicate['id']) $found = true;
            }
            if (!$found) $duplicates[] = $contactsDuplicate;
        }

        return $duplicates;
    }

    /**
     * @param int $id_main_owner
     * @param array $duplicates_ids
     * @param array $merge_data
     * @return bool
     * @throws \Throwable
     */
    public function linkOwners($id_main_owner, $duplicates_ids = [], array $merge_data = [])
    {
        $contactIds = [];
        $mainOwner = PetOwners::findOne(['id' => $id_main_owner]);

        $mainOwner->is_main = true;
        $mainOwner->id_main_owner = null;
        $mainOwner->duble_validation = date('Y-m-d H:i:s');

        foreach ($merge_data as $key => $value) {
            if (in_array($key, ['phone', 'email']))
                $contactIds[] = $value;
        }

        $copyFieldsNames = ['birthday', 'f_fio', 'i_fio', 'o_fio', 'fullname', 'id_fias_address', 'id_fact_fias_address'];
        $fiasFieldsNames = ['id_fias_address', 'id_fact_fias_address'];
        $copy_snils_sso_id = false;
        $emptyFields = [];
        $contacts = [];

        //находим незаполненные поля
        foreach ($copyFieldsNames as $copyFieldsName) {
            if (in_array($copyFieldsName, ['f_fio', 'i_fio', 'o_fio', 'fullname']) && !isset($merge_data['fullname']))
                continue;
            if (empty($mainOwner->$copyFieldsName) || in_array($copyFieldsName, ['f_fio', 'i_fio', 'o_fio', 'fullname']) && isset($merge_data['fullname'])) {
                $emptyFields[$copyFieldsName] = ['time' => false, 'owner_id' => null, 'value' => null];
            }
            if (in_array($copyFieldsName, $fiasFieldsNames)) {
                $address = FiasAddresses::findOne($mainOwner->$copyFieldsName);
                if (isset($merge_data['fias_addresses']) || isset($merge_data['fact_fias_addresses']) || empty($address) || $address && empty($address->streetguid) && empty($address->houseguid)) {
                    $emptyFields[$copyFieldsName] = ['time' => false, 'owner_id' => null, 'value' => null];
                }
            }
        }
        //нужно ли переносить снилc и sso_id VETAIS-3364
        if (empty($mainOwner->snils) && empty($mainOwner->sso_id)) {
            $copy_snils_sso_id = true;
            $emptyFields['snils'] = ['time' => false, 'owner_id' => null, 'value' => null];
            $emptyFields['sso_id'] = ['time' => false, 'owner_id' => null, 'value' => null];
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if (!$mainOwner->update(true, ['is_main', 'id_main_owner', 'duble_validation', 'updated_at', 'updated_by'])) {
                $this->addErrors($mainOwner->getErrors());
                $transaction->rollBack();

                return false;
            }
            foreach ($duplicates_ids as $id_duplicate) {
                $owner = PetOwners::findOne(['id' => $id_duplicate]);
                if ($owner->is_main === true) {
                    $this->addError('duplicates_ids', 'Ошибка объединения с владельцем ID ' . $owner->id . ' - он уже является основным');
                    $transaction->rollBack();

                    return false;
                };

                if (!$this->checkEnterprenuerAndLegal($mainOwner, $owner)) {
                    return false;
                }

                foreach ($emptyFields as $emptyFieldName => $updateData) {
                    if (empty($owner->$emptyFieldName)) {
                        continue;
                        //если копируем адрес, то считаем адрес пустым если у него нет улицы и дома
                    } elseif (in_array($emptyFieldName, $fiasFieldsNames)) {
                        $address = FiasAddresses::findOne($owner->$emptyFieldName);
                        if (empty($address) || $address && empty($address->streetguid) && empty($address->houseguid)) {
                            continue;
                        }
                    } elseif (in_array($emptyFieldName, ['snils', 'sso_id']) && $copy_snils_sso_id) {
                        // Обновление снилса и sso_id. т.к. они переносятся вместе,
                        // Время обновления можно смотреть только по одному из них
                        if ((isset($owner->snils, $owner->sso_id))) {
                            if (!$emptyFields['snils']['time'] || strtotime($owner->updated_at) > $emptyFields['snils']['time']) {
                                $emptyFields['snils'] = [
                                    'time' => strtotime($owner->updated_at),
                                    'owner_id' => $owner->id,
                                    'value' => $owner->snils,
                                    'old_value' => $mainOwner->snils
                                ];

                                $emptyFields['sso_id'] = [
                                    'time' => strtotime($owner->updated_at),
                                    'owner_id' => $owner->id,
                                    'value' => $owner->sso_id,
                                    'old_value' => $mainOwner->sso_id
                                ];

                                $mainOwner->snils = $owner->snils;
                                $mainOwner->sso_id = $owner->sso_id;
                            }
                        }
                        continue;
                    }
                    if (!$updateData['time'] || strtotime($owner->updated_at) > $updateData['time']) {
                        $emptyFields[$emptyFieldName]['time'] = strtotime($owner->updated_at);
                        $emptyFields[$emptyFieldName]['owner_id'] = $owner->id;
                        $emptyFields[$emptyFieldName]['value'] = $owner->$emptyFieldName;
                        $emptyFields[$emptyFieldName]['old_value'] = $mainOwner->$emptyFieldName;

                        if (in_array($emptyFieldName, array_keys($emptyFields))) {

                            if ($emptyFieldName === 'id_fias_address' && isset($merge_data['fias_addresses'])) {
                                $mainOwner->id_fias_address = $merge_data['fias_addresses'];
                            }

                            if ($emptyFieldName === 'id_fact_fias_address' && isset($merge_data['fact_fias_addresses'])) {
                                $mainOwner->id_fact_fias_address = $merge_data['fact_fias_addresses'];
                            }

                            $mainOwner->addresses_is_equal = false;

                            if (isset($merge_data['fullname']) && $owner->id === $merge_data['fullname']) {
                                $mainOwner->fullname = $owner->fullname;
                                $mainOwner->f_fio = $owner->f_fio;
                                $mainOwner->i_fio = $owner->i_fio;
                                $mainOwner->o_fio = $owner->o_fio;
                            }
                        } else
                            $mainOwner->$emptyFieldName = $owner->$emptyFieldName;
                    }
                }

                foreach ($owner->contacts as $contact) {
                    if (!in_array($contact->id, $contactIds))
                        continue;

                    $contacts[$owner->id][$contact->id] = [
                        'entity_id' => $contact->entity_id,
                        'main_flag' => $contact->main_flag,
                    ];
                    $contact->main_flag = false;
                    $contact->entity_id = $mainOwner->id;
                    $contact->save();
                }


                $owner->is_main = false;
                $owner->id_main_owner = $id_main_owner;
                $owner->duble_validation = date('Y-m-d H:i:s');
                $owner->is_deleted = true;
                if (!$owner->update(true, ['is_main', 'id_main_owner', 'duble_validation', 'updated_at', 'updated_by', 'is_deleted'])) {
                    #if (!$owner->update(true, ['is_main', 'id_main_owner', 'duble_validation', 'updated_at', 'updated_by'])) {
                    $this->addErrors($owner->getErrors());
                    $transaction->rollBack();

                    return false;
                }
            }

            //Затираем снилс у того дубля, с которого будет осуществлен перенос
            //Иначе нарушение уникальности БД
            if (!empty($emptyFields['snils']['value']) && !empty($emptyFields['sso_id']['value'])) {
                $this->eraseSnils($emptyFields['snils']['owner_id']);
            } else {
                unset($emptyFields['snils'], $emptyFields['sso_id']);
            }

            $historyData = [];
            foreach ($emptyFields as $emptyFieldName => $emptyFieldData) {
                if (empty($emptyFieldData['owner_id'])) {
                    continue;
                }
                $historyData[$emptyFieldData['owner_id']]['values'][$emptyFieldName] = $emptyFieldData;
            }
            foreach ($contacts as $owner_id => $contact) {
                $historyData[$owner_id]['contacts'] = $contact;
            }

            if (!$mainOwner->save(false)) {
                $this->addErrors($mainOwner->getErrors());
                $transaction->rollBack();

                return false;
            }

            foreach ($historyData as $id => $data) {
                $ownerHistory = new PetOwnersLinkHistory();
                $ownerHistory->id_owner_main = $mainOwner->id;
                $ownerHistory->id_owner_duplicate = $id;
                $ownerHistory->values = $data['values'] ?? null;
                $ownerHistory->contacts = $data['contacts'] ?? null;
                $ownerHistory->save();
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
     * Метод открепления владельца-дубля от основного владельца
     * (п.1.6.1 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Открепить")
     *
     * @param int $id ID владельца-дубля
     * @return bool
     */
    public
    function unlinkOwner($id)
    {
        $owner = PetOwners::findOne(['id' => $id]);

        if ($owner->is_main !== false || empty($owner->id_main_owner)) {
            $this->addError('id', 'Ошибка открепления владельца с ID ' . $id . ' - он не является дублем');

            return false;
        }

        $mainOwner = PetOwners::findOne(['id' => $owner->id_main_owner]);

        if ($mainOwner === null) {
            // возможно просто у дубля остался заполненным id_main_owner
            $owner->is_main = null;
            $owner->id_main_owner = null;
            $owner->duble_validation = null;

            return $owner->update(true, ['is_main', 'id_main_owner', 'duble_validation', 'updated_at', 'updated_by']);
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $owner->is_main = null;
            $owner->id_main_owner = null;
            $owner->duble_validation = null;
            if (!$owner->update(true, ['is_main', 'id_main_owner', 'duble_validation', 'updated_at', 'updated_by'])) {
                $this->addErrors($owner->getErrors());
                $transaction->rollBack();
                return false;
            }

            $ownerHistory = PetOwnersLinkHistory::findOne(
                [
                    'id_owner_main' => $mainOwner->id,
                    'id_owner_duplicate' => $owner->id,
                    'enabled' => true
                ]
            );
            if ($ownerHistory) {
                if (isset($ownerHistory->values)) {
                    foreach ($ownerHistory->values as $field => $value) {
                        $mainOwner->$field = $value['old_value'];
                    }
                }
                if (isset($ownerHistory->contacts)) {
                    foreach ($ownerHistory->contacts as $contact_id => $fields) {
                        if (!$contact = Contacts::findOne($contact_id)) {
                            continue;
                        }
                        foreach ($fields as $contact_field => $contact_value) {
                            $contact->$contact_field = $contact_value;
                        }
                        $contact->save();
                    }
                }

                if (!$mainOwner->save(false)) {
                    $this->addErrors($mainOwner->getErrors());
                    $transaction->rollBack();
                    return false;
                }
                //Восстанавливаем снилс у дубля
                if (isset($ownerHistory->values['snils'])) {
                    $this->restoreSnils($owner->id, $ownerHistory->values['snils']['value']);
                }

                $ownerHistory->enabled = false;
                $ownerHistory->save();
            }

            /** @var \app\models\db\Pets[] $pets */
            $pets = Pets::find()
                ->alias('p')
                ->leftJoin(PetsToOwner::tableName() . ' pto', 'p.id = pto.id_pet')
                ->where([
                    'p.is_main' => false,
                ])
                ->andWhere(['pto.id_owner' => $owner->id])
                ->all();

            foreach ($pets as $pet) {
                $model = new PetsDuplicatesModel();
                if (!$model->unlinkPet($mainOwner->id, $pet->id)) {
                    $this->addErrors($model->getErrors());
                    $transaction->rollBack();

                    return false;
                }
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
     * Метод снятия признака "Основной" у владельца
     * (п.1.6.1.1 https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=124203916
     * - обработка кнопки "Отменить признак "Основная запись"")
     *
     * @param int $id ID основного владельца
     * @return bool
     */
    public
    function undoMain(int $id)
    {
        $owner = PetOwners::findOne(['id' => $id]);

        if ($owner->is_main !== true || !empty($owner->id_main_owner)) {
            $this->addError('id', 'Ошибка при снятии признака "Основной" у владельца с ID ' . $id . ' - он не является основным');

            return false;
        }

        if (!empty($owner->duplicates)) {
            $this->addError('id', 'Ошибка при снятии признака "Основной" у владельца с ID ' . $id . ' - сначала необходимо открепить дублирующие записи');

            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            /** @var \app\models\db\Pets[] $pets */
            $pets = Pets::find()
                ->alias('p')
                ->leftJoin(PetsToOwner::tableName() . ' pto', 'p.id = pto.id_pet')
                ->andWhere(['pto.id_owner' => $owner->id])
                ->all();

            // Если у владельца, у которого снимается признак "основная запись", есть животные-дубли,
            // которые также принадлежат этому владельцу, их необходимо открепить (привести в исходное состояние)
            foreach ($pets as $i => $pet) {
                $petOwners = $pet->owners;
                if (count($petOwners) > 1) {
                    // надо проверить, не прикреплены ли животные к какому-то еще владельцу,
                    // который является основным - в этом случае  не будем ничего восстанавливать у животного
                    // - возможно оно было "склеено" через другого владельца
                    $found = false;
                    foreach ($petOwners as $petOwner) {
                        if ($petOwner->id == $owner->id) {
                            continue;
                        }
                        if ($petOwner->is_main === true) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found === true) {
                        unset($pets[$i]);
                        continue;
                    }
                }

                if ($pet->is_main === true) {
                    // пока не трогаем, разбираемся сначала с дублями
                    continue;
                } else {
                    if ($pet->is_main === false && !empty($pet->id_main_pet)) {
                        $model = new PetsDuplicatesModel();
                        if (!$model->unlinkPet($owner->id, $pet->id)) {
                            $this->addErrors($model->getErrors());
                            $transaction->rollBack();

                            return false;
                        }
                    }
                    unset($pets[$i]);
                }
            }
            // добиваем оставшихся животных с is_main === true
            if (!empty($pets)) {
                foreach ($pets as $pet) {
                    $model = new PetsDuplicatesModel();
                    if (!$model->undoMakeMain($owner->id, $pet->id)) {
                        $this->addErrors($model->getErrors());
                        $transaction->rollBack();

                        return false;
                    }
                }
            }

            $owner->is_main = null;
            $owner->duble_validation = null;
            if (!$owner->update(true, ['is_main', 'duble_validation', 'updated_at', 'updated_by'])) {
                $this->addErrors($owner->getErrors());
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
     * Формируем данные для вывода в подсказках краткой информации о животных владельца на фронте:
     *
     * > Животные Вид / Кличка - двухстрочный заголовок.
     * > В таблице поле - 3 строки. В первых двух строках выводится вид и кличка двух первых (по id) животных владельца...
     * > В третьей строчке выводится текст "Еще <количество животных владельца> животных". Текст выводится в случае, если кол-во животных владельца превышает 2.
     *
     * @param array $suggestions
     */
    public
    function addPetsSummary(&$suggestions)
    {
        $ownersIds = ArrayHelper::getColumn($suggestions, 'id');

        $rows = (new Query())
            ->select([
                'p.name',
                's.name AS species_name',
                'pto.id_pet',
                'pto.id_owner'
            ])
            ->from([
                'pto' => (new Query())
                    ->select(['id_pet', 'id_owner'])
                    ->from(PetsToOwner::tableName())
                    ->where(['in', 'id_owner', $ownersIds])
                    ->orderBy([
                        'id_owner' => SORT_ASC,
                        'id_pet' => SORT_ASC,
                    ])
                    ->distinct(),
            ])
            ->leftJoin(Pets::tableName() . ' p', 'p.id = pto.id_pet')
            ->leftJoin(Species::tableName() . ' s', 's.id = p.id_species')
            ->all();

        $pets_data = ArrayHelper::index($rows, null, 'id_owner');

        foreach ($suggestions as &$suggestion) {
            $pets = array_key_exists($suggestion['id'], $pets_data) ? $pets_data[$suggestion['id']] : [];
            $pets_summary['first_2_pets'] = array_slice($pets, 0, 2);
            $pets_summary['more_pets'] = $suggestion['pets_count'] - count($pets_summary['first_2_pets']);
            $suggestion['pets_summary'] = $pets_summary;
        }
    }

    /**
     * Ставит СНИЛС в null у владельца id_owner.
     * Необходимо при склейке СНИЛС из-за ограничений уникальности.
     * @see https://jira.altarix.ru/browse/VETAIS-3364
     * @param int $id_owner
     */
    private function eraseSnils($id_owner)
    {
        $owner = PetOwners::findOne($id_owner);
        $owner->snils = null;
        if (!$owner->update()) {
            throw new Exception('Ошибка при переносе СНИЛС');
        }
    }

    /**
     * Возвращает СНИЛС владельцу
     * @param int $id_owner
     * @param string $snils
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function restoreSnils($id_owner, $snils)
    {
        $owner = PetOwners::findOne($id_owner);
        $owner->snils = $snils;
        if (!$owner->update(false)) {
            throw new Exception('Ошибка при переносе СНИЛС');
        }
    }

    /**
     * если есть два юр лица с разными инн и/или с разными огрн запрещаем склеивать
     * @param PetOwners $mainOwner
     * @param PetOwners $owner
     * @return bool
     */
    private function checkEnterprenuerAndLegal(PetOwners $mainOwner, PetOwners $owner)
    {
        if ($mainOwner->is_legal && $owner->is_legal) {
            if ($mainOwner->inn != $owner->inn) {
                $this->addError('inn', 'Нельзя склеить два юр. лица с разными ИНН');
                return false;
            }
            if ($mainOwner->ogrn != $owner->ogrn) {
                $this->addError('ogrn', 'Нельзя склеить два юр. лица с разными ОГРН');
                return false;
            }
        } else if ($mainOwner->entrepreneur && $owner->entrepreneur) {
            if ($mainOwner->inn != $owner->inn) {
                $this->addError('inn', 'Нельзя склеить два ИП с разными ИНН');
                return false;
            }
        }
        return true;
    }
}
