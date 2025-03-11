<?php

use app\commands\migrate\Migration;

/**
 * Class m240409_092538_create_new_otchet_0310_Hloridi
 */
class m240409_092538_create_new_otchet_0310_Hloridi extends Migration
{
    public function up()
    {
        $existingReport = (new \yii\db\Query())
            ->from('public.reports')
            ->where([
                'name' => 'Показатели биохимического анализа крови - определение хлоридов',
                'report_type' => 'R',
                'sending' => true,
            ])
            ->exists();

        if (!$existingReport) {
            $this->insert('public.reports', [
                'name' => 'Показатели биохимического анализа крови - определение хлоридов',
                'report_type' => 'R',
                'sending' => true,
            ]);
        }

        $id = (new \yii\db\Query())
            ->select('id')
            ->from('public.reports')
            ->where([
                'name' => 'Показатели биохимического анализа крови - определение хлоридов',
                'report_type' => 'R',
                'sending' => true,
            ])
            ->scalar();

        // Проверяем есть ли запись в таблице public.reports_params
        $existingReportParam = (new \yii\db\Query())
            ->from('public.reports_params')
            ->where([
                'id_report' => $id,
                'id_param' => (new \yii\db\Query())
                    ->select('id')
                    ->from('public.params')
                    ->where(['tech_name' => ['P83_Chloridemmdesc', 'P82_Chloridemmvalue']])
                    ->column(),
            ])
            ->exists();

        if (!$existingReportParam) {
            // Добавляем запись в таблицу public.reports_params
            $this->insert('public.reports_params', [
                'id_report' => $id,
                'id_param' => (new \yii\db\Query())
                    ->select('id')
                    ->from('public.params')
                    ->where(['tech_name' => ['P83_Chloridemmdesc', 'P82_Chloridemmvalue']])
                    ->column(),
            ]);
        }
    }

    public function down()
    {
        // Удаляем связи из таблицы public.reports_params
        $this->delete('public.reports_params', ['id_report' => (new \yii\db\Query())
            ->select('id')
            ->from('public.reports')
            ->where(['name' => 'Показатели биохимического анализа крови - определение хлоридов'])
        ]);

        // Удаляем добавленную запись из таблицы public.reports
        $this->delete('public.reports', ['name' => 'Показатели биохимического анализа крови - определение хлоридов']);
    }

}
