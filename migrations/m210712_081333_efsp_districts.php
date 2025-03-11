<?php

use app\commands\migrate\Migration;

/**
 * Class m210712_081333_efsp_districts
 */
class m210712_081333_efsp_districts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'efsp.districts', [
                'id' => $this->primaryKey(),
                'parent_id' => $this->integer(),
                'level' => $this->integer()->notNull(),
                'name' => $this->char(256),
                'type' => $this->char(256),
                'okato' => $this->char(256),
                'oktmo' => $this->char(256),
                'code' => $this->char(256),
                'bti_city_area_code' => $this->char(256)->unique(),
            ]
        );

        $data = file_get_contents(__DIR__ . '/data/districts.json');
        $districts = json_decode($data);

        foreach ($districts->level2 as $item) {
            $this->insert('efsp.districts', [
                'level' => 2,
                'name' => $item->name,
                'type' => $item->type,
                'okato' => $item->okato,
                'oktmo' => isset( $item->oktmo) ? $item->oktmo : null,
                'code' => $item->code,
                'bti_city_area_code' => $item->bti_adm_area_code,
            ]);
            $parent_id = $this->db->getLastInsertID();
            /*
             * Level 3
             */
            foreach ($item->level3 as $level3) {
                $this->insert('efsp.districts', [
                    'parent_id' => $parent_id,
                    'level' => 3,
                    'name' => $level3->name,
                    'type' => $level3->type,
                    'okato' => $level3->okato,
                    'oktmo' => $level3->oktmo,
                    'code' => $level3->code,
                    'bti_city_area_code' => $level3->bti_city_area_code,
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('efsp.districts');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210712_081333_efsp_districts cannot be reverted.\n";

        return false;
    }
    */
}
