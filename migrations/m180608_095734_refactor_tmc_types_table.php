<?php

use yii\db\Migration;

/**
 * Class m180608_095734_refactor_tmc_types_table
 */
class m180608_095734_refactor_tmc_types_table extends Migration
{
    const TMC_TYPES_TABLE_NAME = 'tmc_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('TRUNCATE ' . self::TMC_TYPES_TABLE_NAME . ' CASCADE');
        $this->execute("CREATE TYPE tmc_class_list AS ENUM ('drug', 'equipment', 'vaccine')");
        $this->dropColumn(self::TMC_TYPES_TABLE_NAME, 'tmc_class');
        $this->addColumn(self::TMC_TYPES_TABLE_NAME, 'tmc_class', 'tmc_class_list NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(self::TMC_TYPES_TABLE_NAME, 'tmc_class', 'string');

        $this->execute("DROP TYPE IF EXISTS tmc_class_list");
    }

}
