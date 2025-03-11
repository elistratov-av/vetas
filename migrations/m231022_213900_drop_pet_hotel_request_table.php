<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel_animal_owner`.
 */
class m231022_213900_drop_pet_hotel_request_table extends Migration
{

    public function execSql($sql)
    {
        $time = $this->beginCommand($sql);
        $this->db->createCommand($sql)->execute();
        $this->endCommand($time);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->execSql('drop table public.pet_hotel_request cascade');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // невозможно отменить
        return false;
    }
}
