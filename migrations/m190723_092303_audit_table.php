<?php

use app\commands\migrate\Migration;

/**
 * Class m190723_092303_audit_table
 */
class m190723_092303_audit_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA IF NOT EXISTS audit');
        $this->execute("COMMENT ON SCHEMA audit IS 'Схема аудита'");

        $this->createTable('audit.log', [
            'id' => $this->primaryKey()
                ->comment('ID'),
            'login' => $this->string(255)
                ->comment('Логин, который отправил запрос на создание / редактирование / удаление'),
            'id_user' => $this->integer()
                ->comment('id_user, который отправил запрос на создание / редактирование / удаление'),
            'action' => $this->string(1)
                ->comment('действие: I - добавление, U - обновление, D - удаление, S - точка начального отсчета, T - пометка "удалено", R - восстановлено, F - fail (не удалось сохранить событие)'),
            'date' => $this->timestamp(0)
                ->comment('Дата и время изменения данных'),
            'snapshot' => $this->json()
                ->comment('Состояние, после обновления/создания'),
            'api_version' => $this->integer()
                ->comment('Версия API'),
            'snapshot_generator_version' => $this->integer()
                ->comment('Версия создателя снимков'),
            'table_name' => $this
                ->string(128)
                ->notNull()
                ->comment('Имя таблицы, в которой произошли изменения'),
            'parent_table_name' => $this
                ->string(128)
                ->notNull()
                ->comment('Имя базовой таблицы'),
            'entity_id' => $this
                ->integer()
                ->notNull()
                ->comment('Id сущности'),
            'parent_entity_id' => $this
                ->integer()
                ->notNull()
                ->comment('Id базовой сущности'),
            'id_visit' => $this
                ->integer()
                ->comment('Ссылка на визит (для некоторых записей)'),
        ]);

        $this->addCommentOnTable(
            'audit.log',
            'Лог для аудита'
        );

        /**
         * Создается  без указания схемы, а удаляется только если указана
         * Видимо баг Yii и может изменить свое поведение в будущем
         */
        $this->createIndex(
            'idx_audit_audit_log_parent_table_name_parent_entity_id',
            'audit.log',
            [
                'parent_table_name',
                'parent_entity_id',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /**
         * Создается  без указания схемы, а удаляется только если указана
         * Видимо баг Yii и может изменить свое поведение в будущем
         */
        $this->dropIndex(
            'audit.idx_audit_audit_log_parent_table_name_parent_entity_id',
            'audit.log'
        );
        $this->dropTable('audit.log');
        $this->execute('DROP SCHEMA IF EXISTS audit');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190723_092303_audit_table cannot be reverted.\n";

        return false;
    }
    */
}
