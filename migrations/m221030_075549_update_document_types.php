<?php

use app\commands\migrate\Migration;
use app\models\db\DocumentTypes;

class m221030_075549_update_document_types extends Migration
{
    public function safeUp()
    {
	DocumentTypes::updateTypes();
    }
}
