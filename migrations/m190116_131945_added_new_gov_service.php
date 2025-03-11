<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m190116_131945_added_new_gov_service
 */
class m190116_131945_added_new_gov_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->resetSequences();

        $data = [
            'Отоскопия',
            '130',
            'Клинико-диагностические исследования',
            'исследование',
            'Терапевтический',
            '381',
            '20',
            '0',
            'False',
            'Терапия'
        ];

        $name = $data[0];
        $price = number_format((int)$data[1], 2, '.', '');

        //Rumenko, 16:06
        //получается, что эта новая услуга тоже должна быть. у неё такие же виды животных как и услуги Отовидеоскопия

        /* @var $copyFrom \app\models\db\GovServices */
        $copyFrom = GovServices::find()
            ->where(['name' => 'Отовидеоскопия'])
            ->limit(1)
            ->one();

        if ($copyFrom === null) {
            Console::output(Console::ansiFormat('Service to copy from not found', [Console::FG_RED]));
        } else {
            $model = new GovServices([
                'name' => $name,
                'price' => $price,
                'cod' => ('0' . $data[5]),
                'duration' => $data[6],
                'cooldown' => $data[7],
                'id_pricelist' => $copyFrom->id_pricelist,
                'id_service_type' => $copyFrom->id_service_type,
                'id_cabinet_type' => $copyFrom->id_cabinet_type,
                'id_specialization' => $copyFrom->id_specialization,
                'id_service_measure' => $copyFrom->id_service_measure,
            ]);
            if (!$model->save()) {
                Console::output(Console::ansiFormat('Failed to save service [' . $name . ']', [Console::FG_RED]));
            } else {
                $species_ids = (new Query())
                    ->select('id_species')
                    ->from('public.species_services')
                    ->where(['id_service' => $copyFrom->id])
                    ->column();
                if (!empty($species_ids)) {
                    $species_ids = array_unique($species_ids);
                    $rows = [];
                    foreach ($species_ids as $species_id) {
                        $rows[] = [$species_id, $model->id];
                    }
                    Yii::$app->db->createCommand()
                        ->batchInsert('public.species_services', ['id_species', 'id_service'], $rows)
                        ->execute();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->resetSequences();
    }

    private function resetSequences()
    {
        $tables = [
            'services',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
    }
}
