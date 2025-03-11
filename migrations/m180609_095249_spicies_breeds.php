<?php

use yii\db\Migration;

/**
 * Class m180609_095249_spicies_breeds
 */
class m180609_095249_spicies_breeds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('species', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'description' => $this->string(),
        ]);

        $this->createTable('species_breeds', [
            'id' => $this->primaryKey(),
            'id_species' => $this->integer(),
            'id_breed' => $this->integer(),
        ]);

        $this->addForeignKey('fk-species',  'species_breeds', 'id_species', 'species', 'id', 'NO ACTION' );
        $this->addForeignKey('fk-breeds',  'species_breeds', 'id_breed', 'breeds', 'id', 'NO ACTION' );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180609_095249_spicies_breeds cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180609_095249_spicies_breeds cannot be reverted.\n";

        return false;
    }
    */
}
