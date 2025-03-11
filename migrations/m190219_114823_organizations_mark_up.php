<?php

use app\commands\migrate\Migration;

/**
 * Class m190219_114823_organizations_mark_up
 */
class m190219_114823_organizations_mark_up extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'organizations',
            'mark_up_flag',
            $this->boolean()->defaultValue('false')
        );

        $this->addColumn(
            'organizations',
            'mark_up_ratio',
            $this->decimal(8, 2)
        );

        $this->addColumn(
            'organizations',
            'mark_up_from_time',
            $this->time(0)
        );

        $this->addColumn(
            'organizations',
            'mark_up_to_time',
            $this->time(0)
        );

        $this->execute('
ALTER TABLE public.organizations 
  ADD CONSTRAINT duration_check 
    CHECK (mark_up_ratio >= 1 OR mark_up_ratio IS NULL)');

        $this->update('organizations',[
            'mark_up_flag' => true,
            'mark_up_ratio' => 2,
            'mark_up_from_time' => '22:00',
            'mark_up_to_time' => '6:00',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'organizations',
            'mark_up_flag'
        );

        $this->dropColumn(
            'organizations',
            'mark_up_ratio'
        );

        $this->dropColumn(
            'organizations',
            'mark_up_from_time'
        );

        $this->dropColumn(
            'organizations',
            'mark_up_to_time'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190219_114823_organizations_mark_up cannot be reverted.\n";

        return false;
    }
    */
}
