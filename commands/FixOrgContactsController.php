<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.19
 * Time: 14:50
 */

namespace app\commands;

use app\models\db\Contacts;
use app\models\db\ContactTypes;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\validators\EmailValidator;
use yii\validators\RegularExpressionValidator;

/**
 * Class FixOrgContactsController
 * @package app\commands
 *
 * Последовательно(!) выполнить консольные команды:
 *
 * > php yii fix-org-contacts/normalize
 *
 * Удаленные и измененные контакты будут сохранены в таблицах contacts_deleted и contacts_updated
 */
class FixOrgContactsController extends Controller
{
    private $dryRun = false;

    /**
     * Приводим к единому формату телефоны
     * Удаляем невалидные телефоны и email
     * @param int $dryRun
     * @return int
     */
    public function actionNormalize($dryRun = null)
    {
        $this->dryRun = (!empty($dryRun) && strtolower($dryRun) !== 'false');
        $this->prepareLogTables(true);

        $types = $this->findContactTypes();

        $query = (new Query())
            ->from(Contacts::tableName())
            ->where([
                'entity_type' => 'organization',
            ])
            ->orderBy(['id' => SORT_ASC]);

        $total = $query->count();
        $processed = 0;
        $deleted = 0;
        $skipped = 0;
        $updated = 0;

        // откровенно левые телефоны
        $dummy = [
            '+71111111111',
            '+70000000000',
        ];

        Console::startProgress($processed, $total, '', false);

        foreach ($query->each() as $row) {
            $value = trim($row['name']);

            if (in_array($value, $dummy, true)) {
                $this->deleteRecord($row, 'От балды забитый телефон');
                $deleted++;
                $processed++;
                Console::updateProgress($processed, $total);
                continue;
            }

            if (!array_key_exists($row['id_contact_type'], $types)) {
                $this->deleteRecord($row, 'Неизвестный тип контакта');
                $deleted++;
                $processed++;
                Console::updateProgress($processed, $total);
                continue;
            }

            $type = $types[$row['id_contact_type']];

            if ($type->type != ContactTypes::TYPE_EMAIL && $type->type != ContactTypes::TYPE_PHONE) {
                $skipped++;
                $processed++;
                Console::updateProgress($processed, $total);
                continue;
            }

            if ($type->type == ContactTypes::TYPE_EMAIL) {
                // просто удаляем невалидные
                $validator = new EmailValidator();
                if (!$validator->validate($value)) {
                    $this->deleteRecord($row, 'Невалидный email');
                    $deleted++;
                } elseif ($value !== $row['name']) {
                    $this->updateRecord($row, $value, 'Удалены лишние пробелы');
                    $updated++;
                }
                $processed++;
                Console::updateProgress($processed, $total);
                continue;
            }

            if ($type->type == ContactTypes::TYPE_PHONE) {
                $validator = new RegularExpressionValidator([
                    'pattern' => Contacts::PHONE_REG_EXP,
                ]);
                if ($validator->validate($value)) {
                    if ($value !== $row['name']) {
                        $this->updateRecord($row, $value, 'Удалены лишние пробелы');
                        $updated++;
                    }
                } else {
                    // с телефонами будет больше работы
                    $value = preg_replace('#\D#', '', $value);
                    $len = strlen($value);
                    if ($len < 10 || $len > 11) {
                        // Менее 10 символов - Удалить
                        // Более 11 символов - Удалить
                        $this->deleteRecord($row, 'Невалидный телефон');
                        $deleted++;
                    } elseif ($len == 10) {
                        // Удалить номера, начинающиеся с 0, 7, 89
                        // Остальным добавить +7 в начало записи
                        $first = substr($value, 0, 1);
                        $first2 = substr($value, 0, 2);
                        if ($first == '0' || $first == '7' || $first2 == '89') {
                            $this->deleteRecord($row, 'Невалидный телефон');
                            $deleted++;
                        } else {
                            $value = '+7' . $value;
                            $this->updateRecord($row, $value, 'Добавлено +7');
                            $updated++;
                        }
                    } else {
                        // Для номеров, начинающихся с 7 - добавить «+» в начало записи
                        // Для номеров, начинающихся с 8 – заменить 8 на 7 и добавить «+» в начало записи
                        // Остальные удалить
                        $first = substr($value, 0, 1);
                        if ($first == '7') {
                            $value = '+' . $value;
                            $this->updateRecord($row, $value, 'Добавлен +');
                            $updated++;
                        } elseif ($first == '8') {
                            $value = '+7' . substr($value, 1);
                            $this->updateRecord($row, $value, 'Добавлен +, заменена начальная 8 на 7');
                            $updated++;
                        } else {
                            $this->deleteRecord($row, 'Невалидный телефон');
                            $deleted++;
                        }
                    }
                }
                $processed++;
                Console::updateProgress($processed, $total);
            }
        }

        Console::endProgress();

        Console::output('Total:     ' . $total);
        Console::output('Processed: ' . $processed);
        Console::output('Skipped:   ' . $skipped);
        Console::output('Deleted:   ' . $deleted);
        Console::output('Updated:   ' . $updated);

        return ExitCode::OK;
    }

    /**
     * @return \app\models\db\ContactTypes[]
     */
    private function findContactTypes()
    {
        return ContactTypes::find()
            ->where([
                'entity_type' => 'organization',
            ])
            ->orderBy(['id' => SORT_ASC])
            ->indexBy('id')
            ->all();
    }

    /**
     * @param array  $record
     * @param string $reason
     * @throws \yii\db\Exception
     */
    private function deleteRecord($record, $reason)
    {
        $log = $record;
        $log['iteration'] = $this->action->id;
        $log['delete_reason'] = $reason;

        foreach (['short_name'] as $key) {
            ArrayHelper::remove($log, $key);
        }

        \Yii::$app->db
            ->createCommand()
            ->insert('contacts_deleted', $log)
            ->execute();

        if ($this->dryRun === true) {
            return;
        }

        \Yii::$app->db
            ->createCommand()
            ->delete(Contacts::tableName(), ['id' => $record['id']])
            ->execute();
    }

    /**
     * @param array  $record
     * @param string $value
     * @param string $reason
     * @throws \yii\db\Exception
     */
    private function updateRecord($record, $value, $reason)
    {
        $log = $record;
        $log['iteration'] = $this->action->id;
        $log['update_reason'] = $reason;
        $log['new_value'] = is_array($value) ? reset($value) : $value;

        \Yii::$app->db
            ->createCommand()
            ->insert('contacts_updated', $log)
            ->execute();

        if ($this->dryRun === true) {
            return;
        }

        $id = ArrayHelper::remove($record, 'id');
        if (is_array($value)) {
            // передано другое поле для обновления
            foreach ($value as $key => $v) {
                $record[$key] = $v;
            }
        } else {
            // по умолчанию обновляем name
            $record['name'] = $value;
        }

        \Yii::$app->db
            ->createCommand()
            ->update(Contacts::tableName(), $record, ['id' => $id])
            ->execute();
    }

    /**
     * @param bool $fullCleanup
     * @throws \yii\db\Exception
     */
    private function prepareLogTables($fullCleanup = false)
    {
        $sql1 = <<<SQL
create table if not exists contacts_deleted
(
	iteration varchar(255),
	id integer not null,
	id_contact_type integer not null,
	entity_type varchar(50) not null,
	entity_id integer not null,
	name varchar(255) not null,
	created_by integer,
	updated_by integer,
	created_at timestamp,
	updated_at timestamp,
	main_flag boolean default false not null,
	confirmed boolean default false,
	delete_reason varchar(255)
);
SQL;

        $sql2 = <<<SQL
create table if not exists contacts_updated
(
	iteration varchar(255),
	id integer not null,
	id_contact_type integer not null,
	entity_type varchar(50) not null,
	entity_id integer not null,
	name varchar(255) not null,
	created_by integer,
	updated_by integer,
	created_at timestamp,
	updated_at timestamp,
	main_flag boolean default false not null,
	confirmed boolean default false,
	new_value varchar(255) not null,
	update_reason varchar(255)
);
SQL;

        \Yii::$app->db->createCommand($sql1)->execute();
        \Yii::$app->db->createCommand($sql2)->execute();

        if ($fullCleanup === true) {
            \Yii::$app->db->createCommand()->truncateTable('contacts_deleted')->execute();
            \Yii::$app->db->createCommand()->truncateTable('contacts_updated')->execute();
        } else {
            \Yii::$app->db->createCommand()->delete('contacts_deleted', ['iteration' => $this->action->id])->execute();
            \Yii::$app->db->createCommand()->delete('contacts_updated', ['iteration' => $this->action->id])->execute();
        }
    }

}