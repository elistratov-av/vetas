<?php

use app\commands\migrate\Migration;

/**
 * Class m190304_083404_animalid_init
 */
class m190412_083404_animalid_init extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->execute("create schema animalid");

        $this->createTable('animalid.queue', [
            'id' => $this->primaryKey(),
            'data' => $this->json(),
            'action' => $this->string(),
            'type' => $this->string(),
        ]);

        $this->createTable('animalid.conflicts', [
            'id' => $this->primaryKey(),
            'data' => $this->json(),
            'action' => $this->string(),
            'type' => $this->string(),
            'reason' => $this->string(),
        ]);

        $this->createTable('animalid.errors', [
            'id' => $this->primaryKey(),
            'data' => $this->json(),
            'action' => $this->string(),
            'type' => $this->string(),
            'reason' => $this->string(),
        ]);

        $this->createTable('animalid.id_mapping', [
            'id' => $this->primaryKey(),
            'our' => $this->integer(),
            'their' => $this->integer(),
            'type' => $this->string()
        ]);

        $this->createIndex('idx_id_mapping_our_their', 'animalid.id_mapping', ['our','their'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190304_083404_animalid_init cannot be reverted.\n";

        return false;
    }
}
