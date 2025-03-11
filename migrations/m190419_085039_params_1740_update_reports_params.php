<?php

use app\commands\migrate\Migration;
use app\models\db\Params;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m190419_085039_params_1740_update_reports_params
 */
class m190419_085039_params_1740_update_reports_params extends Migration
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

        $names = [
            'Бланк регистрации и вакцинации животных' => 'Бланк регистрации и вакцинации животных',
            'Биохимия крови' => 'Результат биохимического исследования крови',
            'влаг цитол' => 'Результат цитологического исследования мазка - отпечатка',
            'Гельминто-копр' => 'Гельминто- копрологическое исследование',
            'Клин анализ мочи' => 'Результат клинического анализа мочи',
            'ЛД' => 'Результат люминесцентной диагностики',
            'Микроскопическое исследование' => 'Результат микроскопического исследования',
            'результат биохимического исследования кала' => 'Результат биохимического исследования кала',
            'результаты гормонального исследования крови' => 'Результат гормональных исследований крови',
            'УЗИ глаза' => 'Ультразвуковое исследование глаза',
            'УЗИ мочевыдел' => 'Ультразвуковое исследование мочевыделительной системы',
            'УЗИ печ. и т.д' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
            'УЗИ репр. Самки' => 'Ультразвуковое исследование репродуктивной системы самки',
            'УЗИ репр. Самца' => 'Ультразвуковое исследование репродуктивной системы самца',
            'ЭКГ' => 'Электрокардиографическое исследование',
            'ЭХО-КГ' => 'ЭХО-кардиографическое исследование',
            'ОАК_Mythic' => 'Результат общего клинического анализа крови',
            'Журнал общих клинических исследований мочи' => 'Журнал общих клинических исследований мочи',
            'Журнал учета лабораторных исследований на паразитарные болезни животных' => 'Журнал учета лабораторных исследований на паразитарные болезни животных',
            'Журнал общих исследований фекалий' => 'Журнал общих исследований фекалий',
            'Журнал цитологических исследований' => 'Журнал цитологических исследований',
            'Журнал биохимического исследования крови' => 'Журнал биохимического исследования крови',
            'Журнал регистрации и вакцинации животных' => 'Журнал регистрации и вакцинации животных',
            'Журнал гематологических исследований' => 'Журнал гематологических исследований',
            'Журнал регистрации платных ветеринарных услуг животным' => 'Журнал регистрации платных ветеринарных услуг животным',
            'Журнал по оказанию ветеринарных услуг бригадами неотложной ветеринарной помощи' => 'Журнал по оказанию ветеринарных услуг бригадами неотложной ветеринарной помощи',
        ];

        $reports = [];
        foreach ($names as $short => $name) {
            $report = Reports::findOne(['name' => $name]);
            if ($report === null) {
                Console::output(Console::ansiFormat('Not found report [' . $name . ']', [Console::FG_RED]));
            } else {
                $reports[$short] = $report->id;
            }
        }

        $processed = 0;
        $reportsNotFound = 0;
        $paramsNotFound = 0;
        $ids = [];
        $paramsCreated = 0;
        $paramsCreateErrors = 0;
        $paramsToRemove = [];
        $paramsRemoved = 0;
        $paramsRemoveErrors = 0;

        foreach ($items as $item) {
            // №;id_report;id_param;№;
            $reportName = trim($item[1]);
            $tech_name = trim($item[2]);

            $id_report = ArrayHelper::getValue($reports, $reportName);
            if (empty($id_report)) {
                Console::output(Console::ansiFormat('Not found report [' . $reportName . ']', [Console::FG_RED]));
                $reportsNotFound++;
                $processed++;
                continue;
            }

            $param = $this->findParamByTechName($tech_name);

            if ($param === null) {
                $paramsNotFound++;
                Console::output(Console::ansiFormat('Not found param [' . $tech_name . ']', [Console::FG_RED]));
                $processed++;
                continue;
            }

            $reportParam = ReportsParams::findOne([
                'id_param' => $param->id,
                'id_report' => $id_report,
            ]);

            if ($reportParam !== null) {
                $ids[] = $reportParam->id;
                $processed++;
                continue;
            }

            Console::output(Console::ansiFormat('Creating reports_param for [' . $tech_name . '][' . $reportName . ']...', [Console::FG_YELLOW]));

            $reportParam = new ReportsParams([
                'id_param' => $param->id,
                'id_report' => $id_report,
            ]);

            if ($reportParam->save()) {
                $ids[] = $reportParam->id;
                $paramsCreated++;
                Console::output(Console::ansiFormat('   - OK', [Console::FG_GREEN]));
            } else {
                $paramsCreateErrors++;
                Console::output(Console::ansiFormat('   - Error', [Console::FG_RED]));
            }
            $processed++;
        }

        if (!empty($ids)) {
            $paramsToRemove = ReportsParams::find()
                ->where(['not in', 'id', $ids])
                ->orderBy(['id' => SORT_ASC])
                ->all();

            if (!empty($paramsToRemove)) {
                foreach ($paramsToRemove as $rp) {
                    Console::output(Console::ansiFormat('Removing reports_param [' . $rp->id . ']...', [Console::FG_YELLOW]));
                    if (!$rp->delete()) {
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
        Console::output('Not found reports:' . $reportsNotFound);
        Console::output(Console::ansiFormat('Created reports_params:' . $paramsCreated, [Console::FG_GREEN]));
        if (!empty($paramsCreateErrors)) {
            Console::output(Console::ansiFormat('Create errors:' . $paramsCreateErrors, [Console::FG_RED]));
        }

        Console::output('Params to remove:' . count($paramsToRemove));
        Console::output(Console::ansiFormat('Removed reports_params:' . $paramsRemoved, [Console::FG_GREEN]));
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
            'reports_params',
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

    private function loadCsv()
    {
        // №;id_report;id_param;№;

        $csv = <<<CSV
4;Бланк регистрации и вакцинации животных;P13_Servicetext;4;
7;Биохимия крови;P0_Venousbloodanalysisnum;7;
8;Биохимия крови;P14_Totalbilirubinmcmvalue;8;
9;Биохимия крови;P15_Totalbilirubinmcmdesc;9;
10;Биохимия крови;P16_Totalbilirubinmgvalue;10;
11;Биохимия крови;P17_Totalbilirubinmgdesc;11;
12;Биохимия крови;P18_Conjugatedbilirubinmcmvalue;12;
13;Биохимия крови;P19_Conjugatedbilirubinmcmdesc;13;
14;Биохимия крови;P20_Conjugatedbilirubinmgvalue;14;
15;Биохимия крови;P21_Conjugatedbilirubinmgdesc;15;
16;Биохимия крови;P22_Altalanniamvalue;16;
17;Биохимия крови;P23_Altalanniamvadesc;17;
18;Биохимия крови;P24_Astaspartvalue;18;
19;Биохимия крови;P25_Astaspartdesc;19;
20;Биохимия крови;P26_Mochevinammvalue;20;
21;Биохимия крови;P27_Mochevinammdesc;21;
22;Биохимия крови;P28_Mochevinamgvalue;22;
23;Биохимия крови;P29_Mochevinamgdesc;23;
24;Биохимия крови;P30_Creatininemcmvalue;24;
25;Биохимия крови;P31_Creatininemcmdesc;25;
26;Биохимия крови;P32_Creatininemgvalue;26;
27;Биохимия крови;P33_Creatininemgdesc;27;
28;Биохимия крови;P34_Shelochfosfatvalue;28;
29;Биохимия крови;P35_Shelochfosfatdesc;29;
30;Биохимия крови;P36_Amilazavalue;30;
31;Биохимия крови;P37_Amilazadesc;31;
32;Биохимия крови;P38_Pancreatinevalue;32;
33;Биохимия крови;P39_Pancreatinedesc;33;
34;Биохимия крови;P40_Glukozamcmvalue;34;
35;Биохимия крови;P41_Glukozamcmdesc;35;
36;Биохимия крови;P42_Glukozamgvalue;36;
37;Биохимия крови;P43_Glukozamgdesc;37;
38;Биохимия крови;P44_Ldglactodvalue;38;
39;Биохимия крови;P45_Ldglactoddesc;39;
40;Биохимия крови;P46_Lgtgammavalue;40;
41;Биохимия крови;P47_Lgtgammadesc;41;
42;Биохимия крови;P48_Kfkcreatinevalue;42;
43;Биохимия крови;P49_Kfkcreatinedesc;43;
44;Биохимия крови;P50_Holestermmvalue;44;
45;Биохимия крови;P51_Holestermmdesc;45;
46;Биохимия крови;P52_Holestermgvalue;46;
47;Биохимия крови;P53_Holestermgdesc;47;
48;Биохимия крови;P54_Triglyceridsmmvalue;48;
49;Биохимия крови;P55_Triglyceridsmmdesc;49;
50;Биохимия крови;P56_Triglyceridsmgvalue;50;
51;Биохимия крови;P57_Triglyceridsmgdesc;51;
52;Биохимия крови;P58_Caliummmvalue;52;
53;Биохимия крови;P59_Caliummmdesc;53;
54;Биохимия крови;P60_Caliummecvalue;54;
55;Биохимия крови;P61_Caliummecdesc;55;
56;Биохимия крови;P62_Natrmmvalue;56;
57;Биохимия крови;P63_Natrmmdesc;57;
58;Биохимия крови;P64_Natrmecvalue;58;
59;Биохимия крови;P65_Natrmecdesc;59;
60;Биохимия крови;P66_Phosphormmvalue;60;
61;Биохимия крови;P67_Phosphormmdesc;61;
62;Биохимия крови;P68_Phosphormgvalue;62;
63;Биохимия крови;P69_Phosphormgdesc;63;
64;Биохимия крови;P70_Calciummmvalue;64;
65;Биохимия крови;P71_Calciummmdesc;65;
66;Биохимия крови;P72_Calciummcgvalue;66;
67;Биохимия крови;P73_Calciummcgdesc;67;
68;Биохимия крови;P74_Ironmcmvalue;68;
69;Биохимия крови;P75_Ironmcmdesc;69;
70;Биохимия крови;P76_Ironmcgvalue;70;
71;Биохимия крови;P77_Ironmcgdesc;71;
72;Биохимия крови;P78_Magnesiummmvalue;72;
73;Биохимия крови;P79_Magnesiummmdesc;73;
74;Биохимия крови;P80_Magnesiummecvalue;74;
75;Биохимия крови;P81_Magnesiummecdesc;75;
76;Биохимия крови;P82_Chloridemmvalue;76;
77;Биохимия крови;P83_Chloridemmdesc;77;
78;Биохимия крови;P84_Chloridemecvalue;78;
79;Биохимия крови;P85_Chloridemecdesc;79;
80;Биохимия крови;P86_Acidityvalue;80;
81;Биохимия крови;P87_Aciditydesc;81;
82;Биохимия крови;P88_Mochekislnmvalue;82;
83;Биохимия крови;P89_Mochekislnmdesc;83;
84;Биохимия крови;P90_Mochekislmgvalue;84;
85;Биохимия крови;P91_Mochekislmgdesc;85;
86;Биохимия крови;P92_Lipazavalue;86;
87;Биохимия крови;P93_Lipazadesc;87;
88;Биохимия крови;P94_Totalproteinglvalue;88;
89;Биохимия крови;P95_Totalproteingldesc;89;
90;Биохимия крови;P96_Totalproteingdlvalue;90;
91;Биохимия крови;P97_Totalproteingdldesc;91;
92;Биохимия крови;P98_Albuminglvalue;92;
93;Биохимия крови;P99_Albumingldesc;93;
94;Биохимия крови;P100_Albumingdlvalue;94;
95;Биохимия крови;P101_Albumingdldesc;95;
96;Биохимия крови;P102_Hemoglobinvalue;96;
97;Биохимия крови;P103_Hemoglobindesc;97;
98;Биохимия крови;P104_Ketonebodiesvalue;98;
99;Биохимия крови;P105_Bikarbonatvalue;99;
100;Биохимия крови;P106_Proteinfractionsvalue;100;
101;влаг цитол;P0_Cytologicsanalysisnum;101;
102;влаг цитол;P14_Bazalvalue;102;
103;влаг цитол;P15_Parabazalvalue;103;
104;влаг цитол;P16_Promezhutvalue;104;
105;влаг цитол;P17_Poverkhvalue;105;
106;влаг цитол;P18_Leukocytesvalue;106;
107;влаг цитол;P19_Erythrocytesvalue;107;
108;влаг цитол;P20_Bacteriavalue;108;
109;влаг цитол;P21_Fazatsiklavalue;109;
110;влаг цитол;P22_Recomendvalue;110;
111;влаг цитол;P23_Cytologicscreeninganswerdate;111;
112;Гельминто-копр;P0_Coproalysisnum;112;
113;Гельминто-копр;P12_Serviceresultvalue;113;
114;Гельминто-копр;P13_Serviceresultdesc;114;
115;Клин анализ мочи;P0_Urinalysisnum;115;
116;Клин анализ мочи;P14_Colorurinevalue;116;
117;Клин анализ мочи;P15_Colorurinedesc;117;
118;Клин анализ мочи;P16_Transparencyvalue;118;
119;Клин анализ мочи;P17_Transparencydesc;119;
120;Клин анализ мочи;P18_Acidityvalue;120;
121;Клин анализ мочи;P19_Aciditydesc;121;
122;Клин анализ мочи;P20_Proteinvalue;122;
123;Клин анализ мочи;P21_Proteindesc;123;
124;Клин анализ мочи;P22_Glukozavalue;124;
125;Клин анализ мочи;P23_Glukozadesc;125;
126;Клин анализ мочи;P24_Ketonbodvalue;126;
127;Клин анализ мочи;P25_Ketonboddesc;127;
128;Клин анализ мочи;P26_Relativedensityvalue;128;
129;Клин анализ мочи;P27_Relativedensitydesc;129;
130;Клин анализ мочи;P28_Bilirubinvalue;130;
131;Клин анализ мочи;P29_Bilirubindesc;131;
132;Клин анализ мочи;P30_Hemeglvalue;132;
133;Клин анализ мочи;P31_Hemegldesc;133;
134;Клин анализ мочи;P32_Erythrocytvalue;134;
135;Клин анализ мочи;P33_Erythrocytdesc;135;
136;Клин анализ мочи;P34_Leucocytvalue;136;
137;Клин анализ мочи;P35_Leucocytdesc;137;
138;Клин анализ мочи;P36_Ploskiyvalue;138;
139;Клин анализ мочи;P37_Ploskiydesc;139;
140;Клин анализ мочи;P38_Perehodvalue;140;
141;Клин анализ мочи;P39_Perehoddesc;141;
142;Клин анализ мочи;P40_Pochechnvalue;142;
143;Клин анализ мочи;P41_Pochechndesc;143;
144;Клин анализ мочи;P42_Hyalinevalue;144;
145;Клин анализ мочи;P43_Hyalinedesc;145;
146;Клин анализ мочи;P44_Granularvalue;146;
147;Клин анализ мочи;P45_Granulardesc;147;
148;Клин анализ мочи;P46_Waxvalue;148;
149;Клин анализ мочи;P47_Waxdesc;149;
150;Клин анализ мочи;P48_Lekocitvalue;150;
151;Клин анализ мочи;P49_Lekocitdesc;151;
152;Клин анализ мочи;P50_Eritrocitvalue;152;
153;Клин анализ мочи;P51_Eritrocitdesc;153;
154;Клин анализ мочи;P52_Epitelvalue;154;
155;Клин анализ мочи;P53_Epiteldesc;155;
156;Клин анализ мочи;P54_Cilindvalue;156;
157;Клин анализ мочи;P55_Cilinddesc;157;
158;Клин анализ мочи;P56_Bacteriavalue;158;
159;Клин анализ мочи;P57_Bacteriadesc;159;
160;Клин анализ мочи;P58_Saltvalue;160;
161;Клин анализ мочи;P59_Saltdesc;161;
162;Клин анализ мочи;P61_Urinunitweightvalue;162;
163;Клин анализ мочи;P62_Urinreactionvalue;163;
164;Клин анализ мочи;P63_Nitratvalue;164;
165;Клин анализ мочи;P64_Urinorddepositionvalue;165;
167;Клин анализ мочи;P66_UrinAnswerdate;166;
168;Клин анализ мочи;P67_Urinconsistency;167;
169;ЛД;P12_Serviceresultvalue;168;
170;ЛД;P13_Serviceresultdesc;169;
171;Микроскопическое исследование;P15_Analysisresult;170;
172;Микроскопическое исследование;P16_Analysisdesc;171;
173;Микроскопическое исследование;P0_Capillarybloodanalysisnum;172;
174;Микроскопическое исследование;P14_Analysiscount;173;
175;Микроскопическое исследование;P15_Activator;174;
176;Микроскопическое исследование;P18_Analysisdate;175;
177;Микроскопическое исследование;P19_Animalcount;176;
178;Микроскопическое исследование;P20_Analysisobject;177;
179;Микроскопическое исследование;P21_Diagnostictechnique;178;
180;Микроскопическое исследование;P22_Parasdiseasesanswerdate;179;
181;результат биохимического исследования кала;P14_Coprformvalue;180;
182;результат биохимического исследования кала;P15_Coprformdesc;181;
183;результат биохимического исследования кала;P16_Coprcolorvalue;182;
184;результат биохимического исследования кала;P17_Coprcolordesc;183;
185;результат биохимического исследования кала;P18_Coprodorvalue;184;
186;результат биохимического исследования кала;P19_Coprodordesc;185;
187;результат биохимического исследования кала;P20_Acidityvalue;186;
188;результат биохимического исследования кала;P21_Aciditydesc;187;
189;результат биохимического исследования кала;P22_Stercobilinvalue;188;
190;результат биохимического исследования кала;P23_Stercobilindesc;189;
191;результат биохимического исследования кала;P24_Bilirubinvalue;190;
192;результат биохимического исследования кала;P25_Bilirubindesc;191;
193;результат биохимического исследования кала;P26_Bloodvalue;192;
194;результат биохимического исследования кала;P27_Blooddesc;193;
195;результат биохимического исследования кала;P28_Muscledfibersvalue;194;
196;результат биохимического исследования кала;P29_Muscledfibersdesc;195;
197;результат биохимического исследования кала;P30_Contissuefibersvalue;196;
198;результат биохимического исследования кала;P31_Contissuefibersdesc;197;
199;результат биохимического исследования кала;P32_Neutralfatvalue;198;
200;результат биохимического исследования кала;P33_Neutralfatdesc;199;
201;результат биохимического исследования кала;P34_Fattyacidsvalue;200;
202;результат биохимического исследования кала;P35_Fattyacidsdesc;201;
203;результат биохимического исследования кала;P36_Soapvalue;202;
204;результат биохимического исследования кала;P37_Soapdesc;203;
205;результат биохимического исследования кала;P38_Starchvalue;204;
206;результат биохимического исследования кала;P39_Starchdesc;205;
207;результат биохимического исследования кала;P38_Starchvalue;206;
208;результаты гормонального исследования крови;P14_Cortisolbazalvalue;207;
209;результаты гормонального исследования крови;P15_Cortisolactgvalue;208;
210;результаты гормонального исследования крови;P16_Cortisoldexvalue;209;
211;результаты гормонального исследования крови;P17_Proganesvalue;210;
212;результаты гормонального исследования крови;P18_Progproenstvalue;211;
213;результаты гормонального исследования крови;P19_Progestrusvalue;212;
214;результаты гормонального исследования крови;P20_Progmetestvalue;213;
215;результаты гормонального исследования крови;P21_Estradanesvalue;214;
216;результаты гормонального исследования крови;P22_Estradproenstvalue;215;
217;результаты гормонального исследования крови;P23_Estradestrusvalue;216;
218;результаты гормонального исследования крови;P24_Estradmetestvalue;217;
219;результаты гормонального исследования крови;P25_Estradmalevalue;218;
220;результаты гормонального исследования крови;P26_Testostervalue;219;
221;результаты гормонального исследования крови;P27_Thyroxvalue;220;
222;результаты гормонального исследования крови;P28_Triiodtirvalue;221;
223;результаты гормонального исследования крови;P14_Cortisolbazalvalue;222;
224;УЗИ глаза;P0_Organsystem;223;
225;УЗИ глаза;P14_Odrazmerperednegootrezka;224;
226;УЗИ глаза;P15_Odrazmerzadnegootrezka;225;
227;УЗИ глаза;P16_Odstructuraperedcamer;226;
228;УЗИ глаза;P17_Odrazmerhrust;227;
229;УЗИ глаза;P18_Odstructurahrust;228;
230;УЗИ глаза;P19_Odcapsulahrust;229;
231;УЗИ глаза;P20_Odstructurasteklotelo;230;
232;УЗИ глаза;P21_Oddiametrzrachka;231;
233;УЗИ глаза;P22_Odcontur;232;
234;УЗИ глаза;P23_Odstructura;233;
235;УЗИ глаза;P24_Odstructuradiskazritnerva;234;
236;УЗИ глаза;P25_Odstructuraretrobulyar;235;
237;УЗИ глаза;P26_Osrazmerperednegootrezka;236;
238;УЗИ глаза;P27_Osrazmerzadnegootrezka;237;
239;УЗИ глаза;P28_Osstructuraperedcamer;238;
240;УЗИ глаза;P29_Osrazmerhrust;239;
241;УЗИ глаза;P30_Osstructurahrust;240;
242;УЗИ глаза;P31_Oscapsulahrust;241;
243;УЗИ глаза;P32_Osstructurasteklotelo;242;
244;УЗИ глаза;P33_Osdiametrzrachka;243;
245;УЗИ глаза;P34_Oscontur;244;
246;УЗИ глаза;P35_Osstructura;245;
247;УЗИ глаза;P36_Osstructuradiskazritnerva;246;
248;УЗИ глаза;P37_Osstructuraretrobulyar;247;
249;УЗИ глаза;P38_Serviceresult;248;
250;УЗИ мочевыдел;P0_UltrasoundFile;249;
251;УЗИ мочевыдел;P0_Abdomenorgansystem;250;
252;УЗИ мочевыдел;P0_Organsystem;251;
253;УЗИ мочевыдел;P14_Prvpochraspoloshenie;252;
254;УЗИ мочевыдел;P15_Prvpochgranica;253;
255;УЗИ мочевыдел;P16_Prvpochrazmer;254;
256;УЗИ мочевыдел;P17_Prvpochkortiksloytolshina;255;
257;УЗИ мочевыдел;P18_Prvpochkortiksloyekhogennost;256;
258;УЗИ мочевыдел;P19_Prvpochkortiksloyekhostruktura;257;
259;УЗИ мочевыдел;P20_Prvpochmedullyarsloytolshchina;258;
260;УЗИ мочевыдел;P21_Prvpochmedullyarsloyekhogennost;259;
261;УЗИ мочевыдел;P22_Prvpochmedullyarsloyekhostruktura;260;
262;УЗИ мочевыдел;P23_Prvpochmedullyarsloykortmeddiffer;261;
263;УЗИ мочевыдел;P24_Prvpochpiyelicheskiyindeks;262;
264;УЗИ мочевыдел;P25_Prvpochpochsinusekhogennost;263;
265;УЗИ мочевыдел;P26_Prvpochpochsinuschetkostdifferents;264;
266;УЗИ мочевыдел;P27_Prvpochpochsinuspolostlokhanki;265;
267;УЗИ мочевыдел;P28_Prvpochpochsinusstepenlokhanki;266;
268;УЗИ мочевыдел;P29_Prvpochsosudyparenkhimy;267;
269;УЗИ мочевыдел;P30_PrvpochIndeksrezistivnpochechnart;268;
270;УЗИ мочевыдел;P31_PrvpochIndeksrezistivnmezhdolevoyart;269;
271;УЗИ мочевыдел;P32_Prvpochkonkrementy;270;
272;УЗИ мочевыдел;P33_Prvpochobyemnobrazov;271;
273;УЗИ мочевыдел;P34_Levpochraspoloshenie;272;
274;УЗИ мочевыдел;P35_Levpochgranica;273;
275;УЗИ мочевыдел;P36_Levpochrazmer;274;
276;УЗИ мочевыдел;P37_Levpochkortiksloytolshina;275;
277;УЗИ мочевыдел;P38_Levpochkortiksloyekhogennost;276;
278;УЗИ мочевыдел;P39_Levpochkortiksloyekhostruktura;277;
279;УЗИ мочевыдел;P40_Levpochmedullyarsloytolshchina;278;
280;УЗИ мочевыдел;P41_Levpochmedullyarsloyekhogennost;279;
281;УЗИ мочевыдел;P42_Levpochmedullyarsloyekhostruktura;280;
282;УЗИ мочевыдел;P43_Levpochmedullyarsloykortmeddiffer;281;
283;УЗИ мочевыдел;P44_Levpochpiyelicheskiyindeks;282;
284;УЗИ мочевыдел;P45_Levpochpochsinusekhogennost;283;
285;УЗИ мочевыдел;P46_Levpochpochsinuschetkostdifferents;284;
286;УЗИ мочевыдел;P47_Levpochpochsinuspolostlokhanki;285;
287;УЗИ мочевыдел;P48_Levpochpochsinusstenkilokhanki;286;
288;УЗИ мочевыдел;P49_Levpochsosudyparenkhimy;287;
289;УЗИ мочевыдел;P50_LevpochIndeksrezistivnpochechnart;288;
290;УЗИ мочевыдел;P51_LevpochIndeksrezistivnmezhdolevoyart;289;
291;УЗИ мочевыдел;P52_Levpochkonkrementy;290;
292;УЗИ мочевыдел;P53_Levpochobyemnobrazov;291;
293;УЗИ мочевыдел;P54_Mochpuzstepnapoln;292;
294;УЗИ мочевыдел;P55_Mochpuztolshchinastenki;293;
295;УЗИ мочевыдел;P56_Mochpuzdeformatsiya;294;
296;УЗИ мочевыдел;P57_Mochpuzuretra;295;
297;УЗИ мочевыдел;P58_MochpuzObyemnobrazov;296;
298;УЗИ мочевыдел;P59_Serviceresult;297;
299;УЗИ печ. и т.д;P0_UltrasoundFile;298;
300;УЗИ печ. и т.д;P0_Abdomenorgansystem;299;
301;УЗИ печ. и т.д;P0_Organsystem;300;
302;УЗИ печ. и т.д;P14_Pechenraspoloshenie;301;
303;УЗИ печ. и т.д;P15_Pechenkontur;302;
304;УЗИ печ. и т.д;P16_Pechenrazmer;303;
305;УЗИ печ. и т.д;P17_Pechenekhostruktura;304;
306;УЗИ печ. и т.д;P18_Pechenekhogennost;305;
307;УЗИ печ. и т.д;P19_Pechenperifsosudrisunok;306;
308;УЗИ печ. и т.д;P20_Pechenportae;307;
309;УЗИ печ. и т.д;P21_Pechenvhepatica;308;
310;УЗИ печ. и т.д;P22_Pechenahepatica;309;
311;УЗИ печ. и т.д;P23_Pechenobyemnobrazov;310;
312;УЗИ печ. и т.д;P24_Zhelchpuzyrstepennapolneniya;311;
313;УЗИ печ. и т.д;P25_Zhelchpuzyrformazhelchpuzyrya;312;
314;УЗИ печ. и т.д;P26_Zhelchpuzyrtolshchinastenki;313;
315;УЗИ печ. и т.д;P27_Zhelchpuzyrdeformatsiya;314;
316;УЗИ печ. и т.д;P28_Zhelchpuzyrstrukturazhelchi;315;
317;УЗИ печ. и т.д;P29_Zhelchpuzyrpuzyrprotok;316;
318;УЗИ печ. и т.д;P30_Zhelchpuzyrobshzhelchprotok;317;
319;УЗИ печ. и т.д;P31_Zhelchpuzyrpechenochnprotok;318;
320;УЗИ печ. и т.д;P32_Zhelchpuzyrobyemnobrazov;319;
321;УЗИ печ. и т.д;P33_Selezenkaraspoloshenie;320;
322;УЗИ печ. и т.д;P34_Selezenkakontur;321;
323;УЗИ печ. и т.д;P35_Selezenkarazmer;322;
324;УЗИ печ. и т.д;P36_Selezenkaekhostruktura;323;
325;УЗИ печ. и т.д;P37_Selezenkaekhogennost;324;
326;УЗИ печ. и т.д;P38_Selezenkasosudrisunok;325;
327;УЗИ печ. и т.д;P39_Selezenkaobyemnobrazov;326;
328;УЗИ печ. и т.д;P40_Podzhelzhelezaraspoloshenie;327;
329;УЗИ печ. и т.д;P41_Podzhelzhelezakontur;328;
330;УЗИ печ. и т.д;P42_Podzhelzhelezarazmer;329;
331;УЗИ печ. и т.д;P43_Podzhelzhelezaekhostruktura;330;
332;УЗИ печ. и т.д;P44_Podzhelzhelezaekhogennost;331;
333;УЗИ печ. и т.д;P45_Podzhelzhelezaobyemnobrazov;332;
334;УЗИ печ. и т.д;P46_Zheludkishechntrakt;333;
335;УЗИ печ. и т.д;P47_Svobodnzhidkost;334;
336;УЗИ печ. и т.д;P48_Serviceresult;335;
337;УЗИ репр. Самки;P0_UltrasoundFile;336;
338;УЗИ репр. Самки;P0_Abdomenorgansystem;337;
339;УЗИ репр. Самки;P0_Organsystem;338;
340;УЗИ репр. Самки;P14_Matkadiametrtela;339;
341;УЗИ репр. Самки;P15_Matkatolshinatela;340;
342;УЗИ репр. Самки;P16_Matkastrukturastenkitela;341;
343;УЗИ репр. Самки;P17_Matkasostoyanpolosti;342;
344;УЗИ репр. Самки;P18_Matkadiametrpravroga;343;
345;УЗИ репр. Самки;P19_Matkatolshinapravroga;344;
346;УЗИ репр. Самки;P20_Matkastrukturastenkipravroga;345;
347;УЗИ репр. Самки;P21_Matkasoderzhimpolostipravroga;346;
348;УЗИ репр. Самки;P22_Matkadiametrlevroga;347;
349;УЗИ репр. Самки;P23_Matkatolshinalevroga;348;
350;УЗИ репр. Самки;P24_Matkastrukturastenkilevroga;349;
351;УЗИ репр. Самки;P25_Matkasoderzhimpolostilevroga;350;
352;УЗИ репр. Самки;P26_Pravyaichnikrazmer;351;
353;УЗИ репр. Самки;P27_Pravyaichnikkontur;352;
354;УЗИ репр. Самки;P28_Pravyaichniknovoobrazov;353;
355;УЗИ репр. Самки;P29_Levyaichnikrazmer;354;
356;УЗИ репр. Самки;P30_Levyaichnikkontur;355;
357;УЗИ репр. Самки;P31_Levyaichniknovoobrazov;356;
358;УЗИ репр. Самки;P32_Serviceresult;357;
359;УЗИ репр. Самца ;P0_UltrasoundFile;358;
360;УЗИ репр. Самца ;P0_Abdomenorgansystem;359;
361;УЗИ репр. Самца ;P0_Organsystem;360;
362;УЗИ репр. Самца ;P14_Predstzhelezarazmer;361;
363;УЗИ репр. Самца ;P15_Predstzhelezakontur;362;
364;УЗИ репр. Самца ;P16_Predstzhelezaparenkhima;363;
365;УЗИ репр. Самца ;P17_Predstzhelezaobyemnobrazov;364;
366;УЗИ репр. Самца ;P18_Pravsemrazmer;365;
367;УЗИ репр. Самца ;P19_Pravsemkontur;366;
368;УЗИ репр. Самца ;P20_Pravsemparenkhima;367;
369;УЗИ репр. Самца ;P21_Pravsemobyemnobrazov;368;
370;УЗИ репр. Самца ;P22_Pridatokpravsemgolovka;369;
371;УЗИ репр. Самца ;P23_Pridatokpravsemtelo;370;
372;УЗИ репр. Самца ;P24_Pridatokpravsemobyemnobrazov;371;
373;УЗИ репр. Самца ;P25_Levsemrazmer;372;
374;УЗИ репр. Самца ;P26_Levsemkontur;373;
375;УЗИ репр. Самца ;P27_Levsemparenkhima;374;
376;УЗИ репр. Самца ;P28_Levsemobyemnobrazov;375;
377;УЗИ репр. Самца ;P29_Pridatoklevsemgolovka;376;
378;УЗИ репр. Самца ;P30_Pridatoklevsemtelo;377;
379;УЗИ репр. Самца ;P31_Pridatoklevsemobyemnobrazov;378;
380;УЗИ репр. Самца ;P32_Abdomultmserviceresult;379;
381;ЭКГ;P12_Pc;380;
382;ЭКГ;P13_Pmv;381;
383;ЭКГ;P14_Р1;382;
384;ЭКГ;P15_P2;383;
385;ЭКГ;P16_P3;384;
386;ЭКГ;P17_Pq;385;
387;ЭКГ;P18_Qrs;386;
388;ЭКГ;P19_Qrsdesc;387;
389;ЭКГ;P20_Rmv;388;
390;ЭКГ;P21_Rdesc;389;
391;ЭКГ;P22_Tmv;390;
392;ЭКГ;P23_Tdesc;391;
393;ЭКГ;P24_St;392;
394;ЭКГ;P25_Qt;393;
395;ЭКГ;P26_Eos;394;
396;ЭКГ;P27_Chss;395;
397;ЭКГ;P28_Ritm;396;
398;ЭКГ;P29_Ekstrasistoly;397;
399;ЭКГ;P30_Serviceresult;398;
400;ЭХО-КГ;P14_LVIDd;399;
401;ЭХО-КГ;P15_LVIDs;400;
402;ЭХО-КГ;P16_LVWTd;401;
403;ЭХО-КГ;P17_LVWTs;402;
404;ЭХО-КГ;P18_IVSTd;403;
405;ЭХО-КГ;P19_IVSTs;404;
406;ЭХО-КГ;P20_EF;405;
407;ЭХО-КГ;P21_FS;406;
408;ЭХО-КГ;P22_LA;407;
409;ЭХО-КГ;P23_AO;408;
410;ЭХО-КГ;P24_LA/AO;409;
411;ЭХО-КГ;P25_RVIDd;410;
412;ЭХО-КГ;P26_RVIDs;411;
413;ЭХО-КГ;P27_RVWTd;412;
414;ЭХО-КГ;P28_RVWTs;413;
415;ЭХО-КГ;P29_RA;414;
416;ЭХО-КГ;P30_Defektivs;415;
417;ЭХО-КГ;P31_Defektias;416;
418;ЭХО-КГ;P32_Svobodnzhidkostperikarde;417;
419;ЭХО-КГ;P33_Svobodnzhidkostplevralpolosti;418;
420;ЭХО-КГ;P34_Novoobrazov;419;
421;ЭХО-КГ;P35_Mitrklapnstvorki;420;
422;ЭХО-КГ;P36_Mitrklapnskorostkrovotoka;421;
423;ЭХО-КГ;P37_Mitrklapnregurgitatsiya;422;
424;ЭХО-КГ;P38_Trikuspklapnstvorki;423;
425;ЭХО-КГ;P39_Trikuspklapnskorostkrovotoka;424;
426;ЭХО-КГ;P40_Trikuspklapnregurgitatsiya;425;
427;ЭХО-КГ;P41_Aortaklapnstvorki;426;
428;ЭХО-КГ;P42_Aortaklapnskorostkrovotoka;427;
429;ЭХО-КГ;P43_Aortaklapnregurgitatsiya;428;
430;ЭХО-КГ;P44_Klapnlegartstvorki;429;
431;ЭХО-КГ;P45_Klapnlegartskorostkrovotoka;430;
432;ЭХО-КГ;P46_Klapnlegartregurgitatsiya;431;
433;ЭХО-КГ;P47_Serviceresult;432;
434;ОАК_Mythic;P14_Wbcvalue;433;
435;ОАК_Mythic;P15_Wbcdesc;434;
436;ОАК_Mythic;P16_Lymvalue;435;
437;ОАК_Mythic;P17_Lymdesc;436;
438;ОАК_Mythic;P18_Monvalue;437;
439;ОАК_Mythic;P19_Mondesc;438;
440;ОАК_Mythic;P20_Gravalue;439;
441;ОАК_Mythic;P21_Gradesc;440;
442;ОАК_Mythic;P22_Rbcvalue;441;
443;ОАК_Mythic;P23_Rbcdesc;442;
444;ОАК_Mythic;P24_Hgbvalue;443;
445;ОАК_Mythic;P25_Hgbdesc;444;
446;ОАК_Mythic;P26_Hctvalue;445;
447;ОАК_Mythic;P27_Hctdesc;446;
448;ОАК_Mythic;P28_Mcvvalue;447;
449;ОАК_Mythic;P29_Mcvdesc;448;
450;ОАК_Mythic;P30_Mchvalue;449;
451;ОАК_Mythic;P31_Mchdesc;450;
452;ОАК_Mythic;P32_Mchcvalue;451;
453;ОАК_Mythic;P33_Mchcdesc;452;
454;ОАК_Mythic;P34_Rdwvalue;453;
455;ОАК_Mythic;P35_Rdwdesc;454;
456;ОАК_Mythic;P36_Pltvalue;455;
457;ОАК_Mythic;P37_Pltdesc;456;
458;ОАК_Mythic;P38_Mpvvalue;457;
459;ОАК_Mythic;P39_Mpvdesc;458;
460;ОАК_Mythic;P40_Pctvalue;459;
461;ОАК_Mythic;P41_Pctdesc;460;
462;ОАК_Mythic;P42_Pdwvalue;461;
463;ОАК_Mythic;P43_Pdwdesc;462;
464;ОАК_Mythic;P44_Soevalue;463;
465;ОАК_Mythic;P45_Soedesc;464;
466;ОАК_Mythic;P46_Youngvalue;465;
467;ОАК_Mythic;P47_Youngdesc;466;
468;ОАК_Mythic;P48_Palochkoyadervalue;467;
469;ОАК_Mythic;P49_Palochkoyaderdesc;468;
470;ОАК_Mythic;P50_Segmentvalue;469;
471;ОАК_Mythic;P51_Segmentdesc;470;
472;ОАК_Mythic;P52_Eosinophilsvalue;471;
473;ОАК_Mythic;P53_Eosinophilsdesc;472;
474;ОАК_Mythic;P54_Monocitvalue;473;
475;ОАК_Mythic;P55_Monocitdesc;474;
476;ОАК_Mythic;P56_Bazophilvalue;475;
477;ОАК_Mythic;P57_Bazophildesc;476;
478;ОАК_Mythic;P58_Limphocitvalue;477;
479;ОАК_Mythic;P59_Limphocitdesc;478;
480;Журнал биохимического исследования крови;P3_Visitstartdate;479;
481;Журнал биохимического исследования крови;P4_Ownername;480;
482;Журнал биохимического исследования крови;P5_Owneraddres;481;
483;Журнал биохимического исследования крови;P0_Venousbloodanalysisnum;482;
484;Журнал биохимического исследования крови;P6_Speciesname;483;
485;Журнал биохимического исследования крови;P10_Petbirthday;484;
486;Журнал биохимического исследования крови;P8_Petsex;485;
487;Журнал биохимического исследования крови;P14_Totalbilirubinmcmvalue;486;
488;Журнал биохимического исследования крови;P18_Conjugatedbilirubinmcmvalue;487;
489;Журнал биохимического исследования крови;P22_Altalanniamvalue;488;
490;Журнал биохимического исследования крови;P24_Astaspartvalue;489;
491;Журнал биохимического исследования крови;P36_Amilazavalue;490;
492;Журнал биохимического исследования крови;P34_Shelochfosfatvalue;491;
493;Журнал биохимического исследования крови;P26_Mochevinammvalue;492;
494;Журнал биохимического исследования крови;P30_Creatininemcmvalue;493;
495;Журнал биохимического исследования крови;P50_Holestermmvalue;494;
496;Журнал биохимического исследования крови;P54_Triglyceridsmmvalue;495;
497;Журнал биохимического исследования крови;P40_Glukozamcmvalue;496;
498;Журнал биохимического исследования крови;P104_Ketonebodiesvalue;497;
499;Журнал биохимического исследования крови;P105_Bikarbonatvalue;498;
500;Журнал биохимического исследования крови;P94_Totalproteinglvalue;499;
501;Журнал биохимического исследования крови;P70_Calciummmvalue;500;
502;Журнал биохимического исследования крови;P66_Phosphormmvalue;501;
503;Журнал биохимического исследования крови;P58_Caliummmvalue;502;
504;Журнал биохимического исследования крови;P62_Natrmmvalue;503;
505;Журнал биохимического исследования крови;P106_Proteinfractionsvalue;504;
506;Журнал регистрации и вакцинации животных;P0_Balanceinventorynumber;505;
507;Журнал регистрации и вакцинации животных;P0_Petchpidentificationcode;506;
508;Журнал регистрации и вакцинации животных;P0_Petstampidentificationcode;507;
509;Журнал регистрации и вакцинации животных;P0_Petregnum;508;
510;Журнал регистрации и вакцинации животных;P0_Vaccinename;509;
511;Журнал регистрации и вакцинации животных;P0_VisitServiceTMCcount;510;
512;Журнал регистрации и вакцинации животных;P10_Petbirthday;511;
513;Журнал регистрации и вакцинации животных;P17_Petcolor;512;
514;Журнал регистрации и вакцинации животных;P18_Petspecialtrait;513;
515;Журнал регистрации и вакцинации животных;P19_Petregexpiredate;514;
516;Журнал регистрации и вакцинации животных;P3_Visitstartdate;515;
517;Журнал регистрации и вакцинации животных;P4_Ownername;516;
518;Журнал регистрации и вакцинации животных;P5_Owneraddres;517;
519;Журнал регистрации и вакцинации животных;P5_Ownercontact;518;
520;Журнал регистрации и вакцинации животных;P6_Speciesname;519;
521;Журнал регистрации и вакцинации животных;P7_Breedname;520;
522;Журнал регистрации и вакцинации животных;P8_Petsex;521;
523;Журнал регистрации и вакцинации животных;P15_Vacexpirationdate;522;
524;Журнал регистрации и вакцинации животных;P9_Petname;523;
525;Журнал общих клинических исследований мочи;P3_Visitstartdate;524;
526;Журнал общих клинических исследований мочи;P33_SpecialistFIO;525;
527;Журнал общих клинических исследований мочи;P5_Owneraddres;526;
528;Журнал общих клинических исследований мочи;P6_Speciesname;527;
529;Журнал общих клинических исследований мочи;P10_Petbirthday;528;
530;Журнал общих клинических исследований мочи;P9_Petname;529;
531;Журнал общих клинических исследований мочи;P14_Colorurinevalue;530;
532;Журнал общих клинических исследований мочи;P16_Transparencyvalue;531;
533;Журнал общих клинических исследований мочи;P67_Urinconsistency;532;
534;Журнал общих клинических исследований мочи;P61_Urinunitweightvalue;533;
535;Журнал общих клинических исследований мочи;P62_Urinreactionvalue;534;
536;Журнал общих клинических исследований мочи;P34_Leucocytvalue;535;
537;Журнал общих клинических исследований мочи;P63_Nitratvalue;536;
538;Журнал общих клинических исследований мочи;P20_Proteinvalue;537;
539;Журнал общих клинических исследований мочи;P22_Glukozavalue;538;
540;Журнал общих клинических исследований мочи;P24_Ketonbodvalue;539;
541;Журнал общих клинических исследований мочи;P22_Glukozavalue;540;
542;Журнал общих клинических исследований мочи;P28_Bilirubinvalue;541;
543;Журнал общих клинических исследований мочи;P32_Erythrocytvalue;542;
544;Журнал общих клинических исследований мочи;P64_Urinorddepositionvalue;543;
546;Журнал общих клинических исследований мочи;P66_UrinAnswerdate;544;
547;Журнал учета лабораторных исследований на паразитарные болезни животных;P0_Capillarybloodanalysisnum;545;
548;Журнал учета лабораторных исследований на паразитарные болезни животных;P18_Analysisdate;546;
549;Журнал учета лабораторных исследований на паразитарные болезни животных;P3_Visitstartdate;547;
550;Журнал учета лабораторных исследований на паразитарные болезни животных;P4_Ownername;548;
551;Журнал учета лабораторных исследований на паразитарные болезни животных;P5_Owneraddres;549;
552;Журнал учета лабораторных исследований на паразитарные болезни животных;P6_Speciesname;550;
553;Журнал учета лабораторных исследований на паразитарные болезни животных;P10_Petbirthday;551;
554;Журнал учета лабораторных исследований на паразитарные болезни животных;P19_Animalcount;552;
555;Журнал учета лабораторных исследований на паразитарные болезни животных;P18_Analysisdate;553;
556;Журнал учета лабораторных исследований на паразитарные болезни животных;P12_Analysisnum;554;
557;Журнал учета лабораторных исследований на паразитарные болезни животных;P14_Analysiscount;555;
558;Журнал учета лабораторных исследований на паразитарные болезни животных;P20_Analysisobject;556;
559;Журнал учета лабораторных исследований на паразитарные болезни животных;P21_Diagnostictechnique;557;
560;Журнал учета лабораторных исследований на паразитарные болезни животных;P15_Analysisresult;558;
561;Журнал учета лабораторных исследований на паразитарные болезни животных;P15_Activator;559;
562;Журнал учета лабораторных исследований на паразитарные болезни животных;P22_Parasdiseasesanswerdate;560;
567;Журнал общих исследований фекалий;P10_Petbirthday;561;
571;Журнал общих исследований фекалий;P14_Coprformvalue;562;
569;Журнал общих исследований фекалий;P16_Coprcolorvalue;563;
570;Журнал общих исследований фекалий;P18_Coprodorvalue;564;
572;Журнал общих исследований фекалий;P20_Acidityvalue;565;
575;Журнал общих исследований фекалий;P22_Stercobilinvalue;566;
574;Журнал общих исследований фекалий;P24_Bilirubinvalue;567;
573;Журнал общих исследований фекалий;P26_Bloodvalue;568;
576;Журнал общих исследований фекалий;P28_Muscledfibersvalue;569;
563;Журнал общих исследований фекалий;P3_Visitstartdate;570;
577;Журнал общих исследований фекалий;P30_Contissuefibersvalue;571;
578;Журнал общих исследований фекалий;P32_Neutralfatvalue;572;
564;Журнал общих исследований фекалий;P33_SpecialistFIO;573;
579;Журнал общих исследований фекалий;P34_Fattyacidsvalue;574;
580;Журнал общих исследований фекалий;P36_Soapvalue;575;
581;Журнал общих исследований фекалий;P38_Starchvalue;576;
582;Журнал общих исследований фекалий;P39_Coproanswerdate;577;
565;Журнал общих исследований фекалий;P5_Owneraddres;578;
566;Журнал общих исследований фекалий;P6_Speciesname;579;
568;Журнал общих исследований фекалий;P9_Petname;580;
583;Журнал цитологических исследований;P0_Cytologicsanalysisnum;581;P39_Coproanswerdate
584;Журнал цитологических исследований;P3_Visitstartdate;582;
585;Журнал цитологических исследований;P4_Ownername;583;
586;Журнал цитологических исследований;P5_Owneraddres;584;
587;Журнал цитологических исследований;P6_Speciesname;585;
588;Журнал цитологических исследований;P24_Anamnesis;586;
589;Журнал цитологических исследований;P14_Bazalvalue;587;
590;Журнал цитологических исследований;P15_Parabazalvalue;588;
591;Журнал цитологических исследований;P16_Promezhutvalue;589;
592;Журнал цитологических исследований;P17_Poverkhvalue;590;
593;Журнал цитологических исследований;P18_Leukocytesvalue;591;
594;Журнал цитологических исследований;P19_Erythrocytesvalue;592;
595;Журнал цитологических исследований;P20_Bacteriavalue;593;
596;Журнал цитологических исследований;P21_Fazatsiklavalue;594;
597;Журнал цитологических исследований;P22_Recomendvalue;595;
598;Журнал цитологических исследований;P23_Cytologicscreeninganswerdate;596;
599;Бланк регистрации и вакцинации животных;P15_Vacexpirationdate;597;
600;УЗИ глаза;P0_UltrasoundFile;598;
601;УЗИ глаза;P13_Serviceresultdesc;599;
602;УЗИ мочевыдел;P13_Serviceresultdesc;600;
603;УЗИ печ. и т.д;P13_Serviceresultdesc;601;
604;УЗИ репр. Самки;P13_Serviceresultdesc;602;
605;УЗИ репр. Самца ;P13_Serviceresultdesc;603;
606;Бланк регистрации и вакцинации животных;P2_SerialServiceNum;600
607;Биохимия крови;P2_SerialServiceNum;601
608;влаг цитол;P2_SerialServiceNum;602
609;Гельминто-копр;P2_SerialServiceNum;603
610;Клин анализ мочи;P2_SerialServiceNum;604
611;ЛД;P2_SerialServiceNum;605
612;Микроскопическое исследование;P2_SerialServiceNum;606
613;результат биохимического исследования кала;P2_SerialServiceNum;607
614;результаты гормонального исследования крови;P2_SerialServiceNum;608
615;УЗИ глаза;P2_SerialServiceNum;609
616;УЗИ мочевыдел;P2_SerialServiceNum;610
617;УЗИ печ. и т.д;P2_SerialServiceNum;611
618;УЗИ репр. Самки;P2_SerialServiceNum;612
619;УЗИ репр. Самца;P2_SerialServiceNum;613
620;ЭКГ;P2_SerialServiceNum;614
621;ЭХО-КГ;P2_SerialServiceNum;615
622;ОАК_Mythic;P2_SerialServiceNum;616
CSV;

        return $csv;
    }
}
