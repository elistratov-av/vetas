<?php

use app\commands\migrate\Migration;
use yii\db\Expression;
use yii\helpers\Console;

/**
 * Class m210320_164010_3296_new_balance_flow
 */
class m210320_164010_3296_new_balance_flow extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tmc.balance_flow', [
            'id' => $this->bigPrimaryKey(),
            'id_tmc_balance' => $this->integer()->notNull(),
            'flow_type' => $this->char(1)->notNull(), // I, D, H
            'flow_action' => $this->string(16)->notNull(),

            'count' => $this->decimal(11, 2),
            'id_visit_service' => $this->integer(),
            'id_balance_action' => $this->integer(),

            'created_by' => $this->integer(),
            'created_at' => $this
                ->timestamp(0)
                ->defaultValue(new Expression("now()::timestamp without time zone")),
        ]);

        $this->addCommentOnTable(
            'tmc.balance_flow',
            'История пополнений/списаний балансовых ТМЦ. Используется для подсчета текущего баланса'
        );

        $this->addCommentOnColumn(
            'tmc.balance_flow', 'id_tmc_balance', 'Ссылка на балансовую единицу'
        );
        $this->addCommentOnColumn(
            'tmc.balance_flow', 'flow_type', 'Тип операции (приход/расход/холд)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_flow', 'flow_action', 'Действие'
        );
        $this->addCommentOnColumn(
            'tmc.balance_flow', 'count', 'Кол-во'
        );
        $this->addCommentOnColumn(
            'tmc.balance_flow', 'id_visit_service', 'Ссылка на данные приема'
        );
        $this->addCommentOnColumn(
            'tmc.balance_flow', 'id_balance_action', 'Ссылка на действия с ТМЦ'
        );

        $this->addCommentOnColumn('tmc.balance_flow', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('tmc.balance_flow', 'created_by', 'Автор добавления');


        $this->createIndex( // для триггера пересчета
            'idx_tmc_balance_flow_id_tmc_balance-flow_type',
            'tmc.balance_flow',
            ['id_tmc_balance', 'flow_type']
        );


        $this->addForeignKey(
            'fk_tmc_balance_flow-visits_gov_services',
            'tmc.balance_flow',
            'id_visit_service',
            'visits_gov_services',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_tmc_balance_flow-tmc_balance',
            'tmc.balance_flow',
            'id_tmc_balance',
            'tmc.balance',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_tmc_balance_flow_tmc_balance_action',
            'tmc.balance_flow',
            'id_balance_action',
            'tmc.balance_action',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        // переносим
        $this->copyDataFromOldTable();
    }


    protected function copyDataFromOldTable()
    {
        /**
         * В tmc.balance есть поле old_id
         * В паре с type_tmc оно соответствует старым ключам
         *
         * tmc.balance (old_id, type_tmc) => public.balance_flow (id_balance_tmc_type + balance_tmc_type)
         *
         * Тк в старой реализации теоретически была возможность удалить балансовые препараты,
         * перенесем только записи, которые еще есть в объединенном балансовом справочнике
         */

        $sql = "
        INSERT INTO tmc.balance_flow 
        (
            id_tmc_balance,
            flow_type,
            flow_action,
            count,
            id_visit_service,
            created_by,
            created_at
        ) 
        SELECT 
               tb.id AS id_tmc_balance,
                CASE 
                    WHEN pbf.flow_type = 'expense' THEN 'D'
                    WHEN pbf.flow_type = 'income' THEN 'I'
                END AS flow_type,
               pbf.flow_type AS flow_action,
               pbf.count,
               pbf.id_visitservice,
               pbf.created_by,
               pbf.created_at
        FROM 
             tmc.balance tb
        INNER JOIN 
             public.balance_flow pbf ON pbf.id_balance_tmc_type = tb.old_id AND pbf.balance_tmc_type = tb.type_tmc
        ";

        $this->execute($sql);

        $query_old = (new \yii\db\Query())->from('public.balance_flow');
        $query_new = (new \yii\db\Query())->from('tmc.balance_flow');

        Console::output(PHP_EOL.PHP_EOL);
        Console::output('Count in public.balance_flow: ' . $query_old->count());
        Console::output('Count in tmc.balance_flow: ' . $query_new->count());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('tmc.balance_flow');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210320_164010_3296_new_balance_flow cannot be reverted.\n";

        return false;
    }
    */
}
