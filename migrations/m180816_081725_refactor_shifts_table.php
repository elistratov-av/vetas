<?php

use yii\db\Migration;

/**
 * Class m180816_081725_refactor_shifts_table
 */
class m180816_081725_refactor_shifts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE public.shifts ADD COLUMN shift_type_id integer;");

        /*
         *  Тк мы не знаем что к какому типу отнести существующие элементы с idle = true,
         *  проставляем им тип - выходной
         */
        $sql = '
UPDATE
	shifts
SET
	shift_type_id = subquery.id
FROM (
	SELECT 
		id
	FROM 
		shift_type
	WHERE
		shift_type.type = \'HOLIDAY\'
    LIMIT 1
) AS subquery
WHERE
	shifts.idle = TRUE;';
        $this->execute($sql);

        /**
         * Оставшимся проставляем - рабочее время
         */
        $sql_workday = '
UPDATE
	shifts
SET
	shift_type_id = subquery.id
FROM (
	SELECT 
		id
	FROM 
		shift_type
	WHERE
		shift_type.type = \'WORKDAY\'
    LIMIT 1
) AS subquery
WHERE
	shifts.idle = FALSE
	OR 
	shifts.idle IS NULL;';

    $this->execute($sql_workday);

    /*
     *  Для того что бы система работала корректно - еще дублируем рабочий день как запись из живой очереди
     * (тк опять же не знаем куда его отнести)
     */
    $sql_live = '
INSERT INTO shifts (
	name, from_time, idle, duration, id_organization, 
	created_by, updated_by, created_at, updated_at,colour, shift_type_id)
SELECT
	shifts.name || \' живая очередь\' AS name,
	shifts.from_time,
	shifts.idle,
	shifts.duration,
	shifts.id_organization,
	shifts.created_by,
	shifts.updated_by,
	shifts.created_at,
	shifts.updated_at,
	shifts.colour,
	(SELECT id FROM shift_type WHERE type = \'LIVE_QUEUE\' LIMIT 1) AS shift_type_id

FROM 
	shifts
WHERE
	shifts.idle = FALSE
	OR 
	shifts.idle IS NULL
';

        $this->execute($sql_live);

        // Индексы, ключи
        $this->createIndex('uniq-shifts_name_id_organization','shifts',['name', 'id_organization'],TRUE);
        $this->addForeignKey('fk-shifts_id_shift_type','shifts','shift_type_id','shift_type','id');

        // Not null
        $this->execute('ALTER TABLE public.shifts ALTER COLUMN shift_type_id SET NOT NULL;');

        //DEL OLD
        $this->execute("ALTER TABLE public.shifts DROP COLUMN idle; ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180816_081725_refactor_shifts_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180816_081725_refactor_shifts_table cannot be reverted.\n";

        return false;
    }
    */
}
