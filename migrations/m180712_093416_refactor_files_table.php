<?php

use yii\db\Migration;

/**
 * Class m180712_093416_refactor_files_table
 */
class m180712_093416_refactor_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('files', 'entity_id', $this->integer());
        $this->addColumn('files', 'entity_type', $this->string(30));

        $command = Yii::$app->db->createCommand("
            SELECT 
              files.id AS file_id, tmc.id AS entity_id, tmc_types.tmc_class AS entity_type 
            FROM files
            JOIN tmc ON tmc.id = files.id_tmc
            JOIN tmc_types ON tmc.id_tmc_type = tmc_types.id
        ");

        $updateCommand = Yii::$app->db->createCommand("
            UPDATE files SET entity_id = :entity_id, entity_type = :entity_type WHERE id = :file_id
        ");
        if ($rows = $command->queryAll()) {
            foreach ($rows as $row) {
                $updateCommand->bindValues($row)->execute();
            }
        }

        $this->dropColumn('files', 'id_tmc');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('files', 'id_tmc', $this->integer());

        $this->dropColumn('files', 'entity_id');
        $this->dropColumn('files', 'entity_type');
    }

}
