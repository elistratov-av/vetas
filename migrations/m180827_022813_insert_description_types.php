<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m180827_022813_insert_description_types
 */
class m180827_022813_insert_description_types extends Migration
{
    private $tableName = 'description_types';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
Анамнез;visit;1
Симптомы;visit;2
Предварительный диагноз;visit;3
Заключительный диагноз;visit;4
Схема лечения;visit;5
Рекомендации ;visit;6
Этиопатогенез и особенности;disease;1
Этиопатогенез;disease;2
Особенности;disease;3
Патогенез;disease;4
Этиология;disease;5
Суммарная клиника;disease;6
Клиника;disease;7
Симптомы;disease;8
Клинические признаки;disease;9
Клиническая лаборатория;disease;10
Визуализация;disease;11
Диагноз;disease;12
Общий анализ крови / биохимия крови / анализ мочи;disease;13
Признаки;disease;14
Хирургические учёты;disease;15
Диагностические исследования;disease;16
Факторы риска;disease;17
Причины;disease;18
Другие лабораторные тесты;disease;19
Возможные взаимодействия;disease;20
Возможные осложнения;disease;21
Двигательная активность;disease;22
Дифференциальный диагноз;disease;23
Затронутые системы;disease;24
Медикаменты выбора;disease;25
Патологические изменения;disease;26
Частота проявления / Преваленция;disease;27
Патоанатомические изменения;disease;28
Превенция;disease;29
Лечение, развитие и прогноз;disease;30
Лечение;disease;31
Развитие;disease;32
Прогноз;disease;33
Развитие и прогноз;disease;34
Ожидаемое развитие и прогноз;disease;35
Диета;disease;36
Профилактика;disease;37
Стационарный уход;disease;38
Мониторинг пациента;disease;39
Особые указания;disease;40
Патофизиология;disease;41
Породная предрасположенность;disease;42
Породы;disease;43
Генетика, наследственность;disease;44
Восприимчивость;disease;45
Противопоказания;disease;46
Соответствующий уход;disease;47
Альтернативные медикаменты;disease;48
Предосторожности;disease;49
Сопровождающие состояния (патологические осложнения);disease;50
Сопровождающие факторы и заболевания;disease;51
Сопровождающие состояния;disease;52
Причины и факторы риска;disease;53
Беременность;disease;54
Информирование клиента;disease;55
Смотри также;disease;56
Сокращения;disease;57
Синонимы;disease;58
Библиография;disease;59
CSV;

        $data = str_getcsv($csv, "\n");
        foreach ($data as $row) {
            $arr = str_getcsv($row, ';', '');
            $name = $arr[0];
            $entity_type = $arr[1];
            $sort_by = (int)$arr[2];
            $record = (new Query())
                ->select('*')
                ->from($this->tableName)
                ->where(['name' => $name, 'entity_type' => $entity_type])
                ->limit(1)
                ->one();
            if (empty($record)) {
                Yii::$app->db
                    ->createCommand()
                    ->insert($this->tableName, ['name' => $name, 'entity_type' => $entity_type, 'sort_by' => $sort_by])
                    ->execute();
            } else {
                Yii::$app->db
                    ->createCommand()
                    ->update($this->tableName, ['sort_by' => $sort_by], ['id' => $record['id']])
                    ->execute();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180827_022813_insert_description_types cannot be reverted.\n";

        return false;
    }
    */
}
