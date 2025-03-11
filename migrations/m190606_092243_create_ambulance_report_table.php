<?php

use yii\db\Migration;

/**
 * Handles the creation of table `ambulance_report`.
 */
class m190606_092243_create_ambulance_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('statistic.ambulance_report', [
            'id' => $this->primaryKey(),
            'id_specialist' => $this->integer()->defaultValue(0)
                ->comment("id специалиста"),
            'spec_name' => $this->string()->notNull()->comment("Специалист, выехавший на вызов"),
            'date' => $this->date()->notNull(),
            'total_calls' => $this->integer()->defaultValue(0)->comment("Общее кол-во принятых звонков"),
            'total_cancelled' => $this->integer()->defaultValue(0)->comment("Всего отмененных записей"),
            'cancelled_by_owner' => $this->integer()->defaultValue(0)
                ->comment('Кол-во записей отмененных по инициативе владельца'),
            'cancelled_by_org' =>  $this->integer()->defaultValue(0)
                ->comment('Кол-во записей отмененных по инициативе организации'),
            'total_house_calls' => $this->integer()->defaultValue(0)
                ->comment("Кол-во выездов"),
            'total_commercial' => $this->integer()->defaultValue(0)
                ->comment("Кол-во платных выездов"),
            'total_free_for_blind' => $this->integer()->defaultValue(0)
                ->comment("Количество льготных выездов к незрячим"),
            'total_free_for_the_rest' => $this->integer()->defaultValue(0)
                ->comment("Количество льготных выездов ко всем остальным")
        ]);

        $this->createIndex(
            'idx-ambulance-report-date-specialist',
            'statistic.ambulance_report',
            ['date', 'id_specialist'],
            true
        );

        $this->createIndex(
            'idx-ambulance-report-date',
            'statistic.ambulance_report',
            'date'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-ambulance-report-date-specialist', 'statistic.ambulance_report');
        $this->dropIndex('idx-ambulance-report-date', 'statistic.ambulance_report');
        $this->dropTable('statistic.ambulance_report');
    }
}
