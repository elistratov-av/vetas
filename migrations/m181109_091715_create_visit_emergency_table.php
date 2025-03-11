<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visits_emergency`.
 */
class m181109_091715_create_visit_emergency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visits_emergency', [
            'id' => $this->primaryKey(),
            'id_visit' => $this->integer()->comment('Ссылка на прием'),
            'id_emergency' => $this->integer()->comment('Ссылка на экстренную ситуацию'),
            'notify_status' => $this->boolean()->defaultValue(false)
        ]);
        $this->addCommentOnTable('visits_emergency', 'Список приемов помеченных для переноса в связи в экстренной ситуацией');

        $this->addForeignKey(
            'fk-visits_emergency-id_visit',
            'visits_emergency',
            'id_visit',
            'visits',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-visits_emergency-id_emergency',
            'visits_emergency',
            'id_emergency',
            'organizations_emergency',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('visits_emergency');
    }
}
