<?php

use yii\db\Migration;

/**
 * Class m180821_125035_create_funtion_mosru_vailable_timerange
 */
class m180821_125035_create_funtion_mosru_vailable_timerange extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "
    CREATE OR REPLACE FUNCTION mosru_available_timerange(date_start date, date_end date) 
    RETURNS TABLE (mosru_available_timerange tsrange)  AS $$ 
BEGIN
    RETURN QUERY SELECT
    
	tsrange(a, b,  '[)'::text)
FROM (
SELECT
	generate_series(
		 ($1::text || ' 10:00:00')::timestamp without time zone, 
		 ($2::text || ' 10:00:00')::timestamp without time zone,
		'1 day'::interval
	) AS a,
	generate_series(
		($1::text || ' 12:00:00')::timestamp without time zone, 
		($2::text || ' 12:00:00')::timestamp without time zone,
		'1 day'::interval
	) AS b
UNION ALL
SELECT
	generate_series(
		($1::text || ' 14:00:00')::timestamp without time zone, 
		($2::text || ' 14:00:00')::timestamp without time zone,
		'1 day'::interval
	) AS a,
	generate_series(
		($1::text || ' 16:00:00')::timestamp without time zone, 
		($2::text || ' 16:00:00')::timestamp without time zone,
		'1 day'::interval
	) AS b
) AS generate_series_subquery;
END; $$ 
LANGUAGE plpgsql;
        
        ";

        $this->execute($sql);

        $this->execute("COMMENT ON FUNCTION mosru_available_timerange 
        IS 'Временная функция, возвращающая диапазоны времени доступные для записи с mosr_ru';");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP FUNCTION mosru_available_timerange ;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180821_125035_create_funtion_mosru_vailable_timerange cannot be reverted.\n";

        return false;
    }
    */
}
