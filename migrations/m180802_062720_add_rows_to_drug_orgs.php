<?php

use yii\db\Migration;

/**
 * Class m180802_062720_add_rows_to_drug_orgs
 */
class m180802_062720_add_rows_to_drug_orgs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("INSERT INTO 
        drug_orgs(name, created_at, updated_at)
        VALUES 
        ('Организация 1', NOW(), NOW()), 
        ('Организация 2', NOW(), NOW()), 
        ('Организация 3', NOW(), NOW()), 
        ('Организация 4', NOW(), NOW()),
        ('Организация 5', NOW(), NOW()),
        ('Организация 6', NOW(), NOW()),
        ('Организация 7', NOW(), NOW()),
        ('Организация 8', NOW(), NOW()),
        ('Организация 9', NOW(), NOW()),
        ('Организация 10', NOW(), NOW());
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180802_062720_add_rows_to_drug_orgs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180802_062720_add_rows_to_drug_orgs cannot be reverted.\n";

        return false;
    }
    */
}
