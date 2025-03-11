<?php

namespace app\modules\audit\models;

use app\models\db\audit\AuditLog;

class AuditLogHelper
{
    public static $action_texts = [
        AuditLog::ACTION_SYSTEM_FAIL => 'Ошибка при создании снимка',
        AuditLog::ACTION_TRASH => 'Пометка "удалено"',
        AuditLog::ACTION_UPDATE => 'Редактирование',
        AuditLog::ACTION_INSERT => 'Добавление',
        AuditLog::ACTION_DELETE => 'Удаление',
        AuditLog::ACTION_MULTIPLE_SAVE => 'Массовое редактирование'
    ];

    protected static $tables_text = [
        'public.organizations' => 'Организация',
        'public.pets_to_owner' => 'Связь животное-владелец',
        'public.pets' => 'Животное',
        'public.pet_rabies_vaccination' => 'Вакцинации животного: от бешенства',
        'public.pet_other_vaccinations' => 'Вакцинации животного: иное',
        'public.pet_ectoparasites' => 'Вакцинации животного: против эктопаразитов',
        'public.pet_dehelmintization' => 'Дегельминтизация',
        'public.pet_identification' => 'Идентификация',

        'public.contacts' => 'Контакты',
        'public.pet_owners' => 'Владелец',
        AuditLog::ERROR_LOG_FAKE_TABLE => 'Запись о системной ошибке',
        AuditLog::PETS_VACCINATION_FAKE_TABLE => 'Вакцинации животного',

    ];

    protected static $description_template = [
        'public.organizations' => [
            AuditLog::ACTION_TRASH => 'Организация помечена как удаленная ',
            AuditLog::ACTION_DELETE => 'Организация удалена ',
            AuditLog::ACTION_UPDATE => 'Организация обновлена ',
            AuditLog::ACTION_INSERT => 'Организация добавлена ',
        ],
        'public.pets_to_owner' => [
            AuditLog::ACTION_TRASH => 'Связь животное-владелец с ID %id помечена как удаленная',
            AuditLog::ACTION_DELETE => 'Связь животное-владелец с ID %id удалена ',
            AuditLog::ACTION_UPDATE => 'Связь животное-владелец с ID %id обновлена ',
            AuditLog::ACTION_INSERT => 'Связь животное-владелец с ID %id добавлена ',
        ],
        'public.pets' => [
            AuditLog::ACTION_TRASH => 'Животное помечено как удаленное ',
            AuditLog::ACTION_DELETE => 'Животное удалено ',
            AuditLog::ACTION_UPDATE => 'Животное обновлено ',
            AuditLog::ACTION_INSERT => 'Животное добавлено',
        ],
        'public.pet_rabies_vaccination' => [
            AuditLog::ACTION_TRASH => 'Вакцинация от бешенства с ID %id помечена как удаленная',
            AuditLog::ACTION_DELETE => 'Вакцинация от бешенства с ID %id удалена ',
            AuditLog::ACTION_UPDATE => 'Вакцинация от бешенства с ID %id обновлена ',
            AuditLog::ACTION_INSERT => 'Вакцинация от бешенства с ID %id добавлена ',
        ],
        'public.pet_other_vaccinations' => [
            AuditLog::ACTION_TRASH => 'Вакцинация "иная" с ID %id помечена как удаленная',
            AuditLog::ACTION_DELETE => 'Вакцинация "иная" с ID %id удалена ',
            AuditLog::ACTION_UPDATE => 'Вакцинация "иная" с ID %id обновлена ',
            AuditLog::ACTION_INSERT => 'Вакцинация "иная" с ID %id добавлена ',
        ],
        'public.pet_ectoparasites' => [
            AuditLog::ACTION_TRASH => 'Запись об обработке против эктопаразитов с ID %id помечена как удаленная',
            AuditLog::ACTION_DELETE => 'Запись об обработке против эктопаразитов с ID %id удалена ',
            AuditLog::ACTION_UPDATE => 'Запись об обработке против эктопаразитов с ID %id обновлена ',
            AuditLog::ACTION_INSERT => 'Запись об обработке против эктопаразитов с ID %id добавлена ',
        ],
        'public.pet_dehelmintization' => [
            AuditLog::ACTION_TRASH => 'Запись о дегельминтизации с ID %id помечена как удаленная',
            AuditLog::ACTION_DELETE => 'Запись о дегельминтизации с ID %id удалена ',
            AuditLog::ACTION_UPDATE => 'Запись о дегельминтизации с ID %id обновлена ',
            AuditLog::ACTION_INSERT => 'Запись о дегельминтизации с ID %id добавлена ',
        ],
        'public.pet_identification' => [
            AuditLog::ACTION_TRASH => 'Идентификатор (животного) с ID %id помечен как удаленный',
            AuditLog::ACTION_DELETE => 'Идентификатор (животного) с ID %id удален ',
            AuditLog::ACTION_UPDATE => 'Идентификатор (животного) с ID %id обновлен ',
            AuditLog::ACTION_INSERT => 'Идентификатор (животного) с ID %id добавлен ',
        ],
        'public.contacts' => [
            AuditLog::ACTION_TRASH => 'Контакт с ID %id помечен как удаленный',
            AuditLog::ACTION_DELETE => 'Контакт с ID %id удален ',
            AuditLog::ACTION_UPDATE => 'Контакт с ID %id обновлен ',
            AuditLog::ACTION_INSERT => 'Контакт с ID %id добавлен ',
        ],
        'public.pet_owners' => [
            AuditLog::ACTION_TRASH => 'Владелец с ID %id помечен как удаленный',
            AuditLog::ACTION_DELETE => 'Владелец с ID %id удален ',
            AuditLog::ACTION_UPDATE => 'Владелец с ID %id обновлен ',
            AuditLog::ACTION_INSERT => 'Владелец с ID %id добавлен ',
        ],
        AuditLog::PETS_VACCINATION_FAKE_TABLE => [
            AuditLog::ACTION_MULTIPLE_SAVE => 'Массовое редактирование',
        ]
    ];

    /**
     * Возвращает читаемое описание действия
     * @param $action
     * @return string
     */
    public static function getActionText($action)
    {
        if (array_key_exists($action, self::$action_texts)) {
            return self::$action_texts[$action];
        } else {
            return $action;
        }
    }

    /**
     * Возвращает читаемое описание таблицы
     * @param $table_name
     * @return string
     */
    public static function getTableText($table_name)
    {
        if (array_key_exists($table_name, self::$tables_text)) {
            return self::$tables_text[$table_name];
        } else {
            return $table_name;
        }
    }

    /**
     * @param AuditLog $audit_log_record
     * @return string
     */
    public static function getDescription($audit_log_record)
    {
        if ($audit_log_record->action == AuditLog::ACTION_SYSTEM_FAIL) {
            return 'ОШИБКА: системе аудита не удалось сохранить лог. 
            В отчете могут содержаться данные, которые помогут разработчикам понять почему это произошло';
        }

        if (!empty(self::$description_template[$audit_log_record->table_name][$audit_log_record->action])) {
            $msg = self::$description_template[$audit_log_record->table_name][$audit_log_record->action];
            $msg .= ' пользователем с логином ' . $audit_log_record->login;

            $msg = str_replace('%id', $audit_log_record->entity_id, $msg);
            return $msg;
        } else {
            return 'ОШИБКА: лог аудита был сохранен неверно, или возникла ошибка при выводе';
        }
    }

    /**
     * Выводит объект в текстовом виде
     *
     * @param array $key_names
     * @param array $snapshot
     * @return string
     */
    public static function viewObject($key_names, $snapshot)
    {
        if (!is_array($snapshot)){
            return 'ОШИБКА: лог аудита был сохранен неверно, или возникла ошибка при выводе';
        }

        $text = '';

        foreach ($snapshot as $key => $value){
            if (!array_key_exists($key, $key_names)){
                continue;
            }

            if (is_bool($value)){
                $value = $value ? 'Да' : 'Нет';
            }

            $text .= '<strong>'. $key_names[$key] .':</strong> ' . $value . '<br>';
        }

        return $text;
    }

    /**
     * Выводит массив объектов в текстовом виде
     *
     * @param string $key
     * @param array $key_names
     * @param array $snapshot
     * @return string
     */
    public static function viewArrayObjects($key, $key_names, $snapshot)
    {
        if (!is_array($snapshot) || !array_key_exists($key, $snapshot)) {
            return 'Ошибка вывода';
        }

        $text = [];
        foreach ($snapshot[$key] as $item) {
            $text[] = AuditLogHelper::viewObject($key_names, $item);
        }

        return implode('<br><br>', $text);
    }

}
