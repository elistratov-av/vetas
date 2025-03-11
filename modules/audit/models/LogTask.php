<?php

namespace app\modules\audit\models;

use app\modules\audit\models\snapshot_creators\GenericSnapshot;

class LogTask
{
    /**
     * Имя поля, которое содержит id обновленной/удаленной/созданной записи в бд
     * Используется только для ссылки на эту запись
     * Слепки снимаются по parent_entity_id_field
     *
     * @var string
     */
    public $entity_id_field;

    /**
     * Имя поля, которое содержит id родительской записи для
     * обновленной/удаленной/созданной записи в бд
     * Например, поле ссылающееся на владельца
     * Если запись сама является родительской - то сдесь должен быть ее ID
     *
     * @var string
     */
    public $parent_entity_id_field;

    /**
     * Класс, который должен сгенерировать слепок
     * @var GenericSnapshot
     */
    public $snapshot_generator;

    /**
     * Действие, которое произошло над объектом
     * @var string
     */
    public $action;

    /**
     * Имя таблицы в которой произошли изменения
     * Используйте формат schema.table_name во избежание проблем в будущем
     *
     * @var string
     */
    public $table;

    /**
     * Имя родительской таблицы
     * Используйте формат schema.table_name во избежание проблем в будущем
     *
     * @var string
     */
    public $parent_table;

    /**
     * Флаг: это доченяя запись
     * @var bool
     */
    public $is_child_record = false;

    /**
     * Action контроллера
     * @var string
     */
    public $action_id;

    /**
     * LogTask constructor.
     *
     * @param string $entity_id_field Имя поля, которое содержит id обновленной/удаленной/созданной записи в бд
     * @param string $parent_entity_id_field Имя поля, которое содержит id родительской записи
     *                                       для обновленной/удаленной/созданной записи в бд
     *                                       Например, поле ссылающееся на владельца
     * @param GenericSnapshot $snapshot_generator Класс, который должен сгенерировать слепок
     * @param string $action Действие, которое произошло над объектом
     * @param string $table Имя таблицы в которой произошли изменения. Используйте формат schema.table_name во избежание проблем в будущем
     * @param string $parent_table Имя родительской таблицы.  Используйте формат schema.table_name во избежание проблем в будущем
     * @param boolean $is_child_record
     * @param string $action_id
     */
    public function __construct(
        $entity_id_field,
        $parent_entity_id_field,
        $snapshot_generator,
        $action,
        $table,
        $parent_table,
        $is_child_record = false,
        $action_id = null
    )
    {
        $this->entity_id_field = $entity_id_field;
        $this->parent_entity_id_field = $parent_entity_id_field;
        $this->snapshot_generator = $snapshot_generator;
        $this->action = $action;
        $this->table = $table;
        $this->parent_table = $parent_table;
        $this->is_child_record = $is_child_record;
        $this->action_id = $action_id;
    }
}
