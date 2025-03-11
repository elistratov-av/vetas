<?php

use app\commands\migrate\Migration;

/**
 * Class m230511_101400_informing_table_create
 */
class m230511_101400_informing_table_create extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.informings', [
            'id' => $this->primaryKey(),
            'text' => $this->string(255)->comment('Текст уведомления'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()
        ]);
        $this->execute("ALTER TABLE public.informings ADD COLUMN date tsrange; ");
        $this->addCommentOnTable('informings', 'Таблица уведомлений для информирования пользователей');

        $this->createTable('public.informings_organizations', [
            'id_informing' => $this->integer()->comment('ID уведомления'),
            'id_organization' => $this->integer()->comment('ID организации')->defaultValue(null),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()
        ]);

        $this->addPrimaryKey('informings_organizations_pkey', 'public.informings_organizations', ['id_informing', 'id_organization']);
        $this->addCommentOnTable('informings_organizations', 'Таблица связи информирований и организаций');
       
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('informings');
        $this->dropTable('informings_organizations');

        return true;
    }
}
