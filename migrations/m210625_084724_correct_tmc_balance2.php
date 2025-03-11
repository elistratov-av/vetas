<?php

use app\commands\migrate\Migration;

/**
 * Class m210625_084724_correct_tmc_balance2
 */
class m210625_084724_correct_tmc_balance2 extends Migration
{

    /**
     * Дата, от которой правим баланс
     */
    const START_DATE = '2020-01-01 00:00:00';

    /**
     * @var array $this = {m210625_084724_correct_tmc_balance2} [7]
     */
    private $balanceCountList = [];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tmc.balance_flow', 'migrate_flag', $this->boolean()->comment('Временный флаг для отката миграции'));

        $this->initBalanceCountList();
        $this->correctBalanceStep1();
        $this->correctBalanceStep2();
    }

    /**
     * Step1
     * Выводит значения в ноль начиная от определенной даты
     */
    private function correctBalanceStep1()
    {
        $balanceCountList = array_filter($this->balanceCountList, function ($item) {
            return $item['count'] < 0;
        });

        foreach ($balanceCountList as $balanceId => $item) {
            $attributes = [
                'id_tmc_balance' => $balanceId,
                'flow_type'      => 'I',
                'flow_action'    => 'income',
                'count'          => floatval($item['count']) * (-1),
                'created_at'     => (new DateTime($item['created_at']))->modify('+1 second')->format('Y-m-d H:i:s'),
                'migrate_flag'   => true,
            ];

            $this->insert('tmc.balance_flow', $attributes);
            $this->balanceCountList[$balanceId]['count'] = 0;
        }
    }

    /**
     * Step2
     * Выравниваем баланс пошагово
     * Выводит значения в ноль начиная пошагово, идя по журналу двищения ТМЦ по балансу.
     * Проверяем, что произойдет при операции приема/списания. Если списание приводик в минусовому значению,
     * тогда перед ним будет добавлено приход на сумму недостачи.
     *
     * @throws \yii\db\Exception
     */
    private function correctBalanceStep2()
    {
        $result = [
            'count'     => 0,
            'corrected' => 0,
        ];

        foreach ($this->findBalanceFlows('asc') as $balanceFlow) {
            if (!array_key_exists($balanceFlow['id_tmc_balance'], $this->balanceCountList)) {
                continue;
            }
            $countReal = $this->balanceCountList[$balanceFlow['id_tmc_balance']]['count'];

            switch ($balanceFlow['flow_action']) {
                case 'expense' :
                case 'expense_utilize' :
                    $countReal = round(floatval($countReal) - floatval($balanceFlow['count']), 2);
                    break;
                case 'income' :
                    $countReal = round(floatval($countReal) + floatval($balanceFlow['count']), 2);
                    break;
                default:
                    continue 1;
            }

            if ($countReal < 0) {
                $attributes = [
                    'id_tmc_balance' => $balanceFlow['id_tmc_balance'],
                    'flow_type'      => 'I',
                    'flow_action'    => 'income',
                    'count'          => $countReal * (-1),
                    'created_at'     => (new DateTime($balanceFlow['created_at']))->modify('-1 second')->format('Y-m-d H:i:s'),
                    'migrate_flag'   => true,
                ];
                $this->insert('tmc.balance_flow', $attributes);
                $this->balanceCountList[$balanceFlow['id_tmc_balance']]['count'] = 0;
                $result['corrected']++;
            } else {
                $this->balanceCountList[$balanceFlow['id_tmc_balance']]['count'] = $countReal;
            }

            $result['count']++;
        }

        print_r($result);
    }

    private function initBalanceCountList()
    {
        foreach ($this->findBalanceFlows('desc') as $balanceFlow) {
            $count = 0;

            if (array_key_exists($balanceFlow['id_tmc_balance'], $this->balanceCountList)) {
                $countBalance = $this->balanceCountList[$balanceFlow['id_tmc_balance']]['count'];
            } else {
                $countBalance = $this->findBalanceById($balanceFlow['id_tmc_balance']);
                $countBalance = $countBalance['count'];
            }

            switch ($balanceFlow['flow_action']) {
                case 'expense' :
                case 'expense_utilize' :
                    $count = (float)$countBalance + (float)$balanceFlow['count'];
                    break;
                case 'income' :
                    $count = (float)$countBalance - (float)$balanceFlow['count'];
                    break;
            }

            $count = round($count, 2);

            $this->addToBalanceCountList($balanceFlow['id_tmc_balance'], $count, $balanceFlow['created_at']);
        }
    }

    /**
     * @param $balanceId
     * @param $count
     */
    private function addToBalanceCountList($balanceId, $count, $created_at)
    {
        if (array_key_exists($balanceId, $this->balanceCountList)) {
            $this->balanceCountList[$balanceId]['count'] = $count;
            $this->balanceCountList[$balanceId]['created_at'] = $created_at;
        } else {
            $this->balanceCountList[$balanceId] = [
                'count'      => $count,
                'created_at' => $created_at,
            ];
        }
    }

    /**
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function findBalanceFlows($order)
    {
        $startDate = self::START_DATE;

        return Yii::$app->db->createCommand("
            SELECT *
            FROM tmc.balance_flow
            /*WHERE created_at >= '$startDate'*/
            order by id $order
        ")->queryAll();
    }

    /**
     * @param $id
     * @return array|false|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function findBalances()
    {
        $startDate = self::START_DATE;

        return Yii::$app->db->createCommand("
            SELECT id, count
            FROM tmc.balance
            WHERE id in (
                SELECT id_tmc_balance
                FROM tmc.balance_flow
                WHERE created_at >= '$startDate'
            )
        ")->queryAll();
    }

    /**
     * @param $id
     * @return array|false|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function findBalanceById($id)
    {
        return Yii::$app->db->createCommand("
            SELECT *
            FROM tmc.balance
            WHERE id = $id

        ")->queryOne();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('tmc.balance_flow', 'migrate_flag = true');
        $this->dropColumn('tmc.balance_flow', 'migrate_flag');
    }
}
