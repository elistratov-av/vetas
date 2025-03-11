<?php

use yii\db\Migration;

/**
 * Handles the creation of table `drug_orgs`.
 */
class m180627_101834_create_drug_orgs_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('drug_orgs', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->unique()->notNull(),
        ]);
        $this->createIndex('idx_drug_orgs_name', 'drug_orgs', 'name', true);

        $this->insert('drug_orgs', ['name' => 'Орг1']);
        $this->execute('ALTER TABLE "drugs"
ALTER COLUMN "id_representation" TYPE integer USING (id_representation::integer)');
        $this->update('drugs', [
            'id_manufactured' => 1,
            'id_registered' => 1,
            'id_representation' => 1,
        ]);

        $this->addForeignKey('fk-drugs-id_manufactured', 'drugs',
            'id_manufactured',
            'drug_orgs',
            'id');

        $this->addForeignKey('fk-drugs-id_registered', 'drugs', 'id_registered',
            'drug_orgs',
            'id');

        $this->addForeignKey('fk-drugs-id_representation', 'drugs',
            'id_representation',
            'drug_orgs',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_drug_orgs_name', 'drug_orgs');
        $this->dropForeignKey('fk-drugs-id_manufactured', 'drugs');
        $this->dropForeignKey('fk-drugs-id_registered', 'drugs');
        $this->dropForeignKey('fk-drugs-id_representation', 'drugs');
        $this->dropTable('drug_orgs');
    }
}
