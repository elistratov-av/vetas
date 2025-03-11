<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m210315_165842_3296_visit_service_tmc_new_entity_ids
 */
class m210315_165842_3296_visit_service_tmc_new_entity_ids extends Migration
{

    protected $types = [
        'drug' => 'balance_drugs',
        'vaccine' => 'balance_vaccines',
        'exp_material' => 'balance_exp_materials',
        'equipment' => 'balance_equipments',
    ];

    protected function getTypeForVisitServiceTmc($type_from_balance)
    {
        if (array_key_exists($type_from_balance, $this->types)) {
            return $this->types[$type_from_balance];
        } else {
            Console::output(Console::ansiFormat('Unknown type in balance: ' . $type_from_balance, [Console::FG_RED]));
            die();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * При слиянии таблиц
         *  'balance_equipments', 'balance_vaccines', 'balance_exp_materials', 'balance_drugs'
         *  в таблицу tmc.balance сменился  id (тк ранее у каждой таблицы был свой PK)
         * При переносе старый id из этих таблиц  положили в old_id
         *
         * Необходимо теперь перебить в таблице visit_service_tmc поле id_entity на новый ID
         * для записей с entity_type IN ('balance_equipments', 'balance_vaccines', 'balance_exp_materials', 'balance_drugs')
         * Сопоставление типа в balance и visit_service_tmc
         *  [
         *  'drug' => 'balance_drugs',
         *  'vaccine' => 'balance_vaccines'
         *  'exp_material' => 'balance_exp_materials',
         *  'equipment' => 'balance_equipments',
         * ]
         * PS У остальных записей ID нормальный тк у родительских таблиц
         * 'drugs', 'equipments', 'exp_materials', 'vaccines' был сквозной, который просто портировали в tmc.tmc
         *
         * PPS У некоторых записей из visit_service_tmc уже может и не быть родителя tmc.balance
         * в случае, если он был удален ранее из таблиц balance_*
         * Но тут уже ничего не сделаешь. Умножим их id_entity на -1
         */

        // Что бы не обновились уже обновленные
        $this->addColumn(
            'visit_service_tmc',
            'flag_updated',
            $this->boolean()->defaultValue('false')
        );

        $query = new Query();
        $query->select([
            'id',
            'old_id',
            'type_tmc',
        ])->from('tmc.balance');

        foreach ($query->each(1) as $row) {
            $type_for_update = $this->getTypeForVisitServiceTmc($row['type_tmc']);
            $attrs = [
                ':new_id' => $row['id'],
                ':old_id' => $row['old_id'],
                ':type' => $type_for_update
            ];
            $this->execute(
                'UPDATE visit_service_tmc SET id_entity=:new_id, flag_updated = true 
                WHERE id_entity=:old_id AND entity_type=:type AND flag_updated = false',
                $attrs
            );
        }

        // Остальным ставим отрицательный id - дабы потом не пересеклись с настоящими добавленными позже
        $this->execute("
UPDATE visit_service_tmc SET id_entity = id_entity*-1 WHERE flag_updated <> true AND entity_type
  IN ('balance_equipments', 'balance_vaccines', 'balance_exp_materials', 'balance_drugs')");

        $this->dropColumn(
            'visit_service_tmc',
            'flag_updated'
        );
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /**
         * Возвращаем назад
         */

        // Что бы не обновились уже обновленные
        $this->addColumn(
            'visit_service_tmc',
            'flag_updated',
            $this->boolean()->defaultValue('false')
        );

        $query = new Query();
        $query->select([
            'id',
            'old_id',
            'type_tmc',
        ])->from('tmc.balance');

        foreach ($query->each(1) as $row) {
            $type_for_update = $this->getTypeForVisitServiceTmc($row['type_tmc']);
            $attrs = [
                ':new_id' => $row['old_id'], // Здесь наоборот - заменяем на old_id
                ':old_id' => $row['id'],
                ':type' => $type_for_update
            ];

            $this->execute(
                'UPDATE visit_service_tmc SET id_entity=:new_id, flag_updated = true 
                WHERE id_entity=:old_id AND entity_type=:type AND flag_updated = false',
                $attrs
            );
        }

        // Убираем отрицательный id - дабы потом не пересеклись с настоящими добавленными позже
        $this->execute("
UPDATE visit_service_tmc SET id_entity = id_entity*-1 WHERE id_entity < 0 AND entity_type
  IN ('balance_equipments', 'balance_vaccines', 'balance_exp_materials', 'balance_drugs')");

        $this->dropColumn(
            'visit_service_tmc',
            'flag_updated'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210315_165842_3296_visit_service_tmc_new_entity_ids cannot be reverted.\n";

        return false;
    }
    */
}
