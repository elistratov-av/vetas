<?php

use app\commands\migrate\Migration;

/**
 * Class m210330_211039_3296_change_comment_in_production_form
 */
class m210330_211039_3296_change_comment_in_production_form extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addCommentOnColumn('tmc.production_form', 'is_utilize',
            'Переиначено: теперь - списывать(использовать) только в форме выпуска');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addCommentOnColumn('tmc.production_form', 'is_utilize',
            'Автоматически утилизировать остаток после использования');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210330_211039_3296_change_comment_in_production_form cannot be reverted.\n";

        return false;
    }
    */
}
