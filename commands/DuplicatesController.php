<?php

namespace app\commands;

use app\models\db\elk\ElkPets;
use app\models\db\PetIdentification;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Visits;
use app\modules\v2\modules\petOwners\models\PetOwnersModel;
use app\modules\v2\modules\petOwners\models\PetsDuplicatesModel;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Class DuplicatesController
 * @package app\commands
 */
class DuplicatesController extends Controller
{
    /**
     * @var bool
     */
    private $dryRun = true;

    /**
     * @param int $dryRun
     * @param int $lastId
     * @return int
     */
    public function actionMerge($dryRun = 1, $lastId = null)
    {
        if ($dryRun === 0 || $dryRun === '0') {
            $this->dryRun = false;
        }

        $start = microtime(true);

        $query = PetOwners::find()
            ->alias('po')
            ->where(['is_main' => null])
            ->andWhere([
                'and',
                ['not ilike', 'f_fio', 'Инкогнито'],
                ['not ilike', 'i_fio', 'Инкогнито'],
                ['not ilike', 'o_fio', 'Инкогнито'],
            ])
            ->innerJoin([
                'pto' => (new Query())
                    ->select('id_owner')
                    ->from(PetsToOwner::tableName())
                    ->where(['id_owner_type' => 1])
                    ->groupBy(['id_owner'])
                    ->orderBy(['id_owner' => SORT_ASC]),
            ],
                'pto.id_owner = po.id'
            )
            ->orderBy(['po.id' => SORT_ASC]);

        if (!empty($lastId)) {
            $query->andWhere(['>', 'po.id', $lastId]);
        }

        $total = $query->count();

        $processed = 0;
        $mayHaveDuplicates = 0;
        $duplicateIds = [];
        $withNoPets = 0;
        $withStartedPets = 0;
        $withNonamePets = 0;
        $doneAny = 0;
        $doneNothing = 0;
        $mainPets = 0;
        $duplicatePets = 0;
        $mainOwnersSuccess = 0;
        $mainPetsSuccess = 0;
        $duplicatePetsSuccess = 0;

        $dbErrors = 0;

        Console::startProgress($processed, $total, '', false);

        $this->writeCsv('owners_may_have_duplicates.csv', [
            'id',
            'f_fio',
            'i_fio',
            'o_fio',
            'jur_name',
            'inn',
            'ogrn',
            'snils',
            'is_legal',
            'entrepreneur',
        ]);
        $this->writeCsv('pets_merged.csv', [
            'id',
            'id_main_pet',
            'name',
            'species_name',
            'breeds_name',
            'sex',
            'birthday',
            'elk_pet_id',
            'chip',
            'visits_count',
            'created_at',
            'created_by',
            'owner_id',
            'owner_fullname',
            'is_success',
            'error',
        ]);

        $limit = 100;
        $batches = (int)ceil($total / $limit);

        for ($batch = 0; $batch < $batches; $batch++) {
            $owners = $query
                ->offset($batch * $limit)
                ->limit($limit)
                ->all();

            foreach ($owners as $owner) {
                $done = false;

                try {
                    /* @var $owner \app\models\db\PetOwners */
                    if (array_key_exists($owner->id, $duplicateIds)) {
                        $mayHaveDuplicates++;
                        $processed++;
                        $this->writeCsv('owners_may_have_duplicates.csv', [
                            $owner->id,
                            $owner->f_fio,
                            $owner->i_fio,
                            $owner->o_fio,
                            $owner->jur_name,
                            $owner->inn,
                            $owner->ogrn,
                            $owner->snils,
                            $owner->is_legal,
                            $owner->entrepreneur,
                        ]);
                        Console::updateProgress($processed, $total);
                        continue;
                    }

                    $poModel = new PetOwnersModel();
                    $suggestions = $poModel->suggestDuplicates(
                        $owner->f_fio,
                        $owner->i_fio,
                        $owner->o_fio,
                        $owner->jur_name,
                        $owner->inn,
                        $owner->ogrn,
                        $owner->snils,
                        $owner->is_legal,
                        $owner->entrepreneur,
                        $owner->id,
                        false
                    );

                    if (!empty($suggestions)) {
                        $duplicateIds[$owner->id] = true;
                        $mayHaveDuplicates++;
                        $processed++;
                        $this->writeCsv('owners_may_have_duplicates.csv', [
                            $owner->id,
                            $owner->f_fio,
                            $owner->i_fio,
                            $owner->o_fio,
                            $owner->jur_name,
                            $owner->inn,
                            $owner->ogrn,
                            $owner->snils,
                            $owner->is_legal,
                            $owner->entrepreneur,
//                            $owner->passport,
                        ]);
                        // заносим его дубли в массив, чтобы в дальнейшем не проверять еще раз
                        foreach ($suggestions as $suggestion) {
                            $duplicateIds[$suggestion['id']] = true;
                        }
                        Console::updateProgress($processed, $total);
                        continue;
                    }

                    $pets = $owner->hasMany(Pets::class, ['id' => 'id_pet'])
                        ->viaTable('pets_to_owner', ['id_owner' => 'id'], function ($query) {
                            $query->andWhere(['id_owner_type' => 1]);
                        })
                        ->andWhere(['id_reg_expire_reason' => null])
                        ->indexBy('id')
                        ->orderBy(['id' => SORT_ASC])
                        ->all();

                    if (empty($pets)) {
                        $withNoPets++;
                        $processed++;
                        Console::updateProgress($processed, $total);
                        continue;
                    }

                    $isMain = ArrayHelper::getColumn($pets, 'is_main');
                    if (in_array(true, $isMain, true) || in_array(false, $isMain, true)) {
                        $withStartedPets++;
                        $processed++;
                        Console::updateProgress($processed, $total);
                        continue;
                    }

                    /* @var $pets \app\models\db\Pets[] */
                    foreach ($pets as $id_pet => $pet) {
                        if (empty($pet->name)) {
                            unset($pets[$id_pet]);
                        }
                    }

                    if (empty($pets)) {
                        $withNonamePets++;
                        $processed++;
                        Console::updateProgress($processed, $total);
                        continue;
                    }

                    $petsArr = ArrayHelper::index(
                        $pets,
                        'id',
                        [
                            'id_species',
                            function ($el) {
                                return mb_strtolower($el['name']);
                            },
                        ]
                    );

                    foreach ($petsArr as $id_species => $groups) {
                        foreach ($groups as $petName => $group) {
                            $mainPet = null;
                            $pet_id = null;
                            $chip = null;

                            if (count($group) < 2) {
                                continue;
                            }

                            /* @var $group \app\models\db\Pets[] */
                            /* @var $mainPet \app\models\db\Pets */
                            foreach ($group as $id_pet => $pet) {
                                $pet_id = $this->findElkPetId($pet->id);
                                if ($pet_id !== null) {
                                    $chip = $this->findChip($pet->id);
                                    $mainPet = ArrayHelper::remove($group, $id_pet);
                                    break;
                                }
                            }

                            if ($mainPet === null) {
                                foreach ($group as $id_pet => $pet) {
                                    $chip = $this->findChip($pet->id);
                                    if ($chip !== null) {
                                        $mainPet = ArrayHelper::remove($group, $id_pet);
                                        break;
                                    }
                                }
                            }

                            if ($mainPet === null) {
                                $mainPet = reset($group);
                                unset($group[$mainPet->id]);
                            }

                            $done = true;
                            $mainPets++;
                            $duplicatePets += count($group);

                            $isSuccess = null;
                            $errorMessage = null;
                            $ownerError = false;
                            $ownerErrorMessage = false;

                            // main-owner
                            if ($this->dryRun === false && $owner->is_main === null) {
                                $poModel = new PetOwnersModel();
                                if (!$poModel->linkOwners($owner->id)) {
                                    $ownerError = true;
                                    $ownerErrorMessage = 'Не удалось назначить владельца основным' . "\n" . implode("\n", array_values($poModel->getErrorSummary(true)));
                                } else {
                                    $owner->refresh();
                                    $mainOwnersSuccess++;
                                }
                            }

                            // main-pet
                            if ($this->dryRun === false) {
                                if ($ownerError === false) {
                                    $model = new PetsDuplicatesModel();
                                    $isSuccess = $model->makeMain($owner->id, $mainPet->id);
                                    if ($isSuccess === false) {
                                        $errorMessage = 'Не удалось назначить основное животное' . "\n" . implode("\n", array_values($model->getErrorSummary(true)));
                                    } else {
                                        $mainPetsSuccess++;
                                    }
                                } else {
                                    $isSuccess = false;
                                    $errorMessage = $ownerErrorMessage;
                                }
                            }

                            $this->writeCsv('pets_merged.csv', [
                                $mainPet->id,
                                0,
                                $mainPet->name,
                                $mainPet->species->name,
                                ($mainPet->breeds ? $mainPet->breeds->name : null),
                                $mainPet->sex,
                                $mainPet->birthday,
                                $pet_id,
                                $chip,
                                $this->countVisits($mainPet->id),
                                $mainPet->created_at,
                                $mainPet->created_by,
                                $owner->id,
                                $owner->fullname,
                                $isSuccess,
                                $errorMessage,
                            ]);

                            $mainSuccess = $isSuccess;

                            foreach ($group as $pet) {
                                $isSuccess = null;
                                $errorMessage = null;

                                // link-pets
                                if ($this->dryRun === false) {
                                    if ($mainSuccess === false) {
                                        $isSuccess = false;
                                        $errorMessage = 'Не назначено основное животное';
                                    } else {
                                        $model = new PetsDuplicatesModel();
                                        $isSuccess = $model->linkPets($owner->id, $mainPet->id, [$pet->id]);
                                        if ($isSuccess === false) {
                                            $errorMessage = 'Не удалось назначить животное дублем' . "\n" . implode("\n", array_values($model->getErrorSummary(true)));
                                        } else {
                                            $duplicatePetsSuccess++;
                                        }
                                    }
                                }

                                $this->writeCsv('pets_merged.csv', [
                                    $pet->id,
                                    $mainPet->id,
                                    $pet->name,
                                    $pet->species->name,
                                    ($pet->breeds ? $pet->breeds->name : null),
                                    $pet->sex,
                                    $pet->birthday,
                                    null,
                                    $this->findChip($pet->id),
                                    $this->countVisits($pet->id),
                                    $pet->created_at,
                                    $pet->created_by,
                                    $owner->id,
                                    $owner->fullname,
                                    $isSuccess,
                                    $errorMessage,
                                ]);
                            }
                        }
                    }

                    if ($done === true) {
                        $doneAny++;
                    } else {
                        $doneNothing++;
                    }

                    $processed++;
                    Console::updateProgress($processed, $total);

                } catch (\yii\db\Exception $e) {
                    $done = false;
                    $dbErrors++;
                    $this->writeCsv('owners_db_errors.csv', [
                        $owner->id,
                        $owner->f_fio,
                        $owner->i_fio,
                        $owner->o_fio,
                        $owner->jur_name,
                        $owner->inn,
                        $owner->ogrn,
                        $owner->snils,
                        $owner->is_legal,
                        $owner->entrepreneur,
                    ]);
                    unset($owner);
                    unset($pets);
                    unset($petsArr);
                    \Yii::error($e);
                    sleep(5);
                }
            }
        }

        Console::updateProgress($processed, $processed);
        Console::endProgress();

        $time = microtime(true) - $start;
        Console::output('Time elapsed:         ' . sprintf('%.3f', $time) . ' sec.');
        Console::output('Processed:            ' . $processed);
        Console::output('May have dups:        ' . $mayHaveDuplicates);
        Console::output('With no pets:         ' . $withNoPets);            // без животных на учете
        Console::output('With started pets:    ' . $withStartedPets);       // есть животные, у которых был начат процесс склейки
        Console::output('With noname pets:     ' . $withNonamePets);        // только с животными без имени
        Console::output('Owners done nothing:  ' . $doneNothing);
        Console::output('Owners done smth:     ' . $doneAny);
        Console::output('Main pets chosen:     ' . $mainPets);
        Console::output('Duplicate pets  :     ' . $duplicatePets);
        Console::output('Main owners saved:    ' . $mainOwnersSuccess);
        Console::output('Main pets saved:      ' . $mainPetsSuccess);
        Console::output('Duplicate pets saved: ' . $duplicatePetsSuccess);
        Console::output('DB errors:            ' . $dbErrors);

        return ExitCode::OK;
    }

    /**
     * @param int $id_pet
     * @return string|null
     */
    private function findElkPetId($id_pet)
    {
        $elkExists = (new Query())
            ->from(ElkPets::tableName())
            ->where(['id_pet' => $id_pet])
            ->one();

        return ($elkExists === false) ? null : $elkExists['ext_id'];
    }

    /**
     * @param int $id_pet
     * @return string|null
     */
    private function findChip($id_pet)
    {
        $chipExists = (new Query())
            ->from(PetIdentification::tableName())
            ->where([
                'id_pet' => $id_pet,
                'id_ident_type' => 1,
            ])
            ->one();

        return ($chipExists === false) ? null : $chipExists['identification_code'];
    }

    /**
     * @param int $id_pet
     * @return int
     */
    private function countVisits($id_pet)
    {
        return (new Query())
            ->from(Visits::tableName())
            ->where(['id_pet' => $id_pet])
            ->count();
    }

    /**
     * @param string $filename
     * @param array $data
     */
    private function writeCsv($filename, $data)
    {
        $dir = \Yii::getAlias('@runtime/logs/automerge');
        if (!FileHelper::createDirectory($dir)) {
            throw new Exception('Failed to create directory');
        }

        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $data, ';') === false) {
            throw new Exception('Failed to format csv');
        }
        rewind($f);
        $line = stream_get_contents($f);
        fclose($f);

        file_put_contents($dir . '/' . $filename, $line, FILE_APPEND | FILE_TEXT | LOCK_EX);
    }
}
