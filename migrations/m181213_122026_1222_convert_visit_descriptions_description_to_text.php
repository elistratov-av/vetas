<?php

use app\commands\migrate\Migration;

/**
 * Class m181213_122026_1222_convert_visit_descriptions_description_to_text
 */
class m181213_122026_1222_convert_visit_descriptions_description_to_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE public.visit_descriptions ALTER COLUMN description TYPE text;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181213_122026_1222_convert_visit_descriptions_description_to_text cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181213_122026_1222_convert_visit_descriptions_description_to_text cannot be reverted.\n";

        return false;
    }
    */
}
