<?php

use app\commands\migrate\Migration;
use app\common\helpers\PhoneHelper;
use app\models\db\elk\ElkOwners;
use app\models\db\elk\ElkPets;
use app\models\db\PetIdentification;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\modules\elk\exceptions\ELKException;
use app\modules\elk\models\db\PetOwners;
use app\modules\elk\models\PetsHandler;
use yii\helpers\Console;

/**
 * Class m191008_072725_load_elk_data
 */
class m191008_072725_load_elk_data extends Migration
{
    /**
     * @return bool|void
     * @throws Throwable
     */
    public function up()
    {
        // нужно только для мастера, на других контурах отключаем
        return true;
        $data = $this->parseCsv(__DIR__ . '/data/pets_elk.csv');
        $total = count($data);
        $c = 0;
        foreach ($data as $owner) {
            $c++;
            try {
                $this->db->transaction(function() use($owner, $c, $total){
                    $animal = [
                        'ext_id' => $owner['pet_ext_id'],
                        'name' => $owner['pet_name'],
                        'sex' => $owner['pet_sex'],
                        'id_species' => $owner['pet_species_id'],
                        'id_breed' => $owner['pet_breed_id'],
                        'birthday' => $owner['pet_birthday'],
                        'chip' => $owner['pet_chip']
                    ];

                    $p = round($c * 100 / $total);
                    Console::output(Console::ansiFormat(
                        "{$c}/{$total} {$p}%) {$owner['f_fio']} {$owner['i_fio']} {$owner['o_fio']} {$owner['sso_id']}",
                        [Console::FG_GREEN])
                    );

                    if ($elkOwner = $this->getElkOwner($owner['sso_id'])) {
                        Console::output(Console::ansiFormat("Пользователь добавлен ранее через ЕЛК", [Console::FG_YELLOW]));
                    } else {
                        $elkOwner = $this->createElkOwner($owner);
                    }

                    if (!$petOwner = $elkOwner->owner) {
                        if (!$petOwner = $this->findOwner($owner)) {
                            $petOwner = $this->createPetOwner($owner);
                        } elseif ($petOwner->sso_id != $owner['sso_id']) {
                            Console::output(Console::ansiFormat("Найден пользователь в pet_owners, обновляем sso_id", [Console::FG_YELLOW]));
                            $petOwner->sso_id = $owner['sso_id'];
                            $petOwner->save(false);
                        }

                        $elkOwner->id_owner = $petOwner->id;
                        $elkOwner->save(false);
                    }

                    if ($elkPet = $this->findElkPet($animal['ext_id'])) {
                        // если у животного были приемы - данные не редактируем
                        if ($elkPet->hasVisits() || $elkPet->hasViolations()) {
                            Console::output(Console::ansiFormat("Не обновляем данные животного (имеются приемы или нарушения)", [Console::FG_YELLOW]));
                            return;
                        }
                        $pet = $elkPet->pet;
                        $this->updatePet($pet, $animal);
                        $this->updateElkPet($elkPet, $animal);
                    } else {
                        $pet = $this->getPet($animal);
                        $this->linkPetAndOwner($pet, $petOwner);
                        $this->saveElkPet($pet, $animal, $elkOwner);
                    }
                });

                //usleep(100000);
            } catch (Exception $e) {
                Console::error(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                $this->insert('elk.conflicts', [
                    'sso_id' => $owner['sso_id'],
                    'ext_id' => $owner['pet_ext_id'],
                    'data' => $owner,
                    'error' => $this->db->quoteSql($e->getMessage())
                ]);
            }
        }
    }

    /**
     * @param $sso_id
     * @return ElkOwners|null
     */
    protected function getElkOwner($sso_id)
    {
        return ElkOwners::findOne(['sso_id' => $sso_id]);
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
     * @param $animal
     * @return Pets|array|bool|\yii\db\ActiveRecord|null
     * @throws ELKException
     */
    protected function getPet($animal)
    {
        $pet = false;
        if ($animal['chip']) {
            $pet = Pets::find()
                ->joinWith('pet_identification', true, 'INNER JOIN')
                ->where([
                    'pet_identification.identification_code' => $animal['chip'],
                    'pet_identification.id_ident_type' => 1 // чип
                ])
                ->one();
        }

        if (!$pet) {
            $pet = $this->createPet($animal);
        }

        return $pet;
    }

    /**
     * @param Pets $pet
     * @param $animal
     * @param ElkOwners $elkOwner
     * @throws ELKException
     */
    protected function saveElkPet(Pets $pet, $animal, ElkOwners $elkOwner)
    {
        Console::output(Console::ansiFormat("Сохраняем данные питомца в ЕЛК", [Console::FG_YELLOW]));
        $data = [
            'id_pet' => $pet->id,
            'ext_id' => $animal['ext_id'],
            'id_elk_owner' => $elkOwner->id,
            'id_pet_owner' => $elkOwner->id_owner
        ];
        $elkPet = new ElkPets(array_merge($data, $animal));

        if (!$elkPet->save()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $elkPet->getErrorSummary(true),
                "Не удалось сохранить профиль животного в ЕЛК:"
            ));
        }
    }

    /**
     * @param Pets $pet
     * @param PetOwners $owner
     */
    protected function linkPetAndOwner(Pets $pet, $owner)
    {
        Console::output(Console::ansiFormat("Сохраняем связь владельца и питомца", [Console::FG_YELLOW]));
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
    }
    /**
     * @param PetOwners|\app\models\db\PetOwners $owner
     * @param $sso_id
     */
    protected function saveOwnerSsoId(PetOwners $owner, $sso_id)
    {
        if (!$owner->sso_id) {
            Console::output(Console::ansiFormat("Сохраняем sso_id у пользователя", [Console::FG_YELLOW]));
            $owner->sso_id = $sso_id;
            $owner->save(false);
        } else {
            Console::output(Console::ansiFormat("Обновляем sso_id у пользователя", [Console::FG_YELLOW]));
            $owner->sso_id = $sso_id;
            $owner->save(false);
        }
    }

    /**
     * @param $owner
     * @return ElkOwners
     * @throws ELKException
     */
    protected function createElkOwner($owner)
    {
        Console::output(Console::ansiFormat("Сохраняем пользователя в ЕЛК", [Console::FG_YELLOW]));
        $elkOwner = new ElkOwners([
            'sso_id' => $owner['sso_id'],
            'first_name' => $owner['i_fio'],
            'last_name' => $owner['f_fio'],
            'middle_name' => $owner['o_fio'],
            'snils' => $owner['snils'],
            'phone' => $owner['phone'],
            'email' => $owner['email']
        ]);

        if (!$elkOwner->validate()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $elkOwner->getErrorSummary(true),
                "Ошибка при сохранении профиля владельца в ЕЛК:"
            ));
        }

        $elkOwner->save(false);

        return $elkOwner;
    }

    /**
     * @param $owner
     * @return PetOwners|array|bool|\yii\db\ActiveRecord|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function findOwner($owner)
    {
        if (!empty($this->owner->Snils) &&
            $petOwner = PetOwners::find()->active()->bySnils($owner['snils'])->one()
        ) {
            return $petOwner;
        }

        /** @var PetOwners $petOwner */
        $query = PetOwners::find()
            ->active()
            ->byFio(
                $owner['f_fio'],
                $owner['i_fio'],
                $owner['o_fio']
            );

        if (!empty($owner['phone'])) {
            $query->byMobilePhone(PhoneHelper::extractMosRuPhoneNumber($owner['phone']));
        } elseif (!empty($owner['email'])) {
            $query->byEmail($owner['email']);
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
     * @param $owner
     * @return PetOwners
     * @throws ELKException
     */
    protected function createPetOwner($owner)
    {
        Console::output(Console::ansiFormat("Сохраняем пользователя в pet_owners", [Console::FG_YELLOW]));
        $owner = new PetOwners([
            'f_fio' => $owner['f_fio'],
            'i_fio' => $owner['i_fio'],
            'o_fio' => $owner['o_fio'],
            'sso_id' => $owner['sso_id']
        ]);

        if (!empty($owner['snils'])) {
            $owner->snils = $owner['snils'];
        }

        if (!$owner->validate()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $owner->getErrorSummary(true),
                "Ошибка при сохранении владельца:"
            ));
        }

        $owner->save();
        return $owner;
    }

    /**
     * @param $animal
     * @return Pets
     * @throws ELKException
     */
    protected function createPet($animal)
    {
        Console::output(Console::ansiFormat("Сохраняем животное в pets", [Console::FG_YELLOW]));
        $pet = new Pets();
        $data = [
            'id_breed' => $animal['id_breed'],
            'id_species' => $animal['id_species'],
            'name' => $animal['name'],
            'sex' => $animal['sex'],
            'birthday' => $animal['birthday']
        ];
        if (!$pet->load($data, '') || !$pet->save()) {
            throw new ELKException(PetsHandler::prepareErrors(
                $pet->getErrorSummary(true),
                "Ошибка создания животного:"
            ));
        }

        if (!empty($animal['chip'])) {
            $this->savePetIdentification($pet, $animal['chip']);
        }

        return $pet;
    }


    /**
     * @param Pets $pet
     * @param $animal
     * @throws ELKException
     */
    protected function updatePet(Pets $pet, $animal)
    {
        Console::output(Console::ansiFormat("Обновляем данные питомца в pets", [Console::FG_YELLOW]));
        $pet->setAttributes([
            'id_breed' => $animal['id_breed'],
            'id_species' => $animal['id_species'],
            'name' => $animal['name'],
            'sex' => $animal['sex'],
            'birthday' => $animal['birthday']
        ]);

        if ($pet->getDirtyAttributes()) {
            if (!$pet->save()) {
                throw new ELKException(PetsHandler::prepareErrors(
                    $pet->getErrorSummary(true),
                    "Не удалось обновить данные по питомцу {$pet->name}:"
                ));
            }
        }

        if (!empty($animal['chip'])) {
            $identification = PetIdentification::findOne([
                'id_pet' => $pet->id,
                'id_ident_type' => 1,
                'identification_code' => $animal['chip']
            ]);
            if (!$identification) {
                $this->savePetIdentification($pet, $animal['chip']);
            }
        }
    }

    /**
     * @param ElkPets $pet
     * @param $animal
     */
    protected function updateElkPet(ElkPets $pet, $animal)
    {
        Console::output(Console::ansiFormat("Сохраняем пользователя в ЕЛК", [Console::FG_YELLOW]));
        $pet->setAttributes($animal);
        if ($pet->getDirtyAttributes() && $pet->validate()) {
            $pet->save(false);
        }
    }

    /**
     * @param Pets $pet
     * @param $chip
     */
    protected function savePetIdentification(Pets $pet, $chip)
    {
        Console::output(Console::ansiFormat("Сохраняем чип", [Console::FG_YELLOW]));
        $petIdentification = new PetIdentification();
        $petIdentification->id_pet = $pet->id;
        $petIdentification->id_ident_type = 1;
        $petIdentification->identification_code = $chip;
        $petIdentification->main_flag = true;
        $petIdentification->save(false);
    }

    /**
     * @param $filePath
     * @return array
     * @throws Exception
     */
    protected function parseCsv($filePath)
    {
        $fp = fopen($filePath, 'r');
        if ($fp === false) {
            throw new Exception("Не удалось открыть файл {$filePath}");
        }
        $result = [];
        while (($rows = fgetcsv($fp, 1024, ';')) !== false) {
            /*
1 SSO_ID - иденитификатор пользоваель
2 SURNAME - Фамилия владельца
3 NAME - Имя владельца
4 PATRONIMYC - отчество владельца
5 MP_PHONE - телефон владельца
6 SNILS - СНИЛС владельца
7 EMAIL - email владельца
8 PET_ID - идентификатор питомца (ITEM_ID в БД ЛК)
9 PET_NAME - кличка питомца
10 date_PET_NAME - дата редактирования клички (ГГГГ-ММ-ДДTЧЧ:СС)
11 PET_GENDER - пол питомца
12 date_PET_GENDER - дата редактирования пола (ГГГГ-ММ-ДДTЧЧ:СС)
13 PET_SPECIES - код вида животного
14 date_PET_SPECIES - дата редактирования вида (ГГГГ-ММ-ДДTЧЧ:СС)
15 PET_BREED - порода
16 date_PET_BREED - дата редактирования породы (ГГГГ-ММ-ДДTЧЧ:СС)
17 PET_BIRTHDATE - дата рождения животного
18 date_PET_BREED - дата редактирования ДР (ГГГГ-ММ-ДДTЧЧ:СС)
19 PET_CHIP_NUMBER - номер чипа
20 date_PET_CHIP_NUMBER - дата редактирования чипа (ГГГГ-ММ-ДДTЧЧ:СС)
             */
            $ssoId = $rows[0];
            $fFio = $rows[1];
            $iFio = $rows[2];
            $oFio = $rows[3];
            $phone = $rows[4];
            $snils = $rows[5];
            $email = $rows[6];
            $extId = $rows[7];
            $petName = $rows[8];
            $petSex = $rows[10];
            $speciesId = $rows[12];
            $breeedId = $rows[14];
            $petBirthday = date('Y-m-d', strtotime($rows[16]));
            $chip = $rows[18];

            /*
            if (!isset($result[$ssoId])) {
                $result[$ssoId] = [
                    'sso_id' => $ssoId,
                    'f_fio' => $fFio,
                    'i_fio' => $iFio,
                    'o_fio' => $oFio,
                    'phone' => $phone,
                    'shils' => $shils,
                    'email' => $email,
                    'pets' => []
                ];
            }

            if (!isset($result[$ssoId]['pets'][$extId])) {
                $result[$ssoId]['pets'][$extId] = [
                    'ext_id' => $extId,
                    'name' => $petName,
                    'sex' => $petSex,
                    'species_id' => $speciesId,
                    'breed_id' => $breeedId,
                    'birthday' => $petBirthday,
                    'chip' => $chip
                ];
            } else {
                throw new Exception("Животное {$extId} уже объявлено ранее");
            }
            */
            $result[] = [
                'sso_id' => $ssoId,
                'f_fio' => $fFio,
                'i_fio' => $iFio,
                'o_fio' => $oFio,
                'phone' => $phone,
                'snils' => $snils,
                'email' => $email,
                'pet_ext_id' => $extId,
                'pet_name' => $petName,
                'pet_sex' => $petSex,
                'pet_species_id' => $speciesId,
                'pet_breed_id' => $breeedId,
                'pet_birthday' => $petBirthday,
                'pet_chip' => $chip
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}

