<?php

use app\commands\migrate\Migration;

/**
 * Class m190805_122301_fix_pet_dehelmintization_comment
 */
class m190805_122301_fix_pet_dehelmintization_comment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addCommentOnTable(
            'pet_dehelmintization',
            'Дегельминтизация'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addCommentOnTable(
            'pet_dehelmintization',
            'Обработки против эктопаразитов'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190805_122301_fix_pet_dehelmintization_comment cannot be reverted.\n";

        return false;
    }
    */
}
