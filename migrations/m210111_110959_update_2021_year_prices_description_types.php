<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m210111_110959_update_2021_year_prices_description_types
 */
class m210111_110959_update_2021_year_prices_description_types extends Migration
{
    private const SERVICES = [
        '0240' => ['0245', '0286', '0403'],
        '0203' => ['0213', '0216', '0211', '0214', '0215', '0467', '0468', '0469', '0212', '0217', '0218', '0470', '0471', '0472', '0400', '0401', '0402', '0473', '0474', '0475', '0419'],
    ];

    private const REQUIRED = ['0213', '0216', '0211', '0214', '0215', '0467', '0468', '0469', '0212', '0217', '0218', '0470', '0471', '0472', '0400', '0401', '0402', '0473', '0474', '0475'];

    private const NON_REQUIRED = ['0245', '0286', '0403', '0419'];

    private static $table = 'public.gov_services';

    private static $descTable = 'public.services_description_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $records = [];
        foreach (self::SERVICES as $from => $to) {
            $types = $this
                ->query(['id_description_type', 'required'], self::$descTable)
                ->where(['id_service' => $this->query(['id'])->where(['cod' => $from, 'deleted' => false])])
                ->all();
            $services = $this
                ->query(['id', 'cod'])
                ->where([
                    'and',
                    ['deleted' => false],
                    ['in', 'cod', $to],
                ])
                ->indexBy('cod')
                ->all();
            if (!count($types) || !count($services)) {
                continue;
            }
            foreach ($to as $code) {
                $service = $services[$code] ?? null;
                if (!$service) {
                    continue;
                }
                foreach ($types as ['id_description_type' => $dId, 'required' => $required]) {
                    if (in_array($code, self::REQUIRED, true)) {
                        $required = true;
                    } elseif (in_array($code, self::NON_REQUIRED, true)) {
                        $required = false;
                    }
                    $records[] = [$service['id'], $dId, $required];
                }
            }
        }
        if (!count($records)) {
            return;
        }
        $this->batchInsert(self::$descTable, ['id_service', 'id_description_type', 'required'], $records);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $ids = $this
            ->query(['id'])
            ->where([
                'and',
                ['deleted' => false],
                ['in', 'cod', array_merge(...array_values(self::SERVICES))],
            ])
            ->column();
        if (!count($ids)) {
            return;
        }

        $this->delete(self::$descTable, [
            'in',
            'id_service',
            $ids,
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
