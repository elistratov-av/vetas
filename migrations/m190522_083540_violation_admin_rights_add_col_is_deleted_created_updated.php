<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m190522_083540_violation_admin_rights_add_col_is_deleted_created_updated
 */
class m190522_083540_violation_admin_rights_add_col_is_deleted_created_updated extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'violation_admin_rights',
            'is_deleted',
            $this->boolean()->notNull()->defaultValue('FALSE')
        );

        $this->addCommentOnColumn('violation_admin_rights', 'is_deleted', 'Флаг: удалено');

        $this->addColumn('violation_admin_rights', 'created_at', $this->timestamp(0));
        $this->addColumn('violation_admin_rights', 'updated_at', $this->timestamp(0));
        $this->addColumn('violation_admin_rights', 'created_by', $this->integer());
        $this->addColumn('violation_admin_rights', 'updated_by', $this->integer());

        /*
         * Снимаем уникальность
         */
        $results = (new Query())
            ->select('constraint_name')
            ->from('information_schema.table_constraints')
            ->where([
                'AND',
                ['table_schema' => 'public'],
                ['table_name' => 'violation_admin_rights'],
                ['constraint_type' => 'UNIQUE'],
            ])->column();

        foreach ($results as $result){
            $this->execute('ALTER TABLE violation_admin_rights DROP CONSTRAINT ' . $result);
        }

        /*
         * Уникальность для неудаленных
         */
        $create_partial_uniq_violation_admin_rights_short_name_index = '
        CREATE UNIQUE INDEX partial_uniq_violation_admin_rights_short_name_index
        ON violation_admin_rights (short_name) 
        WHERE is_deleted = FALSE';

        $this->execute($create_partial_uniq_violation_admin_rights_short_name_index);

        $create_partial_uniq_violation_admin_rights_full_name_index = '
        CREATE UNIQUE INDEX partial_uniq_violation_admin_rights_full_name_index
        ON violation_admin_rights (full_name) 
        WHERE is_deleted = FALSE';

        $this->execute($create_partial_uniq_violation_admin_rights_full_name_index);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'violation_admin_rights',
            'is_deleted'
        );

        $this->dropColumn('violation_admin_rights', 'created_at');
        $this->dropColumn('violation_admin_rights', 'updated_at');
        $this->dropColumn('violation_admin_rights', 'created_by');
        $this->dropColumn('violation_admin_rights', 'updated_by');

        /*
         * Удаляем частичный индекс
         */
        //$this->dropIndex('partial_uniq_violation_admin_rights_short_name_index', 'violation_admin_rights');
        //$this->dropIndex('partial_uniq_violation_admin_rights_full_name_index', 'violation_admin_rights');

        /*
         * Восстанавливаем уникальность
         */
        $this->execute('ALTER TABLE violation_admin_rights ADD UNIQUE (short_name)');
        $this->execute('ALTER TABLE violation_admin_rights ADD UNIQUE (full_name)');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190522_083540_violation_admin_rights_add_col_is_deleted_created_updated cannot be reverted.\n";

        return false;
    }
    */
}
