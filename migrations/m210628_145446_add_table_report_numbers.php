<?php

use app\commands\migrate\Migration;

/**
 * Class m210628_145446_add_table_report_numbers
 */
class m210628_145446_add_table_report_numbers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->db->createCommand('CREATE SCHEMA report')->execute();
        $this->createTable('report.numbers', [
            'id'        => $this->primaryKey(),
            'id_report' => $this->smallInteger()->notNull()->comment('id отчета'),
            'version'   => $this->string(3)->notNull()->comment('Версия отчета'),
            'hash'      => $this->string('32')->notNull()->comment('Хэш'),
            'number'    => $this->integer()->notNull()->comment('Номер'),
            'created_at' => $this->dateTime()->notNull()->comment('Дата создания'),
        ]);

        $this->addCommentOnTable('report.numbers', 'Номер отчетов');
        $this->createIndex('idx-report_numbers', 'report.numbers', ['id_report', 'version', 'hash', 'created_at', 'number'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('report.numbers');
        Yii::$app->db->createCommand('DROP SCHEMA report')->execute();
    }
}
