<?php

use app\commands\migrate\Migration;
use app\models\db\Dictionaries;

/**
 * Class m240523_074121_add_vsd_params
 */
class m240523_074121_add_vsd_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $record = new Dictionaries();
        $record->name = 'Проставлена отметка в паспорт животного';
        $record->type = 'vsdtypes';
        if (!$record->save()) {
            throw new Exception('Error saving dictionary value');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $record = Dictionaries::findOne(['name' => 'Проставлена отметка в паспорт животного', 'type' => 'vsdtypes']);
        if ($record) {
            if (!$record->delete()) {
                throw new Exception('Error deleting dictionary value');
            }
            return true;
        } else {
            echo "Record not found.\n";
            return false;
        }
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240523_074121_add_vsd_params cannot be reverted.\n";

        return false;
    }
    */
}
