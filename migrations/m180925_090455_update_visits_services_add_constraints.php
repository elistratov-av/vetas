<?php

use app\commands\migrate\Migration;

/**
 * Class m180925_090455_update_visits_services_add_constraints
 */
class m180925_090455_update_visits_services_add_constraints extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // https://jira.altarix.ru/browse/VETAIS-846
        
        $this->execute('UPDATE visits SET cooldown = abs(cooldown)');
        $this->execute('ALTER TABLE visits ADD CONSTRAINT check_cooldown CHECK (cooldown >= 0 OR cooldown IS NULL)');
        
        $this->execute('UPDATE services SET price = abs(price)');
        $this->execute('UPDATE gov_services SET duration = abs(duration)');
        $this->execute('UPDATE gov_services SET cooldown = abs(cooldown)');
        $this->execute('ALTER TABLE services ADD CONSTRAINT check_price CHECK (price >= 0)');
        $this->execute('ALTER TABLE gov_services ADD CONSTRAINT check_duration CHECK (duration >= 0)');
        $this->execute('ALTER TABLE gov_services ADD CONSTRAINT check_cooldown CHECK (cooldown >= 0 OR cooldown IS NULL)');
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180925_090455_update_visits_services_add_constraints cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180925_090455_update_visits_services_add_constraints cannot be reverted.\n";

        return false;
    }
    */
}
