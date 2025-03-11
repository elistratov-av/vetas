<?php

use yii\db\Migration;

/**
 * Class m180629_185221_orgs_rels
 */
class m180629_185221_orgs_rels extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'id_area', 'integer');
        $this->addColumn('organizations', 'id_district', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180629_185221_orgs_rels cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180629_185221_orgs_rels cannot be reverted.\n";

        return false;
    }
    */
}
