<?php

namespace app\models\db\audit;

use app\models\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
 * This is the model class for table "audit.log".
 *
 * @property int    $id                         ID
 * @property string $login                      Логин, который отправил запрос на создание / редактирование / удаление
 * @property int    $id_user                    id_user, который отправил запрос на создание / редактирование / удаление
 * @property string $action                     действие: I - добавление, U - обновление, D - удаление, S - точка начального отсчета, T - пометка "удалено", R - восстановлено, F - fail (не удалось сохранить событие)
 * @property string $date                       Дата и время изменения данных
 * @property array  $snapshot                   Состояние, после обновления/создания
 * @property int    $api_version                Версия API
 * @property int    $snapshot_generator_version Версия создателя снимков
 * @property string $table_name                 Имя таблицы, в которой произошли изменения
 * @property string $parent_table_name          Имя базовой таблицы
 * @property int    $entity_id                  Id сущности
 * @property int    $parent_entity_id           Id базовой сущности
 * @property int    $id_visit                   Ссылка на визит (для некоторых записей)
 * @property string $action_id                  Action контроллера
 */
class AuditLog extends ActiveRecord
{
    const
        ACTION_INSERT = 'I',
        ACTION_UPDATE = 'U',
        ACTION_DELETE = 'D',
        ACTION_TRASH = 'T',
        ACTION_SYSTEM_FAIL = 'F',
        ACTION_MULTIPLE_SAVE = 'M';
    //ACTION_START_POINT = 'S',
    //ACTION_RECOVER_FROM_TRASH = 'R',

    /**
     * Используется для сохранения лога ошибки аудита
     */
    const ERROR_LOG_FAKE_TABLE = 'error_log_fake_table';

    /**
     * Используется для сохранения лога ошибки аудита
     */
    const PETS_VACCINATION_FAKE_TABLE = 'pets_vaccination_fake_table';

    /**
     * Пользователи для модулей без авторизации
     */
    const
        SYS_USER_ELK = '[sys_user_elk]',
        SYS_USER_MOS_RU = '[sys_user_mos_ru]',
        SYS_USER_SUBSCRIPTION = '[sys_user_subscription]',
        SYS_USER_ANIMAL_ID = '[sys_user_animal_id]',
        SYS_USER_MDM = '[sys_user_mdm]';


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()::timestamp without time zone'),
            ],
        ];
    }

    /**
     * Отключаем триннеры (что бы не зацикливать лог)
     */

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
    }
    /** END : Отключаем триггеры (что бы не зацикливать лог) : END */


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'audit.log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_user', 'api_version', 'snapshot_generator_version', 'entity_id', 'parent_entity_id', 'id_visit'], 'default', 'value' => null],
            [['id_user', 'api_version', 'snapshot_generator_version', 'entity_id', 'parent_entity_id', 'id_visit'], 'integer'],
            [['date', 'snapshot'], 'safe'],
            [['table_name', 'parent_table_name', 'entity_id', 'parent_entity_id'], 'required'],
            [['login'], 'string', 'max' => 255],
            [['action'], 'string', 'max' => 1],
            [['table_name', 'parent_table_name'], 'string', 'max' => 128],
            ['action', 'in', 'range' => [
                self::ACTION_DELETE, self::ACTION_INSERT,
                self::ACTION_TRASH, self::ACTION_UPDATE,
                self::ACTION_SYSTEM_FAIL, self::ACTION_MULTIPLE_SAVE,
                //self::ACTION_START_POINT , self::ACTION_RECOVER_FROM_TRASH,
            ]],
            ['action_id', 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Логин, который отправил запрос на создание / редактирование / удаление',
            'id_user' => 'id_user, который отправил запрос на создание / редактирование / удаление',
            'action' => 'действие: I - добавление, U - обновление, D - удаление, S - точка начального отсчета, T - пометка \"удалено\", R - восстановлено, F - fail (не удалось сохранить событие)',
            'date' => 'Дата и время изменения данных',
            'snapshot' => 'Состояние, после обновления/создания',
            'api_version' => 'Версия API',
            'snapshot_generator_version' => 'Версия создателя снимков',
            'table_name' => 'Имя таблицы, в которой произошли изменения',
            'parent_table_name' => 'Имя базовой таблицы',
            'entity_id' => 'Id сущности',
            'parent_entity_id' => 'Id базовой сущности',
            'id_visit' => 'Ссылка на визит (для некоторых записей)',
            'action_id' => 'Action контроллера',
        ];
    }
}
