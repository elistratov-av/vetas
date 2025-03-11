<?php

use app\commands\migrate\Migration;

/**
 * Class m201030_133409_add_column_is_priority_for_moderation_fot_table_found_pet_ads
 */
class m201030_133409_add_column_is_priority_for_moderation_fot_table_found_pet_ads extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'found_pet.ads',
            'is_priority_for_moderation',
            $this->boolean()
                ->comment('Приоритет для модерации')
        );

        $this->update(
            'found_pet.ads',
            [
                'is_priority_for_moderation' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'found_pet.ads',
            'is_priority_for_moderation'
        );
    }
}
