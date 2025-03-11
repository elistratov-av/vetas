<?php

use yii\db\Migration;

/**
 * Handles the creation of table `balance_vaccines`.
 * Has foreign keys to the tables:
 *
 * - `organizations`
 * - `vaccines`
 */
class m180726_073531_create_balance_vaccines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('balance_vaccines', [
            'id' => $this->primaryKey(),
            'dose_count' => $this->integer()->notNull(),
            'inventory_number' => $this->string(150)->notNull(),
            'registration_date' => $this->date()->notNull(),
            'id_organization' => $this->integer(),
            'id_vaccine' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-balance_vaccines-id_organization',
            'balance_vaccines',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-balance_vaccines-id_organization',
            'balance_vaccines',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        // creates index for column `id_vaccine`
        $this->createIndex(
            'idx-balance_vaccines-id_vaccine',
            'balance_vaccines',
            'id_vaccine'
        );

        // add foreign key for table `vaccines`
        $this->addForeignKey(
            'fk-balance_vaccines-id_vaccine',
            'balance_vaccines',
            'id_vaccine',
            'vaccines',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-balance_vaccines-id_organization',
            'balance_vaccines'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-balance_vaccines-id_organization',
            'balance_vaccines'
        );

        // drops foreign key for table `vaccines`
        $this->dropForeignKey(
            'fk-balance_vaccines-id_vaccine',
            'balance_vaccines'
        );

        // drops index for column `id_vaccine`
        $this->dropIndex(
            'idx-balance_vaccines-id_vaccine',
            'balance_vaccines'
        );

        $this->dropTable('balance_vaccines');
    }
}
