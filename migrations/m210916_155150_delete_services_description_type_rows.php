<?php

use app\commands\migrate\Migration;

/**
 * Class m210916_155150_delete_services_description_type_rows
 */
class m210916_155150_delete_services_description_type_rows extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \Yii::$app->getDb()->createCommand("
            DELETE FROM services_description_types
            WHERE id_service in (
                SELECT id FROM gov_services WHERE
                name = 'Общий анализ мочи: Исследование мочи на креатинин' OR
                name = 'Общий анализ мочи: Исследование мочи на мочевину' OR
                name = 'Общий анализ мочи: Исследование мочи на глюкозу' OR
                name = 'Общий клинический анализ крови: Подсчет ретикулоцитов'
            )")->execute();
    }

    /**
     * @return bool
     */
    public function safeDown()
    {
        return true;
    }
}




