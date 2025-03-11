<?php

use app\commands\migrate\Migration;

/**
 * Class m210608_081446_create_table_reg_updates
 */
class m210608_081446_create_table_reg_updates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reg_updates', [
            'id' => $this->primaryKey(),
            'id_reg' => $this->integer(),
            'snapshot' => $this->json()->comment('Снимок до обновления'),
            'reason' => $this->string()->comment('Причина обновления'),
            'update_date' => $this->dateTime()->comment('Дата обновления'),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'FK_REG-CERT_TO_REG-UPDATES',
            'reg_updates',
            'id_reg',
            \app\models\db\RegCertificates::tableName(),
            'id'
        );
        $this->createIndex('idx_reg-updates_reg-id', 'reg_updates', 'id_reg', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_REG-CERT_TO_REG-UPDATES', 'reg_updates');
        $this->dropIndex('idx_reg-updates_reg-id','reg_updates');
        $this->dropTable('reg_updates');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210608_081446_create_table_reg_updates cannot be reverted.\n";

        return false;
    }
    */
}
