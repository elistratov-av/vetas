<?php

use app\commands\migrate\Migration;

/**
 * Class m180923_163648_add_call_to_home_shift_type
 */
class m180923_163648_add_call_to_home_shift_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $WORKDAY_id = $this->getDb()->createCommand("SELECT id FROM shift_type WHERE type = 'WORKDAY'")->queryScalar();

        $this->insert('shift_type',[
            'parent_id' => $WORKDAY_id,
            'type' => 'CALL_TO_HOME',
            'idle' => FALSE,
            'description' => 'Выезд на дом',
            'colour' => '#d680ab'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('shift_type',[
            'type' => 'CALL_TO_HOME',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180923_163648_add_call_to_home_shift_type cannot be reverted.\n";

        return false;
    }
    */
}
