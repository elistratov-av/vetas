<?php

use app\commands\migrate\Migration;

/**
 * Class m190110_075224_table_pets_to_owner
 */
class m190110_075224_table_pets_to_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pets_to_owner',[
            'id' => $this->primaryKey(),
            'id_owner' => $this->integer()->notNull(),
            'id_pet' => $this->integer()->notNull(),
            'id_owner_type' => $this->integer()->notNull()
        ]);

        $this->addForeignKey(
            'fk-pets_to_owner-id_owner',
            'pets_to_owner',
            'id_owner',
            'pet_owners',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-pets_to_owner-id_pet',
            'pets_to_owner',
            'id_pet',
            'pets',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk-pets_to_owner-id_owner_type',
            'pets_to_owner',
            'id_owner_type',
            'pet_owner_type',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            'uniq_pets_to_owner_id_owner_id_pet',
            'pets_to_owner',
            ['id_pet', 'id_owner'],
            TRUE
        );

        $this->addCommentOnTable('pets_to_owner', 'Связь животные-владельцы');

        // Migrate data
        $owner_type_id = (new \yii\db\Query())
            ->select('id')
            ->from('pet_owner_type')
            ->where(['name' => 'Владелец'])
            ->scalar()
        ;

        if (empty($owner_type_id)){
            throw new \Exception('Cant find type OWNER in table pet_owner_type');
        }


        $sql = '
        INSERT INTO pets_to_owner (id_owner, id_pet, id_owner_type)
        SELECT 
               id_owner, 
               id AS id_pet,
               '. $owner_type_id.' AS id_owner_type
        FROM pets 
        WHERE 
            id_owner IS NOT NULL
            AND
            id_owner IN (SELECT id FROM pet_owners)
        ';

        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pets_to_owner-id_owner', 'pets_to_owner');
        $this->dropForeignKey('fk-pets_to_owner-id_pet','pets_to_owner');
        $this->dropForeignKey('fk-pets_to_owner-id_owner_type','pets_to_owner');

        $this->dropTable('pets_to_owner');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190110_075224_table_pets_to_owner cannot be reverted.\n";

        return false;
    }
    */
}
