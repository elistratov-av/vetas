<?php

use yii\db\Migration;

/**
 * Class m180807_122954_update_addresses_table_add_name_tsvector
 */
class m180807_122954_update_addresses_table_add_name_tsvector extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('addresses', 'name_tsv', 'tsvector');
        $this->execute("CREATE INDEX \"idx-addresses-name_tsv\" ON addresses USING GIN (name_tsv);");

        /* @see https://postgrespro.ru/docs/postgrespro/10/textsearch-features#TEXTSEARCH-UPDATE-TRIGGERS */
        $this->execute("CREATE TRIGGER \"addresses-name_tsv-tsvectorupdate\" BEFORE INSERT OR UPDATE ON addresses FOR EACH ROW EXECUTE PROCEDURE tsvector_update_trigger(name_tsv, 'pg_catalog.russian', name);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TRIGGER IF EXISTS \"addresses-name_tsv-tsvectorupdate\" ON addresses");
        $this->dropIndex('idx-addresses-name_tsv', 'addresses');
        $this->dropColumn('addresses', 'name_tsv');
    }
}
