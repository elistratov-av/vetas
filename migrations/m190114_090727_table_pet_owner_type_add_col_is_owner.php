<?php

use app\commands\migrate\Migration;

/**
 * Class m190114_090727_table_pet_owner_type_add_col_is_owner
 */
class m190114_090727_table_pet_owner_type_add_col_is_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'pet_owner_type',
            'is_owner',
            $this->boolean()->notNull()->defaultValue(false)
        );

        $owner_type_id = (new \yii\db\Query())
            ->select('id')
            ->from('pet_owner_type')
            ->where(['name' => 'Владелец'])
            ->scalar()
        ;

        if (empty($owner_type_id)){
            throw new \Exception('Cant find type OWNER in table pet_owner_type');
        }

        $this->execute('UPDATE pet_owner_type SET is_owner=true WHERE id = :id',['id' => $owner_type_id]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owner_type', 'is_owner');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190114_090727_table_pet_owner_type_add_col_is_owner cannot be reverted.\n";

        return false;
    }
    */
}
