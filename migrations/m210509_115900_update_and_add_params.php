<?php

use app\commands\migrate\Migration;
use yii\db\Expression;

/**
 * Class m210509_115900_update_and_add_params
 */
class m210509_115900_update_and_add_params extends Migration
{
    const REPORT_ID = 1; //Бланк регистрации и вакцинации животных

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insertIntoParams();
        $this->updateParams(true);

        foreach ($this->findParams() as $param) {
            $this->insertIntoReportsParams($param['id']);
        }
    }

    /**
     * @param array $params
     */
    private function insertIntoParams()
    {
        foreach ($this->getParams() as $params) {
            if ($this->hasParamByTechName($params['tech_name'])) {
                continue;
            }

            $this->insert(
                'public.params',
                $params
            );
        }
    }

    private function updateParams(bool $visitFlag)
    {
        $techName = [
            'P13_Servicetext',
            'P0_Inventorynumber',
            'P15_Vacexpirationdate'
        ];

        $this->update(
            'public.params',
            ['visit_flag' => $visitFlag],
            ['in', 'tech_name', $techName]
        );

    }

    /**
     * @param int $paramId
     */
    private function insertIntoReportsParams(int $paramId)
    {
        $this->insert(
            'public.reports_params', [
                'id_param'   => $paramId,
                'id_report'  => self::REPORT_ID,
                'created_at' => new Expression('now()'),
                'updated_at' => new Expression('now()'),
            ]
        );
    }

    /**
     * @param $techName
     * @return \yii\db\Command
     */
    private function hasParamByTechName($techName): bool
    {
        return (bool)Yii::$app->db->createCommand("
            SELECT id FROM public.params where tech_name = '" . $techName . "' 
        ")->queryScalar();
    }

    /**
     * @param $techName
     * @return \yii\db\Command
     */
    private function findParams()
    {
        $techNameList = array_map(function ($params) {
            return "'" . $params['tech_name'] . "'";
        }, $this->getParams());

        return Yii::$app->db->createCommand("
            SELECT id, tech_name FROM params where tech_name in (" . implode(',', $techNameList) . ") 
        ")->queryAll();
    }

    /**
     * @return array
     */
    private function getParams(): array
    {
        return [
            [
                'name'             => 'Перечень заболеваний, от которых привито животное',
                'tech_name'        => 'P13_ListOfDiseases',
                'datatype'         => 'text',
                'datatype_details' => 3000,
                'created_at'       => new Expression('now()'),
                'updated_at'       => new Expression('now()'),
                'visit_flag'       => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->updateParams(false);

        foreach ($this->getParams() as $params) {
            $this->delete(
                "public.params",
                "tech_name = '" . $params['tech_name'] . "'"
            );
        }
    }
}
