<?php

use app\commands\migrate\Migration;

class m231110_202536_newsletter_reception_update_column_text extends Migration
{
    /**
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->db->createCommand()
            ->update(
                'newsletter_reception',
                ['text' => '<p>Вы записаны на прием <дата, время>, в клинику <наименование организации>, которая находится по адресу <адрес организации>, к специалисту <ФИО врача из приема>, на следующие услуги:</p><услуги>'])
            ->execute();
        return true;
    }

    public function safeDown()
    {
        // не отменяется
        return false;
    }
}
