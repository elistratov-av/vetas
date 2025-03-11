<?php

use app\commands\migrate\Migration;

/**
 * Class m190811_165541_change_dosages_calc_relations
 */
class m190811_165541_change_dosages_calc_relations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('truncate dosages CASCADE');
        $this->execute('truncate diseases_dosages CASCADE ');
        $this->execute('truncate species_dosages CASCADE ');

        $sql = <<<SQL
alter table public.dosages drop constraint if exists age_range_constraint;
SQL;
        $this->execute($sql);
        $sql = <<<SQL
alter table public.dosages drop constraint if exists weight_range_constraint;
SQL;
        $this->execute($sql);

        $this->addColumn('dosages', 'hash', $this->string(255));

        $sql = <<<SQL
alter table public.dosages add constraint
	age_range_constraint EXCLUDE USING GIST (
	age_range WITH &&,
	hash WITH =
);
SQL;
        $this->execute($sql);

        $sql = <<<SQL
alter table public.dosages add constraint
  weight_range_constraint EXCLUDE USING GIST (
  weight_range WITH &&,
  hash WITH =
);
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190811_165541_change_dosages_calc_relations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190811_165541_change_dosages_calc_relations cannot be reverted.\n";

        return false;
    }
    */
}
