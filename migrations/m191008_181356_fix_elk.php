<?php

use app\commands\migrate\Migration;
use app\models\db\elk\ElkPets;
use yii\helpers\Console;

/**
 * Class m191008_181356_fix_elk
 */
class m191008_181356_fix_elk extends Migration
{
    /**
     * @return bool|void
     * @throws Throwable
     */
    public function up()
    {
        return;
        $data = $this->parseCsv(__DIR__ . '/data/pets_elk.csv');
        $c = 0;
        foreach ($data as $owner) {
            try {
                if (!empty($owner['pet_chip_err'])) {
                    if (empty($owner['pet_chip'])) {
                        $this->deleteChip($owner['pet_ext_id'], $owner['pet_chip_err']);
                    } else {
                        $this->updateChip($owner['pet_ext_id'], $owner['pet_chip'], $owner['pet_chip_err']);
                    }
                }
            } catch (Exception $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            }
        }
    }

    /**
     * @param $ext_id
     * @param $chipErr
     * @throws Exception
     */
    protected function deleteChip($ext_id, $chipErr)
    {
        Console::output(Console::ansiFormat("Удаление чипа {$chipErr} для животного {$ext_id}", [Console::FG_YELLOW]));
        if (!$elkPet = ElkPets::findOne(['ext_id' => $ext_id])) {
            throw new Exception("не найдено животное {$ext_id} в elk.pets");
        }
        if (!$pet = $elkPet->pet) {
            Console::output(Console::ansiFormat("Не найдено животное в pets", [Console::FG_RED]));
        } else {
            $command = $this->db->createCommand(
                "delete from pet_identification where id_pet = :id_pet and identification_code = :chip", [
                    ':id_pet' => $pet->id,
                    ':chip' => $chipErr
                ]
            );
            //$command->execute();
            print_r($command->rawSql);
        }
        //$elkPet->chip = null;
        //$elkPet->save(false);
    }

    /**
     * @param $ext_id
     * @param $chip
     * @param $chipErr
     * @throws Exception
     */
    protected function updateChip($ext_id, $chip, $chipErr)
    {
        Console::output(Console::ansiFormat("Обновление чипа {$chipErr} для животного {$ext_id}: {$chip}", [Console::FG_YELLOW]));
        if (!$elkPet = ElkPets::findOne(['ext_id' => $ext_id])) {
            throw new Exception("не найдено животное {$ext_id} в elk.pets");
        }
        if (!$pet = $elkPet->pet) {
            Console::output(Console::ansiFormat("Не найдено животное в pets", [Console::FG_RED]));
        } else {
            $command = $this->db->createCommand(
                "update pet_identification set identification_code = :chip where id_pet = :id_pet and identification_code = :chip_err", [
                    ':chip' => $chip,
                    ':id_pet' => $pet->id,
                    ':chip_err' => $chipErr
                ]
            );
            //$command->execute();
            print_r($command->rawSql);
        }
        //$elkPet->chip = $chip;
        //$elkPet->save(false);
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

            if (count($rows) > 20)  {
                continue;
            }

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
            $chipErr = $rows[19];

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
                'pet_chip' => $chip,
                'pet_chip_err' => $chipErr
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
