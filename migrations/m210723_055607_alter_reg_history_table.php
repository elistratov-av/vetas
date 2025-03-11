<?php

use app\commands\migrate\Migration;

/**
 * Class m210723_055607_alter_reg_history_table
 */
class m210723_055607_alter_reg_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('reg_updates', 'reg_certificates_history');
        $this->addColumn('reg_certificates_history', 'action', $this->string()->comment('Действие над данным удостоверением'));
        $this->addColumn('reg_certificates_history', 'id_pet', $this->integer()->comment('ИД животного'));
        $this->dropColumn('reg_certificates_history', 'update_date'); // Та же семантика, что и у created_at
        $this->dropForeignKey('FK_REG-CERT_TO_REG-UPDATES', 'reg_certificates_history'); // При удалении запись удаляется из БД

        $sql = <<<SQL
UPDATE reg_certificates_history rch
SET id_pet = (SELECT id_pet FROM reg_certificates rc WHERE rc.id = rch.id_reg )
SQL;


        $this->execute($sql); // Проставим id_pet для старых записей
        $this->update('reg_certificates_history', ['action' => 'update']); // Изначально сохранялись только обновления
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210723_055607_alter_reg_history_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210723_055607_alter_reg_history_table cannot be reverted.\n";

        return false;
    }
    */
}
