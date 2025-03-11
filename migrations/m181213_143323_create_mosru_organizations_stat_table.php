<?php

use yii\db\Migration;

/**
 * Handles the creation of table `mosru_organizations_stat`.
 */
class m181213_143323_create_mosru_organizations_stat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('statistic.mosru_organizations_stat', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'total' => $this->integer()->defaultValue(0)->comment("Общее кол-во записей с mos.ru"),
            'canceled_by_owner' => $this->integer()->defaultValue(0)
                ->comment('Кол-во записей отмененных по инициативе владельца'),
            'canceled_by_org' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во записей отмененных по инициативе организации'),
            'moved' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во перенесенных записей'),
            'cats_visits' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во созданных записей на прием с кошками'),
            'dogs_visits' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во созданных записей на прием с собаками'),
            'cats_finished_visits' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во проведенных приемов для кошек'),
            'dogs_finished_visits' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во проведенных приемов для собак'),
        ]);

        $this->createIndex(
            'idx-mosru_organizations_stat-date-organization',
            'statistic.mosru_organizations_stat',
            ['date', 'id_organization'],
            true
        );

        $this->createIndex(
            'idx-mosru_organizations_stat-date',
            'statistic.mosru_organizations_stat',
            'date'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-mosru_organizations_stat-date-organization', 'statistic.mosru_organizations_stat');
        $this->dropIndex('statistic.mosru_organizations_stat', 'statistic.mosru_organizations_stat');
        $this->dropTable('statistic.mosru_organizations_stat');
    }
}
