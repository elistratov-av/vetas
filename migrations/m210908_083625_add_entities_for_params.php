<?php

use app\commands\migrate\Migration;

/**
 * Class m210908_083625_add_entities_for_params
 */
class m210908_083625_add_entities_for_params extends Migration
{
    const SERVICE_149 = 149;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * 1. Добавляем новые и вяжем с услугой
         * 2. Обновляем сортировку
         */
        $this->insertParams();
        $this->updateSorting();

    }

    private function insertParams()
    {
        $params = $this->getParams();

        foreach ($params as $param) {
            if (!$param['id']) {
                $paramAttr = array_filter($param, function ($val, $key) {
                    return in_array($key, ['name', 'tech_name', 'datatype', 'datatype_details', 'visit_flag']);
                }, ARRAY_FILTER_USE_BOTH);

                $param['id'] = $this->insertIntoParams($paramAttr);
            }

            $this->insertIntoServiceParams([
                'id_service' => self::SERVICE_149,
                'id_param'   => $param['id'],
                'req_in'     => false,
                'req_out'    => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'flag_in'    => false,
                'flag_out'   => true,
            ]);
        }
    }

    /**
     * @param $attributes
     * @return false|int|string|\yii\db\DataReader|null
     * @throws \yii\db\Exception
     */
    private function insertIntoParams($attributes)
    {
        $fiels = implode(',', array_keys($attributes));
        $values = implode(',', array_map(function ($val) {
            if (is_bool($val)) {
                return $val ? 'true' : 'false';
            }

            return "'" . $val . "'";
        }, array_values($attributes)));

        return Yii::$app->db->createCommand("
            INSERT INTO params ($fiels) VALUES ($values) RETURNING id;
        ")->queryScalar();
    }

    /**
     * @param $attributes
     * @return false|int|string|\yii\db\DataReader|null
     * @throws \yii\db\Exception
     */
    private function insertIntoServiceParams($attributes)
    {
        $fiels = implode(',', array_keys($attributes));
        $values = implode(',', array_map(function ($val) {
            if (is_bool($val)) {
                return $val ? 'true' : 'false';
            } else {
                if (is_numeric($val)) {
                    return $val;
                }
            }

            return "'" . $val . "'";
        }, array_values($attributes)));

        return Yii::$app->db->createCommand("
            INSERT INTO gov_services_params ($fiels) VALUES ($values) RETURNING id;
        ")->queryScalar();
    }

    private function updateSorting()
    {
        $orderRules = [
            'P12_Analysisnum'       => 1,
            'P18_Analysisdate'      => 2,
            'P14_Analysiscount'     => 3,
            'P15_Analysisresult'    => 4,
            'P3_Epithermal_cells'   => 5,
            'P18_Leukocytesvalue'   => 6,
            'P19_Erythrocytesvalue' => 7,
            'P20_Bacteriavalue'     => 8,
            'P3_Mushrooms'          => 9,
            'P3_Serviceresult'      => 10,
            'P16_Analysisdesc'      => 11,
        ];

        $idService = self::SERVICE_149;
        foreach ($orderRules as $techName => $orderValue) {
            $idParam = Yii::$app->db->createCommand("SELECT id FROM params WHERE tech_name = '$techName'")->queryScalar();
            $this->update('gov_services_params', ['sort_by' => $orderValue], "id_param = $idParam AND id_service = $idService");
        }
    }

    private function getParams()
    {
        return [
            [
                'id'               => null,
                'name'             => 'Эпитермальные клетки',
                'tech_name'        => 'P3_Epithermal_cells',
                'datatype'         => 'text',
                'datatype_details' => '255',
                'visit_flag'       => false,
            ],
            [
                'id'               => null,
                'name'             => 'Грибы',
                'tech_name'        => 'P3_Mushrooms',
                'datatype'         => 'text',
                'datatype_details' => '255',
                'visit_flag'       => false,
            ],
            [
                'id'               => null,
                'name'             => 'Заключение',
                'tech_name'        => 'P3_Serviceresult',
                'datatype'         => 'text',
                'datatype_details' => '3000',
                'visit_flag'       => false,
            ],
            [
                'id'        => 80,
                'name'      => 'Лейкоциты',
                'tech_name' => 'P18_Leukocytesvalue',
            ],
            [
                'id'        => 91,
                'name'      => 'Эритроциты',
                'tech_name' => 'P19_Erythrocytesvalue',
            ],
            [
                'id'        => 100,
                'name'      => 'Бактерии',
                'tech_name' => 'P20_Bacteriavalue',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('params', [
            'tech_name' => [
                'P3_Epithermal_cells',
                'P3_Mushrooms',
                'P3_Serviceresult',
            ]
        ]);

        $this->delete('gov_services_params', [
            'id_param'   => [80, 91, 100],
            'id_service' => self::SERVICE_149,
        ]);
    }
}
