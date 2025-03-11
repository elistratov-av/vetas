<?php

use app\commands\migrate\Migration;

/**
 * Class m181023_041952_update_service_params_configs
 */
class m181023_041952_update_service_params_configs extends Migration
{
    private $tableName = 'params';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $data = [
            [
                'params' => [
                    'P14_Odrazmerperednegootrezka',
                    'P15_Odrazmerzadnegootrezka',
                    'P16_Odstructuraperedcamer',
                    'P17_Odrazmerhrust',
                    'P18_Odstructurahrust',
                    'P19_Odcapsulahrust',
                    'P20_Odstructurasteklotelo',
                    'P21_Oddiametrzrachka',
                    'P22_Odcontur',
                    'P23_Odstructura',
                    'P24_Odstructuradiskazritnerva',
                    'P25_Odstructuraretrobulyar',
                    'P26_Osrazmerperednegootrezka',
                    'P27_Osrazmerzadnegootrezka',
                    'P28_Osstructuraperedcamer',
                    'P29_Osrazmerhrust',
                    'P30_Osstructurahrust',
                    'P31_Oscapsulahrust',
                    'P32_Osstructurasteklotelo',
                    'P33_Osdiametrzrachka',
                    'P34_Oscontur',
                    'P35_Osstructura',
                    'P36_Osstructuradiskazritnerva',
                    'P37_Osstructuraretrobulyar',
                    'P38_Serviceresult',
                ],
                'config' => [
                    'req_in' => [
                        'tech_name' => 'P0_Organsystem',
                        'value' => 'глаза',
                    ],
                    'report' => 'Ультразвуковое исследование глаза',
                ],
            ],
            [
                'params' => [
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
                    'P59_Serviceresult',
                ],
                'config' => [
                    'req_in' => [
                        'tech_name' => 'P0_Organsystem',
                        'value' => 'мочевыделительная система',
                    ],
                    'report' => 'Ультразвуковое исследование мочевыделительной системы',
                ],
            ],
            [
                'params' => [
                    'P14_Pechenraspoloshenie',
                    'P15_Pechenkontur',
                    'P16_Pechenrazmer',
                    'P17_Pechenekhostruktura',
                    'P18_Pechenekhogennost',
                    'P19_Pechenperifsosudrisunok',
                    'P20_Pechenportae',
                    'P21_Pechenvhepatica',
                    'P22_Pechenahepatica',
                    'P23_Pechenobyemnobrazov',
                    'P24_Zhelchpuzyrstepennapolneniya',
                    'P25_Zhelchpuzyrformazhelchpuzyrya',
                    'P26_Zhelchpuzyrtolshchinastenki',
                    'P27_Zhelchpuzyrdeformatsiya',
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
                    'P40_Podzhelzhelezaraspoloshenie',
                    'P41_Podzhelzhelezakontur',
                    'P42_Podzhelzhelezarazmer',
                    'P43_Podzhelzhelezaekhostruktura',
                    'P44_Podzhelzhelezaekhogennost',
                    'P45_Podzhelzhelezaobyemnobrazov',
                    'P46_Zheludkishechntrakt',
                    'P47_Svobodnzhidkost',
                    'P48_Serviceresult',
                ],
                'config' => [
                    'req_in' => [
                        'tech_name' => 'P0_Organsystem',
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    ],
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                ],
            ],
            [
                'params' => [
                    'P14_Matkadiametrtela',
                    'P15_Matkatolshinatela',
                    'P16_Matkastrukturastenkitela',
                    'P17_Matkasostoyanpolosti',
                    'P18_Matkadiametrpravroga',
                    'P19_Matkatolshinapravroga',
                    'P20_Matkastrukturastenkipravroga',
                    'P21_Matkasoderzhimpolostipravroga',
                    'P22_Matkadiametrlevroga',
                    'P23_Matkatolshinalevroga',
                    'P24_Matkastrukturastenkilevroga',
                    'P25_Matkasoderzhimpolostilevroga',
                    'P26_Pravyaichnikrazmer',
                    'P27_Pravyaichnikkontur',
                    'P28_Pravyaichniknovoobrazov',
                    'P29_Levyaichnikrazmer',
                    'P30_Levyaichnikkontur',
                    'P31_Levyaichniknovoobrazov',
                    'P32_Serviceresult',
                ],
                'config' => [
                    'req_in' => [
                        'tech_name' => 'P0_Organsystem',
                        'value' => 'репродуктивная система самки',
                    ],
                    'report' => 'Ультразвуковое исследование репродуктивной системы самки',
                ],
            ],
            [
                'params' => [
                    'P14_Predstzhelezarazmer',
                    'P15_Predstzhelezakontur',
                    'P16_Predstzhelezaparenkhima',
                    'P17_Predstzhelezaobyemnobrazov',
                    'P18_Pravsemrazmer',
                    'P19_Pravsemkontur',
                    'P20_Pravsemparenkhima',
                    'P21_Pravsemobyemnobrazov',
                    'P22_Pridatokpravsemgolovka',
                    'P23_Pridatokpravsemtelo',
                    'P24_Pridatokpravsemobyemnobrazov',
                    'P25_Levsemrazmer',
                    'P26_Levsemkontur',
                    'P27_Levsemparenkhima',
                    'P28_Levsemobyemnobrazov',
                    'P29_Pridatoklevsemgolovka',
                    'P30_Pridatoklevsemtelo',
                    'P31_Pridatoklevsemobyemnobrazov',
                    'P32_Abdomultmserviceresult',
                ],
                'config' => [
                    'req_in' => [
                        'tech_name' => 'P0_Organsystem',
                        'value' => 'репродуктивная система самца',
                    ],
                    'report' => 'Ультразвуковое исследование репродуктивной системы самца',
                ],
            ],
        ];

        foreach ($data as $group) {
            $config = $group['config'];
            foreach ($group['params'] as $tech_name) {
                $this->db
                    ->createCommand()
                    ->update('{{%' . $this->tableName . '}}', ['config' => $config], ['tech_name' => $tech_name])
                    ->execute();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db
            ->createCommand()
            ->update('{{%' . $this->tableName . '}}', ['config' => null])
            ->execute();
    }
}
