<?php

use app\commands\migrate\Migration;

/**
 * Class m210326_194315_3296_create_new_table_visit_service_tmc
 */
class m210326_194315_3296_create_new_table_visit_service_tmc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_service_tmc', [
            'id' => $this->bigPrimaryKey(),
            'id_visit' => $this->integer()->notNull(),
            'id_visits_gov_service' => $this->integer()->notNull(),

            'id_tmc' => $this->integer()->notNull(),
            'type_tmc' => 'tmc.tmc_class_list NOT NULL',

            'id_balance_tmc' => $this->integer(),

            'id_dosage' => $this->integer(),

            'count_selected' => $this->decimal(11, 2),
            'count' => $this->decimal(11, 2),

            'price' => $this->decimal(8, 2),

            'write_off_pack_form' => $this->boolean()->notNull()->defaultValue(false),

            'apply_discount' => $this->boolean()->notNull()->defaultValue(false),
            'apply_night_discount' => $this->boolean()->notNull()->defaultValue(false),

            'created_at' => $this->timestamp(0),
            'created_by' => $this->integer(),
            'updated_at' => $this->timestamp(0),
            'updated_by' => $this->integer(),
        ]);

        $this->addCommentOnTable(
            'visit_service_tmc',
            'Использованные в услуге ТМЦ'
        );


        $this->addCommentOnColumn('visit_service_tmc', 'id_visit',
            'ID приема'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'id_visits_gov_service',
            'ID услуги в приеме'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'id_tmc',
            'Ссылка на ТМЦ (если внебалансовое)'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'type_tmc',
            'Ссылка на ТМЦ (если внебалансовое)'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'id_balance_tmc',
            'Ссылка на балансовый ТМЦ'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'id_dosage',
            'ID дозировки (если выбрано списание в дозировке)'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'count_selected',
            'Значение кол-во введенное пользователем'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'count',
            'Кол-во высчитанное в стандартных единицах измерения данного ТМЦ'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'price',
            'Высчитанная цена по кол-ву (без скидок или наценок)'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'write_off_pack_form',
            'Флаг: Списать целиком упаковку (или иную форму производства)'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'apply_discount',
            'Флаг: применять скидку при выставлении счета'
        );
        $this->addCommentOnColumn('visit_service_tmc', 'apply_night_discount',
            'Флаг: применять ночной рейт при выставлении счета'
        );

        $this->addCommentOnColumn('visit_service_tmc', 'created_at', 'Дата создания');
        $this->addCommentOnColumn('visit_service_tmc', 'created_by', 'Автор добавления');
        $this->addCommentOnColumn('visit_service_tmc', 'updated_at', 'Дата изменения');
        $this->addCommentOnColumn('visit_service_tmc', 'updated_by', 'Автор последнего изменения');

        // VISITS
        $this->addForeignKey(
            'fk-visit_service_tmc-visit',
            'visit_service_tmc',
            'id_visit',
            'visits',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // VISITS GOV SERVICES
        $this->addForeignKey(
            'fk-visit_service_tmc-visits_gov_service',
            'visit_service_tmc',
            'id_visits_gov_service',
            'visits_gov_services',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // TMC
        $this->addForeignKey(
            'fk-visit_service_tmc-id_visit-tmc_tmc',
            'visit_service_tmc',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'NO ACTION',
            'CASCADE'
        );

        $this->createIndex(
            'uniq_balance_id_id_tmc_type_tmc',
            'tmc.balance',
            ['id', 'id_tmc', 'type_tmc'],
            true
        );

        // TMC BALANCE
        $this->addForeignKey(
            'fk-visit_service_tmc-id_visit-tmc_balance',
            'visit_service_tmc',
            ['id_balance_tmc', 'id_tmc', 'type_tmc'],
            'tmc.balance',
            ['id', 'id_tmc', 'type_tmc'],
            'NO ACTION',
            'CASCADE'
        );

        // DOSAGE
        $this->addForeignKey(
            'fk-visit_service_tmc-id_visit-tmc_dosages',
            'visit_service_tmc',
            'id_dosage',
            'tmc.dosages',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->createIndex(
            'idx_visit_service_tmc_id_visit_id_visits_gov_service',
            'visit_service_tmc',
            ['id_visit', 'id_visits_gov_service'],
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('visit_service_tmc');
        $this->dropIndex(
            'tmc.uniq_balance_id_id_tmc_type_tmc',
            'tmc.balance'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210326_194315_3296_create_new_table_visit_service_tmc cannot be reverted.\n";

        return false;
    }
    */
}
