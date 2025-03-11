<?php

use app\commands\migrate\Migration;

/**
 * Class m181121_071332_add_id_fias_address_to_organizations_table
 */
class m181121_071332_add_id_fias_address_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'id_fias_address', $this->integer());
        $this->addCommentOnColumn('organizations', 'id_fias_address', 'Ссылка на адрес ФИАС, таблица fias_address');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'id_fias_address');
    }

}
