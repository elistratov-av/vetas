<?php

use yii\db\Migration;

/**
 * Class m180822_102110_create_specialist_specializations_list_view
 */
class m180822_102110_create_specialist_specializations_list_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.specialist_specializations_list AS 
 SELECT personal_specializations.id_specialist,
    array_agg(personal_specializations.id_specialization) AS specializations_list
   FROM personal_specializations
  GROUP BY personal_specializations.id_specialist;

SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.specialist_specializations_list IS 'Представление для вывода списка специализаций специалистов';
");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.specialist_specializations_list");
    }

}
