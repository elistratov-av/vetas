<?php

use app\commands\migrate\Migration;

/**
 * Class m210915_112310_add_created_at_to_owner_feedback
 */
class m210915_112310_add_created_at_to_owner_feedback extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('owner_feedback', 'created_at', $this->date()->comment('Дата заполнения формы обратной связи'));
        $this->addColumn('owner_feedback', 'updated_at', $this->date());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('owner_feedback', 'created_at');
        $this->dropColumn('owner_feedback', 'updated_at');
    }
}
