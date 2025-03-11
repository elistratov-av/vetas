<?php

use app\commands\migrate\Migration;

/**
 * Class m190803_083110_add_sending_field_to_reports
 */
class m190803_083110_add_sending_fields_to_reports extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('reports', 'sending', $this->boolean()->defaultValue(false));
        $this->addCommentOnColumn('reports', 'sending', 'Признак доступности отчета для уведомления');
        $reportsForSending = [
            'Результат биохимического исследования крови',
            'Гельминто- копрологическое исследование',
            'Результат клинического анализа мочи',
            'Результат цитологического исследования мазка - отпечатка',
            'Результат микроскопического исследования',
            'Результат общего клинического анализа крови',
            'Результат биохимического исследования кала',
            'Результат гормональных исследований крови'
        ];

        foreach ($reportsForSending as $name) {
            if (!$report = \app\models\db\Reports::findOne(['name' => $name])) {
                throw new \yii\base\Exception("Не найден отчет {$name}");
            }

            Yii::$app->db->createCommand("update reports set sending = true where id = :id", ['id' => $report->id])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('reports', 'sending');
    }

}
