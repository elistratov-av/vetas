<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visits_logs`.
 */
class m200822_125544_create_visits_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('audit.visits_logs', [
			'id' => $this->primaryKey()->comment('ID'),
			'id_visit' => $this->integer()->comment('ID приема'),
			'status_visit' => $this->string(255)->comment('Статус приема'),
			'snapshot' => $this->json()->comment('Состояние приема'),
			'initiator' => $this->string(255)->comment('Инициатор смены статуса приема'),
			'id_user' => $this->integer()->comment('ID пользователя, изменивший статус приема'),
			'fio_user' => $this->string(255)->comment('ФИО пользователя, изменивший статус приема'),
			'date' => $this->timestamp(0)->comment('Дата и время изменения данных'),
			'api_version' => $this->integer()->comment('Версия API'),
			'snapshot_generator_version' => $this->integer()->comment('Версия создателя снимков'),
        ]);

		$this->addCommentOnTable(
			'audit.visits_logs',
			'История смены статусов визитов'
		);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('audit.visits_logs');
    }
}
