<?php

use app\commands\migrate\Migration;

/**
 * Class m210421_122934_create_order_tables
 */
class m210421_122934_create_order_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'order',
            [
                'id_order' => $this->primaryKey(),
                'number' => $this->string()->unique()->notNull()->comment('Номер предписания'),
                'id_violation' => $this->integer()->notNull()->comment('Идентификатор нарушения'),
                'id_type' => $this->integer()->notNull()->comment('Идентификатор типа предписания'),
                'date_order' => $this->dateTime()->notNull()->comment('Дата предписания'),
                'date_to' => $this->date()->notNull()->comment('Дата, до которой нарушение должно быть устранено'),
            ]
        );

        $this->createTable(
            'order_type',
            [
                'id_type' => $this->primaryKey(),
                'name' => $this->string()->notNull()->comment('Наименование предписания'),
            ]
        );

        $this->createTable(
            'violation_ARV',
            [
                'id' => $this->primaryKey(),
                'id_ARV' => $this->integer()->notNull()->comment('Идентификатор типа АПН'),
                'id_violation' => $this->integer()->notNull()->comment('Идентификатор нарушения'),
                'id_order' => $this->integer()->notNull()->comment('Предписание на основе которого создано АПН'),
                'number' => $this->string()->unique()->notNull()->comment('Номер АПН'),
                'date_ARV' => $this->date()->notNull()->comment('Дата АПН'),
                'created_at' => $this->dateTime(),
                'updated_at' => $this->dateTime(),
                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
            ]
        );

        $this->addForeignKey(
            'fk_violation-arv-id_order',
            'violation_ARV',
            'id_order',
            'order',
            'id_order',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-order-id_violation',
            'order',
            'id_violation',
            'violation',
            'id_violation',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-order-id_type',
            'order',
            'id_type',
            'order_type',
            'id_type',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-arv-id_arv',
            'violation_ARV',
            'id_ARV',
            'violation_admin_rights',
            'id_ARV',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-arv-id_violation',
            'violation_ARV',
            'id_ARV',
            'violation',
            'id_violation',
            'CASCADE'
        );

        $this->insert('order_type', ['name' => 'Primary']);
        $this->insert('order_type', ['name' => 'Secondary']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('violation_ARV');
        $this->dropTable('order');
        $this->dropTable('order_type');
    }
}
