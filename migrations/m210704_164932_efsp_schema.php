<?php

use app\commands\migrate\Migration;

/**
 * Class m210704_164932_efsp_schema
 */
class m210704_164932_efsp_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA efsp;');
        $this->execute("COMMENT ON SCHEMA tmc IS 'Данные из ЕФСП (адреса)'");

        $this->createTable('efsp.fias_data', [
           'id' => $this->bigPrimaryKey(),
           'fias_uid uuid',
            'api_url' => $this->char(1024)->comment('API URL с которого получили данные'),
            'date' => $this->timestamp(0)->defaultValue('NOW()')->comment('Дата получения результата'),
            'result' => $this->json()->comment('Результат получения адреса'),
        ]);

        $this->addCommentOnTable(
            'efsp.fias_data',
            'Данные об адресах из ЕФСП (пополняются консольной командой вручную)'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('efsp.fias_data');
        $this->execute('DROP SCHEMA efsp;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210704_164932_efsp_schema cannot be reverted.\n";

        return false;
    }
    */
}
