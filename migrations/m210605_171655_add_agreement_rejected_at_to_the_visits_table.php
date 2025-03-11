<?php

use app\commands\migrate\Migration;

/**
 * Class m210605_171655_add_agreement_rejected_at_to_the_visits_table
 */
class m210605_171655_add_agreement_rejected_at_to_the_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'agreement_rejected_at', $this->timestamp(0));
        $this->addCommentOnColumn(
            'visits',
            'agreement_rejected_at',
            'Временная метка отмены последнего действующего согласия на обработку персональных данных'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'agreement_rejected_at');
//        echo "m210605_171655_add_agreement_rejected_at_to_the_visits_table cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210605_171655_add_agreement_rejected_at_to_the_visits_table cannot be reverted.\n";

        return false;
    }
    */
}
