<?php

use app\commands\migrate\Migration;

/**
 * Class m180926_070848_add_reg_number_field_to_organizations_table
 */
class m180926_070848_add_reg_number_field_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'reg_number', $this->integer());
        $regs = [
            '770101' => '527',
            '770102' => '644',
            '770203' => '515',
            '770304' => '662',
            '770305' => '570',
            '770306' => '638',
            '770307' => '640',
            '770408' => '307',
            '770509' => '529',
            '770510' => '580',
            '770511' => '646',
            '770613' => '517',
            '770714' => '533',
            '770815' => '574',
            '770816' => '576',
            '770917' => '525',
            '770918' => '578',
            '771019' => '523',
            '771020' => '572',
            '771221' => '513',
            '771222' => '628',
            '771223' => '630',
            '771224' => '634',
            '771225' => '636',
            '771226' => '632',
            '770427' => '458',
        ];
        foreach ($regs as $reg => $id){
            $this->update('organizations', ['reg_number' => $reg], ['id' => $id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'reg_number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180926_070848_add_reg_number_field_to_organizations_table cannot be reverted.\n";

        return false;
    }
    */
}
