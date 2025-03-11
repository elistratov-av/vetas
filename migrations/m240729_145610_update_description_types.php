<?php

use app\commands\migrate\Migration;
use app\models\db\DescriptionTypes;

/**
 * Class m240729_145610_update_description_types
 */
class m240729_145610_update_description_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $newDescriptionType = new DescriptionTypes();
        $newDescriptionType->name = 'Результаты лабораторных исследований (ссылка)';
        $newDescriptionType->tech_name = 'LABORATORY_FILE_LINK';
        $newDescriptionType->entity_type = 'visit';
        $newDescriptionType->sort_by = 1;
        
        if (!$newDescriptionType->save()) {
            echo "Error saving new description type.\n";
            return false;
        }

        echo "New description type added successfully.\n";
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $descriptionType = DescriptionTypes::findOne(['tech_name' => 'LABORATORY_FILE_LINK']);
        if ($descriptionType === null) {
            echo "Description type not found.\n";
            return false;
        }
        if (!$descriptionType->delete()) {
            echo "Error deleting description type.\n";
            return false;
        }
        echo "Description type removed successfully.\n";
        return true;
    }
}
