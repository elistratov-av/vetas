<?php

use app\commands\migrate\Migration;

/**
 * Class m191030_084532_edit_animalid_map_types
 */
class m191030_084532_edit_animalid_map_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('animalid.id_mapping', ['type' => 'pets'], 'type = \'pet\'');
        $this->update('animalid.id_mapping', ['type' => 'organizations'], 'type = \'company\' and our < 0'); //компании без минуса перед айди - государственные
        $this->update('animalid.id_mapping', ['type' => 'companies'], 'type = \'company\' and our < 0');
        $this->update('animalid.id_mapping', ['type' => 'kinds'], 'type = \'kind\'');
        $this->update('animalid.id_mapping', ['type' => 'breeds'], 'type = \'breed\'');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m191030_084532_edit_animalid_map_types cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191030_084532_edit_animalid_map_types cannot be reverted.\n";

        return false;
    }
    */
}
