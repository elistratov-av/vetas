<?php

use app\commands\migrate\Migration;
use app\models\db\Pets;

/**
 * Class m220124_123424_add_castrated_info
 */
class m220124_123424_add_castrated_info extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Pets::tableName(), 'castrated_date', $this->date());
        $this->addColumn(Pets::tableName(), 'castrated_specialist_id', $this->integer());
        $this->addColumn(Pets::tableName(), 'castrated_org_id', $this->integer());
        $this->addColumn(Pets::tableName(), 'early_castrated', $this->boolean()->defaultValue(false));

        $this->addForeignKey(
            'fk-pets-castrated_specialist_id',
            'pets',
            'castrated_specialist_id',
            'specialists',
            'id',
            'SET NULL'
        );
        $this->addForeignKey(
            'fk-pets-castrated_org_id',
            'pets',
            'castrated_org_id',
            'organizations',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pets-castrated_specialist_id', 'pets');
        $this->dropForeignKey('fk-pets-castrated_org_id', 'pets');

        $this->dropColumn(Pets::tableName(), 'castrated_date');
        $this->dropColumn(Pets::tableName(), 'castrated_specialist_id');
        $this->dropColumn(Pets::tableName(), 'castrated_org_id');
        $this->dropColumn(Pets::tableName(), 'early_castrated');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220124_123424_add_castrated_info cannot be reverted.\n";

        return false;
    }
    */
}