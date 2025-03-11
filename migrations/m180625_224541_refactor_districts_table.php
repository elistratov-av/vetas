<?php

use yii\db\Migration;

/**
 * Class m180625_224541_refactor_districts_table
 */
class m180625_224541_refactor_districts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('districts', 'parent_id');
        $this->addColumn('districts', 'id_area', $this->integer());
        $this->execute('UPDATE "districts" SET "id_area"=1');
        $this->execute('ALTER TABLE "districts" ALTER COLUMN "id_area" SET NOT NULL');
        $this->execute('ALTER TABLE "districts" ALTER COLUMN "bti_code" TYPE varchar(10)');
        $this->createIndex('idx_area', 'districts', 'id_area');
        $this->addForeignKey('fk-districts-areas', 'districts', "id_area",
          'areas',
          'id','CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180625_224541_refactor_districts_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180625_224541_refactor_districts_table cannot be reverted.\n";

        return false;
    }
    */
}
