<?php

use yii\db\Migration;

/**
 * Class m180810_102044_insert_rows_into_identification_types
 */
class m180810_102044_insert_rows_into_identification_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('identification_types', ['name', 'description'],
            [
                ['чип', ''],
                ['клеймо', ''],
                ['бирка', ''],
                ['татуировка', ''],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180810_102044_insert_rows_into_identification_types cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180810_102044_insert_rows_into_identification_types cannot be reverted.\n";

        return false;
    }
    */
}
