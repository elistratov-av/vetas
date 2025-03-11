<?php

namespace app\commands;

use app\models\db\Breeds;
use app\models\db\IdentificationTypes;
use app\models\db\Organizations;
use app\models\db\PetIdentification;
use app\models\db\PetRefColor;
use app\models\db\PetRefEarType;
use app\models\db\PetRefSize;
use app\models\db\PetRefTailType;
use app\models\db\PetRefWoolType;
use app\models\db\Pets;
use app\models\db\ShelterGuests;
use app\models\db\Species;
use app\modules\v2\modules\petOwners\models\PetsDuplicatesModel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\console\Controller;
use yii\console\ExitCode;
use PhpOffice;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportShelterPetsController extends Controller
{
    const IDENT_CHIP_LENGTH = 15;
    const NUMBER = 'A';
    const STATUS = 'B'; // "В приюте", "Карантин"
    const QUARANTINE_START = 'C'; // "19/04/2022"
    const QUARANTINE_END = 'D';
    const SHELTER_ARRIVAL_DATE = 'E';
    const PET_SPECIE = 'F'; // "Кошки", "Собаки"
    const PET_BREED = 'G'; // "метис"
    const PET_GENDER = 'H'; // "Женский", "Мужской"
    const PET_NAME = 'I';
    const PET_BIRTHDAY = 'J'; // "10.2019", "2019", "10,2019"
    const PET_COLOR = 'K';
    const PET_SIZE = 'L';
    const PET_WOOL = 'M';
    const PET_EARS = 'N';
    const PET_TAIL = 'O';
    const PET_SOCIAL = 'P';
    const PET_CHARACTERISTICS = 'Q';
    const PET_CHARACTER = 'R';
    const IDENT_CHIP = 'S';
    const IDENT_BRAND = 'T';
    const IDENT_LABEL = 'U';
    const IDENT_TATOO = 'V';
    const IDENT_MARK = 'W';

    private $path = '/web/shelterImport';
    private $id_organization = 685;
    private $files = [
        685 => 'ДЖКХ_Зеленоград_загрузки_животных_в_приюты.xlsx',
        686 => 'ДЖКХ_Зоорассвет_загрузки_животных_в_приюты.xlsx',
        'none1' => 'Префектура ВАО_загрузки_животных_в_приюты.xlsx', // 676, 701 ?
        677 => 'Префектура ЗАО_загрузки_животных_в_приюты.xlsx',
        700 => 'Префектура САО_GETDOG_загрузки_животных_в_приюты.xlsx',
        'none2' => 'Префектура САО_Молжаниново_загрузки_животных_в_приюты.xlsx', // 697, 698, 678 ? + GETDOG выше
        679 => 'Префектура СВАО_Дубовая роща_загрузки_животных_в_приюты.xlsx',
        681 => [
            'Префектура СВАО_Искра_загрузки_животных_в_приюты (кошки).xlsx',
            'Префектура СВАО_Искра_загрузки_животных_в_приюты (собаки).xlsx'
        ],
        680 => 'Префектура СВАО_Красная Сосна_загрузки_животных_в_приюты.xlsx',
        684 => 'Префектура ЮАО_загрузки_животных_в_приюты.xlsx',
        690 => 'Префектура ЮВАО_Некрасовка_загрузки_животных_в_приюты.xlsx',
        682 => 'Префектура ЮВАО_Печатники_загрузки_животных_в_приюты.xlsx',
        683 => 'Префектура ЮЗАО_загрузки_животных_в_приюты.xlsx',
    ];
    private $specieDogs;
    private $specieCats;

    private $breedMetisDogs;
    private $breedMetisCats;

    private $petIdentificationChipId;
    private $organization;
    private $documentName = null;

    private $petsDuplicateModel;

    public function __construct($id, $module, $config = [])
    {
        $this->specieDogs = Species::findOne(['name' => 'собаки']);
        $this->specieCats = Species::findOne(['name' => 'кошки']);
        $this->breedMetisDogs = Breeds::findOne(['name' => 'метис', 'species_id' => $this->specieDogs->id]);
        $this->breedMetisCats = Breeds::findOne(['name' => 'метис', 'species_id' => $this->specieCats->id]);

        $this->petIdentificationChipId = IdentificationTypes::findIdentificationTypeId('чип');

        $this->petsDuplicateModel = new PetsDuplicatesModel();

        parent::__construct($id, $module, $config);
    }

    /**
     * Импортит файл указанный параметром команды командой: 1 параметр - имя файла, 2 - id организации приюта
     *
     * @param $filename
     * @param $organization_id
     * @return int
     * @throws PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionImport($filename = null, $organization_id = null)
    {
        if (!$filename || !$organization_id) {
            throw new \Exception('Параметр filename и organization_id являются обязательными');
        }
        $this->documentName = $filename;
        $spreadsheet = IOFactory::load(\Yii::$app->basePath .'/'. $this->path .'/'. $this->documentName);
        $sheet = $spreadsheet->getSheet(0);

        $this->id_organization = $organization_id;
        $this->organization = Organizations::findOne(['id' => $this->id_organization]);

        $row = 6;
        while ($sheet->getCell('A'.$row)->getValue()) {
            try {
                $this->parseRow($sheet, $row);
            } catch (\Exception $e) {
                throw new \Exception("Ошибка парсинга документа $this->documentName в строке $row | ".static::class."\n$e");
            }
            $row++;
        }

        return ExitCode::OK;
    }

    /**
     * Импортит все файлы указанные хардкодом (необходимо дополнить список айдишников организаций-приютов)
     *
     * @return int
     * @throws PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionImportAll()
    {
        foreach ($this->files as $organization_id => $filenames) {
            // Пропустить те файлы для которых не определены айдишники организации
            if (is_string($organization_id)) {
                continue;
            }
            if (is_string($filenames)) {
                $this->actionImport($filenames, $organization_id);
            }
            // Если для одной организации более одного файла - передаём массивом
            if (is_array($filenames)) {
                foreach ($filenames as $filename) {
                    $this->actionImport($filename, $organization_id);
                }
            }
        }

        return ExitCode::OK;
    }

    private function parseRow(Worksheet $sheet, int $row)
    {
        $identChips = $this->prepareChipField($sheet->getCell(self::IDENT_CHIP . $row)->getValue());
        if (count($identChips) === 0) {
            return;
        }

        // Проверяем есть ли данные идентификации в БД
        /** @var PetIdentification[] $identifications */
        $identifications = PetIdentification::find()->where([
            'identification_code' => $identChips,
            'id_ident_type' => $this->petIdentificationChipId,
        ])->all();

        // Нет соответствующей идентификации в БД, создаём животное, идентификации и гостя приюта
        if (count($identifications) === 0) {
            $this->createPetFromRow($sheet, $row, $identChips);
            return;
        }

        // Найдена 1 идентификация, обновляем данные животного, создаём или обновляем данные по гостю приюта, добавляем
        // новые идентификации (по необходимости)
        if (count($identifications) === 1) {
            $this->updatePetFromRow($identifications[0]->pet, $identChips, $identifications, $sheet, $row);
            return;
        }

        // Найдена 1 или более идентификаций
        if (count($identifications) > 1) {
            $pets = [];
            foreach ($identifications as $identification) {
                $pets[$identification->id_pet] = $identification->pet;
            }
            // Если все идентификации на одно животное - просто добавим новые (по необходимости) и обновим данные животного
            // и гостя приюта
            if (count($pets) === 1) {
                $this->updatePetFromRow(reset($pets), $identChips, $identifications, $sheet, $row);
                return;
            }
            // В противном случае необходимо "склеить" животных перед обновлением
            $mainPet = $this->defineMainPet($pets);
            $duplicates = array_filter($pets, function ($pet) use ($mainPet) {
                return $pet->id !== $mainPet->id;
            });

            $this->petsDuplicateModel->linkPets($this->organization->representative->id, $mainPet->id, array_keys($duplicates), [], true);

            $this->updatePetFromRow($mainPet, $identChips, $identifications, $sheet, $row);
        }
    }

    private function createPetFromRow(Worksheet $sheet, int $row, array $identificationCodes)
    {
        Pets::getDb()->beginTransaction();

        $pet = new Pets;
        $pet = $this->fillPetFromRow($pet, $sheet, $row);
        if (!$pet->validate() || !$pet->save()) {
            Pets::getDb()->transaction->rollBack();
            //TODO
            var_dump($pet->getErrors());
            return false;
        }

        foreach ($identificationCodes as $code) {
            if (!$this->createIdentification($code, $pet->id)) {
                Pets::getDb()->transaction->rollBack();
                return false;
            }
        }

        $shelterGuest = new ShelterGuests();
        $shelterGuest = $this->fillShelterGuestFromRow($shelterGuest, $pet->id, $sheet, $row);
        if (!$shelterGuest->save()) {
            if (Pets::getDb()->transaction->isActive) {
                Pets::getDb()->transaction->rollBack();
            }
            //TODO
            var_dump($shelterGuest->getErrors());
            return false;
        }

        if (Pets::getDb()->transaction->isActive) {
            Pets::getDb()->transaction->commit();
        }

        return $pet;
    }

    private function updatePetFromRow(Pets $pet, array $identChips, array $identifications, Worksheet $sheet, int $row)
    {
        Pets::getDb()->beginTransaction();
        $this->fillPetFromRow($pet, $sheet, $row);
        $pet->save();
        if (!$pet->save()) {
            Pets::getDb()->transaction->rollBack();
            //TODO
            var_dump($pet->getErrors());
            return false;
        }

        if (!$this->findOrCreateShelterGuest($pet->id, $sheet, $row)) {
            Pets::getDb()->transaction->rollBack();
            return false;
        }

        $identificationCodes = [];
        foreach ($identifications as $identification) {
            $identificationCodes[] = $identification->identification_code;
        }
        $identToCreate = array_filter($identChips, function($code) use ($identificationCodes) {
            return !in_array($code, $identificationCodes);
        });

        foreach ($identToCreate as $code) {
            if (!$this->createIdentification($code, $pet->id)) {
                Pets::getDb()->transaction->rollBack();
                return false;
            }
        }

        if (Pets::getDb()->transaction->isActive) {
            Pets::getDb()->transaction->commit();
        }

        return $pet;
    }

    private function fillPetFromRow(Pets $pet, Worksheet $sheet, int $row)
    {
        $specieId = $this->prepareSpecieField($sheet->getCell(self::PET_SPECIE . $row)->getValue());

        $pet->id_breed = $this->prepareBreedField($sheet->getCell(self::PET_BREED . $row)->getValue(), $specieId) ?? $pet->id_breed;
        $pet->id_species = $specieId;
        $pet->sex = $this->prepareGenderField($sheet->getCell(self::PET_GENDER . $row)->getValue()) ?? $pet->sex;
        $pet->color_id = $this->getColorRefIdFromDb($sheet->getCell(self::PET_COLOR . $row)->getValue()) ?? $pet->color_id;
        $pet->characteristics = $sheet->getCell(self::PET_CHARACTERISTICS . $row)->getValue() ?? $pet->characteristics;
        $pet->birthday = $this->prepareBirthdayField($sheet->getCell(self::PET_BIRTHDAY . $row)->getValue()) ?? $pet->birthday;
        $pet->name = $sheet->getCell(self::PET_NAME . $row)->getValue() ?? $pet->name;
        $pet->character = $sheet->getCell(self::PET_CHARACTER . $row)->getValue() ?? $pet->character;
        $pet->ear_type_id = $this->getEarsRefIdFromDb($sheet->getCell(self::PET_EARS . $row)->getValue()) ?? $pet->ear_type_id;
        $pet->tail_type_id = $this->getTailRefIdFromDb($sheet->getCell(self::PET_TAIL . $row)->getValue()) ?? $pet->tail_type_id;
        $pet->wool_type_id = $this->getWoolRefIdFromDb($sheet->getCell(self::PET_WOOL . $row)->getValue()) ?? $pet->wool_type_id;
        $pet->size_id = $this->getSizeRefIdFromDb($sheet->getCell(self::PET_SIZE . $row)->getValue()) ?? $pet->size_id;
        $pet->id_created_organization = $pet->id_created_organization ?? $this->id_organization;
        $pet->guide_dog = $pet->guide_dog ?? false;
        $pet->castrated = $pet->castrated ?? false;

        return $pet;
    }

    private function findOrCreateShelterGuest(int $petId, Worksheet $sheet, int $row)
    {
        $shelterGuest = ShelterGuests::find()->where([
            'AND',
            ['id_organization' => $this->id_organization],
            ['id_pet' => $petId],
            ['NOT', ['status' => ShelterGuests::STATUS_DEPARTURED]],
        ])->one();
        if (!$shelterGuest) {
            $shelterGuest = new ShelterGuests();
        }
        $shelterGuest = $this->fillShelterGuestFromRow($shelterGuest, $petId, $sheet, $row);
        if (!$shelterGuest->save()) {
            return false;
        }

        return $shelterGuest;
    }

    private function fillShelterGuestFromRow(ShelterGuests $shelterGuest, int $id_pet, Worksheet $sheet, int $row)
    {
        $shelterGuest->id_pet = $id_pet;
        $shelterGuest->id_organization = $this->id_organization;
        $shelterGuest->arrival_date = $this->prepareDateField($sheet->getCell(self::SHELTER_ARRIVAL_DATE . $row)->getValue());
        $shelterGuest->quarantine_from = $this->prepareDateField($sheet->getCell(self::QUARANTINE_START . $row)->getValue());
        $shelterGuest->quarantine_to = $this->prepareDateField($sheet->getCell(self::QUARANTINE_END . $row)->getValue());
        $shelterGuest->socialized = $this->prepareSocializedField($sheet->getCell(self::PET_SOCIAL . $row)->getValue());
        $shelterGuest->status = $this->prepareStatusField($sheet->getCell(self::STATUS . $row)->getValue());
        $shelterGuest->is_quarantine = $shelterGuest->is_quarantine ?? false;
        $shelterGuest->is_catching_video = $shelterGuest->is_catching_video ?? false;

        return $shelterGuest;
    }

    private function createIdentification($code, $petId)
    {
        $identification = new PetIdentification();
        $identification->id_pet = $petId;
        $identification->id_ident_type = $this->petIdentificationChipId;
        $identification->identification_code = $code;
        $identification->main_flag = false;

        if (!$identification->save()) {
            var_dump($identification->getErrors());
            return false;
        }
        return true;
    }

    private function defineMainPet(array $pets)
    {
        $petIds = [];
        // Животное с рег.удостоверением не может быть дублем
        foreach ($pets as $petId => $pet) {
            if ($pet->reg_certificate) {
                return $pet;
            }
            $petIds[] = $petId;
        }

        // Если животное находится в приюте считаем основным его
        $shelterGuest = ShelterGuests::find()
            ->where([
                'AND',
                ['id_organization' => $this->id_organization],
                ['id_pet' => $petIds],
                ['NOT', ['status' => ShelterGuests::STATUS_DEPARTURED]],
            ])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();

        // Если животное не находится, но было в приюте, считаем основным его
        if (!$shelterGuest) {
            $shelterGuest = ShelterGuests::find()
                ->where([
                    'AND',
                    ['id_organization' => $this->id_organization],
                    ['id_pet' => $petIds],
                ])
                ->one();
        }

        // Если нет и не было в текущем, то смотрим, если был в другом приюте
        if (!$shelterGuest) {
            $shelterGuest = ShelterGuests::find()
                ->where([
                    'AND',
                    ['id_pet' => $petIds],
                    ['NOT', ['status' => ShelterGuests::STATUS_DEPARTURED]],
                ])
                ->one();
        }

        /** @var ShelterGuests $shelterGuest */
        if ($shelterGuest) {
            return $shelterGuest->pet;
        }

        // В противном случае отдаём первого в списке
        return $pets[$petIds[0]];
    }

    private function prepareDateField($value)
    {
        try {
            $newValue = date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
        } catch (\Exception $e) {
            $newValue = null;
        }
        if ($newValue === null) {
            $newValue = preg_replace('/[^0-9]/', '', $value);
            try {
                $newValue = date('Y-m-d', strtotime($newValue));
            } catch (\Exception $e) {
                $newValue = null;
            }
        }
        return $newValue;
    }

    private function prepareSpecieField(string $value)
    {
        $value = trim($value);
        $value = mb_substr($value, 0, -1, 'UTF-8');
        $value = mb_strtolower($value, 'UTF-8');
        switch($value) {
            case 'кошк':
                return $this->specieCats->id;
            case 'собак':
                return $this->specieDogs->id;
            default:
                var_dump($value);
                $species = Species::find()->where(['like', 'name', '%'.$value.'%'])->one();
                return $species->id;
        }
    }

    private function prepareBreedField($value, int $specie_id)
    {
        $value = mb_strtolower($value, 'UTF-8');
        switch($value) {
            case $specie_id === $this->specieDogs->id && $value = 'метис':
                return $this->breedMetisDogs->id;
            case $specie_id === $this->specieCats->id && $value = 'метис':
                return $this->breedMetisCats->id;
            default:
                $species = Breeds::find()->where(['name' => $value, 'species_id' => $specie_id])->one();
                return $species ? $species->id : null;
        }
    }

    private function prepareGenderField(string $value)
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        switch(true) {
            case in_array($value, ['женский', 'сука']):
                return Pets::SEX_FEMALE;
            case in_array($value, ['мужской', 'кобель']):
                return Pets::SEX_MALE;
            default:
                return null;
        }
    }

    /**
     * @param string|null $value
     * @return bool|null
     */
    private function prepareSocializedField($value)
    {
        $value = mb_strtolower($value, 'UTF-8');
        switch($value) {
            case 'да':
                return true;
            case 'нет':
                return false;
            default:
                return null;
        }
    }

    private function prepareStatusField($value)
    {
        $value = trim($value);
        $value = mb_strtolower($value, 'UTF-8');
        switch($value) {
            case 'в приюте':
                return ShelterGuests::STATUS_IN_SHELTER;
            case 'карантин':
                return ShelterGuests::STATUS_QUARANTINE;
            default:
                return null;
        }
    }

    private function getColorRefIdFromDb($value)
    {
        $value = trim($value);
        if (strlen($value) === 0) {
            return null;
        }
        $color = PetRefColor::find()->where(['lower(title)' => mb_strtolower($value, 'UTF-8')])->one();
        return $color ? $color->id : null;
    }

    private function getWoolRefIdFromDb($value)
    {
        $value = trim($value);
        if (strlen($value) === 0) {
            return null;
        }
        $wool = PetRefWoolType::find()->where(['lower(title)' => mb_strtolower($value, 'UTF-8')])->one();
        return $wool ? $wool->id : null;
    }

    private function getTailRefIdFromDb($value)
    {
        $value = trim($value);
        if (strlen($value) === 0) {
            return null;
        }
        $tail = PetRefTailType::find()->where(['lower(title)' => mb_strtolower($value, 'UTF-8')])->one();
        return $tail ? $tail->id : null;
    }

    private function getEarsRefIdFromDb($value)
    {
        $value = trim($value);
        if (strlen($value) === 0) {
            return null;
        }
        $ears = PetRefEarType::find()->where(['lower(title)' => mb_strtolower($value, 'UTF-8')])->one();
        return $ears ? $ears->id : null;
    }

    private function getSizeRefIdFromDb($value)
    {
        $value = trim($value);
        if (strlen($value) === 0) {
            return null;
        }
        $size = PetRefSize::find()->where(['lower(title)' => mb_strtolower($value, 'UTF-8')])->one();
        return $size ? $size->id : null;
    }

    /**
     * Формат записи чипа(ов) в каждом документе может быть разный, поэтому очистим строку от не чисел, проверим, что
     * длина получившиегося результата кратна 15, и разобьём результат на количество чипов
     *
     * @param $value
     * @return array
     */
    private function prepareChipField($value)
    {
        $value = str_replace(' ', '', $value);
        $value = str_replace('(2чипа)', '', $value);
        $onlyNumbers = preg_replace('/[^0-9]/', '', $value);
        $valueLength = strlen($onlyNumbers);
        if ($valueLength % self::IDENT_CHIP_LENGTH != 0 || $valueLength === 0) {
            return [];
        }
        if ($valueLength === 15) {
            return [ $onlyNumbers ];
        }
        $splittedOnlyNumbers = trim(chunk_split($onlyNumbers, 15, ' '));

        return explode(' ', $splittedOnlyNumbers);
    }

    private function prepareBirthdayField($value)
    {
        $value = trim($value);
        if (strlen($value) === 7) {
            $datetime = \DateTime::createFromFormat("m.Y", $value);
            if (!$datetime) {
                $datetime = \DateTime::createFromFormat("m,Y", $value);
            }

            return $datetime->format('Y-m-d');
        }
        if (strlen($value) === 4) {
            $datetime = \DateTime::createFromFormat("Y", $value);
            return $datetime->format('Y-m-d');
        }
        return null;
    }
}
