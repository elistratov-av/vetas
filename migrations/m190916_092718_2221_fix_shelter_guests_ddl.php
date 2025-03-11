<?php

use app\commands\migrate\Migration;

/**
 * Class m190916_092718_2221_fix_shelter_guests_ddl
 */
class m190916_092718_2221_fix_shelter_guests_ddl extends Migration
{
    private $tableName = 'shelter_guests';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk_' . $this->tableName . '_id_pet', '{{%' . $this->tableName . '}}');
        $this->dropForeignKey('fk_' . $this->tableName . '_id_organization', '{{%' . $this->tableName . '}}');

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_pet',
            '{{%' . $this->tableName . '}}',
            'id_pet',
            'pets',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk_' . $this->tableName . '_id_organization',
            '{{%' . $this->tableName . '}}',
            'id_organization',
            'organizations',
            'id',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190916_092718_2221_fix_shelter_guests_ddl cannot be reverted.\n";

        return false;
    }
}
