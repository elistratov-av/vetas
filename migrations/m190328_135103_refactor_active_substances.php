<?php

use app\commands\migrate\Migration;

/**
 * Class m190328_135103_refactor_active_substances
 */
class m190328_135103_refactor_active_substances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex(
            'idx_name_en',
            'active_substances'
        );
        $this->execute('ALTER TABLE active_substances ALTER COLUMN name_en DROP NOT NULL');
        $this->addColumn(
            'active_substances',
            'description',
            $this->string()
        );
        $this->addCommentOnColumn(
            'active_substances',
            'description',
            'Комментарий'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex(
            'idx_name_en',
            'active_substances',
            ['name_en'],
            true
        );
        $this->execute('ALTER TABLE active_substances ALTER COLUMN name_en SET NOT NULL');
        $this->dropColumn(
            'active_substances',
            'description'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190328_135103_refactor_active_substances cannot be reverted.\n";

        return false;
    }
    */
}
