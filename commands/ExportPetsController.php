<?php

namespace app\commands;

use app\models\db\Addresses;
use app\models\db\RegExpireReasons;
use app\models\db\Species;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Organizations;
use app\models\db\Specialists;
use yii\helpers\FileHelper;

/**
 * Class ExportPetsController
 * @package app\commands
 */
class ExportPetsController extends Controller
{
    private $time_start;
    private $dstPath = '@runtime/export';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->time_start = time();
        $this->dstPath = \Yii::getAlias($this->dstPath);
        if (!FileHelper::createDirectory($this->dstPath)) {
            throw new InvalidConfigException('Target directory not exists');
        }
    }

    /**
     * По каждой организации сделать выгрузку заведенных ими владельцев и животных в csv со следующими полями:
     * 1) Краткое название организации ,
     * 2) ФИО владельца,
     * 3) Адрес владельца,
     * 4) ФИО кем сделана запись (владельца),
     * 5) Дата создания(владельца),
     * 6) ФИО кем изменена запись (владельца),
     * 7) дата изменения(владельца),
     * 8) кличка животного,
     * 9) вид животного,
     * 10) идентификатор животного (номер чипа / бирки),
     * 11) ФИО кем сделана запись (животного),
     * 12) Дата создания(животного),
     * 13) ФИО кем изменена запись (животного),
     * 14) дата изменения(животного).
     * Если значений где-то нет - все равно выгружаем
     *
     * Выгрузка в один файл:
     *   > php yii export-pets/run-1
     *
     * Выгрузка в несколько файлов (по организациям):
     *   > php yii export-pets/run-1 1
     *
     * @param int $splitFiles
     * @return int
     */
    public function actionRun1($splitFiles = null)
    {
        $rows = (new Query())
            ->select([
                '[[o]].[[short_name]] as org_name',
                '[[po]].[[fullname]] as owner_fullname',
                '[[a]].[[name]] as address',
                '[[po]].[[created_by]] as po_created_by',
                '[[po]].[[created_at]]::date as po_created_at',
                '[[po]].[[updated_by]] as po_updated_by',
                '[[po]].[[updated_at]]::date as po_updated_at',
                '[[p]].[[name]]',
                '[[s]].[[name]] as species_name',
                '[[p]].[[identification_code]]',
                '[[p]].[[created_by]]',
                '[[p]].[[created_at]]::date',
                '[[p]].[[updated_by]]',
                '[[p]].[[updated_at]]::date',
            ])
            ->from(Pets::tableName() . ' p')
            ->innerJoin(Organizations::tableName() . ' o', '[[p]].[[id_reg_organization]] = [[o]].[[id]]')
            ->leftJoin(PetOwners::tableName() . ' po', '[[p]].[[id_owner]] = [[po]].[[id]]')
            ->leftJoin(Addresses::tableName() . ' a', '[[po]].[[id_address]] = [[a]].[[id]]')
            ->leftJoin(Species::tableName() . ' s', '[[p]].[[id_species]] = [[s]].[[id]]')
            ->where(['not', ['[[p]].[[id_reg_organization]]' => null]])
            ->orderBy([
                '[[p]].[[id_reg_organization]]' => SORT_ASC,
                '[[p]].[[name]]' => SORT_ASC,
            ])
            ->all();

        $total = count($rows);

        if ($splitFiles == 1) {
            $orgsMap = $this->orgsMap();
        }

        $specsMap = $this->specsMap();

        $last = null;
        $filename = ($splitFiles == 1) ? null : $this->filename('pets');
        $processed = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($rows as $i => $row) {
            if ($splitFiles == 1) {
                if ($last === null || $row['org_name'] !== $last) {
                    $last = $row['org_name'];
                    $file = 'pets-org-' . array_search($row['org_name'], $orgsMap);
                    $filename = $this->filename($file);
                }
            }

            $row['po_created_by'] = empty($row['po_created_by']) ? null : ArrayHelper::getValue($specsMap, $row['po_created_by']);
            $row['po_updated_by'] = empty($row['po_updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['po_updated_by']);
            $row['created_by'] = empty($row['created_by']) ? null : ArrayHelper::getValue($specsMap, $row['created_by']);
            $row['updated_by'] = empty($row['updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['updated_by']);

            $this->writeLn($filename, array_values($row));
            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();
        Console::output('Total pets: ' . $total);

        return ExitCode::OK;
    }

    /**
     * Для записей владельцев и животных, которые были заведены не в рамках какой-то организации
     * сделать такую же выгрузку как в п1, но без названия организации
     *
     *   > php yii export-pets/run-2
     *
     * @return int
     */
    public function actionRun2()
    {
        $rows = (new Query())
            ->select([
                '[[po]].[[fullname]] as owner_fullname',
                '[[a]].[[name]] as address',
                '[[po]].[[created_by]] as po_created_by',
                '[[po]].[[created_at]]::date as po_created_at',
                '[[po]].[[updated_by]] as po_updated_by',
                '[[po]].[[updated_at]]::date as po_updated_at',
                '[[p]].[[name]]',
                '[[s]].[[name]] as species_name',
                '[[p]].[[identification_code]]',
                '[[p]].[[created_by]]',
                '[[p]].[[created_at]]::date',
                '[[p]].[[updated_by]]',
                '[[p]].[[updated_at]]::date',
            ])
            ->from(Pets::tableName() . ' p')
            ->leftJoin(PetOwners::tableName() . ' po', '[[p]].[[id_owner]] = [[po]].[[id]]')
            ->leftJoin(Addresses::tableName() . ' a', '[[po]].[[id_address]] = [[a]].[[id]]')
            ->leftJoin(Species::tableName() . ' s', '[[p]].[[id_species]] = [[s]].[[id]]')
            ->where(['[[p]].[[id_reg_organization]]' => null])
            ->orderBy(['[[p]].[[name]]' => SORT_ASC])
            ->all();

        $total = count($rows);

        $specsMap = $this->specsMap();

        $filename = $this->filename('pets-no-org');
        $processed = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($rows as $i => $row) {
            $row['po_created_by'] = empty($row['po_created_by']) ? null : ArrayHelper::getValue($specsMap, $row['po_created_by']);
            $row['po_updated_by'] = empty($row['po_updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['po_updated_by']);
            $row['created_by'] = empty($row['created_by']) ? null : ArrayHelper::getValue($specsMap, $row['created_by']);
            $row['updated_by'] = empty($row['updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['updated_by']);

            $this->writeLn($filename, array_values($row));
            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();
        Console::output('Total pets: ' . $total);

        return ExitCode::OK;
    }

    /**
     * Выгрузить список владельцев, не имющих животных в формате:
     * 1) Краткое название организации,
     * 2) ФИО владельца,
     * 3) Адрес владельца,
     * 4) ФИО кем сделана запись (владельца),
     * 5) Дата создания(владельца),
     * 6) ФИО кем изменена запись (владельца),
     * 7) дата изменения(владельца).
     * Если значений где-то нет - все равно выгружаем
     *
     *   > php yii export-pets/run-3
     *
     * @return int
     */
    public function actionRun3()
    {
        $rows = (new Query())
            ->select([
                '[[po]].[[fullname]] as owner_fullname',
                '[[a]].[[name]] as address',
                '[[po]].[[created_by]] as po_created_by',
                '[[po]].[[created_at]]::date as po_created_at',
                '[[po]].[[updated_by]] as po_updated_by',
                '[[po]].[[updated_at]]::date as po_updated_at',
            ])
            ->from(PetOwners::tableName() . ' po')
            ->leftJoin(Addresses::tableName() . ' a', '[[po]].[[id_address]] = [[a]].[[id]]')
            ->where([
                'not in',
                '[[po]].[[id]]',
                (new Query())
                    ->select('id_owner')
                    ->from(Pets::tableName())
                    ->where(['not', ['id_owner' => null]])
            ])
            ->orderBy(['[[po]].[[id]]' => SORT_ASC])
            ->all();

        $total = count($rows);
        $specsMap = $this->specsMap();
        $orgsMap = $this->orgsMap();
        $specsOrgsMap = $this->specsOrgsMap();

        $filename = $this->filename('owners-no-pets');
        $processed = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($rows as $i => $row) {
            $short_name = '';
            if (!empty($row['po_created_by'])) {
                $id_organization = ArrayHelper::getValue($specsOrgsMap, $row['po_created_by']);
                if (!empty($id_organization)) {
                    $short_name = ArrayHelper::getValue($orgsMap, $id_organization);
                }
            }
            $row['po_created_by'] = empty($row['po_created_by']) ? null : ArrayHelper::getValue($specsMap, $row['po_created_by']);
            $row['po_updated_by'] = empty($row['po_updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['po_updated_by']);
            $row['created_by'] = empty($row['created_by']) ? null : ArrayHelper::getValue($specsMap, $row['created_by']);
            $row['updated_by'] = empty($row['updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['updated_by']);

            $this->writeLn($filename, array_merge([$short_name], array_values($row)));
            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();
        Console::output('Total owners: ' . $total);

        return ExitCode::OK;
    }

    /**
     * Подготовить запрос на удаление по списку из п.3
     *
     * Прогон без удаления (dry run):
     *   > php yii export-pets/run-4
     *
     * Прогон с удалением:
     *   > php yii export-pets/run-4 1
     *
     * @param int $execute
     * @return int
     */
    public function actionRun4($execute = null)
    {
        $rows = (new Query())
            ->select([
                '[[po]].[[id]]',
                '[[po]].[[fullname]] as owner_fullname',
            ])
            ->from(PetOwners::tableName() . ' po')
            ->where([
                'not in',
                '[[po]].[[id]]',
                (new Query())
                    ->select('id_owner')
                    ->from(Pets::tableName())
                    ->where(['not', ['id_owner' => null]])
            ])
            ->orderBy(['[[po]].[[id]]' => SORT_ASC])
            ->all();

        $total = count($rows);

        $processed = 0;
        $deleted = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($rows as $i => $row) {
            if ($execute == 1) {
                try {
                    \Yii::$app->db
                        ->createCommand()
                        ->delete(PetOwners::tableName(), ['id' => $row['id']])
                        ->execute();
                    $deleted++;
                } catch (\Throwable $e) {
                    Console::output('Error deleting ID ' . $row['id'] . ' | ' . $row['fullname']);
                }
            }

            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();
        Console::output('Total owners: ' . $total);
        Console::output('Deleted owners: ' . $deleted);

        return ExitCode::OK;
    }

    /**
     * Проверить поле «Организация регистрации» в карточке животного,
     * почему у некоторых животных не заполнено - возможно связано с тем, что изначально оно не заполнялось.
     * Возможно ли будет восстановить по логину того, кто заводил?
     *
     * Прогон без обновления (dry run):
     *   > php yii export-pets/run-5
     *
     * Прогон с удалением:
     *   > php yii export-pets/run-5 1
     *
     * @param int $execute
     * @return int
     */
    public function actionRun5($execute = null)
    {
        $rows = (new Query())
            ->select([
                '[[p]].[[id]]',
                '[[p]].[[name]]',
                '[[s]].[[name]] as species_name',
                '[[p]].[[identification_code]]',
                '[[p]].[[created_by]]',
                '[[p]].[[created_at]]::date',
            ])
            ->from(Pets::tableName() . ' p')
            ->leftJoin(Species::tableName() . ' s', '[[p]].[[id_species]] = [[s]].[[id]]')
            ->where(['[[p]].[[id_reg_organization]]' => null])
            ->andWhere(['not', ['[[p]].[[created_by]]' => null]])
            ->orderBy(['[[p]].[[name]]' => SORT_ASC])
            ->all();

        $total = count($rows);

        $orgsMap = $this->orgsMap();
        $specsMap = $this->specsMap();
        $specsOrgsMap = $this->specsOrgsMap();

        $filename = $this->filename('pets-restore-org');
        $processed = 0;
        $possible = 0;
        $updated = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($rows as $i => $row) {
            $id_organization = ArrayHelper::getValue($specsOrgsMap, $row['created_by']);
            if (empty($id_organization)) {
                $processed++;
                continue;
            }
            $possible++;
            $id = ArrayHelper::remove($row, 'id');
            $short_name = ArrayHelper::getValue($orgsMap, $id_organization);

            $row['created_by'] = empty($row['created_by']) ? null : ArrayHelper::getValue($specsMap, $row['created_by']);
            $row['updated_by'] = empty($row['updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['updated_by']);

            $this->writeLn($filename, array_merge([$short_name], array_values($row)));

            if ($execute == 1) {
                try {
                    \Yii::$app->db
                        ->createCommand()
                        ->update(Pets::tableName(), ['id_reg_organization' => $id_organization, 'reg_date' => $row['created_at']], ['id' => $id])
                        ->execute();
                    $updated++;
                } catch (\Throwable $e) {
                    Console::output('Error updating ID ' . $row['id'] . ' | ' . $row['name']);
                }
            }

            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();
        Console::output('Total pets: ' . $total);
        Console::output('Can restore: ' . $possible);
        Console::output('Updated: ' . $updated);

        return ExitCode::OK;
    }

    /**
     * Выгрузить список животных в статусе "Снят с учета" с причиной По инициативе владельца. в формате:
     * 1) Краткое название организации ,
     * 2) ФИО владельца,
     * 3) Адрес владельца,
     * 4) кличка животного,
     * 5) вид животного,
     * 6) идентификатор животного (номер чипа / бирки),
     * 7) ФИО кем изменена запись (животного),
     * 8) дата изменения(животного)
     *
     *   > php yii export-pets/run-6
     *
     * @return int
     */
    public function actionRun6()
    {
        $id_reg_expire_reason = (new Query())
            ->select('id')
            ->from(RegExpireReasons::tableName())
            ->where(['name' => 'по инициативе владельца'])
            ->scalar();

        if (empty($id_reg_expire_reason)) {
            Console::output('Not found ID for reg expire reason');
            return ExitCode::DATAERR;
        }

        $rows = (new Query())
            ->select([
                '[[o]].[[short_name]] as org_name',
                '[[po]].[[fullname]] as owner_fullname',
                '[[a]].[[name]] as address',
                '[[p]].[[name]]',
                '[[s]].[[name]] as species_name',
                '[[p]].[[identification_code]]',
                '[[p]].[[created_by]]',
                '[[p]].[[created_at]]::date',
                '[[p]].[[updated_by]]',
                '[[p]].[[updated_at]]::date',
            ])
            ->from(Pets::tableName() . ' p')
            ->leftJoin(Organizations::tableName() . ' o', '[[p]].[[id_reg_organization]] = [[o]].[[id]]')
            ->leftJoin(PetOwners::tableName() . ' po', '[[p]].[[id_owner]] = [[po]].[[id]]')
            ->leftJoin(Addresses::tableName() . ' a', '[[po]].[[id_address]] = [[a]].[[id]]')
            ->leftJoin(Species::tableName() . ' s', '[[p]].[[id_species]] = [[s]].[[id]]')
            ->where(['[[p]].[[id_reg_expire_reason]]' => $id_reg_expire_reason])
            ->orderBy([
                '[[p]].[[name]]' => SORT_ASC,
            ])
            ->all();

        $total = count($rows);

        $specsMap = $this->specsMap();

        $filename = $this->filename('pets-reg-expired');
        $processed = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($rows as $i => $row) {

            $row['created_by'] = empty($row['created_by']) ? null : ArrayHelper::getValue($specsMap, $row['created_by']);
            $row['updated_by'] = empty($row['updated_by']) ? null : ArrayHelper::getValue($specsMap, $row['updated_by']);

            $this->writeLn($filename, array_values($row));
            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();
        Console::output('Total pets: ' . $total);

        return ExitCode::OK;
    }

    /**
     * @param string $name
     * @return string
     */
    private function filename($name)
    {
        return 'export-' . $this->time_start . '-' . $name . '.csv';
    }

    /**
     * @param string $filename
     * @param array $data
     */
    private function writeLn($filename, $data)
    {
        $str = '';
        foreach ($data as $val) {
            $str .= ($val === null) ? '' : str_replace(';', ',', $val);
            $str .= ';';
        }
        $str = rtrim($str, ';');

        file_put_contents($this->dstPath . DIRECTORY_SEPARATOR . $filename, $str . "\r\n", FILE_TEXT | FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array
     */
    private function orgsMap(): array
    {
        $orgs = (new Query())
            ->from(Organizations::tableName())
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return ArrayHelper::map($orgs, 'id', 'short_name');
    }

    /**
     * @return array
     */
    private function specsMap(): array
    {
        $specs = (new Query())
            ->from(Specialists::tableName())
            ->orderBy(['id_user' => SORT_ASC])
            ->where(['not', ['id_user' => null]])
            ->all();

        return ArrayHelper::map($specs, 'id_user', 'fullname');
    }

    /**
     * @return array
     */
    private function specsOrgsMap(): array
    {
        $specs = (new Query())
            ->from(Specialists::tableName())
            ->orderBy(['id_user' => SORT_ASC])
            ->where(['not', ['id_user' => null]])
            ->all();

        return ArrayHelper::map($specs, 'id_user', 'id_organization');
    }
}
