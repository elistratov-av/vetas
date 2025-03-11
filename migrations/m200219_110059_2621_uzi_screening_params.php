<?php

use app\commands\migrate\Migration;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\helpers\Console;

/**
 * Class m200219_110059_2621_uzi_screening_params
 */
class m200219_110059_2621_uzi_screening_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $service = \app\models\db\GovServices::findOne(['name' => 'Ультразвуковой скрининг органов брюшной полости']);

        if ($service === null) {
            Console::output('Service not found');
            return false;
        }

        $techNames = [
            'P14_Pechenraspoloshenie',
            'P15_Pechenkontur',
            'P16_Capsula',
            'P17_Pechenekhostruktura',
            'P18_Pechenekhogennost',
            'P19_Pechenperifsosudrisunok',
            'P20_Pechenportae',
            'P21_Pechenvhepatica',
            'P22_Pechenahepatica',
            'P23_Pechenobyemnobrazov',
            'P24_Zhelchpuzyrraspoloshenie',
            'P24_Zhelchpuzyrstepennapolneniya',
            'P25_Zhelchpuzyrformazhelchpuzyrya',
            'P26_Zhelchpuzyrtolshchinastenki',
            'P28_Zhelchpuzyrstrukturazhelchi',
            'P29_Zhelchpuzyrpuzyrprotok',
            'P30_Zhelchpuzyrobshzhelchprotok',
            'P31_Zhelchpuzyrpechenochnprotok',
            'P32_Zhelchpuzyrobyemnobrazov',
            'P33_Selezenkaraspoloshenie',
            'P34_Selezenkakontur',
            'P35_Selezenkarazmer',
            'P36_Selezenkaekhostruktura',
            'P37_Selezenkaekhogennost',
            'P38_Selezenkasosudrisunok',
            'P39_Selezenkaobyemnobrazov',
            'P42_Podzhelzhelezavisualization',
            'P40_Podzhelzhelezaraspoloshenie',
            'P41_Podzhelzhelezakontur',
            'P43_Podzhelzhelezaekhostruktura',
            'P46_Zheludkishechntrakt',
            'P47_Svobodnzhidkost',
            'P14_Prvpochraspoloshenie',
            'P15_Prvpochgranica',
            'P16_Prvpochrazmer',
            'P17_Prvpochkortiksloytolshina',
            'P18_Prvpochkortiksloyekhogennost',
            'P19_Prvpochkortiksloyekhostruktura',
            'P20_Prvpochmedullyarsloytolshchina',
            'P21_Prvpochmedullyarsloyekhogennost',
            'P22_Prvpochmedullyarsloyekhostruktura',
            'P23_Prvpochmedullyarsloykortmeddiffer',
            'P24_Prvpochpiyelicheskiyindeks',
            'P25_Prvpochpochsinusekhogennost',
            'P26_Prvpochpochsinuschetkostdifferents',
            'P27_Prvpochpochsinuspolostlokhanki',
            'P28_Prvpochpochsinusstepenlokhanki',
            'P29_Prvpochsosudyparenkhimy',
            'P30_PrvpochIndeksrezistivnpochechnart',
            'P31_PrvpochIndeksrezistivnmezhdolevoyart',
            'P32_Prvpochkonkrementy',
            'P33_Prvpochobyemnobrazov',
            'P34_Levpochraspoloshenie',
            'P35_Levpochgranica',
            'P36_Levpochrazmer',
            'P37_Levpochkortiksloytolshina',
            'P38_Levpochkortiksloyekhogennost',
            'P39_Levpochkortiksloyekhostruktura',
            'P40_Levpochmedullyarsloytolshchina',
            'P41_Levpochmedullyarsloyekhogennost',
            'P42_Levpochmedullyarsloyekhostruktura',
            'P43_Levpochmedullyarsloykortmeddiffer',
            'P44_Levpochpiyelicheskiyindeks',
            'P45_Levpochpochsinusekhogennost',
            'P46_Levpochpochsinuschetkostdifferents',
            'P47_Levpochpochsinuspolostlokhanki',
            'P48_Levpochpochsinusstenkilokhanki',
            'P49_Levpochsosudyparenkhimy',
            'P50_LevpochIndeksrezistivnpochechnart',
            'P51_LevpochIndeksrezistivnmezhdolevoyart',
            'P52_Levpochkonkrementy',
            'P53_Levpochobyemnobrazov',
            'P54_Mochpuzstepnapoln',
            'P55_Mochpuztolshchinastenki',
            'P56_Mochpuzdeformatsiya',
            'P57_Mochpuzuretra',
            'P58_MochpuzObyemnobrazov',
        ];

        foreach ($techNames as $i => $techName) {
            $param = Params::findOne(['tech_name' => $techName]);
            if ($param !== null) {
                Console::output('Updating param: ' . $techName);
                $sortBy = $i + 1;
                \Yii::$app->db->createCommand()
                    ->update(
                        GovServicesParams::tableName(),
                        ['sort_by' => $sortBy],
                        [
                            'id_service' => $service->id,
                            'id_param' => $param->id,
                        ]
                    )
                    ->execute();
            } else {

                Console::output('Param not found: ' . $techName);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200219_110059_2621_uzi_screening_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200219_110059_2621_uzi_screening_params cannot be reverted.\n";

        return false;
    }
    */
}
