<?php

use app\commands\migrate\Migration;
use app\models\db\DocumentTypes as Model;

/**
 * Class m221219_135238_delete_type_contract
 */
class m221219_135238_delete_type_contract extends Migration
{
    public function safeUp()
    {
        $this->execute("
        WITH contracts AS (SELECT id FROM document_types WHERE type = '" . Model::TYPE_CONTRACT . "' AND \"group\" = '" . Model::GROUP_DEPARTURE_REASON_RETURNED_TO_NEW_OWNER . "'),
        opeka_contracts AS (SELECT id FROM document_types WHERE type = '" . Model::TYPE_CONTRACT_GUARDIANSHIP . "')
        UPDATE documents SET type_id = (SELECT id FROM opeka_contracts)
            WHERE type_id = (SELECT id FROM contracts)");
        $this->execute("DELETE FROM  document_types WHERE type = '" . Model::TYPE_CONTRACT . "'");
    }

    public function safeDown()
    {
        echo "m221219_135238_delete_type_contract cannot be reverted.\n";

        return false;
    }

    
}
