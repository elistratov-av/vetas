<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m180921_104612_update_params_reports
 */
class m180921_104612_update_params_reports extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTmpTable();
        $this->createTmpTable();
        $this->truncateTables();
        $this->resetSequences();

        $this->fixOwnernameP4();
        $this->loadParams();
        $this->linkServiceParams();
        $this->loadReports();
        $this->linkServiceReports();
        $this->linkReportParams();

        $this->dropTmpTable();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTmpTable();
        $this->resetSequences();
    }

    private function fixOwnernameP4()
    {
        $param = $this->findParamByTechName('P4_Ownername');
        if (!empty($param)) {
            \Yii::$app->db
                ->createCommand()
                ->update('params', ['name' => 'Наименование организации/ ФИО владельца'], ['id' => $param['id']])
                ->execute();
        }
    }

    private function loadParams()
    {
        $csv = <<<CSV
P0_Petchpidentificationcode;№ чипа;text;50;true
P0_Petlabelidentificationcode;№ бирки;text;50;true
P10_Petbirthday;Возраст животного;dttm;;true
P8_Petsex;Пол животного;text;1;true
P52_Holestermgvalue;Холестерол (мг/дл) - результат исследований;numeric;3,2;false
P53_Holestermgdesc;Холестерол (мг/дл) - примечание;text;255;false
P0_Biopsycytologicsanalysisnum;№ пробы;text;50;false
P0_Capillarybloodanalysisnum;№ пробы крови из капилляра;text;50;false
P0_Coproalysisnum;№ пробы;text;50;false
P0_Cytologicsanalysisnum;№ мазка - отпечатка;text;50;false
P0_Diagnostictestinganalysisnum;№ пробы;text;50;false
P0_Expdiagnosglucosevalue;Уровень глюкозы (ммоль/л);numeric;2,1;false
P0_Petweight;Вес животного (кг);numeric;4,2;false
P0_Petwoollength;Длина шерсти животного (см);numeric;2;false
P0_Schirmertestresult;Результат теста;numeric;2;false
P0_Urinalysisnum;№ пробы;text;50;false
P0_Venousbloodanalysisnum;№ пробы крови из вены;text;50;false
P100_Albumingdlvalue;Альбумины (г/дл) - результат исследований;numeric;3,2;false
P101_Albumingdldesc;Альбумины (г/дл) - примечание;text;255;false
P102_Hemoglobinvalue;Гемоглобин - результат исследований;numeric;3,2;false
P103_Hemoglobindesc;Гемоглобин - примечание;text;255;false
P12_Analysisnum;Номер анализа;text;50;false
P12_Pc;P в c;numeric;2,2;false
P12_Serviceresultvalue;Результат исследования;text;255;false
P13_Pmv;P в mD;numeric;2,2;false
P13_Serviceresultdesc;Примечание;text;255;false
P13_Servicetext;Описание;text;255;false
P14_Analysiscount;Количество проб;numeric;3;false
P14_Bazalvalue;базальные;numeric;3,2;false
P14_Colorurinevalue;Цвет мочи - результат исследования;dict;Urincolor;false
P14_Coprformvalue;Консистенция, форма - результат исследования;dict;Coprform;false
P14_Cortisolbazalvalue;Кортизол базальный;numeric;3;false
P14_Odrazmerperednegootrezka;Размер переднего отрезка оси (od);numeric;3,2;false
P14_Pechenraspoloshenie;Печень: Расположение;dict;Abdomendposition;false
P14_Predstzhelezarazmer;Предстательная железа: Размеры;numeric;4,2;false
P14_Prvpochraspoloshenie;Правая почка: Расположение;dict;Kidneyposition;false
P14_Totalbilirubinmcmvalue;билирубин общий (мкмоль/л) - результат исследований;numeric;3,2;false
P14_Wbcvalue;WBC (общее кол-во лейкоцитов) - результат исследования;numeric;2,1;false
P14_Р1;Р (I);numeric;2,2;false
P15_Analysisresult;Ход и результат исследования;text;255;false
P15_Colorurinedesc;Цвет мочи - примечание;text;255;false
P15_Coprformdesc;Консистенция, форма - примечание;text;255;false
P15_Cortisolactgvalue;Кортизол после стимуляции АКТГ;numeric;3;false
P15_Odrazmerzadnegootrezka;Размер заднего отрезка оси (od);numeric;3,2;false
P15_P2;P (II);numeric;2,2;false
P15_Parabazalvalue;парабазальные;numeric;3,2;false
P15_Pechenkontur;Печень: Контуры;numeric;4,2;false
P15_Predstzhelezakontur;Предстательная железа: Контуры;numeric;4,2;false
P15_Prvpochgranica;Правая почка: Границы;numeric;4,2;false
P15_Totalbilirubinmcmdesc;билирубин общий (мкмоль/л) - примечание;text;255;false
P15_Wbcdesc;WBC (общее кол-во лейкоцитов) - примечание;text;255;false
P16_Analysisdesc;Примечание;text;255;false
P16_Coprcolorvalue;Цвет - результат исследования;dict;Coprcolor;false
P16_Cortisoldexvalue;Кортизол 8ч после дексаметазоновой пробы (0,01-0,015 мг/кг);numeric;3;false
P16_Lymvalue;LYM (лимфоциты) - результат исследования;numeric;2,1;false
P16_Odstructuraperedcamer;Структура передней камеры (od);text;100;false
P16_P3;P (III);numeric;2,2;false
P16_Pechenrazmer;Печень: Размеры;numeric;4,2;false
P16_Predstzhelezaparenkhima;Предстательная железа: Паренхима;dict;Parenchymatous;false
P16_Promezhutvalue;Промежуточные;numeric;3,2;false
P16_Prvpochrazmer;Правая почка: Размеры;numeric;4,2;false
P16_Totalbilirubinmgvalue;билирубин общий (мг/дл) - результат исследований;numeric;3,2;false
P16_Transparencyvalue;Прозрачность - результат исследования;dict;Urintransparency;false
P17_Coprcolordesc;Цвет - примечание;text;255;false
P17_Lymdesc;LYM (лимфоциты) - примечание;text;255;false
P17_Odrazmerhrust;Размеры хрусталика (od);numeric;3,2;false
P17_Pechenekhostruktura;Печень: Эхоструктура;numeric;4,2;false
P17_Poverkhvalue;поверхностные;numeric;3,2;false
P17_Pq;P-Q;numeric;2,2;false
P17_Predstzhelezaobyemnobrazov;Предстательная железа: Объемные образования;numeric;4,2;false
P17_Proganesvalue;Прогестерон - анэструс;numeric;2,1;false
P17_Prvpochkortiksloytolshina;Правая почка: кортикальный слой - Толщина;numeric;4,2;false
P17_Totalbilirubinmgdesc;билирубин общий (мг/дл) - примечание;text;255;false
P17_Transparencydesc;Прозрачность - примечание;text;255;false
P18_Acidityvalue;Кислотность (рН) - результат исследования;numeric;2,1;false
P18_Analysisdate;Дата взятия пробы;dttm;;false
P18_Conjugatedbilirubinmcmvalue;билирубин прямой (мкмоль/л) - результат исследований;numeric;3,2;false
P18_Coprodorvalue;Запах - результат исследования;dict;Coprodor;false
P18_Leukocytesvalue;Лейкоциты;numeric;3,2;false
P18_Monvalue;MON (моноциты) - результат исследования;numeric;2,1;false
P18_Odstructurahrust;Структура хрусталика (od);text;100;false
P18_Pechenekhogennost;Печень: Эхогенность паренхимы;dict;Abdomendechogenicity;false
P18_Pravsemrazmer;Правый семенник: Размеры;numeric;4,2;false
P18_Progproenstvalue;Прогестерон - проэструс;numeric;2,2;false
P18_Prvpochkortiksloyekhogennost;Правая почка: кортикальный слой - Эхогенность;dict;Kidneyechogenicity;false
P18_Qrs;QRS;numeric;2,2;false
P19_Aciditydesc;Кислотность (рН) - примечание;text;255;false
P19_Conjugatedbilirubinmcmdesc;билирубин прямой (мкмоль/л) - примечание;text;255;false
P19_Coprodordesc;Запах - примечание;text;255;false
P19_Erythrocytesvalue;Эритроциты;numeric;3,2;false
P19_Mondesc;MON (моноциты) - примечание;text;255;false
P19_Odcapsulahrust;Капсула хрусталика (od);text;100;false
P19_Pechenperifsosudrisunok;Печень: Периферический сосудистый рисунок;dict;Periphvascularpattern;false
P19_Pravsemkontur;Правый семенник: Контуры;numeric;4,2;false
P19_Progestrusvalue;Прогестерон - эструс;numeric;2,1;false
P19_Prvpochkortiksloyekhostruktura;Правая почка: кортикальный слой - Эхоструктура;numeric;4,2;false
P19_Qrsdesc;QRS - Примечание;text;50;false
P20_Acidityvalue;Кислотность - результат исследования;numeric;2,1;false
P20_Bacteriavalue;Бактерии;dict;Bacteria;false
P20_Conjugatedbilirubinmgvalue;билирубин прямой (мг/дл) - результат исследований;numeric;3,2;false
P20_Gravalue;GRA (гранулоциты) - результат исследования;numeric;2,1;false
P20_Odstructurasteklotelo;Структура стекловидного тела (od);text;100;false
P20_Pechenportae;Печень: v. portae;numeric;4,2;false
P20_Pravsemparenkhima;Правый семенник: Паренхима;dict;Parenchymatous;false
P20_Progmetestvalue;Прогестерон - метэструс;numeric;3;false
P20_Proteinvalue;Белок - результат исследования;numeric;2,1;false
P20_Prvpochmedullyarsloytolshchina;Правая почка: Медуллярный слой - Толщина;numeric;4,2;false
P20_Rmv;R;numeric;2,2;false
P21_Aciditydesc;Кислотность - примечание;text;255;false
P21_Conjugatedbilirubinmgdesc;билирубин прямой (мг/дл) - примечание;text;255;false
P21_Estradanesvalue;Эстрадиол проэструс - анэструс;numeric;1,3;false
P21_Fazatsiklavalue;Фаза цикла;numeric;3,2;false
P21_Gradesc;GRA (гранулоциты) - примечание;text;255;false
P21_Oddiametrzrachka;Диаметр зрачкового отверстия (od);numeric;3,2;false
P21_Pechenvhepatica;Печень: v. hepatica ;numeric;4,2;false
P21_Pravsemobyemnobrazov;Правый семенник: Объемные образования;numeric;4,2;false
P21_Proteindesc;Белок - примечание;text;255;false
P21_Prvpochmedullyarsloyekhogennost;Правая почка: Медуллярный слой - Эхогенность;dict;Kidneyechogenicity;false
P21_Rdesc;R - Примечание;text;50;false
P22_Altalanniamvalue;АЛТ аланинаминотрансфераза - результат исследований;numeric;3,2;false
P22_Estradproenstvalue;Эстрадиол проэструс - проэструс;numeric;1,3;false
P22_Glukozavalue;Глюкоза - результат исследования;numeric;2,1;false
P22_Odcontur;Задняя стенка глазного яблока (od) - Контуры;text;100;false
P22_Pechenahepatica;Печень: a. hepatica ;numeric;4,2;false
P22_Pridatokpravsemgolovka;Придаток правого семенника: Головка;numeric;4,2;false
P22_Prvpochmedullyarsloyekhostruktura;Правая почка: Медуллярный слой - Эхоструктура;numeric;4,2;false
P22_Rbcvalue;RBC (общее кол-во эритроцитов) - результат исследования;numeric;2,1;false
P22_Recomendvalue;Рекомендации;text;255;false
P22_Stercobilinvalue;Стеркобилин - результат исследования;numeric;3;false
P22_Tmv;T;numeric;2,2;false
P23_Altalanniamvadesc;АЛТ аланинаминотрансфераза - примечание;text;255;false
P23_Estradestrusvalue;Эстрадиол проэструс - эструс;numeric;1,3;false
P23_Glukozadesc;Глюкоза - примечание;text;255;false
P23_Odstructura;Задняя стенка глазного яблока (od) - Структура;text;100;false
P23_Pechenobyemnobrazov;Печень: Объемные образования ;numeric;4,2;false
P23_Pridatokpravsemtelo;Придаток правого семенника: Тело;numeric;4,2;false
P23_Prvpochmedullyarsloykortmeddiffer;Правая почка: Медуллярный слой - Кортико-медуллярная дифференциация;numeric;4,2;false
P23_Rbcdesc;RBC (общее кол-во эритроцитов) - примечание;text;255;false
P23_Stercobilindesc;Стеркобилин - примечание;text;255;false
P23_Tdesc;T - Примечание;text;50;false
P24_Astaspartvalue;АСТ аспартатаминотрансфераза - результат исследований;numeric;3,2;false
P24_Bilirubinvalue;Билирубин - результат исследования;dict;Coprobilirubin;false
P24_Estradmetestvalue;Эстрадиол проэструс - метэструс;numeric;1,3;false
P24_Hgbvalue;HGB (гемоглобин) - результат исследования;numeric;3;false
P24_Ketonbodvalue;Кетоновые тела - результат исследования;numeric;2,1 ;false
P24_Odstructuradiskazritnerva;Задняя стенка глазного яблока (od) - Структура диска зрительного нерва;text;100;false
P24_Pridatokpravsemobyemnobrazov;Придаток правого семенника: Объемные образования;numeric;4,2;false
P24_Prvpochpiyelicheskiyindeks;Правая почка: Паренхимо-пиелический индекс;numeric;4,2;false
P24_St;S-T;numeric;2,2;false
P24_Zhelchpuzyrstepennapolneniya;Желчный пузырь: Степень наполнения;numeric;4,2;false
P25_Astaspartdesc;АСТ аспартатаминотрансфераза - примечание;text;255;false
P25_Bilirubindesc;Билирубин - примечание;text;255;false
P25_Estradmalevalue;Эстрадиол проэструс - самцы;numeric;1,2;false
P25_Hgbdesc;HGB (гемоглобин) - примечание;text;255;false
P25_Ketonboddesc;Кетоновые тела - примечание;text;255;false
P25_Levsemrazmer;Левый семенник: Размеры;numeric;4,2;false
P25_Odstructuraretrobulyar;Структура ретробульбарного пространства (od);text;100;false
P25_Prvpochpochsinusekhogennost;Правая почка: Почечный синус - Эхогенность;dict;Kidneyechogenicity;false
P25_Qt;Q-T;numeric;2,2;false
P25_Zhelchpuzyrformazhelchpuzyrya;Желчный пузырь: Форма желчного пузыря;dict;Abdomendform;false
P26_Bloodvalue;Кровь - результат исследования;dict;Coprblood;false
P26_Eos;ЭОС;text;4;false
P26_Hctvalue;HCT (гематокрит) - результат исследования;numeric;3,1;false
P26_Levsemkontur;Левый семенник: Контуры;numeric;4,2;false
P26_Mochevinammvalue;мочевина (ммоль/л) - результат исследований;numeric;3,2;false
P26_Osrazmerperednegootrezka;Размер переднего отрезка оси (os);numeric;3,2;false
P26_Prvpochpochsinuschetkostdifferents;Правая почка: Почечный синус - Четкость дифференциации;numeric;4,2;false
P26_Relativedensityvalue;Относительная плотность - результат исследования;numeric;2,3;false
P26_Testostervalue;Тестостерон (самцы);numeric;2,1;false
P26_Zhelchpuzyrtolshchinastenki;Желчный пузырь: Толщина стенки;numeric;4,2;false
P27_Blooddesc;Кровь - примечание;text;255;false
P27_Chss;ЧСС;numeric;3;false
P27_Hctdesc;HCT (гематокрит) - примечание;text;255;false
P27_Levsemparenkhima;Левый семенник: Паренхима;dict;Parenchymatous;false
P27_Mochevinammdesc;мочевина (ммоль/л) - примечание;text;255;false
P27_Osrazmerzadnegootrezka;Размер заднего отрезка оси (os);numeric;3,2;false
P27_Prvpochpochsinuspolostlokhanki;Правая почка: Почечный синус - Полость лоханки;numeric;4,2;false
P27_Relativedensitydesc;Относительная плотность - примечание;text;255;false
P27_Thyroxvalue;Тироксин (Т4);numeric;3;false
P27_Zhelchpuzyrdeformatsiya;Желчный пузырь: Деформация;dict;Abdomendeformation;false
P28_Bilirubinvalue;Билирубин - результат исследования;numeric;3,2;false
P28_Levsemobyemnobrazov;Левый семенник: Объемные образования;numeric;4,2;false
P28_Mcvvalue;MCV (средний объем эритроцита) - результат исследования;numeric;3,1;false
P28_Mochevinamgvalue;мочевина (мг/дл) - результат исследований;numeric;3,2;false
P28_Muscledfibersvalue;мышечные волокна - результат исследования;dict;Coprmuscledfibers;false
P28_Osstructuraperedcamer;Структура передней камеры (os);text;100;false
P28_Prvpochpochsinusstepenlokhanki;Правая почка: Почечный синус - Стенки лоханки;numeric;4,2;false
P28_Ritm;Ритм;numeric;3;false
P28_Triiodtirvalue;Трийодтиронин (Т3);numeric;1,1;false
P28_Zhelchpuzyrstrukturazhelchi;Желчный пузырь: Структура желчи;dict;Patternbile;false
P29_Bilirubindesc;Билирубин - примечание;text;255;false
P29_Ekstrasistoly;Экстрасистолы;numeric;3;false
P29_Mcvdesc;MCV (средний объем эритроцита) - примечание;text;255;false
P29_Mochevinamgdesc;мочевина (мг/дл) - примечание;text;255;false
P29_Muscledfibersdesc;мышечные волокна - примечание;text;255;false
P29_Osrazmerhrust;Размеры хрусталика (os);numeric;3,2;false
P29_Pridatoklevsemgolovka;Придаток левого семенника: Головка;numeric;4,2;false
P29_Prvpochsosudyparenkhimy;Правая почка: Сосуды паренхимы;numeric;4,2;false
P29_Zhelchpuzyrpuzyrprotok;Желчный пузырь: Пузырный проток;numeric;4,2;false
P30_Contissuefibersvalue;соединительно-тканные волокна - результат исследования;dict;Coprcontissuefibers;false
P30_Creatininemcmvalue;креатинин (мкмоль/л) - результат исследований;numeric;3,2;false
P30_Hemeglvalue;Гемоглобин - результат исследования;numeric;3,2;false
P30_Mchvalue;MCH (сод. гемоглобина в 1 эритроците) - результат исследования;numeric;3,1;false
P30_Osstructurahrust;Структура хрусталика (os);text;100;false
P30_Pridatoklevsemtelo;Придаток левого семенника: Тело;numeric;4,2;false
P30_PrvpochIndeksrezistivnpochechnart;Правая почка: Индекс резистивности на участке почечной артерии;numeric;4,2;false
P30_Serviceresult;Заключение;text;255;false
P30_Zhelchpuzyrobshzhelchprotok;Желчный пузырь: Общий желчный проток;numeric;4,2;false
P31_Contissuefibersdesc;соединительно-тканные волокна - примечание;text;255;false
P31_Creatininemcmdesc;креатинин (мкмоль/л) - примечание;text;255;false
P31_Hemegldesc;Гемоглобин - примечание;text;255;false
P31_Mchdesc;MCH (сод. гемоглобина в 1 эритроците) - примечание;text;255;false
P31_Oscapsulahrust;Капсула хрусталика (os);text;100;false
P31_Pridatoklevsemobyemnobrazov;Придаток левого семенника: Объемные образования;numeric;4,2;false
P31_PrvpochIndeksrezistivnmezhdolevoyart;Правая почка: Индекс резистивности на участке междолевой артерии;numeric;4,2;false
P31_Zhelchpuzyrpechenochnprotok;Желчный пузырь: Печеночные протоки;numeric;4,2;false
P32_Creatininemgvalue;креатинин (мг/дл) - результат исследований;numeric;3,2;false
P32_Erythrocytvalue;Эритроциты - результат исследования;dict;Urinerythrocyt;false
P32_Mchcvalue;MCHC (конц. гемоглобина в 1 эритроците) - результат исследования;numeric;3,1;false
P32_Neutralfatvalue;нейтральный жир - результат исследования;dict;Coprneutralfat;false
P32_Osstructurasteklotelo;Структура стекловидного тела (os);text;100;false
P32_Prvpochkonkrementy;Правая почка: Конкременты;numeric;4,2;false
P32_Abdomultmserviceresult;Заключение;text;255;false
P32_Zhelchpuzyrobyemnobrazov;Желчный пузырь: Объемные образования;numeric;4,2;false
P33_Creatininemgdesc;креатинин (мг/дл) - примечание;text;255;false
P33_Erythrocytdesc;Эритроциты - примечание;text;255;false
P33_Mchcdesc;MCHC (конц. гемоглобина в 1 эритроците) - примечание;text;255;false
P33_Neutralfatdesc;нейтральный жир - примечание;text;255;false
P33_Osdiametrzrachka;Диаметр зрачкового отверстия (os);numeric;3,2;false
P33_Prvpochobyemnobrazov;Правая почка: Объемные образования;numeric;4,2;false
P33_Selezenkaraspoloshenie;Селезенка: Расположение;dict;Abdomendposition;false
P34_Fattyacidsvalue;жирные кислоты - результат исследования;dict;Coprfattyacids;false
P34_Leucocytvalue;Лейкоциты - результат исследования;numeric;2;false
P34_Levpochraspoloshenie;Левая почка: Расположение;dict;Kidneyposition;false
P34_Oscontur;Задняя стенка глазного яблока (os) - Контуры;text;100;false
P34_Rdwvalue;RDW (ширина распределения эритроцитов) - результат исследования;numeric;2,1;false
P34_Selezenkakontur;Селезенка: Контуры;numeric;4,2;false
P34_Shelochfosfatvalue;Щелочная фосфатаза - результат исследований;numeric;3,2;false
P35_Fattyacidsdesc;жирные кислоты - примечание;text;255;false
P35_Leucocytdesc;Лейкоциты - примечание;text;255;false
P35_Levpochgranica;Левая почка: Границы;numeric;4,2;false
P35_Osstructura;Задняя стенка глазного яблока (os) - Структура;text;100;false
P35_Rdwdesc;RDW (ширина распределения эритроцитов) - примечание;text;255;false
P35_Selezenkarazmer;Селезенка: Размеры;numeric;4,2;false
P35_Shelochfosfatdesc;Щелочная фосфатаза - примечание;text;255;false
P36_Amilazavalue;?- амилаза - результат исследований;numeric;4,2;false
P36_Levpochrazmer;Левая почка: Размеры;numeric;4,2;false
P36_Osstructuradiskazritnerva;Задняя стенка глазного яблока (os) - Структура диска зрительного нерва;text;100;false
P36_Ploskiyvalue;Эпителий - Плоский - результат исследования;dict;Urinflat;false
P36_Pltvalue;PLT (тромбоциты) - результат исследования;numeric;4;false
P36_Selezenkaekhostruktura;Селезенка: Эхоструктура;numeric;4,2;false
P36_Soapvalue;мыла - результат исследования;dict;Coprsoap;false
P37_Amilazadesc;?- амилаза - примечание;text;255;false
P37_Levpochkortiksloytolshina;Левая почка: кортикальный слой - Толщина;numeric;4,2;false
P37_Osstructuraretrobulyar;Структура ретробульбарного пространства (os);text;100;false
P37_Ploskiydesc;Эпителий - Плоский - примечание;text;255;false
P37_Pltdesc;PLT (тромбоциты) - примечание;text;255;false
P37_Selezenkaekhogennost;Селезенка: Эхогенность паренхимы;dict;Abdomendechogenicity;false
P37_Soapdesc;мыла - примечание;text;255;false
P38_Levpochkortiksloyekhogennost;Левая почка: кортикальный слой - Эхогенность;dict;Kidneyechogenicity;false
P38_Mpvvalue;MPV (средний объем тромбоцита) - результат исследования;numeric;2,1;false
P38_Pancreatinevalue;Панкреатическая амилаза - результат исследований;numeric;4,2;false
P38_Perehodvalue;Эпителий - переходный - результат исследования;dict;Urintransepithelium;false
P38_Selezenkasosudrisunok;Селезенка: Сосудистый рисунок;dict;Periphvascularpattern;false
P38_Serviceresult;Заключение;text;255;false
P38_Starchvalue;крахмал - результат исследования;dict;Coprstarch;false
P39_Levpochkortiksloyekhostruktura;Левая почка: кортикальный слой - Эхоструктура;numeric;4,2;false
P39_Mpvdesc;MPV (средний объем тромбоцита) - примечание;text;255;false
P39_Pancreatinedesc;Панкреатическая амилаза - примечание;text;255;false
P39_Perehoddesc;Эпителий - переходный - примечание;text;255;false
P39_Selezenkaobyemnobrazov;Селезенка: Объемные образования;numeric;4,2;false
P39_Starchdesc;крахмал - примечание;text;255;false
P40_Glukozamcmvalue;глюкоза (мкмоль/л) - результат исследований;numeric;3,2;false
P40_Levpochmedullyarsloytolshchina;Левая почка: Медуллярный слой - Толщина;numeric;4,2;false
P40_Pctvalue;PCT (тромбокрит) - результат исследования;numeric;1,3;false
P40_Pochechnvalue;Эпителий - почечный - результат исследования;dict;Urinrenalepithelium;false
P40_Podzhelzhelezaraspoloshenie;Поджелудочная железа: Расположение;dict;Abdomendposition;false
P41_Glukozamcmdesc;глюкоза (мкмоль/л) - примечание;text;255;false
P41_Levpochmedullyarsloyekhogennost;Левая почка: Медуллярный слой - Эхогенность;dict;Kidneyechogenicity;false
P41_Pctdesc;PCT (тромбокрит) - примечание;text;255;false
P41_Pochechndesc;Эпителий - почечный - примечание;text;255;false
P41_Podzhelzhelezakontur;Поджелудочная железа: Контуры;numeric;4,2;false
P42_Glukozamgvalue;глюкоза (мг/дл) - результат исследований;numeric;3,2;false
P42_Hyalinevalue;Цилиндры гиалиновые - результат исследования;numeric;3,2;false
P42_Levpochmedullyarsloyekhostruktura;Левая почка: Медуллярный слой - Эхоструктура;numeric;4,2;false
P42_Pdwvalue;PDW (ширина распределения тромбоцитов) - результат исследования;numeric;2,1;false
P42_Podzhelzhelezarazmer;Поджелудочная железа: Размеры;numeric;4,2;false
P43_Glukozamgdesc;глюкоза (мг/дл) - примечание;text;255;false
P43_Hyalinedesc;Цилиндры гиалиновые - примечание;text;255;false
P43_Levpochmedullyarsloykortmeddiffer;Левая почка: Медуллярный слой - Кортико-медуллярная дифференциация;numeric;4,2;false
P43_Pdwdesc;PDW (ширина распределения тромбоцитов) - примечание;text;255;false
P43_Podzhelzhelezaekhostruktura;Поджелудочная железа: Эхоструктура;numeric;4,2;false
P44_Granularvalue;Цилиндры Зернистые - результат исследования;numeric;3,2;false
P44_Ldglactodvalue;ЛДГ лактадегидрогиназа - результат исследований;numeric;3,2;false
P44_Levpochpiyelicheskiyindeks;Левая почка: Паренхимо-пиелический индекс;numeric;4,2;false
P44_Podzhelzhelezaekhogennost;Поджелудочная железа: Эхогенность;dict;Abdomendechogenicity;false
P44_Soevalue;СОЭ - результат исследования;numeric;2;false
P45_Granulardesc;Цилиндры Зернистые - примечание;text;255;false
P45_Ldglactoddesc;ЛДГ лактадегидрогиназа - примечание;text;255;false
P45_Levpochpochsinusekhogennost;Левая почка: Почечный синус - Эхогенность;dict;Kidneyechogenicity;false
P45_Podzhelzhelezaobyemnobrazov;Поджелудочная железа: Объемные образования;numeric;4,2;false
P45_Soedesc;СОЭ - примечание;text;255;false
P46_Levpochpochsinuschetkostdifferents;Левая почка: Почечный синус - Четкость дифференциации;numeric;4,2;false
P46_Lgtgammavalue;ГГТ гамма-глутамилтрансфераза - результат исследований;numeric;3,2;false
P46_Waxvalue;Цилиндры восковидные - результат исследования;numeric;3,2;false
P46_Youngvalue;Нейтрофилы - Юные - результат исследования;numeric;2;false
P46_Zheludkishechntrakt;Желудочно-кишечный тракт ;text;100;false
P47_Levpochpochsinuspolostlokhanki;Левая почка: Почечный синус - Полость лоханки;numeric;4,2;false
P47_Lgtgammadesc;ГГТ гамма-глутамилтрансфераза - примечание;text;255;false
P47_Svobodnzhidkost;Свободная жидкость в брюшной полости;numeric;4,2;false
P47_Waxdesc;Цилиндры восковидные - примечание;text;255;false
P47_Youngdesc;Нейтрофилы - Юные - примечание;text;255;false
P48_Kfkcreatinevalue;КФК креатинфосфокиназа - результат исследований;numeric;3,2;false
P48_Lekocitvalue;Цилиндры лейкоцитарные - результат исследования;numeric;3,2;false
P48_Levpochpochsinusstenkilokhanki;Левая почка: Почечный синус - Стенки лоханки;numeric;4,2;false
P48_Palochkoyadervalue;Нейтрофилы - Палочкоядерные - результат исследования;numeric;2;false
P48_Serviceresult;Заключение;text;255;false
P49_Kfkcreatinedesc;КФК креатинфосфокиназа - примечание;text;255;false
P49_Lekocitdesc;Цилиндры лейкоцитарные - примечание;text;255;false
P49_Levpochsosudyparenkhimy;Левая почка: Сосуды паренхимы;numeric;4,2;false
P49_Palochkoyaderdesc;Нейтрофилы - Палочкоядерные - примечание;text;255;false
P50_Eritrocitvalue;Цилиндры эритроцитарные - результат исследования;numeric;3,2;false
P50_Holestermmvalue;Холестерол (ммоль/л) - результат исследований;numeric;3,2;false
P50_LevpochIndeksrezistivnpochechnart;Левая почка: Индекс резистивности на участке почечной артерии;numeric;4,2;false
P50_Segmentvalue;Нейтрофилы - Сегментоядерные - результат исследования;numeric;3;false
P51_Eritrocitdesc;Цилиндры эритроцитарные - примечание;text;255;false
P51_Holestermmdesc;Холестерол (ммоль/л) - примечание;text;255;false
P51_LevpochIndeksrezistivnmezhdolevoyart;Левая почка: Индекс резистивности на участке междолевой артерии;numeric;4,2;false
P51_Segmentdesc;Нейтрофилы - Сегментоядерные - примечание;text;255;false
P52_Eosinophilsvalue;Эозинофилы - результат исследования;numeric;2;false
P52_Epitelvalue;Цилиндры эпителиальные - результат исследования;numeric;3,2;false
P52_Levpochkonkrementy;Левая почка: Конкременты;numeric;4,2;false
P53_Eosinophilsdesc;Эозинофилы - примечание;text;255;false
P53_Epiteldesc;Цилиндры эпителиальные - примечание;text;255;false
P53_Levpochobyemnobrazov;Левая почка: Объемные образования;numeric;4,2;false
P54_Cilindvalue;Цилиндры цилиндроиды - результат исследования;numeric;3,2;false
P54_Mochpuzstepnapoln;Мочевой пузырь: Степень наполнения;numeric;4,2;false
P54_Monocitvalue;Моноциты - результат исследования;numeric;2;false
P54_Triglyceridsmmvalue;Триглицериды (ммоль/л) - результат исследований;numeric;3,2;false
P55_Cilinddesc;Цилиндры цилиндроиды - примечание;text;255;false
P55_Mochpuztolshchinastenki;Мочевой пузырь: Толщина стенки;numeric;4,2;false
P55_Monocitdesc;Моноциты - примечание;text;255;false
P55_Triglyceridsmmdesc;Триглицериды (ммоль/л) - примечание;text;255;false
P56_Bacteriavalue;Бактерии - результат исследования;dict;Urinbacteria;false
P56_Bazophilvalue;Базофилы - результат исследования;numeric;2;false
P56_Mochpuzdeformatsiya;Мочевой пузырь: Деформация;dict;Urinarybladderdeformation;false
P56_Triglyceridsmgvalue;Триглицериды (мг/дл) - результат исследований;numeric;3,2;false
P57_Bacteriadesc;Бактерии - примечание;text;255;false
P57_Bazophildesc;Базофилы - примечание;text;255;false
P57_Mochpuzuretra;Уретра;numeric;4,2;false
P57_Triglyceridsmgdesc;Триглицериды (мг/дл) - примечание;text;255;false
P58_Caliummmvalue;калий (ммоль/л) - результат исследований;numeric;3,2;false
P58_Limphocitvalue;Лимфоциты - результат исследования;numeric;3;false
P58_MochpuzObyemnobrazov;Объемные образования;numeric;4,2;false
P58_Saltvalue;Соли - результат исследования;numeric;3,2;false
P59_Caliummmdesc;калий (ммоль/л) - примечание;text;255;false
P59_Limphocitdesc;Лимфоциты - примечание;text;255;false
P59_Saltdesc;Соли - примечание;text;255;false
P59_Serviceresult;Заключение;text;255;false
P60_Caliummecvalue;калий (мэкв/дл) - результат исследований;numeric;3,2;false
P61_Caliummecdesc;калий (мэкв/дл) - примечание;text;255;false
P62_Natrmmvalue;натрий (ммоль/л) - результат исследований;numeric;3,2;false
P63_Natrmmdesc;натрий (ммоль/л) - примечание;text;255;false
P64_Natrmecvalue;натрий (мэкв/дл) - результат исследований;numeric;3,2;false
P65_Natrmecdesc;натрий (мэкв/дл) - примечание;text;255;false
P66_Phosphormmvalue;фосфор (ммоль/л) - результат исследований;numeric;3,2;false
P67_Phosphormmdesc;фосфор (ммоль/л) - примечание;text;255;false
P68_Phosphormgvalue;фосфор (мг/дл) - результат исследований;numeric;3,2;false
P69_Phosphormgdesc;фосфор (мг/дл) - примечание;text;255;false
P70_Calciummmvalue;кальций (ммоль/л) - результат исследований;numeric;3,2;false
P71_Calciummmdesc;кальций (ммоль/л) - примечание;text;255;false
P72_Calciummcgvalue;кальций (мг/дл) - результат исследований;numeric;3,2;false
P73_Calciummcgdesc;кальций (мг/дл) - примечание;text;255;false
P74_Ironmcmvalue;железо (мкмоль/л) - результат исследований;numeric;3,2;false
P75_Ironmcmdesc;железо (мкмоль/л) - примечание;text;255;false
P76_Ironmcgvalue;железо (мкг/дл) - результат исследований;numeric;3,2;false
P77_Ironmcgdesc;железо (мкг/дл) - примечание;text;255;false
P78_Magnesiummmvalue;магний (ммоль/л) - результат исследований;numeric;3,2;false
P79_Magnesiummmdesc;магний (ммоль/л) - примечание;text;255;false
P80_Magnesiummecvalue;магний (мэкв/дл) - результат исследований;numeric;3,2;false
P81_Magnesiummecdesc;магний (мэкв/дл) - примечание;text;255;false
P82_Chloridemmvalue;хлорид (ммоль/л) - результат исследований;numeric;3,2;false
P83_Chloridemmdesc;хлорид (ммоль/л) - примечание;text;255;false
P84_Chloridemecvalue;хлорид (мэкв/дл) - результат исследований;numeric;3,2;false
P85_Chloridemecdesc;хлорид (мэкв/дл) - примечание;text;255;false
P86_Acidityvalue;кислотность - результат исследований;numeric;3,2;false
P87_Aciditydesc;кислотность - примечание;text;255;false
P88_Mochekislnmvalue;мочевая кислота (нмоль/л) - результат исследований;numeric;2,4;false
P89_Mochekislnmdesc;мочевая кислота (нмоль/л) - примечание;text;255;false
P90_Mochekislmgvalue;мочевая кислота (мг/дл) - результат исследований;numeric;2,4;false
P91_Mochekislmgdesc;мочевая кислота (мг/дл) - примечание;text;255;false
P92_Lipazavalue;липаза - результат исследований;numeric;3,2;false
P93_Lipazadesc;липаза - примечание;text;255;false
P94_Totalproteinglvalue;Общий белок (г/л) - результат исследований;numeric;3,2;false
P95_Totalproteingldesc;Общий белок (г/л) - примечание;text;255;false
P96_Totalproteingdlvalue;Общий белок (г/дл) - результат исследований;numeric;4,2;false
P97_Totalproteingdldesc;Общий белок (г/дл) - примечание;text;255;false
P98_Albuminglvalue;Альбумины (г/л) - результат исследований;numeric;3,2;false
P99_Albumingldesc;Альбумины (г/л) - примечание;text;255;false
P14_Matkadiametrtela;Матка - Диаметр тела;numeric;4,2;false
P15_Matkatolshinatela;Матка - Толщина стенки тела;numeric;4,2;false
P16_Matkastrukturastenkitela;Матка - Структура стенки тела;dict;Alvusstructure;false
P17_Matkasostoyanpolosti;Матка - Состояние полости;dict;Alvuscontents;false
P18_Matkadiametrpravroga;Диаметр правого рога;numeric;4,2;false
P19_Matkatolshinapravroga;Толщина стенки правого рога;numeric;4,2;false
P20_Matkastrukturastenkipravroga;Структура стенки правого рога;dict;Alvusstructure;false
P21_Matkasoderzhimpolostipravroga;Содержимое полости правого рога;dict;Alvuscontents;false
P22_Matkadiametrlevroga;Диаметр левого рога;numeric;4,2;false
P23_Matkatolshinalevroga;Толщина стенки левого рога;numeric;4,2;false
P24_Matkastrukturastenkilevroga;Структура стенки левого рога;dict;Alvusstructure;false
P25_Matkasoderzhimpolostilevroga;Содержимое полости левого рога;dict;Alvuscontents;false
P26_Pravyaichnikrazmer;Правый яичник - Размеры;numeric;4,2;false
P27_Pravyaichnikkontur;Правый яичник - Контуры;text;50;false
P28_Pravyaichniknovoobrazov;Правый яичник - Новообразования;text;100;false
P29_Levyaichnikrazmer;Левый яичник - Размеры;numeric;4,2;false
P30_Levyaichnikkontur;Левый яичник - Контуры;text;50;false
P31_Levyaichniknovoobrazov;Левый яичник - Новообразования;text;100;false
P32_Serviceresult;Заключение;text;255;false
P0_Juraddress;Адрес выезда;text;250;false
P0_Petregnum;Регистрационный № животного;text;50;true
P17_Petcolor;Окрас животного;dict;Petcolor;false
P18_Petspecialtrait;Особые приметы животного;text;255;false
P19_Petregexpiredate;Информация о снятии животного с регистрации;dttm;;true
P3_Visitstartdate;Дата;dttm;;true
P33_SpecialistFIO;Специалист;text;250;true
P4_Ownername;Наименование организации/ ФИО владельца;text;150;true
P5_Owneraddres;Адрес владельца;text;250;true
P5_Ownercontact;Телефон владельца;text;50;true
P6_Speciesname;Вид животного;text;50;true
P7_Breedname;Порода животного;text;100;true
P9_Petname;Кличка животного;text;50;true
P0_Balanceinventorynumber;Серия вакцины;text;50;false
P0_Vaccinename;Наименование вакцины;text;50;false
P0_VisitServiceTMCcount;Доза ;numeric;2;false
P104_Ketonebodiesvalue;Кетоновые тела;numeric;4,2;false
P105_Bikarbonatvalue;Бикарботаны;numeric;4,2;false
P106_Proteinfractionsvalue;Белковые фракции;numeric;4,2;false
P108_Issuedate;Дата выдачи;dttm;;false
P15_Activator;Название выделенного возбудителя и его характеристика;text;255;false
P19_Animalcount;Количество животных в гурте, отаре, группе;numeric;3;false
P20_Analysisobject;На что исследовалось;text;255;false
P21_Diagnostictechnique;Метод исследования;text;255;false
P22_Parasdiseasesanswerdate;Дата отправки ответа;dttm;;false
P23_Cytologicscreeninganswerdate;Дата отправки ответа;dttm;;false
P24_Anamnesis;Анамнез;text;255;false
P39_Coproanswerdate;Дата ответа;dttm;0;false
P61_Urinunitweightvalue;Удельный вес ;numeric;4,2;false
P62_Urinreactionvalue;Реакция ;text;255;false
P63_Nitratvalue;Нитраты ;numeric;2;false
P64_Urinorddepositionvalue;Организованный осадок;text;255;false
P65_Urindisorddepositionvalue;Неорганизованный осадок;text;255;false
P66_UrinAnswerdate;Дата ответа;dttm;;false
P67_Urinconsistency;Консистенция;text;255;false
P14_LVIDd;LVIDd;numeric;2,2;false
P15_LVIDs;LVIDs;numeric;2,2;false
P16_LVWTd;LVWTd;numeric;2,2;false
P17_LVWTs;LVWTs;numeric;2,2;false
P18_IVSTd;IVSTd;numeric;2,2;false
P19_IVSTs;IVSTs;numeric;2,2;false
P20_EF;EF;numeric;2,2;false
P21_FS;FS;numeric;2,2;false
P22_LA;LA;numeric;2,2;false
P23_AO;AO;numeric;2,2;false
P24_LA/AO;LA/AO;numeric;2,2;false
P25_RVIDd;RVIDd ;numeric;2,2;false
P26_RVIDs;RVIDs;numeric;2,2;false
P27_RVWTd;RVWTd;numeric;2,2;false
P28_RVWTs;RVWTs;numeric;2,2;false
P29_RA;RA;numeric;2,2;false
P30_Defektivs;Дефект IVS;numeric;2,2;false
P31_Defektias;Дефект IAS;numeric;2,2;false
P32_Svobodnzhidkostperikarde;Свободная жидкость в перикарде;numeric;4,2;false
P33_Svobodnzhidkostplevralpolosti;Свободная жидкость в плевральной полости;numeric;4,2;false
P34_Novoobrazov;Новообразования;numeric;4,2;false
P35_Mitrklapnstvorki;Митральный клапан: Створки;numeric;4,2;false
P36_Mitrklapnskorostkrovotoka;Митральный клапан: Скорость кровотока;numeric;4,2;false
P37_Mitrklapnregurgitatsiya;Митральный клапан: Регургитация[РЛА6] ;numeric;4,2;false
P38_Trikuspklapnstvorki;Трикуспидальный клапан: Створки;numeric;4,2;false
P39_Trikuspklapnskorostkrovotoka;Трикуспидальный клапан: Скорость кровотока;numeric;4,2;false
P40_Trikuspklapnregurgitatsiya;Трикуспидальный клапан: Регургитация[РЛА7] ;numeric;4,2;false
P41_Aortaklapnstvorki;Аортальный клапан: Створки;numeric;4,2;false
P42_Aortaklapnskorostkrovotoka;Аортальный клапан: Скорость кровотока;numeric;4,2;false
P43_Aortaklapnregurgitatsiya;Аортальный клапан: Регургитация;numeric;4,2;false
P44_Klapnlegartstvorki;Клапан легочной артерии: Створки;numeric;4,2;false
P45_Klapnlegartskorostkrovotoka;Клапан легочной артерии: Скорость кровотока;numeric;4,2;false
P46_Klapnlegartregurgitatsiya;Клапан легочной артерии: Регургитация[РЛА8] ;numeric;4,2;false
P47_Serviceresult;Заключение;text;255;false
P0_Organsystem;Система органов;dict;Ultrasoundorgansystem;false
P1_Parentorgshortname;Наименование главной организации;text;100;true
P13_Orgshortname;Наименование организации;text;100;true
P15_Vacexpirationdate;Дата окончания действия вакцины;dttm;;false
P0_Investigationarea;Зона исследования;dict;Investigationarea;false
CSV;

        $tableName = 'params';

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $tech_name = $item[0];
            $name = empty($item[1]) ? $tech_name : $item[1];    // фикс для пустых name
            $datatype = $item[2];
            $datatype_details = $item[3];
            $visit_flag = ($item[4] == '') ? false : ($item[4] === 'true' ? true : false);
            $columns = compact('name', 'tech_name', 'datatype', 'datatype_details', 'visit_flag');
            $record = $this->findParamByTechName($tech_name);
            if (empty($record)) {
                Console::output(Console::ansiFormat('Creating param [' . $tech_name  . ']', [Console::FG_GREEN]));
                \Yii::$app->db->createCommand()
                    ->insert($tableName, $columns)
                    ->execute();
            } else {
                foreach ($columns as $column => $value) {
                    if ($record[$column] !== $value) {
                        // надо обновить param
                        // остальные поля можно не проверять
                        Console::output(Console::ansiFormat('Updating param [' . $tech_name  . ']', [Console::FG_YELLOW]));
                        \Yii::$app->db->createCommand()
                            ->update($tableName, $columns, ['id' => $record['id']])
                            ->execute();
                        break;
                    }
                }
            }
        }

        $techNames = ArrayHelper::getColumn($items, 0);
        $toDelete = (new Query())
            ->from($tableName)
            ->where(['not in', 'tech_name', $techNames])
            ->all();

        if (!empty($toDelete)) {
            foreach ($toDelete as $row) {
                Console::output(Console::ansiFormat('Removing param [' . $row['tech_name']  . ']', [Console::FG_PURPLE]));
                foreach (['visit_param_values', 'visit_service_param_values'] as $table) {
                    // удалим связанные values
                    \Yii::$app->db->createCommand()
                        ->delete($table, ['id_param' => $row['id']])
                        ->execute();
                }
                \Yii::$app->db->createCommand()
                    ->delete($tableName, ['id' => $row['id']])
                    ->execute();
            }
        }
    }

    private function linkServiceParams()
    {
        $csv = <<<CSV
Ампутация рудиментарных фаланг у собак - до 2-х недельного возраста (с местным обезболиванием);P10_Petbirthday;;true;false;1
Ампутация рудиментарных фаланг у собак - от 2-х до 4-х недельного возраста;P10_Petbirthday;;true;false;1
Ампутация рудиментарных фаланг у собак - свыше 4-х недельного возраста;P10_Petbirthday;;true;false;1
Ампутация хвоста у собак - до 10-ти дневного возраста (с местным обезболиванием);P10_Petbirthday;;true;false;1
Ампутация хвоста у собак - от 10-ти дневного до 2-х месячного возраста;P10_Petbirthday;;true;false;1
Ампутация хвоста у собак - свыше 2-х месячного возраста;P10_Petbirthday;;true;false;1
Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы);P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы);P22_Altalanniamvalue;;false;true;10
Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы);P23_Altalanniamvadesc;;false;true;11
Биохимические исследования крови - определение амилазы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение амилазы;P36_Amilazavalue;;false;true;24
Биохимические исследования крови - определение амилазы;P37_Amilazadesc;;false;true;25
Биохимические исследования крови - определение амилазы панкреатической;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение амилазы панкреатической;P38_Pancreatinevalue;;false;true;26
Биохимические исследования крови - определение амилазы панкреатической;P39_Pancreatinedesc;;false;true;27
Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы);P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы);P24_Astaspartvalue;;false;true;12
Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы);P25_Astaspartdesc;;false;true;13
Биохимические исследования крови - определение белковых фракций;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение белковых фракций;P100_Albumingdlvalue;;false;true;88
Биохимические исследования крови - определение белковых фракций;P101_Albumingdldesc;;false;true;89
Биохимические исследования крови - определение белковых фракций;P106_Proteinfractionsvalue;;false;true;119
Биохимические исследования крови - определение белковых фракций;P98_Albuminglvalue;;false;true;86
Биохимические исследования крови - определение белковых фракций;P99_Albumingldesc;;false;true;87
Биохимические исследования крови - определение гаммаглутамилтрансферазы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение гаммаглутамилтрансферазы;P46_Lgtgammavalue;;false;true;34
Биохимические исследования крови - определение гаммаглутамилтрансферазы;P47_Lgtgammadesc;;false;true;35
Биохимические исследования крови - определение гемоглобина;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение гемоглобина;P102_Hemoglobinvalue;;false;true;90
Биохимические исследования крови - определение гемоглобина;P103_Hemoglobindesc;;false;true;91
Биохимические исследования крови - определение глюкозы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение глюкозы;P40_Glukozamcmvalue;;false;true;28
Биохимические исследования крови - определение глюкозы;P41_Glukozamcmdesc;;false;true;29
Биохимические исследования крови - определение глюкозы;P42_Glukozamgvalue;;false;true;30
Биохимические исследования крови - определение глюкозы;P43_Glukozamgdesc;;false;true;31
Биохимические исследования крови - определение железа;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение железа;P74_Ironmcmvalue;;false;true;62
Биохимические исследования крови - определение железа;P75_Ironmcmdesc;;false;true;63
Биохимические исследования крови - определение железа;P76_Ironmcgvalue;;false;true;64
Биохимические исследования крови - определение железа;P77_Ironmcgdesc;;false;true;65
Биохимические исследования крови - определение калия;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение калия;P58_Caliummmvalue;;false;true;46
Биохимические исследования крови - определение калия;P59_Caliummmdesc;;false;true;47
Биохимические исследования крови - определение калия;P60_Caliummecvalue;;false;true;48
Биохимические исследования крови - определение калия;P61_Caliummecdesc;;false;true;49
Биохимические исследования крови - определение кальция;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение кальция;P70_Calciummmvalue;;false;true;58
Биохимические исследования крови - определение кальция;P71_Calciummmdesc;;false;true;59
Биохимические исследования крови - определение кальция;P72_Calciummcgvalue;;false;true;60
Биохимические исследования крови - определение кальция;P73_Calciummcgdesc;;false;true;61
Биохимические исследования крови - определение креатинина;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение креатинина;P30_Creatininemcmvalue;;false;true;18
Биохимические исследования крови - определение креатинина;P31_Creatininemcmdesc;;false;true;19
Биохимические исследования крови - определение креатинина;P32_Creatininemgvalue;;false;true;20
Биохимические исследования крови - определение креатинина;P33_Creatininemgdesc;;false;true;21
Биохимические исследования крови - определение креатинкиназы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение креатинкиназы;P48_Kfkcreatinevalue;;false;true;36
Биохимические исследования крови - определение креатинкиназы;P49_Kfkcreatinedesc;;false;true;37
Биохимические исследования крови - определение лактатдегидрогеназы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение лактатдегидрогеназы;P44_Ldglactodvalue;;false;true;32
Биохимические исследования крови - определение лактатдегидрогеназы;P45_Ldglactoddesc;;false;true;33
Биохимические исследования крови - определение липазы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение липазы;P92_Lipazavalue;;false;true;80
Биохимические исследования крови - определение липазы;P93_Lipazadesc;;false;true;81
Биохимические исследования крови - определение магния;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение магния;P78_Magnesiummmvalue;;false;true;66
Биохимические исследования крови - определение магния;P79_Magnesiummmdesc;;false;true;67
Биохимические исследования крови - определение магния;P80_Magnesiummecvalue;;false;true;68
Биохимические исследования крови - определение магния;P81_Magnesiummecdesc;;false;true;69
Биохимические исследования крови - определение мочевины;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение мочевины;P104_Ketonebodiesvalue;;false;true;117
Биохимические исследования крови - определение мочевины;P26_Mochevinammvalue;;false;true;14
Биохимические исследования крови - определение мочевины;P27_Mochevinammdesc;;false;true;15
Биохимические исследования крови - определение мочевины;P28_Mochevinamgvalue;;false;true;16
Биохимические исследования крови - определение мочевины;P29_Mochevinamgdesc;;false;true;17
Биохимические исследования крови - определение мочевой кислоты;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение мочевой кислоты;P105_Bikarbonatvalue;;false;true;118
Биохимические исследования крови - определение мочевой кислоты;P88_Mochekislnmvalue;;false;true;76
Биохимические исследования крови - определение мочевой кислоты;P89_Mochekislnmdesc;;false;true;77
Биохимические исследования крови - определение мочевой кислоты;P90_Mochekislmgvalue;;false;true;78
Биохимические исследования крови - определение мочевой кислоты;P91_Mochekislmgdesc;;false;true;79
Биохимические исследования крови - определение натрия;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение натрия;P62_Natrmmvalue;;false;true;50
Биохимические исследования крови - определение натрия;P63_Natrmmdesc;;false;true;51
Биохимические исследования крови - определение натрия;P64_Natrmecvalue;;false;true;52
Биохимические исследования крови - определение натрия;P65_Natrmecdesc;;false;true;53
Биохимические исследования крови - определение общего белка;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение общего белка;P82_Chloridemmvalue;"Неоходимо уточнить для какой услуги заполняются параметры:  Уровень хлорида в крови в ммоль/л Уровень хлорида в крови в мэкв/дл Поэтому параметр не обязателен к заполнению.";false;false;70
Биохимические исследования крови - определение общего белка;P83_Chloridemmdesc;"Неоходимо уточнить для какой услуги заполняются параметры:  Уровень хлорида в крови в ммоль/л Уровень хлорида в крови в мэкв/дл Поэтому параметр не обязателен к заполнению.";false;false;71
Биохимические исследования крови - определение общего белка;P84_Chloridemecvalue;"Неоходимо уточнить для какой услуги заполняются параметры:  Уровень хлорида в крови в ммоль/л Уровень хлорида в крови в мэкв/дл Поэтому параметр не обязателен к заполнению.";false;false;72
Биохимические исследования крови - определение общего белка;P85_Chloridemecdesc;"Неоходимо уточнить для какой услуги заполняются параметры:  Уровень хлорида в крови в ммоль/л Уровень хлорида в крови в мэкв/дл Поэтому параметр не обязателен к заполнению.";false;false;73
Биохимические исследования крови - определение общего белка;P86_Acidityvalue;"Неоходимо уточнить для какой услуги заполняются параметры:  Уровень хлорида в крови в ммоль/л Уровень хлорида в крови в мэкв/дл Поэтому параметр не обязателен к заполнению.";false;false;74
Биохимические исследования крови - определение общего белка;P87_Aciditydesc;"Неоходимо уточнить для какой услуги заполняются параметры:  Уровень хлорида в крови в ммоль/л Уровень хлорида в крови в мэкв/дл Поэтому параметр не обязателен к заполнению.";false;false;75
Биохимические исследования крови - определение общего белка;P94_Totalproteinglvalue;;false;true;82
Биохимические исследования крови - определение общего белка;P95_Totalproteingldesc;;false;true;83
Биохимические исследования крови - определение общего белка;P96_Totalproteingdlvalue;;false;true;84
Биохимические исследования крови - определение общего белка;P97_Totalproteingdldesc;;false;true;85
Биохимические исследования крови - определение общего билирубина;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение общего билирубина;P14_Totalbilirubinmcmvalue;;false;true;2
Биохимические исследования крови - определение общего билирубина;P15_Totalbilirubinmcmdesc;;false;true;3
Биохимические исследования крови - определение общего билирубина;P16_Totalbilirubinmgvalue;;false;true;4
Биохимические исследования крови - определение общего билирубина;P17_Totalbilirubinmgdesc;;false;true;5
Биохимические исследования крови - определение общего билирубина;P18_Conjugatedbilirubinmcmvalue;;false;true;6
Биохимические исследования крови - определение общего билирубина;P19_Conjugatedbilirubinmcmdesc;;false;true;7
Биохимические исследования крови - определение общего билирубина;P20_Conjugatedbilirubinmgvalue;;false;true;8
Биохимические исследования крови - определение общего билирубина;P21_Conjugatedbilirubinmgdesc;;false;true;9
Биохимические исследования крови - определение общего холестерина;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение общего холестерина;P50_Holestermmvalue;;false;true;38
Биохимические исследования крови - определение общего холестерина;P51_Holestermmdesc;;false;true;39
Биохимические исследования крови - определение общего холестерина;P52_Holestermgvalue;;false;true;40
Биохимические исследования крови - определение общего холестерина;P53_Holestermgdesc;;false;true;41
Биохимические исследования крови - определение триглицеридов;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение триглицеридов;P54_Triglyceridsmmvalue;;false;true;42
Биохимические исследования крови - определение триглицеридов;P55_Triglyceridsmmdesc;;false;true;43
Биохимические исследования крови - определение триглицеридов;P56_Triglyceridsmgvalue;;false;true;44
Биохимические исследования крови - определение триглицеридов;P57_Triglyceridsmgdesc;;false;true;45
Биохимические исследования крови - определение фосфора неорганического;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение фосфора неорганического;P66_Phosphormmvalue;;false;true;54
Биохимические исследования крови - определение фосфора неорганического;P67_Phosphormmdesc;;false;true;55
Биохимические исследования крови - определение фосфора неорганического;P68_Phosphormgvalue;;false;true;56
Биохимические исследования крови - определение фосфора неорганического;P69_Phosphormgdesc;;false;true;57
Биохимические исследования крови - определение щелочной фосфатазы;P0_Venousbloodanalysisnum;Один параметр отноится к множеству услуг. Видимо на форме нужно добавить логику, что tech_name не дублировался на форме.;true;false;1
Биохимические исследования крови - определение щелочной фосфатазы;P34_Shelochfosfatvalue;;false;true;22
Биохимические исследования крови - определение щелочной фосфатазы;P35_Shelochfosfatdesc;;false;true;23
Биркование сельскохозяйственных животных;P0_Petlabelidentificationcode;Добавил только в таблицу ServiceParam. Нет связи с отчетом.;false;true;1
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P0_Balanceinventorynumber;если делать - нужно увести в маппинг;false;true;2
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P0_Vaccinename;ИВАН: Это ТМЦ. ОК?;false;true;1
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P0_VisitServiceTMCcount;ИВАН: Это ТМЦ. ОК?;false;true;3
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P13_Servicetext;;false;true;4
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P17_Petcolor;изменил 270718 - убрал флаг Visit;false;true;5
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P18_Petspecialtrait;изменил 270718 - убрал флаг Visit;false;true;6
Ветеринарное освидетельствование животных для оформления ветеринарных сопроводительных документов, включающая проведение клинического осмотра и изучение ветеринарных документов (паспорта на животное, результатов лабораторных исследований и др.) - с гельминтокопрологическим исследованием;P0_Coproalysisnum;Добавил только в таблицу ServiceParam. Нет связи с отчетом.;true;false;1
Вакцинация животных с проведением клинического осмотра, консультации, инъекции;P15_Vacexpirationdate;;false;true;7
Взвешивание животных;P0_Petweight;;false;true;1
Взятие мазка отпечатка на цитологический анализ;P0_Cytologicsanalysisnum;;false;true;1
Взятие проб крови из вены;P0_Venousbloodanalysisnum;;false;true;1
Взятие проб крови из капилляра;P0_Capillarybloodanalysisnum;;false;true;1
Выезд ветврача;P0_Juraddress;;true;false;1
Выезд для оказания ветеринарной помощи на дому;P0_Juraddress;;true;false;1
Гельминтокопрологические исследования;P0_Coproalysisnum;;true;false;1
Взятие соскобов, мазков, смывов для диагностических исследований;P0_Diagnostictestinganalysisnum;;false;true;1
Гельминтокопрологические исследования;P12_Serviceresultvalue;;false;true;2
Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см);P0_Petweight;;true;false;1
Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см);P0_Petwoollength;;true;false;1
Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см);P0_Petweight;;true;false;1
Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см);P0_Petwoollength;;true;false;1
Исследование на кровепаразитарные болезни;P0_Capillarybloodanalysisnum;Ранее был указан параметр P12_Analysisnum ;true;false;1
Исследование на кровепаразитарные болезни;P14_Analysiscount;P14_Analysismicrocapillarybloodcount ;true;false;2
Гельминтокопрологические исследования;P13_Serviceresultdesc;;false;false;3
Исследование на кровепаразитарные болезни;P15_Activator;P15_Analysismicrocapillarybloodactivator;false;true;18
Исследование на кровепаразитарные болезни;P15_Analysisresult;P15_Analysismicrocapillarybloodresult;false;true;3
Исследование на кровепаразитарные болезни;P18_Analysisdate;P18_Analysismicrocapillaryblooddate;true;false;5
Исследование на кровепаразитарные болезни;P16_Analysisdesc;P16_Analysismicrocapillaryblooddesc;false;false;4
Исследование на кровепаразитарные болезни;P19_Animalcount;P19_Animalcount;false;false;29
Исследование на кровепаразитарные болезни;P20_Analysisobject;P20_Analysisobject;false;false;28
Исследование на кровепаразитарные болезни;P21_Diagnostictechnique;Сама услуга должна предполагать конкретный метод исследования. ;false;false;27
Кастрация, стерилизация (оперативное вмешательство) - кошки, самки декоративных животных (хорьки, норки, морские свинки, лисы и другие животные);P8_Petsex;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг;P0_Petweight;;true;false;3
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг;P10_Petbirthday;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг;P8_Petsex;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг;P0_Petweight;;true;false;3
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг;P10_Petbirthday;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг;P8_Petsex;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг;P0_Petweight;;true;false;3
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг;P10_Petbirthday;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг;P8_Petsex;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - с/х ж-ые до 2-х месяцев, коты, самцы декоративных животных (хорьки, норки, морские свинки, лисы и другие животные);P10_Petbirthday;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - с/х ж-ые до 2-х месяцев, коты, самцы декоративных животных (хорьки, норки, морские свинки, лисы и другие животные);P8_Petsex;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг;P0_Petweight;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг;P8_Petsex;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг;P0_Petweight;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг;P8_Petsex;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг;P0_Petweight;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг;P8_Petsex;;true;false;1
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг;P0_Petweight;;true;false;2
Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг;P8_Petsex;;true;false;1
Купирование ушных раковин у собак - до 10-ти дневного возраста (с местным обезболиванием);P10_Petbirthday;;true;false;3
Купирование ушных раковин у собак - от 10-ти дневного до 3-х месячного возраста;P10_Petbirthday;;true;false;2
Купирование ушных раковин у собак - свыше 3-х месячного возраста;P10_Petbirthday;;true;false;1
Исследование на кровепаразитарные болезни;P22_Parasdiseasesanswerdate;"Дата ответа. См. вкладку ""Param""";false;false;25
Люминесцентная диагностика на микроспорию с применением лампы Вуда;P12_Serviceresultvalue;;false;true;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - до 5 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 10 до 20 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 20 до 30 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 30 до 40 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 40 до 50 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 5 до 10 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 50 до 60 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 60 до 70 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 70 до 80 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 80 до 90 кг;P0_Petweight;;true;false;1
Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 90 до 100 кг;P0_Petweight;;true;false;1
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P12_Analysisnum;;true;false;1
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P14_Analysiscount;;true;false;2
Люминесцентная диагностика на микроспорию с применением лампы Вуда;P13_Serviceresultdesc;;false;false;2
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P15_Analysisresult;;false;true;3
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P18_Analysisdate;;true;false;5
Наложение гипсовой повязки (без репозиции) - крупные породы собак;P0_Petweight;;true;false;1
Наложение гипсовой повязки (без репозиции) - мелкие породы собак и кошки;P0_Petweight;;true;false;1
Обрезка рогов с/х животного;P10_Petbirthday;;true;false;1
Обрезка рогов с/х животного - обезроживание телят;P10_Petbirthday;;true;false;1
Общий анализ кала;P0_Coproalysisnum;;true;false;1
Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты;P16_Analysisdesc;;false;false;4
Общий анализ кала;P14_Coprformvalue;;false;true;2
Общий анализ кала;P15_Coprformdesc;;false;false;3
Общий анализ кала;P16_Coprcolorvalue;;false;true;4
Общий анализ кала;P17_Coprcolordesc;;false;false;5
Общий анализ кала;P18_Coprodorvalue;;false;true;6
Общий анализ кала;P19_Coprodordesc;;false;false;7
Общий анализ кала;P20_Acidityvalue;;false;true;8
Общий анализ кала;P21_Aciditydesc;;false;false;9
Общий анализ кала;P22_Stercobilinvalue;;false;true;10
Общий анализ кала;P23_Stercobilindesc;;false;false;11
Общий анализ кала;P24_Bilirubinvalue;;false;true;12
Общий анализ кала;P25_Bilirubindesc;;false;false;13
Общий анализ кала;P26_Bloodvalue;;false;true;14
Общий анализ кала;P27_Blooddesc;;false;false;15
Общий анализ кала;P28_Muscledfibersvalue;;false;true;16
Общий анализ кала;P29_Muscledfibersdesc;;false;false;17
Общий анализ кала;P30_Contissuefibersvalue;;false;true;18
Общий анализ кала;P31_Contissuefibersdesc;;false;false;19
Общий анализ кала;P32_Neutralfatvalue;;false;true;20
Общий анализ кала;P33_Neutralfatdesc;;false;false;21
Общий анализ кала;P34_Fattyacidsvalue;;false;true;22
Общий анализ кала;P35_Fattyacidsdesc;;false;false;23
Общий анализ кала;P36_Soapvalue;;false;true;24
Общий анализ кала;P37_Soapdesc;;false;false;25
Общий анализ кала;P38_Starchvalue;;false;true;26
Общий анализ кала;P39_Coproanswerdate;"Дата ответа. См. вкладку ""Param""";false;false;
Общий анализ мочи;P0_Urinalysisnum;Номер пробы мочи. Добавил только в таблицу ServiceParam. Нет связи с отчетом.;true;false;1
Общий анализ кала;P39_Starchdesc;;false;false;27
Общий анализ мочи;P14_Colorurinevalue;;false;true;2
Общий анализ мочи;P15_Colorurinedesc;;false;false;3
Общий анализ мочи;P16_Transparencyvalue;;false;true;4
Общий анализ мочи;P17_Transparencydesc;;false;false;5
Общий анализ мочи;P18_Acidityvalue;;false;true;6
Общий анализ мочи;P19_Aciditydesc;;false;false;7
Общий анализ мочи;P20_Proteinvalue;;false;true;8
Общий анализ мочи;P21_Proteindesc;;false;false;9
Общий анализ мочи;P22_Glukozavalue;;false;true;10
Общий анализ мочи;P23_Glukozadesc;;false;false;11
Общий анализ мочи;P24_Ketonbodvalue;;false;true;12
Общий анализ мочи;P25_Ketonboddesc;;false;false;13
Общий анализ мочи;P26_Relativedensityvalue;;false;true;14
Общий анализ мочи;P27_Relativedensitydesc;;false;false;15
Общий анализ мочи;P28_Bilirubinvalue;;false;true;16
Общий анализ мочи;P29_Bilirubindesc;;false;false;17
Общий анализ мочи;P30_Hemeglvalue;;false;true;18
Общий анализ мочи;P31_Hemegldesc;;false;false;19
Общий анализ мочи;P32_Erythrocytvalue;;false;true;20
Общий анализ мочи;P33_Erythrocytdesc;;false;false;21
Общий анализ мочи;P34_Leucocytvalue;;false;true;22
Общий анализ мочи;P35_Leucocytdesc;;false;false;23
Общий анализ мочи;P36_Ploskiyvalue;;false;true;24
Общий анализ мочи;P37_Ploskiydesc;;false;false;25
Общий анализ мочи;P38_Perehodvalue;;false;true;26
Общий анализ мочи;P39_Perehoddesc;;false;false;27
Общий анализ мочи;P40_Pochechnvalue;;false;true;28
Общий анализ мочи;P41_Pochechndesc;;false;false;29
Общий анализ мочи;P42_Hyalinevalue;;false;true;30
Общий анализ мочи;P43_Hyalinedesc;;false;false;31
Общий анализ мочи;P44_Granularvalue;;false;true;32
Общий анализ мочи;P45_Granulardesc;;false;false;33
Общий анализ мочи;P46_Waxvalue;;false;true;34
Общий анализ мочи;P47_Waxdesc;;false;false;35
Общий анализ мочи;P48_Lekocitvalue;;false;true;36
Общий анализ мочи;P49_Lekocitdesc;;false;false;37
Общий анализ мочи;P50_Eritrocitvalue;;false;true;38
Общий анализ мочи;P51_Eritrocitdesc;;false;false;39
Общий анализ мочи;P52_Epitelvalue;;false;true;40
Общий анализ мочи;P53_Epiteldesc;;false;false;41
Общий анализ мочи;P54_Cilindvalue;;false;true;42
Общий анализ мочи;P55_Cilinddesc;;false;false;43
Общий анализ мочи;P56_Bacteriavalue;;false;true;44
Общий анализ мочи;P57_Bacteriadesc;;false;false;45
Общий анализ мочи;P58_Saltvalue;;false;true;46
Общий анализ мочи;P59_Saltdesc;;false;false;47
Общий анализ мочи;P61_Urinunitweightvalue;;false;false;48
Общий анализ мочи;P62_Urinreactionvalue;;false;false;49
Общий анализ мочи;P63_Nitratvalue;;false;false;50
Общий анализ мочи;P64_Urinorddepositionvalue;;false;false;51
Общий анализ мочи;P65_Urindisorddepositionvalue;;false;false;52
Общий анализ мочи;P66_UrinAnswerdate;"Дата ответа. См. вкладку ""Param""";false;false;53
Общий клинический анализ крови - выведение лейкоцитарной формулы;P0_Venousbloodanalysisnum;Общий параметр для нескольких услуг. Не дублировать на форме Отчета;true;false;1
Общий анализ мочи;P67_Urinconsistency;;false;false;54
Общий клинический анализ крови - определение гемоглобина;P0_Venousbloodanalysisnum;Общий параметр для нескольких услуг. Не дублировать на форме Отчета;true;false;1
Общий клинический анализ крови - определение СОЭ;P0_Venousbloodanalysisnum;Общий параметр для нескольких услуг. Не дублировать на форме Отчета;true;false;1
Общий клинический анализ крови - подсчет лейкоцитов;P0_Venousbloodanalysisnum;Общий параметр для нескольких услуг. Не дублировать на форме Отчета;true;false;1
Общий клинический анализ крови - подсчет эритроцитов;P0_Venousbloodanalysisnum;Общий параметр для нескольких услуг. Не дублировать на форме Отчета;true;false;1
Общий клинический анализ крови - подсчет лейкоцитов;P14_Wbcvalue;;false;true;2
Общий клинический анализ крови - подсчет лейкоцитов;P15_Wbcdesc;;false;false;3
Общий клинический анализ крови - подсчет лейкоцитов;P16_Lymvalue;;false;true;4
Общий клинический анализ крови - подсчет лейкоцитов;P17_Lymdesc;;false;false;5
Общий клинический анализ крови - подсчет лейкоцитов;P18_Monvalue;;false;true;6
Общий клинический анализ крови - подсчет лейкоцитов;P19_Mondesc;;false;false;7
Общий клинический анализ крови - подсчет лейкоцитов;P20_Gravalue;;false;true;8
Общий клинический анализ крови - подсчет лейкоцитов;P21_Gradesc;;false;false;9
Общий клинический анализ крови - подсчет эритроцитов;P22_Rbcvalue;;false;true;10
Общий клинический анализ крови - подсчет эритроцитов;P23_Rbcdesc;;false;false;11
Общий клинический анализ крови - определение гемоглобина;P24_Hgbvalue;;false;true;12
Общий клинический анализ крови - определение гемоглобина;P25_Hgbdesc;;false;false;13
Общий клинический анализ крови - определение гемоглобина;P26_Hctvalue;;false;true;14
Общий клинический анализ крови - определение гемоглобина;P27_Hctdesc;;false;false;15
Общий клинический анализ крови - подсчет эритроцитов;P28_Mcvvalue;;false;true;16
Общий клинический анализ крови - подсчет эритроцитов;P29_Mcvdesc;;false;false;17
Общий клинический анализ крови - определение гемоглобина;P30_Mchvalue;;false;true;18
Общий клинический анализ крови - определение гемоглобина;P31_Mchdesc;;false;false;19
Общий клинический анализ крови - определение гемоглобина;P32_Mchcvalue;;false;true;20
Общий клинический анализ крови - определение гемоглобина;P33_Mchcdesc;;false;false;21
Общий клинический анализ крови - подсчет эритроцитов;P34_Rdwvalue;;false;true;22
Общий клинический анализ крови - подсчет эритроцитов;P35_Rdwdesc;;false;false;23
Общий клинический анализ крови - подсчет эритроцитов;P36_Pltvalue;;false;false;24
Общий клинический анализ крови - подсчет эритроцитов;P37_Pltdesc;;false;false;25
Общий клинический анализ крови - подсчет эритроцитов;P38_Mpvvalue;;false;false;26
Общий клинический анализ крови - подсчет эритроцитов;P39_Mpvdesc;;false;false;27
Общий клинический анализ крови - подсчет эритроцитов;P40_Pctvalue;;false;false;28
Общий клинический анализ крови - подсчет эритроцитов;P41_Pctdesc;;false;false;29
Общий клинический анализ крови - подсчет эритроцитов;P42_Pdwvalue;;false;false;30
Общий клинический анализ крови - подсчет эритроцитов;P43_Pdwdesc;;false;false;31
Общий клинический анализ крови - определение СОЭ;P44_Soevalue;;false;true;32
Общий клинический анализ крови - определение СОЭ;P45_Soedesc;;false;false;33
Общий клинический анализ крови - выведение лейкоцитарной формулы;P46_Youngvalue;;false;true;34
Общий клинический анализ крови - выведение лейкоцитарной формулы;P47_Youngdesc;;false;false;35
Общий клинический анализ крови - выведение лейкоцитарной формулы;P48_Palochkoyadervalue;;false;true;36
Общий клинический анализ крови - выведение лейкоцитарной формулы;P49_Palochkoyaderdesc;;false;false;37
Общий клинический анализ крови - выведение лейкоцитарной формулы;P50_Segmentvalue;;false;true;38
Общий клинический анализ крови - выведение лейкоцитарной формулы;P51_Segmentdesc;;false;false;39
Общий клинический анализ крови - выведение лейкоцитарной формулы;P52_Eosinophilsvalue;;false;true;40
Общий клинический анализ крови - выведение лейкоцитарной формулы;P53_Eosinophilsdesc;;false;false;41
Общий клинический анализ крови - выведение лейкоцитарной формулы;P54_Monocitvalue;;false;true;42
Общий клинический анализ крови - выведение лейкоцитарной формулы;P55_Monocitdesc;;false;false;43
Общий клинический анализ крови - выведение лейкоцитарной формулы;P56_Bazophilvalue;;false;true;44
Общий клинический анализ крови - выведение лейкоцитарной формулы;P57_Bazophildesc;;false;false;45
Общий клинический анализ крови - выведение лейкоцитарной формулы;P58_Limphocitvalue;;false;true;46
Определение гормонов в сыворотке крови - кортизол;P0_Venousbloodanalysisnum;;true;false;1
Общий клинический анализ крови - выведение лейкоцитарной формулы;P59_Limphocitdesc;;false;false;47
Определение гормонов в сыворотке крови - кортизол;P14_Cortisolbazalvalue;;false;true;2
Определение гормонов в сыворотке крови - кортизол;P15_Cortisolactgvalue;;false;true;3
Определение гормонов в сыворотке крови - прогестерон;P0_Venousbloodanalysisnum;;true;false;1
Определение гормонов в сыворотке крови - кортизол;P16_Cortisoldexvalue;;false;true;4
Определение гормонов в сыворотке крови - прогестерон;P17_Proganesvalue;;false;true;5
Определение гормонов в сыворотке крови - прогестерон;P18_Progproenstvalue;;false;true;6
Определение гормонов в сыворотке крови - прогестерон;P19_Progestrusvalue;;false;true;7
Определение гормонов в сыворотке крови - тестостерон;P0_Venousbloodanalysisnum;;true;false;1
Определение гормонов в сыворотке крови - прогестерон;P20_Progmetestvalue;;false;true;8
Определение гормонов в сыворотке крови - тироксин;P0_Venousbloodanalysisnum;;true;false;1
Определение гормонов в сыворотке крови - тестостерон;P26_Testostervalue;;false;true;14
Определение гормонов в сыворотке крови - трийодтиронин;P0_Venousbloodanalysisnum;;true;false;1
Определение гормонов в сыворотке крови - тироксин;P27_Thyroxvalue;;false;true;15
Определение гормонов в сыворотке крови - эстрадиол;P0_Venousbloodanalysisnum;;true;false;1
Определение гормонов в сыворотке крови - трийодтиронин;P28_Triiodtirvalue;;false;true;16
Определение гормонов в сыворотке крови - эстрадиол;P21_Estradanesvalue;;false;true;9
Определение гормонов в сыворотке крови - эстрадиол;P22_Estradproenstvalue;;false;true;10
Определение гормонов в сыворотке крови - эстрадиол;P23_Estradestrusvalue;;false;true;11
Определение гормонов в сыворотке крови - эстрадиол;P24_Estradmetestvalue;;false;true;12
Определение гормонов в сыворотке крови - эстрадиол;P25_Estradmalevalue;;false;true;13
Повторное ультразвуковое исследование;P0_Organsystem;Определяющий параметр;true;false;1
Определение слезопродукции при диагностике глаз (тест Ширмера);P0_Schirmertestresult;;false;true;1
Повторное ультразвуковое исследование;P14_Matkadiametrtela;УЗИ репр. Самки;false;true;1
Повторное ультразвуковое исследование;P14_Odrazmerperednegootrezka;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;2
Повторное ультразвуковое исследование;P14_Pechenraspoloshenie;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;1
Повторное ультразвуковое исследование;P14_Predstzhelezarazmer;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;1
Повторное ультразвуковое исследование;P14_Prvpochraspoloshenie;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;1
Повторное ультразвуковое исследование;P15_Matkatolshinatela;УЗИ репр. Самки;false;true;2
Повторное ультразвуковое исследование;P15_Odrazmerzadnegootrezka;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;3
Повторное ультразвуковое исследование;P15_Pechenkontur;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;2
Повторное ультразвуковое исследование;P15_Predstzhelezakontur;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;2
Повторное ультразвуковое исследование;P15_Prvpochgranica;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;2
Повторное ультразвуковое исследование;P16_Matkastrukturastenkitela;УЗИ репр. Самки;false;true;3
Повторное ультразвуковое исследование;P16_Odstructuraperedcamer;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;4
Повторное ультразвуковое исследование;P16_Pechenrazmer;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;3
Повторное ультразвуковое исследование;P16_Predstzhelezaparenkhima;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;3
Повторное ультразвуковое исследование;P16_Prvpochrazmer;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;3
Повторное ультразвуковое исследование;P17_Matkasostoyanpolosti;УЗИ репр. Самки;false;true;4
Повторное ультразвуковое исследование;P17_Odrazmerhrust;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;5
Повторное ультразвуковое исследование;P17_Pechenekhostruktura;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;4
Повторное ультразвуковое исследование;P17_Predstzhelezaobyemnobrazov;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;4
Повторное ультразвуковое исследование;P17_Prvpochkortiksloytolshina;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;4
Повторное ультразвуковое исследование;P18_Matkadiametrpravroga;УЗИ репр. Самки;false;true;5
Повторное ультразвуковое исследование;P18_Odstructurahrust;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;6
Повторное ультразвуковое исследование;P18_Pechenekhogennost;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;5
Повторное ультразвуковое исследование;P18_Pravsemrazmer;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;5
Повторное ультразвуковое исследование;P18_Prvpochkortiksloyekhogennost;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;5
Повторное ультразвуковое исследование;P19_Matkatolshinapravroga;УЗИ репр. Самки;false;true;6
Повторное ультразвуковое исследование;P19_Odcapsulahrust;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;7
Повторное ультразвуковое исследование;P19_Pechenperifsosudrisunok;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;6
Повторное ультразвуковое исследование;P19_Pravsemkontur;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;6
Повторное ультразвуковое исследование;P19_Prvpochkortiksloyekhostruktura;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;6
Повторное ультразвуковое исследование;P20_Matkastrukturastenkipravroga;УЗИ репр. Самки;false;true;7
Повторное ультразвуковое исследование;P20_Odstructurasteklotelo;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;8
Повторное ультразвуковое исследование;P20_Pechenportae;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;7
Повторное ультразвуковое исследование;P20_Pravsemparenkhima;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;7
Повторное ультразвуковое исследование;P20_Prvpochmedullyarsloytolshchina;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;7
Повторное ультразвуковое исследование;P21_Matkasoderzhimpolostipravroga;УЗИ репр. Самки;false;true;8
Повторное ультразвуковое исследование;P21_Oddiametrzrachka;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;9
Повторное ультразвуковое исследование;P21_Pechenvhepatica;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;8
Повторное ультразвуковое исследование;P21_Pravsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;8
Повторное ультразвуковое исследование;P21_Prvpochmedullyarsloyekhogennost;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;8
Повторное ультразвуковое исследование;P22_Matkadiametrlevroga;УЗИ репр. Самки;false;true;9
Повторное ультразвуковое исследование;P22_Odcontur;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;10
Повторное ультразвуковое исследование;P22_Pechenahepatica;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;9
Повторное ультразвуковое исследование;P22_Pridatokpravsemgolovka;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;9
Повторное ультразвуковое исследование;P22_Prvpochmedullyarsloyekhostruktura;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;9
Повторное ультразвуковое исследование;P23_Matkatolshinalevroga;УЗИ репр. Самки;false;true;10
Повторное ультразвуковое исследование;P23_Odstructura;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;11
Повторное ультразвуковое исследование;P23_Pechenobyemnobrazov;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;10
Повторное ультразвуковое исследование;P23_Pridatokpravsemtelo;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;10
Повторное ультразвуковое исследование;P23_Prvpochmedullyarsloykortmeddiffer;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;10
Повторное ультразвуковое исследование;P24_Matkastrukturastenkilevroga;УЗИ репр. Самки;false;true;11
Повторное ультразвуковое исследование;P24_Odstructuradiskazritnerva;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;12
Повторное ультразвуковое исследование;P24_Pridatokpravsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;11
Повторное ультразвуковое исследование;P24_Prvpochpiyelicheskiyindeks;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;11
Повторное ультразвуковое исследование;P24_Zhelchpuzyrstepennapolneniya;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;11
Повторное ультразвуковое исследование;P25_Levsemrazmer;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;12
Повторное ультразвуковое исследование;P25_Matkasoderzhimpolostilevroga;УЗИ репр. Самки;false;true;12
Повторное ультразвуковое исследование;P25_Odstructuraretrobulyar;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;13
Повторное ультразвуковое исследование;P25_Prvpochpochsinusekhogennost;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;12
Повторное ультразвуковое исследование;P25_Zhelchpuzyrformazhelchpuzyrya;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;12
Повторное ультразвуковое исследование;P26_Levsemkontur;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;13
Повторное ультразвуковое исследование;P26_Osrazmerperednegootrezka;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;14
Повторное ультразвуковое исследование;P26_Pravyaichnikrazmer;УЗИ репр. Самки;false;true;13
Повторное ультразвуковое исследование;P26_Prvpochpochsinuschetkostdifferents;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;13
Повторное ультразвуковое исследование;P26_Zhelchpuzyrtolshchinastenki;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;13
Повторное ультразвуковое исследование;P27_Levsemparenkhima;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;14
Повторное ультразвуковое исследование;P27_Osrazmerzadnegootrezka;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;15
Повторное ультразвуковое исследование;P27_Pravyaichnikkontur;УЗИ репр. Самки;false;true;14
Повторное ультразвуковое исследование;P27_Prvpochpochsinuspolostlokhanki;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;14
Повторное ультразвуковое исследование;P27_Zhelchpuzyrdeformatsiya;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;14
Повторное ультразвуковое исследование;P28_Levsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;15
Повторное ультразвуковое исследование;P28_Osstructuraperedcamer;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;16
Повторное ультразвуковое исследование;P28_Pravyaichniknovoobrazov;УЗИ репр. Самки;false;true;15
Повторное ультразвуковое исследование;P28_Prvpochpochsinusstepenlokhanki;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;15
Повторное ультразвуковое исследование;P28_Zhelchpuzyrstrukturazhelchi;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;15
Повторное ультразвуковое исследование;P29_Levyaichnikrazmer;УЗИ репр. Самки;false;true;16
Повторное ультразвуковое исследование;P29_Osrazmerhrust;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;17
Повторное ультразвуковое исследование;P29_Pridatoklevsemgolovka;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;16
Повторное ультразвуковое исследование;P29_Prvpochsosudyparenkhimy;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;16
Повторное ультразвуковое исследование;P29_Zhelchpuzyrpuzyrprotok;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;16
Повторное ультразвуковое исследование;P30_Levyaichnikkontur;УЗИ репр. Самки;false;true;17
Повторное ультразвуковое исследование;P30_Osstructurahrust;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;18
Повторное ультразвуковое исследование;P30_Pridatoklevsemtelo;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;17
Повторное ультразвуковое исследование;P30_PrvpochIndeksrezistivnpochechnart;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;17
Повторное ультразвуковое исследование;P30_Zhelchpuzyrobshzhelchprotok;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;17
Повторное ультразвуковое исследование;P31_Levyaichniknovoobrazov;УЗИ репр. Самки;false;true;18
Повторное ультразвуковое исследование;P31_Oscapsulahrust;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;19
Повторное ультразвуковое исследование;P31_Pridatoklevsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;18
Повторное ультразвуковое исследование;P31_PrvpochIndeksrezistivnmezhdolevoyart;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;18
Повторное ультразвуковое исследование;P31_Zhelchpuzyrpechenochnprotok;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;18
Повторное ультразвуковое исследование;P32_Abdomultmserviceresult;19. УЗИ репр. Самца.pdf . Повторное ультразвуковое исследование;false;true;19
Повторное ультразвуковое исследование;P32_Osstructurasteklotelo;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;20
Повторное ультразвуковое исследование;P32_Prvpochkonkrementy;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;19
Повторное ультразвуковое исследование;P32_Serviceresult;УЗИ репр. Самки;false;true;19
Повторное ультразвуковое исследование;P32_Zhelchpuzyrobyemnobrazov;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;19
Повторное ультразвуковое исследование;P33_Osdiametrzrachka;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;21
Повторное ультразвуковое исследование;P33_Prvpochobyemnobrazov;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;20
Повторное ультразвуковое исследование;P33_Selezenkaraspoloshenie;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;20
Повторное ультразвуковое исследование;P34_Levpochraspoloshenie;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;21
Повторное ультразвуковое исследование;P34_Oscontur;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;22
Повторное ультразвуковое исследование;P34_Selezenkakontur;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;21
Повторное ультразвуковое исследование;P35_Levpochgranica;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;22
Повторное ультразвуковое исследование;P35_Osstructura;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;23
Повторное ультразвуковое исследование;P35_Selezenkarazmer;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;22
Повторное ультразвуковое исследование;P36_Levpochrazmer;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;23
Повторное ультразвуковое исследование;P36_Osstructuradiskazritnerva;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;24
Повторное ультразвуковое исследование;P36_Selezenkaekhostruktura;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;23
Повторное ультразвуковое исследование;P37_Levpochkortiksloytolshina;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;24
Повторное ультразвуковое исследование;P37_Osstructuraretrobulyar;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;25
Повторное ультразвуковое исследование;P37_Selezenkaekhogennost;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;24
Повторное ультразвуковое исследование;P38_Levpochkortiksloyekhogennost;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;25
Повторное ультразвуковое исследование;P38_Selezenkasosudrisunok;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;25
Повторное ультразвуковое исследование;P38_Serviceresult;15. УЗИ глаза.pdf. Повторное ультразвуковое исследование;false;true;26
Повторное ультразвуковое исследование;P39_Levpochkortiksloyekhostruktura;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;26
Повторное ультразвуковое исследование;P39_Selezenkaobyemnobrazov;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;26
Повторное ультразвуковое исследование;P40_Levpochmedullyarsloytolshchina;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;27
Повторное ультразвуковое исследование;P40_Podzhelzhelezaraspoloshenie;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;27
Повторное ультразвуковое исследование;P41_Levpochmedullyarsloyekhogennost;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;28
Повторное ультразвуковое исследование;P41_Podzhelzhelezakontur;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;28
Повторное ультразвуковое исследование;P42_Levpochmedullyarsloyekhostruktura;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;29
Повторное ультразвуковое исследование;P42_Podzhelzhelezarazmer;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;29
Повторное ультразвуковое исследование;P43_Levpochmedullyarsloykortmeddiffer;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;30
Повторное ультразвуковое исследование;P43_Podzhelzhelezaekhostruktura;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;30
Повторное ультразвуковое исследование;P44_Levpochpiyelicheskiyindeks;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;31
Повторное ультразвуковое исследование;P44_Podzhelzhelezaekhogennost;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;31
Повторное ультразвуковое исследование;P45_Levpochpochsinusekhogennost;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;32
Повторное ультразвуковое исследование;P45_Podzhelzhelezaobyemnobrazov;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;32
Повторное ультразвуковое исследование;P46_Levpochpochsinuschetkostdifferents;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;33
Повторное ультразвуковое исследование;P46_Zheludkishechntrakt;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;33
Повторное ультразвуковое исследование;P47_Levpochpochsinuspolostlokhanki;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;34
Повторное ультразвуковое исследование;P47_Svobodnzhidkost;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;34
Повторное ультразвуковое исследование;P48_Levpochpochsinusstenkilokhanki;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;35
Повторное ультразвуковое исследование;P48_Serviceresult;17. УЗИ печ. и т.д.pdf . Повторное ультразвуковое исследование;false;true;35
Повторное ультразвуковое исследование;P49_Levpochsosudyparenkhimy;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;36
Повторное ультразвуковое исследование;P50_LevpochIndeksrezistivnpochechnart;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;37
Повторное ультразвуковое исследование;P51_LevpochIndeksrezistivnmezhdolevoyart;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;38
Повторное ультразвуковое исследование;P52_Levpochkonkrementy;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;39
Повторное ультразвуковое исследование;P53_Levpochobyemnobrazov;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;40
Повторное ультразвуковое исследование;P54_Mochpuzstepnapoln;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;41
Повторное ультразвуковое исследование;P55_Mochpuztolshchinastenki;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;42
Повторное ультразвуковое исследование;P56_Mochpuzdeformatsiya;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;43
Повторное ультразвуковое исследование;P57_Mochpuzuretra;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;44
Повторное ультразвуковое исследование;P58_MochpuzObyemnobrazov;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;45
Повторное ультразвуковое исследование;P59_Serviceresult;16. УЗИ мочевыдел.pdf . Повторное ультразвуковое исследование;false;true;46
Санитарная помывка животных - крупные животные (свыше 15 кг);P0_Petweight;;true;false;1
Санитарная помывка животных - мелкие животные (до 5 кг);P0_Petweight;;true;false;1
Санитарная помывка животных - средние животные (свыше 5 кг до 15 кг);P0_Petweight;;true;false;1
Санитарная стрижка животных - крупные животные (свыше 15 кг);P0_Petweight;;true;false;1
Санитарная стрижка животных - мелкие животные (до 5 кг);P0_Petweight;;true;false;1
Санитарная стрижка животных - средние животные (свыше 5 кг до 15 кг);P0_Petweight;;true;false;1
Пункционная биопсия на цитологический анализ;P0_Biopsycytologicsanalysisnum;Не понятно для какой услуги является вхоядщим параметром.;false;true;1
Скрининговое ЭХО-кардиографическое исследование;P14_LVIDd;;false;true;1
Скрининговое ЭХО-кардиографическое исследование;P15_LVIDs;;false;true;2
Скрининговое ЭХО-кардиографическое исследование;P16_LVWTd;;false;true;3
Скрининговое ЭХО-кардиографическое исследование;P17_LVWTs;;false;true;4
Скрининговое ЭХО-кардиографическое исследование;P18_IVSTd;;false;true;5
Скрининговое ЭХО-кардиографическое исследование;P19_IVSTs;;false;true;6
Скрининговое ЭХО-кардиографическое исследование;P20_EF;;false;true;7
Скрининговое ЭХО-кардиографическое исследование;P21_FS;;false;false;8
Скрининговое ЭХО-кардиографическое исследование;P22_LA;;false;true;9
Скрининговое ЭХО-кардиографическое исследование;P23_AO;;false;false;10
Скрининговое ЭХО-кардиографическое исследование;P24_LA/AO;;false;true;11
Скрининговое ЭХО-кардиографическое исследование;P25_RVIDd;;false;false;12
Скрининговое ЭХО-кардиографическое исследование;P26_RVIDs;;false;true;13
Скрининговое ЭХО-кардиографическое исследование;P27_RVWTd;;false;true;14
Скрининговое ЭХО-кардиографическое исследование;P28_RVWTs;;false;true;15
Скрининговое ЭХО-кардиографическое исследование;P29_RA;;false;true;16
Скрининговое ЭХО-кардиографическое исследование;P30_Defektivs;;false;true;17
Скрининговое ЭХО-кардиографическое исследование;P31_Defektias;;false;true;18
Скрининговое ЭХО-кардиографическое исследование;P32_Svobodnzhidkostperikarde;;false;true;19
Скрининговое ЭХО-кардиографическое исследование;P33_Svobodnzhidkostplevralpolosti;;false;true;20
Скрининговое ЭХО-кардиографическое исследование;P34_Novoobrazov;;false;true;21
Скрининговое ЭХО-кардиографическое исследование;P35_Mitrklapnstvorki;;false;true;22
Скрининговое ЭХО-кардиографическое исследование;P36_Mitrklapnskorostkrovotoka;;false;true;23
Скрининговое ЭХО-кардиографическое исследование;P37_Mitrklapnregurgitatsiya;;false;true;24
Скрининговое ЭХО-кардиографическое исследование;P38_Trikuspklapnstvorki;;false;true;25
Скрининговое ЭХО-кардиографическое исследование;P39_Trikuspklapnskorostkrovotoka;;false;true;26
Скрининговое ЭХО-кардиографическое исследование;P40_Trikuspklapnregurgitatsiya;;false;true;27
Скрининговое ЭХО-кардиографическое исследование;P41_Aortaklapnstvorki;;false;true;28
Скрининговое ЭХО-кардиографическое исследование;P42_Aortaklapnskorostkrovotoka;;false;true;29
Скрининговое ЭХО-кардиографическое исследование;P43_Aortaklapnregurgitatsiya;;false;true;30
Скрининговое ЭХО-кардиографическое исследование;P44_Klapnlegartstvorki;;false;true;31
Скрининговое ЭХО-кардиографическое исследование;P45_Klapnlegartskorostkrovotoka;;false;true;32
Скрининговое ЭХО-кардиографическое исследование;P46_Klapnlegartregurgitatsiya;;false;true;33
Снятие гипсовой повязки - крупные породы собак;P0_Petweight;;true;false;1
Снятие гипсовой повязки - мелкие породы собак и кошки;P0_Petweight;;true;false;1
Содержание животных - кошки и собаки (до 5 кг);P0_Petweight;;true;false;1
Содержание животных - кошки и собаки (свыше 5 кг);P0_Petweight;;true;false;1
Скрининговое ЭХО-кардиографическое исследование;P47_Serviceresult;;false;true;34
Ультразвуковое исследование;P0_Organsystem;;true;false;1
Считывание номера микрочипа (сканирование);P0_Petchpidentificationcode;;false;true;1
Ультразвуковое исследование;P14_Matkadiametrtela;УЗИ репр. Самки;false;true;1
Ультразвуковое исследование;P14_Odrazmerperednegootrezka;15. УЗИ глаза.pdf.;false;true;1
Ультразвуковое исследование;P14_Pechenraspoloshenie;17. УЗИ печ. и т.д.pdf;false;true;1
Ультразвуковое исследование;P14_Predstzhelezarazmer;19. УЗИ репр. Самца.pdf;false;true;1
Ультразвуковое исследование;P14_Prvpochraspoloshenie;16. УЗИ мочевыдел.pdf . ;false;true;1
Ультразвуковое исследование;P15_Matkatolshinatela;УЗИ репр. Самки;false;true;2
Ультразвуковое исследование;P15_Odrazmerzadnegootrezka;15. УЗИ глаза.pdf.;false;true;2
Ультразвуковое исследование;P15_Pechenkontur;17. УЗИ печ. и т.д.pdf;false;true;2
Ультразвуковое исследование;P15_Predstzhelezakontur;19. УЗИ репр. Самца.pdf;false;true;2
Ультразвуковое исследование;P15_Prvpochgranica;16. УЗИ мочевыдел.pdf . ;false;true;2
Ультразвуковое исследование;P16_Matkastrukturastenkitela;УЗИ репр. Самки;false;true;3
Ультразвуковое исследование;P16_Odstructuraperedcamer;15. УЗИ глаза.pdf.;false;true;3
Ультразвуковое исследование;P16_Pechenrazmer;17. УЗИ печ. и т.д.pdf;false;true;3
Ультразвуковое исследование;P16_Predstzhelezaparenkhima;19. УЗИ репр. Самца.pdf;false;true;3
Ультразвуковое исследование;P16_Prvpochrazmer;16. УЗИ мочевыдел.pdf . ;false;true;3
Ультразвуковое исследование;P17_Matkasostoyanpolosti;УЗИ репр. Самки;false;true;4
Ультразвуковое исследование;P17_Odrazmerhrust;15. УЗИ глаза.pdf.;false;true;4
Ультразвуковое исследование;P17_Pechenekhostruktura;17. УЗИ печ. и т.д.pdf;false;true;4
Ультразвуковое исследование;P17_Predstzhelezaobyemnobrazov;19. УЗИ репр. Самца.pdf;false;true;4
Ультразвуковое исследование;P17_Prvpochkortiksloytolshina;16. УЗИ мочевыдел.pdf . ;false;true;4
Ультразвуковое исследование;P18_Matkadiametrpravroga;УЗИ репр. Самки;false;true;5
Ультразвуковое исследование;P18_Odstructurahrust;15. УЗИ глаза.pdf.;false;true;5
Ультразвуковое исследование;P18_Pechenekhogennost;17. УЗИ печ. и т.д.pdf;false;true;5
Ультразвуковое исследование;P18_Pravsemrazmer;19. УЗИ репр. Самца.pdf;false;true;5
Ультразвуковое исследование;P18_Prvpochkortiksloyekhogennost;16. УЗИ мочевыдел.pdf . ;false;true;5
Ультразвуковое исследование;P19_Matkatolshinapravroga;УЗИ репр. Самки;false;true;6
Ультразвуковое исследование;P19_Odcapsulahrust;15. УЗИ глаза.pdf.;false;true;6
Ультразвуковое исследование;P19_Pechenperifsosudrisunok;17. УЗИ печ. и т.д.pdf;false;true;6
Ультразвуковое исследование;P19_Pravsemkontur;19. УЗИ репр. Самца.pdf;false;true;6
Ультразвуковое исследование;P19_Prvpochkortiksloyekhostruktura;16. УЗИ мочевыдел.pdf . ;false;true;6
Ультразвуковое исследование;P20_Matkastrukturastenkipravroga;УЗИ репр. Самки;false;true;7
Ультразвуковое исследование;P20_Odstructurasteklotelo;15. УЗИ глаза.pdf.;false;true;7
Ультразвуковое исследование;P20_Pechenportae;17. УЗИ печ. и т.д.pdf;false;true;7
Ультразвуковое исследование;P20_Pravsemparenkhima;19. УЗИ репр. Самца.pdf;false;true;7
Ультразвуковое исследование;P20_Prvpochmedullyarsloytolshchina;16. УЗИ мочевыдел.pdf . ;false;true;7
Ультразвуковое исследование;P21_Matkasoderzhimpolostipravroga;УЗИ репр. Самки;false;true;8
Ультразвуковое исследование;P21_Oddiametrzrachka;15. УЗИ глаза.pdf.;false;true;8
Ультразвуковое исследование;P21_Pechenvhepatica;17. УЗИ печ. и т.д.pdf;false;true;8
Ультразвуковое исследование;P21_Pravsemobyemnobrazov;19. УЗИ репр. Самца.pdf;false;true;8
Ультразвуковое исследование;P21_Prvpochmedullyarsloyekhogennost;16. УЗИ мочевыдел.pdf . ;false;true;8
Ультразвуковое исследование;P22_Matkadiametrlevroga;УЗИ репр. Самки;false;true;9
Ультразвуковое исследование;P22_Odcontur;15. УЗИ глаза.pdf.;false;true;9
Ультразвуковое исследование;P22_Pechenahepatica;17. УЗИ печ. и т.д.pdf;false;true;9
Ультразвуковое исследование;P22_Pridatokpravsemgolovka;19. УЗИ репр. Самца.pdf;false;true;9
Ультразвуковое исследование;P22_Prvpochmedullyarsloyekhostruktura;16. УЗИ мочевыдел.pdf . ;false;true;9
Ультразвуковое исследование;P23_Matkatolshinalevroga;УЗИ репр. Самки;false;true;10
Ультразвуковое исследование;P23_Odstructura;15. УЗИ глаза.pdf.;false;true;10
Ультразвуковое исследование;P23_Pechenobyemnobrazov;17. УЗИ печ. и т.д.pdf;false;true;10
Ультразвуковое исследование;P23_Pridatokpravsemtelo;19. УЗИ репр. Самца.pdf;false;true;10
Ультразвуковое исследование;P23_Prvpochmedullyarsloykortmeddiffer;16. УЗИ мочевыдел.pdf . ;false;true;10
Ультразвуковое исследование;P24_Matkastrukturastenkilevroga;УЗИ репр. Самки;false;true;11
Ультразвуковое исследование;P24_Odstructuradiskazritnerva;15. УЗИ глаза.pdf.;false;true;11
Ультразвуковое исследование;P24_Pridatokpravsemobyemnobrazov;19. УЗИ репр. Самца.pdf;false;true;11
Ультразвуковое исследование;P24_Prvpochpiyelicheskiyindeks;16. УЗИ мочевыдел.pdf . ;false;true;11
Ультразвуковое исследование;P24_Zhelchpuzyrstepennapolneniya;17. УЗИ печ. и т.д.pdf;false;true;11
Ультразвуковое исследование;P25_Levsemrazmer;19. УЗИ репр. Самца.pdf;false;true;12
Ультразвуковое исследование;P25_Matkasoderzhimpolostilevroga;УЗИ репр. Самки;false;true;12
Ультразвуковое исследование;P25_Odstructuraretrobulyar;15. УЗИ глаза.pdf.;false;true;12
Ультразвуковое исследование;P25_Prvpochpochsinusekhogennost;16. УЗИ мочевыдел.pdf . ;false;true;12
Ультразвуковое исследование;P25_Zhelchpuzyrformazhelchpuzyrya;17. УЗИ печ. и т.д.pdf;false;true;12
Ультразвуковое исследование;P26_Levsemkontur;19. УЗИ репр. Самца.pdf;false;true;13
Ультразвуковое исследование;P26_Osrazmerperednegootrezka;15. УЗИ глаза.pdf.;false;true;13
Ультразвуковое исследование;P26_Pravyaichnikrazmer;УЗИ репр. Самки;false;true;13
Ультразвуковое исследование;P26_Prvpochpochsinuschetkostdifferents;16. УЗИ мочевыдел.pdf . ;false;true;13
Ультразвуковое исследование;P26_Zhelchpuzyrtolshchinastenki;17. УЗИ печ. и т.д.pdf;false;true;13
Ультразвуковое исследование;P27_Levsemparenkhima;19. УЗИ репр. Самца.pdf;false;true;14
Ультразвуковое исследование;P27_Osrazmerzadnegootrezka;15. УЗИ глаза.pdf.;false;true;14
Ультразвуковое исследование;P27_Pravyaichnikkontur;УЗИ репр. Самки;false;true;14
Ультразвуковое исследование;P27_Prvpochpochsinuspolostlokhanki;16. УЗИ мочевыдел.pdf . ;false;true;14
Ультразвуковое исследование;P27_Zhelchpuzyrdeformatsiya;17. УЗИ печ. и т.д.pdf;false;true;14
Ультразвуковое исследование;P28_Levsemobyemnobrazov;19. УЗИ репр. Самца.pdf;false;true;15
Ультразвуковое исследование;P28_Osstructuraperedcamer;15. УЗИ глаза.pdf.;false;true;15
Ультразвуковое исследование;P28_Pravyaichniknovoobrazov;УЗИ репр. Самки;false;true;15
Ультразвуковое исследование;P28_Prvpochpochsinusstepenlokhanki;16. УЗИ мочевыдел.pdf . ;false;true;15
Ультразвуковое исследование;P28_Zhelchpuzyrstrukturazhelchi;17. УЗИ печ. и т.д.pdf;false;true;15
Ультразвуковое исследование;P29_Levyaichnikrazmer;УЗИ репр. Самки;false;true;16
Ультразвуковое исследование;P29_Osrazmerhrust;15. УЗИ глаза.pdf.;false;true;16
Ультразвуковое исследование;P29_Pridatoklevsemgolovka;19. УЗИ репр. Самца.pdf;false;true;16
Ультразвуковое исследование;P29_Prvpochsosudyparenkhimy;16. УЗИ мочевыдел.pdf . ;false;true;16
Ультразвуковое исследование;P29_Zhelchpuzyrpuzyrprotok;17. УЗИ печ. и т.д.pdf;false;true;16
Ультразвуковое исследование;P30_Levyaichnikkontur;УЗИ репр. Самки;false;true;17
Ультразвуковое исследование;P30_Osstructurahrust;15. УЗИ глаза.pdf.;false;true;17
Ультразвуковое исследование;P30_Pridatoklevsemtelo;19. УЗИ репр. Самца.pdf;false;true;17
Ультразвуковое исследование;P30_PrvpochIndeksrezistivnpochechnart;16. УЗИ мочевыдел.pdf . ;false;true;17
Ультразвуковое исследование;P30_Zhelchpuzyrobshzhelchprotok;17. УЗИ печ. и т.д.pdf;false;true;17
Ультразвуковое исследование;P31_Levyaichniknovoobrazov;УЗИ репр. Самки;false;true;18
Ультразвуковое исследование;P31_Oscapsulahrust;15. УЗИ глаза.pdf.;false;true;18
Ультразвуковое исследование;P31_Pridatoklevsemobyemnobrazov;19. УЗИ репр. Самца.pdf;false;true;18
Ультразвуковое исследование;P31_PrvpochIndeksrezistivnmezhdolevoyart;16. УЗИ мочевыдел.pdf . ;false;true;18
Ультразвуковое исследование;P31_Zhelchpuzyrpechenochnprotok;17. УЗИ печ. и т.д.pdf;false;true;18
Ультразвуковое исследование;P32_Abdomultmserviceresult;19. УЗИ репр. Самца.pdf;false;true;19
Ультразвуковое исследование;P32_Osstructurasteklotelo;15. УЗИ глаза.pdf.;false;true;19
Ультразвуковое исследование;P32_Prvpochkonkrementy;16. УЗИ мочевыдел.pdf . ;false;true;19
Ультразвуковое исследование;P32_Serviceresult;УЗИ репр. Самки;false;true;19
Ультразвуковое исследование;P32_Zhelchpuzyrobyemnobrazov;17. УЗИ печ. и т.д.pdf;false;true;19
Ультразвуковое исследование;P33_Osdiametrzrachka;15. УЗИ глаза.pdf.;false;true;20
Ультразвуковое исследование;P33_Prvpochobyemnobrazov;16. УЗИ мочевыдел.pdf . ;false;true;20
Ультразвуковое исследование;P33_Selezenkaraspoloshenie;17. УЗИ печ. и т.д.pdf;false;true;20
Ультразвуковое исследование;P34_Levpochraspoloshenie;16. УЗИ мочевыдел.pdf . ;false;true;21
Ультразвуковое исследование;P34_Oscontur;15. УЗИ глаза.pdf.;false;true;21
Ультразвуковое исследование;P34_Selezenkakontur;17. УЗИ печ. и т.д.pdf;false;true;21
Ультразвуковое исследование;P35_Levpochgranica;16. УЗИ мочевыдел.pdf . ;false;true;22
Ультразвуковое исследование;P35_Osstructura;15. УЗИ глаза.pdf.;false;true;22
Ультразвуковое исследование;P35_Selezenkarazmer;17. УЗИ печ. и т.д.pdf;false;true;22
Ультразвуковое исследование;P36_Levpochrazmer;16. УЗИ мочевыдел.pdf . ;false;true;23
Ультразвуковое исследование;P36_Osstructuradiskazritnerva;15. УЗИ глаза.pdf.;false;true;23
Ультразвуковое исследование;P36_Selezenkaekhostruktura;17. УЗИ печ. и т.д.pdf;false;true;23
Ультразвуковое исследование;P37_Levpochkortiksloytolshina;16. УЗИ мочевыдел.pdf . ;false;true;24
Ультразвуковое исследование;P37_Osstructuraretrobulyar;15. УЗИ глаза.pdf.;false;true;24
Ультразвуковое исследование;P37_Selezenkaekhogennost;17. УЗИ печ. и т.д.pdf;false;true;24
Ультразвуковое исследование;P38_Levpochkortiksloyekhogennost;16. УЗИ мочевыдел.pdf . ;false;true;25
Ультразвуковое исследование;P38_Selezenkasosudrisunok;17. УЗИ печ. и т.д.pdf;false;true;25
Ультразвуковое исследование;P38_Serviceresult;15. УЗИ глаза.pdf.;false;true;25
Ультразвуковое исследование;P39_Levpochkortiksloyekhostruktura;16. УЗИ мочевыдел.pdf . ;false;true;26
Ультразвуковое исследование;P39_Selezenkaobyemnobrazov;17. УЗИ печ. и т.д.pdf;false;true;26
Ультразвуковое исследование;P40_Levpochmedullyarsloytolshchina;16. УЗИ мочевыдел.pdf . ;false;true;27
Ультразвуковое исследование;P40_Podzhelzhelezaraspoloshenie;17. УЗИ печ. и т.д.pdf;false;true;27
Ультразвуковое исследование;P41_Levpochmedullyarsloyekhogennost;16. УЗИ мочевыдел.pdf . ;false;true;28
Ультразвуковое исследование;P41_Podzhelzhelezakontur;17. УЗИ печ. и т.д.pdf;false;true;28
Ультразвуковое исследование;P42_Levpochmedullyarsloyekhostruktura;16. УЗИ мочевыдел.pdf . ;false;true;29
Ультразвуковое исследование;P42_Podzhelzhelezarazmer;17. УЗИ печ. и т.д.pdf;false;true;29
Ультразвуковое исследование;P43_Levpochmedullyarsloykortmeddiffer;16. УЗИ мочевыдел.pdf . ;false;true;30
Ультразвуковое исследование;P43_Podzhelzhelezaekhostruktura;17. УЗИ печ. и т.д.pdf;false;true;30
Ультразвуковое исследование;P44_Levpochpiyelicheskiyindeks;16. УЗИ мочевыдел.pdf . ;false;true;31
Ультразвуковое исследование;P44_Podzhelzhelezaekhogennost;17. УЗИ печ. и т.д.pdf;false;true;31
Ультразвуковое исследование;P45_Levpochpochsinusekhogennost;16. УЗИ мочевыдел.pdf . ;false;true;32
Ультразвуковое исследование;P45_Podzhelzhelezaobyemnobrazov;17. УЗИ печ. и т.д.pdf;false;true;32
Ультразвуковое исследование;P46_Levpochpochsinuschetkostdifferents;16. УЗИ мочевыдел.pdf . ;false;true;33
Ультразвуковое исследование;P46_Zheludkishechntrakt;17. УЗИ печ. и т.д.pdf;false;true;33
Ультразвуковое исследование;P47_Levpochpochsinuspolostlokhanki;16. УЗИ мочевыдел.pdf . ;false;true;34
Ультразвуковое исследование;P47_Svobodnzhidkost;17. УЗИ печ. и т.д.pdf;false;true;34
Ультразвуковое исследование;P48_Levpochpochsinusstenkilokhanki;16. УЗИ мочевыдел.pdf . ;false;true;35
Ультразвуковое исследование;P48_Serviceresult;17. УЗИ печ. и т.д.pdf;false;true;35
Ультразвуковое исследование;P49_Levpochsosudyparenkhimy;16. УЗИ мочевыдел.pdf . ;false;true;36
Ультразвуковое исследование;P50_LevpochIndeksrezistivnpochechnart;16. УЗИ мочевыдел.pdf . ;false;true;37
Ультразвуковое исследование;P51_LevpochIndeksrezistivnmezhdolevoyart;16. УЗИ мочевыдел.pdf . ;false;true;38
Ультразвуковое исследование;P52_Levpochkonkrementy;16. УЗИ мочевыдел.pdf . ;false;true;39
Ультразвуковое исследование;P53_Levpochobyemnobrazov;16. УЗИ мочевыдел.pdf . ;false;true;40
Ультразвуковое исследование;P54_Mochpuzstepnapoln;16. УЗИ мочевыдел.pdf . ;false;true;41
Ультразвуковое исследование;P55_Mochpuztolshchinastenki;16. УЗИ мочевыдел.pdf . ;false;true;42
Ультразвуковое исследование;P56_Mochpuzdeformatsiya;16. УЗИ мочевыдел.pdf . ;false;true;43
Ультразвуковое исследование;P57_Mochpuzuretra;16. УЗИ мочевыдел.pdf . ;false;true;44
Ультразвуковое исследование;P58_MochpuzObyemnobrazov;16. УЗИ мочевыдел.pdf . ;false;true;45
Ультразвуковой скрининг органов брюшной полости;P0_Organsystem;;true;false;1
Ультразвуковое исследование;P59_Serviceresult;16. УЗИ мочевыдел.pdf . ;false;true;46
Ультразвуковой скрининг органов брюшной полости;P14_Matkadiametrtela;УЗИ репр. Самки;false;true;2
Ультразвуковой скрининг органов брюшной полости;P14_Pechenraspoloshenie;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;2
Ультразвуковой скрининг органов брюшной полости;P14_Predstzhelezarazmer;19. УЗИ репр. Самца.pdf . Скрининг;false;true;2
Ультразвуковой скрининг органов брюшной полости;P14_Prvpochraspoloshenie;16. УЗИ мочевыдел.pdf . Скрининг;false;true;2
Ультразвуковой скрининг органов брюшной полости;P15_Matkatolshinatela;УЗИ репр. Самки;false;true;3
Ультразвуковой скрининг органов брюшной полости;P15_Pechenkontur;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;3
Ультразвуковой скрининг органов брюшной полости;P15_Predstzhelezakontur;19. УЗИ репр. Самца.pdf . Скрининг;false;true;3
Ультразвуковой скрининг органов брюшной полости;P15_Prvpochgranica;16. УЗИ мочевыдел.pdf . Скрининг;false;true;3
Ультразвуковой скрининг органов брюшной полости;P16_Matkastrukturastenkitela;УЗИ репр. Самки;false;true;4
Ультразвуковой скрининг органов брюшной полости;P16_Pechenrazmer;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;4
Ультразвуковой скрининг органов брюшной полости;P16_Predstzhelezaparenkhima;19. УЗИ репр. Самца.pdf . Скрининг;false;true;4
Ультразвуковой скрининг органов брюшной полости;P16_Prvpochrazmer;16. УЗИ мочевыдел.pdf . Скрининг;false;true;4
Ультразвуковой скрининг органов брюшной полости;P17_Matkasostoyanpolosti;УЗИ репр. Самки;false;true;5
Ультразвуковой скрининг органов брюшной полости;P17_Pechenekhostruktura;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;5
Ультразвуковой скрининг органов брюшной полости;P17_Predstzhelezaobyemnobrazov;19. УЗИ репр. Самца.pdf . Скрининг;false;true;5
Ультразвуковой скрининг органов брюшной полости;P17_Prvpochkortiksloytolshina;16. УЗИ мочевыдел.pdf . Скрининг;false;true;5
Ультразвуковой скрининг органов брюшной полости;P18_Matkadiametrpravroga;УЗИ репр. Самки;false;true;6
Ультразвуковой скрининг органов брюшной полости;P18_Pechenekhogennost;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;6
Ультразвуковой скрининг органов брюшной полости;P18_Pravsemrazmer;19. УЗИ репр. Самца.pdf . Скрининг;false;true;6
Ультразвуковой скрининг органов брюшной полости;P18_Prvpochkortiksloyekhogennost;16. УЗИ мочевыдел.pdf . Скрининг;false;true;6
Ультразвуковой скрининг органов брюшной полости;P19_Matkatolshinapravroga;УЗИ репр. Самки;false;true;7
Ультразвуковой скрининг органов брюшной полости;P19_Pechenperifsosudrisunok;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;7
Ультразвуковой скрининг органов брюшной полости;P19_Pravsemkontur;19. УЗИ репр. Самца.pdf . Скрининг;false;true;7
Ультразвуковой скрининг органов брюшной полости;P19_Prvpochkortiksloyekhostruktura;16. УЗИ мочевыдел.pdf . Скрининг;false;true;7
Ультразвуковой скрининг органов брюшной полости;P20_Matkastrukturastenkipravroga;УЗИ репр. Самки;false;true;8
Ультразвуковой скрининг органов брюшной полости;P20_Pechenportae;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;8
Ультразвуковой скрининг органов брюшной полости;P20_Pravsemparenkhima;19. УЗИ репр. Самца.pdf . Скрининг;false;true;8
Ультразвуковой скрининг органов брюшной полости;P20_Prvpochmedullyarsloytolshchina;16. УЗИ мочевыдел.pdf . Скрининг;false;true;8
Ультразвуковой скрининг органов брюшной полости;P21_Matkasoderzhimpolostipravroga;УЗИ репр. Самки;false;true;9
Ультразвуковой скрининг органов брюшной полости;P21_Pechenvhepatica;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;9
Ультразвуковой скрининг органов брюшной полости;P21_Pravsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Скрининг;false;true;9
Ультразвуковой скрининг органов брюшной полости;P21_Prvpochmedullyarsloyekhogennost;16. УЗИ мочевыдел.pdf . Скрининг;false;true;9
Ультразвуковой скрининг органов брюшной полости;P22_Matkadiametrlevroga;УЗИ репр. Самки;false;true;10
Ультразвуковой скрининг органов брюшной полости;P22_Pechenahepatica;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;10
Ультразвуковой скрининг органов брюшной полости;P22_Pridatokpravsemgolovka;19. УЗИ репр. Самца.pdf . Скрининг;false;true;10
Ультразвуковой скрининг органов брюшной полости;P22_Prvpochmedullyarsloyekhostruktura;16. УЗИ мочевыдел.pdf . Скрининг;false;true;10
Ультразвуковой скрининг органов брюшной полости;P23_Matkatolshinalevroga;УЗИ репр. Самки;false;true;11
Ультразвуковой скрининг органов брюшной полости;P23_Pechenobyemnobrazov;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;11
Ультразвуковой скрининг органов брюшной полости;P23_Pridatokpravsemtelo;19. УЗИ репр. Самца.pdf . Скрининг;false;true;11
Ультразвуковой скрининг органов брюшной полости;P23_Prvpochmedullyarsloykortmeddiffer;16. УЗИ мочевыдел.pdf . Скрининг;false;true;11
Ультразвуковой скрининг органов брюшной полости;P24_Matkastrukturastenkilevroga;УЗИ репр. Самки;false;true;12
Ультразвуковой скрининг органов брюшной полости;P24_Pridatokpravsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Скрининг;false;true;12
Ультразвуковой скрининг органов брюшной полости;P24_Prvpochpiyelicheskiyindeks;16. УЗИ мочевыдел.pdf . Скрининг;false;true;12
Ультразвуковой скрининг органов брюшной полости;P24_Zhelchpuzyrstepennapolneniya;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;12
Ультразвуковой скрининг органов брюшной полости;P25_Levsemrazmer;19. УЗИ репр. Самца.pdf . Скрининг;false;true;13
Ультразвуковой скрининг органов брюшной полости;P25_Matkasoderzhimpolostilevroga;УЗИ репр. Самки;false;true;13
Ультразвуковой скрининг органов брюшной полости;P25_Prvpochpochsinusekhogennost;16. УЗИ мочевыдел.pdf . Скрининг;false;true;13
Ультразвуковой скрининг органов брюшной полости;P25_Zhelchpuzyrformazhelchpuzyrya;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;13
Ультразвуковой скрининг органов брюшной полости;P26_Levsemkontur;19. УЗИ репр. Самца.pdf . Скрининг;false;true;14
Ультразвуковой скрининг органов брюшной полости;P26_Pravyaichnikrazmer;УЗИ репр. Самки;false;true;14
Ультразвуковой скрининг органов брюшной полости;P26_Prvpochpochsinuschetkostdifferents;16. УЗИ мочевыдел.pdf . Скрининг;false;true;14
Ультразвуковой скрининг органов брюшной полости;P26_Zhelchpuzyrtolshchinastenki;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;14
Ультразвуковой скрининг органов брюшной полости;P27_Levsemparenkhima;19. УЗИ репр. Самца.pdf . Скрининг;false;true;15
Ультразвуковой скрининг органов брюшной полости;P27_Pravyaichnikkontur;УЗИ репр. Самки;false;true;15
Ультразвуковой скрининг органов брюшной полости;P27_Prvpochpochsinuspolostlokhanki;16. УЗИ мочевыдел.pdf . Скрининг;false;true;15
Ультразвуковой скрининг органов брюшной полости;P27_Zhelchpuzyrdeformatsiya;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;15
Ультразвуковой скрининг органов брюшной полости;P28_Levsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Скрининг;false;true;16
Ультразвуковой скрининг органов брюшной полости;P28_Pravyaichniknovoobrazov;УЗИ репр. Самки;false;true;16
Ультразвуковой скрининг органов брюшной полости;P28_Prvpochpochsinusstepenlokhanki;16. УЗИ мочевыдел.pdf . Скрининг;false;true;16
Ультразвуковой скрининг органов брюшной полости;P28_Zhelchpuzyrstrukturazhelchi;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;16
Ультразвуковой скрининг органов брюшной полости;P29_Levyaichnikrazmer;УЗИ репр. Самки;false;true;17
Ультразвуковой скрининг органов брюшной полости;P29_Pridatoklevsemgolovka;19. УЗИ репр. Самца.pdf . Скрининг;false;true;17
Ультразвуковой скрининг органов брюшной полости;P29_Prvpochsosudyparenkhimy;16. УЗИ мочевыдел.pdf . Скрининг;false;true;17
Ультразвуковой скрининг органов брюшной полости;P29_Zhelchpuzyrpuzyrprotok;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;17
Ультразвуковой скрининг органов брюшной полости;P30_Levyaichnikkontur;УЗИ репр. Самки;false;true;18
Ультразвуковой скрининг органов брюшной полости;P30_Pridatoklevsemtelo;19. УЗИ репр. Самца.pdf . Скрининг;false;true;18
Ультразвуковой скрининг органов брюшной полости;P30_PrvpochIndeksrezistivnpochechnart;16. УЗИ мочевыдел.pdf . Скрининг;false;true;18
Ультразвуковой скрининг органов брюшной полости;P30_Zhelchpuzyrobshzhelchprotok;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;18
Ультразвуковой скрининг органов брюшной полости;P31_Levyaichniknovoobrazov;УЗИ репр. Самки;false;true;19
Ультразвуковой скрининг органов брюшной полости;P31_Pridatoklevsemobyemnobrazov;19. УЗИ репр. Самца.pdf . Скрининг;false;true;19
Ультразвуковой скрининг органов брюшной полости;P31_PrvpochIndeksrezistivnmezhdolevoyart;16. УЗИ мочевыдел.pdf . Скрининг;false;true;19
Ультразвуковой скрининг органов брюшной полости;P31_Zhelchpuzyrpechenochnprotok;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;19
Ультразвуковой скрининг органов брюшной полости;P32_Abdomultmserviceresult;19. УЗИ репр. Самца.pdf . Скрининг;false;true;20
Ультразвуковой скрининг органов брюшной полости;P32_Prvpochkonkrementy;16. УЗИ мочевыдел.pdf . Скрининг;false;true;20
Ультразвуковой скрининг органов брюшной полости;P32_Serviceresult;УЗИ репр. Самки;false;true;20
Ультразвуковой скрининг органов брюшной полости;P32_Zhelchpuzyrobyemnobrazov;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;20
Ультразвуковой скрининг органов брюшной полости;P33_Prvpochobyemnobrazov;16. УЗИ мочевыдел.pdf . Скрининг;false;true;21
Ультразвуковой скрининг органов брюшной полости;P33_Selezenkaraspoloshenie;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;21
Ультразвуковой скрининг органов брюшной полости;P34_Levpochraspoloshenie;16. УЗИ мочевыдел.pdf . Скрининг;false;true;22
Ультразвуковой скрининг органов брюшной полости;P34_Selezenkakontur;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;22
Ультразвуковой скрининг органов брюшной полости;P35_Levpochgranica;16. УЗИ мочевыдел.pdf . Скрининг;false;true;23
Ультразвуковой скрининг органов брюшной полости;P35_Selezenkarazmer;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;23
Ультразвуковой скрининг органов брюшной полости;P36_Levpochrazmer;16. УЗИ мочевыдел.pdf . Скрининг;false;true;24
Ультразвуковой скрининг органов брюшной полости;P36_Selezenkaekhostruktura;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;24
Ультразвуковой скрининг органов брюшной полости;P37_Levpochkortiksloytolshina;16. УЗИ мочевыдел.pdf . Скрининг;false;true;25
Ультразвуковой скрининг органов брюшной полости;P37_Selezenkaekhogennost;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;25
Ультразвуковой скрининг органов брюшной полости;P38_Levpochkortiksloyekhogennost;16. УЗИ мочевыдел.pdf . Скрининг;false;true;26
Ультразвуковой скрининг органов брюшной полости;P38_Selezenkasosudrisunok;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;26
Ультразвуковой скрининг органов брюшной полости;P39_Levpochkortiksloyekhostruktura;16. УЗИ мочевыдел.pdf . Скрининг;false;true;27
Ультразвуковой скрининг органов брюшной полости;P39_Selezenkaobyemnobrazov;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;27
Ультразвуковой скрининг органов брюшной полости;P40_Levpochmedullyarsloytolshchina;16. УЗИ мочевыдел.pdf . Скрининг;false;true;28
Ультразвуковой скрининг органов брюшной полости;P40_Podzhelzhelezaraspoloshenie;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;28
Ультразвуковой скрининг органов брюшной полости;P41_Levpochmedullyarsloyekhogennost;16. УЗИ мочевыдел.pdf . Скрининг;false;true;29
Ультразвуковой скрининг органов брюшной полости;P41_Podzhelzhelezakontur;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;29
Ультразвуковой скрининг органов брюшной полости;P42_Levpochmedullyarsloyekhostruktura;16. УЗИ мочевыдел.pdf . Скрининг;false;true;30
Ультразвуковой скрининг органов брюшной полости;P42_Podzhelzhelezarazmer;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;30
Ультразвуковой скрининг органов брюшной полости;P43_Levpochmedullyarsloykortmeddiffer;16. УЗИ мочевыдел.pdf . Скрининг;false;true;31
Ультразвуковой скрининг органов брюшной полости;P43_Podzhelzhelezaekhostruktura;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;31
Ультразвуковой скрининг органов брюшной полости;P44_Levpochpiyelicheskiyindeks;16. УЗИ мочевыдел.pdf . Скрининг;false;true;32
Ультразвуковой скрининг органов брюшной полости;P44_Podzhelzhelezaekhogennost;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;32
Ультразвуковой скрининг органов брюшной полости;P45_Levpochpochsinusekhogennost;16. УЗИ мочевыдел.pdf . Скрининг;false;true;33
Ультразвуковой скрининг органов брюшной полости;P45_Podzhelzhelezaobyemnobrazov;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;33
Ультразвуковой скрининг органов брюшной полости;P46_Levpochpochsinuschetkostdifferents;16. УЗИ мочевыдел.pdf . Скрининг;false;true;34
Ультразвуковой скрининг органов брюшной полости;P46_Zheludkishechntrakt;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;34
Ультразвуковой скрининг органов брюшной полости;P47_Levpochpochsinuspolostlokhanki;16. УЗИ мочевыдел.pdf . Скрининг;false;true;35
Ультразвуковой скрининг органов брюшной полости;P47_Svobodnzhidkost;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;35
Ультразвуковой скрининг органов брюшной полости;P48_Levpochpochsinusstenkilokhanki;16. УЗИ мочевыдел.pdf . Скрининг;false;true;36
Ультразвуковой скрининг органов брюшной полости;P48_Serviceresult;17. УЗИ печ. и т.д.pdf . Скрининг;false;true;36
Ультразвуковой скрининг органов брюшной полости;P49_Levpochsosudyparenkhimy;16. УЗИ мочевыдел.pdf . Скрининг;false;true;37
Ультразвуковой скрининг органов брюшной полости;P50_LevpochIndeksrezistivnpochechnart;16. УЗИ мочевыдел.pdf . Скрининг;false;true;38
Ультразвуковой скрининг органов брюшной полости;P51_LevpochIndeksrezistivnmezhdolevoyart;16. УЗИ мочевыдел.pdf . Скрининг;false;true;39
Ультразвуковой скрининг органов брюшной полости;P52_Levpochkonkrementy;16. УЗИ мочевыдел.pdf . Скрининг;false;true;40
Ультразвуковой скрининг органов брюшной полости;P53_Levpochobyemnobrazov;16. УЗИ мочевыдел.pdf . Скрининг;false;true;41
Ультразвуковой скрининг органов брюшной полости;P54_Mochpuzstepnapoln;16. УЗИ мочевыдел.pdf . Скрининг;false;true;42
Ультразвуковой скрининг органов брюшной полости;P55_Mochpuztolshchinastenki;16. УЗИ мочевыдел.pdf . Скрининг;false;true;43
Ультразвуковой скрининг органов брюшной полости;P56_Mochpuzdeformatsiya;16. УЗИ мочевыдел.pdf . Скрининг;false;true;44
Ультразвуковой скрининг органов брюшной полости;P57_Mochpuzuretra;16. УЗИ мочевыдел.pdf . Скрининг;false;true;45
Ультразвуковой скрининг органов брюшной полости;P58_MochpuzObyemnobrazov;16. УЗИ мочевыдел.pdf . Скрининг;false;true;46
Утилизация (сжигание) биологических отходов без транспортировки;P0_Petweight;;true;false;1
Утилизация (сжигание) биологических отходов с транспортировкой;P0_Petweight;Возможно для данной услуги стоит добавить ещё адрес. ;true;false;1
Цитологические исследования;P0_Cytologicsanalysisnum;;true;false;1
Ультразвуковой скрининг органов брюшной полости;P59_Serviceresult;16. УЗИ мочевыдел.pdf . Скрининг;false;true;47
Цитологические исследования;P14_Bazalvalue;;false;true;2
Цитологические исследования;P15_Parabazalvalue;;false;true;3
Цитологические исследования;P16_Promezhutvalue;;false;true;4
Цитологические исследования;P17_Poverkhvalue;;false;true;5
Цитологические исследования;P18_Leukocytesvalue;;false;true;6
Цитологические исследования;P19_Erythrocytesvalue;;false;true;7
Цитологические исследования;P20_Bacteriavalue;;false;true;8
Цитологические исследования;P21_Fazatsiklavalue;;false;true;9
Цитологические исследования;P22_Recomendvalue;;false;true;10
Цитологические исследования;P23_Cytologicscreeninganswerdate;"Дата ответа. См. вкладку ""Param""";false;false;
Экспресс-диагностика глюкозы (с использованием глюкометра);P0_Expdiagnosglucosevalue;;false;true;1
Электрокардиография;P12_Pc;20. ЭКГ.pdf;false;true;1
Электрокардиография;P13_Pmv;20. ЭКГ.pdf;false;true;2
Электрокардиография;P14_Р1;20. ЭКГ.pdf;false;true;3
Электрокардиография;P15_P2;20. ЭКГ.pdf;false;true;4
Электрокардиография;P16_P3;20. ЭКГ.pdf;false;true;5
Электрокардиография;P17_Pq;20. ЭКГ.pdf;false;true;6
Электрокардиография;P18_Qrs;20. ЭКГ.pdf;false;true;7
Электрокардиография;P19_Qrsdesc;20. ЭКГ.pdf;false;false;8
Электрокардиография;P20_Rmv;20. ЭКГ.pdf;false;true;9
Электрокардиография;P21_Rdesc;20. ЭКГ.pdf;false;false;10
Электрокардиография;P22_Tmv;20. ЭКГ.pdf;false;true;11
Электрокардиография;P23_Tdesc;20. ЭКГ.pdf;false;false;12
Электрокардиография;P24_St;20. ЭКГ.pdf;false;true;13
Электрокардиография;P25_Qt;20. ЭКГ.pdf;false;true;14
Электрокардиография;P26_Eos;20. ЭКГ.pdf;false;true;15
Электрокардиография;P27_Chss;20. ЭКГ.pdf;false;true;16
Электрокардиография;P28_Ritm;20. ЭКГ.pdf;false;true;17
Электрокардиография;P29_Ekstrasistoly;20. ЭКГ.pdf;false;true;18
Электрокардиография;P30_Serviceresult;20. ЭКГ.pdf;false;true;19
Электронное мечение животного (чипирование со сканированием);P0_Petchpidentificationcode;;false;true;1
Компьютерная томография без введения контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_Investigationarea;;true;false;1
Компьютерная томография без введения контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_Investigationarea;;true;false;1
Компьютерная томография с введением контрастного вещества - голова, отделы конечностей (сустав, регион конечности);P0_Investigationarea;;true;false;1
Компьютерная томография с введением контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_Investigationarea;;true;false;1
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - голова, отделы конечностей (сустав, регион конечности);P0_Investigationarea;;true;false;1
Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - шейный, грудной, поясничный, крестцово-тазовый отделы;P0_Investigationarea;;true;false;1
CSV;


        $tableName = 'gov_services_params';

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $service_name = $item[0];
            $tech_name = $item[1];
            $req_in = ($item[3] == '') ? false : ($item[3] === 'true' ? true : false);
            $req_out = ($item[4] == '') ? false : ($item[4] === 'true' ? true : false);
            $sort_by = empty($item[5]) ? 0 : (int)$item[5];

            $param = $this->findParamByTechName($tech_name);
            if (empty($param)) {
                Console::output(Console::ansiFormat('Param not found [' . $tech_name  . ']', [Console::FG_RED]));
                continue;
            }

            $gov_service = $this->findRecord('gov_services', ['name' => $service_name]);
            if (empty($gov_service)) {
                Console::output(Console::ansiFormat('Service not found for param [' . $tech_name  . '] - [' . $service_name . ']', [Console::FG_RED]));
                continue;
            }

            $id_service = $gov_service['id'];
            $id_param = $param['id'];

            $columns = compact('id_param', 'id_service', 'req_in', 'req_out', 'sort_by');

            \Yii::$app->db->createCommand()
                ->insert($tableName, $columns)
                ->execute();
        }
    }

    private function loadReports()
    {
        $csv = <<<CSV
Бланк регистрации и вакцинации животных;Бланк регистрации и вакцинации животных;R
Биохимия крови;Результат биохимического исследования крови;R
влаг цитол;Результат цитологического исследования мазка - отпечатка;R
Гельминто-копр;Гельминто- копрологическое исследование;R
Клин анализ мочи;Результат клинического анализа мочи;R
ЛД;Результат люминесцентной диагностики;R
Микроскопическое исследование;Результат микроскопического исследования;R
результат биохимического исследования кала;Результат биохимического исследования кала;R
результаты гормонального исследования крови;Результат гормональных исследований крови;R
УЗИ глаза;Ультразвуковое исследование глаза;R
УЗИ мочевыдел;Ультразвуковое исследование мочевыделительной системы;R
УЗИ печ. и т.д;Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта;R
УЗИ репр. Самки;Ультразвуковое исследование репродуктивной системы самки;R
УЗИ репр. Самца ;Ультразвуковое исследование репродуктивной системы самца;R
ЭКГ;Электрокардиографическое исследование;R
ЭХО-КГ;ЭХО-кардиографическое исследование;R
ОАК_Mythic;Результат общего клинического анализа крови;R
Журнал общих клинических исследований мочи;Журнал общих клинических исследований мочи;J
Журнал учета лабораторных исследований на паразитарные болезни животных;Журнал учета лабораторных исследований на паразитарные болезни животных;J
Журнал общих исследований фекалий;Журнал общих исследований фекалий;J
Журнал цитологических исследований;Журнал цитологических исследований;J
Журнал биохимического исследования крови;Журнал биохимического исследования крови;J
Журнал регистрации и вакцинации животных;Журнал регистрации и вакцинации животных;J
CSV;


        $tableName = 'reports';
        $tmpTable = 'reports_tmp';

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $short_name = $item[0];
            $name = $item[1];
            $report_type = $item[2];

            $record = $this->findRecord($tableName, ['name' => $name]);

            $columns = compact('name', 'report_type');

            if (empty($record)) {
                Console::output(Console::ansiFormat('Creating report [' . $name  . ']', [Console::FG_GREEN]));
                \Yii::$app->db->createCommand()
                    ->insert($tableName, $columns)
                    ->execute();
                $id = $this->db->getLastInsertID('reports_id_seq');
            } else {
                $id = $record['id'];
                foreach ($columns as $column => $value) {
                    if ($record[$column] !== $value) {
                        // надо обновить report
                        // остальные поля можно не проверять
                        Console::output(Console::ansiFormat('Updating report [' . $name  . ']', [Console::FG_YELLOW]));
                        \Yii::$app->db->createCommand()
                            ->update($tableName, $columns, ['id' => $record['id']])
                            ->execute();
                        break;
                    }
                }
            }

            \Yii::$app->db->createCommand()
                ->insert($tmpTable, [
                    'id' => $id,
                    'short_name' => $short_name,
                    'name' => $name,
                ])
                ->execute();
        }
    }

    private function linkServiceReports()
    {
        $csv = <<<CSV
Бланк регистрации и вакцинации животных;Вакцинация животных с проведением клинического осмотра, консультации, инъекции
Биохимия крови;Биохимические исследования крови - определение общего билирубина
Биохимия крови;Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы)
Биохимия крови;Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы)
Биохимия крови;Биохимические исследования крови - определение мочевины
Биохимия крови;Биохимические исследования крови - определение креатинина
Биохимия крови;Биохимические исследования крови - определение щелочной фосфатазы
Биохимия крови;Биохимические исследования крови - определение амилазы
Биохимия крови;Биохимические исследования крови - определение амилазы панкреатической
Биохимия крови;Биохимические исследования крови - определение глюкозы
Биохимия крови;Биохимические исследования крови - определение лактатдегидрогеназы
Биохимия крови;Биохимические исследования крови - определение гаммаглутамилтрансферазы
Биохимия крови;Биохимические исследования крови - определение креатинкиназы
Биохимия крови;Биохимические исследования крови - определение общего холестерина
Биохимия крови;Биохимические исследования крови - определение триглицеридов
Биохимия крови;Биохимические исследования крови - определение калия
Биохимия крови;Биохимические исследования крови - определение натрия
Биохимия крови;Биохимические исследования крови - определение фосфора неорганического
Биохимия крови;Биохимические исследования крови - определение кальция
Биохимия крови;Биохимические исследования крови - определение железа
Биохимия крови;Биохимические исследования крови - определение магния
Биохимия крови;Биохимические исследования крови - определение мочевой кислоты
Биохимия крови;Биохимические исследования крови - определение липазы
Биохимия крови;Биохимические исследования крови - определение общего белка
Биохимия крови;Биохимические исследования крови - определение белковых фракций
Биохимия крови;Биохимические исследования крови - определение гемоглобина
влаг цитол;Цитологические исследования
Гельминто-копр;Гельминтокопрологические исследования
Клин анализ мочи;Общий анализ мочи
ЛД;Люминесцентная диагностика на микроспорию с применением лампы Вуда
Микроскопическое исследование;Исследование на кровепаразитарные болезни
Микроскопическое исследование;Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты
результат биохимического исследования кала;Общий анализ кала
результаты гормонального исследования крови;Определение гормонов в сыворотке крови - кортизол
результаты гормонального исследования крови;Определение гормонов в сыворотке крови - прогестерон
результаты гормонального исследования крови;Определение гормонов в сыворотке крови - эстрадиол
результаты гормонального исследования крови;Определение гормонов в сыворотке крови - тестостерон
результаты гормонального исследования крови;Определение гормонов в сыворотке крови - тироксин
результаты гормонального исследования крови;Определение гормонов в сыворотке крови - трийодтиронин
УЗИ глаза;Ультразвуковое исследование
УЗИ глаза;Повторное ультразвуковое исследование
УЗИ мочевыдел;Ультразвуковое исследование
УЗИ мочевыдел;Повторное ультразвуковое исследование
УЗИ мочевыдел;Ультразвуковой скрининг органов брюшной полости
УЗИ печ. и т.д;Ультразвуковое исследование
УЗИ печ. и т.д;Повторное ультразвуковое исследование
УЗИ печ. и т.д;Ультразвуковой скрининг органов брюшной полости
УЗИ репр. Самки;Ультразвуковое исследование
УЗИ репр. Самки;Повторное ультразвуковое исследование
УЗИ репр. Самки;Ультразвуковой скрининг органов брюшной полости
УЗИ репр. Самца ;Ультразвуковое исследование
УЗИ репр. Самца ;Повторное ультразвуковое исследование
УЗИ репр. Самца ;Ультразвуковой скрининг органов брюшной полости
ЭКГ;Электрокардиография
ЭХО-КГ;Скрининговое ЭХО-кардиографическое исследование
ЭХО-КГ;ЭХОкардиография, допплеровское исследование кровотока внутренних органов и периферических сосудов
ОАК_Mythic;Общий клинический анализ крови - подсчет лейкоцитов
ОАК_Mythic;Общий клинический анализ крови - подсчет эритроцитов
ОАК_Mythic;Общий клинический анализ крови - определение гемоглобина
ОАК_Mythic;Общий клинический анализ крови - определение СОЭ
ОАК_Mythic;Общий клинический анализ крови - выведение лейкоцитарной формулы
Гельминто-копр;Ветеринарное освидетельствование животных для оформления ветеринарных сопроводительных документов, включающая проведение клинического осмотра и изучение ветеринарных документов (паспорта на животное, результатов лабораторных исследований и др.) - с гельминтокопрологическим исследованием
Журнал регистрации и вакцинации животных;Вакцинация животных с проведением клинического осмотра, консультации, инъекции
Журнал общих клинических исследований мочи;Общий анализ мочи
Журнал учета лабораторных исследований на паразитарные болезни животных;Исследование на кровепаразитарные болезни
Журнал биохимического исследования крови;Биохимические исследования крови - определение общего билирубина
Журнал биохимического исследования крови;Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы)
Журнал биохимического исследования крови;Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы)
Журнал биохимического исследования крови;Биохимические исследования крови - определение мочевины
Журнал биохимического исследования крови;Биохимические исследования крови - определение креатинина
Журнал биохимического исследования крови;Биохимические исследования крови - определение щелочной фосфатазы
Журнал биохимического исследования крови;Биохимические исследования крови - определение амилазы
Журнал биохимического исследования крови;Биохимические исследования крови - определение амилазы панкреатической
Журнал биохимического исследования крови;Биохимические исследования крови - определение глюкозы
Журнал биохимического исследования крови;Биохимические исследования крови - определение лактатдегидрогеназы
Журнал биохимического исследования крови;Биохимические исследования крови - определение гаммаглутамилтрансферазы
Журнал биохимического исследования крови;Биохимические исследования крови - определение креатинкиназы
Журнал биохимического исследования крови;Биохимические исследования крови - определение общего холестерина
Журнал биохимического исследования крови;Биохимические исследования крови - определение триглицеридов
Журнал биохимического исследования крови;Биохимические исследования крови - определение калия
Журнал биохимического исследования крови;Биохимические исследования крови - определение натрия
Журнал биохимического исследования крови;Биохимические исследования крови - определение фосфора неорганического
Журнал биохимического исследования крови;Биохимические исследования крови - определение кальция
Журнал биохимического исследования крови;Биохимические исследования крови - определение железа
Журнал биохимического исследования крови;Биохимические исследования крови - определение магния
Журнал биохимического исследования крови;Биохимические исследования крови - определение мочевой кислоты
Журнал биохимического исследования крови;Биохимические исследования крови - определение липазы
Журнал биохимического исследования крови;Биохимические исследования крови - определение общего белка
Журнал биохимического исследования крови;Биохимические исследования крови - определение белковых фракций
Журнал биохимического исследования крови;Биохимические исследования крови - определение гемоглобина
Журнал общих исследований фекалий;Общий анализ кала
Журнал цитологических исследований;Цитологические исследования
CSV;

        $tableName = 'gov_services_reports';
        $tmpTable = 'reports_tmp';

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $short_name = $item[0];
            $service_name = $item[1];

            $report = $this->findRecord($tmpTable, ['short_name' => $short_name]);
            if (empty($report)) {
                Console::output(Console::ansiFormat('Report not found [' . $short_name  . ']', [Console::FG_RED]));
                continue;
            }

            $gov_service = $this->findRecord('gov_services', ['name' => $service_name]);
            if (empty($gov_service)) {
                Console::output(Console::ansiFormat('Service not found for report [' . $short_name  . '] - [' . $service_name . ']', [Console::FG_RED]));
                continue;
            }

            $id_report = $report['id'];
            $id_service = $gov_service['id'];

            $columns = compact('id_report', 'id_service');

            \Yii::$app->db->createCommand()
                ->insert($tableName, $columns)
                ->execute();
        }
    }

    private function linkReportParams()
    {
        $csv = <<<CSV
Бланк регистрации и вакцинации животных;P0_Balanceinventorynumber
Бланк регистрации и вакцинации животных;P0_Vaccinename
Бланк регистрации и вакцинации животных;P0_VisitServiceTMCcount
Бланк регистрации и вакцинации животных;P13_Servicetext
Бланк регистрации и вакцинации животных;P17_Petcolor
Бланк регистрации и вакцинации животных;P18_Petspecialtrait
Биохимия крови;P0_Venousbloodanalysisnum
Биохимия крови;P14_Totalbilirubinmcmvalue
Биохимия крови;P15_Totalbilirubinmcmdesc
Биохимия крови;P16_Totalbilirubinmgvalue
Биохимия крови;P17_Totalbilirubinmgdesc
Биохимия крови;P18_Conjugatedbilirubinmcmvalue
Биохимия крови;P19_Conjugatedbilirubinmcmdesc
Биохимия крови;P20_Conjugatedbilirubinmgvalue
Биохимия крови;P21_Conjugatedbilirubinmgdesc
Биохимия крови;P22_Altalanniamvalue
Биохимия крови;P23_Altalanniamvadesc
Биохимия крови;P24_Astaspartvalue
Биохимия крови;P25_Astaspartdesc
Биохимия крови;P26_Mochevinammvalue
Биохимия крови;P27_Mochevinammdesc
Биохимия крови;P28_Mochevinamgvalue
Биохимия крови;P29_Mochevinamgdesc
Биохимия крови;P30_Creatininemcmvalue
Биохимия крови;P31_Creatininemcmdesc
Биохимия крови;P32_Creatininemgvalue
Биохимия крови;P33_Creatininemgdesc
Биохимия крови;P34_Shelochfosfatvalue
Биохимия крови;P35_Shelochfosfatdesc
Биохимия крови;P36_Amilazavalue
Биохимия крови;P37_Amilazadesc
Биохимия крови;P38_Pancreatinevalue
Биохимия крови;P39_Pancreatinedesc
Биохимия крови;P40_Glukozamcmvalue
Биохимия крови;P41_Glukozamcmdesc
Биохимия крови;P42_Glukozamgvalue
Биохимия крови;P43_Glukozamgdesc
Биохимия крови;P44_Ldglactodvalue
Биохимия крови;P45_Ldglactoddesc
Биохимия крови;P46_Lgtgammavalue
Биохимия крови;P47_Lgtgammadesc
Биохимия крови;P48_Kfkcreatinevalue
Биохимия крови;P49_Kfkcreatinedesc
Биохимия крови;P50_Holestermmvalue
Биохимия крови;P51_Holestermmdesc
Биохимия крови;P52_Holestermgvalue
Биохимия крови;P53_Holestermgdesc
Биохимия крови;P54_Triglyceridsmmvalue
Биохимия крови;P55_Triglyceridsmmdesc
Биохимия крови;P56_Triglyceridsmgvalue
Биохимия крови;P57_Triglyceridsmgdesc
Биохимия крови;P58_Caliummmvalue
Биохимия крови;P59_Caliummmdesc
Биохимия крови;P60_Caliummecvalue
Биохимия крови;P61_Caliummecdesc
Биохимия крови;P62_Natrmmvalue
Биохимия крови;P63_Natrmmdesc
Биохимия крови;P64_Natrmecvalue
Биохимия крови;P65_Natrmecdesc
Биохимия крови;P66_Phosphormmvalue
Биохимия крови;P67_Phosphormmdesc
Биохимия крови;P68_Phosphormgvalue
Биохимия крови;P69_Phosphormgdesc
Биохимия крови;P70_Calciummmvalue
Биохимия крови;P71_Calciummmdesc
Биохимия крови;P72_Calciummcgvalue
Биохимия крови;P73_Calciummcgdesc
Биохимия крови;P74_Ironmcmvalue
Биохимия крови;P75_Ironmcmdesc
Биохимия крови;P76_Ironmcgvalue
Биохимия крови;P77_Ironmcgdesc
Биохимия крови;P78_Magnesiummmvalue
Биохимия крови;P79_Magnesiummmdesc
Биохимия крови;P80_Magnesiummecvalue
Биохимия крови;P81_Magnesiummecdesc
Биохимия крови;P82_Chloridemmvalue
Биохимия крови;P83_Chloridemmdesc
Биохимия крови;P84_Chloridemecvalue
Биохимия крови;P85_Chloridemecdesc
Биохимия крови;P86_Acidityvalue
Биохимия крови;P87_Aciditydesc
Биохимия крови;P88_Mochekislnmvalue
Биохимия крови;P89_Mochekislnmdesc
Биохимия крови;P90_Mochekislmgvalue
Биохимия крови;P91_Mochekislmgdesc
Биохимия крови;P92_Lipazavalue
Биохимия крови;P93_Lipazadesc
Биохимия крови;P94_Totalproteinglvalue
Биохимия крови;P95_Totalproteingldesc
Биохимия крови;P96_Totalproteingdlvalue
Биохимия крови;P97_Totalproteingdldesc
Биохимия крови;P98_Albuminglvalue
Биохимия крови;P99_Albumingldesc
Биохимия крови;P100_Albumingdlvalue
Биохимия крови;P101_Albumingdldesc
Биохимия крови;P102_Hemoglobinvalue
Биохимия крови;P103_Hemoglobindesc
Биохимия крови;P104_Ketonebodiesvalue
Биохимия крови;P105_Bikarbonatvalue
Биохимия крови;P106_Proteinfractionsvalue
влаг цитол;P0_Cytologicsanalysisnum
влаг цитол;P14_Bazalvalue
влаг цитол;P15_Parabazalvalue
влаг цитол;P16_Promezhutvalue
влаг цитол;P17_Poverkhvalue
влаг цитол;P18_Leukocytesvalue
влаг цитол;P19_Erythrocytesvalue
влаг цитол;P20_Bacteriavalue
влаг цитол;P21_Fazatsiklavalue
влаг цитол;P22_Recomendvalue
влаг цитол;P23_Cytologicscreeninganswerdate
Гельминто-копр;P0_Coproalysisnum
Гельминто-копр;P12_Serviceresultvalue
Гельминто-копр;P13_Serviceresultdesc
Клин анализ мочи;P0_Urinalysisnum
Клин анализ мочи;P14_Colorurinevalue
Клин анализ мочи;P15_Colorurinedesc
Клин анализ мочи;P16_Transparencyvalue
Клин анализ мочи;P17_Transparencydesc
Клин анализ мочи;P18_Acidityvalue
Клин анализ мочи;P19_Aciditydesc
Клин анализ мочи;P20_Proteinvalue
Клин анализ мочи;P21_Proteindesc
Клин анализ мочи;P22_Glukozavalue
Клин анализ мочи;P23_Glukozadesc
Клин анализ мочи;P24_Ketonbodvalue
Клин анализ мочи;P25_Ketonboddesc
Клин анализ мочи;P26_Relativedensityvalue
Клин анализ мочи;P27_Relativedensitydesc
Клин анализ мочи;P28_Bilirubinvalue
Клин анализ мочи;P29_Bilirubindesc
Клин анализ мочи;P30_Hemeglvalue
Клин анализ мочи;P31_Hemegldesc
Клин анализ мочи;P32_Erythrocytvalue
Клин анализ мочи;P33_Erythrocytdesc
Клин анализ мочи;P34_Leucocytvalue
Клин анализ мочи;P35_Leucocytdesc
Клин анализ мочи;P36_Ploskiyvalue
Клин анализ мочи;P37_Ploskiydesc
Клин анализ мочи;P38_Perehodvalue
Клин анализ мочи;P39_Perehoddesc
Клин анализ мочи;P40_Pochechnvalue
Клин анализ мочи;P41_Pochechndesc
Клин анализ мочи;P42_Hyalinevalue
Клин анализ мочи;P43_Hyalinedesc
Клин анализ мочи;P44_Granularvalue
Клин анализ мочи;P45_Granulardesc
Клин анализ мочи;P46_Waxvalue
Клин анализ мочи;P47_Waxdesc
Клин анализ мочи;P48_Lekocitvalue
Клин анализ мочи;P49_Lekocitdesc
Клин анализ мочи;P50_Eritrocitvalue
Клин анализ мочи;P51_Eritrocitdesc
Клин анализ мочи;P52_Epitelvalue
Клин анализ мочи;P53_Epiteldesc
Клин анализ мочи;P54_Cilindvalue
Клин анализ мочи;P55_Cilinddesc
Клин анализ мочи;P56_Bacteriavalue
Клин анализ мочи;P57_Bacteriadesc
Клин анализ мочи;P58_Saltvalue
Клин анализ мочи;P59_Saltdesc
Клин анализ мочи;P61_Urinunitweightvalue
Клин анализ мочи;P62_Urinreactionvalue
Клин анализ мочи;P63_Nitratvalue
Клин анализ мочи;P64_Urinorddepositionvalue
Клин анализ мочи;P65_Urindisorddepositionvalue
Клин анализ мочи;P66_UrinAnswerdate
Клин анализ мочи;P67_Urinconsistency
ЛД;P12_Serviceresultvalue
ЛД;P13_Serviceresultdesc
Микроскопическое исследование;P15_Analysisresult
Микроскопическое исследование;P16_Analysisdesc
Микроскопическое исследование;P0_Capillarybloodanalysisnum
Микроскопическое исследование;P14_Analysiscount
Микроскопическое исследование;P15_Activator
Микроскопическое исследование;P18_Analysisdate
Микроскопическое исследование;P19_Animalcount
Микроскопическое исследование;P20_Analysisobject
Микроскопическое исследование;P21_Diagnostictechnique
Микроскопическое исследование;P22_Parasdiseasesanswerdate
результат биохимического исследования кала;P14_Coprformvalue
результат биохимического исследования кала;P15_Coprformdesc
результат биохимического исследования кала;P16_Coprcolorvalue
результат биохимического исследования кала;P17_Coprcolordesc
результат биохимического исследования кала;P18_Coprodorvalue
результат биохимического исследования кала;P19_Coprodordesc
результат биохимического исследования кала;P20_Acidityvalue
результат биохимического исследования кала;P21_Aciditydesc
результат биохимического исследования кала;P22_Stercobilinvalue
результат биохимического исследования кала;P23_Stercobilindesc
результат биохимического исследования кала;P24_Bilirubinvalue
результат биохимического исследования кала;P25_Bilirubindesc
результат биохимического исследования кала;P26_Bloodvalue
результат биохимического исследования кала;P27_Blooddesc
результат биохимического исследования кала;P28_Muscledfibersvalue
результат биохимического исследования кала;P29_Muscledfibersdesc
результат биохимического исследования кала;P30_Contissuefibersvalue
результат биохимического исследования кала;P31_Contissuefibersdesc
результат биохимического исследования кала;P32_Neutralfatvalue
результат биохимического исследования кала;P33_Neutralfatdesc
результат биохимического исследования кала;P34_Fattyacidsvalue
результат биохимического исследования кала;P35_Fattyacidsdesc
результат биохимического исследования кала;P36_Soapvalue
результат биохимического исследования кала;P37_Soapdesc
результат биохимического исследования кала;P38_Starchvalue
результат биохимического исследования кала;P39_Starchdesc
результат биохимического исследования кала;P38_Starchvalue
результаты гормонального исследования крови;P14_Cortisolbazalvalue
результаты гормонального исследования крови;P15_Cortisolactgvalue
результаты гормонального исследования крови;P16_Cortisoldexvalue
результаты гормонального исследования крови;P17_Proganesvalue
результаты гормонального исследования крови;P18_Progproenstvalue
результаты гормонального исследования крови;P19_Progestrusvalue
результаты гормонального исследования крови;P20_Progmetestvalue
результаты гормонального исследования крови;P21_Estradanesvalue
результаты гормонального исследования крови;P22_Estradproenstvalue
результаты гормонального исследования крови;P23_Estradestrusvalue
результаты гормонального исследования крови;P24_Estradmetestvalue
результаты гормонального исследования крови;P25_Estradmalevalue
результаты гормонального исследования крови;P26_Testostervalue
результаты гормонального исследования крови;P27_Thyroxvalue
результаты гормонального исследования крови;P28_Triiodtirvalue
результаты гормонального исследования крови;P14_Cortisolbazalvalue
УЗИ глаза;P0_Organsystem
УЗИ глаза;P14_Odrazmerperednegootrezka
УЗИ глаза;P15_Odrazmerzadnegootrezka
УЗИ глаза;P16_Odstructuraperedcamer
УЗИ глаза;P17_Odrazmerhrust
УЗИ глаза;P18_Odstructurahrust
УЗИ глаза;P19_Odcapsulahrust
УЗИ глаза;P20_Odstructurasteklotelo
УЗИ глаза;P21_Oddiametrzrachka
УЗИ глаза;P22_Odcontur
УЗИ глаза;P23_Odstructura
УЗИ глаза;P24_Odstructuradiskazritnerva
УЗИ глаза;P25_Odstructuraretrobulyar
УЗИ глаза;P26_Osrazmerperednegootrezka
УЗИ глаза;P27_Osrazmerzadnegootrezka
УЗИ глаза;P28_Osstructuraperedcamer
УЗИ глаза;P29_Osrazmerhrust
УЗИ глаза;P30_Osstructurahrust
УЗИ глаза;P31_Oscapsulahrust
УЗИ глаза;P32_Osstructurasteklotelo
УЗИ глаза;P33_Osdiametrzrachka
УЗИ глаза;P34_Oscontur
УЗИ глаза;P35_Osstructura
УЗИ глаза;P36_Osstructuradiskazritnerva
УЗИ глаза;P37_Osstructuraretrobulyar
УЗИ глаза;P38_Serviceresult
УЗИ мочевыдел;P0_Organsystem
УЗИ мочевыдел;P14_Prvpochraspoloshenie
УЗИ мочевыдел;P15_Prvpochgranica
УЗИ мочевыдел;P16_Prvpochrazmer
УЗИ мочевыдел;P17_Prvpochkortiksloytolshina
УЗИ мочевыдел;P18_Prvpochkortiksloyekhogennost
УЗИ мочевыдел;P19_Prvpochkortiksloyekhostruktura
УЗИ мочевыдел;P20_Prvpochmedullyarsloytolshchina
УЗИ мочевыдел;P21_Prvpochmedullyarsloyekhogennost
УЗИ мочевыдел;P22_Prvpochmedullyarsloyekhostruktura
УЗИ мочевыдел;P23_Prvpochmedullyarsloykortmeddiffer
УЗИ мочевыдел;P24_Prvpochpiyelicheskiyindeks
УЗИ мочевыдел;P25_Prvpochpochsinusekhogennost
УЗИ мочевыдел;P26_Prvpochpochsinuschetkostdifferents
УЗИ мочевыдел;P27_Prvpochpochsinuspolostlokhanki
УЗИ мочевыдел;P28_Prvpochpochsinusstepenlokhanki
УЗИ мочевыдел;P29_Prvpochsosudyparenkhimy
УЗИ мочевыдел;P30_PrvpochIndeksrezistivnpochechnart
УЗИ мочевыдел;P31_PrvpochIndeksrezistivnmezhdolevoyart
УЗИ мочевыдел;P32_Prvpochkonkrementy
УЗИ мочевыдел;P33_Prvpochobyemnobrazov
УЗИ мочевыдел;P34_Levpochraspoloshenie
УЗИ мочевыдел;P35_Levpochgranica
УЗИ мочевыдел;P36_Levpochrazmer
УЗИ мочевыдел;P37_Levpochkortiksloytolshina
УЗИ мочевыдел;P38_Levpochkortiksloyekhogennost
УЗИ мочевыдел;P39_Levpochkortiksloyekhostruktura
УЗИ мочевыдел;P40_Levpochmedullyarsloytolshchina
УЗИ мочевыдел;P41_Levpochmedullyarsloyekhogennost
УЗИ мочевыдел;P42_Levpochmedullyarsloyekhostruktura
УЗИ мочевыдел;P43_Levpochmedullyarsloykortmeddiffer
УЗИ мочевыдел;P44_Levpochpiyelicheskiyindeks
УЗИ мочевыдел;P45_Levpochpochsinusekhogennost
УЗИ мочевыдел;P46_Levpochpochsinuschetkostdifferents
УЗИ мочевыдел;P47_Levpochpochsinuspolostlokhanki
УЗИ мочевыдел;P48_Levpochpochsinusstenkilokhanki
УЗИ мочевыдел;P49_Levpochsosudyparenkhimy
УЗИ мочевыдел;P50_LevpochIndeksrezistivnpochechnart
УЗИ мочевыдел;P51_LevpochIndeksrezistivnmezhdolevoyart
УЗИ мочевыдел;P52_Levpochkonkrementy
УЗИ мочевыдел;P53_Levpochobyemnobrazov
УЗИ мочевыдел;P54_Mochpuzstepnapoln
УЗИ мочевыдел;P55_Mochpuztolshchinastenki
УЗИ мочевыдел;P56_Mochpuzdeformatsiya
УЗИ мочевыдел;P57_Mochpuzuretra
УЗИ мочевыдел;P58_MochpuzObyemnobrazov
УЗИ мочевыдел;P59_Serviceresult
УЗИ печ. и т.д;P0_Organsystem
УЗИ печ. и т.д;P14_Pechenraspoloshenie
УЗИ печ. и т.д;P15_Pechenkontur
УЗИ печ. и т.д;P16_Pechenrazmer
УЗИ печ. и т.д;P17_Pechenekhostruktura
УЗИ печ. и т.д;P18_Pechenekhogennost
УЗИ печ. и т.д;P19_Pechenperifsosudrisunok
УЗИ печ. и т.д;P20_Pechenportae
УЗИ печ. и т.д;P21_Pechenvhepatica
УЗИ печ. и т.д;P22_Pechenahepatica
УЗИ печ. и т.д;P23_Pechenobyemnobrazov
УЗИ печ. и т.д;P24_Zhelchpuzyrstepennapolneniya
УЗИ печ. и т.д;P25_Zhelchpuzyrformazhelchpuzyrya
УЗИ печ. и т.д;P26_Zhelchpuzyrtolshchinastenki
УЗИ печ. и т.д;P27_Zhelchpuzyrdeformatsiya
УЗИ печ. и т.д;P28_Zhelchpuzyrstrukturazhelchi
УЗИ печ. и т.д;P29_Zhelchpuzyrpuzyrprotok
УЗИ печ. и т.д;P30_Zhelchpuzyrobshzhelchprotok
УЗИ печ. и т.д;P31_Zhelchpuzyrpechenochnprotok
УЗИ печ. и т.д;P32_Zhelchpuzyrobyemnobrazov
УЗИ печ. и т.д;P33_Selezenkaraspoloshenie
УЗИ печ. и т.д;P34_Selezenkakontur
УЗИ печ. и т.д;P35_Selezenkarazmer
УЗИ печ. и т.д;P36_Selezenkaekhostruktura
УЗИ печ. и т.д;P37_Selezenkaekhogennost
УЗИ печ. и т.д;P38_Selezenkasosudrisunok
УЗИ печ. и т.д;P39_Selezenkaobyemnobrazov
УЗИ печ. и т.д;P40_Podzhelzhelezaraspoloshenie
УЗИ печ. и т.д;P41_Podzhelzhelezakontur
УЗИ печ. и т.д;P42_Podzhelzhelezarazmer
УЗИ печ. и т.д;P43_Podzhelzhelezaekhostruktura
УЗИ печ. и т.д;P44_Podzhelzhelezaekhogennost
УЗИ печ. и т.д;P45_Podzhelzhelezaobyemnobrazov
УЗИ печ. и т.д;P46_Zheludkishechntrakt
УЗИ печ. и т.д;P47_Svobodnzhidkost
УЗИ печ. и т.д;P48_Serviceresult
УЗИ репр. Самки;P0_Organsystem
УЗИ репр. Самки;P14_Matkadiametrtela
УЗИ репр. Самки;P15_Matkatolshinatela
УЗИ репр. Самки;P16_Matkastrukturastenkitela
УЗИ репр. Самки;P17_Matkasostoyanpolosti
УЗИ репр. Самки;P18_Matkadiametrpravroga
УЗИ репр. Самки;P19_Matkatolshinapravroga
УЗИ репр. Самки;P20_Matkastrukturastenkipravroga
УЗИ репр. Самки;P21_Matkasoderzhimpolostipravroga
УЗИ репр. Самки;P22_Matkadiametrlevroga
УЗИ репр. Самки;P23_Matkatolshinalevroga
УЗИ репр. Самки;P24_Matkastrukturastenkilevroga
УЗИ репр. Самки;P25_Matkasoderzhimpolostilevroga
УЗИ репр. Самки;P26_Pravyaichnikrazmer
УЗИ репр. Самки;P27_Pravyaichnikkontur
УЗИ репр. Самки;P28_Pravyaichniknovoobrazov
УЗИ репр. Самки;P29_Levyaichnikrazmer
УЗИ репр. Самки;P30_Levyaichnikkontur
УЗИ репр. Самки;P31_Levyaichniknovoobrazov
УЗИ репр. Самки;P32_Serviceresult
УЗИ репр. Самца ;P0_Organsystem
УЗИ репр. Самца ;P14_Predstzhelezarazmer
УЗИ репр. Самца ;P15_Predstzhelezakontur
УЗИ репр. Самца ;P16_Predstzhelezaparenkhima
УЗИ репр. Самца ;P17_Predstzhelezaobyemnobrazov
УЗИ репр. Самца ;P18_Pravsemrazmer
УЗИ репр. Самца ;P19_Pravsemkontur
УЗИ репр. Самца ;P20_Pravsemparenkhima
УЗИ репр. Самца ;P21_Pravsemobyemnobrazov
УЗИ репр. Самца ;P22_Pridatokpravsemgolovka
УЗИ репр. Самца ;P23_Pridatokpravsemtelo
УЗИ репр. Самца ;P24_Pridatokpravsemobyemnobrazov
УЗИ репр. Самца ;P25_Levsemrazmer
УЗИ репр. Самца ;P26_Levsemkontur
УЗИ репр. Самца ;P27_Levsemparenkhima
УЗИ репр. Самца ;P28_Levsemobyemnobrazov
УЗИ репр. Самца ;P29_Pridatoklevsemgolovka
УЗИ репр. Самца ;P30_Pridatoklevsemtelo
УЗИ репр. Самца ;P31_Pridatoklevsemobyemnobrazov
УЗИ репр. Самца ;P32_Abdomultmserviceresult
ЭКГ;P12_Pc
ЭКГ;P13_Pmv
ЭКГ;P14_Р1
ЭКГ;P15_P2
ЭКГ;P16_P3
ЭКГ;P17_Pq
ЭКГ;P18_Qrs
ЭКГ;P19_Qrsdesc
ЭКГ;P20_Rmv
ЭКГ;P21_Rdesc
ЭКГ;P22_Tmv
ЭКГ;P23_Tdesc
ЭКГ;P24_St
ЭКГ;P25_Qt
ЭКГ;P26_Eos
ЭКГ;P27_Chss
ЭКГ;P28_Ritm
ЭКГ;P29_Ekstrasistoly
ЭКГ;P30_Serviceresult
ЭХО-КГ;P14_LVIDd
ЭХО-КГ;P15_LVIDs
ЭХО-КГ;P16_LVWTd
ЭХО-КГ;P17_LVWTs
ЭХО-КГ;P18_IVSTd
ЭХО-КГ;P19_IVSTs
ЭХО-КГ;P20_EF
ЭХО-КГ;P21_FS
ЭХО-КГ;P22_LA
ЭХО-КГ;P23_AO
ЭХО-КГ;P24_LA/AO
ЭХО-КГ;P25_RVIDd
ЭХО-КГ;P26_RVIDs
ЭХО-КГ;P27_RVWTd
ЭХО-КГ;P28_RVWTs
ЭХО-КГ;P29_RA
ЭХО-КГ;P30_Defektivs
ЭХО-КГ;P31_Defektias
ЭХО-КГ;P32_Svobodnzhidkostperikarde
ЭХО-КГ;P33_Svobodnzhidkostplevralpolosti
ЭХО-КГ;P34_Novoobrazov
ЭХО-КГ;P35_Mitrklapnstvorki
ЭХО-КГ;P36_Mitrklapnskorostkrovotoka
ЭХО-КГ;P37_Mitrklapnregurgitatsiya
ЭХО-КГ;P38_Trikuspklapnstvorki
ЭХО-КГ;P39_Trikuspklapnskorostkrovotoka
ЭХО-КГ;P40_Trikuspklapnregurgitatsiya
ЭХО-КГ;P41_Aortaklapnstvorki
ЭХО-КГ;P42_Aortaklapnskorostkrovotoka
ЭХО-КГ;P43_Aortaklapnregurgitatsiya
ЭХО-КГ;P44_Klapnlegartstvorki
ЭХО-КГ;P45_Klapnlegartskorostkrovotoka
ЭХО-КГ;P46_Klapnlegartregurgitatsiya
ЭХО-КГ;P47_Serviceresult
ОАК_Mythic;P14_Wbcvalue
ОАК_Mythic;P15_Wbcdesc
ОАК_Mythic;P16_Lymvalue
ОАК_Mythic;P17_Lymdesc
ОАК_Mythic;P18_Monvalue
ОАК_Mythic;P19_Mondesc
ОАК_Mythic;P20_Gravalue
ОАК_Mythic;P21_Gradesc
ОАК_Mythic;P22_Rbcvalue
ОАК_Mythic;P23_Rbcdesc
ОАК_Mythic;P24_Hgbvalue
ОАК_Mythic;P25_Hgbdesc
ОАК_Mythic;P26_Hctvalue
ОАК_Mythic;P27_Hctdesc
ОАК_Mythic;P28_Mcvvalue
ОАК_Mythic;P29_Mcvdesc
ОАК_Mythic;P30_Mchvalue
ОАК_Mythic;P31_Mchdesc
ОАК_Mythic;P32_Mchcvalue
ОАК_Mythic;P33_Mchcdesc
ОАК_Mythic;P34_Rdwvalue
ОАК_Mythic;P35_Rdwdesc
ОАК_Mythic;P36_Pltvalue
ОАК_Mythic;P37_Pltdesc
ОАК_Mythic;P38_Mpvvalue
ОАК_Mythic;P39_Mpvdesc
ОАК_Mythic;P40_Pctvalue
ОАК_Mythic;P41_Pctdesc
ОАК_Mythic;P42_Pdwvalue
ОАК_Mythic;P43_Pdwdesc
ОАК_Mythic;P44_Soevalue
ОАК_Mythic;P45_Soedesc
ОАК_Mythic;P46_Youngvalue
ОАК_Mythic;P47_Youngdesc
ОАК_Mythic;P48_Palochkoyadervalue
ОАК_Mythic;P49_Palochkoyaderdesc
ОАК_Mythic;P50_Segmentvalue
ОАК_Mythic;P51_Segmentdesc
ОАК_Mythic;P52_Eosinophilsvalue
ОАК_Mythic;P53_Eosinophilsdesc
ОАК_Mythic;P54_Monocitvalue
ОАК_Mythic;P55_Monocitdesc
ОАК_Mythic;P56_Bazophilvalue
ОАК_Mythic;P57_Bazophildesc
ОАК_Mythic;P58_Limphocitvalue
ОАК_Mythic;P59_Limphocitdesc
Журнал биохимического исследования крови;P3_Visitstartdate
Журнал биохимического исследования крови;P4_Ownername
Журнал биохимического исследования крови;P5_Owneraddres
Журнал биохимического исследования крови;P0_Venousbloodanalysisnum
Журнал биохимического исследования крови;P6_Speciesname
Журнал биохимического исследования крови;P10_Petbirthday
Журнал биохимического исследования крови;P8_Petsex
Журнал биохимического исследования крови;P14_Totalbilirubinmcmvalue
Журнал биохимического исследования крови;P18_Conjugatedbilirubinmcmvalue
Журнал биохимического исследования крови;P22_Altalanniamvalue
Журнал биохимического исследования крови;P24_Astaspartvalue
Журнал биохимического исследования крови;P36_Amilazavalue
Журнал биохимического исследования крови;P34_Shelochfosfatvalue
Журнал биохимического исследования крови;P26_Mochevinammvalue
Журнал биохимического исследования крови;P30_Creatininemcmvalue
Журнал биохимического исследования крови;P50_Holestermmvalue
Журнал биохимического исследования крови;P54_Triglyceridsmmvalue
Журнал биохимического исследования крови;P40_Glukozamcmvalue
Журнал биохимического исследования крови;P104_Ketonebodiesvalue
Журнал биохимического исследования крови;P105_Bikarbonatvalue
Журнал биохимического исследования крови;P94_Totalproteinglvalue
Журнал биохимического исследования крови;P70_Calciummmvalue
Журнал биохимического исследования крови;P66_Phosphormmvalue
Журнал биохимического исследования крови;P58_Caliummmvalue
Журнал биохимического исследования крови;P62_Natrmmvalue
Журнал биохимического исследования крови;P106_Proteinfractionsvalue
Журнал регистрации и вакцинации животных;P0_Balanceinventorynumber
Журнал регистрации и вакцинации животных;P0_Petchpidentificationcode
Журнал регистрации и вакцинации животных;P0_Petlabelidentificationcode
Журнал регистрации и вакцинации животных;P0_Petregnum
Журнал регистрации и вакцинации животных;P0_Vaccinename
Журнал регистрации и вакцинации животных;P0_VisitServiceTMCcount
Журнал регистрации и вакцинации животных;P10_Petbirthday
Журнал регистрации и вакцинации животных;P17_Petcolor
Журнал регистрации и вакцинации животных;P18_Petspecialtrait
Журнал регистрации и вакцинации животных;P19_Petregexpiredate
Журнал регистрации и вакцинации животных;P3_Visitstartdate
Журнал регистрации и вакцинации животных;P4_Ownername
Журнал регистрации и вакцинации животных;P5_Owneraddres
Журнал регистрации и вакцинации животных;P5_Ownercontact
Журнал регистрации и вакцинации животных;P6_Speciesname
Журнал регистрации и вакцинации животных;P7_Breedname
Журнал регистрации и вакцинации животных;P8_Petsex
Журнал регистрации и вакцинации животных;P9_Petname
Журнал общих клинических исследований мочи;P3_Visitstartdate
Журнал общих клинических исследований мочи;P33_SpecialistFIO
Журнал общих клинических исследований мочи;P5_Owneraddres
Журнал общих клинических исследований мочи;P6_Speciesname
Журнал общих клинических исследований мочи;P10_Petbirthday
Журнал общих клинических исследований мочи;P9_Petname
Журнал общих клинических исследований мочи;P14_Colorurinevalue
Журнал общих клинических исследований мочи;P16_Transparencyvalue
Журнал общих клинических исследований мочи;P67_Urinconsistency
Журнал общих клинических исследований мочи;P61_Urinunitweightvalue
Журнал общих клинических исследований мочи;P62_Urinreactionvalue
Журнал общих клинических исследований мочи;P34_Leucocytvalue
Журнал общих клинических исследований мочи;P63_Nitratvalue
Журнал общих клинических исследований мочи;P20_Proteinvalue
Журнал общих клинических исследований мочи;P22_Glukozavalue
Журнал общих клинических исследований мочи;P24_Ketonbodvalue
Журнал общих клинических исследований мочи;P22_Glukozavalue
Журнал общих клинических исследований мочи;P28_Bilirubinvalue
Журнал общих клинических исследований мочи;P32_Erythrocytvalue
Журнал общих клинических исследований мочи;P64_Urinorddepositionvalue
Журнал общих клинических исследований мочи;P65_Urindisorddepositionvalue
Журнал общих клинических исследований мочи;P66_UrinAnswerdate
Журнал учета лабораторных исследований на паразитарные болезни животных;P0_Capillarybloodanalysisnum
Журнал учета лабораторных исследований на паразитарные болезни животных;P18_Analysisdate
Журнал учета лабораторных исследований на паразитарные болезни животных;P3_Visitstartdate
Журнал учета лабораторных исследований на паразитарные болезни животных;P4_Ownername
Журнал учета лабораторных исследований на паразитарные болезни животных;P5_Owneraddres
Журнал учета лабораторных исследований на паразитарные болезни животных;P6_Speciesname
Журнал учета лабораторных исследований на паразитарные болезни животных;P10_Petbirthday
Журнал учета лабораторных исследований на паразитарные болезни животных;P19_Animalcount
Журнал учета лабораторных исследований на паразитарные болезни животных;P18_Analysisdate
Журнал учета лабораторных исследований на паразитарные болезни животных;P12_Analysisnum
Журнал учета лабораторных исследований на паразитарные болезни животных;P14_Analysiscount
Журнал учета лабораторных исследований на паразитарные болезни животных;P20_Analysisobject
Журнал учета лабораторных исследований на паразитарные болезни животных;P21_Diagnostictechnique
Журнал учета лабораторных исследований на паразитарные болезни животных;P15_Analysisresult
Журнал учета лабораторных исследований на паразитарные болезни животных;P15_Activator
Журнал учета лабораторных исследований на паразитарные болезни животных;P22_Parasdiseasesanswerdate
Журнал общих исследований фекалий;P3_Visitstartdate
Журнал общих исследований фекалий;P33_SpecialistFIO
Журнал общих исследований фекалий;P5_Owneraddres
Журнал общих исследований фекалий;P6_Speciesname
Журнал общих исследований фекалий;P10_Petbirthday
Журнал общих исследований фекалий;P9_Petname
Журнал общих исследований фекалий;P16_Coprcolorvalue
Журнал общих исследований фекалий;P18_Coprodorvalue
Журнал общих исследований фекалий;P14_Coprformvalue
Журнал общих исследований фекалий;P20_Acidityvalue
Журнал общих исследований фекалий;P26_Bloodvalue
Журнал общих исследований фекалий;P24_Bilirubinvalue
Журнал общих исследований фекалий;P22_Stercobilinvalue
Журнал общих исследований фекалий;P28_Muscledfibersvalue
Журнал общих исследований фекалий;P30_Contissuefibersvalue
Журнал общих исследований фекалий;P32_Neutralfatvalue
Журнал общих исследований фекалий;P34_Fattyacidsvalue
Журнал общих исследований фекалий;P36_Soapvalue
Журнал общих исследований фекалий;P38_Starchvalue
Журнал общих исследований фекалий;P39_Coproanswerdate
Журнал цитологических исследований;P0_Cytologicsanalysisnum
Журнал цитологических исследований;P3_Visitstartdate
Журнал цитологических исследований;P4_Ownername
Журнал цитологических исследований;P5_Owneraddres
Журнал цитологических исследований;P6_Speciesname
Журнал цитологических исследований;P24_Anamnesis
Журнал цитологических исследований;P14_Bazalvalue
Журнал цитологических исследований;P15_Parabazalvalue
Журнал цитологических исследований;P16_Promezhutvalue
Журнал цитологических исследований;P17_Poverkhvalue
Журнал цитологических исследований;P18_Leukocytesvalue
Журнал цитологических исследований;P19_Erythrocytesvalue
Журнал цитологических исследований;P20_Bacteriavalue
Журнал цитологических исследований;P21_Fazatsiklavalue
Журнал цитологических исследований;P22_Recomendvalue
Журнал цитологических исследований;P23_Cytologicscreeninganswerdate
CSV;

        $tableName = 'reports_params';
        $tmpTable = 'reports_tmp';

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $short_name = $item[0];
            $tech_name = $item[1];

            $param = $this->findParamByTechName($tech_name);
            if (empty($param)) {
                Console::output(Console::ansiFormat('Param not found [' . $tech_name  . ']', [Console::FG_RED]));
                continue;
            }

            $report = $this->findRecord($tmpTable, ['short_name' => $short_name]);
            if (empty($report)) {
                Console::output(Console::ansiFormat('Report not found [' . $short_name  . ']', [Console::FG_RED]));
                continue;
            }

            $id_param = $param['id'];
            $id_report = $report['id'];

            $columns = compact('id_param', 'id_report');

            \Yii::$app->db->createCommand()
                ->insert($tableName, $columns)
                ->execute();
        }
    }

    private function createTmpTable()
    {
        $this->createTable('reports_tmp', [
            'id' => $this->integer(),
            'short_name' => $this->string(),
            'name' => $this->string(),
        ]);
        $this->addPrimaryKey('reports_tmp_pkey', 'reports_tmp', ['id', 'short_name']);
    }

    private function dropTmpTable()
    {
        $this->execute('drop table if exists reports_tmp');
    }

    private function truncateTables()
    {
        $tables = [
            'reports_params',
            'gov_services_params',
            'gov_services_reports',
        ];

        foreach ($tables as $table) {
            $this->truncateTable($table);
        }
    }

    private function resetSequences()
    {
        $tables = [
            'params',
            'reports',
            'services',
            'reports_params',
            'gov_services_params',
            'gov_services_reports',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
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
            $arr[] = str_getcsv($row, ';', '');
        }

        if (empty($arr)) {
            throw new \Exception($function . ': Failed to parse CSV');
        }

        return $arr;
    }

    /**
     * @param string $tableName
     * @param array $condition
     * @return array
     */
    private function findRecord($tableName, $condition)
    {
        return (new Query())
            ->from($tableName)
            ->where($condition)
            ->limit(1)
            ->one();
    }

    /**
     * @param $tech_name
     * @return array
     */
    private function findParamByTechName($tech_name)
    {
        return $this->findRecord('params', ['tech_name' => $tech_name]);
    }
}
