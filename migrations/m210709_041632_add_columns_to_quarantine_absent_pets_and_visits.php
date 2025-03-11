<?php

use app\commands\migrate\Migration;

/**
 * Class m210709_041632_add_columns_to_quarantine_absent_pets_and_visits
 */
class m210709_041632_add_columns_to_quarantine_absent_pets_and_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('TRUNCATE quarantine_absent_pets');
        $this->dropColumn('quarantine_absent_pets', 'full_address');
        $this->addColumn('quarantine_absent_pets', 'id_fias_address', $this->integer()->notNull()->comment('Адрес по которому не обнаружено животное'));
        $this->addColumn('visits', 'id_quarantine', $this->integer()->comment('Карантин в рамках которого создан осмотр'));

        $this->addForeignKey(
            'fk-quarantine_absent_pets-id_fias_address',
            'quarantine_absent_pets',
            'id_fias_address',
            'fias_addresses',
            'id'
        );

        $this->addForeignKey(
            'fk-visits_id_quarantine',
            'visits',
            'id_quarantine',
            'quarantines',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('quarantine_absent_pets', 'id_fias_address');
        $this->dropColumn('visits', 'id_quarantine');
        $this->addColumn('quarantine_absent_pets', 'full_address', $this->string());
    }
}
