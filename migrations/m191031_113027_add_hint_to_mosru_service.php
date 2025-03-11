<?php

use app\commands\migrate\Migration;

/**
 * Class m191031_113027_add_hint_to_mosru_service
 */
class m191031_113027_add_hint_to_mosru_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $id_service = $this->db->createCommand(
            "select id from gov_services  where type = :type and name = :name", [
                ':type' => 'mosru',
                ':name' => 'УЗИ'
            ])
            ->queryScalar();
        $this->insert('mosru.services_hints', [
            'id_service' => $id_service,
            'text' => 'Перед проведением УЗИ для животного рекомендована голодная диета не менее 12 часов.'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191031_113027_add_hint_to_mosru_service cannot be reverted.\n";

        return false;
    }
}
