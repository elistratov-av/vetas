<?php

use app\commands\migrate\Migration;

/**
 * Class m231013_040656_add_not_selected_breed
 */
class m231013_040656_add_not_selected_breed extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("insert into breeds(name,description,species_id,created_by,updated_by,created_at,updated_at,sort_by)
values ('Не указана','Опция для явного выбора отсутствия выбора',null,null,null,now(),now(),1)");
        $id = Yii::$app->db->getLastInsertID();
        $this->execute("update pets set id_breed = ".$id." where id_breed is null");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("update pets set id_breed = null from breeds where pets.id_breed = breeds.id and breeds.name = 'Не указана'");
        $this->execute("delete from breeds where name = 'Не указана'");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231013_040656_add_not_selected_breed cannot be reverted.\n";

        return false;
    }
    */
}
