<?php

use app\commands\migrate\Migration;
use app\models\db\Dictionaries;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\Reports;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m181206_035811_update_params_uzi
 */
class m181206_035811_update_params_uzi extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // новый параметр:
        // P0_Abdomenorgansystem;Система органов;dict;ultrasoundabdomenorgansystem;False

        $columns = [
            'tech_name' => 'P0_Abdomenorgansystem',
            'name' => 'Система органов',
            'datatype' => 'dict',
            'datatype_details' => 'ultrasoundabdomenorgansystem',
            'visit_flag' => false,
        ];

        $param = Params::findOne(['tech_name' => $columns['tech_name']]);
        if ($param === null) {
            $param = new Params($columns);
            $param->save();
        }

        if (empty($param)) {
            Console::output('Failed to create param ' . $columns['tech_name']);
            return ExitCode::DATAERR;
        }

        $id_param = $param->id;

        // значения для нового справочника:
        $values = [
            'мочевыделительная система',
            'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
            'репродуктивная система самки',
            'репродуктивная система самца',
        ];
        foreach ($values as $value) {
            $this->db->createCommand()
                ->insert(
                    Dictionaries::tableName(),
                    [
                        'name' => $value,
                        'type' => 'ultrasoundabdomenorgansystem',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                )
                ->execute();
        }

        // Для услуги "Ультразвуковой скрининг органов брюшной полости"
        // установена связь с параметром P0_Abdomenorgansystem вместо P0_Organsystem
        // Ультразвуковой скрининг органов брюшной полости;P0_Abdomenorgansystem;;True;False;1
        $service_name = 'Ультразвуковой скрининг органов брюшной полости';
        $govService = GovServices::findOne(['name' => $service_name]);
        if (empty($govService)) {
            Console::output('Service not found ' . $service_name);
            return ExitCode::DATAERR;
        }

        $oldParam = Params::findOne(['tech_name' => 'P0_Organsystem']);
        if (empty($oldParam)) {
            Console::output('Param not found ' . 'P0_Organsystem');
            return ExitCode::DATAERR;
        }

        $this->db
            ->createCommand()
            ->delete(
                GovServicesParams::tableName(),
                [
                    'id_param' => $oldParam->id,
                    'id_service' => $govService->id,
                ]
            )
            ->execute();

        $this->db
            ->createCommand()
            ->insert(
                GovServicesParams::tableName(),
                [
                    'id_param' => $id_param,
                    'id_service' => $govService->id,
                    'req_in' => true,
                    'req_out' => false,
                    'sort_by' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            )
            ->execute();

        /*
         * В ReportParam добавлены записи:
            УЗИ мочевыдел P0_Abdomenorgansystem
            УЗИ репр. Самки P0_Abdomenorgansystem
            УЗИ репр. Самца P0_Abdomenorgansystem
            УЗИ печ. и т.д P0_Abdomenorgansystem
         * */

        $reports = [
            'Ультразвуковое исследование мочевыделительной системы',
            'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
            'Ультразвуковое исследование репродуктивной системы самки',
            'Ультразвуковое исследование репродуктивной системы самца',
        ];

        foreach ($reports as $report) {
            $id_report = (new Query())
                ->select('id')
                ->from(Reports::tableName())
                ->where(['name' => $report])
                ->scalar();
            if (empty($id_report)) {
                Console::output('Report not found ' . $report);
                continue;
            }
            $this->db->createCommand()
                ->insert('public.reports_params', [
                    'id_param' => $id_param,
                    'id_report' => $id_report,
                    'created_at' => date('Y-m-d H:i:s')
                ])
                ->execute();
        }

        // обновляем конфиги для разных вариантов входящих
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
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Organsystem',
                            'value' => 'глаза',
                        ],
                        'report' => 'Ультразвуковое исследование глаза',
                    ],
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
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Organsystem',
                            'value' => 'мочевыделительная система',
                        ],
                        'report' => 'Ультразвуковое исследование мочевыделительной системы',
                    ],
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Abdomenorgansystem',
                            'value' => 'мочевыделительная система',
                        ],
                        'report' => 'Ультразвуковое исследование мочевыделительной системы',
                    ],
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
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Organsystem',
                            'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        ],
                        'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    ],
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Abdomenorgansystem',
                            'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        ],
                        'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    ],
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
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Organsystem',
                            'value' => 'репродуктивная система самки',
                        ],
                        'report' => 'Ультразвуковое исследование репродуктивной системы самки',
                    ],
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Abdomenorgansystem',
                            'value' => 'репродуктивная система самки',
                        ],
                        'report' => 'Ультразвуковое исследование репродуктивной системы самки',
                    ],
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
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Organsystem',
                            'value' => 'репродуктивная система самца',
                        ],
                        'report' => 'Ультразвуковое исследование репродуктивной системы самца',
                    ],
                    [
                        'req_in' => [
                            'tech_name' => 'P0_Abdomenorgansystem',
                            'value' => 'репродуктивная система самца',
                        ],
                        'report' => 'Ультразвуковое исследование репродуктивной системы самца',
                    ],
                ],
            ],
        ];

        foreach ($data as $group) {
            $config = $group['config'];
            foreach ($group['params'] as $tech_name) {
                $this->db
                    ->createCommand()
                    ->update(Params::tableName(), ['config' => $config], ['tech_name' => $tech_name])
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
}
