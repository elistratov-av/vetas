<?php

use app\commands\migrate\Migration;
use app\models\db\DocumentTypes;

class m220607_220677_rename_place_type_to_group_in_document_types extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('document_types', 'place_type', 'group');
        DocumentTypes::updateAll(['group' => DocumentTypes::GROUP_CARD], ['group' => 'DOC_PLACE_CARD']);
        DocumentTypes::updateAll(['group' => DocumentTypes::GROUP_NEW_OWNER], ['group' => 'DOC_PLACE_NEW_OWNER']);
        DocumentTypes::updateAll(['group' => DocumentTypes::GROUP_DEATH], ['group' => 'DOC_PLACE_DEATH']);
        DocumentTypes::updateAll(['group' => DocumentTypes::GROUP_RETURN], ['group' => 'DOC_PLACE_RETURN']);
    }

    public function safeDown()
    {
        $this->renameColumn('document_types', 'group', 'place_type');
    }
}
