<?php

use yii\db\Migration;

class m201006_115237_create_timesheets_logs_table extends Migration
{

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $this->createTable(
            'audit.timesheets_logs',
            [
                'id' => $this->primaryKey()->comment('ID'),
                'id_timesheet' => $this->integer()->comment('ID расписания'),
                'id_initiator' => $this->integer()->comment('ID инициатора'),
                'fio_initiator' => $this->string(255)->comment('ФИО инициатора'),
                'id_specialist' => $this->integer()->comment('ID специалиста'),
                'snapshot' => $this->json()->comment('Состояние расписания'),
                'api_version' => $this->integer()->comment('Версия API'),
                'snapshot_generator_version' => $this->integer()->comment('Версия создателя снимков'),
                'date' => $this->timestamp(0)->comment('Дата и время изменения данных'),
            ]
        );

        $this->addCommentOnTable(
            'audit.timesheets_logs',
            'История удаления расписания'
        );
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $this->dropTable('audit.timesheets_logs');
    }
}