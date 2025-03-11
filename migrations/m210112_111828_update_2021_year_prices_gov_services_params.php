<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class m210112_111828_update_2021_year_prices_gov_services_params
 */
class m210112_111828_update_2021_year_prices_gov_services_params extends Migration
{
    private const PARAMS = [
        [
            'name' => 'Материал для исследования',
            'tech_name' => 'P111_Measurements',
            'datatype' => 'text',
            'datatype_details' => '3000',
        ],
        [
            'name' => 'Заключение',
            'tech_name' => 'P111_Serviceresult',
            'datatype' => 'text',
            'datatype_details' => '3000',
        ],
    ];

    private const SERVICES = [
        '0285' => [
            ['tech_name' => 'P0_Venousbloodanalysisnum', 'required' => true],
            ['tech_name' => 'P14_Totalbilirubinmcmvalue', 'required' => false],
        ],
        '0421' => [
            ['tech_name' => 'P0_Urinalysisnum', 'required' => true],
            ['tech_name' => 'P20_Proteinvalue', 'required' => false],
            ['tech_name' => 'P16_Analysisdesc', 'required' => false],
        ],
        '0428' => [
            ['tech_name' => 'P0_Cytologicsanalysisnum', 'required' => true],
            ['tech_name' => 'P111_Measurements', 'required' => false],
            ['tech_name' => 'P18_Analysisdate', 'required' => false],
            ['tech_name' => 'P14_Analysiscount', 'required' => false],
            ['tech_name' => 'P15_Analysisresult', 'required' => false],
            ['tech_name' => 'P111_Serviceresult', 'required' => false],
            ['tech_name' => 'P16_Analysisdesc', 'required' => false],
        ],
    ];

    private static $table = 'public.gov_services';

    private static $govParamsTable = 'public.gov_services_params';

    private static $paramsTable = 'public.params';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');
        $columns = [
            'name',
            'tech_name',
            'datatype',
            'datatype_details',
        ];
        $this->batchInsert(self::$paramsTable, $columns, array_map(static function (array $r) use ($now, $columns) {
            $record = array_merge($r, ['created_at' => $now, 'updated_at' => $now]);
            $row = [];
            foreach ($columns as $c) {
                $row[] = $record[$c];
            }

            return $row;
        }, self::PARAMS));
        $pIds = $this
            ->query(['id'], self::$paramsTable)
            ->where(['in', 'tech_name', ArrayHelper::getColumn(array_merge(...array_values(self::SERVICES)), 'tech_name', false)])
            ->indexBy('tech_name')
            ->column();
        $q = $this
            ->query(['id'])
            ->where([
                'and',
                ['deleted' => false],
                ['in', 'cod', array_keys(self::SERVICES)],
            ])
            ->indexBy('cod');
        $rows = [];
        foreach ($q->column() as $code => $sId) {
            $sort = 1;
            foreach (self::SERVICES[$code] as ['tech_name' => $techName, 'required' => $required]) {
                $pId = $pIds[$techName] ?? null;
                if (!$pId) {
                    continue;
                }
                $rows[] = [$sId, $pId, $now, $now, $sort, false, $required, false, true];
                $sort++;
            }
        }
        if (!count($rows)) {
            return;
        }
        $this->batchInsert(self::$govParamsTable, ['id_service', 'id_param', 'created_at', 'updated_at', 'sort_by', 'req_in', 'req_out', 'flag_in', 'flag_out'], $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(self::$govParamsTable, [
            'in',
            'id_service',
            $this->query(['id'])->where(['in', 'cod', array_keys(self::SERVICES)]),
        ]);
        $this->delete(self::$paramsTable, [
            'in',
            'tech_name',
            array_map(static function (array $r) {
                return $r['tech_name'];
            }, self::PARAMS),
        ]);
    }

    private function query(array $select = [], ?string $table = null): Query
    {
        $q = (new Query())->from($table ?: self::$table);
        if (!count($select)) {
            return $q;
        }

        return $q->select($select);
    }
}
