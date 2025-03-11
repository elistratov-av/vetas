<?php

use app\commands\migrate\Migration;

/**
 * Class m190205_105141_pet_owners_snils_uniq
 */
class m190205_105141_pet_owners_snils_uniq extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $sql = '
UPDATE pet_owners SET snils = NULL
WHERE snils IN (
  SELECT  snils FROM pet_owners
  WHERE snils IS NOT NULL
  GROUP BY snils
  HAVING count(*) > 1
);';
        $this->execute($sql);

        /** Только для неудаленных уникальность snils (исключая NULL) **/
        $create_partial_uniq_snils_index = '
        CREATE UNIQUE INDEX partial_uniq_snils_in_pet_owners
        ON pet_owners (snils) 
        WHERE is_deleted = FALSE AND snils IS NOT NULL';

        $this->execute($create_partial_uniq_snils_index);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('partial_uniq_snils_in_pet_owners', 'pet_owners');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190205_105141_pet_owners_snils_uniq cannot be reverted.\n";

        return false;
    }
    */
}
