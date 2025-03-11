<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%refund_data}}`.
 */
class m230925_065536_create_refund_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%refund_data}}', [
            'id' => $this->primaryKey(),
            'bank_name' => $this->string()->comment('Наименование банка'),
            'corresponded_account' => $this->string()->comment('Корреспонденсткий счёт'),
            'org_name' => $this->string()->comment('Наименование организации'),
            'bik' => $this->string()->comment('БИК'),
            'client_account' => $this->string()->comment('Клиентский счёт'),
            'client_fio' => $this->string()->comment('ФИО клиента'),
            'id_visit' => $this->integer()->comment('Ссылка на осмотр по которому оформляется возврат средств'),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);
        $this->addCommentOnTable('refund_data', 'Данные предоставленные для возврата средств');

        $this->createIndex(
            'idx-refund_data-id_visit',
            'refund_data',
            'id_visit',
        );

        $this->addForeignKey(
            'fk-refund_data-id_visit',
            'refund_data',
            'id_visit',
            'visits',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-refund_data-id_visit',
            'refund_data'
        );

        $this->dropIndex(
            'idx-refund_data-id_visit',
            'refund_data'
        );

        $this->dropTable('{{%refund_data}}');
    }
}
