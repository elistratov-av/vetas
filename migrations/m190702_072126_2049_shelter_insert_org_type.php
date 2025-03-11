<?php

use app\commands\migrate\Migration;
use app\models\db\OrgTypes;

/**
 * Class m190702_072126_2049_shelter_insert_org_type
 */
class m190702_072126_2049_shelter_insert_org_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $exist = OrgTypes::find()->where(['is_tech' => true])->exists();
        if ($exist) {
            return;
        }

        $this->db->createCommand()
            ->insert(OrgTypes::tableName(), [
                'name' => 'Приют',
                'description' => 'Приют',
                'is_tech' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ])
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db->createCommand()
            ->delete(OrgTypes::tableName(), ['is_tech' => true])
            ->execute();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190702_072126_2049_shelter_insert_org_type cannot be reverted.\n";

        return false;
    }
    */
}
