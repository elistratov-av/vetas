<?php

use app\commands\migrate\Migration;

/**
 * Class m210707_081846_schema_spk
 */
class m210707_081846_schema_spk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA spk;');
        $this->execute("COMMENT ON SCHEMA spk IS 'Данные о подписках из ИС ПК'");

        $this->createTable('spk.update_subscription_task', [
            'id' => $this->bigPrimaryKey(),
            'status' => $this->char(1),
            'date_start' => $this->timestamp(0)->defaultValue('NOW()')->comment('Дата начала получения данных из ИС ПК'),
            'date_end' => $this->timestamp(0)->defaultValue('NOW()')->comment('Дата окончания получения данных из ИС ПК'),
            'current_offset' => $this
                ->integer()
                ->notNull()
                ->defaultValue('0')
                ->comment('Текущий offset для работающего задания, последний - для заверщенного'),
            'error_msg' => $this->text()->comment('Описание ошибки (если есть)'),
        ]);
        $this->addCommentOnTable(
            'spk.update_subscription_task',
            'Статус выполнения обновления данных из ИС ПК'
        );


        $this->createTable('spk.subscription',[
            'id' => $this->bigPrimaryKey(),
            'id_task' => $this->bigInteger()->comment('ID задания на обновление'),
            'ext_id' => $this->integer()->comment('ID в системе ИС ПК'),
            'stream' => $this->char(16)->comment('Тип коммуникационного канала'),
            'email' => $this->text()->comment('Адрес электронной почты (контакта)'),
            'msisdn' => $this->char(16)->comment('Номер телефона (контакта)'),
            'service' => $this->char(32)->comment('Код сервиса подписки'),
            'options' =>  $this->json()->comment('Опции подписки для сервиса (при наличии)'),
            'expiration' => $this->timestamp(0)->comment('Время окончания срока действия подписки'),
            'created' => $this->timestamp(0)->comment('Время создания подписки (по данным ИС ПК)'),
            'day_time' => $this->json()->comment('Дни недели, по которым контакту можно рассылать уведомления'),
        ]);

        $this->addCommentOnTable(
            'spk.subscription',
            'Данные о подписках из ИС ПК'
        );

        $this->addForeignKey(
            'fk_spk_subscription_id_task',
            'spk.subscription',
            'id_task',
            'spk.update_subscription_task',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('spk.subscription');
        $this->dropTable('spk.update_subscription_task');
        $this->execute('DROP SCHEMA spk;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210707_081846_schema_spk cannot be reverted.\n";

        return false;
    }
    */
}
