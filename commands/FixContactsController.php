<?php

namespace app\commands;

use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\models\db\ShiftType;
use app\models\db\Visits;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\validators\EmailValidator;
use yii\validators\RegularExpressionValidator;

/**
 * Class FixContactsController
 * @package app\commands
 *
 * Последовательно(!) выполнить консольные команды:
 *
 * > php yii fix-contacts/normalize
 * > php yii fix-contacts/uniq
 * > php yii fix-contacts/main-phone
 *
 * Удаленные и измененные контакты будут сохранены в таблицах contacts_deleted и contacts_updated
 */
class FixContactsController extends Controller
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
                'entity_type' => 'pet_owner',
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
        // непонятный баг - в период с 01.03 по 17.05 были созданы одинаковые контакты у разных владельцев
        // +79629240998 (532 контакта), sharikina28389@mail.ru (531 контакта)
        // (при этом created_by не было заполнено)
        $bugs = [
            '+79629240998',
            'sharikina28389@mail.ru',
        ];
        // прочие массовые присвоения одного контакта разным владельцам
        // ludakudalb@mail.ru (87), +79262303888 (84), +79774439425 (40), vetdoctor-cao@mail.ru (40), +79096547620 (34), suvlvet@mail.ru (34)
        $bugs2 = [
            'ludakudalb@mail.ru',
            '+79262303888',
            '+79774439425',
            'vetdoctor-cao@mail.ru',
            '+79096547620',
            'suvlvet@mail.ru',
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
            if (in_array($value, $bugs, true)) {
                $this->deleteRecord($row, 'Баг с одинаковыми контактами у разных владельцев с 01.03 по 17.05');
                $deleted++;
                $processed++;
                Console::updateProgress($processed, $total);
                continue;
            }
            if (in_array($value, $bugs2, true)) {
                $this->deleteRecord($row, 'Прочие массовые присвоения одного контакта разным владельцам');
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
     * Удаляем неуникальные контакты
     * @param int $dryRun
     * @return int
     */
    public function actionUniq($dryRun = null)
    {
        $this->dryRun = (!empty($dryRun) && strtolower($dryRun) !== 'false');
        $this->prepareLogTables();

        $sql = 'select distinct "name", count("name") as cnt from contacts where entity_type=\'pet_owner\' group by "name" order by cnt desc';

        $total = \Yii::$app->db
            ->createCommand('select count(*) from contacts where "name" in (select "name" from (' . $sql . ') subq where cnt > 1)')
            ->queryScalar();

        $totalNames = \Yii::$app->db
            ->createCommand('select count("name") from (' . $sql . ') subq where cnt > 1')
            ->queryScalar();

        $processed = 0;
        $deleted = 0;
        $skipped = 0;

        Console::startProgress($processed, $total, '', false);

        $query = (new Query())
            ->from(new Expression('(' . $sql . ') subq where cnt > 1'));

        foreach ($query->each() as $row) {
            $records = (new Query())
                ->select('c.*')
                ->addSelect('po.snils, po.f_fio, po.i_fio, po.o_fio')
                ->from(Contacts::tableName() . ' c')
                ->leftJoin(PetOwners::tableName() . ' po', 'po.id = c.entity_id')
                ->where([
                    'entity_type' => 'pet_owner',
                    'name' => $row['name'],
                ])
                ->orderBy(['id' => SORT_ASC])
                ->indexBy('id')
                ->all();

            $ownerIds = ArrayHelper::getColumn($records, 'entity_id');
            $ownerIds = array_unique($ownerIds);

            if (count($ownerIds) == 1) {
                // все контакты привязаны к одному владельцу, оставляем один
                $mainFound = false;
                $i = 0;
                foreach ($records as $record) {
                    $i++;
                    if ($record['main_flag'] === true && $mainFound === false) {
                        // оставляем основной контакт
                        $mainFound = true;
                        $skipped++;
                        $processed++;
                        Console::updateProgress($processed, $total);
                        continue;
                    } else {
                        if ($i == count($records) && $mainFound === false) {
                            // оставляем последний контакт
                            $skipped++;
                        } else {
                            // остальные удаляем
                            $this->deleteRecord($record, 'Дублирующийся контакт у одного владельца');
                            $deleted++;
                        }
                        $processed++;
                        Console::updateProgress($processed, $total);
                    }
                }
            } else {
                // контакты привязаны к нескольким владельцам, проверяем снилс
                $snils = ArrayHelper::getColumn($records, 'snils');
                $snils = array_unique(array_filter($snils));
                $countSnils = count($snils);
                if ($countSnils > 1) {
                    // снилс есть у нескольких - удаляем контакты у всех владельцев
                    foreach ($records as $record) {
                        $this->deleteRecord($record, 'Дублирующийся контакт у нескольких владельцев, несколько СНИЛС');
                        $deleted++;
                        $processed++;
                        Console::updateProgress($processed, $total);
                    }
                } elseif ($countSnils == 1) {
                    // оставляем один контакт у владельца со снилс
                    $oneFound = false;
                    foreach ($records as $record) {
                        if (!empty($record['snils']) && $oneFound === false) {
                            $oneFound = true;
                            $skipped++;
                            $processed++;
                            Console::updateProgress($processed, $total);
                            continue;
                        }
                        $this->deleteRecord($record, 'Дублирующийся контакт у нескольких владельцев, оставлен контакт у владельца со СНИЛС');
                        $deleted++;
                        $processed++;
                        Console::updateProgress($processed, $total);
                    }
                } else {
                    // снилс нет ни у кого - проверяем наличие приемов с mos.ru
                    $visits = (new Query())
                        ->select('v.*')
                        ->addSelect('sh.id AS shift_type_id')
                        ->from(Visits::tableName() . ' v')
                        ->leftJoin(ShiftType::tableName() . ' sh', 'sh.id = v.channel')
                        ->where(['sh.type' => ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT])
                        ->andWhere(['in', 'id_owner', $ownerIds])
                        ->all();

                    if (!empty($visits)) {
                        $visitsOwnerIds = ArrayHelper::getColumn($visits, 'id_owner');
                        $visitsOwnerIds = array_unique($visitsOwnerIds);
                        if (count($visitsOwnerIds) == 1) {
                            // прием с mos.ru есть у одного, удаляем остальные
                            $ownerId = reset($visitsOwnerIds);
                            $oneFound = false;
                            foreach ($records as $record) {
                                if ($record['entity_id'] == $ownerId && $oneFound === false) {
                                    $oneFound = true;
                                    $skipped++;
                                    $processed++;
                                    Console::updateProgress($processed, $total);
                                    continue;
                                }
                                $this->deleteRecord($record, 'Дублирующийся контакт у нескольких владельцев, оставлен контакт у владельца с mos.ru');
                                $deleted++;
                                $processed++;
                                Console::updateProgress($processed, $total);
                            }
                        } else {
                            // прием с mos.ru есть у нескольких - удаляем контакты у всех владельцев
                            foreach ($records as $record) {
                                $this->deleteRecord($record, 'Дублирующийся контакт у нескольких владельцев, несколько приемов с mos.ru');
                                $deleted++;
                                $processed++;
                                Console::updateProgress($processed, $total);
                            }
                        }
                    } else {
                        // приемов с mos.ru нет ни у кого, проверяем наличие фамилии, имени, отчества:
                        // - есть у одного - оставляем контакт у владельца с полным ФИО
                        // - есть у нескольких или нет ни у одного - оставляем контакт у владельца с наибольшим ID
                        ArrayHelper::multisort($records, 'entity_id', SORT_DESC);
                        $i = 0;
                        $oneFound = false;
                        foreach ($records as $record) {
                            $i++;
                            if ($oneFound === false
                                && ((!empty($record['f_fio']) && !empty($record['i_fio']) && !empty($record['o_fio']))
                                    || $i == count($records))) {
                                $oneFound = true;
                                $skipped++;
                                $processed++;
                                Console::updateProgress($processed, $total);
                                continue;
                            }
                            $this->deleteRecord($record, 'Дублирующийся контакт у нескольких владельцев, удаление после проверки по ФИО');
                            $deleted++;
                            $processed++;
                            Console::updateProgress($processed, $total);
                        }
                    }
                }
            }
        }

        Console::endProgress();

        Console::output('Duplicate names: ' . $totalNames);
        Console::output('Total records:   ' . $total);
        Console::output('Processed:       ' . $processed);
        Console::output('Skipped:         ' . $skipped);
        Console::output('Deleted:         ' . $deleted);

        return ExitCode::OK;
    }

    /**
     * Изменить у телефонов владельцев тип контакта "Основной телефон"
     * @param int $dryRun
     * @return int
     */
    public function actionMainPhone($dryRun = null)
    {
        $this->dryRun = (!empty($dryRun) && strtolower($dryRun) !== 'false');
        $this->prepareLogTables();

        $types = $this->findContactTypes();
        $map = ArrayHelper::map($types, 'name', function ($el) {
            return $el['id'];
        });

        $oldMainTypeId = ArrayHelper::getValue($map, 'Основной телефон');
        $mobileTypeId = ArrayHelper::getValue($map, 'Мобильный телефон');
        $homeTypeId = ArrayHelper::getValue($map, 'Домашний телефон');

        if (empty($oldMainTypeId)) {
            Console::output('Main phone contact type not found');

            return ExitCode::OK;
        }
        if (empty($mobileTypeId)) {
            Console::output('Main phone contact type not found');

            return ExitCode::OK;
        }
        if (empty($homeTypeId)) {
            Console::output('Main phone contact type not found');

            return ExitCode::OK;
        }

        $query = (new Query())
            ->from(Contacts::tableName())
            ->where([
                'entity_type' => 'pet_owner',
                'id_contact_type' => $oldMainTypeId,
            ])
            ->orderBy(['id' => SORT_ASC]);

        $total = $query->count();

        $processed = 0;
        $updated = 0;

        Console::startProgress($processed, $total, '', false);

        foreach ($query->each() as $row) {
            // в зависимости от того, что идет в записи по номеру телефона после +7:
            // если 9, то мобильный. Если любая другая цифра, то домашний.
            $first = substr($row['name'], 0, 3);
            $newType = ($first == '+79') ? $mobileTypeId : $homeTypeId;
            $this->updateRecord($row, ['id_contact_type' => $newType], 'Заменен тип телефона на ' . $newType . ' [' . ($newType == $mobileTypeId ? 'мобильный' : 'домашний') . ']');
            $updated++;
            $processed++;
            Console::updateProgress($processed, $total);
        }

        Console::endProgress();

        if ($this->dryRun !== true) {
            \Yii::$app->db
                ->createCommand()
                ->delete(ContactTypes::tableName(), ['id' => $oldMainTypeId])
                ->execute();
        }

        Console::output('Total:     ' . $total);
        Console::output('Processed: ' . $processed);
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
                'entity_type' => 'pet_owner',
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

        foreach (['snils', 'f_fio', 'i_fio', 'o_fio'] as $key) {
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
