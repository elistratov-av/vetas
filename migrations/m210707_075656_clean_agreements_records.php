<?php

use app\commands\migrate\Migration;

/**
 * Class m210707_075656_clean_agreements_records
 */
class m210707_075656_clean_agreements_records extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('alter table agreements alter column id_organization drop not null');
        $this->execute('alter table agreements alter column id_visit drop not null');
//        $this->execute('alter table agreements alter column id_organization drop not null');
        $this->update('agreements', ['id_organization' => null]);
        $this->update(
            'agreements',
            ['is_agree' => false],
            'id not in (select max(id)
              from agreements a
              where is_agree = true
              group by id_pet_owner)');
        $this->update(
            'visits',
            [
                'is_agreed_pers_data' => null,
                'agreement_rejected_at' => null
            ],
            '
            is_agreed_pers_data is not null
            and id not in (select id_visit
              from agreements a
              where a.is_agree = true
              and id_type = 1)'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m210707_075656_clean_agreements_records cannot be reverted.\n";
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
        echo "m210707_075656_clean_agreements_records cannot be reverted.\n";

        return false;
    }
    */
}
