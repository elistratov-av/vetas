<?php

use app\commands\migrate\Migration;

/**
 * Class m210605_104619_alter_table_balnce_action
 */
class m210605_104619_alter_table_balnce_action extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('tmc.balance_action', 'acceptor_id_specialist', $this->string());
        $this->alterColumn('tmc.balance_action', 'acceptor_id_specialist', 'int USING acceptor_id_specialist::integer');

        $data = \Yii::$app->db->createCommand('SELECT * FROM tmc.balance_action WHERE acceptor_date IS NULL')
            ->queryAll();

        if (!$data) {
            return;
        }

        foreach ($data as $row) {
            if ($row['action'] == 'write_off') {
                $this->update(
                    'tmc.balance_action',
                    [
                        'acceptor_date'          => $row['updated_at'], // считаем, что это дата последней операции над действием
                        'acceptor_id_specialist' => $row['initiator_id_specialist'],
                    ],
                    'id = ' . $row['id']
                );
            } else {
                if ($row['status'] == 'C') {
                    $acceptorIdSpecialist = $row['to_id_specialist'];
                } else {
                    if ($row['status'] == 'R') {
                        $acceptorIdSpecialist = $row['from_id_specialist']; // Ставим передающего, так как невозможно точно опеределить, кто отклонил
                    } else {
                        continue;
                    }
                }
                $this->update(
                    'tmc.balance_action',
                    [
                        'acceptor_date'          => $row['updated_at'], // считаем, что это дата последней операции над действием
                        'acceptor_id_specialist' => $acceptorIdSpecialist,
                    ],
                    'id = ' . $row['id']
                );
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
}

