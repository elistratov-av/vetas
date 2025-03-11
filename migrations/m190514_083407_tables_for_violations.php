<?php

use app\commands\migrate\Migration;

/**
 * Class m190514_083407_tables_for_violations
 */
class m190514_083407_tables_for_violations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * violation_type
         */
        $this->createTable('violation_type', [
            'id_type' => $this->primaryKey(),
            'name' => $this->text()->notNull()
        ]);

        $this->addCommentOnTable(
            'violation_type',
            'Типы нарушений'
        );

        /*
         * violation_admin_rights
         */
        $this->createTable('violation_admin_rights', [
            'id_ARV' => $this->primaryKey(),
            'short_name' => $this->text()->notNull()->unique(),
            'full_name' => $this->text()->unique(),
            'description' => $this->text(),
        ]);

        $this->addCommentOnTable(
            'violation_admin_rights',
            'Справочник АПН'
        );

        /*
         * violation_cancellation
         */
        $this->createTable('violation_cancellation', [
            'id_cancellation' => $this->primaryKey(),
            'description' => $this->text()->unique(),
        ]);

        $this->addCommentOnTable(
            'violation_cancellation',
            'Справочник причины отмены работы над нарушением'
        );

        /*
         * violation
         */
        $this->createTable('violation', [
            'id_violation' => $this->primaryKey(),
            'state' => $this->char(1)->notNull(),
            'id_owner' => $this->integer()->notNull(),
            'id_pet' => $this->integer()->notNull(),
            'id_type' => $this->integer()->notNull(),
            'id_disease' => $this->integer()->notNull(),
            'id_ARV' => $this->integer(),
            'id_veterinarian' => $this->integer(),
            'id_visit' => $this->integer(),
            'id_cancellation' => $this->integer(),
            'date_violation' => $this->timestamp(0)->notNull(),
            'date_plan' => $this->date(),
            'rejection_reason' => $this->text(),
            'comment' => $this->text(),
            'cancellation_details' => $this->text(),
        ]);

        $this->addForeignKey(
            'fk-violation-id_owner',
            'violation',
            'id_owner',
            'pet_owners',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_pet',
            'violation',
            'id_pet',
            'pets',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_type',
            'violation',
            'id_type',
            'violation_type',
            'id_type',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_disease',
            'violation',
            'id_type',
            'diseases',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_ARV',
            'violation',
            'id_ARV',
            'violation_admin_rights',
            'id_ARV',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_veterinarian',
            'violation',
            'id_veterinarian',
            'users',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_visit',
            'violation',
            'id_visit',
            'visits',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-violation-id_cancellation',
            'violation',
            'id_cancellation',
            'violation_cancellation',
            'id_cancellation',
            'NO ACTION',
            'CASCADE'
        );

        $this->addCommentOnTable(
            'violation',
            'Нарушения'
        );

        $this->addCommentOnColumn(
            'violation',
            'state',
            'Состояние нарушения: N - новое, W – в работе, C - отменено, F - завершено'
        );

        $this->addCommentOnColumn(
            'violation',
            'id_veterinarian',
            'Id из users; Заполняется при фиксации нарушения на приеме'
        );

        $this->addCommentOnColumn(
            'violation',
            'id_visit',
            'Заполняется при фиксации нарушения на приеме'
        );

        $this->addCommentOnColumn(
            'violation',
            'date_violation',
            'Дата фиксации нарушения'
        );

        $this->addCommentOnColumn(
            'violation',
            'date_plan',
            'Планируемая дата для ликвидации нарушения (вакцинации или идентификации животного)'
        );

        $this->addCommentOnColumn(
            'violation',
            'rejection_reason',
            'Заполняется при отказе от вакцинации, идентификаци'
        );

        $this->addCommentOnColumn(
            'violation',
            'comment',
            'Описание нарушения'
        );

        $this->addCommentOnColumn(
            'violation',
            'id_cancellation',
            'Причина отмены работы над нарушением'
        );

        $this->addCommentOnColumn(
            'violation',
            'cancellation_details',
            'Заполняется при варианте причины отмены работы над нарушениями «Иное»'
        );

        /*
         * violation_history
         */
        $this->createTable('violation_history', [
            'id_change' => $this->primaryKey(),
            'id_violation' => $this->integer()->notNull(),
            'id_inspector' => $this->integer(),
            'date' => $this->timestamp(0)->notNull(),
            'description' => $this->text()->notNull()
        ]);

        $this->addCommentOnTable(
            'violation_history',
            'История работы с нарушениями'
        );

        $this->addCommentOnColumn(
            'violation_history',
            'description',
            'Заполняется автоматически при совершении действия над нарушением'
        );

        $this->addForeignKey(
            'fk-violation_history-id_violation',
            'violation_history',
            'id_violation',
            'violation',
            'id_violation',
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /*
        * violation_history
        */
        $this->dropForeignKey(
            'fk-violation_history-id_violation',
            'violation_history'
        );

        $this->dropTable('violation_history');


        /*
         * violation
         */
        $this->dropForeignKey(
            'fk-violation-id_owner',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_pet',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_type',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_disease',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_ARV',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_veterinarian',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_visit',
            'violation'
        );

        $this->dropForeignKey(
            'fk-violation-id_cancellation',
            'violation'
        );

        $this->dropTable('violation');

        /*
         * violation_type
         */
        $this->dropTable('violation_type');

        /*
         * violation_admin_rights
         */
        $this->dropTable('violation_admin_rights');

        /*
         * violation_cancellation
         */
        $this->dropTable('violation_cancellation');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190514_083407_tables_for_violations cannot be reverted.\n";

        return false;
    }
    */
}
