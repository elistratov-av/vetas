<?php

use app\commands\migrate\Migration;

/**
 * Class m240213_094436_create_add_service_description_type_89
 */
class m240213_094436_create_add_service_description_type_89 extends Migration
{

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $this->insert('description_types',[
            'name' => 'Примечание ',
            'entity_type' => 'visit',
            'tech_name' => 'VISIT_PRIMECHANIE',
        ]);

        $this->insert('description_types',[
            'name' => 'Заключительный диагноз ',
            'entity_type' => 'visit',
            'tech_name' => 'VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_DISEASE',
        ]);

        $this->insert('description_types',[
            'name' => 'Предварительный диагноз ',
            'entity_type' => 'visit',
            'tech_name' => 'VISIT_PREDVARITELNYJ_DIAGNOZ_DISEASE',
        ]);

        // Добавление новых полей 'id_description_type' и 'required' в таблицу 'services_description_types'
        $this->addColumn('services_description_types', 'id_description_type', $this->integer()->notNull()->defaultValue(89));
        $this->addColumn('services_description_types', 'required', $this->boolean()->notNull()->defaultValue(1));

        // Выбор и вставка записей, удовлетворяющих условиям
        $sql = "SELECT id FROM gov_services WHERE name LIKE 'Общий анализ мочи' OR name LIKE 'Общий клинический анализ крови' OR name LIKE 'Биохимические исследования крови%' OR name LIKE 'Общий анализ кала'";
        $govServiceIds = $this->getDb()->createCommand($sql)->queryAll();

        foreach ($govServiceIds as $govServiceId) {
            $this->insert('services_description_types', [
                'id_service' => $govServiceId['id'],
                'id_description_type' => 89,
                'required' => 1,
            ]);
        }

    }


    public function down()
    {
        echo "m240213_094436_create_add_service_description_type_89 cannot be reverted.\n";

        return false;
    }

}
