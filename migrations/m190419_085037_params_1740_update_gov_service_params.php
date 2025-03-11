<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m190419_085037_params_1740_update_gov_service_params
 */
class m190419_085037_params_1740_update_gov_service_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = $this->loadCsv();
        $items = $this->parseCsv($csv, __FUNCTION__);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV', [Console::FG_RED]));

            return false;
        }

        $this->resetSequences();

        $processed = 0;
        $ids = [];
        $servicesNotFound = 0;
        $paramsNotFound = 0;
        $paramsCreated = 0;
        $paramsCreateErrors = 0;
        $paramsUpdated = 0;
        $paramsUpdateErrors = 0;
        $paramsToRemove = [];
        $paramsRemoved = 0;
        $paramsRemoveErrors = 0;

        foreach ($items as $item) {
            // id_service;id_param;req_in;req_out;flag_in;flag_out;sort_by;№;
            $serviceName = trim($item[0]);
            $tech_name = trim($item[1]);
            $req_in = ($item[2] == '') ? false : ($item[2] === 'true' ? true : false);
            $req_out = ($item[3] == '') ? false : ($item[3] === 'true' ? true : false);
            $flag_in = ($item[4] == '') ? false : ($item[4] === 'true' ? true : false);
            $flag_out = ($item[5] == '') ? false : ($item[5] === 'true' ? true : false);
            $sort_by = (int)$item[6];

            $param = $this->findParamByTechName($tech_name);

            if ($param === null) {
                $paramsNotFound++;
                Console::output(Console::ansiFormat('Not found param [' . $tech_name . ']', [Console::FG_RED]));
                $processed++;
                continue;
            }

            $govService = $this->findGovServiceByName($serviceName);

            if ($govService === null) {
                $servicesNotFound++;
                Console::output(Console::ansiFormat('Not found service [' . $serviceName . ']', [Console::FG_RED]));
                $processed++;
                continue;
            }

            $columns = compact('req_in', 'req_out', 'flag_in', 'flag_out', 'sort_by');

            $gsp = $this->findGovServiceParam($param->id, $govService->id);

            if ($gsp === null) {
                // create gov_services_params record
                $columns['id_param'] = $param->id;
                $columns['id_service'] = $govService->id;
                Console::output(Console::ansiFormat('Creating gov_services_param for [' . $tech_name . '][' . $serviceName . ']...', [Console::FG_YELLOW]));
                $gsp = new GovServicesParams($columns);
                if ($gsp->save()) {
                    $ids[] = $gsp->id;
                    $paramsCreated++;
                    Console::output(Console::ansiFormat('   - OK', [Console::FG_GREEN]));
                } else {
                    $paramsCreateErrors++;
                    Console::output(Console::ansiFormat('   - Error', [Console::FG_RED]));
                }
                $processed++;
                continue;
            } else {
                // check to update gov_services_params record
                $ids[] = $gsp->id;
                $to_update = [];
                foreach ($columns as $column => $value) {
                    if ($gsp->$column !== $value) {
                        $to_update[$column] = $value;
                    }
                }
                if (empty($to_update)) {
                    $processed++;
                    continue;
                }

                $columns_to_update = array_keys($to_update);
                Console::output(Console::ansiFormat('Updating gov_services_param [' . $gsp->id . '] (' . implode(', ', $columns_to_update) . ') for [' . $tech_name . '][' . $serviceName . ']...', [Console::FG_YELLOW]));
                $gsp->load($to_update, '');
                if ($gsp->save(true, array_merge($columns_to_update, ['updated_at']))) {
                    $paramsUpdated++;
                    Console::output(Console::ansiFormat('   - OK', [Console::FG_GREEN]));
                } else {
                    $paramsUpdateErrors++;
                    Console::output(Console::ansiFormat('   - Error', [Console::FG_RED]));
                }
            }

            $processed++;
        }

        if (!empty($ids)) {
            $paramsToRemove = GovServicesParams::find()
                ->where(['not in', 'id', $ids])
                ->orderBy(['id' => SORT_ASC])
                ->all();

            if (!empty($paramsToRemove)) {
                foreach ($paramsToRemove as $gspr) {
                    Console::output(Console::ansiFormat('Removing gov_services_param [' . $gspr->id . ']...', [Console::FG_YELLOW]));
                    if (!$gspr->delete()) {
                        $paramsRemoveErrors++;
                        Console::output(Console::ansiFormat('   - Error', [Console::FG_RED]));
                    } else {
                        $paramsRemoved++;
                        Console::output(Console::ansiFormat('   - OK', [Console::FG_GREEN]));}
                }
            }
        }

        Console::output('Loaded params:' . count($items));
        Console::output('Processed params:' . $processed);
        Console::output('Not found params:' . $paramsNotFound);
        Console::output('Not found services:' . $servicesNotFound);
        Console::output(Console::ansiFormat('Created gov_services_params:' . $paramsCreated, [Console::FG_GREEN]));
        if (!empty($paramsCreateErrors)) {
            Console::output(Console::ansiFormat('Create errors:' . $paramsCreateErrors, [Console::FG_RED]));
        }
        Console::output(Console::ansiFormat('Updated gov_services_params:' . $paramsUpdated, [Console::FG_GREEN]));
        if (!empty($paramsUpdateErrors)) {
            Console::output(Console::ansiFormat('Update errors:' . $paramsUpdateErrors, [Console::FG_RED]));
        }
        Console::output('Params to remove:' . count($paramsToRemove));
        Console::output(Console::ansiFormat('Removed gov_services_params:' . $paramsRemoved, [Console::FG_GREEN]));
        if (!empty($paramsRemoveErrors)) {
            Console::output(Console::ansiFormat('Remove errors:' . $paramsRemoveErrors, [Console::FG_RED]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->resetSequences();
    }

    /**
     * @param string $csv
     * @param string $function
     * @return array
     * @throws \Exception
     */
    private function parseCsv($csv, $function)
    {
        $arr = [];
        $data = str_getcsv($csv, "\n");
        foreach ($data as $row) {
            $arr[] = str_getcsv($row, ';', '"');
        }

        if (empty($arr)) {
            throw new \Exception($function . ': Failed to parse CSV');
        }

        return $arr;
    }

    private function resetSequences()
    {
        $tables = [
            'gov_services_params',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
    }

    /**
     * @param string $tech_name
     * @return \app\models\db\Params|null
     */
    private function findParamByTechName($tech_name)
    {
        return Params::findOne(['tech_name' => $tech_name]);
    }

    /**
     * @param string $name
     * @return \app\models\db\GovServices|null
     */
    private function findGovServiceByName($name)
    {
        return GovServices::find()
            ->where(['name' => $name])
            ->andWhere([
                    'or',
                    ['type' => null],
                    ['not', ['type' => GovServices::TYPE_MOSRU]],
                ]
            )
            ->limit(1)
            ->one();
    }

    /**
     * @param int $id_param
     * @param int $id_service
     * @return \app\models\db\GovServicesParams|null
     */
    private function findGovServiceParam($id_param, $id_service)
    {
        return GovServicesParams::findOne([
            'id_param' => $id_param,
            'id_service' => $id_service
        ]);
    }

    private function loadCsv()
    {
        // id_service;id_param;req_in;req_out;flag_in;flag_out;sort_by;№;

        $csv = <<<CSV
Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы);P0_Venousbloodanalysisnum;false;true;false;true;1;7;
Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы);P22_Altalanniamvalue;false;false;false;true;10;8;
Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы);P23_Altalanniamvadesc;false;false;false;true;11;9;
Биохимические исследования крови - определение амилазы;P0_Venousbloodanalysisnum;false;true;false;true;1;10;
Биохимические исследования крови - определение амилазы;P36_Amilazavalue;false;false;false;true;24;11;
Биохимические исследования крови - определение амилазы;P37_Amilazadesc;false;false;false;true;25;12;
Биохимические исследования крови - определение амилазы панкреатической;P0_Venousbloodanalysisnum;false;true;false;true;1;13;
Биохимические исследования крови - определение амилазы панкреатической;P38_Pancreatinevalue;false;false;false;true;26;14;
Биохимические исследования крови - определение амилазы панкреатической;P39_Pancreatinedesc;false;false;false;true;27;15;
Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы);P0_Venousbloodanalysisnum;false;true;false;true;1;16;
Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы);P24_Astaspartvalue;false;false;false;true;12;17;
Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы);P25_Astaspartdesc;false;false;false;true;13;18;
Биохимические исследования крови - определение белковых фракций;P0_Venousbloodanalysisnum;false;true;false;true;1;19;
Биохимические исследования крови - определение белковых фракций;P100_Albumingdlvalue;false;false;false;true;88;20;
Биохимические исследования крови - определение белковых фракций;P101_Albumingdldesc;false;false;false;true;89;21;
Биохимические исследования крови - определение белковых фракций;P106_Proteinfractionsvalue;false;false;false;true;119;22;
Биохимические исследования крови - определение белковых фракций;P98_Albuminglvalue;false;false;false;true;86;23;
Биохимические исследования крови - определение белковых фракций;P99_Albumingldesc;false;false;false;true;87;24;
Биохимические исследования крови - определение гаммаглутамилтрансферазы;P0_Venousbloodanalysisnum;false;true;false;true;1;25;
Биохимические исследования крови - определение гаммаглутамилтрансферазы;P46_Lgtgammavalue;false;false;false;true;34;26;
Биохимические исследования крови - определение гаммаглутамилтрансферазы;P47_Lgtgammadesc;false;false;false;true;35;27;
Биохимические исследования крови - определение гемоглобина;P0_Venousbloodanalysisnum;false;true;false;true;1;28;
Биохимические исследования крови - определение гемоглобина;P102_Hemoglobinvalue;false;false;false;true;90;29;
Биохимические исследования крови - определение гемоглобина;P103_Hemoglobindesc;false;false;false;true;91;30;
Биохимические исследования крови - определение глюкозы;P0_Venousbloodanalysisnum;false;true;false;true;1;31;
Биохимические исследования крови - определение глюкозы;P40_Glukozamcmvalue;false;false;false;true;28;32;
Биохимические исследования крови - определение глюкозы;P41_Glukozamcmdesc;false;false;false;true;29;33;
Биохимические исследования крови - определение глюкозы;P42_Glukozamgvalue;false;false;false;true;30;34;
Биохимические исследования крови - определение глюкозы;P43_Glukozamgdesc;false;false;false;true;31;35;
Биохимические исследования крови - определение железа;P0_Venousbloodanalysisnum;false;true;false;true;1;36;
Биохимические исследования крови - определение железа;P74_Ironmcmvalue;false;false;false;true;62;37;
Биохимические исследования крови - определение железа;P75_Ironmcmdesc;false;false;false;true;63;38;
Биохимические исследования крови - определение железа;P76_Ironmcgvalue;false;false;false;true;64;39;
Биохимические исследования крови - определение железа;P77_Ironmcgdesc;false;false;false;true;65;40;
Биохимические исследования крови - определение калия;P0_Venousbloodanalysisnum;false;true;false;true;1;41;
Биохимические исследования крови - определение калия;P58_Caliummmvalue;false;false;false;true;46;42;
Биохимические исследования крови - определение калия;P59_Caliummmdesc;false;false;false;true;47;43;
Биохимические исследования крови - определение калия;P60_Caliummecvalue;false;false;false;true;48;44;
Биохимические исследования крови - определение калия;P61_Caliummecdesc;false;false;false;true;49;45;
Биохимические исследования крови - определение кальция;P0_Venousbloodanalysisnum;false;true;false;true;1;46;
Биохимические исследования крови - определение кальция;P70_Calciummmvalue;false;false;false;true;58;47;
Биохимические исследования крови - определение кальция;P71_Calciummmdesc;false;false;false;true;59;48;
Биохимические исследования крови - определение кальция;P72_Calciummcgvalue;false;false;false;true;60;49;
Биохимические исследования крови - определение кальция;P73_Calciummcgdesc;false;false;false;true;61;50;
Биохимические исследования крови - определение креатинина;P0_Venousbloodanalysisnum;false;true;false;true;1;51;
Биохимические исследования крови - определение креатинина;P30_Creatininemcmvalue;false;false;false;true;18;52;
Биохимические исследования крови - определение креатинина;P31_Creatininemcmdesc;false;false;false;true;19;53;
Биохимические исследования крови - определение креатинина;P32_Creatininemgvalue;false;false;false;true;20;54;
Биохимические исследования крови - определение креатинина;P33_Creatininemgdesc;false;false;false;true;21;55;
Биохимические исследования крови - определение креатинкиназы;P0_Venousbloodanalysisnum;false;true;false;true;1;56;
Биохимические исследования крови - определение креатинкиназы;P48_Kfkcreatinevalue;false;false;false;true;36;57;
Биохимические исследования крови - определение креатинкиназы;P49_Kfkcreatinedesc;false;false;false;true;37;58;
Биохимические исследования крови - определение лактатдегидрогеназы;P0_Venousbloodanalysisnum;false;true;false;true;1;59;
Биохимические исследования крови - определение лактатдегидрогеназы;P44_Ldglactodvalue;false;false;false;true;32;60;
Биохимические исследования крови - определение лактатдегидрогеназы;P45_Ldglactoddesc;false;false;false;true;33;61;
Биохимические исследования крови - определение липазы;P0_Venousbloodanalysisnum;false;true;false;true;1;62;
Биохимические исследования крови - определение липазы;P92_Lipazavalue;false;false;false;true;80;63;
Биохимические исследования крови - определение липазы;P93_Lipazadesc;false;false;false;true;81;64;
Биохимические исследования крови - определение магния;P0_Venousbloodanalysisnum;false;true;false;true;1;65;
Биохимические исследования крови - определение магния;P78_Magnesiummmvalue;false;false;false;true;66;66;
Биохимические исследования крови - определение магния;P79_Magnesiummmdesc;false;false;false;true;67;67;
Биохимические исследования крови - определение магния;P80_Magnesiummecvalue;false;false;false;true;68;68;
Биохимические исследования крови - определение магния;P81_Magnesiummecdesc;false;false;false;true;69;69;
Биохимические исследования крови - определение мочевины;P0_Venousbloodanalysisnum;false;true;false;true;1;70;
Биохимические исследования крови - определение мочевины;P104_Ketonebodiesvalue;false;false;false;true;117;71;
Биохимические исследования крови - определение мочевины;P26_Mochevinammvalue;false;false;false;true;14;72;
Биохимические исследования крови - определение мочевины;P27_Mochevinammdesc;false;false;false;true;15;73;
Биохимические исследования крови - определение мочевины;P28_Mochevinamgvalue;false;false;false;true;16;74;
Биохимические исследования крови - определение мочевины;P29_Mochevinamgdesc;false;false;false;true;17;75;
Биохимические исследования крови - определение мочевой кислоты;P0_Venousbloodanalysisnum;false;true;false;true;1;76;
Биохимические исследования крови - определение мочевой кислоты;P105_Bikarbonatvalue;false;false;false;true;118;77;
Биохимические исследования крови - определение мочевой кислоты;P88_Mochekislnmvalue;false;false;false;true;76;78;
Биохимические исследования крови - определение мочевой кислоты;P89_Mochekislnmdesc;false;false;false;true;77;79;
Биохимические исследования крови - определение мочевой кислоты;P90_Mochekislmgvalue;false;false;false;true;78;80;
Биохимические исследования крови - определение мочевой кислоты;P91_Mochekislmgdesc;false;false;false;true;79;81;
Биохимические исследования крови - определение натрия;P0_Venousbloodanalysisnum;false;true;false;true;1;82;
Биохимические исследования крови - определение натрия;P62_Natrmmvalue;false;false;false;true;50;83;
Биохимические исследования крови - определение натрия;P63_Natrmmdesc;false;false;false;true;51;84;
Биохимические исследования крови - определение натрия;P64_Natrmecvalue;false;false;false;true;52;85;
Биохимические исследования крови - определение натрия;P65_Natrmecdesc;false;false;false;true;53;86;
Биохимические исследования крови - определение общего белка;P0_Venousbloodanalysisnum;false;true;false;true;1;87;
Биохимические исследования крови - определение общего белка;P82_Chloridemmvalue;false;false;false;true;70;88;
Биохимические исследования крови - определение общего белка;P83_Chloridemmdesc;false;false;false;true;71;89;
Биохимические исследования крови - определение общего белка;P84_Chloridemecvalue;false;false;false;true;72;90;
Биохимические исследования крови - определение общего белка;P85_Chloridemecdesc;false;false;false;true;73;91;
Биохимические исследования крови - определение общего белка;P86_Acidityvalue;false;false;false;true;74;92;
Биохимические исследования крови - определение общего белка;P87_Aciditydesc;false;false;false;true;75;93;
Биохимические исследования крови - определение общего белка;P94_Totalproteinglvalue;false;false;false;true;82;94;
Биохимические исследования крови - определение общего белка;P95_Totalproteingldesc;false;false;false;true;83;95;
Биохимические исследования крови - определение общего белка;P96_Totalproteingdlvalue;false;false;false;true;84;96;
Биохимические исследования крови - определение общего белка;P97_Totalproteingdldesc;false;false;false;true;85;97;
Биохимические исследования крови - определение общего билирубина;P0_Venousbloodanalysisnum;false;true;false;true;1;98;
Биохимические исследования крови - определение общего билирубина;P14_Totalbilirubinmcmvalue;false;false;false;true;2;99;
Биохимические исследования крови - определение общего билирубина;P15_Totalbilirubinmcmdesc;false;false;false;true;3;100;
Биохимические исследования крови - определение общего билирубина;P16_Totalbilirubinmgvalue;false;false;false;true;4;101;
Биохимические исследования крови - определение общего билирубина;P17_Totalbilirubinmgdesc;false;false;false;true;5;102;
Биохимические исследования крови - определение общего билирубина;P18_Conjugatedbilirubinmcmvalue;false;false;false;true;6;103;
Биохимические исследования крови - определение общего билирубина;P19_Conjugatedbilirubinmcmdesc;false;false;false;true;7;104;
Биохимические исследования крови - определение общего билирубина;P20_Conjugatedbilirubinmgvalue;false;false;false;true;8;105;
Биохимические исследования крови - определение общего билирубина;P21_Conjugatedbilirubinmgdesc;false;false;false;true;9;106;
Биохимические исследования крови - определение общего холестерина;P0_Venousbloodanalysisnum;false;true;false;true;1;107;
Биохимические исследования крови - определение общего холестерина;P50_Holestermmvalue;false;false;false;true;38;108;
Биохимические исследования крови - определение общего холестерина;P51_Holestermmdesc;false;false;false;true;39;109;
Биохимические исследования крови - определение общего холестерина;P52_Holestermgvalue;false;false;false;true;40;110;
Биохимические исследования крови - определение общего холестерина;P53_Holestermgdesc;false;false;false;true;41;111;
Биохимические исследования крови - определение триглицеридов;P0_Venousbloodanalysisnum;false;true;false;true;1;112;
Биохимические исследования крови - определение триглицеридов;P54_Triglyceridsmmvalue;false;false;false;true;42;113;
Биохимические исследования крови - определение триглицеридов;P55_Triglyceridsmmdesc;false;false;false;true;43;114;
Биохимические исследования крови - определение триглицеридов;P56_Triglyceridsmgvalue;false;false;false;true;44;115;
Биохимические исследования крови - определение триглицеридов;P57_Triglyceridsmgdesc;false;false;false;true;45;116;
Биохимические исследования крови - определение фосфора неорганического;P0_Venousbloodanalysisnum;false;true;false;true;1;117;
Биохимические исследования крови - определение фосфора неорганического;P66_Phosphormmvalue;false;false;false;true;54;118;
Биохимические исследования крови - определение фосфора неорганического;P67_Phosphormmdesc;false;false;false;true;55;119;
Биохимические исследования крови - определение фосфора неорганического;P68_Phosphormgvalue;false;false;false;true;56;120;
Биохимические исследования крови - определение фосфора неорганического;P69_Phosphormgdesc;false;false;false;true;57;121;
Биохимические исследования крови - определение щелочной фосфатазы;P0_Venousbloodanalysisnum;false;true;false;true;1;122;
Биохимические исследования крови - определение щелочной фосфатазы;P34_Shelochfosfatvalue;false;false;false;true;22;123;
Биохимические исследования крови - определение щелочной фосфатазы;P35_Shelochfosfatdesc;false;false;false;true;23;124;
Биркование сельскохозяйственных животных;P0_Petlabelidentificationcode;false;true;false;true;1;125;
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P13_Servicetext;false;true;false;true;4;129;
Ветеринарное освидетельствование животных для оформления ветеринарных сопроводительных документов, включающая проведение клинического осмотра и изучение ветеринарных документов (паспорта на животное, результатов лабораторных исследований и др.) - с гельминтокопрологическим исследованием;P0_Coproalysisnum;false;true;false;true;1;132;
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P15_Vacexpirationdate;false;true;false;true;7;133;
Взвешивание животных;P0_Petweight;false;false;false;true;1;134;
Взятие мазка отпечатка на цитологический анализ;P0_Cytologicsanalysisnum;false;true;false;true;1;135;
Взятие проб крови из вены;P0_Venousbloodanalysisnum;false;true;false;true;1;136;
Взятие проб крови из капилляра;P0_Capillarybloodanalysisnum;false;true;false;true;1;137;
Гельминтокопрологические исследования;P0_Coproalysisnum;false;true;false;true;1;140;
Взятие соскобов, мазков, смывов для диагностических исследований;P0_Diagnostictestinganalysisnum;false;true;false;true;1;141;
Гельминтокопрологические исследования;P12_Serviceresultvalue;false;false;false;true;2;142;
Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см);P0_Petweight;false;false;false;true;1;143;
Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см);P0_Petwoollength;false;true;false;true;1;144;
Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см);P0_Petweight;false;false;false;true;1;145;
Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см);P0_Petwoollength;false;true;false;true;1;146;
Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см);P0_Petweight;false;false;false;true;1;147;
Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см);P0_Petwoollength;false;true;false;true;1;148;
Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см);P0_Petweight;false;false;false;true;1;149;
Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см);P0_Petwoollength;false;true;false;true;1;150;
Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см);P0_Petweight;false;false;false;true;1;151;
Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см);P0_Petwoollength;false;true;false;true;1;152;
Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см);P0_Petweight;false;false;false;true;1;153;
Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см);P0_Petwoollength;false;true;false;true;1;154;
Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см);P0_Petweight;false;false;false;true;1;155;
Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см);P0_Petwoollength;false;true;false;true;1;156;
Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см);P0_Petweight;false;false;false;true;1;157;
Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см);P0_Petwoollength;false;true;false;true;1;158;
Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см);P0_Petweight;false;false;false;true;1;159;
Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см);P0_Petwoollength;false;true;false;true;1;160;
Исследование на кровепаразитарные болезни;P12_Analysisnum;false;true;false;true;1;161;
Исследование на кровепаразитарные болезни;P14_Analysiscount;false;false;false;true;2;162;
Гельминтокопрологические исследования;P13_Serviceresultdesc;false;false;false;true;3;163;
Исследование на кровепаразитарные болезни;P15_Activator;false;false;false;true;18;164;
Исследование на кровепаразитарные болезни;P15_Analysisresult;false;false;false;true;3;165;
Исследование на кровепаразитарные болезни;P18_Analysisdate;false;false;false;true;5;166;
Исследование на кровепаразитарные болезни;P16_Analysisdesc;false;false;false;true;4;167;
Исследование на кровепаразитарные болезни;P19_Animalcount;false;false;false;true;29;168;
Исследование на кровепаразитарные болезни;P20_Analysisobject;false;false;false;true;28;169;
Исследование на кровепаразитарные болезни;P21_Diagnostictechnique;false;false;false;true;27;170;
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг;P0_Petweight;false;false;false;true;3;171;
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг;P0_Petweight;false;false;false;true;3;172;
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг;P0_Petweight;false;false;false;true;3;173;
Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг;P0_Petweight;false;false;false;true;2;174;
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг;P0_Petweight;false;false;false;true;2;175;
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг;P0_Petweight;false;false;false;true;2;176;
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг;P0_Petweight;false;false;false;true;2;177;
Компьютерная томография без введения контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_Investigationarea;true;false;true;false;1;191;
Компьютерная томография без введения контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_Investigationarea;true;false;true;false;1;192;
Компьютерная томография с введением контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_Investigationarea;true;false;true;false;1;193;
Исследование на кровепаразитарные болезни;P22_Parasdiseasesanswerdate;false;false;false;true;25;194;
Люминесцентная диагностика на микроспорию с применением лампы Вуда;P12_Serviceresultvalue;false;false;false;true;1;195;
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - голова, отделы конечностей (сустав, регион конечности);P0_Investigationarea;true;false;true;false;1;196;
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_Investigationarea;true;false;true;false;1;197;
Компьютерная томография с введением контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_Investigationarea;true;false;true;false;1;198;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - до 5 кг;P0_Petweight;false;false;false;true;1;202;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 10 до 20 кг;P0_Petweight;false;false;false;true;1;203;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 20 до 30 кг;P0_Petweight;false;false;false;true;1;204;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 30 до 40 кг;P0_Petweight;false;false;false;true;1;205;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 40 до 50 кг;P0_Petweight;false;false;false;true;1;206;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 5 до 10 кг;P0_Petweight;false;false;false;true;1;207;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 50 до 60 кг;P0_Petweight;false;false;false;true;1;208;
Люминесцентная диагностика на микроспорию с применением лампы Вуда;P13_Serviceresultdesc;false;false;false;true;2;209;
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P15_Analysisresult;false;false;false;true;3;210;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 60 до 70 кг;P0_Petweight;false;false;false;true;1;211;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 70 до 80 кг;P0_Petweight;false;false;false;true;1;212;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 80 до 90 кг;P0_Petweight;false;false;false;true;1;213;
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 90 до 100 кг;P0_Petweight;false;false;false;true;1;214;
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P12_Analysisnum;false;false;false;true;1;215;
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P14_Analysiscount;false;false;false;true;2;216;
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P16_Analysisdesc;false;false;false;true;4;217;
Общий анализ кала;P14_Coprformvalue;false;false;false;true;2;218;
Общий анализ кала;P15_Coprformdesc;false;false;false;true;3;219;
Общий анализ кала;P16_Coprcolorvalue;false;false;false;true;4;220;
Общий анализ кала;P17_Coprcolordesc;false;false;false;true;5;221;
Общий анализ кала;P18_Coprodorvalue;false;false;false;true;6;222;
Общий анализ кала;P19_Coprodordesc;false;false;false;true;7;223;
Общий анализ кала;P20_Acidityvalue;false;false;false;true;8;224;
Общий анализ кала;P21_Aciditydesc;false;false;false;true;9;225;
Общий анализ кала;P22_Stercobilinvalue;false;false;false;true;10;226;
Общий анализ кала;P23_Stercobilindesc;false;false;false;true;11;227;
Общий анализ кала;P24_Bilirubinvalue;false;false;false;true;12;228;
Общий анализ кала;P25_Bilirubindesc;false;false;false;true;13;229;
Общий анализ кала;P26_Bloodvalue;false;false;false;true;14;230;
Общий анализ кала;P27_Blooddesc;false;false;false;true;15;231;
Общий анализ кала;P28_Muscledfibersvalue;false;false;false;true;16;232;
Общий анализ кала;P29_Muscledfibersdesc;false;false;false;true;17;233;
Общий анализ кала;P30_Contissuefibersvalue;false;false;false;true;18;234;
Общий анализ кала;P31_Contissuefibersdesc;false;false;false;true;19;235;
Общий анализ кала;P32_Neutralfatvalue;false;false;false;true;20;236;
Общий анализ кала;P33_Neutralfatdesc;false;false;false;true;21;237;
Общий анализ кала;P34_Fattyacidsvalue;false;false;false;true;22;238;
Общий анализ кала;P35_Fattyacidsdesc;false;false;false;true;23;239;
Общий анализ кала;P36_Soapvalue;false;false;false;true;24;240;
Общий анализ кала;P37_Soapdesc;false;false;false;true;25;241;
Общий анализ кала;P38_Starchvalue;false;false;false;true;26;242;
Общий анализ кала;P39_Coproanswerdate;false;false;false;true;;243;
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P18_Analysisdate;false;false;false;true;5;244;
Общий анализ кала;P39_Starchdesc;false;false;false;true;27;245;
Общий анализ мочи;P14_Colorurinevalue;false;false;false;true;2;246;
Общий анализ мочи;P15_Colorurinedesc;false;false;false;true;3;247;
Общий анализ мочи;P16_Transparencyvalue;false;false;false;true;4;248;
Общий анализ мочи;P17_Transparencydesc;false;false;false;true;5;249;
Общий анализ мочи;P18_Acidityvalue;false;false;false;true;6;250;
Общий анализ мочи;P19_Aciditydesc;false;false;false;true;7;251;
Общий анализ мочи;P20_Proteinvalue;false;false;false;true;8;252;
Общий анализ мочи;P21_Proteindesc;false;false;false;true;9;253;
Общий анализ мочи;P22_Glukozavalue;false;false;false;true;10;254;
Общий анализ мочи;P23_Glukozadesc;false;false;false;true;11;255;
Общий анализ мочи;P24_Ketonbodvalue;false;false;false;true;12;256;
Общий анализ мочи;P25_Ketonboddesc;false;false;false;true;13;257;
Общий анализ мочи;P26_Relativedensityvalue;false;false;false;true;14;258;
Общий анализ мочи;P27_Relativedensitydesc;false;false;false;true;15;259;
Общий анализ мочи;P28_Bilirubinvalue;false;false;false;true;16;260;
Общий анализ мочи;P29_Bilirubindesc;false;false;false;true;17;261;
Общий анализ мочи;P30_Hemeglvalue;false;false;false;true;18;262;
Общий анализ мочи;P31_Hemegldesc;false;false;false;true;19;263;
Общий анализ мочи;P32_Erythrocytvalue;false;false;false;true;20;264;
Общий анализ мочи;P33_Erythrocytdesc;false;false;false;true;21;265;
Общий анализ мочи;P34_Leucocytvalue;false;false;false;true;22;266;
Общий анализ мочи;P35_Leucocytdesc;false;false;false;true;23;267;
Общий анализ мочи;P36_Ploskiyvalue;false;false;false;true;24;268;
Общий анализ мочи;P37_Ploskiydesc;false;false;false;true;25;269;
Общий анализ мочи;P38_Perehodvalue;false;false;false;true;26;270;
Общий анализ мочи;P39_Perehoddesc;false;false;false;true;27;271;
Общий анализ мочи;P40_Pochechnvalue;false;false;false;true;28;272;
Общий анализ мочи;P41_Pochechndesc;false;false;false;true;29;273;
Общий анализ мочи;P42_Hyalinevalue;false;false;false;true;30;274;
Общий анализ мочи;P43_Hyalinedesc;false;false;false;true;31;275;
Общий анализ мочи;P44_Granularvalue;false;false;false;true;32;276;
Общий анализ мочи;P45_Granulardesc;false;false;false;true;33;277;
Общий анализ мочи;P46_Waxvalue;false;false;false;true;34;278;
Общий анализ мочи;P47_Waxdesc;false;false;false;true;35;279;
Общий анализ мочи;P48_Lekocitvalue;false;false;false;true;36;280;
Общий анализ мочи;P49_Lekocitdesc;false;false;false;true;37;281;
Общий анализ мочи;P50_Eritrocitvalue;false;false;false;true;38;282;
Общий анализ мочи;P51_Eritrocitdesc;false;false;false;true;39;283;
Общий анализ мочи;P52_Epitelvalue;false;false;false;true;40;284;
Общий анализ мочи;P53_Epiteldesc;false;false;false;true;41;285;
Общий анализ мочи;P54_Cilindvalue;false;false;false;true;42;286;
Общий анализ мочи;P55_Cilinddesc;false;false;false;true;43;287;
Общий анализ мочи;P56_Bacteriavalue;false;false;false;true;44;288;
Общий анализ мочи;P57_Bacteriadesc;false;false;false;true;45;289;
Общий анализ мочи;P58_Saltvalue;false;false;false;true;46;290;
Общий анализ мочи;P59_Saltdesc;false;false;false;true;47;291;
Общий анализ мочи;P61_Urinunitweightvalue;false;false;false;true;48;292;
Общий анализ мочи;P62_Urinreactionvalue;false;false;false;true;49;293;
Общий анализ мочи;P63_Nitratvalue;false;false;false;true;50;294;
Общий анализ мочи;P64_Urinorddepositionvalue;false;false;false;true;51;295;
Общий анализ мочи;P65_Urindisorddepositionvalue;false;false;false;true;52;296;
Общий анализ мочи;P66_UrinAnswerdate;false;false;false;true;53;297;
Наложение гипсовой повязки (без репозиции) - крупные породы собак;P0_Petweight;false;false;false;true;1;298;
Общий анализ мочи;P67_Urinconsistency;false;false;false;true;54;299;
Наложение гипсовой повязки (без репозиции) - мелкие породы собак и кошки;P0_Petweight;false;false;false;true;1;300;
Общий анализ кала;P0_Coproalysisnum;false;true;false;true;1;303;
Общий клинический анализ крови - подсчет лейкоцитов;P14_Wbcvalue;false;false;false;true;2;304;
Общий клинический анализ крови - подсчет лейкоцитов;P15_Wbcdesc;false;false;false;true;3;305;
Общий клинический анализ крови - подсчет лейкоцитов;P16_Lymvalue;false;false;false;true;4;306;
Общий клинический анализ крови - подсчет лейкоцитов;P17_Lymdesc;false;false;false;true;5;307;
Общий клинический анализ крови - подсчет лейкоцитов;P18_Monvalue;false;false;false;true;6;308;
Общий клинический анализ крови - подсчет лейкоцитов;P19_Mondesc;false;false;false;true;7;309;
Общий клинический анализ крови - подсчет лейкоцитов;P20_Gravalue;false;false;false;true;8;310;
Общий клинический анализ крови - подсчет лейкоцитов;P21_Gradesc;false;false;false;true;9;311;
Общий клинический анализ крови - подсчет эритроцитов;P22_Rbcvalue;false;false;false;true;10;312;
Общий клинический анализ крови - подсчет эритроцитов;P23_Rbcdesc;false;false;false;true;11;313;
Общий клинический анализ крови - определение гемоглобина;P24_Hgbvalue;false;false;false;true;12;314;
Общий клинический анализ крови - определение гемоглобина;P25_Hgbdesc;false;false;false;true;13;315;
Общий клинический анализ крови - определение гемоглобина;P26_Hctvalue;false;false;false;true;14;316;
Общий клинический анализ крови - определение гемоглобина;P27_Hctdesc;false;false;false;true;15;317;
Общий клинический анализ крови - подсчет эритроцитов;P28_Mcvvalue;false;false;false;true;16;318;
Общий клинический анализ крови - подсчет эритроцитов;P29_Mcvdesc;false;false;false;true;17;319;
Общий клинический анализ крови - определение гемоглобина;P30_Mchvalue;false;false;false;true;18;320;
Общий клинический анализ крови - определение гемоглобина;P31_Mchdesc;false;false;false;true;19;321;
Общий клинический анализ крови - определение гемоглобина;P32_Mchcvalue;false;false;false;true;20;322;
Общий клинический анализ крови - определение гемоглобина;P33_Mchcdesc;false;false;false;true;21;323;
Общий клинический анализ крови - подсчет эритроцитов;P34_Rdwvalue;false;false;false;true;22;324;
Общий клинический анализ крови - подсчет эритроцитов;P35_Rdwdesc;false;false;false;true;23;325;
Общий клинический анализ крови - подсчет эритроцитов;P36_Pltvalue;false;false;false;true;24;326;
Общий клинический анализ крови - подсчет эритроцитов;P37_Pltdesc;false;false;false;true;25;327;
Общий клинический анализ крови - подсчет эритроцитов;P38_Mpvvalue;false;false;false;true;26;328;
Общий клинический анализ крови - подсчет эритроцитов;P39_Mpvdesc;false;false;false;true;27;329;
Общий клинический анализ крови - подсчет эритроцитов;P40_Pctvalue;false;false;false;true;28;330;
Общий клинический анализ крови - подсчет эритроцитов;P41_Pctdesc;false;false;false;true;29;331;
Общий клинический анализ крови - подсчет эритроцитов;P42_Pdwvalue;false;false;false;true;30;332;
Общий клинический анализ крови - подсчет эритроцитов;P43_Pdwdesc;false;false;false;true;31;333;
Общий клинический анализ крови - определение СОЭ;P44_Soevalue;false;false;false;true;32;334;
Общий клинический анализ крови - определение СОЭ;P45_Soedesc;false;false;false;true;33;335;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P46_Youngvalue;false;false;false;true;34;336;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P47_Youngdesc;false;false;false;true;35;337;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P48_Palochkoyadervalue;false;false;false;true;36;338;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P49_Palochkoyaderdesc;false;false;false;true;37;339;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P50_Segmentvalue;false;false;false;true;38;340;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P51_Segmentdesc;false;false;false;true;39;341;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P52_Eosinophilsvalue;false;false;false;true;40;342;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P53_Eosinophilsdesc;false;false;false;true;41;343;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P54_Monocitvalue;false;false;false;true;42;344;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P55_Monocitdesc;false;false;false;true;43;345;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P56_Bazophilvalue;false;false;false;true;44;346;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P57_Bazophildesc;false;false;false;true;45;347;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P58_Limphocitvalue;false;false;false;true;46;348;
Общий анализ мочи;P0_Urinalysisnum;false;true;false;true;1;349;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P59_Limphocitdesc;false;false;false;true;47;350;
Определение гормонов в сыворотке крови - кортизол;P14_Cortisolbazalvalue;false;false;false;true;2;351;
Определение гормонов в сыворотке крови - кортизол;P15_Cortisolactgvalue;false;false;false;true;3;352;
Общий клинический анализ крови - выведение лейкоцитарной формулы;P0_Venousbloodanalysisnum;false;true;false;true;1;353;
Определение гормонов в сыворотке крови - кортизол;P16_Cortisoldexvalue;false;false;false;true;4;354;
Определение гормонов в сыворотке крови - прогестерон;P17_Proganesvalue;false;false;false;true;5;355;
Определение гормонов в сыворотке крови - прогестерон;P18_Progproenstvalue;false;false;false;true;6;356;
Определение гормонов в сыворотке крови - прогестерон;P19_Progestrusvalue;false;false;false;true;7;357;
Общий клинический анализ крови - определение гемоглобина;P0_Venousbloodanalysisnum;false;true;false;true;1;358;
Определение гормонов в сыворотке крови - прогестерон;P20_Progmetestvalue;false;false;false;true;8;359;
Общий клинический анализ крови - определение СОЭ;P0_Venousbloodanalysisnum;false;true;false;true;1;360;
Определение гормонов в сыворотке крови - тестостерон;P26_Testostervalue;false;false;false;true;14;361;
Общий клинический анализ крови - подсчет лейкоцитов;P0_Venousbloodanalysisnum;false;true;false;true;1;362;
Определение гормонов в сыворотке крови - тироксин;P27_Thyroxvalue;false;false;false;true;15;363;
Общий клинический анализ крови - подсчет эритроцитов;P0_Venousbloodanalysisnum;false;true;false;true;1;364;
Определение гормонов в сыворотке крови - трийодтиронин;P28_Triiodtirvalue;false;false;false;true;16;365;
Определение гормонов в сыворотке крови - эстрадиол;P21_Estradanesvalue;false;false;false;true;9;366;
Определение гормонов в сыворотке крови - эстрадиол;P22_Estradproenstvalue;false;false;false;true;10;367;
Определение гормонов в сыворотке крови - эстрадиол;P23_Estradestrusvalue;false;false;false;true;11;368;
Определение гормонов в сыворотке крови - эстрадиол;P24_Estradmetestvalue;false;false;false;true;12;369;
Определение гормонов в сыворотке крови - эстрадиол;P25_Estradmalevalue;false;false;false;true;13;370;
Определение гормонов в сыворотке крови - кортизол;P0_Venousbloodanalysisnum;false;true;false;true;1;371;
Определение слезопродукции при диагностике глаз (тест Ширмера);P0_Schirmertestresult;false;true;false;true;1;372;
Повторное ультразвуковое исследование;P14_Matkadiametrtela;false;false;false;true;1;373;
Повторное ультразвуковое исследование;P14_Odrazmerperednegootrezka;false;false;false;true;2;374;
Повторное ультразвуковое исследование;P14_Pechenraspoloshenie;false;false;false;true;1;375;
Повторное ультразвуковое исследование;P14_Predstzhelezarazmer;false;false;false;true;1;376;
Повторное ультразвуковое исследование;P14_Prvpochraspoloshenie;false;false;false;true;1;377;
Повторное ультразвуковое исследование;P15_Matkatolshinatela;false;false;false;true;2;378;
Повторное ультразвуковое исследование;P15_Odrazmerzadnegootrezka;false;false;false;true;3;379;
Повторное ультразвуковое исследование;P15_Pechenkontur;false;false;false;true;2;380;
Повторное ультразвуковое исследование;P15_Predstzhelezakontur;false;false;false;true;2;381;
Повторное ультразвуковое исследование;P15_Prvpochgranica;false;false;false;true;2;382;
Повторное ультразвуковое исследование;P16_Matkastrukturastenkitela;false;false;false;true;3;383;
Повторное ультразвуковое исследование;P16_Odstructuraperedcamer;false;false;false;true;4;384;
Повторное ультразвуковое исследование;P16_Pechenrazmer;false;false;false;true;3;385;
Повторное ультразвуковое исследование;P16_Predstzhelezaparenkhima;false;false;false;true;3;386;
Повторное ультразвуковое исследование;P16_Prvpochrazmer;false;false;false;true;3;387;
Повторное ультразвуковое исследование;P17_Matkasostoyanpolosti;false;false;false;true;4;388;
Повторное ультразвуковое исследование;P17_Odrazmerhrust;false;false;false;true;5;389;
Повторное ультразвуковое исследование;P17_Pechenekhostruktura;false;false;false;true;4;390;
Повторное ультразвуковое исследование;P17_Predstzhelezaobyemnobrazov;false;false;false;true;4;391;
Повторное ультразвуковое исследование;P17_Prvpochkortiksloytolshina;false;false;false;true;4;392;
Повторное ультразвуковое исследование;P18_Matkadiametrpravroga;false;false;false;true;5;393;
Повторное ультразвуковое исследование;P18_Odstructurahrust;false;false;false;true;6;394;
Повторное ультразвуковое исследование;P18_Pechenekhogennost;false;false;false;true;5;395;
Повторное ультразвуковое исследование;P18_Pravsemrazmer;false;false;false;true;5;396;
Повторное ультразвуковое исследование;P18_Prvpochkortiksloyekhogennost;false;false;false;true;5;397;
Повторное ультразвуковое исследование;P19_Matkatolshinapravroga;false;false;false;true;6;398;
Повторное ультразвуковое исследование;P19_Odcapsulahrust;false;false;false;true;7;399;
Повторное ультразвуковое исследование;P19_Pechenperifsosudrisunok;false;false;false;true;6;400;
Повторное ультразвуковое исследование;P19_Pravsemkontur;false;false;false;true;6;401;
Повторное ультразвуковое исследование;P19_Prvpochkortiksloyekhostruktura;false;false;false;true;6;402;
Повторное ультразвуковое исследование;P20_Matkastrukturastenkipravroga;false;false;false;true;7;403;
Повторное ультразвуковое исследование;P20_Odstructurasteklotelo;false;false;false;true;8;404;
Повторное ультразвуковое исследование;P20_Pechenportae;false;false;false;true;7;405;
Повторное ультразвуковое исследование;P20_Pravsemparenkhima;false;false;false;true;7;406;
Повторное ультразвуковое исследование;P20_Prvpochmedullyarsloytolshchina;false;false;false;true;7;407;
Повторное ультразвуковое исследование;P21_Matkasoderzhimpolostipravroga;false;false;false;true;8;408;
Повторное ультразвуковое исследование;P21_Oddiametrzrachka;false;false;false;true;9;409;
Повторное ультразвуковое исследование;P21_Pechenvhepatica;false;false;false;true;8;410;
Повторное ультразвуковое исследование;P21_Pravsemobyemnobrazov;false;false;false;true;8;411;
Повторное ультразвуковое исследование;P21_Prvpochmedullyarsloyekhogennost;false;false;false;true;8;412;
Повторное ультразвуковое исследование;P22_Matkadiametrlevroga;false;false;false;true;9;413;
Повторное ультразвуковое исследование;P22_Odcontur;false;false;false;true;10;414;
Повторное ультразвуковое исследование;P22_Pechenahepatica;false;false;false;true;9;415;
Повторное ультразвуковое исследование;P22_Pridatokpravsemgolovka;false;false;false;true;9;416;
Повторное ультразвуковое исследование;P22_Prvpochmedullyarsloyekhostruktura;false;false;false;true;9;417;
Повторное ультразвуковое исследование;P23_Matkatolshinalevroga;false;false;false;true;10;418;
Повторное ультразвуковое исследование;P23_Odstructura;false;false;false;true;11;419;
Повторное ультразвуковое исследование;P23_Pechenobyemnobrazov;false;false;false;true;10;420;
Повторное ультразвуковое исследование;P23_Pridatokpravsemtelo;false;false;false;true;10;421;
Повторное ультразвуковое исследование;P23_Prvpochmedullyarsloykortmeddiffer;false;false;false;true;10;422;
Повторное ультразвуковое исследование;P24_Matkastrukturastenkilevroga;false;false;false;true;11;423;
Повторное ультразвуковое исследование;P24_Odstructuradiskazritnerva;false;false;false;true;12;424;
Повторное ультразвуковое исследование;P24_Pridatokpravsemobyemnobrazov;false;false;false;true;11;425;
Повторное ультразвуковое исследование;P24_Prvpochpiyelicheskiyindeks;false;false;false;true;11;426;
Повторное ультразвуковое исследование;P24_Zhelchpuzyrstepennapolneniya;false;false;false;true;11;427;
Повторное ультразвуковое исследование;P25_Levsemrazmer;false;false;false;true;12;428;
Повторное ультразвуковое исследование;P25_Matkasoderzhimpolostilevroga;false;false;false;true;12;429;
Повторное ультразвуковое исследование;P25_Odstructuraretrobulyar;false;false;false;true;13;430;
Повторное ультразвуковое исследование;P25_Prvpochpochsinusekhogennost;false;false;false;true;12;431;
Повторное ультразвуковое исследование;P25_Zhelchpuzyrformazhelchpuzyrya;false;false;false;true;12;432;
Повторное ультразвуковое исследование;P26_Levsemkontur;false;false;false;true;13;433;
Повторное ультразвуковое исследование;P26_Osrazmerperednegootrezka;false;false;false;true;14;434;
Повторное ультразвуковое исследование;P26_Pravyaichnikrazmer;false;false;false;true;13;435;
Повторное ультразвуковое исследование;P26_Prvpochpochsinuschetkostdifferents;false;false;false;true;13;436;
Повторное ультразвуковое исследование;P26_Zhelchpuzyrtolshchinastenki;false;false;false;true;13;437;
Повторное ультразвуковое исследование;P27_Levsemparenkhima;false;false;false;true;14;438;
Повторное ультразвуковое исследование;P27_Osrazmerzadnegootrezka;false;false;false;true;15;439;
Повторное ультразвуковое исследование;P27_Pravyaichnikkontur;false;false;false;true;14;440;
Повторное ультразвуковое исследование;P27_Prvpochpochsinuspolostlokhanki;false;false;false;true;14;441;
Повторное ультразвуковое исследование;P27_Zhelchpuzyrdeformatsiya;false;false;false;true;14;442;
Повторное ультразвуковое исследование;P28_Levsemobyemnobrazov;false;false;false;true;15;443;
Повторное ультразвуковое исследование;P28_Osstructuraperedcamer;false;false;false;true;16;444;
Повторное ультразвуковое исследование;P28_Pravyaichniknovoobrazov;false;false;false;true;15;445;
Повторное ультразвуковое исследование;P28_Prvpochpochsinusstepenlokhanki;false;false;false;true;15;446;
Повторное ультразвуковое исследование;P28_Zhelchpuzyrstrukturazhelchi;false;false;false;true;15;447;
Повторное ультразвуковое исследование;P29_Levyaichnikrazmer;false;false;false;true;16;448;
Повторное ультразвуковое исследование;P29_Osrazmerhrust;false;false;false;true;17;449;
Повторное ультразвуковое исследование;P29_Pridatoklevsemgolovka;false;false;false;true;16;450;
Повторное ультразвуковое исследование;P29_Prvpochsosudyparenkhimy;false;false;false;true;16;451;
Повторное ультразвуковое исследование;P29_Zhelchpuzyrpuzyrprotok;false;false;false;true;16;452;
Повторное ультразвуковое исследование;P30_Levyaichnikkontur;false;false;false;true;17;453;
Повторное ультразвуковое исследование;P30_Osstructurahrust;false;false;false;true;18;454;
Повторное ультразвуковое исследование;P30_Pridatoklevsemtelo;false;false;false;true;17;455;
Повторное ультразвуковое исследование;P30_PrvpochIndeksrezistivnpochechnart;false;false;false;true;17;456;
Повторное ультразвуковое исследование;P30_Zhelchpuzyrobshzhelchprotok;false;false;false;true;17;457;
Повторное ультразвуковое исследование;P31_Levyaichniknovoobrazov;false;false;false;true;18;458;
Повторное ультразвуковое исследование;P31_Oscapsulahrust;false;false;false;true;19;459;
Повторное ультразвуковое исследование;P31_Pridatoklevsemobyemnobrazov;false;false;false;true;18;460;
Повторное ультразвуковое исследование;P31_PrvpochIndeksrezistivnmezhdolevoyart;false;false;false;true;18;461;
Повторное ультразвуковое исследование;P31_Zhelchpuzyrpechenochnprotok;false;false;false;true;18;462;
Повторное ультразвуковое исследование;P32_Abdomultmserviceresult;false;false;false;true;19;463;
Повторное ультразвуковое исследование;P32_Osstructurasteklotelo;false;false;false;true;20;464;
Повторное ультразвуковое исследование;P32_Prvpochkonkrementy;false;false;false;true;19;465;
Повторное ультразвуковое исследование;P32_Serviceresult;false;false;false;true;19;466;
Повторное ультразвуковое исследование;P32_Zhelchpuzyrobyemnobrazov;false;false;false;true;19;467;
Повторное ультразвуковое исследование;P33_Osdiametrzrachka;false;false;false;true;21;468;
Повторное ультразвуковое исследование;P33_Prvpochobyemnobrazov;false;false;false;true;20;469;
Повторное ультразвуковое исследование;P33_Selezenkaraspoloshenie;false;false;false;true;20;470;
Повторное ультразвуковое исследование;P34_Levpochraspoloshenie;false;false;false;true;21;471;
Повторное ультразвуковое исследование;P34_Oscontur;false;false;false;true;22;472;
Повторное ультразвуковое исследование;P34_Selezenkakontur;false;false;false;true;21;473;
Повторное ультразвуковое исследование;P35_Levpochgranica;false;false;false;true;22;474;
Повторное ультразвуковое исследование;P35_Osstructura;false;false;false;true;23;475;
Повторное ультразвуковое исследование;P35_Selezenkarazmer;false;false;false;true;22;476;
Повторное ультразвуковое исследование;P36_Levpochrazmer;false;false;false;true;23;477;
Повторное ультразвуковое исследование;P36_Osstructuradiskazritnerva;false;false;false;true;24;478;
Повторное ультразвуковое исследование;P36_Selezenkaekhostruktura;false;false;false;true;23;479;
Повторное ультразвуковое исследование;P37_Levpochkortiksloytolshina;false;false;false;true;24;480;
Повторное ультразвуковое исследование;P37_Osstructuraretrobulyar;false;false;false;true;25;481;
Повторное ультразвуковое исследование;P37_Selezenkaekhogennost;false;false;false;true;24;482;
Повторное ультразвуковое исследование;P38_Levpochkortiksloyekhogennost;false;false;false;true;25;483;
Повторное ультразвуковое исследование;P38_Selezenkasosudrisunok;false;false;false;true;25;484;
Повторное ультразвуковое исследование;P38_Serviceresult;false;false;false;true;26;485;
Повторное ультразвуковое исследование;P39_Levpochkortiksloyekhostruktura;false;false;false;true;26;486;
Повторное ультразвуковое исследование;P39_Selezenkaobyemnobrazov;false;false;false;true;26;487;
Повторное ультразвуковое исследование;P40_Levpochmedullyarsloytolshchina;false;false;false;true;27;488;
Повторное ультразвуковое исследование;P40_Podzhelzhelezaraspoloshenie;false;false;false;true;27;489;
Повторное ультразвуковое исследование;P41_Levpochmedullyarsloyekhogennost;false;false;false;true;28;490;
Повторное ультразвуковое исследование;P41_Podzhelzhelezakontur;false;false;false;true;28;491;
Повторное ультразвуковое исследование;P42_Levpochmedullyarsloyekhostruktura;false;false;false;true;29;492;
Повторное ультразвуковое исследование;P42_Podzhelzhelezarazmer;false;false;false;true;29;493;
Повторное ультразвуковое исследование;P43_Levpochmedullyarsloykortmeddiffer;false;false;false;true;30;494;
Повторное ультразвуковое исследование;P43_Podzhelzhelezaekhostruktura;false;false;false;true;30;495;
Повторное ультразвуковое исследование;P44_Levpochpiyelicheskiyindeks;false;false;false;true;31;496;
Повторное ультразвуковое исследование;P44_Podzhelzhelezaekhogennost;false;false;false;true;31;497;
Повторное ультразвуковое исследование;P45_Levpochpochsinusekhogennost;false;false;false;true;32;498;
Повторное ультразвуковое исследование;P45_Podzhelzhelezaobyemnobrazov;false;false;false;true;32;499;
Повторное ультразвуковое исследование;P46_Levpochpochsinuschetkostdifferents;false;false;false;true;33;500;
Повторное ультразвуковое исследование;P46_Zheludkishechntrakt;false;false;false;true;33;501;
Повторное ультразвуковое исследование;P47_Levpochpochsinuspolostlokhanki;false;false;false;true;34;502;
Повторное ультразвуковое исследование;P47_Svobodnzhidkost;false;false;false;true;34;503;
Повторное ультразвуковое исследование;P48_Levpochpochsinusstenkilokhanki;false;false;false;true;35;504;
Повторное ультразвуковое исследование;P48_Serviceresult;false;false;false;true;35;505;
Повторное ультразвуковое исследование;P49_Levpochsosudyparenkhimy;false;false;false;true;36;506;
Повторное ультразвуковое исследование;P50_LevpochIndeksrezistivnpochechnart;false;false;false;true;37;507;
Повторное ультразвуковое исследование;P51_LevpochIndeksrezistivnmezhdolevoyart;false;false;false;true;38;508;
Повторное ультразвуковое исследование;P52_Levpochkonkrementy;false;false;false;true;39;509;
Повторное ультразвуковое исследование;P53_Levpochobyemnobrazov;false;false;false;true;40;510;
Повторное ультразвуковое исследование;P54_Mochpuzstepnapoln;false;false;false;true;41;511;
Повторное ультразвуковое исследование;P55_Mochpuztolshchinastenki;false;false;false;true;42;512;
Повторное ультразвуковое исследование;P56_Mochpuzdeformatsiya;false;false;false;true;43;513;
Повторное ультразвуковое исследование;P57_Mochpuzuretra;false;false;false;true;44;514;
Повторное ультразвуковое исследование;P58_MochpuzObyemnobrazov;false;false;false;true;45;515;
Повторное ультразвуковое исследование;P59_Serviceresult;false;false;false;true;46;516;
Определение гормонов в сыворотке крови - прогестерон;P0_Venousbloodanalysisnum;false;true;false;true;1;517;
Определение гормонов в сыворотке крови - тестостерон;P0_Venousbloodanalysisnum;false;true;false;true;1;518;
Определение гормонов в сыворотке крови - тироксин;P0_Venousbloodanalysisnum;false;true;false;true;1;519;
Определение гормонов в сыворотке крови - трийодтиронин;P0_Venousbloodanalysisnum;false;true;false;true;1;520;
Определение гормонов в сыворотке крови - эстрадиол;P0_Venousbloodanalysisnum;false;true;false;true;1;521;
Повторное ультразвуковое исследование;P0_Organsystem;true;false;true;false;1;522;
Пункционная биопсия на цитологический анализ;P0_Biopsycytologicsanalysisnum;false;true;false;true;1;523;
Скрининговое ЭХО-кардиографическое исследование;P14_LVIDd;false;false;false;true;1;524;
Скрининговое ЭХО-кардиографическое исследование;P15_LVIDs;false;false;false;true;2;525;
Скрининговое ЭХО-кардиографическое исследование;P16_LVWTd;false;false;false;true;3;526;
Скрининговое ЭХО-кардиографическое исследование;P17_LVWTs;false;false;false;true;4;527;
Скрининговое ЭХО-кардиографическое исследование;P18_IVSTd;false;false;false;true;5;528;
Скрининговое ЭХО-кардиографическое исследование;P19_IVSTs;false;false;false;true;6;529;
Скрининговое ЭХО-кардиографическое исследование;P20_EF;false;false;false;true;7;530;
Скрининговое ЭХО-кардиографическое исследование;P21_FS;false;false;false;true;8;531;
Скрининговое ЭХО-кардиографическое исследование;P22_LA;false;false;false;true;9;532;
Скрининговое ЭХО-кардиографическое исследование;P23_AO;false;false;false;true;10;533;
Скрининговое ЭХО-кардиографическое исследование;P24_LA/AO;false;false;false;true;11;534;
Скрининговое ЭХО-кардиографическое исследование;P25_RVIDd;false;false;false;true;12;535;
Скрининговое ЭХО-кардиографическое исследование;P26_RVIDs;false;false;false;true;13;536;
Скрининговое ЭХО-кардиографическое исследование;P27_RVWTd;false;false;false;true;14;537;
Скрининговое ЭХО-кардиографическое исследование;P28_RVWTs;false;false;false;true;15;538;
Скрининговое ЭХО-кардиографическое исследование;P29_RA;false;false;false;true;16;539;
Скрининговое ЭХО-кардиографическое исследование;P30_Defektivs;false;false;false;true;17;540;
Скрининговое ЭХО-кардиографическое исследование;P31_Defektias;false;false;false;true;18;541;
Скрининговое ЭХО-кардиографическое исследование;P32_Svobodnzhidkostperikarde;false;false;false;true;19;542;
Скрининговое ЭХО-кардиографическое исследование;P33_Svobodnzhidkostplevralpolosti;false;false;false;true;20;543;
Скрининговое ЭХО-кардиографическое исследование;P34_Novoobrazov;false;false;false;true;21;544;
Скрининговое ЭХО-кардиографическое исследование;P35_Mitrklapnstvorki;false;false;false;true;22;545;
Скрининговое ЭХО-кардиографическое исследование;P36_Mitrklapnskorostkrovotoka;false;false;false;true;23;546;
Скрининговое ЭХО-кардиографическое исследование;P37_Mitrklapnregurgitatsiya;false;false;false;true;24;547;
Скрининговое ЭХО-кардиографическое исследование;P38_Trikuspklapnstvorki;false;false;false;true;25;548;
Скрининговое ЭХО-кардиографическое исследование;P39_Trikuspklapnskorostkrovotoka;false;false;false;true;26;549;
Скрининговое ЭХО-кардиографическое исследование;P40_Trikuspklapnregurgitatsiya;false;false;false;true;27;550;
Скрининговое ЭХО-кардиографическое исследование;P41_Aortaklapnstvorki;false;false;false;true;28;551;
Скрининговое ЭХО-кардиографическое исследование;P42_Aortaklapnskorostkrovotoka;false;false;false;true;29;552;
Скрининговое ЭХО-кардиографическое исследование;P43_Aortaklapnregurgitatsiya;false;false;false;true;30;553;
Скрининговое ЭХО-кардиографическое исследование;P44_Klapnlegartstvorki;false;false;false;true;31;554;
Скрининговое ЭХО-кардиографическое исследование;P45_Klapnlegartskorostkrovotoka;false;false;false;true;32;555;
Скрининговое ЭХО-кардиографическое исследование;P46_Klapnlegartregurgitatsiya;false;false;false;true;33;556;
Санитарная помывка животных - крупные животные (свыше 15 кг);P0_Petweight;false;false;false;true;1;557;
Санитарная помывка животных - мелкие животные (до 5 кг);P0_Petweight;false;false;false;true;1;558;
Санитарная помывка животных - средние животные (свыше 5 кг до 15 кг);P0_Petweight;false;false;false;true;1;559;
Санитарная стрижка животных - крупные животные (свыше 15 кг);P0_Petweight;false;false;false;true;1;560;
Скрининговое ЭХО-кардиографическое исследование;P47_Serviceresult;false;false;false;true;34;561;
Санитарная стрижка животных - мелкие животные (до 5 кг);P0_Petweight;false;false;false;true;1;562;
Считывание номера микрочипа (сканирование);P0_Petchpidentificationcode;false;true;false;true;1;563;
Ультразвуковое исследование;P14_Matkadiametrtela;false;false;false;true;1;564;
Ультразвуковое исследование;P14_Odrazmerperednegootrezka;false;false;false;true;1;565;
Ультразвуковое исследование;P14_Pechenraspoloshenie;false;false;false;true;1;566;
Ультразвуковое исследование;P14_Predstzhelezarazmer;false;false;false;true;1;567;
Ультразвуковое исследование;P14_Prvpochraspoloshenie;false;false;false;true;1;568;
Ультразвуковое исследование;P15_Matkatolshinatela;false;false;false;true;2;569;
Ультразвуковое исследование;P15_Odrazmerzadnegootrezka;false;false;false;true;2;570;
Ультразвуковое исследование;P15_Pechenkontur;false;false;false;true;2;571;
Ультразвуковое исследование;P15_Predstzhelezakontur;false;false;false;true;2;572;
Ультразвуковое исследование;P15_Prvpochgranica;false;false;false;true;2;573;
Ультразвуковое исследование;P16_Matkastrukturastenkitela;false;false;false;true;3;574;
Ультразвуковое исследование;P16_Odstructuraperedcamer;false;false;false;true;3;575;
Ультразвуковое исследование;P16_Pechenrazmer;false;false;false;true;3;576;
Ультразвуковое исследование;P16_Predstzhelezaparenkhima;false;false;false;true;3;577;
Ультразвуковое исследование;P16_Prvpochrazmer;false;false;false;true;3;578;
Ультразвуковое исследование;P17_Matkasostoyanpolosti;false;false;false;true;4;579;
Ультразвуковое исследование;P17_Odrazmerhrust;false;false;false;true;4;580;
Ультразвуковое исследование;P17_Pechenekhostruktura;false;false;false;true;4;581;
Ультразвуковое исследование;P17_Predstzhelezaobyemnobrazov;false;false;false;true;4;582;
Ультразвуковое исследование;P17_Prvpochkortiksloytolshina;false;false;false;true;4;583;
Ультразвуковое исследование;P18_Matkadiametrpravroga;false;false;false;true;5;584;
Ультразвуковое исследование;P18_Odstructurahrust;false;false;false;true;5;585;
Ультразвуковое исследование;P18_Pechenekhogennost;false;false;false;true;5;586;
Ультразвуковое исследование;P18_Pravsemrazmer;false;false;false;true;5;587;
Ультразвуковое исследование;P18_Prvpochkortiksloyekhogennost;false;false;false;true;5;588;
Ультразвуковое исследование;P19_Matkatolshinapravroga;false;false;false;true;6;589;
Ультразвуковое исследование;P19_Odcapsulahrust;false;false;false;true;6;590;
Ультразвуковое исследование;P19_Pechenperifsosudrisunok;false;false;false;true;6;591;
Ультразвуковое исследование;P19_Pravsemkontur;false;false;false;true;6;592;
Ультразвуковое исследование;P19_Prvpochkortiksloyekhostruktura;false;false;false;true;6;593;
Ультразвуковое исследование;P20_Matkastrukturastenkipravroga;false;false;false;true;7;594;
Ультразвуковое исследование;P20_Odstructurasteklotelo;false;false;false;true;7;595;
Ультразвуковое исследование;P20_Pechenportae;false;false;false;true;7;596;
Ультразвуковое исследование;P20_Pravsemparenkhima;false;false;false;true;7;597;
Ультразвуковое исследование;P20_Prvpochmedullyarsloytolshchina;false;false;false;true;7;598;
Ультразвуковое исследование;P21_Matkasoderzhimpolostipravroga;false;false;false;true;8;599;
Ультразвуковое исследование;P21_Oddiametrzrachka;false;false;false;true;8;600;
Ультразвуковое исследование;P21_Pechenvhepatica;false;false;false;true;8;601;
Ультразвуковое исследование;P21_Pravsemobyemnobrazov;false;false;false;true;8;602;
Ультразвуковое исследование;P21_Prvpochmedullyarsloyekhogennost;false;false;false;true;8;603;
Ультразвуковое исследование;P22_Matkadiametrlevroga;false;false;false;true;9;604;
Ультразвуковое исследование;P22_Odcontur;false;false;false;true;9;605;
Ультразвуковое исследование;P22_Pechenahepatica;false;false;false;true;9;606;
Ультразвуковое исследование;P22_Pridatokpravsemgolovka;false;false;false;true;9;607;
Ультразвуковое исследование;P22_Prvpochmedullyarsloyekhostruktura;false;false;false;true;9;608;
Ультразвуковое исследование;P23_Matkatolshinalevroga;false;false;false;true;10;609;
Ультразвуковое исследование;P23_Odstructura;false;false;false;true;10;610;
Ультразвуковое исследование;P23_Pechenobyemnobrazov;false;false;false;true;10;611;
Ультразвуковое исследование;P23_Pridatokpravsemtelo;false;false;false;true;10;612;
Ультразвуковое исследование;P23_Prvpochmedullyarsloykortmeddiffer;false;false;false;true;10;613;
Ультразвуковое исследование;P24_Matkastrukturastenkilevroga;false;false;false;true;11;614;
Ультразвуковое исследование;P24_Odstructuradiskazritnerva;false;false;false;true;11;615;
Ультразвуковое исследование;P24_Pridatokpravsemobyemnobrazov;false;false;false;true;11;616;
Ультразвуковое исследование;P24_Prvpochpiyelicheskiyindeks;false;false;false;true;11;617;
Ультразвуковое исследование;P24_Zhelchpuzyrstepennapolneniya;false;false;false;true;11;618;
Ультразвуковое исследование;P25_Levsemrazmer;false;false;false;true;12;619;
Ультразвуковое исследование;P25_Matkasoderzhimpolostilevroga;false;false;false;true;12;620;
Ультразвуковое исследование;P25_Odstructuraretrobulyar;false;false;false;true;12;621;
Ультразвуковое исследование;P25_Prvpochpochsinusekhogennost;false;false;false;true;12;622;
Ультразвуковое исследование;P25_Zhelchpuzyrformazhelchpuzyrya;false;false;false;true;12;623;
Ультразвуковое исследование;P26_Levsemkontur;false;false;false;true;13;624;
Ультразвуковое исследование;P26_Osrazmerperednegootrezka;false;false;false;true;13;625;
Ультразвуковое исследование;P26_Pravyaichnikrazmer;false;false;false;true;13;626;
Ультразвуковое исследование;P26_Prvpochpochsinuschetkostdifferents;false;false;false;true;13;627;
Ультразвуковое исследование;P26_Zhelchpuzyrtolshchinastenki;false;false;false;true;13;628;
Ультразвуковое исследование;P27_Levsemparenkhima;false;false;false;true;14;629;
Ультразвуковое исследование;P27_Osrazmerzadnegootrezka;false;false;false;true;14;630;
Ультразвуковое исследование;P27_Pravyaichnikkontur;false;false;false;true;14;631;
Ультразвуковое исследование;P27_Prvpochpochsinuspolostlokhanki;false;false;false;true;14;632;
Ультразвуковое исследование;P27_Zhelchpuzyrdeformatsiya;false;false;false;true;14;633;
Ультразвуковое исследование;P28_Levsemobyemnobrazov;false;false;false;true;15;634;
Ультразвуковое исследование;P28_Osstructuraperedcamer;false;false;false;true;15;635;
Ультразвуковое исследование;P28_Pravyaichniknovoobrazov;false;false;false;true;15;636;
Ультразвуковое исследование;P28_Prvpochpochsinusstepenlokhanki;false;false;false;true;15;637;
Ультразвуковое исследование;P28_Zhelchpuzyrstrukturazhelchi;false;false;false;true;15;638;
Ультразвуковое исследование;P29_Levyaichnikrazmer;false;false;false;true;16;639;
Ультразвуковое исследование;P29_Osrazmerhrust;false;false;false;true;16;640;
Ультразвуковое исследование;P29_Pridatoklevsemgolovka;false;false;false;true;16;641;
Ультразвуковое исследование;P29_Prvpochsosudyparenkhimy;false;false;false;true;16;642;
Ультразвуковое исследование;P29_Zhelchpuzyrpuzyrprotok;false;false;false;true;16;643;
Ультразвуковое исследование;P30_Levyaichnikkontur;false;false;false;true;17;644;
Ультразвуковое исследование;P30_Osstructurahrust;false;false;false;true;17;645;
Ультразвуковое исследование;P30_Pridatoklevsemtelo;false;false;false;true;17;646;
Ультразвуковое исследование;P30_PrvpochIndeksrezistivnpochechnart;false;false;false;true;17;647;
Ультразвуковое исследование;P30_Zhelchpuzyrobshzhelchprotok;false;false;false;true;17;648;
Ультразвуковое исследование;P31_Levyaichniknovoobrazov;false;false;false;true;18;649;
Ультразвуковое исследование;P31_Oscapsulahrust;false;false;false;true;18;650;
Ультразвуковое исследование;P31_Pridatoklevsemobyemnobrazov;false;false;false;true;18;651;
Ультразвуковое исследование;P31_PrvpochIndeksrezistivnmezhdolevoyart;false;false;false;true;18;652;
Ультразвуковое исследование;P31_Zhelchpuzyrpechenochnprotok;false;false;false;true;18;653;
Ультразвуковое исследование;P32_Abdomultmserviceresult;false;false;false;true;19;654;
Ультразвуковое исследование;P32_Osstructurasteklotelo;false;false;false;true;19;655;
Ультразвуковое исследование;P32_Prvpochkonkrementy;false;false;false;true;19;656;
Ультразвуковое исследование;P32_Serviceresult;false;false;false;true;19;657;
Ультразвуковое исследование;P32_Zhelchpuzyrobyemnobrazov;false;false;false;true;19;658;
Ультразвуковое исследование;P33_Osdiametrzrachka;false;false;false;true;20;659;
Ультразвуковое исследование;P33_Prvpochobyemnobrazov;false;false;false;true;20;660;
Ультразвуковое исследование;P33_Selezenkaraspoloshenie;false;false;false;true;20;661;
Ультразвуковое исследование;P34_Levpochraspoloshenie;false;false;false;true;21;662;
Ультразвуковое исследование;P34_Oscontur;false;false;false;true;21;663;
Ультразвуковое исследование;P34_Selezenkakontur;false;false;false;true;21;664;
Ультразвуковое исследование;P35_Levpochgranica;false;false;false;true;22;665;
Ультразвуковое исследование;P35_Osstructura;false;false;false;true;22;666;
Ультразвуковое исследование;P35_Selezenkarazmer;false;false;false;true;22;667;
Ультразвуковое исследование;P36_Levpochrazmer;false;false;false;true;23;668;
Ультразвуковое исследование;P36_Osstructuradiskazritnerva;false;false;false;true;23;669;
Ультразвуковое исследование;P36_Selezenkaekhostruktura;false;false;false;true;23;670;
Ультразвуковое исследование;P37_Levpochkortiksloytolshina;false;false;false;true;24;671;
Ультразвуковое исследование;P37_Osstructuraretrobulyar;false;false;false;true;24;672;
Ультразвуковое исследование;P37_Selezenkaekhogennost;false;false;false;true;24;673;
Ультразвуковое исследование;P38_Levpochkortiksloyekhogennost;false;false;false;true;25;674;
Ультразвуковое исследование;P38_Selezenkasosudrisunok;false;false;false;true;25;675;
Ультразвуковое исследование;P38_Serviceresult;false;false;false;true;25;676;
Ультразвуковое исследование;P39_Levpochkortiksloyekhostruktura;false;false;false;true;26;677;
Ультразвуковое исследование;P39_Selezenkaobyemnobrazov;false;false;false;true;26;678;
Ультразвуковое исследование;P40_Levpochmedullyarsloytolshchina;false;false;false;true;27;679;
Ультразвуковое исследование;P40_Podzhelzhelezaraspoloshenie;false;false;false;true;27;680;
Ультразвуковое исследование;P41_Levpochmedullyarsloyekhogennost;false;false;false;true;28;681;
Ультразвуковое исследование;P41_Podzhelzhelezakontur;false;false;false;true;28;682;
Ультразвуковое исследование;P42_Levpochmedullyarsloyekhostruktura;false;false;false;true;29;683;
Ультразвуковое исследование;P42_Podzhelzhelezarazmer;false;false;false;true;29;684;
Ультразвуковое исследование;P43_Levpochmedullyarsloykortmeddiffer;false;false;false;true;30;685;
Ультразвуковое исследование;P43_Podzhelzhelezaekhostruktura;false;false;false;true;30;686;
Ультразвуковое исследование;P44_Levpochpiyelicheskiyindeks;false;false;false;true;31;687;
Ультразвуковое исследование;P44_Podzhelzhelezaekhogennost;false;false;false;true;31;688;
Ультразвуковое исследование;P45_Levpochpochsinusekhogennost;false;false;false;true;32;689;
Ультразвуковое исследование;P45_Podzhelzhelezaobyemnobrazov;false;false;false;true;32;690;
Ультразвуковое исследование;P46_Levpochpochsinuschetkostdifferents;false;false;false;true;33;691;
Ультразвуковое исследование;P46_Zheludkishechntrakt;false;false;false;true;33;692;
Ультразвуковое исследование;P47_Levpochpochsinuspolostlokhanki;false;false;false;true;34;693;
Ультразвуковое исследование;P47_Svobodnzhidkost;false;false;false;true;34;694;
Ультразвуковое исследование;P48_Levpochpochsinusstenkilokhanki;false;false;false;true;35;695;
Ультразвуковое исследование;P48_Serviceresult;false;false;false;true;35;696;
Ультразвуковое исследование;P49_Levpochsosudyparenkhimy;false;false;false;true;36;697;
Ультразвуковое исследование;P50_LevpochIndeksrezistivnpochechnart;false;false;false;true;37;698;
Ультразвуковое исследование;P51_LevpochIndeksrezistivnmezhdolevoyart;false;false;false;true;38;699;
Ультразвуковое исследование;P52_Levpochkonkrementy;false;false;false;true;39;700;
Ультразвуковое исследование;P53_Levpochobyemnobrazov;false;false;false;true;40;701;
Ультразвуковое исследование;P54_Mochpuzstepnapoln;false;false;false;true;41;702;
Ультразвуковое исследование;P55_Mochpuztolshchinastenki;false;false;false;true;42;703;
Ультразвуковое исследование;P56_Mochpuzdeformatsiya;false;false;false;true;43;704;
Ультразвуковое исследование;P57_Mochpuzuretra;false;false;false;true;44;705;
Ультразвуковое исследование;P58_MochpuzObyemnobrazov;false;false;false;true;45;706;
Санитарная стрижка животных - средние животные (свыше 5 кг до 15 кг);P0_Petweight;false;false;false;true;1;707;
Ультразвуковое исследование;P59_Serviceresult;false;false;false;true;46;708;
Ультразвуковой скрининг органов брюшной полости;P14_Matkadiametrtela;false;false;false;true;2;709;
Ультразвуковой скрининг органов брюшной полости;P14_Pechenraspoloshenie;false;false;false;true;2;710;
Ультразвуковой скрининг органов брюшной полости;P14_Predstzhelezarazmer;false;false;false;true;2;711;
Ультразвуковой скрининг органов брюшной полости;P14_Prvpochraspoloshenie;false;false;false;true;2;712;
Ультразвуковой скрининг органов брюшной полости;P15_Matkatolshinatela;false;false;false;true;3;713;
Ультразвуковой скрининг органов брюшной полости;P15_Pechenkontur;false;false;false;true;3;714;
Ультразвуковой скрининг органов брюшной полости;P15_Predstzhelezakontur;false;false;false;true;3;715;
Ультразвуковой скрининг органов брюшной полости;P15_Prvpochgranica;false;false;false;true;3;716;
Ультразвуковой скрининг органов брюшной полости;P16_Matkastrukturastenkitela;false;false;false;true;4;717;
Ультразвуковой скрининг органов брюшной полости;P16_Pechenrazmer;false;false;false;true;4;718;
Ультразвуковой скрининг органов брюшной полости;P16_Predstzhelezaparenkhima;false;false;false;true;4;719;
Ультразвуковой скрининг органов брюшной полости;P16_Prvpochrazmer;false;false;false;true;4;720;
Ультразвуковой скрининг органов брюшной полости;P17_Matkasostoyanpolosti;false;false;false;true;5;721;
Ультразвуковой скрининг органов брюшной полости;P17_Pechenekhostruktura;false;false;false;true;5;722;
Ультразвуковой скрининг органов брюшной полости;P17_Predstzhelezaobyemnobrazov;false;false;false;true;5;723;
Ультразвуковой скрининг органов брюшной полости;P17_Prvpochkortiksloytolshina;false;false;false;true;5;724;
Ультразвуковой скрининг органов брюшной полости;P18_Matkadiametrpravroga;false;false;false;true;6;725;
Ультразвуковой скрининг органов брюшной полости;P18_Pechenekhogennost;false;false;false;true;6;726;
Ультразвуковой скрининг органов брюшной полости;P18_Pravsemrazmer;false;false;false;true;6;727;
Ультразвуковой скрининг органов брюшной полости;P18_Prvpochkortiksloyekhogennost;false;false;false;true;6;728;
Ультразвуковой скрининг органов брюшной полости;P19_Matkatolshinapravroga;false;false;false;true;7;729;
Ультразвуковой скрининг органов брюшной полости;P19_Pechenperifsosudrisunok;false;false;false;true;7;730;
Ультразвуковой скрининг органов брюшной полости;P19_Pravsemkontur;false;false;false;true;7;731;
Ультразвуковой скрининг органов брюшной полости;P19_Prvpochkortiksloyekhostruktura;false;false;false;true;7;732;
Ультразвуковой скрининг органов брюшной полости;P20_Matkastrukturastenkipravroga;false;false;false;true;8;733;
Ультразвуковой скрининг органов брюшной полости;P20_Pechenportae;false;false;false;true;8;734;
Ультразвуковой скрининг органов брюшной полости;P20_Pravsemparenkhima;false;false;false;true;8;735;
Ультразвуковой скрининг органов брюшной полости;P20_Prvpochmedullyarsloytolshchina;false;false;false;true;8;736;
Ультразвуковой скрининг органов брюшной полости;P21_Matkasoderzhimpolostipravroga;false;false;false;true;9;737;
Ультразвуковой скрининг органов брюшной полости;P21_Pechenvhepatica;false;false;false;true;9;738;
Ультразвуковой скрининг органов брюшной полости;P21_Pravsemobyemnobrazov;false;false;false;true;9;739;
Ультразвуковой скрининг органов брюшной полости;P21_Prvpochmedullyarsloyekhogennost;false;false;false;true;9;740;
Ультразвуковой скрининг органов брюшной полости;P22_Matkadiametrlevroga;false;false;false;true;10;741;
Ультразвуковой скрининг органов брюшной полости;P22_Pechenahepatica;false;false;false;true;10;742;
Ультразвуковой скрининг органов брюшной полости;P22_Pridatokpravsemgolovka;false;false;false;true;10;743;
Ультразвуковой скрининг органов брюшной полости;P22_Prvpochmedullyarsloyekhostruktura;false;false;false;true;10;744;
Ультразвуковой скрининг органов брюшной полости;P23_Matkatolshinalevroga;false;false;false;true;11;745;
Ультразвуковой скрининг органов брюшной полости;P23_Pechenobyemnobrazov;false;false;false;true;11;746;
Ультразвуковой скрининг органов брюшной полости;P23_Pridatokpravsemtelo;false;false;false;true;11;747;
Ультразвуковой скрининг органов брюшной полости;P23_Prvpochmedullyarsloykortmeddiffer;false;false;false;true;11;748;
Ультразвуковой скрининг органов брюшной полости;P24_Matkastrukturastenkilevroga;false;false;false;true;12;749;
Ультразвуковой скрининг органов брюшной полости;P24_Pridatokpravsemobyemnobrazov;false;false;false;true;12;750;
Ультразвуковой скрининг органов брюшной полости;P24_Prvpochpiyelicheskiyindeks;false;false;false;true;12;751;
Ультразвуковой скрининг органов брюшной полости;P24_Zhelchpuzyrstepennapolneniya;false;false;false;true;12;752;
Ультразвуковой скрининг органов брюшной полости;P25_Levsemrazmer;false;false;false;true;13;753;
Ультразвуковой скрининг органов брюшной полости;P25_Matkasoderzhimpolostilevroga;false;false;false;true;13;754;
Ультразвуковой скрининг органов брюшной полости;P25_Prvpochpochsinusekhogennost;false;false;false;true;13;755;
Ультразвуковой скрининг органов брюшной полости;P25_Zhelchpuzyrformazhelchpuzyrya;false;false;false;true;13;756;
Ультразвуковой скрининг органов брюшной полости;P26_Levsemkontur;false;false;false;true;14;757;
Ультразвуковой скрининг органов брюшной полости;P26_Pravyaichnikrazmer;false;false;false;true;14;758;
Ультразвуковой скрининг органов брюшной полости;P26_Prvpochpochsinuschetkostdifferents;false;false;false;true;14;759;
Ультразвуковой скрининг органов брюшной полости;P26_Zhelchpuzyrtolshchinastenki;false;false;false;true;14;760;
Ультразвуковой скрининг органов брюшной полости;P27_Levsemparenkhima;false;false;false;true;15;761;
Ультразвуковой скрининг органов брюшной полости;P27_Pravyaichnikkontur;false;false;false;true;15;762;
Ультразвуковой скрининг органов брюшной полости;P27_Prvpochpochsinuspolostlokhanki;false;false;false;true;15;763;
Ультразвуковой скрининг органов брюшной полости;P27_Zhelchpuzyrdeformatsiya;false;false;false;true;15;764;
Ультразвуковой скрининг органов брюшной полости;P28_Levsemobyemnobrazov;false;false;false;true;16;765;
Ультразвуковой скрининг органов брюшной полости;P28_Pravyaichniknovoobrazov;false;false;false;true;16;766;
Ультразвуковой скрининг органов брюшной полости;P28_Prvpochpochsinusstepenlokhanki;false;false;false;true;16;767;
Ультразвуковой скрининг органов брюшной полости;P28_Zhelchpuzyrstrukturazhelchi;false;false;false;true;16;768;
Ультразвуковой скрининг органов брюшной полости;P29_Levyaichnikrazmer;false;false;false;true;17;769;
Ультразвуковой скрининг органов брюшной полости;P29_Pridatoklevsemgolovka;false;false;false;true;17;770;
Ультразвуковой скрининг органов брюшной полости;P29_Prvpochsosudyparenkhimy;false;false;false;true;17;771;
Ультразвуковой скрининг органов брюшной полости;P29_Zhelchpuzyrpuzyrprotok;false;false;false;true;17;772;
Ультразвуковой скрининг органов брюшной полости;P30_Levyaichnikkontur;false;false;false;true;18;773;
Ультразвуковой скрининг органов брюшной полости;P30_Pridatoklevsemtelo;false;false;false;true;18;774;
Ультразвуковой скрининг органов брюшной полости;P30_PrvpochIndeksrezistivnpochechnart;false;false;false;true;18;775;
Ультразвуковой скрининг органов брюшной полости;P30_Zhelchpuzyrobshzhelchprotok;false;false;false;true;18;776;
Ультразвуковой скрининг органов брюшной полости;P31_Levyaichniknovoobrazov;false;false;false;true;19;777;
Ультразвуковой скрининг органов брюшной полости;P31_Pridatoklevsemobyemnobrazov;false;false;false;true;19;778;
Ультразвуковой скрининг органов брюшной полости;P31_PrvpochIndeksrezistivnmezhdolevoyart;false;false;false;true;19;779;
Ультразвуковой скрининг органов брюшной полости;P31_Zhelchpuzyrpechenochnprotok;false;false;false;true;19;780;
Ультразвуковой скрининг органов брюшной полости;P32_Abdomultmserviceresult;false;false;false;true;20;781;
Ультразвуковой скрининг органов брюшной полости;P32_Prvpochkonkrementy;false;false;false;true;20;782;
Ультразвуковой скрининг органов брюшной полости;P32_Serviceresult;false;false;false;true;20;783;
Ультразвуковой скрининг органов брюшной полости;P32_Zhelchpuzyrobyemnobrazov;false;false;false;true;20;784;
Ультразвуковой скрининг органов брюшной полости;P33_Prvpochobyemnobrazov;false;false;false;true;21;785;
Ультразвуковой скрининг органов брюшной полости;P33_Selezenkaraspoloshenie;false;false;false;true;21;786;
Ультразвуковой скрининг органов брюшной полости;P34_Levpochraspoloshenie;false;false;false;true;22;787;
Ультразвуковой скрининг органов брюшной полости;P34_Selezenkakontur;false;false;false;true;22;788;
Ультразвуковой скрининг органов брюшной полости;P35_Levpochgranica;false;false;false;true;23;789;
Ультразвуковой скрининг органов брюшной полости;P35_Selezenkarazmer;false;false;false;true;23;790;
Ультразвуковой скрининг органов брюшной полости;P36_Levpochrazmer;false;false;false;true;24;791;
Ультразвуковой скрининг органов брюшной полости;P36_Selezenkaekhostruktura;false;false;false;true;24;792;
Ультразвуковой скрининг органов брюшной полости;P37_Levpochkortiksloytolshina;false;false;false;true;25;793;
Ультразвуковой скрининг органов брюшной полости;P37_Selezenkaekhogennost;false;false;false;true;25;794;
Ультразвуковой скрининг органов брюшной полости;P38_Levpochkortiksloyekhogennost;false;false;false;true;26;795;
Ультразвуковой скрининг органов брюшной полости;P38_Selezenkasosudrisunok;false;false;false;true;26;796;
Ультразвуковой скрининг органов брюшной полости;P39_Levpochkortiksloyekhostruktura;false;false;false;true;27;797;
Ультразвуковой скрининг органов брюшной полости;P39_Selezenkaobyemnobrazov;false;false;false;true;27;798;
Ультразвуковой скрининг органов брюшной полости;P40_Levpochmedullyarsloytolshchina;false;false;false;true;28;799;
Ультразвуковой скрининг органов брюшной полости;P40_Podzhelzhelezaraspoloshenie;false;false;false;true;28;800;
Ультразвуковой скрининг органов брюшной полости;P41_Levpochmedullyarsloyekhogennost;false;false;false;true;29;801;
Ультразвуковой скрининг органов брюшной полости;P41_Podzhelzhelezakontur;false;false;false;true;29;802;
Ультразвуковой скрининг органов брюшной полости;P42_Levpochmedullyarsloyekhostruktura;false;false;false;true;30;803;
Ультразвуковой скрининг органов брюшной полости;P42_Podzhelzhelezarazmer;false;false;false;true;30;804;
Ультразвуковой скрининг органов брюшной полости;P43_Levpochmedullyarsloykortmeddiffer;false;false;false;true;31;805;
Ультразвуковой скрининг органов брюшной полости;P43_Podzhelzhelezaekhostruktura;false;false;false;true;31;806;
Ультразвуковой скрининг органов брюшной полости;P44_Levpochpiyelicheskiyindeks;false;false;false;true;32;807;
Ультразвуковой скрининг органов брюшной полости;P44_Podzhelzhelezaekhogennost;false;false;false;true;32;808;
Ультразвуковой скрининг органов брюшной полости;P45_Levpochpochsinusekhogennost;false;false;false;true;33;809;
Ультразвуковой скрининг органов брюшной полости;P45_Podzhelzhelezaobyemnobrazov;false;false;false;true;33;810;
Ультразвуковой скрининг органов брюшной полости;P46_Levpochpochsinuschetkostdifferents;false;false;false;true;34;811;
Ультразвуковой скрининг органов брюшной полости;P46_Zheludkishechntrakt;false;false;false;true;34;812;
Ультразвуковой скрининг органов брюшной полости;P47_Levpochpochsinuspolostlokhanki;false;false;false;true;35;813;
Ультразвуковой скрининг органов брюшной полости;P47_Svobodnzhidkost;false;false;false;true;35;814;
Ультразвуковой скрининг органов брюшной полости;P48_Levpochpochsinusstenkilokhanki;false;false;false;true;36;815;
Ультразвуковой скрининг органов брюшной полости;P48_Serviceresult;false;false;false;true;36;816;
Ультразвуковой скрининг органов брюшной полости;P49_Levpochsosudyparenkhimy;false;false;false;true;37;817;
Ультразвуковой скрининг органов брюшной полости;P50_LevpochIndeksrezistivnpochechnart;false;false;false;true;38;818;
Ультразвуковой скрининг органов брюшной полости;P51_LevpochIndeksrezistivnmezhdolevoyart;false;false;false;true;39;819;
Ультразвуковой скрининг органов брюшной полости;P52_Levpochkonkrementy;false;false;false;true;40;820;
Ультразвуковой скрининг органов брюшной полости;P53_Levpochobyemnobrazov;false;false;false;true;41;821;
Ультразвуковой скрининг органов брюшной полости;P54_Mochpuzstepnapoln;false;false;false;true;42;822;
Ультразвуковой скрининг органов брюшной полости;P55_Mochpuztolshchinastenki;false;false;false;true;43;823;
Ультразвуковой скрининг органов брюшной полости;P56_Mochpuzdeformatsiya;false;false;false;true;44;824;
Ультразвуковой скрининг органов брюшной полости;P57_Mochpuzuretra;false;false;false;true;45;825;
Ультразвуковой скрининг органов брюшной полости;P58_MochpuzObyemnobrazov;false;false;false;true;46;826;
Снятие гипсовой повязки - крупные породы собак;P0_Petweight;false;false;false;true;1;827;
Снятие гипсовой повязки - мелкие породы собак и кошки;P0_Petweight;false;false;false;true;1;828;
Содержание животных - кошки и собаки (до 5 кг);P0_Petweight;false;false;false;true;1;829;
Ультразвуковой скрининг органов брюшной полости;P59_Serviceresult;false;false;false;true;47;830;
Экспресс-диагностика глюкозы (с использованием глюкометра);P0_Expdiagnosglucosevalue;false;true;false;true;1;841;
Электрокардиография;P12_Pc;false;false;false;true;1;842;
Электрокардиография;P13_Pmv;false;false;false;true;2;843;
Электрокардиография;P14_Р1;false;false;false;true;3;844;
Электрокардиография;P15_P2;false;false;false;true;4;845;
Электрокардиография;P16_P3;false;false;false;true;5;846;
Электрокардиография;P17_Pq;false;false;false;true;6;847;
Электрокардиография;P18_Qrs;false;false;false;true;7;848;
Электрокардиография;P19_Qrsdesc;false;false;false;true;8;849;
Электрокардиография;P20_Rmv;false;false;false;true;9;850;
Электрокардиография;P21_Rdesc;false;false;false;true;10;851;
Электрокардиография;P22_Tmv;false;false;false;true;11;852;
Электрокардиография;P23_Tdesc;false;false;false;true;12;853;
Электрокардиография;P24_St;false;false;false;true;13;854;
Электрокардиография;P25_Qt;false;false;false;true;14;855;
Электрокардиография;P26_Eos;false;false;false;true;15;856;
Электрокардиография;P27_Chss;false;false;false;true;16;857;
Электрокардиография;P28_Ritm;false;false;false;true;17;858;
Электрокардиография;P29_Ekstrasistoly;false;false;false;true;18;859;
Электрокардиография;P30_Serviceresult;false;false;false;true;19;860;
Электронное мечение животного (чипирование со сканированием);P0_Petchpidentificationcode;false;true;false;true;1;861;
Содержание животных - кошки и собаки (свыше 5 кг);P0_Petweight;false;false;false;true;1;862;
Ультразвуковое исследование;P0_Organsystem;true;false;true;false;1;863;
Ультразвуковой скрининг органов брюшной полости;P0_Abdomenorgansystem;true;false;true;false;1;864;
Утилизация (сжигание) биологических отходов без транспортировки;P0_Petweight;false;false;false;true;1;865;
Утилизация (сжигание) биологических отходов с транспортировкой;P0_Petweight;false;false;false;true;1;866;
R-графия;P0_RgraphyFile;false;false;false;true;999;868;
Ультразвуковое исследование;P0_UltrasoundFile;false;false;false;true;999;869;
Повторное ультразвуковое исследование;P0_UltrasoundFile;false;false;false;true;999;870;
Скрининговое ЭХО-кардиографическое исследование;P0_ScreenEchoFile;false;false;false;true;999;871;
Ультразвуковой скрининг органов брюшной полости;P0_UltrasoundScreenFile;false;false;false;true;999;872;
Компьютерная томография без введения контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_CTScanFile;false;false;false;true;999;873;
Компьютерная томография без введения контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_CTScanFile;false;false;false;true;999;874;
Компьютерная томография с введением контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_CTScanFile;false;false;false;true;999;875;
Компьютерная томография с введением контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_CTScanFile;false;false;false;true;999;876;
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - голова, отделы конечностей (сустав, регион конечности);P0_CTScanFile;false;false;false;true;999;877;
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_CTScanFile;false;false;false;true;999;878;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P0_EchoFile;false;false;false;true;999;879;
Ультразвуковое исследование;P13_Serviceresultdesc;false;false;false;true;999;880;
Повторное ультразвуковое исследование;P13_Serviceresultdesc;false;false;false;true;999;881;
Ультразвуковой скрининг органов брюшной полости;P13_Serviceresultdesc;false;false;false;true;999;882;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P14_LVIDd;false;false;false;true;1;883;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P15_LVIDs;false;false;false;true;2;884;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P16_LVWTd;false;false;false;true;3;885;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P17_LVWTs;false;false;false;true;4;886;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P18_IVSTd;false;false;false;true;5;887;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P19_IVSTs;false;false;false;true;6;888;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P20_EF;false;false;false;true;7;889;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P21_FS;false;false;false;true;8;890;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P22_LA;false;false;false;true;9;891;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P23_AO;false;false;false;true;10;892;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P24_LA/AO;false;false;false;true;11;893;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P25_RVIDd;false;false;false;true;12;894;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P26_RVIDs;false;false;false;true;13;895;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P27_RVWTd;false;false;false;true;14;896;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P28_RVWTs;false;false;false;true;15;897;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P29_RA;false;false;false;true;16;898;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P30_Defektivs;false;false;false;true;17;899;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P31_Defektias;false;false;false;true;18;900;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P32_Svobodnzhidkostperikarde;false;false;false;true;19;901;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P33_Svobodnzhidkostplevralpolosti;false;false;false;true;20;902;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P34_Novoobrazov;false;false;false;true;21;903;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P35_Mitrklapnstvorki;false;false;false;true;22;904;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P36_Mitrklapnskorostkrovotoka;false;false;false;true;23;905;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P37_Mitrklapnregurgitatsiya;false;false;false;true;24;906;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P38_Trikuspklapnstvorki;false;false;false;true;25;907;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P39_Trikuspklapnskorostkrovotoka;false;false;false;true;26;908;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P40_Trikuspklapnregurgitatsiya;false;false;false;true;27;909;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P41_Aortaklapnstvorki;false;false;false;true;28;910;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P42_Aortaklapnskorostkrovotoka;false;false;false;true;29;911;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P43_Aortaklapnregurgitatsiya;false;false;false;true;30;912;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P44_Klapnlegartstvorki;false;false;false;true;31;913;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P45_Klapnlegartskorostkrovotoka;false;false;false;true;32;914;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P46_Klapnlegartregurgitatsiya;false;false;false;true;33;915;
ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов;P47_Serviceresult;false;false;false;true;34;916;
Цитологические исследования;P15_Analysisresult;false;false;false;true;3;917;
Цитологические исследования;P12_Analysisnum;false;false;false;true;1;918;
Цитологические исследования;P14_Analysiscount;false;false;false;true;2;919;
Цитологические исследования;P16_Analysisdesc;false;false;false;true;4;920;
Цитологические исследования;P18_Analysisdate;false;false;false;true;5;921;
CSV;

        return $csv;
    }
}
