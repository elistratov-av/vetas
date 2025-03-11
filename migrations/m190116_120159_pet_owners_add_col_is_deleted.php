<?php

use app\commands\migrate\Migration;

/**
 * Class m190116_120159_pet_owners_add_col_is_deleted
 */
class m190116_120159_pet_owners_add_col_is_deleted extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'is_deleted', $this->boolean()->notNull()->defaultValue('false'));
        $this->addCommentOnColumn('pet_owners', 'is_deleted', 'Флаг: пользователь удален');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners', 'is_deleted');
    }


}
