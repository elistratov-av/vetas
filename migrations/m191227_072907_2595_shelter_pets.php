<?php

use app\commands\migrate\Migration;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\Organizations;
use app\models\db\OrgTypes;
use app\models\db\PetIdentification;
use app\models\db\Pets;
use app\models\db\ShelterGuests;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m191227_072907_2595_shelter_pets
 */
class m191227_072907_2595_shelter_pets extends Migration
{
    private $logSuffix = '';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->logSuffix = time();

        $shelters = [
            1 => [
                'inn' => '7724283290',
                'name' => 'Благотворительный фонд защиты животных "Ласковый зверь" - Дубнинская',
                'phone' => '+74953506775',
                'email' => 'shelter@legely.ru',
                'address' => 'ул. Дубнинская д. 83 стр 25',
            ],
            18 => [
                'inn' => '7724283290',
                'name' => 'Благотворительный фонд защиты животных "Ласковый зверь" - Сигнальный',
                'phone' => '+74953506775',
                'email' => 'shelter@legely.ru',
                'address' => 'ул. Дубнинская д. 83 стр 9',
            ],
            2 => [
                'inn' => '7703158072',
                'name' => 'БАНО "ЭКО"',
                'phone' => '+79859675459',
                'email' => 'anjelapriut@yandex.ru',
                'address' => 'пересечение 6-й Радиальной и Дуговой ул.',
            ],
            3 => [
                'inn' => '7701274845',
                'name' => 'Благотворительный фонд защиты животных "Бим"',
                'phone' => '+74957622760',
                'email' => 'dok1410@mail.ru',
                'address' => 'Верхняя Лихоборская д.7А',
            ],
            4 => [
                'inn' => '7735129412',
                'name' => 'Благотворительный фонд помощи бездомным животным "Ника"',
                'phone' => '+79629152139',
                'email' => 'info@fond-nika.ru',
                'address' => 'п. Малино, ул. Школьная, д.2',
            ],
            5 => [
                'inn' => '7719788792',
                'name' => 'ГБУ "Автомобильные дороги ВАО", Приют Кожухово',
                'phone' => '+74952233177',
                'email' => 'gbu.ad.vao@mail.ru',
                'address' => 'Пехорская д.1 Б,в районе Проектируемого пр-зд 265',
            ],
            6 => [
                'inn' => '7731413983',
                'name' => 'ГБУ "Автомобильные дороги ЗАО"',
                'phone' => '+74997926807',
                'email' => 'priyut.solntcevo@yandex.ru',
                'address' => 'г.Москва, ул.Родниковая, вл.26',
            ],
            7 => [
                'inn' => '7714855565',
                'name' => 'ГБУ "Автомобильные дороги САО"',
                'phone' => '+74991520340',
                'email' => 'nikitchenko-aleksandra@mail.ru',
                'address' => '75 км МКАД,  Проектируемый пр-д 727',
            ],
            8 => [
                'inn' => '7717709594',
                'name' => 'ГБУ "Автомобильные дороги СВАО" - Дубовая Роща',
                'phone' => '+79161452588',
                'email' => 'priuyt.dubovayaroshcha@mail.ru',
                'address' => 'г. Москва, пр-д Дубовой Рощи 23/25',
            ],
            19 => [
                'inn' => '7717709594',
                'name' => 'ГБУ "Автомобильные дороги СВАО" - Красная Сосна',
                'phone' => '+79266045617',
                'email' => 'krasnaysosna.2017@mail.ru',
                'address' => 'г. Москва, ул. Красная Сосна, вл. 30',
            ],
            20 => [
                'inn' => '7717709594',
                'name' => 'ГБУ "Автомобильные дороги СВАО" - Искра',
                'phone' => '+79266071854',
                'email' => 'iskra23a@mail.ru',
                'address' => 'Г. Москва, ул. Искра 23а',
            ],
            9 => [
                'inn' => '7723814332',
                'name' => 'ГБУ "Автомобильные дороги ЮВАО"',
                'phone' => '+74953495741',
                'email' => 'office@avtodor-uvao.ru',
                'address' => 'г. Москва, ЮВАО, Промзона "Курьяново", Проектируемый пр-д 5112, вл 1-3',
            ],
            10 => [
                'inn' => '7727763150',
                'name' => 'ГБУ "Автомобильные дороги ЮЗАО"',
                'phone' => '+74992345346',
                'email' => 'priyut.shcherbinka@mail.ru',
                'address' => 'г. Москва, ул. Брусилова, вл. 32, строение 1-5',
            ],
            11 => [
                'inn' => '7724807867',
                'name' => 'ГБУ "Автомобильные дороги ЮАО"',
                'phone' => '+79269121293',
                'email' => 'a-d-uao@yandex.ru',
                'address' => ' г. Москва, Востряковский проезд, д. вл. 10',
            ],
            12 => [
                'inn' => '7710958150',
                'name' => 'ГБУ "Доринвест"',
                'phone' => '+79099185924',
                'email' => 'zelenograd.p@yandex.ru',
                'address' => 'г.Москва, Зеленоград, Фирсановское шоссе, 5500',
            ],
            13 => [
                'inn' => '7710958150',
                'name' => 'ГБУ "Доринвест", Приют ББЖ "Зоорассвет"',
                'phone' => '+74992511494',
                'email' => 'druzhininam@dom.mos.ru',
                'address' => 'Рассветная аллея д. 10',
            ],
            14 => [
                'inn' => '773471389193',  // ИП
                'name' => 'МЦРиР "Счастливый друг"',
                'phone' => '+79167221199',
                'email' => 'info@wild-friends.ru',
                'address' => 'г. Москва, п. Краснопахорское, в районе д. Юрово',
            ],
            15 => [
                'inn' => '7701274845',
                'name' => 'НО БФЗЖ "БИМ"',
                'phone' => '+79857622760',
                'email' => 'findbim@moscowbim.ru',
                'address' => 'г.Москва, ул. Привольная, вл.12',
            ],
            16 => [
                'inn' => '7733112741',
                'name' => 'ПО "Благжилстрой"',
                'phone' => '+79163810988',
                'email' => 'zosya99@gmail.com',
                'address' => 'г.Москва, ул.Зорге, д.21',
            ],
            17 => [
                'inn' => '7723814332',
                'name' => 'Приют для безнадзорных и бесхозяйных животных "Некрасовка"',
                'phone' => '+74953494565',
                'email' => 'office@avtodor-uvao.ru',
                'address' => 'г.Москва, улица 2-я Вольская, вл.17, стр.3',
            ],
        ];

        $orgType = OrgTypes::findOne(['is_tech' => true])->id;
        $id_contact_type_phone = $this->idContactTypePhone();
        $id_contact_type_email = $this->idContactTypeEmail();

        foreach ($shelters as $key => $shelter) {
            $organization = Organizations::findOne(['name' => $shelter['name']]);
            if ($organization !== null) {
                $shelters[$key]['id'] = $organization->id;
            } else {
                $organization = new Organizations([
                    'id_org_type' => $orgType,
                ]);
                $organization->name = $shelter['name'];
                $organization->short_name = $shelter['name'];
                if (!empty($shelter['inn'])) {
                    $organization->inn = $shelter['inn'];
                }
                if ($organization->save(false)) {
                    $shelters[$key]['id'] = $organization->id;
                    // email, phone
                    if (!empty($shelter['email'])) {
                        $email = new Contacts([
                            'id_contact_type' => $id_contact_type_email,
                            'entity_type' => 'organization',
                            'entity_id' => $organization->id,
                        ]);
                        $email->name = $shelter['email'];
                        if (!$email->save()) {
                            Console::output('Error saving organization email for: ' . $shelter['name']);
                        }
                    }
                    if (!empty($shelter['phone'])) {
                        $phone = new Contacts([
                            'id_contact_type' => $id_contact_type_phone,
                            'entity_type' => 'organization',
                            'entity_id' => $organization->id,
                        ]);
                        $phone->name = $shelter['phone'];
                        if (!$phone->save()) {
                            Console::output('Error saving organization phone for: ' . $shelter['name']);
                        }
                    }
                } else {
                    Console::output('Error saving organization: ' . $shelter['name']);
                    return ExitCode::DATAERR;
                }
            }
            unset($organization);
        }

        $csv = $this->loadCsv();
        $items = $this->parseCsv($csv, __FUNCTION__);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV', [Console::FG_RED]));

            return false;
        }

        $species = [
            'кошки' => 9,
            'собаки' => 25,
        ];
        $breeds = $this->findBreeds();

        $now = date('Y-m-d H:i:s');

        $total = count($items);
        $failed = 0;
        $failedRecs = 0;

        $chipsInFile = [];

        foreach ($items as $i => $item) {
            $chip = trim($item[2]);
            if (empty($chip)) {
                // нет чипа - не сохраняем
                $this->logError($i + 2, $item, 'Не указан номер чипа');
                $failed++;
                continue;
            }

            if (strpos($chip, ',') !== false) {
                // несколько чипов, разделенные запятой
                $chips = explode(',', $chip);
            } else {
                $chips = [$chip];
            }

            $duplicate = false;
            foreach ($chips as $k => $val) {
                $val = preg_replace('#\D#', '', $val);
                if (!empty($val) && preg_match('#^\d{15}$#', $val) === 1) {
                    if (array_key_exists($val, $chipsInFile)) {
                        $validChip = false;
                        $duplicate = true;
                        $this->logError($i + 2, $item, 'Данный номер чипа ' . $val . ' дублирует запись в файле, строка: ' . $chipsInFile[$val], 'chip_duplicates_in_file');
                        break;
                    } else {
                        $chips[$k] = $val;
                        $validChip = true;
                        $chipsInFile[$val] = $i + 2;
                    }
                } else {
                    $validChip = false;
                    break;
                }
            }

            if ($validChip === false) {
                if ($duplicate === true) {
                    // дублирующийся номер чипа - не сохраняем
                    $this->logError($i + 2, $item, 'Дублирующийся номер чипа');
                } else {
                    // некорректный номер чипа - не сохраняем
                    $this->logError($i + 2, $item, 'Некорректный номер чипа');
                }
                $failed++;
                continue;
            }

            $error = false;
            foreach ($chips as $k => $val) {
                $exists = $this->checkChipExists($val);
                if ($exists) {
                    // такой номер чипа существует - не сохраняем
                    $this->logError($i + 2, $item, 'Данный номер чипа ' . $val . ' уже существует в базе, id_pet: ' . $exists['id_pet']);
                    $failed++;
                    $error = true;
                    break;
                }
            }

            if ($error === true) {
                continue;
            }

            $id_species = ArrayHelper::getValue($species, mb_convert_case($item[0], MB_CASE_LOWER));
            if (empty($id_species)) {
                // не указан вид животного - не сохраняем
                $this->logError($i + 2, $item, 'Некорректный вид животного');
                $failed++;
                continue;
            }

            $id_organization = ArrayHelper::getValue($shelters[$item[4]], 'id');

            $pet = new Pets();
            $pet->id_species = $id_species;
            $pet->id_breed = ArrayHelper::getValue($breeds[$id_species], mb_convert_case($item[1], MB_CASE_LOWER));
            if (!empty($item[5])) {
                $pet->sex = $item[5];
            }
            $pet->id_created_organization = $id_organization;
            $pet->created_at = $now;

            if (!$pet->save(false)) {
                $this->logError($i + 2, $item, 'Ошибка при сохранении животного');
                $failed++;
                continue;
            }

            foreach ($chips as $j => $code) {
                $ident = new PetIdentification();
                $ident->id_ident_type = 1;
                $ident->id_pet = $pet->id;
                $ident->identification_code = $code;
                $ident->main_flag = (count($chips) === 1 || $j == 0);
                $ident->identif_org = $id_organization;
                $ident->created_at = $now;
                $ident->save(false);
            }

            $arrivalDate = trim($item[3]);
            if (empty($arrivalDate)) {
                $arrival_date = '1970-01-01';
            } else {
                if (preg_match('#^(\d{2})\.(\d{2})\.(\d{4})$#', $arrivalDate, $matches1) === 1) {
                    $arrival_date = $matches1[3] . '-' . $matches1[2] . '-' . $matches1[1];
                } elseif (preg_match('#^(\d{2})\.(\d{4})$#', $arrivalDate, $matches2) === 1) {
                    $arrival_date = $matches2[2] . '-' . $matches2[1] . '-01';
                } else {
                    $arrival_date = '1970-01-01';
                }
            }
            $record = new ShelterGuests();
            $record->id_organization = $id_organization;
            $record->id_pet = $pet->id;
            $record->arrival_date = $arrival_date;
            $record->created_at = $now;
            if (!$record->save(false)) {
                $this->logError($i + 2, $item, 'Ошибка при сохранении записи о попадании животного в приют');
                $failedRecs++;
            }
        }

        Console::output('Total pets:     ' . $total);
        Console::output('Saved pets:     ' . ($total - $failed));
        Console::output('Failed pets:    ' . $failed);
        Console::output('Failed records: ' . $failedRecs);

        return ExitCode::OK;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191227_072907_2595_shelter_pets cannot be reverted.\n";

        return false;
    }

    /**
     * @param string $csv
     * @param string $function
     * @return array
     * @throws \Exception
     */
    private function parseCsv($csv, $function)
    {
        $arr = [];
        $data = str_getcsv($csv, "\n");
        foreach ($data as $row) {
            $arr[] = str_getcsv($row, ';', '"');
        }

        if (empty($arr)) {
            throw new \Exception($function . ': Failed to parse CSV');
        }

        return $arr;
    }

    /**
     * @return string
     */
    private function loadCsv()
    {
        return file_get_contents(__DIR__ . '/data/2595_shelter_pets.csv');
    }

    /**
     * @param int    $rowNumber
     * @param array  $item
     * @param string $error
     * @param string $logFile
     */
    private function logError($rowNumber, $item, $error, $logFile = 'import_shelter_pets_errors')
    {
        $path = Yii::getAlias('@runtime/logs/' . $logFile . '_' . $this->logSuffix . '.csv');
        $str = $rowNumber . ';';
        foreach ($item as $value) {
            $str .= ('"' . $value . '";');
        }
        $str .= $error;
        $str .= PHP_EOL;

        file_put_contents($path, $str, FILE_APPEND | FILE_TEXT | LOCK_EX);
    }

    /**
     * @param string $val
     * @return array|bool
     */
    private function checkChipExists($val)
    {
        return (new Query())
            ->from(PetIdentification::tableName())
            ->where([
                'id_ident_type' => 1,
                'identification_code' => $val
            ])
            ->one();
    }

    /**
     * @return array
     */
    private function findBreeds()
    {
        $breeds = [
            9 => [],
            25 => [],
        ];

        $rows = (new Query())
            ->from(\app\models\db\Breeds::tableName())
            ->where(['in', 'species_id', [9, 25]])
            ->andWhere([
                'or',
                ['ilike', 'name', 'беспородная'],
                ['ilike', 'name', 'метис']
            ])
            ->orderBy(['species_id' => SORT_ASC])
            ->all();

        foreach ($rows as $row) {
            $breeds[$row['species_id']][$row['name']] = $row['id'];
        }

        return $breeds;
    }
    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    private function idContactTypePhone()
    {
        $type = ContactTypes::findOne([
            'name' => 'Телефон',
            'entity_type' => 'organization',
        ]);

        if ($type === null) {
            throw new InvalidConfigException('Не найден тип контакта "Телефон"');
        }

        return $type->id;
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    private function idContactTypeEmail()
    {
        $type = ContactTypes::findOne([
            'name' => 'Электронная почта организации',
            'entity_type' => 'organization',
        ]);

        if ($type === null) {
            throw new InvalidConfigException('Не найден тип контакта "Электронная почта организации"');
        }

        return $type->id;
    }
}
