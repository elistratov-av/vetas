<?php

use app\commands\migrate\Migration;

use yii\db\Query;
/**
 * Class m210321_174453_3296_production_form_for_exp_materials
 */
class m210321_174453_3296_production_form_for_exp_materials extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->execute("
ALTER TABLE tmc.production_form 
  DROP CONSTRAINT type_tmc_check");

        $this->execute("
ALTER TABLE tmc.production_form 
  ADD CONSTRAINT type_tmc_check 
    CHECK (
            type_tmc  IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
        )");


        $this->execute("
ALTER TABLE tmc.balance 
  DROP CONSTRAINT production_form_check");

        $this->generateDefaultProductionFormsForBalances();

        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT production_form_check 
    CHECK (
        (type_tmc NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list) AND  id_production_form IS NULL)
            OR 
        (type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list) AND  id_production_form IS NOT NULL)
        )");
    }

    /**
     * Балансам надо проставить значения и повесить NOT NULL
     * Генерируем для каждого свой
     * И привязываем к балансу
     */
    protected function generateDefaultProductionFormsForBalances()
    {
        $query = new Query();
        $query->select([
            'tb.id as id_balance',
            'tb.id_tmc',
            'tb.type_tmc',
            'measures.name AS measure_name'
        ])
            ->from('tmc.balance tb')
            ->leftJoin('tmc.tmc tt','tb.id_tmc = tt.id AND tb.type_tmc = tt.type')
            ->leftJoin('measures', 'tt.id_measure = measures.id')
            ->where(['tb.type_tmc' => 'exp_material'])
            ->orderBy('tb.id ASC')
        ;

        foreach ($query->each(1) as $row) {
            /*
             * Создаем новую (сразу удаленную, что бы не использовали больше)
             */
            if (empty($row['measure_name'])){
                $name = '[Не указано. Принято за единицу]';
            }else{
                $name =  '[Не указано. Принято за 1 '. $row['measure_name'] .']';
            }
            $this->db->createCommand()->insert('tmc.production_form', [
                'id_tmc' => $row['id_tmc'],
                'type_tmc' => $row['type_tmc'],
                'name' => $name,
                'volume' => 1,
                'is_utilize' => false,
                'is_deleted' => true,
            ])->execute();

            $production_form_id = $this->db->getLastInsertID();

            /*
             * Привязываем к балансу
             */
            $this->update('tmc.balance',[
                'id_production_form' => $production_form_id
            ],[
                'id' => $row['id_balance']
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("
ALTER TABLE tmc.production_form 
  DROP CONSTRAINT type_tmc_check");

        $this->execute("
ALTER TABLE tmc.balance 
  DROP CONSTRAINT production_form_check");


        $this->execute("UPDATE tmc.balance SET id_production_form = NULL WHERE type_tmc = 'exp_material'::tmc.tmc_class_list");
        $this->execute("DELETE FROM  tmc.production_form  WHERE type_tmc = 'exp_material'::tmc.tmc_class_list");



        $this->execute("
ALTER TABLE tmc.production_form 
  ADD CONSTRAINT type_tmc_check 
    CHECK (
            type_tmc  IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list)
        )");

        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT production_form_check 
    CHECK (
        type_tmc NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list)
            OR 
        (type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list) AND  id_production_form IS NOT NULL)
        )");



    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210321_174453_3296_production_form_for_exp_materials cannot be reverted.\n";

        return false;
    }
    */
}
