<?php

use app\commands\migrate\Migration;

/**
 * Class m210320_110840_3296_balance_actions
 */
class m210320_110840_3296_balance_actions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
//        // В баланс нужно поле кол-во в формах выпуска
//        $this->addColumn(
//            'tmc.balance',
//            'count_in_production_forms',
//            $this->decimal(11, 2),
//        );


        $this->createTable('tmc.balance_action',[
            'id' => $this->primaryKey(),

            'num' => $this->string(256),
            'action' => $this->string(32), // передача на баланс, утилизация, списание, запрос на выдачу
            'status' => $this->string(1)->defaultValue('N')->notNull(),

            // Кто и когда инициировал
            'initiator_date' => $this->date(),
            'initiator_id_specialist' => $this->integer()->notNull(),
            'initiator_comment' => $this->text(),

            // Кто и когда согласился (для некоторых действий)
            'acceptor_date' => $this->date(),
            'acceptor_id_specialist' => $this->date(),
            'acceptor_comment' => $this->text(),

            // С баланса какой организации (или спеца)
            'from_id_organization' => $this->integer()->notNull(),
            'from_id_specialist' => $this->integer(),

            // На баланс какой организации (или спеца) (для некоторых действий)
            'to_id_organization' => $this->integer(),
            'to_id_specialist' => $this->integer(),


            'created_at' => $this->timestamp(0),
            'created_by' => $this->integer(),
            'updated_at' => $this->timestamp(0),
            'updated_by' => $this->integer(),
        ]);

        $this->addCommentOnTable('tmc.balance_action', 'Действия над ТМЦ (передача, утилизация и тд)');

        $this->addCommentOnColumn(
            'tmc.balance_action', 'num', 'Номер (для документов)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'action', 'Действие (передача на баланс, утилизация, списание, запрос на выдачу)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'status', 'Действие (передача на баланс, утилизация, списание, запрос на выдачу)'
        );

        // Кто и когда инициировал, коммент
        $this->addCommentOnColumn(
            'tmc.balance_action', 'initiator_date', 'Когда создал'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'initiator_id_specialist', 'Кто создал'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'initiator_comment', 'Комментарий создателя'
        );

        // Кто и когда согласился (для некоторых действий)
        $this->addCommentOnColumn(
            'tmc.balance_action', 'acceptor_date', 'Когда согласился (отклонил)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'acceptor_id_specialist', 'Кто согласился (отклонил)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'acceptor_comment', 'Комментарий принявшего (отклонившего)'
        );

        // С баланса какой организации (или спеца)
        $this->addCommentOnColumn(
            'tmc.balance_action', 'from_id_organization', 'С баланса какой организации'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'from_id_organization', 'С баланса какого спеца'
        );

        // С баланса какой организации (или спеца)
        $this->addCommentOnColumn(
            'tmc.balance_action', 'to_id_organization', 'На баланс какой организации'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action', 'to_id_specialist', 'На баланс какого спеца'
        );

        $this->addCommentOnColumn('tmc.balance', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('tmc.balance', 'created_by', 'Автор добавления');
        $this->addCommentOnColumn('tmc.balance', 'updated_at', 'Дата изменения');
        $this->addCommentOnColumn('tmc.balance', 'updated_by', 'Автор последнего изменения');


        $this->createTable('tmc.balance_action_tmc_list', [
            'id' => $this->primaryKey(),
            'id_action' => $this->integer()->notNull(),
            'id_balance_tmc' => $this->integer()->notNull(),
            'id_organization' => $this->integer()->notNull(),
            'id_specialist' => $this->integer(),
            'count' => $this->decimal(11, 2),
            'count_in_production_forms' => $this->decimal(11, 2), // справочно
        ]);
        $this->addCommentOnTable(
            'tmc.balance_action_tmc_list',
            'Список ТМЦ над которым производится действие (передача, утилизация и тд)'
        );

        $this->addCommentOnColumn(
            'tmc.balance_action_tmc_list', 'id_action',
            'ID действия'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action_tmc_list', 'id_balance_tmc',
            'ID балансового ТМЦ'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action_tmc_list', 'id_organization',
            'ID организации балансового ТМЦ (нужен для ключа)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action_tmc_list', 'id_specialist',
            'ID специалиста балансового ТМЦ (нужен для ключа)'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action_tmc_list', 'count',
            'Количество в единицах измерения'
        );
        $this->addCommentOnColumn(
            'tmc.balance_action_tmc_list', 'count_in_production_forms',
            'Количество в формах производства (справочно)'
        );


        /**
         * в одну заявку нельзя скидывать переводы с разных балансов (например с двух спецов)
         * поэтому составные ключи
         *  tmc.balance_action(id, from_id_organization, from_id_specialist)
         *   =
         *  tmc.balance_action_tmc_list (id_action, id_organization, id_specialist)
         *  И
         *  tmc.balance_action_tmc_list (id_balance_tmc, id_organization, id_specialist)
         *  =
         *  tmc.balance (id, id_organization, id_specialist)
         */
        $this->createIndex(
            'unq_tmc_balance_id-id_organization-id_specialist',
            'tmc.balance',
            ['id', 'id_organization', 'id_specialist'],
            true
        );

        $this->createIndex(
            'unq_tmc_balance_action_id-from_id_organization-from_id_specialist',
            'tmc.balance_action',
            ['id', 'from_id_organization', 'from_id_specialist'],
            true
        );

        $this->addForeignKey(
            'fk_tmc_balance_action_tmc_list-tmc_balance_action',
            'tmc.balance_action_tmc_list',
            ['id_action', 'id_organization', 'id_specialist'],
            'tmc.balance_action',
            ['id', 'from_id_organization', 'from_id_specialist']
        );

        $this->addForeignKey(
            'fk_tmc_balance_action_tmc_list-tmc_balance',
            'tmc.balance_action_tmc_list',
            ['id_balance_tmc', 'id_organization', 'id_specialist'],
            'tmc.balance',
            ['id', 'id_organization', 'id_specialist']
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropTable('tmc.balance_action_tmc_list');
        $this->dropTable('tmc.balance_action');

        $this->execute('DROP INDEX "tmc"."unq_tmc_balance_id-id_organization-id_specialist"');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210320_110840_3296_balance_actions cannot be reverted.\n";

        return false;
    }
    */
}
