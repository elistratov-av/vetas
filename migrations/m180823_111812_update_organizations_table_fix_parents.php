<?php

use yii\db\Migration;

/**
 * Class m180823_111812_update_organizations_table_fix_parents
 */
class m180823_111812_update_organizations_table_fix_parents extends Migration
{
    private $tableName = 'organizations';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->tableName = 'organizations';

        $this->update($this->tableName, ['parent_id' => 0], ['parent_id' => null]);

        $this->execute('ALTER TABLE ' . $this->tableName . ' ALTER COLUMN parent_id SET DEFAULT 0');
        $this->execute('ALTER TABLE ' . $this->tableName . ' ALTER COLUMN parent_id SET NOT NULL');

        $this->createIndex('idx-' . $this->tableName . '-parent_id', $this->tableName, 'parent_id');

        $this->execute('UPDATE ' . $this->tableName . ' SET parent_id = 0 WHERE parent_id = id');

        $this->execute('UPDATE ' . $this->tableName . ' SET parent_id = 0 WHERE parent_id != 0 AND parent_id NOT IN (select id FROM ' . $this->tableName . ')');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-' . $this->tableName . '-parent_id', $this->tableName);

        $this->execute('ALTER TABLE ' . $this->tableName . ' ALTER COLUMN parent_id DROP NOT NULL');
        $this->execute('ALTER TABLE ' . $this->tableName . ' ALTER COLUMN parent_id SET DEFAULT NULL');
        $this->update($this->tableName, ['parent_id' => null], ['parent_id' => 0]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180823_111812_update_organizations_table_fix_parents cannot be reverted.\n";

        return false;
    }
    */
}
