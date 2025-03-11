<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m180827_024827_insert_service_types_description_types
 */
class m180827_024827_insert_service_types_description_types extends Migration
{
    private $tableName = 'service_types_description_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
Терапия;Анамнез
Терапия;Симптомы
Терапия;Предварительный диагноз
Терапия;Заключительный диагноз
Терапия;Схема лечения
Терапия;Рекомендации 
Вакцинация;Анамнез
Вакцинация;Симптомы
Вакцинация;Рекомендации 
Груминг;Анамнез
Груминг;Симптомы
Груминг;Рекомендации 
Хирургия;Анамнез
Хирургия;Симптомы
Хирургия;Предварительный диагноз
Хирургия;Заключительный диагноз
Хирургия;Схема лечения
Хирургия;Рекомендации 
Стоматология;Анамнез
Стоматология;Симптомы
Стоматология;Предварительный диагноз
Стоматология;Заключительный диагноз
Стоматология;Схема лечения
Стоматология;Рекомендации 
Офтальмология;Анамнез
Офтальмология;Симптомы
Офтальмология;Предварительный диагноз
Офтальмология;Заключительный диагноз
Офтальмология;Схема лечения
Офтальмология;Рекомендации 
Оказание услуг на дому;Анамнез
Оказание услуг на дому;Симптомы
Оказание услуг на дому;Предварительный диагноз
Оказание услуг на дому;Заключительный диагноз
Оказание услуг на дому;Схема лечения
Оказание услуг на дому;Рекомендации 
CSV;

        $data = str_getcsv($csv, "\n");
        foreach ($data as $row) {
            $arr = str_getcsv($row, ';', '');
            $serviceTypeName = $arr[0];
            $descriptionName = $arr[1];

            $service_type = (new Query())
                ->select('*')
                ->from('service_types')
                ->where(['name' => $serviceTypeName])
                ->limit(1)
                ->one();

            $description_type = (new Query())
                ->select('*')
                ->from('description_types')
                ->where([
                    'name' => $descriptionName,
                    'entity_type' => 'visit',
                ])
                ->limit(1)
                ->one();

            if (empty($service_type)) {
                echo 'service_type ' . $serviceTypeName . ' not found ' . "\n";
            } elseif (empty($description_type)) {
                echo 'description_type ' . $descriptionName . ' not found ' . "\n";
            } else {
                Yii::$app->db
                    ->createCommand()
                    ->insert($this->tableName, ['id_service_type' => $service_type['id'], 'id_description_type' => $description_type['id']])
                    ->execute();
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180827_024827_insert_service_types_description_types cannot be reverted.\n";

        return false;
    }
    */
}
