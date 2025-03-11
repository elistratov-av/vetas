<?php

namespace app\modules\audit\models;

use app\models\db\audit\AuditLog;
use app\models\db\ContactTypes;
use app\models\db\Pets;
use app\modules\audit\models\snapshot_creators\SnapshotOrganization;
use app\modules\audit\models\snapshot_creators\SnapshotPet;
use app\modules\audit\models\snapshot_creators\SnapshotPetOwner;
use Yii;
use yii\base\Event;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;

class AuditEventHandler
{

    /**
     * Событие
     * @var Event
     */
    protected $event;

    /**
     * Отправитель
     * @var \app\modules\v1\models\EntityResource|\yii\db\ActiveRecord|\app\models\db\ActiveRecord
     */
    protected $sender;

    /**
     * Действие
     * @var string
     */
    protected $action;

    /**
     * Список таблиц, у которых записи не удаляются,
     * а лишь помечаются как удаленные (is_deleted = true)
     * Используйте формат schema.table_name
     *
     * @var string[]
     */
    protected $_tables_with_is_deleted_flag = [
        'public.pet_owners',
    ];


    /**
     * Обработка события
     * @param Event $event
     * @throws \yii\base\InvalidConfigException
     */
    public function handle($event)
    {
        $this->event = $event;
        $this->action = $event->name;
        $this->sender = $this->event->sender;

        /*
         * Основная обработка вынесена в отдельный метод, что бы имелась возможность
         * отловить исключения и ошибки
         */
        try {
            $this->processEvent();
        } catch (\Exception $exception) {
            return $this->logFail($exception);
        }
    }

    /**
     * Приемник события сохранения вакцинаций
     * @param $event
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function handlePetsVaccination($event)
    {
        $this->event = $event;
        $this->action = $event->name;
        $this->sender = $this->event->sender;

        /*
         * Основная обработка вынесена в отдельный метод, что бы имелась возможность
         * отловить исключения и ошибки
         */
        try {
            $this->processPetsVaccinationEvent();
        } catch (\Exception $exception) {
            return $this->logFail($exception);
        }
    }

    /**
     * Обработка события сохранения вакцинаций
     * @throws \Exception
     */
    protected function processPetsVaccinationEvent()
    {
        $log_task = new LogTask(
            false,
            'id',
            new SnapshotPet(),
            $this->action,
            AuditLog::PETS_VACCINATION_FAKE_TABLE,
            'public.pets'
        );

        $this->saveAuditLog($log_task);
    }

    /**
     * Обработка события
     */
    protected function processEvent()
    {
        /*
         * Проверяем, необходимы ли какие-то действия,
         * и если да то какие
         */
        $log_task = $this->thereIsANeedToLog();

        if (empty($log_task)) {
            return; // В логировании не нуждается
        }

        $this->saveAuditLog($log_task);
    }

    /**
     * @param LogTask $log_task
     * @return AuditLog
     * @throws \Exception
     */
    protected function saveAuditLog($log_task)
    {
        $parent_entity_id = $this->getEntityId($log_task->parent_entity_id_field);
        $user_id_and_login = $this->getUserLoginAndId();

        $audit_log = new AuditLog([
            'login' => $user_id_and_login['login'],
            'id_user' => $user_id_and_login['id'],
            'action' => $this->detectAction($log_task),
            //'date' => 'Дата и время изменения данных',
            'snapshot' => $log_task->snapshot_generator->create($parent_entity_id),
            'api_version' => $this->getApiVersion(),
            'snapshot_generator_version' => $log_task->snapshot_generator->getVersion(),
            'table_name' => $log_task->table,
            'parent_table_name' => $log_task->parent_table,
            'parent_entity_id' => $parent_entity_id,
            'entity_id' => ($log_task->entity_id_field !== false) ? $this->getEntityId($log_task->entity_id_field) : 0,
            'action_id' => $log_task->action_id
        ]);

        if ($audit_log->save() !== true) {
            throw new \Exception('Не удалось сохранить лог #AL01');
        }

        return $audit_log;
    }

    /**
     * @param LogTask $log_task
     * @return string
     * @throws InvalidValueException
     */
    protected function detectAction($log_task)
    {
        switch ($this->action) {
            case Pets::EVENT_AFTER_SAVE_VACCINATION:
                return AuditLog::ACTION_MULTIPLE_SAVE;
            case ActiveRecord::EVENT_BEFORE_DELETE:
            case ActiveRecord::EVENT_AFTER_DELETE:
                return AuditLog::ACTION_DELETE;
            case ActiveRecord::EVENT_AFTER_INSERT:
                return AuditLog::ACTION_INSERT;
            case ActiveRecord::EVENT_AFTER_UPDATE:

                /*
                 * Для таблиц без столбца is_deleted событие всегда обновление
                 */
                if (
                    !in_array($log_task->table, $this->_tables_with_is_deleted_flag) ||
                    !$this->sender->hasAttribute('is_deleted')
                ) {
                    return AuditLog::ACTION_UPDATE;
                }

                /*
                * Тк после сохранения, мы не можем определить,
                * а было ли измененно поле, то считаем,
                * что если is_deleted=true то это удаление в корзину
                */
                $current_status = $this->sender->getAttribute('is_deleted');
                if ($current_status === true) {
                    return AuditLog::ACTION_TRASH;
                }

                return AuditLog::ACTION_UPDATE;

            default:
                throw new InvalidValueException('Неизвестный тип события :' . $this->action);
        }
    }

    /**
     * Возвращает заполненный LogTask если необходимо выполнить логирование
     * или false, если нет
     *
     * @return LogTask|bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function thereIsANeedToLog()
    {
        try {
            $table_name = $this->sender->tableName();
        } catch (\Exception $exception) {
            $this->logFail($exception);
            return false;
        }

        $log_task = false;

        /**
         * @see AuditViewHelper
         */
        switch ($table_name) {
            /*
             * ----------- Организация -----------
             */

            case 'public.organizations':
            case 'organizations':
                $log_task = new LogTask(
                    'id',
                    'id',
                    new SnapshotOrganization(),
                    $this->action,
                    'public.organizations',
                    'public.organizations'
                );
                break;

            /*
             * Владельцы
             */
            case 'public.pet_owners':
            case 'pet_owners':
                $log_task = new LogTask(
                    'id',
                    'id',
                    new SnapshotPetOwner(),
                    $this->action,
                    'public.pet_owners',
                    'public.pet_owners'
                );
                break;

            /*
             * Контакты (организации и владельцев)
             */
            case 'public.contacts':
            case 'contacts':
                // ТУТ разбираться кто у нас будет снапшотером и будет ли вообще
                if (!$this->sender->hasAttribute('entity_type')) {
                    return false;
                }
                if ($this->sender->getAttribute('entity_type') == ContactTypes::ENTITY_TYPE_ORGANIZATION) {
                    $log_task = new LogTask(
                        'id',
                        'entity_id',
                        new SnapshotOrganization(),
                        $this->action,
                        'public.contacts',
                        'public.organizations',
                        true
                    );
                } elseif ($this->sender->getAttribute('entity_type') == ContactTypes::ENTITY_TYPE_PET_OWNER) {
                    $log_task = new LogTask(
                        'id',
                        'entity_id',
                        new SnapshotPetOwner(),
                        $this->action,
                        'public.contacts',
                        'public.pet_owners',
                        true
                    );
                }
                break;

            /*
             * ----------- Животное -----------
             */
            case 'public.pets':
            case 'pets':
                $log_task = new LogTask(
                    'id',
                    'id',
                    new SnapshotPet(),
                    $this->action,
                    'public.pets',
                    'public.pets'
                );
                break;

            /*
             * Идентификация
             */
            case 'public.pet_identification':
            case 'pet_identification':
                $log_task = new LogTask(
                    'id',
                    'id_pet',
                    new SnapshotPet(),
                    $this->action,
                    'public.pet_identification',
                    'public.pets',
                    true
                );
                break;


            /*
             * Связь владелец-животное
             */
            case 'public.pets_to_owner':
            case 'pets_to_owner':
                $log_task = new LogTask(
                    'id',
                    'id_pet',
                    new SnapshotPet(),
                    $this->action,
                    'public.pets_to_owner',
                    'public.pets',
                    true
                );
                break;

            default:
                return false;
        }

        /*
        * Для ДОЧЕРНИХ записей слепок НЕ НАДО делать ПЕРЕД удаления
        */
        if ($log_task->is_child_record == true && $this->event->name == ActiveRecord::EVENT_BEFORE_DELETE) {
            return false;
        }

        /*
         * Для РОДИТЕЛЬСКИХ записей слепок НЕ НАДО делать ПОСЛЕ удаления
         */
        if ($log_task->is_child_record == false && $this->event->name == ActiveRecord::EVENT_AFTER_DELETE) {
            return false;
        }

        try {
            $action_id = Yii::$app->controller->action->getUniqueId();
            $log_task->action_id = $action_id;
        } catch (\Throwable $e) {

        }

        return $log_task;
    }

    /**
     * Возвращает Id сущности
     * По умолчанию - поле id
     *
     * @param $field
     * @return integer
     */
    protected function getEntityId($field = 'id')
    {
        return $this->sender->getAttribute($field);
    }

    /**
     * Возвращает версию API
     * @return int
     */
    protected function getApiVersion()
    {
        if (get_class($this->sender) == 'app\modules\v1\models\EntityResource') {
            return 1;
        } else {
            return 2;
        }
    }

    /**
     * Возвращает id пользователя и login в массиве
     * @return array
     * @throws \Exception
     */
    protected function getUserLoginAndId()
    {
        // Обычный пользователь
        if (Yii::$app->user->getIsGuest() == false) {
            return [
                'id' => Yii::$app->user->identity->getId(),
                'login' => Yii::$app->user->identity->login
            ];
        } else {
            // Модули без авторизации
            $module = Yii::$app->controller->module->id;
            switch ($module) {
                case 'elk':
                    return ['id' => null, 'login' => AuditLog::SYS_USER_ELK];

                case 'subscription':
                    return ['id' => null, 'login' => AuditLog::SYS_USER_SUBSCRIPTION];

                case 'soap':
                    return ['id' => null, 'login' => AuditLog::SYS_USER_MOS_RU];

                case 'animalid':
                    return ['id' => null, 'login' => AuditLog::SYS_USER_ANIMAL_ID];

                case 'mdm':
                    return ['id' => null, 'login' => AuditLog::SYS_USER_MDM];

                default:
                    throw new \Exception('Cant detect user id and login');
            }
        }
    }

    /**
     * Пытается вернуть логин и id текущего пользователя (нужно только для logFail)
     * @return mixed
     */
    protected function tryUserLoginAndId()
    {
        try {
            return $this->getUserLoginAndId();
        } catch (\Exception $e) {
            return ['id' => null, 'login' => null];
        }
    }


    protected function log($message)
    {
        \Yii::debug($message, 'audit');
    }

    /**
     * Пытаемся сохранить в таблице хотя-бы какую-то информацию о проблеме
     * Скорее всего это будет отключено после обкатки в бою
     *
     * @param \Exception $exception
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function logFail($exception)
    {
        $user_id_and_login = $this->tryUserLoginAndId();

        $msg = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => \Yii::$app->request->url,
            'body_params' => \Yii::$app->request->getBodyParams(),
        ];

        $audit_log = new AuditLog([
            'login' => $user_id_and_login['login'],
            'id_user' => $user_id_and_login['id'],
            'action' => AuditLog::ACTION_SYSTEM_FAIL,
            //'date' => 'Дата и время изменения данных',
            'snapshot' => json_encode($msg),
            'snapshot_generator_version' => 1,
            'table_name' => AuditLog::ERROR_LOG_FAKE_TABLE,
            'parent_table_name' => AuditLog::ERROR_LOG_FAKE_TABLE,
            'parent_entity_id' => 1,
            'entity_id' => 1,
        ]);

        return $audit_log->save();
    }
}
