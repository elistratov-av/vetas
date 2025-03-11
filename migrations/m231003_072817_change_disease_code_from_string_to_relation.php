<?php

use app\commands\migrate\Migration;

/**
 * Class m231003_072817_change_disease_code_from_string_to_relation
 */
class m231003_072817_change_disease_code_from_string_to_relation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('public.diseases', 'gost_code');
        $this->addColumn('public.diseases', 'id_gost_disease', $this->integer()->comment('Идентификатор болезни из справочника болезней ГОСТ'));

        $this->addForeignKey(
            'fk-diseases-id_gost_disease',
            'diseases',
            'id_gost_disease',
            'gost_diseases',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-diseases-id_gost_disease',
            'diseases'
        );
        $this->dropColumn('diseases', 'id_gost_disease');

        $this->addColumn('diseases', 'gost_code', $this->string()->defaultValue(null)->comment('Код ГОСТ для болезни'));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231003_072817_change_disease_code_from_string_to_relation cannot be reverted.\n";

        return false;
    }
    */
}
