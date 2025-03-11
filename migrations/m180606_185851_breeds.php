<?php

use yii\db\Migration;

/**
 * Class m180606_185851_breeds
 */
class m180606_185851_breeds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('breeds', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'description' => $this->string(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('breeds');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180606_185851_breeds cannot be reverted.\n";

        return false;
    }
    */
}
