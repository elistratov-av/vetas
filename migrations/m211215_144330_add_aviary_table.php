<?php

use app\commands\migrate\Migration;
use app\models\db\Aviary;
use app\migrations\traits\BlameableBehaviorTrait;
use app\migrations\traits\TimestampBehaviorTrait;
use app\models\db\Organizations;

/**
 * Class m211215_144330_add_aviary_table
 */
class m211215_144330_add_aviary_table extends Migration
{
    // use BlameableBehaviorTrait, TimestampBehaviorTrait;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablename = Aviary::tableName();

        // удаляем таблицу от миграции m211209_135203_add_aviary_table
        if ($this->db->getTableSchema($tablename, true) !== null) {
            $this->dropTable($tablename);
        }

        $this->createTable($tablename, [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'organization_id' => $this->integer()->notNull(),
        ]);

        // $this->addBlameableColumns($tablename);
        // $this->addTimestampColumns($tablename);

        $this->addForeignKey(
            'fk-aviary-organization_id',
            $tablename,
            'organization_id',
            Organizations::tableName(),
            'id',
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Aviary::tableName());
    }
}
