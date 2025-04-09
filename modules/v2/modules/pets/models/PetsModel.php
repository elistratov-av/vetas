<?php

namespace app\modules\v2\modules\pets\models;

use app\common\components\FileService;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\common\helpers\DateHelper;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\common\validators\PGFilterEscapesValidator;
use app\common\validators\PGFilterQuotesValidator;
use app\models\db\Contacts;
use app\models\db\IdentificationTypes;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\models\db\PetOwnersHistory;
use app\models\db\FiasAddresses;
use app\models\db\PetOwnerType;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\modules\mdm\models\Pet;
use app\modules\soap\models\VisitsGovServices;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\pets\skeletons\pets\PetsList;
use app\modules\v2\modules\visit\models\BillModel;
use Yii;
use yii\base\BaseObject;
use yii\base\DynamicModel;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class PetsModel
{
    public $file_type = 'pet';

    /**
     * Создание животного
     *
     * @param string $reg_expire_date
     * @param string $birthday
     * @param string $name
     * @param string $sex
     * @param int $id_species
     * @param int $id_breed
     * @param int|null $id_reg_expire_reason
     * @param bool $guide_dog
     * @param bool $castrated
     * @param int $photo
     * @param string $date_plan_rabies_vaccination
     * @param string $date_plan_identification
     * @param string $date_plan_lept_vaccination
     * @param string $description
     * @param array fias_address
     * @return \app\models\db\Pets
     * @throws BadRequestHttpException
     */
    public function create(
        $reg_expire_date,
        $birthday,
        $name,
        $sex,
        $id_species,
        $id_breed,
        $id_reg_expire_reason,
        $guide_dog,
        $castrated,
        $photo,
        $date_plan_rabies_vaccination,
        $date_plan_identification,
        $date_plan_lept_vaccination,
        $description,
        $fias_address,
        $is_address_pet_owners,
        $id_owner,
        $id_owner_type
    ) {
        if (!empty($id_reg_expire_reason) || !empty($reg_expire_date)) {
            throw new BadRequestHttpException('Нельзя заводить животное в статусе "Снято с учета"');
        }
        if (empty($name)) {
            throw new BadRequestHttpException('Введите имя Питомца');
        }

        if (empty($sex)) {
            throw new BadRequestHttpException('Выберете пол Питомца');
        }

        $pet = new Pets();
        $pet->reg_expire_date = $reg_expire_date;
        $pet->birthday = $birthday;
        $pet->name = $name;
        $pet->sex = $sex;
        $pet->id_species = $id_species;
        $pet->id_breed = $id_breed;
        $pet->id_reg_expire_reason = $id_reg_expire_reason;
        $pet->guide_dog = $guide_dog;
        $pet->castrated = $castrated;
        $pet->photo = $photo;
        $pet->date_plan_rabies_vaccination = $date_plan_rabies_vaccination;
        $pet->date_plan_identification = $date_plan_identification;
        $pet->date_plan_lept_vaccination = $date_plan_lept_vaccination;
        $pet->description = $description;

        //https://jira.altarix.ru/browse/VETAIS-3414
        if ($is_address_pet_owners && $id_owner_type == PetOwnerType::findRepresentativeTypeId()) {
            throw new BadRequestHttpException("Адрес содержания не может совпадать с адресом владельца при добавлении представителю");
        }

        $owner = PetOwners::findOne($id_owner);

        if ($is_address_pet_owners && $owner) { // явно сказано что адрес совпадает с адресом владельца
            if ($owner->fact_fias_addresses) {
                $fias_address = $owner->fact_fias_addresses->getAttributes(null, ['id', 'created_at', 'updated_at', 'text_hash']);
                $pet->id_fias_address =
                    FiasAddresses::createOrUpdateFiasAddress(
                        $pet->id_fias_address,
                        $fias_address,
                        ['text_hash']
                    )->getPrimaryKey();
            } else {
                throw new BadRequestHttpException("Ошибка при создании животного\nФактический адрес владельца не указан\nИ не может являться адресом содержания животного");
            }
            $pet->is_address_pet_owners = $is_address_pet_owners;
        } else {
            if ($fias_address) { // дан какой то адрес - надо понять совпадает ли
                $pet->id_fias_address = FiasAddresses::createOrUpdateFiasAddress(
                    $pet->id_fias_address,
                    $fias_address,
                    ['text_hash']
                )->getPrimaryKey();
                // Если добавлялось владельцу - проставим признак при совпадении
                if ($id_owner_type == PetOwnerType::findOwnerTypeId()) {
                    $pet->is_address_pet_owners = ($owner && $pet->id_fias_address == $owner->id_fact_fias_address);
                }
            } else {
                $pet->id_fias_address = null;
                $pet->is_address_pet_owners = false;
            }
        }


        /** @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        if ($user->specialist !== null) {
            $pet->id_created_organization = $user->specialist->id_organization;
        }

        if (!$pet->save()) {
            $errors = $pet->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании животного' : implode("\n", array_values($errors)));
        }

        return $pet;
    }

    /**
     * Редактирование животного
     *
     * @param int $id
     * @param string $reg_expire_date
     * @param string $birthday
     * @param string $name
     * @param string $sex
     * @param int $id_species
     * @param int $id_breed
     * @param int|null $id_reg_expire_reason
     * @param bool $guide_dog
     * @param bool $castrated
     * @param int $photo
     * @param string $date_plan_rabies_vaccination
     * @param string $date_plan_identification
     * @param string $date_plan_lept_vaccination
     * @param string $description
     * @param array $fias_address
     * @param bool $is_address_pet_owners
     * @return \app\models\db\Pets
     * @throws BadRequestHttpException
     */
    public function edit(
        $id,
        $reg_expire_date,
        $birthday,
        $name,
        $sex,
        $id_species,
        $id_breed,
        $id_reg_expire_reason,
        $guide_dog,
        $castrated,
        $photo,
        $date_plan_rabies_vaccination,
        $date_plan_identification,
        $date_plan_lept_vaccination,
        $description,
        $fias_address,
        $is_address_pet_owners,
        $characteristics
    ) {
        $pet = Pets::find()->where(['id' => $id])->one();

        if (empty($pet)) {
            throw new BadRequestHttpException('Указанное животное не найдено');
        }

        if (!$id_reg_expire_reason) {
            if ($pet->isReadOnly()) {
                throw new BadRequestHttpException('Снятое с учета животное не подлежит редактированию');
            }
        }

        if ($pet->is_main === false && (!empty($reg_expire_date) || !empty($id_reg_expire_reason))) {
            throw new BadRequestHttpException('Для снятия с учета животного-дубля необходимо сначала открепить его от основного животного');
        }

        if ((!empty($reg_expire_date) || !empty($id_reg_expire_reason)) && !empty($pet->getLastActiveShelterRecord())) {
            throw new BadRequestHttpException('Животное находится в приюте. Снятие с учета животного доступно только из приюта');
        }

        if ($date_plan_rabies_vaccination) {
            $pet->date_plan_rabies_vaccination = $date_plan_rabies_vaccination;
            if (!$pet->save()) {
                $errors = $pet->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении плановой даты вакцинации животного' : implode("\n", array_values($errors)));
            }
        }

        $pet->reg_expire_date = $reg_expire_date ? $reg_expire_date : null;
        $pet->birthday = $birthday ? $birthday : $pet->birthday;
        $pet->name = $name ? $name : $pet->name;
        $pet->sex = $sex ? $sex : $pet->sex;
        $pet->id_species = $id_species ? $id_species : $pet->id_species;
        $pet->id_breed = $id_breed ? $id_breed : $pet->id_breed;
        $pet->id_reg_expire_reason = $id_reg_expire_reason;
        $pet->guide_dog = $guide_dog ? $guide_dog : $pet->guide_dog;
        $pet->castrated = $castrated ? $castrated : $pet->castrated;
        $pet->photo = $photo ? $photo : $pet->photo;
        $pet->date_plan_rabies_vaccination = $date_plan_rabies_vaccination ? $date_plan_rabies_vaccination : $pet->date_plan_rabies_vaccination;
        $pet->date_plan_identification = $date_plan_identification ? $date_plan_identification : $pet->date_plan_identification;
        $pet->date_plan_lept_vaccination = $date_plan_lept_vaccination ? $date_plan_lept_vaccination : $pet->date_plan_lept_vaccination;
        $pet->description = $description  ? $description : $pet->description;
        $pet->characteristics = $characteristics  ? $characteristics : $pet->characteristics;
        if ($is_address_pet_owners && $pet->owner) { // явно сказано что адрес совпадает с адресом владельца
            if ($pet->owner->fact_fias_addresses) {
                $fias_address = $pet->owner->fact_fias_addresses->getAttributes(null, ['id', 'created_at', 'updated_at', 'text_hash']);
                $pet->id_fias_address =
                    FiasAddresses::createOrUpdateFiasAddress(
                        $pet->id_fias_address,
                        $fias_address,
                        ['text_hash']
                    )->getPrimaryKey();
            } else {
                throw new BadRequestHttpException("Ошибка при создании животного\nФактический адрес владельца не указан\nИ не может являться адресом содержания животного");
            }
            $pet->is_address_pet_owners = $is_address_pet_owners;
        } else {
            if ($fias_address) { // дан какой то адрес - надо понять совпадает ли
                $pet->id_fias_address = FiasAddresses::createOrUpdateFiasAddress(
                    $pet->id_fias_address,
                    $fias_address,
                    ['text_hash']
                )->getPrimaryKey();
                $pet->is_address_pet_owners = ($pet->owner && $pet->id_fias_address == $pet->owner->id_fact_fias_address);
            } else {
                $pet->id_fias_address = null;
                $pet->is_address_pet_owners = false;
            }
        }

        /*
         * Нельзя снимать с учета животных с открытыми визитами
         */
        if (!empty($id_reg_expire_reason)) {

            $unfinished = (new Query())
                ->select('id_visit AS id_visits')
                ->from('visit_pets')
                ->leftJoin('visits', 'visit_pets.id_visit = visits.id')
                ->where([
                    'AND',
                    ['visit_pets.id_pet' => $pet->id],
                    [
                        'IN',
                        'visits.status',
                        [
                            VisitStatus::NEW,
                            VisitStatus::TRANSFER,
                            VisitStatus::IN_WORK,
                            VisitStatus::CHANGED
                        ]
                    ]
                ])
                ->all();

            if (!empty($unfinished)) {
                throw new BadRequestHttpException(json_encode([
                    'text' => 'Снятие с учета невозможно, найдены незавершенные приемы у животного:',
                    'data' => $unfinished
                ], JSON_UNESCAPED_UNICODE));
            }
        }

        // есть непонятки со связью таблицы pet_ref_size с users
        // (из за этого не сохраняет при edit ) ! нет связи с pets в бд

        switch ($pet->size_id) {
            case 10:
                $pet->size_id = 1;
                break;
            case 20:
                $pet->size_id = 2;
                break;
            case 30:
                $pet->size_id = 3;
                break;
            case 2:
            case 3:
            case 1:
                $pet->size_id;
                break;
            default:
                $pet->size_id = null;
                break;
        }

        if (!$pet->save()) {
            $errors = $pet->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении животного' : implode("\n", array_values($errors)));
        }

        return $pet;
    }

    /**
     * Печать амбулаторной карты
     *
     * @param int|int[] $visit_ids
     * @throws \yii\base\InvalidConfigException
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    public function createPdfCard(int $id_pet, $visit_ids)
    {
        /* @var $generator \app\common\components\pdfGenerator\PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');

        $query = 'SELECT visits.id FROM visits ';
        $query .= 'LEFT JOIN visit_pets ON visit_pets.id_visit=visits.id ';
        $query .= 'WHERE (visit_pets.id_pet=' . $id_pet . ' AND visits.status=\'F\') OR visits.id_pet=' . $id_pet . ' ORDER BY visits.time_range;';

        $visit_ids = [];
        $visits = \Yii::$app->db->createCommand($query)->queryAll();
        foreach ($visits as $row) {
            $visit_ids[] = $row['id'];
        }

        $card_data = $this->printPetCard($id_pet, $visit_ids);
        if (empty($card_data)) {
            return false;
        }

        $data = [
            'data' => $card_data['pet'],
            'visits' => $card_data['visit'],
            'his' => $card_data['history']['ex'],
            'historyowner' => $card_data['history']['current']['owner'],
            //'petService' => $card_data['servicePet'],
        ];

        try {
            [$dir, $filename, $ext] = $generator->createDocument('pets_card', $data);
        } catch (\Exception $e) {
            throw new ServerErrorHttpException($e->getMessage());
        }
        $dir = str_replace('\\', '/', $dir);
        /** @var FileService $fileService */
        $fileService = \Yii::$app->fileService;
        $path = $dir . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
        $hash = $fileService->generateHash($filename);
        $old = FileResource::find()
            ->andWhere(['entity_id' => $id_pet, 'entity_type' => $this->file_type])
            ->orderBy(['created' => SORT_DESC])
            ->one();

        try {
            $old_path = $old ? $old->path : null;
            $old_path ? $fileService->delete($this->file_type, $id_pet, $old_path) : null;
            $fileResource = new FileResource();
            $fileResource->hash = $hash;
            $fileResource->path = '/upload/pdf/' . $filename . '.' . $ext;
            $fileResource->name = $filename . '.' . $ext;
            $fileResource->entity_id = $id_pet;
            $fileResource->entity_type = $this->file_type;
            $fileResource->save();
            $fileService->attach($fileResource);
        } catch (\Throwable $e) {
            $fileService->repository->delete($path);
            throw new ServerErrorHttpException('Ошибка сохранения файла: ' . $e->getMessage());
        }

        return $fileResource;
    }

    /**
     * Возвращает животное с его связанными данными
     *
     * @param int $id
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getPet($id)
    {
        $result = Pets::find()
            ->alias('p')
            ->select([
                'p.*',
                // Подменяем кличку Питомца для данных неавторизованного пользователя mosru
                new Expression('CASE WHEN (tmpp.id IS NOT NULL) THEN tmpp.name ELSE p.name END AS "name"'),
            ])
            ->joinWith([
                'tmpPet' => function ($query) {
                    /** @var ActiveQuery $query */
                    $query->alias('tmpp');
                }
            ])
            ->with('elk_pet')
            ->with('reg_certificate')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
            ->with('brood')
            ->with('reg_expire_reason')
            ->with('organizations')
            ->with([
                'pets_to_owner' => function ($q) {
                    /* @var $q \yii\db\ActiveQuery */
                    $q->orderBy([
                        'id_owner_type' => SORT_ASC,
                    ]);
                }
            ])
            ->with('pets_to_owner.owner_type')
            ->with([
                'pets_to_owner.owner' => function ($query) {
                    /** @var ActiveQuery $query */
                    $query
                        ->alias('o')
                        ->select([
                            'o.*',
                            // Подменяем имя Владельца для данных неавторизованного пользователя mosru
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.f_fio ELSE o.f_fio END AS "f_fio"'),
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.i_fio ELSE o.i_fio END AS "i_fio"'),
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.o_fio ELSE o.o_fio END AS "o_fio"'),
                            new Expression('CASE WHEN (tmpo.id IS NOT NULL) THEN tmpo.fullname ELSE o.fullname END AS "fullname"'),
                        ])
                        ->joinWith([
                            'tmpOwner' => function ($query) {
                                /** @var ActiveQuery $query */
                                $query->alias('tmpo');
                            }
                        ]);
                }
            ])
            //->with('fias_address')
            //->with('pets_to_owner.owner.fias_addresses')
            ->with('shelter_records')
            ->with('shelter_records.organization')
            ->with('shelter_records.organization.contacts')
            ->with('files')
            ->where(['p.id' => $id])
            ->asArray()
            ->one();

        /*
         * Нам нужем bti_city_area_code как массив, приходиться использовать как модель, а не массив
         */
        $result['fias_address'] = !empty($result['id_fias_address']) ?
            FiasAddresses::findOne(['id' => $result['id_fias_address']]) : null;

        foreach ($result['pets_to_owner'] as &$owner) {
            // fias_address
            if (!empty($owner['owner']['id_fias_address'])) {
                $owner['owner']['fias_addresses'] = FiasAddresses::findOne(['id' => $owner['owner']['id_fias_address']]);
            } else {
                $owner['owner']['fias_addresses'] = null;
            }

            // fact_fias_address
            if (!empty($owner['owner']['id_fact_fias_address'])) {
                $owner['owner']['fact_fias_address'] = FiasAddresses::findOne(['id' => $owner['owner']['id_fact_fias_address']]);
            } else {
                $owner['owner']['fact_fias_address'] = null;
            }
        }
        unset($owner);

        return $result;
    }

    /**
     * Поиск животных для приемов
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return PetsList
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */


    public function listPetsVisits($page, $limit, $filter)
    {
        $filter = $this->validateFilter($filter);
        $query = Pets::find()
            ->with('pets_to_owner')
            ->with('pets_to_owner.owner_type')
            ->with('pets_to_owner.owner')
            ->with('pets_to_owner.owner.fias_addresses')
            ->with('pets_to_owner.owner.fact_fias_addresses')
            ->with([
                'pets_to_owner.owner.contacts' => function ($phones) {
                    /** @var ActiveQuery $fias_query * */
                    $phones
                        ->select(['contacts.*'])
                        ->innerJoin(
                            'contact_types phone_types',
                            'contacts.id_contact_type = phone_types.id AND phone_types.type = :contact_types_type',
                            [':contact_types_type' => ContactTypes::TYPE_PHONE]
                        )
                        ->where('contacts.entity_type = :entity_type', [':entity_type' => Contacts::ENTITY_TYPE_PET_OWNER]);
                }
            ])
            ->select(
                [
                    'public.pets.id',
                    new Expression('COALESCE(public.species.name, \'\') || \', \' || COALESCE(public.breeds.name, \'\') || \', \' || public.pets.name || \', Чип: \' || COALESCE(pet_idents.identification_code, \'\') AS pet_info'),
                ]
            )
            ->leftJoin("public.species", "pets.id_species =  species.id")
            ->leftJoin("public.breeds", "pets.id_breed=breeds.id")
            ->leftJoin('pet_identification AS pet_idents', 'pets.id = pet_idents.id_pet')
            ->leftJoin(
                'identification_types AS ident_types',
                'ident_types.id = pet_idents.id_ident_type AND ident_types.id = :ident_types_type',
                [':ident_types_type' => IdentificationTypes::findIdentificationTypeId('чип')]
            )
            ->where(['id_pet_tmp' => null]);

        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetsList(
            $query
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query
                ->limit(null)
                ->offset(null)
                ->count()
        );

        $result->customPagination($page, $limit);
        return $result;
    }

    /**
     * Поиск животных
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return PetsList
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */


    public function listPetsUniversal($page, $limit, $filter, $isAsc, $orderBy)
    {
        $filter = $this->validateFilter($filter);
        $query = Pets::find()
            ->with('species')
            ->with('breeds')
            ->with('brood')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('pets_to_owner')
            ->with('pets_to_owner.owner_type')
            ->with('pets_to_owner.owner')
            ->with('pets_to_owner.owner.fias_addresses')
            ->with(['fias_addresses' => function ($query) {
                /** @var $query ActiveQuery **/
                $query->select([
                    'id',
                    'full_address'
                ]);
            }])
            ->select(
                [
                    'public.pets.*',
                    "public.species.name AS species_name",
                    "public.breeds.name AS breeds_name",
                    "public.pet_owners.fullname AS owner_name",
                    "public.fias_addresses.full_address AS pet_address",
                ]
            )
            ->leftJoin("public.species", "pets.id_species =  species.id")
            ->leftJoin("public.breeds", "pets.id_breed=breeds.id")
            ->leftJoin("public.pets_to_owner", "public.pets.id=public.pets_to_owner.id_pet")
            ->leftJoin("public.pet_owners", "public.pets_to_owner.id_owner=public.pet_owners.id")
            ->leftJoin("public.fias_addresses", "public.pets.id_fias_address=public.fias_addresses.id")
            ->where(['id_pet_tmp' => null])
            ->orderBy("fias_addresses.full_address asc");

        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetsList(
            $query
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query
                ->limit(null)
                ->offset(null)
                ->count()
        );
        return $result;
    }


    public function listPetsNullif($page, $limit, $filter, $isAsc, $orderBy)
    {
        $filter = $this->validateFilter($filter);
        $query = Pets::find()
            ->with('species')
            ->with('breeds')
            ->with('brood')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('pets_to_owner')
            ->with('pets_to_owner.owner_type')
            ->with('pets_to_owner.owner')
            ->with('pets_to_owner.owner.fias_addresses')
            ->where(['id_pet_tmp' => null])
            ->orderBy([new Expression("NULLIF(pets.name, '') {$isAsc} NULLS LAST")]);

        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetsList(
            $query
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query
                ->limit(null)
                ->offset(null)
                ->count()
        );
        $result->customPagination($page, $limit);
        return $result;
    }


    public function listPets($page, $limit, $filter)
    {
        $filter = $this->validateFilter($filter);

        $query = Pets::find()
            ->with('species')
            ->with('breeds')
            ->with('brood')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with(['fias_addresses' => function ($query) {
                /** @var $query ActiveQuery **/
                $query->select([
                    'id',
                    'full_address'
                ]);
            }])
            ->with('pets_to_owner')
            ->with('pets_to_owner.owner_type')
            ->with('pets_to_owner.owner')
            ->with('pets_to_owner.owner.fias_addresses')
            ->with(['phoneContacts' => function ($phoneContacts) {
                /** @var ActiveQuery $phoneContacts * */
                $phoneContacts
                    ->select(['*']);
            }])
            ->orderBy('pets.name')
            ->where(['id_pet_tmp' => null]);

        // Применяем фильтры
        $query = $this->applyFilter($filter, $query);

        // Формируем ответ
        $result = new PetsList(
            $query
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->asArray()
                ->all(),
            $query
                ->limit(null)
                ->offset(null)
                ->count()
        );

        $result->customPagination($page, $limit);

        return $result;
    }

    /**
     * Печать амбулаторной карты животного
     *
     * @param $id_pet
     * @param int|int[] $visit_ids
     * @param array $filter
     * @return array|ActiveRecord|null
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function printPetCard(int $id_pet, $visit_ids)
    {
        $pets = Pets::find()
            ->with('reg_certificate')
            ->with('pet_main_identification')
            ->with('pet_main_identification.ident_type')
            ->with('species')
            ->with('breeds')
            ->with('last_pet_rabies_vaccinations')
            ->where(['id' => $id_pet])
            ->all();
        $spec_fullname = \Yii::$app->user->getIdentity()->specialist->fullname;
        $petData = [];
        foreach ($pets as $pet) {
            $petData[] = [
                'name' => $pet->name ?? 'Не указан',
                'species' => $pet->species->name ?? 'Не указан',
                'breed' => $pet->breeds->name ?? 'Не указан',
                'sex' => $pet->sex == 'm' ? 'м' : 'ж' ?? 'Не указан',
                'birthday' => DateHelper::ageAtDate($pet->birthday, date('Y-m-d H:i:s')) ?? 'Не указан',
                'ident_type' => $pet->pet_main_identification->ident_type->name ?? 'Не указан',
                'pet_ident' => $pet->pet_main_identification->identification_code ?? 'Не указан',
                'regnum' => $pet->reg_certificate->number ?? 'Не указан',
                'vaccination_date' => $pet->last_pet_rabies_vaccinations->date ?? 'Не указан',
                'spec_fullname' => $spec_fullname,
            ];
        }

        $visits = Visits::find()
            ->with('owner')
            ->with('pet')
            ->with('organization')
            ->with('visitsGovService')
            ->with('visitsGovService.service')
            ->with('visitsGovService.visitServiceParamValues')
            ->with('specialists')
            ->with('specialists.user')
            ->with('author_ref.user')
            ->with('anamnesis')
            ->with('clinicalSigns')
            ->with('preDiagnosis')
            ->with('finDiagnosis')
            ->with('treatment')
            ->with('assurance')
            ->with('recommendations')
            ->where(['id' => $visit_ids])
            ->andWhere(['!=', 'channel', '5'])
            ->orderBy(['start_dttm' => SORT_DESC])
            ->all();
        $vIds = implode(', ', $visit_ids);
        $services = Yii::$app->db->createCommand("SELECT id_visit as id, CONCAT(gov_services.cod, ' ', gov_services.name, ' ', visits_gov_services.price_with_discount) AS name FROM visits_gov_services
        LEFT JOIN gov_services ON visits_gov_services.id_service = gov_services.id
        WHERE visits_gov_services.id_visit IN ($vIds)")->queryAll();
        $visitServices = [];
        foreach($services as $service) {
            if (!isset($visitServices[$service['id']])) $visitServices[$service['id']] = $service['name'];
            else $visitServices[$service['id']] .= "<p>{$service['name']}";
        }
        $visitsData = [];
        foreach ($visits as $visit) {
            $visitsData[] = [
                'id' => $visit->id ?? '-',
                'start_date' => date('d.m.Y', strtotime(($visit->fact_start_dttm ?? $visit->start_dttm) ?? $visit->created_at)) ?? '-',
                'clinic' => $visit->organization->short_name ?? 'Не указан',
                'owner' => $visit->owner->fullname ?? 'Не указан',
                'spec' => $visit->specialists->user->fullname ?? 'Не указан',
                'anamnesis' => $visit->anamnesis->description ?? 'Не указан',
                'clinicalSigns' => $visit->clinicalSigns->description ?? 'Не указан',
                'preDiagnosis' => $visit->preDiagnosis->description ?? 'Не указан',
                'finDiagnosis' => $visit->finDiagnosis->description ?? 'Не указан',
                'treatment' => $visit->treatment->description ?? 'Не указан',
                'assurance' => $visit->assurance->description ?? 'Не указан',
                'recommendations' => $visit->recommendations->description ?? ' ',
                'serv' => $visitServices[$visit->id] ?? '-',
                //'serv' => $visit->visitsGovService->service->name ?? 'Нет услуг',
                'pricesum' => PdfVisitGeneratorHelper::getPriceSum(new BillModel($visit->id, null, null, null, false, $id_pet)) ?? '-',
                'reportName' => $visit->visitsGovService->service->name ?? 'Нет услуг',
                'nameIndicator' => $visit->visitsGovService->service->briefname ?? 'Нет данных',
                'indicatorData' => $visit->visitsGovService->visitServiceParamValues->num_value ?? 'Нет данных',
                'indicatorEd' => $visit->visitsGovService->visitServiceParamValues->char_value ?? ' ',
            ];

            $pet = \app\models\db\VisitsGovServices::find()
                ->with('service')
                ->where([
                    'id_pet' => $id_pet,
                    'id_visit' => $visit_ids
                ])
                ->all();

            $petsData = [];
            foreach ($pet as $price) {
                $petsData[] = [
                    'cod' => $price->service->cod ?? '-',
                    'count' => $price->count ?? '-',
                    'services' => $price->service->name ?? '-',
                    'price' => $price->service->price ?? '-',
                ];
            }
        }
        $currentOwners = PetsToOwner::find()
            ->with('owner')
            ->with('owner.fias_addresses')
            ->with('owner.fact_fias_addresses')
            ->with('owner.phoneMainContact')
            ->with('owner.emailMainContact')
            ->where(['pets_to_owner.id_pet' => $id_pet])
            ->orderBy(['id_owner_type' => SORT_ASC])
            ->all();

        $owner = $currentOwners[0]->owner;

        $ownerFields = [
            'date' => date('d.m.Y', strtotime($owner->created_at)),
            'name' => $owner->fullname,
            'addr' => $owner->fias_addresses->full_address ?? 'Не указан',
            'fact_addr' => $owner->fact_fias_addresses->full_address ?? 'Не указан',
            'phone' => $owner->phoneMainContact->name ?? 'Не указан',
            'email' => $owner->emailMainContact->name ?? 'Не указан',
        ];

        $entrepreneurs = [];
        foreach ($currentOwners as $entrep) {
            if ($entrep->id_owner_type == 2) {
                $entrepFields = [];
                $entrepFields['name'] = $entrep->owner->fullname;
                $entrepFields['phone'] = $entrep->owner->phoneMainContact->name ?? 'Не указан';
                $entrepFields['email'] = $entrep->owner->emailMainContact->name ?? 'Не указан';
                $entrepreneurs[] = $entrepFields;
            }
        }

        $exHistory = PetOwnersHistory::find()
            ->with('owner')
            ->with('owner.fact_fias_addresses')
            ->with('owner.fias_addresses')
            ->with('owner.phoneMainContact')
            ->with('owner.emailMainContact')
            ->where([
                'id_pet' => $id_pet,
                'owner_type_new' => 'Владелец'
            ])
            ->orderBy(['date' => SORT_DESC])
            ->all();

        $ex = [];
        foreach ($exHistory as $record) {
            $ex[] = [
                'date' => date('d.m.Y', strtotime($record->date)) ?? '??',
                'name' => $record->owner_name,
                'addr' => $record->owner->fias_addresses->full_address ?? 'Не указан',
                'fact_addr' => $record->owner->fact_fias_addresses->full_address ?? 'Не указан',
                'phone' => $record->owner->phoneMainContact->name ?? 'Не указан',
                'email' => $record->owner->emailMainContact->name ?? 'Не указан'
            ];
        }

        $historyData = [
            'current' => [
                'owner' => $ownerFields,
                'entrepreneurs' => $entrepreneurs
            ],
            'ex' => $ex,
        ];
        $petInfo = [
            'pet' => $petData,
            'visit' => $visitsData,
            'history' => $historyData,
            //  'servicePet' => $petsData,
        ];

        return $petInfo;
    }

    /**
     * Применяет фильтры для v2/pets/pet/list и джоинит нужные таблицы
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
                ['pets.is_main' => true],
                ['pets.is_main' => null]
            ]);

            return $query;
        }

        if (!empty($filter['from'])) {
            $query->andWhere(['>=', new Expression('"visits"."start_dttm"::date'), $filter['from']]);
        }

        if (!empty($filter['to'])) {
            $query->andWhere(['<=', new Expression('"visits"."start_dttm"::date'), $filter['to']]);
        }

        if (!empty($filter['id_organization'])) {
            $query->andWhere(['visits.id_organization' => $filter['id_organization']]);
        }

        if (!empty($filter['id_specialist'])) {
            $query->innerJoin('visits_specialists vs', 'vs.id_visit = visits.id');
            $query->innerJoin('specialists s', 's.id = vs.id_specialist');
            $query->andWhere(['s.id' => $filter['id_specialist']]);
        }

        if (!empty($filter['service_name']) || !empty($filter['service_code']) || !empty($filter['id_service_type'])) {
            $query->innerJoin('visits_gov_services vgs', 'vgs.id_visit = visits.id');
            $query->innerJoin('gov_services gs', 'gs.id = vgs.id_service');
        }

        if (!empty($filter['service_name'])) {
            $query->andWhere(['ilike', 'gs.name', $filter['service_name']]);
        }

        if (!empty($filter['service_code'])) {
            $query->andWhere(['gs.cod' => $filter['service_code']]);
        }

        if (!empty($filter['id_service_type'])) {
            $query->andWhere(['gs.id_service_type' => $filter['id_service_type']]);
        }

        /**
         * Фильтрация по полям животного
         */
        if (!empty($filter['id_breed'])) {
            $query->andWhere(['pets.id_breed' => $filter['id_breed']]);
        }

        if (!empty($filter['id_species'])) {
            $query->andWhere(['pets.id_species' => $filter['id_species']]);
        }

        if (!empty($filter['id_reg_organization'])) {
            $query->andWhere(['pets.id_reg_organization' => $filter['id_reg_organization']]);
        }

        if (!empty($filter['is_reg_number'])) {
            $query->leftJoin('reg_certificates', 'pets.id = reg_certificates.id_pet');
            // Имеется
            if ($filter['is_reg_number'] == 2){
                $query->andWhere('reg_certificates.number IS NOT NULL');
            }
            // Не Имеется
            if ($filter['is_reg_number'] == 3){
                $query->andWhere('reg_certificates.number IS NULL');
            }
        }

        if (!empty($filter['is_reg_zayavka'])) {

            // Имеется
            if ($filter['is_reg_zayavka'] == 2) {
                $query->leftJoin('files', 'pets.id = files.entity_id');
                $query->andWhere(['files.entity_type' => 'reg_application']);
            }

            // Не Имеется
            if ($filter['is_reg_zayavka'] == 3) {
                // Подзапрос для получения идентификаторов владельцев с entity_type = reg_application
                $excludedIdsSubquery = (new \yii\db\Query())
                    ->select('pets.id')
                    ->from('pets')
                    ->leftJoin('files', 'pets.id = files.entity_id')
                    ->where(['files.entity_type' => 'reg_application']);
                $excludedIds = $excludedIdsSubquery->column();

                $query->andWhere(['not in', 'pets.id', $excludedIds]);
            }
        }

        if (!empty($filter['pet_name'])) {
            $query->andWhere(['ILIKE', 'pets.name', $filter['pet_name']]);
        }

        if (!empty($filter['pet_sex'])) {
            $query->andWhere(['pets.sex' => $filter['pet_sex']]);
        }

        /**
         * Фильтрация по полям владельца
         * Если в запросе указаны данные поля - нам потребуется таблица pet_owners
         * отфильтрованная по владельцам
         */
        if (
            !empty($filter['owner_name']) || !empty($filter['owner_name']) || !empty($filter['phone']) || !empty($filter['id_area']) ||
            !empty($filter['ogrn']) || !empty($filter['inn']) || !empty($filter['snils']) || !empty($filter['address']) || !empty($filter['id_district']) ||
            isset($filter['is_legal']) || !empty($filter['id_owner']) || isset($filter['entrepreneur'])
        ) {

            $query
                ->innerJoin('pets_to_owner AS pto', 'pto.id_pet = pets.id')
                ->innerJoin(
                    'pet_owners AS po',
                    'pto.id_owner = po.id'
                );

            $query->distinct(true);
        }

        if (isset($filter['entrepreneur']) && isset($filter['is_legal'])) {
            $query->andWhere([
                'po.entrepreneur' => $filter['entrepreneur'],
                'po.is_legal' => $filter['is_legal']
            ]);
        }

        if (!empty($filter['id_owner'])) {
            $query->andWhere(['pto.id_owner' => $filter['id_owner']]);
        }

        if (!empty($filter['ogrn'])) {
            $query->andWhere(['po.ogrn' => $filter['ogrn']]);
        }

        if (!empty($filter['snils'])) {
            $query->andWhere(['po.snils' => $filter['snils']]);
        }

        if (!empty($filter['inn'])) {
            $query->andWhere(['po.inn' => $filter['inn']]);
        }

        if (!empty($filter['id_area'])) {
            $query->andWhere(['po.id_area' => $filter['id_area']]);
        }

        if (!empty($filter['id_district'])) {
            $query->andWhere(['po.id_district' => $filter['id_district']]);
        }

        if (array_key_exists('is_legal', $filter) && $filter['is_legal'] !== null) {
            $query->andWhere(['po.is_legal' => $filter['is_legal']]);
        }

        if (!empty($filter['owner_name'])) {
            $query->andWhere([
                'OR',
                ['ILIKE', 'po.fullname', $filter['owner_name']],
                ['ILIKE', 'po.jur_name', $filter['owner_name']],
            ]);
        }

        /**
         * Потребуются еще таблицы contacts и contact_types
         */
        if (!empty($filter['phone'])) {
            if (mb_strlen($filter['phone']) >= 4) {
                $query->innerJoin(
                    'contacts',
                    'contacts.entity_type = :entity_type AND contacts.entity_id = po.id',
                    [':entity_type' => Contacts::ENTITY_TYPE_PET_OWNER]
                )->innerJoin(
                    'contact_types',
                    'contacts.id_contact_type = contact_types.id AND contact_types.type = :contact_types_type',
                    [':contact_types_type' => ContactTypes::TYPE_PHONE]
                );

                $query
                    ->andWhere(['ILIKE', 'contacts.name', '+' . $filter['phone'] . '%', false]);
            } else {
                throw new BadRequestHttpException('Номер телефона должен содержать не менее трех цифр');
            }
        }
        /**
         * Фильтрация по адресу животного
         * Нужна таблица fias_addresses
         */
        if (!empty($filter['address_animal'])) {
            $query->innerJoin('fias_addresses', 'pets.id_fias_address = fias_addresses.id');
            $query->andWhere(['ILIKE', 'fias_addresses.full_address', $filter['address_animal']]);
        }

        /**
         * Фильтрация по адресу владельца
         * Нужна таблица fias_addresses
         */
        if (!empty($filter['address'])) {
            $query->innerJoin('fias_addresses', 'po.id_fias_address = fias_addresses.id');
            $query->andWhere(['ILIKE', 'fias_addresses.full_address', $filter['address']]);
        }

        /**
         * Фильтрация по бирке или чипу
         */
        if (!empty($filter['identification_code']) || !empty($filter['id_ident_type'])) {
            $query->innerJoin('pet_identification AS pi', 'pets.id = pi.id_pet');
        }

        if (!empty($filter['identification_code'])) {
            $query->andWhere(['pi.identification_code' => $filter['identification_code']]);
        }

        if (!empty($filter['id_ident_type'])) {
            $query->andWhere(['pi.id_ident_type' => $filter['id_ident_type']]);
        }

        if (isset($filter['only_duplicates']) && $filter['only_duplicates'] === true) {
            $query->andWhere(['pets.is_main' => false]);
        } else {
            $query->andWhere([
                'or',
                ['pets.is_main' => true],
                ['pets.is_main' => null]
            ]);
        }

        if (isset($filter['only_expired']) && $filter['only_expired'] === true) {
            $query->andWhere([
                'AND',
                ['not', ['pets.id_reg_expire_reason' => null]],
                ['not', ['pets.reg_expire_date' => null]],
            ]);
        } else {
            $query->andWhere([
                'AND',
                ['pets.id_reg_expire_reason' => null],
                ['pets.reg_expire_date' => null],
            ]);
        }

        if (isset($filter['is_expired'])){
			if($filter['is_expired'] === true) {
				$query->andWhere([
					'AND',
					['not', ['pets.reg_expire_date' => null]],
				]);
			} else {
				$query->andWhere([
					'AND',
					['pets.reg_expire_date' => null],
				]);
			}
		}

        return $query;
    }

    /**
     * Валидирует фильтр для v2/pets/pet/list
     *
     * @param array $filter
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateFilter($filter)
    {
        if (empty($filter)) {
            return;
        }
        $empty_filter = [
            'id_breed' => null,
            'id_species' => null,
            'id_ident_type' => null,
            'identification_code' => null,
            'is_legal' => null,
            'phone' => null,
            'owner_name' => null,
            'inn' => null,
            'ogrn' => null,
            'snils' => null,
            'address' => null,
            'id_reg_organization' => null,
            'id_area' => null,
            'id_district' => null,
            'id_owner' => null,
            'pet_name' => null,
            'only_duplicates' => null,
            'only_expired' => null,
            'entrepreneur' => null,
            'id_organization' => null,
            'pet_sex' => null,
            'is_expired' => null,
        ];

        $filter = array_merge($empty_filter, $filter);

        $rules = [
            [['id_breed', 'id_species', 'id_ident_type', 'id_reg_organization', 'id_area', 'id_district', 'id_owner'], 'integer'],
            [['phone', 'owner_name', 'address', 'ogrn', 'snils', 'inn', 'pet_name'], 'string'],
            [['owner_name', 'address', 'pet_name'], FullTrimValidator::class],
            [['entrepreneur', 'is_legal', 'only_duplicates', 'only_expired', 'is_expired'], 'boolean'],
        ];

        $model = DynamicModel::validateData($filter, $rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $model->attributes;
    }

    /**
     * @param int $id_owner
     * @param int $id_species
     * @param string $name
     * @param int $id_breed
     * @param int $id_owner_type
     * @param int $id
     * @return array
     */
    public function suggestDuplicates(
        $id_owner,
        $id_species,
        $name = null,
        $optionalFielsdHash
        //        ,
        //        $id_breed = null,
        //        $id_owner_type = null,
        //        $id = null
    ) {
        $filterEscapes = new PGFilterEscapesValidator();
        $filterQuotes = new PGFilterQuotesValidator();

        $name = $filterEscapes->validate($name);

        // не будем искать по $id_breed - дубли могут быть и без породы и наоборот
        // не будем искать по $id_owner_type - дубли могут быть заведены как на владельца, так и на представителя

        $query = Pets::find()
            ->alias('p')
            ->with('reg_certificate')
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
            // ->with('reg_expire_reason')
            ->innerJoin(PetsToOwner::tableName() . ' pto', 'pto.id_pet = p.id')
            ->where([
                'pto.id_owner' => $id_owner,
                'p.id_species' => $id_species,
                'p.id_reg_expire_reason' => null,
            ])
            ->asArray();

        $query->orderBy([
            'p.id_species' => SORT_ASC,
            'p.id' => SORT_ASC,
        ]);

        if (empty($id)) {
            $query->andWhere([
                'or',
                ['p.is_main' => true],
                ['p.is_main' => null],
            ]);
        } else {
            $pet = Pets::findOne(['id' => $id]);
            if ($pet->is_main === false) {
                return [];
            } elseif ($pet->is_main === true) {
                $query->andWhere(['p.is_main' => null]);
            } else {
                $query->andWhere([
                    'or',
                    ['p.is_main' => true],
                    ['p.is_main' => null],
                ]);
            }
        }

        //        if (!empty($name)) {
        //            // При длине слова до 3 символов используем точное совпадение (без учета регистра)
        //            // При длине слова от 4 символов - trgm
        //            $name_condition = (mb_strlen($name) <= 3)
        //                ? ['ilike', 'p.name', $name, false]
        //                : new Expression('p.name % \'' . $filterQuotes->validate($name) . '\'');
        //            $query->andWhere([
        //                'or',
        //                $name_condition,
        //                ['p.name' => null],
        //                ['p.name' => ''],
        //            ]);
        //        }
        if (!empty($name)) {
            // При длине слова до 3 символов используем точное совпадение (без учета регистра)
            // При длине слова от 4 символов - trgm
            //$name_condition = ['=', 'p.name', $name];
            $query->andWhere([
                '=', 'p.name', $name
            ]);
        }

        if (!empty($id)) {
            $query->andWhere(['!=', 'p.id', $id]);
        }

        \Yii::$app->db
            ->createCommand('SET pg_trgm.similarity_threshold = 0.5')
            ->execute();

        $arr = $query->all();
        $res = [];
        foreach ($arr as $r) {
            $check = [
                "id_breed" => $r["id_breed"] ?? null,
                "description" => $r["description"] ?? null,
                "sex" => $r["sex"] ?? null,
                "guide_dog" => $r["guide_dog"] ?? null,
                "castrated" => $r["castrated"] ?? null,
                "birthday" => $r["birthday"] ?? null,
            ];
            $checkFielsdHash = mb_strtoupper(md5(implode('|', $check)));
            if ($checkFielsdHash == $optionalFielsdHash) {
                $res[] = $r;
            }
        }
        return $res;
    }

    /**
     * Поиск ПРИВЯЗАННЫХ дублей животного (вкладка “Дублирующие записи” в карточке животного)
     *
     * @param int $id
     * @return array
     */
    public function findDuplicates($id)
    {
        $pet = Pets::findOne(['id' => $id]);

        if ($pet->is_main !== true) {
            return [];
        }

        $query = $pet->getDuplicates()
            ->with('pet_identification')
            ->with('pet_identification.ident_type')
            ->with('species')
            ->with('breeds')
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

        return $query->all();
    }
}
