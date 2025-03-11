<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\GovServicesReports;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m200115_112542_update_2020_year_pricelist_mvo_3_params_0284
 */
class m200115_112542_update_2020_year_pricelist_mvo_3_params_0284 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $service = (new Query())
            ->from(GovServices::tableName())
            ->where(['name' => 'Общий клинический анализ крови - подсчет форменных элементов крови (эритроцитов, лейкоцитов) с определением гемоглобина'])
            ->one();

        if (empty($service)) {
            Console::output('Gov service 0284 not found');
            return false;
        }

        $sql = 'select gsp.* from public.gov_services_params gsp left join public.gov_services gs on gsp.id_service = gs.id where gs.name in (\'Общий клинический анализ крови - определение гемоглобина\', \'Общий клинический анализ крови - подсчет эритроцитов\', \'Общий клинический анализ крови - подсчет лейкоцитов\') order by gs.id asc, gsp.sort_by asc, gs.id asc';

        $rows = $this->db
            ->createCommand($sql)
            ->queryAll();

        if (empty($rows)) {
            Console::output('Params for service 0284 not found');
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $id_service = $service['id'];
        $id_report = 17;

        foreach ($rows as $row) {
            unset($row['id']);
            $row['id_service'] = $id_service;
            $row['created_at'] = $now;
            $row['updated_at'] = null;
            $result = $this->db
                ->createCommand()
                ->insert(GovServicesParams::tableName(), $row)
                ->execute();

            if ($result != 1) {
                Console::output('Failed to link param ' . $row['id_param'] . ' with service 0284');
                return false;
            }
        }

        $columns = [
            'id_report' => $id_report,
            'id_service' => $id_service,
            'created_at' => $now,
        ];

        $result = $this->db->createCommand()
            ->insert(GovServicesReports::tableName(), $columns)
            ->execute();

        if ($result != 1) {
            Console::output('Failed to link report ' . $id_report . ' with service 0284');
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200115_112542_update_2020_year_pricelist_mvo_3_params_0284 cannot be reverted.\n";

        return false;
    }
}
