<?php

use app\commands\migrate\Migration;
use app\models\db\Reports;
use yii\db\Query;

/**
 * Class m210129_061418_add_reports
 */
class m210129_061418_add_reports extends Migration
{
    private const REPORTS = [
        '0285' => [
            'id' => 28,
            'report_type' => Reports::TYPE_REPORT,
            'name' => 'Результат биохимического исследования крови: - определение прямого билирубина',
            'grouped' => false,
            'sending' => true,
        ],
        '0421' => [
            'id' => 29,
            'report_type' => Reports::TYPE_REPORT,
            'name' => 'Результат клинического анализа белка в моче',
            'grouped' => false,
            'sending' => true,
        ],
        '0428' => [
            'id' => 30,
            'report_type' => Reports::TYPE_REPORT,
            'name' => 'Результат микроскопического исследования (микроскопия)',
            'grouped' => false,
            'sending' => true,
        ],
    ];

    private static $table = 'public.reports';

    private static $govServicesTable = 'public.gov_services';

    private static $govServiceReportsTable = 'public.gov_services_reports';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');
        $columns = ['id', 'report_type', 'name', 'grouped', 'sending', 'created_at', 'updated_at'];

        $this->batchInsert(self::$table, $columns, array_map(static function (array $r) use ($columns, $now) {
            $record = array_merge($r, ['created_at' => $now, 'updated_at' => $now]);
            $row = [];
            foreach ($columns as $c) {
                $row[] = $record[$c];
            }

            return $row;
        }, self::REPORTS));
        $services = $this
            ->query(['id', 'cod'], self::$govServicesTable)
            ->where([
                'and',
                ['deleted' => false],
                ['in', 'cod', array_keys(self::REPORTS)],
            ])
            ->indexBy('cod')
            ->all();

        $mappings = [];
        foreach (self::REPORTS as $code => ['id' => $rId]) {
            $s = $services[$code] ?? null;
            if (!is_array($s)) {
                continue;
            }
            $mappings[] = [$s['id'], $rId, $now, $now];
        }
        if (!count($mappings)) {
            return;
        }

        $this->batchInsert(self::$govServiceReportsTable, ['id_service', 'id_report', 'created_at', 'updated_at'], $mappings);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(self::$table, [
            'in',
            'id',
            array_map(static function (array $r) {
                return $r['id'];
            }, self::REPORTS),
        ]);
    }

    /**
     * @param array $select
     * @param mixed $table
     * @return Query
     */
    private function query(array $select = [], $table = null): Query
    {
        $q = (new Query())->from($table ?? self::$table);
        if (!count($select)) {
            return $q;
        }

        return $q->select($select);
    }
}
