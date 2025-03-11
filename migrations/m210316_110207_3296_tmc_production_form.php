<?php

use app\commands\migrate\Migration;
use yii\db\Query;
/**
 * Class m210316_110207_3296_tmc_production_form
 */
class m210316_110207_3296_tmc_production_form extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tmc.production_form', [
            'id' => $this->primaryKey(),
            'id_tmc' => $this->integer()->notNull(),
            'type_tmc' => 'tmc.tmc_class_list NOT NULL',

            'name' => $this->string(255)->notNull(),
            'volume' => $this->decimal(8, 3),
            'is_utilize' => $this->boolean()->notNull()->defaultValue('false'),
            'is_deleted' => $this->boolean()->notNull()->defaultValue('false'),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable('tmc.production_form', 'Формы выпуска');

        $this->addCommentOnColumn('tmc.production_form', 'id', 'id');
        $this->addCommentOnColumn('tmc.production_form', 'type_tmc', 'Тип тмц');
        $this->addCommentOnColumn('tmc.production_form', 'name', 'Наименование');
        $this->addCommentOnColumn('tmc.production_form', 'volume', 'Объем');
        $this->addCommentOnColumn('tmc.production_form', 'is_utilize', 'Автоматически утилизировать остаток после использования');
        $this->addCommentOnColumn('tmc.production_form', 'is_deleted', 'Удалено');

        $this->addCommentOnColumn('tmc.production_form', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('tmc.production_form', 'created_by', 'Автор добавления');
        $this->addCommentOnColumn('tmc.production_form', 'updated_at', 'Дата изменения');
        $this->addCommentOnColumn('tmc.production_form', 'updated_by', 'Автор последнего изменения');

        /*
         *  Ограничения
         */
        $this->execute("
ALTER TABLE tmc.production_form 
  ADD CONSTRAINT volume_check 
    CHECK (volume > 0)
");

        $this->execute("
ALTER TABLE tmc.production_form 
  ADD CONSTRAINT type_tmc_check 
    CHECK (
            type_tmc  IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list)
        )");

        /*
         * Индекс (потребуется, что бы навесить FK с балансов)
         */
        $this->createIndex(
            'tmc_production_form_id_id_tmc_type_tmc_uniq_idx',
            'tmc.production_form',
            ['id', 'id_tmc', 'type_tmc'],
            true
        );

        /*
         * Генерируем "дефолтные" формы выпуска
         * и привязываем к балансу
         */
        $this->generateDefaultProductionFormsForBalances();

        /*
         * К таблице ТМЦ составной ключ (M:1)
         */
        $this->addForeignKey(
            'fk-tmc_tmc-tmc_production_form',
            'tmc.production_form',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type']
        );

        /*
         * На таблицу баланса вешаем составной ключ (1:1)
         */
        $this->addForeignKey(
            'fk-tmc_balance-tmc_production_form',
            'tmc.balance',
            ['id_production_form', 'id_tmc', 'type_tmc'],
            'tmc.production_form',
            ['id', 'id_tmc', 'type_tmc']
        );

        /*
         * id_production_form NOT NULL для drug|vaccine
         */
        $this->execute("
ALTER TABLE tmc.balance 
  ADD CONSTRAINT production_form_check 
    CHECK (
        type_tmc NOT IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list)
            OR 
        (type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list) AND  id_production_form IS NOT NULL)
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
            ->where([
                'OR',
                ['tb.type_tmc' => 'vaccine'],
                ['tb.type_tmc' => 'drug'],
            ])
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
        $this->execute('ALTER TABLE tmc.balance DROP CONSTRAINT production_form_check;');
        $this->dropForeignKey(
            'fk-tmc_balance-tmc_production_form',
            'tmc.balance'
        );
        $this->dropTable('tmc.production_form');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210316_110207_3296_tmc_production_form cannot be reverted.\n";

        return false;
    }
    */
}
