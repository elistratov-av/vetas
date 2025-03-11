<?php

use app\commands\migrate\Migration;

/**
 * Добавляет в таблицу visit_descriptions столбец id_pet и FK по нему для связи с таблицей pets
 * @see https://jira.altarix.ru/browse/VETAIS-3324
 * @see https://jira.altarix.ru/browse/VETAIS-3316
 */
class m210504_115053_add_visit_descriptions_id_pet_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%visit_descriptions}}', 'id_pet', $this->integer()->null());
        $this->addForeignKey(
            'fk-visit_descriptions-pets',
            '{{%visit_descriptions}}',
            'id_pet',
            '{{%pets}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        // Запишем в id_pet описаний значение id_pet из связанного визита.
        $sql = <<<sql
UPDATE
    {{%visit_descriptions}} vd
SET
    id_pet = v.id_pet
FROM
    {{%visits}} v
WHERE
    v.id = vd.id_visit
;
sql;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-visit_descriptions-pets',
            '{{%visit_descriptions}}'
        );
        $this->dropColumn('{{%visit_descriptions}}', 'id_pet');
    }
}
