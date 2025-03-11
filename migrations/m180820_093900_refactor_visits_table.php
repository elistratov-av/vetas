<?php

use yii\db\Migration;

/**
 * Class m180820_093900_refactor_visits_table
 */
class m180820_093900_refactor_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Удаляем старые значения
        $this->dropColumn('visits','start_dttm');
        $this->dropColumn('visits','duration');

        $this->execute('ALTER TABLE visits DROP COLUMN IF EXISTS channel');
        $this->addColumn('visits','channel',$this->integer());

        // Проставляем всем текущим LIVE_QUEUE
        $sql = '
UPDATE
	visits
SET
	channel = subquery.id
FROM (
	SELECT 
		id
	FROM 
		shift_type
	WHERE
		shift_type.type = \'LIVE_QUEUE\'
    LIMIT 1
) AS subquery
';

        $this->execute($sql);

        // FK
        $this->addForeignKey('fk-visits_channel_shift_type_id','visits','channel','shift_type','id');
        $this->execute('ALTER TABLE visits ALTER COLUMN channel SET NOT NULL;');

        // Описание
        $this->addCommentOnColumn('visits','channel','Канал записи (тип смены)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180820_093900_refactor_visits_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180820_093900_refactor_visits_table cannot be reverted.\n";

        return false;
    }
    */
}
