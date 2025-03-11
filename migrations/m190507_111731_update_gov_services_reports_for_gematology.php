<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use yii\helpers\Console;

/**
 * Class m190507_111731_update_gov_services_reports_for_gematology
 */
class m190507_111731_update_gov_services_reports_for_gematology extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $id_report = 24;

        $serviceNames = [
            'Общий клинический анализ крови - определение гемоглобина',
            'Общий клинический анализ крови - подсчет эритроцитов',
            'Общий клинический анализ крови - подсчет лейкоцитов',
            'Общий клинический анализ крови - определение СОЭ',
            'Общий клинический анализ крови - выведение лейкоцитарной формулы',
        ];

        foreach ($serviceNames as $serviceName) {
            $govService = GovServices::find()
                ->where(['name' => $serviceName])
                ->limit(1)
                ->one();
            if ($govService === null) {
                Console::output('Service not found: ' . $serviceName);
                continue;
            }
            $this->db
                ->createCommand()
                ->insert(
                    'gov_services_reports',
                    [
                        'id_report' => $id_report,
                        'id_service' => $govService->id,
                    ]
                )
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190507_111731_update_gov_services_reports_for_gematology cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190507_111731_update_gov_services_reports_for_gematology cannot be reverted.\n";

        return false;
    }
    */
}
