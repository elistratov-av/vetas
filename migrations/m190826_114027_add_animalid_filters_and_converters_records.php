<?php

use app\commands\migrate\Migration;

/**
 * Class m190826_064026_add_animalid_filters_and_converters_records
 */
class m190826_114027_add_animalid_filters_and_converters_records extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('animalid.filter', [
           'type' => 'pet',
           'field' => 'kind',
           'value' => 'Иные животные',
           'dir' => 'out',
        ]);

        $this->insert('animalid.filter', [
           'type' => 'pet',
           'field' => 'kind',
           'value' => 'Насекомые энтомофаги',
           'dir' => 'out',
        ]);

        $this->insert('animalid.filter', [
           'type' => 'pet',
           'field' => 'kind',
           'value' => 'Паукообразные',
           'dir' => 'out',
        ]);

        $this->insert('animalid.filter', [
           'type' => 'pet',
           'field' => 'kind',
           'value' => 'Моллюски',
           'dir' => 'out',
        ]);

        $this->insert('animalid.filter', [
           'type' => 'pet',
           'field' => 'kind',
            'value' => 'С/Х животные',
            'dir' => 'in',
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190826_064026_add_animalid_filters_and_converters_records cannot be reverted.\n";

        return false;
    }
    */
}
