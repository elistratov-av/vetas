<?php

use app\commands\migrate\Migration;

/**
 * Class m210524_041841_add_address_to_visit_pets
 */
class m210524_041841_add_address_to_visit_pets extends Migration
{
    const FK_VISIT_PETS_FIAS_ADDRESSES = 'fk-visit_pets-fias_address';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            \app\models\db\VisitPets::tableName(),
            'id_fias_address',
            $this->integer()->comment('Адрес содержания на момент завершения приема')
        );
        $this->addForeignKey(
            self::FK_VISIT_PETS_FIAS_ADDRESSES,
            \app\models\db\VisitPets::tableName(),
            'id_fias_address',
            \app\models\db\FiasAddresses::tableName(),
            'id'
        );
        $sql = <<<SQL
UPDATE visit_pets
SET id_fias_address = pets.id_fias_address
FROM pets
WHERE pets.id = visit_pets.id_pet
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(self::FK_VISIT_PETS_FIAS_ADDRESSES, \app\models\db\VisitPets::tableName());
        $this->dropColumn(\app\models\db\VisitPets::tableName(), 'id_fias_address');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210524_041841_add_address_to_visit_pets cannot be reverted.\n";

        return false;
    }
    */
}
