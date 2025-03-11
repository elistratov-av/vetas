<?php

use app\commands\migrate\Migration;

/**
 * Class m180904_124409_update_organizations_table
 */
class m180904_124409_update_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('organizations', 'name', $this->text());
        $this->alterColumn('organizations', 'inn', $this->string(12));
        $this->alterColumn('organizations', 'kpp', $this->string(9));
        $this->alterColumn('organizations', 'ogrn', $this->string(13));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180904_124409_update_organizations_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180904_124409_update_organizations_table cannot be reverted.\n";

        return false;
    }
    */
}
