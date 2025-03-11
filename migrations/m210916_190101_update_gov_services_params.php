<?php

use app\commands\migrate\Migration;

/**
 * Class m210916_190101_update_gov_services_params
 */
class m210916_190101_update_gov_services_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \Yii::$app->getDb()->createCommand("
            UPDATE gov_services_params
            SET req_out = true
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




