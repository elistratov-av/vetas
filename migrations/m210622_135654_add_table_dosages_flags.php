<?php

use app\commands\migrate\Migration;

/**
 * Class m210622_135654_add_table_dosages_flags
 */
class m210622_135654_add_table_dosages_flags extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tmc.dosages_flags', [
            'id'         => $this->primaryKey(),
            'id_dosages' => $this->integer()->comment('ID дозировки'),
            'for_act'    => $this->boolean()->comment('Флаг: для актов'),
        ]);

        $this->addCommentOnTable('tmc.dosages_flags', 'Флаги для дозировок');
        $this->createIndex('dosages_flag_dosages', 'tmc.dosages_flags', 'id_dosages');
        $this->addForeignKey('fk-dosages_flags-id_dosages', 'tmc.dosages_flags', 'id_dosages', 'tmc.dosages', 'id', 'CASCADE', 'CASCADE');

        $dosages = Yii::$app->db->createCommand('
            SELECT *,
                   (
                       SELECT count(*)
                       FROM tmc.dosages d2
                       WHERE d1.id_tmc = d2.id_tmc
                         AND d2.id < d1.id
                   ) AS for_act
            FROM tmc.dosages d1
            ORDER BY d1.id
        ')->queryAll();

        foreach ($dosages as $dosage) {
            $this->insert('tmc.dosages_flags', [
                'id_dosages' => $dosage['id'],
                'for_act'    => $dosage['for_act'] === 0 ? true : false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('tmc.dosages_flags');
    }
}
