<?php

use yii\db\Migration;

/**
 * Class m180822_103239_update_vaccines_table
 */
class m180822_103239_update_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('vaccines', ['id_registered' => 0], ['id_registered' => null]);
        $this->update('vaccines', ['id_produced' => 0], ['id_produced' => null]);
        $this->update('vaccines', ['id_dealer' => 0], ['id_dealer' => null]);

        $this->execute('ALTER TABLE vaccines ALTER COLUMN id_registered SET NOT NULL');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN id_produced SET NOT NULL');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN id_dealer SET NOT NULL');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN form SET NOT NULL');

        $this->execute('ALTER TABLE tmc ALTER COLUMN name TYPE text USING name::text');

        $this->execute('ALTER TABLE vaccines ALTER COLUMN form TYPE text USING form::text');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN form_description TYPE text USING form_description::text');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN excipients TYPE text USING excipients::text');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN basis TYPE text USING basis::text');
        $this->execute('ALTER TABLE vaccines ALTER COLUMN unit TYPE double precision');

        $this->execute('ALTER TABLE content_of_active_substances ALTER COLUMN unit DROP NOT NULL');
        $this->execute('ALTER TABLE content_of_active_substances ALTER COLUMN unit TYPE double precision USING unit::double precision');

        $this->execute('ALTER TABLE content_of_active_substances ALTER COLUMN id_measure DROP NOT NULL');

        $this->dropColumn('vaccines', 'packaging');
        $this->addColumn('vaccines', 'packaging', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180822_103239_update_vaccines_table cannot be reverted.\n";

        return false;
    }
    */
}
