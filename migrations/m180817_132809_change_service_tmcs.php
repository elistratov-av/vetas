<?php

use yii\db\Migration;

/**
 * Class m180817_132809_change_service_tmcs
 */
class m180817_132809_change_service_tmcs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('service_tmcs', 'id_tmc_type', $this->integer());
        $sql = <<<SQL
select 
service_tmcs.*, tmc.id_tmc_type as tmc_id_tmc_type  
from service_tmcs 
left join tmc on tmc.id = service_tmcs.id_tmc
SQL;

        if ($rows = $this->db->createCommand($sql)->queryAll()) {
            foreach ($rows as $row) {
                if (isset($row['tmc_id_tmc_type'])) {
                    $this->update('service_tmcs', [
                        'id_tmc_type' => $row['tmc_id_tmc_type']
                    ], [
                        'id' => $row['id']
                    ]);
                }
            }
        }

        $this->addForeignKey(
            'fk-service_tmcs-id_tmc_type',
            'service_tmcs',
            'id_tmc_type',
            'tmc_types',
            'id'
        );

        $this->createIndex(
            'idx-service_tmcs-unique_service_tmc_type',
            'service_tmcs',
            ['id_service', 'id_tmc_type'],
            true
        );

        $this->dropIndex('idx-service_tmcs-unique_tmc_service', 'service_tmcs');
        $this->dropColumn('service_tmcs', 'id_tmc');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-service_tmcs-unique_service_tmc_type', 'service_tmcs');
        $this->addColumn('service_tmcs', 'id_tmc', $this->integer());
        $this->dropColumn('service_tmcs', 'id_tmc_type');
    }

}
