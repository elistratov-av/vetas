<?php

use app\commands\migrate\Migration;

/**
 * Class m210803_042141_create_reg_expire_reason_tech_name
 */
class m210803_042141_create_reg_expire_reason_tech_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('reg_expire_reasons', 'tech_name', $this->string(50)->comment('Техническое имя причины'));

        $this->update('reg_expire_reasons', [
            'tech_name' => 'DEATH',
        ], ['name' => 'смерть']);

        $this->insert('reg_expire_reasons', [
            'name' => 'Не существует',
            'description' => 'Животное заведённое в системе не существует',
            'tech_name' => 'NOT_EXIST',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('reg_expire_reasons', ['tech_name' => 'NOT_EXIST']);

        $this->dropColumn('reg_expire_reasons', 'tech_name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210803_042141_create_reg_expire_reason_tech_name cannot be reverted.\n";

        return false;
    }
    */
}
