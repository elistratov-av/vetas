<?php

use app\commands\migrate\Migration;

/**
 * Class m180828_051323_add_description_types
 */
class m180828_051323_add_description_types extends Migration
{
    private $tableName = 'description_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $names = [
            'Показания к применению',
            'Порядок применения',
            'Побочные эффекты',
            'Противопоказания к применению',
            'Особые указания и меры личной профилактики',
            'Условия хранения и сроки годности',
        ];

        foreach (['drug', 'vaccine'] as $type) {
            foreach ($names as $name) {
                Yii::$app->db
                    ->createCommand()
                    ->insert($this->tableName, ['name' => $name, 'entity_type' => $type])
                    ->execute();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180828_051323_add_description_types cannot be reverted.\n";

        return false;
    }
    */
}
