<?php

use yii\db\Migration;

/**
 * Class m180705_074747_move_entity_type_field_from_descriptions_to_description_types
 */
class m180705_074747_move_entity_type_field_from_descriptions_to_description_types extends Migration
{
    /**
     * примерный порядок действий при up/down этой миграции
     * 1. добавляем поле в таблицу
     * 2. переносим данные из старой таблицы
     * 3. создаем ограничение NOT NULL
     * 4. удаляем вьюхи (т.к. зависимости от поля в старой)
     * 5. удаляем поле в старой таблице
     * 6. создаем вьюхи
     */

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('description_types', 'entity_type', $this->string());
        $db = $this->getDb();
        $rows = $db->createCommand("select * from descriptions")->queryAll();
        $command = $db->createCommand("UPDATE description_types SET entity_type = :entity_type WHERE id = :id");
        foreach ($rows as $row) {
            $command->bindValues([
                'id' => $row['id_description_type'],
                'entity_type' => $row['entity_type']
            ])->execute();
        }
        $this->execute("ALTER TABLE description_types ALTER COLUMN entity_type SET NOT NULL");

        $this->execute("DROP VIEW public.drugs_descriptions");
        $this->execute('DROP VIEW public.drugs_description_types');

        $this->dropColumn('descriptions', 'entity_type');

        $this->execute("
            CREATE OR REPLACE VIEW public.drugs_description_types AS 
                SELECT descriptions.*, description_types.name, description_types.entity_type FROM descriptions
                JOIN description_types ON description_types.id = descriptions.id_description_type
                WHERE description_types.entity_type::text = 'drug'::text
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('descriptions', 'entity_type', $this->string());
        $db = $this->getDb();
        $rows = $db->createCommand("
            select self.id, dt.entity_type from descriptions as self
            join description_types as dt on dt.id = self.id_description_type
          ")->queryAll();
        $command = $db->createCommand("UPDATE descriptions SET entity_type = :entity_type WHERE id = :id");
        foreach ($rows as $row) {
            $command->bindValues([
                'id' => $row['id'],
                'entity_type' => $row['entity_type']
            ])->execute();
        }
        $this->execute("ALTER TABLE descriptions ALTER COLUMN entity_type SET NOT NULL");

        $this->execute('DROP VIEW public.drugs_description_types');

        $this->dropColumn('description_types', 'entity_type');

        $this->execute("CREATE OR REPLACE VIEW public.drugs_description_types AS SELECT * FROM descriptions");
        $this->execute("CREATE OR REPLACE VIEW public.drugs_descriptions AS SELECT * FROM descriptions");
    }

}
