<?php

use app\commands\migrate\Migration;
use app\models\db\PetOwners;

/**
 * Class m240910_072104_create_incognito_fl
 */
class m240910_072104_create_incognito_fl extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->db->createCommand("INSERT INTO pet_owners (id, f_fio, i_fio) VALUES (1, 'Инкогнито', 'ФЛ')")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand("DELETE FROM pet_owners WHERE id = 1")->execute();
    }

}
