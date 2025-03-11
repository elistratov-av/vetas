<?php

use app\commands\migrate\Migration;

/**
 * Class m240618_142725_create_new_otchet_0310_Hloridi
 */
class m240618_142725_create_new_otchet_0310_Hloridi extends Migration
{
    public function up()
    {
        // если нет создаем отчет Показатели биохимического анализа крови - определение хлоридов
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
        //

        // получаем id отчета Показатели биохимического анализа крови - определение хлоридов
        $id_existingReport = (new \yii\db\Query())
            ->select('id')
            ->from('public.reports')
            ->where([
                'name' => 'Показатели биохимического анализа крови - определение хлоридов',
                'report_type' => 'R',
                'sending' => true,
            ])
            ->scalar();
        //


        // получем id параметров P83_Chloridemmdesc, P82_Chloridemmvalue
        $paramIds = (new \yii\db\Query())
            ->select('id')
            ->from('public.params')
            ->where(['tech_name' => ['P83_Chloridemmdesc', 'P82_Chloridemmvalue']])
            ->column();
        //

        // заполняем параметры отчета Показатели биохимического анализа крови - определение хлоридов
        foreach ($paramIds as $paramId) {
            // Проверяем, существует ли уже такой параметр для данного отчета
            $existingParam = (new \yii\db\Query())
                ->from('public.reports_params')
                ->where(['id_report' => $id_existingReport, 'id_param' => $paramId])
                ->exists();

            if (!$existingParam) {
                $this->insert('public.reports_params', [
                    'id_report' => $id_existingReport,
                    'id_param' => $paramId,
                ]);
            }
        }
        //

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
