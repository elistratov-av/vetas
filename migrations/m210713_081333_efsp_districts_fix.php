<?php

use app\commands\migrate\Migration;

/**
 * Class m210713_081333_efsp_districts_fix
 */
class m210713_081333_efsp_districts_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('efsp.districts');
        $this->createTable(
            'efsp.districts', [
                'id' => $this->primaryKey(),
                'parent_id' => $this->integer(),
                'level' => $this->integer()->notNull(),
                'name' => $this->string(256),
                'type' => $this->string(256),
                'okato' => $this->string(256),
                'oktmo' => $this->string(256),
                'code' => $this->string(256),
                'bti_city_area_code' => $this->string(256)->unique(),
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
        echo "m210713_081333_efsp_districts_fix cannot be reverted.\n";

        return false;
    }
    */
}
