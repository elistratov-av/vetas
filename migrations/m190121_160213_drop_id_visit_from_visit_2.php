<?php

use app\commands\migrate\Migration;

/**
 * Class m190121_160213_drop_id_visit_from_visit_2
 */
class m190121_160213_drop_id_visit_from_visit_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE "public"."visits" DROP CONSTRAINT IF EXISTS "fk-visits-id_visit"');
        $this->execute('ALTER TABLE "public"."visits" DROP COLUMN IF EXISTS "id_visit"');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190121_160213_drop_id_visit_from_visit_2 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190121_160213_drop_id_visit_from_visit_2 cannot be reverted.\n";

        return false;
    }
    */
}
