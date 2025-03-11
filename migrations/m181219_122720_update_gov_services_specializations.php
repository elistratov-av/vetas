<?php

use app\commands\migrate\Migration;

/**
 * Class m181219_122720_update_gov_services_specializations
 */
class m181219_122720_update_gov_services_specializations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Руменко Леонид Андреевич
        // для услуг изменил специализацию с "Терапия" на "Груминг"

        $id_specialization = (new \yii\db\Query())
            ->select('id')
            ->from(\app\models\db\Specializations::tableName())
            ->where(['name' => 'Груминг'])
            ->scalar();

        if (empty($id_specialization)) {
            \yii\helpers\Console::output('Specialization not found');
            return true;
        }

        $services = [
            'Санитарная стрижка животных - мелкие животные (до 5 кг)',
            'Санитарная стрижка животных - средние животные (свыше 5 кг до 15 кг)',
            'Санитарная стрижка животных - крупные животные (свыше 15 кг)',
            'Санитарная помывка животных - мелкие животные (до 5 кг)',
            'Санитарная помывка животных - средние животные (свыше 5 кг до 15 кг)',
            'Санитарная помывка животных - крупные животные (свыше 15 кг)',
        ];

        foreach ($services as $name) {
            $govService = \app\models\db\GovServices::findOne(['name' => $name]);
            if ($govService === null) {
                \yii\helpers\Console::output('Not found: ' . $name);
                continue;
            }
            $govService->updateAttributes([
                'id_specialization' => $id_specialization,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181219_122720_update_gov_services_specializations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181219_122720_update_gov_services_specializations cannot be reverted.\n";

        return false;
    }
    */
}
