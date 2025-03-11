<?php

use app\commands\migrate\Migration;

/**
 * Class m250120_092730_change_desc_shelter_view
 */
class m250120_092730_change_desc_shelter_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('auth_item', ['description' => 'Приюты (просмотр)'], ['name' => 'shelterViewer']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
