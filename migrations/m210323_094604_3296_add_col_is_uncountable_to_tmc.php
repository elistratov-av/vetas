<?php

use app\commands\migrate\Migration;

/**
 * Class m210323_094604_3296_add_col_is_uncountable_to_tmc
 */
class m210323_094604_3296_add_col_is_uncountable_to_tmc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.tmc', 'is_uncountable', $this->boolean());
        $this->addCommentOnColumn('tmc.tmc', 'is_uncountable', 'Неисчислимый расходный материал. Не списывается в приеме');

        $this->execute("UPDATE tmc.tmc SET is_uncountable = false WHERE type = 'exp_material'::tmc.tmc_class_list");
        $this->execute("
ALTER TABLE tmc.tmc 
  ADD CONSTRAINT is_uncountable_check 
    CHECK (
            (type = 'exp_material'::tmc.tmc_class_list AND is_uncountable IS NOT NULL)
            OR 
            (type <> 'exp_material'::tmc.tmc_class_list AND is_uncountable IS NULL)
        )");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE tmc.tmc 
  DROP CONSTRAINT is_uncountable_check");
        $this->dropColumn('tmc.tmc', 'is_uncountable');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210323_094604_3296_add_col_is_uncountable_to_tmc cannot be reverted.\n";

        return false;
    }
    */
}
