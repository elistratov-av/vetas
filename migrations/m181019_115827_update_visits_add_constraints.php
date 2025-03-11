<?php

use app\commands\migrate\Migration;

/**
 * Class m181019_115827_update_visits_add_constraints
 */
class m181019_115827_update_visits_add_constraints extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.visits ADD CONSTRAINT duration_check CHECK (duration >= 0 OR duration IS NULL)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE public.visits DROP CONSTRAINT duration_check');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181019_115827_update_visits__add_constraints cannot be reverted.\n";

        return false;
    }
    */
}
