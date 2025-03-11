<?php

use app\commands\migrate\Migration;

/**
 * Class m201229_075914_update_found_pets_ads_add_processed
 */
class m201229_075914_update_found_pets_ads_add_processed extends Migration
{
    private static $table = 'found_pet.ads';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::$table, 'processed', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::$table, 'processed');
    }
}
