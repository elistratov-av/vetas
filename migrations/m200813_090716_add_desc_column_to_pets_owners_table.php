<?php

use yii\db\Migration;

/**
 * Handles adding desc to table `pets_owners`.
 */
class m200813_090716_add_desc_column_to_pets_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'description', $this->string(255));
        $this->addColumn('pets', 'description', $this->string(255));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners', 'description');
        $this->dropColumn('pets', 'description');

    }
}
