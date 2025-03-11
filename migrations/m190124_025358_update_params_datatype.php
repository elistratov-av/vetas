<?php

use app\commands\migrate\Migration;
use app\models\db\Params;
use yii\db\Query;
use yii\helpers\Console;
use yii\helpers\VarDumper;

/**
 * Class m190124_025358_update_params_datatype
 */
class m190124_025358_update_params_datatype extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // https://jira.altarix.ru/browse/VETAIS-1278
        // https://jira.altarix.ru/browse/VETAIS-1358

        $csv = <<<CSV
P14_Coprformvalue;Консистенция, форма - результат исследования;dict;Coprform;false;1
P16_Coprcolorvalue;Цвет - результат исследования;dict;Coprcolor;false;2
P18_Coprodorvalue;Запах - результат исследования;dict;Coprodor;false;3
P24_Bilirubinvalue;Билирубин - результат исследования;dict;Coprobilirubin;false;4
P26_Bloodvalue;Кровь - результат исследования;dict;Coprblood;false;5
P28_Muscledfibersvalue;мышечные волокна - результат исследования;dict;Coprmuscledfibers;false;6
P30_Contissuefibersvalue;соединительно-тканные волокна - результат исследования;dict;Coprcontissuefibers;false;7
P32_Neutralfatvalue;нейтральный жир - результат исследования;dict;Coprneutralfat;false;8
P36_Soapvalue;мыла - результат исследования;dict;Coprsoap;false;9
P38_Starchvalue;крахмал - результат исследования;dict;Coprstarch;false;10
P14_Colorurinevalue;Цвет мочи - результат исследования;dict;Urincolor;false;11
P16_Transparencyvalue;Прозрачность - результат исследования;dict;Urintransparency;false;12
P14_Pechenraspoloshenie;Печень: Расположение;dict;Abdomendposition;false;13
P14_Prvpochraspoloshenie;Правая почка: Расположение;dict;Kidneyposition;false;14
P16_Predstzhelezaparenkhima;Предстательная железа: Паренхима;dict;Parenchymatous;false;15
P18_Pechenekhogennost;Печень: Эхогенность паренхимы;dict;Abdomendechogenicity;false;16
P18_Prvpochkortiksloyekhogennost;Правая почка: кортикальный слой - Эхогенность;dict;Kidneyechogenicity;false;17
P19_Pechenperifsosudrisunok;Печень: Периферический сосудистый рисунок;dict;Periphvascularpattern;false;18
P20_Bacteriavalue;Бактерии;dict;Bacteria;false;19
P20_Pravsemparenkhima;Правый семенник: Паренхима;dict;Parenchymatous;false;20
P21_Prvpochmedullyarsloyekhogennost;Правая почка: Медуллярный слой - Эхогенность;dict;Kidneyechogenicity;false;21
P25_Prvpochpochsinusekhogennost;Правая почка: Почечный синус - Эхогенность;dict;Kidneyechogenicity;false;22
P25_Zhelchpuzyrformazhelchpuzyrya;Желчный пузырь: Форма желчного пузыря;dict;Abdomendform;false;23
P27_Levsemparenkhima;Левый семенник: Паренхима;dict;Parenchymatous;false;24
P27_Zhelchpuzyrdeformatsiya;Желчный пузырь: Деформация;dict;Abdomendeformation;false;25
P28_Zhelchpuzyrstrukturazhelchi;Желчный пузырь: Структура желчи;dict;Patternbile;false;26
P33_Selezenkaraspoloshenie;Селезенка: Расположение;dict;Abdomendposition;false;27
P34_Levpochraspoloshenie;Левая почка: Расположение;dict;Kidneyposition;false;28
P37_Selezenkaekhogennost;Селезенка: Эхогенность паренхимы;dict;Abdomendechogenicity;false;29
P38_Levpochkortiksloyekhogennost;Левая почка: кортикальный слой - Эхогенность;dict;Kidneyechogenicity;false;30
P38_Selezenkasosudrisunok;Селезенка: Сосудистый рисунок;dict;Periphvascularpattern;false;31
P40_Podzhelzhelezaraspoloshenie;Поджелудочная железа: Расположение;dict;Abdomendposition;false;32
P41_Levpochmedullyarsloyekhogennost;Левая почка: Медуллярный слой - Эхогенность;dict;Kidneyechogenicity;false;33
P44_Podzhelzhelezaekhogennost;Поджелудочная железа: Эхогенность;dict;Abdomendechogenicity;false;34
P45_Levpochpochsinusekhogennost;Левая почка: Почечный синус - Эхогенность;dict;Kidneyechogenicity;false;35
P56_Mochpuzdeformatsiya;Мочевой пузырь: Деформация;dict;Urinarybladderdeformation;false;36
P16_Matkastrukturastenkitela;Матка - Структура стенки тела;dict;Alvusstructure;false;37
P17_Matkasostoyanpolosti;Матка - Состояние полости;dict;Alvuscontents;false;38
P20_Matkastrukturastenkipravroga;Структура стенки правого рога;dict;Alvusstructure;false;39
P21_Matkasoderzhimpolostipravroga;Содержимое полости правого рога;dict;Alvuscontents;false;40
P24_Matkastrukturastenkilevroga;Структура стенки левого рога;dict;Alvusstructure;false;41
P25_Matkasoderzhimpolostilevroga;Содержимое полости левого рога;dict;Alvuscontents;false;42
P0_Organsystem;Система органов;dict;Ultrasoundorgansystem;false;43
P0_Abdomenorgansystem;Система органов;dict;Ultrasoundabdomenorgansystem;false;44
P0_Investigationarea;Зона исследования;dict;Investigationarea;false;45
P18_Analysisdate;Дата взятия пробы;dttm;;false;46
P22_Parasdiseasesanswerdate;Дата отправки ответа;dttm;;false;47
P39_Coproanswerdate;Дата ответа;dttm;0;false;48
P66_UrinAnswerdate;Дата ответа;dttm;;false;49
P10_Petbirthday;Возраст животного;dttm;;true;50
P19_Petregexpiredate;Информация о снятии животного с регистрации;dttm;;true;51
P3_Visitstartdate;Дата;dttm;;true;52
P108_Issuedate;Дата выдачи;dttm;;false;53
P23_Cytologicscreeninganswerdate;Дата отправки ответа;dttm;;false;54
P15_Vacexpirationdate;Дата окончания действия вакцины;dttm;;false;55
P0_RgraphyFile;Вложение;file;Rgraphy;false;56
P0_UltrasoundFile;Вложение;file;Ultrasound;false;57
P0_ScreenEchoFile;Вложение;file;ScreenEcho;false;58
P0_UltrasoundScreenFile;Вложение;file;UltrasoundScreen;false;59
P0_CTScanFile;Вложение;file;CTScan;false;60
P0_EchoFile;Вложение;file;Echo;false;61
P13_Servicedetail;;text;255;false;62
P0_Expdiagnosglucosevalue;Уровень глюкозы (ммоль/л);numeric;2,1;false;63
P0_Petweight;Вес животного (кг);numeric;4,2;false;64
P0_Petwoollength;Длина шерсти животного (см);numeric;2;false;65
P0_Schirmertestresult;Результат теста;numeric;2;false;66
P12_Pc;P в c;numeric;2,2;false;67
P13_Pmv;P в mD;numeric;2,2;false;68
P14_Odrazmerperednegootrezka;Размер переднего отрезка оси (od);numeric;3,2;false;69
P14_Predstzhelezarazmer;Предстательная железа: Размеры;numeric;4,2;false;70
P14_Р1;Р (I);numeric;2,2;false;71
P15_Odrazmerzadnegootrezka;Размер заднего отрезка оси (od);numeric;3,2;false;72
P15_P2;P (II);numeric;2,2;false;73
P15_Pechenkontur;Печень: Контуры;text;100;false;74
P15_Predstzhelezakontur;Предстательная железа: Контуры;numeric;4,2;false;75
P15_Prvpochgranica;Правая почка: Границы;text;100;false;76
P16_P3;P (III);numeric;2,2;false;77
P16_Pechenrazmer;Печень: Размеры;dict;Ultrasoundsystemsize;false;78
P16_Prvpochrazmer;Правая почка: Размеры;text;100;false;79
P17_Odrazmerhrust;Размеры хрусталика (od);numeric;3,2;false;80
P17_Pechenekhostruktura;Печень: Эхоструктура;text;100;false;81
P17_Pq;P-Q;numeric;2,2;false;82
P17_Predstzhelezaobyemnobrazov;Предстательная железа: Объемные образования;text;100;false;83
P17_Prvpochkortiksloytolshina;Правая почка: кортикальный слой - Толщина;numeric;4,2;false;84
P18_Pravsemrazmer;Правый семенник: Размеры;text;100;false;85
P18_Qrs;QRS;numeric;2,2;false;86
P19_Pravsemkontur;Правый семенник: Контуры;numeric;4,2;false;87
P19_Prvpochkortiksloyekhostruktura;Правая почка: кортикальный слой - Эхоструктура;text;100;false;88
P20_Pechenportae;Печень: v. portae;text;100;false;89
P20_Prvpochmedullyarsloytolshchina;Правая почка: Медуллярный слой - Толщина;numeric;4,2;false;90
P20_Rmv;R;numeric;2,2;false;91
P21_Oddiametrzrachka;Диаметр зрачкового отверстия (od);numeric;3,2;false;92
P21_Pechenvhepatica;Печень: v. hepatica ;text;100;false;93
P21_Pravsemobyemnobrazov;Правый семенник: Объемные образования;text;100;false;94
P22_Pechenahepatica;Печень: a. hepatica ;text;100;false;95
P22_Pridatokpravsemgolovka;Придаток правого семенника: Головка;numeric;4,2;false;96
P22_Prvpochmedullyarsloyekhostruktura;Правая почка: Медуллярный слой - Эхоструктура;text;100;false;97
P22_Tmv;T;numeric;2,2;false;98
P23_Pechenobyemnobrazov;Печень: Объемные образования ;text;100;false;99
P23_Pridatokpravsemtelo;Придаток правого семенника: Тело;numeric;4,2;false;100
P23_Prvpochmedullyarsloykortmeddiffer;Правая почка: Медуллярный слой - Кортико-медуллярная дифференциация;text;100;false;101
P24_Pridatokpravsemobyemnobrazov;Придаток правого семенника: Объемные образования;text;100;false;102
P24_Prvpochpiyelicheskiyindeks;Правая почка: Паренхимо-пиелический индекс;numeric;4,2;false;103
P24_St;S-T;numeric;2,2;false;104
P24_Zhelchpuzyrstepennapolneniya;Желчный пузырь: Степень наполнения;text;100;false;105
P25_Levsemrazmer;Левый семенник: Размеры;numeric;4,2;false;106
P25_Qt;Q-T;numeric;2,2;false;107
P26_Levsemkontur;Левый семенник: Контуры;numeric;4,2;false;108
P26_Osrazmerperednegootrezka;Размер переднего отрезка оси (os);numeric;3,2;false;109
P26_Prvpochpochsinuschetkostdifferents;Правая почка: Почечный синус - Четкость дифференциации;text;100;false;110
P26_Zhelchpuzyrtolshchinastenki;Желчный пузырь: Толщина стенки;numeric;4,2;false;111
P27_Chss;ЧСС;numeric;3;false;112
P27_Osrazmerzadnegootrezka;Размер заднего отрезка оси (os);numeric;3,2;false;113
P27_Prvpochpochsinuspolostlokhanki;Правая почка: Почечный синус - Полость лоханки;text;100;false;114
P28_Levsemobyemnobrazov;Левый семенник: Объемные образования;text;100;false;115
P28_Prvpochpochsinusstepenlokhanki;Правая почка: Почечный синус - Стенки лоханки;text;100;false;116
P28_Ritm;Ритм;numeric;3;false;117
P29_Ekstrasistoly;Экстрасистолы;numeric;3;false;118
P29_Osrazmerhrust;Размеры хрусталика (os);numeric;3,2;false;119
P29_Pridatoklevsemgolovka;Придаток левого семенника: Головка;numeric;4,2;false;120
P29_Prvpochsosudyparenkhimy;Правая почка: Сосуды паренхимы;text;100;false;121
P29_Zhelchpuzyrpuzyrprotok;Желчный пузырь: Пузырный проток;text;100;false;122
P30_Pridatoklevsemtelo;Придаток левого семенника: Тело;numeric;4,2;false;123
P30_PrvpochIndeksrezistivnpochechnart;Правая почка: Индекс резистивности на участке почечной артерии;numeric;4,2;false;124
P30_Zhelchpuzyrobshzhelchprotok;Желчный пузырь: Общий желчный проток;text;100;false;125
P31_Pridatoklevsemobyemnobrazov;Придаток левого семенника: Объемные образования;text;100;false;126
P31_PrvpochIndeksrezistivnmezhdolevoyart;Правая почка: Индекс резистивности на участке междолевой артерии;numeric;4,2;false;127
P31_Zhelchpuzyrpechenochnprotok;Желчный пузырь: Печеночные протоки;text;100;false;128
P32_Prvpochkonkrementy;Правая почка: Конкременты;text;100;false;129
P32_Zhelchpuzyrobyemnobrazov;Желчный пузырь: Объемные образования;text;100;false;130
P33_Osdiametrzrachka;Диаметр зрачкового отверстия (os);numeric;3,2;false;131
P33_Prvpochobyemnobrazov;Правая почка: Объемные образования;text;100;false;132
P34_Selezenkakontur;Селезенка: Контуры;text;100;false;133
P35_Levpochgranica;Левая почка: Границы;text;100;false;134
P35_Selezenkarazmer;Селезенка: Размеры;dict;Ultrasoundsystemsize;false;135
P36_Levpochrazmer;Левая почка: Размеры;text;100;false;136
P36_Selezenkaekhostruktura;Селезенка: Эхоструктура;text;100;false;137
P37_Levpochkortiksloytolshina;Левая почка: кортикальный слой - Толщина;numeric;4,2;false;138
P39_Levpochkortiksloyekhostruktura;Левая почка: кортикальный слой - Эхоструктура;text;100;false;139
P39_Selezenkaobyemnobrazov;Селезенка: Объемные образования;text;100;false;140
P40_Levpochmedullyarsloytolshchina;Левая почка: Медуллярный слой - Толщина;numeric;4,2;false;141
P41_Podzhelzhelezakontur;Поджелудочная железа: Контуры;text;100;false;142
P42_Levpochmedullyarsloyekhostruktura;Левая почка: Медуллярный слой - Эхоструктура;text;100;false;143
P42_Podzhelzhelezarazmer;Поджелудочная железа: Размеры;dict;Ultrasoundsystemsize;false;144
P43_Levpochmedullyarsloykortmeddiffer;Левая почка: Медуллярный слой - Кортико-медуллярная дифференциация;text;100;false;145
P43_Podzhelzhelezaekhostruktura;Поджелудочная железа: Эхоструктура;text;100;false;146
P44_Levpochpiyelicheskiyindeks;Левая почка: Паренхимо-пиелический индекс;numeric;4,2;false;147
P45_Podzhelzhelezaobyemnobrazov;Поджелудочная железа: Объемные образования;text;100;false;148
P46_Levpochpochsinuschetkostdifferents;Левая почка: Почечный синус - Четкость дифференциации;text;100;false;149
P47_Levpochpochsinuspolostlokhanki;Левая почка: Почечный синус - Полость лоханки;text;100;false;150
P47_Svobodnzhidkost;Свободная жидкость в брюшной полости;text;100;false;151
P48_Levpochpochsinusstenkilokhanki;Левая почка: Почечный синус - Стенки лоханки;text;100;false;152
P49_Levpochsosudyparenkhimy;Левая почка: Сосуды паренхимы;text;100;false;153
P50_LevpochIndeksrezistivnpochechnart;Левая почка: Индекс резистивности на участке почечной артерии;numeric;4,2;false;154
P51_LevpochIndeksrezistivnmezhdolevoyart;Левая почка: Индекс резистивности на участке междолевой артерии;numeric;4,2;false;155
P52_Levpochkonkrementy;Левая почка: Конкременты;text;100;false;156
P53_Levpochobyemnobrazov;Левая почка: Объемные образования;text;100;false;157
P54_Mochpuzstepnapoln;Мочевой пузырь: Степень наполнения;text;100;false;158
P55_Mochpuztolshchinastenki;Мочевой пузырь: Толщина стенки;numeric;4,2;false;159
P57_Mochpuzuretra;Уретра;numeric;4,2;false;160
P58_MochpuzObyemnobrazov;Объемные образования;text;100;false;161
P14_Matkadiametrtela;Матка - Диаметр тела;numeric;4,2;false;162
P15_Matkatolshinatela;Матка - Толщина стенки тела;numeric;4,2;false;163
P18_Matkadiametrpravroga;Диаметр правого рога;numeric;4,2;false;164
P19_Matkatolshinapravroga;Толщина стенки правого рога;numeric;4,2;false;165
P22_Matkadiametrlevroga;Диаметр левого рога;numeric;4,2;false;166
P23_Matkatolshinalevroga;Толщина стенки левого рога;numeric;4,2;false;167
P26_Pravyaichnikrazmer;Правый яичник - Размеры;numeric;4,2;false;168
P29_Levyaichnikrazmer;Левый яичник - Размеры;numeric;4,2;false;169
P0_VisitServiceTMCcount;Доза ;numeric;2;false;170
P14_LVIDd;LVIDd;numeric;2,2;false;171
P15_LVIDs;LVIDs;numeric;2,2;false;172
P16_LVWTd;LVWTd;numeric;2,2;false;173
P17_LVWTs;LVWTs;numeric;2,2;false;174
P18_IVSTd;IVSTd;numeric;2,2;false;175
P19_IVSTs;IVSTs;numeric;2,2;false;176
P20_EF;EF;numeric;2,2;false;177
P21_FS;FS;numeric;2,2;false;178
P22_LA;LA;numeric;2,2;false;179
P23_AO;AO;numeric;2,2;false;180
P24_LA/AO;LA/AO;numeric;2,2;false;181
P25_RVIDd;RVIDd ;numeric;2,2;false;182
P26_RVIDs;RVIDs;numeric;2,2;false;183
P27_RVWTd;RVWTd;numeric;2,2;false;184
P28_RVWTs;RVWTs;numeric;2,2;false;185
P29_RA;RA;numeric;2,2;false;186
P30_Defektivs;Дефект IVS;numeric;2,2;false;187
P31_Defektias;Дефект IAS;numeric;2,2;false;188
P32_Svobodnzhidkostperikarde;Свободная жидкость в перикарде;numeric;4,2;false;189
P33_Svobodnzhidkostplevralpolosti;Свободная жидкость в плевральной полости;numeric;4,2;false;190
P34_Novoobrazov;Новообразования;text;100;false;191
P35_Mitrklapnstvorki;Митральный клапан: Створки;numeric;4,2;false;192
P36_Mitrklapnskorostkrovotoka;Митральный клапан: Скорость кровотока;numeric;4,2;false;193
P37_Mitrklapnregurgitatsiya;Митральный клапан: Регургитация;numeric;4,2;false;194
P38_Trikuspklapnstvorki;Трикуспидальный клапан: Створки;numeric;4,2;false;195
P39_Trikuspklapnskorostkrovotoka;Трикуспидальный клапан: Скорость кровотока;numeric;4,2;false;196
P40_Trikuspklapnregurgitatsiya;Трикуспидальный клапан: Регургитация;numeric;4,2;false;197
P41_Aortaklapnstvorki;Аортальный клапан: Створки;numeric;4,2;false;198
P42_Aortaklapnskorostkrovotoka;Аортальный клапан: Скорость кровотока;numeric;4,2;false;199
P43_Aortaklapnregurgitatsiya;Аортальный клапан: Регургитация;numeric;4,2;false;200
P44_Klapnlegartstvorki;Клапан легочной артерии: Створки;numeric;4,2;false;201
P45_Klapnlegartskorostkrovotoka;Клапан легочной артерии: Скорость кровотока;numeric;4,2;false;202
P46_Klapnlegartregurgitatsiya;Клапан легочной артерии: Регургитация;numeric;4,2;false;203
P14_Analysiscount;Количество проб;text;15;false;204
P19_Animalcount;Количество животных в гурте, отаре, группе;text;15;false;205
P14_Cortisolbazalvalue;Кортизол базальный;text;15;false;206
P15_Cortisolactgvalue;Кортизол после стимуляции АКТГ;text;15;false;207
P16_Cortisoldexvalue;Кортизол 8ч после дексаметазоновой пробы (0,01-0,015 мг/кг);text;15;false;208
P17_Proganesvalue;Прогестерон - анэструс;text;15;false;209
P18_Progproenstvalue;Прогестерон - проэструс;text;15;false;210
P19_Progestrusvalue;Прогестерон - эструс;text;15;false;211
P20_Progmetestvalue;Прогестерон - метэструс;text;15;false;212
P26_Testostervalue;Тестостерон (самцы);text;15;false;213
P27_Thyroxvalue;Тироксин (Т4);text;15;false;214
P28_Triiodtirvalue;Трийодтиронин (Т3);text;15;false;215
P21_Estradanesvalue;Эстрадиол проэструс - анэструс;text;15;false;216
P22_Estradproenstvalue;Эстрадиол проэструс - проэструс;text;15;false;217
P23_Estradestrusvalue;Эстрадиол проэструс - эструс;text;15;false;218
P24_Estradmetestvalue;Эстрадиол проэструс - метэструс;text;15;false;219
P25_Estradmalevalue;Эстрадиол проэструс - самцы;text;15;false;220
P0_Capillarybloodanalysisnum;№ пробы крови из капилляра;text;50;false;221
P15_Activator;Название выделенного возбудителя и его характеристика;text;255;false;222
P15_Analysisresult;Ход и результат исследования;text;255;false;223
P16_Analysisdesc;Примечание;text;255;false;224
P20_Analysisobject;На что исследовалось;text;255;false;225
P21_Diagnostictechnique;Метод исследования;text;255;false;226
P0_Venousbloodanalysisnum;№ пробы крови из вены;text;50;false;227
P20_Acidityvalue;Кислотность - результат исследования;text;50;false;228
P22_Stercobilinvalue;Стеркобилин - результат исследования;text;50;false;229
P15_Coprformdesc;Консистенция, форма - примечание;text;255;false;230
P17_Coprcolordesc;Цвет - примечание;text;255;false;231
P19_Coprodordesc;Запах - примечание;text;255;false;232
P21_Aciditydesc;Кислотность - примечание;text;255;false;233
P23_Stercobilindesc;Стеркобилин - примечание;text;255;false;234
P25_Bilirubindesc;Билирубин - примечание;text;255;false;235
P27_Blooddesc;Кровь - примечание;text;255;false;236
P29_Muscledfibersdesc;мышечные волокна - примечание;text;255;false;237
P31_Contissuefibersdesc;соединительно-тканные волокна - примечание;text;255;false;238
P33_Neutralfatdesc;нейтральный жир - примечание;text;255;false;239
P34_Fattyacidsvalue;жирные кислоты - результат исследования;text;255;false;240
P35_Fattyacidsdesc;жирные кислоты - примечание;text;255;false;241
P37_Soapdesc;мыла - примечание;text;255;false;242
P39_Starchdesc;крахмал - примечание;text;255;false;243
P0_Coproalysisnum;№ пробы;text;50;false;244
P18_Acidityvalue;Кислотность (рН) - результат исследования;text;50;false;245
P20_Proteinvalue;Белок - результат исследования;text;50;false;246
P22_Glukozavalue;Глюкоза - результат исследования;text;50;false;247
P24_Ketonbodvalue;Кетоновые тела - результат исследования;text;50;false;248
P26_Relativedensityvalue;Относительная плотность - результат исследования;text;50;false;249
P28_Bilirubinvalue;Билирубин - результат исследования;text;50;false;250
P30_Hemeglvalue;Гемоглобин - результат исследования;text;50;false;251
P32_Erythrocytvalue;Эритроциты - результат исследования;text;50;false;252
P34_Leucocytvalue;Лейкоциты - результат исследования;text;50;false;253
P36_Ploskiyvalue;Эпителий - Плоский - результат исследования;text;50;false;254
P38_Perehodvalue;Эпителий - переходный - результат исследования;text;50;false;255
P40_Pochechnvalue;Эпителий - почечный - результат исследования;text;50;false;256
P42_Hyalinevalue;Цилиндры гиалиновые - результат исследования;text;50;false;257
P44_Granularvalue;Цилиндры Зернистые - результат исследования;text;50;false;258
P46_Waxvalue;Цилиндры восковидные - результат исследования;text;50;false;259
P48_Lekocitvalue;Цилиндры лейкоцитарные - результат исследования;text;50;false;260
P50_Eritrocitvalue;Цилиндры эритроцитарные - результат исследования;text;50;false;261
P52_Epitelvalue;Цилиндры эпителиальные - результат исследования;text;50;false;262
P54_Cilindvalue;Цилиндры цилиндроиды - результат исследования;text;50;false;263
P56_Bacteriavalue;Бактерии - результат исследования;text;50;false;264
P58_Saltvalue;Соли - результат исследования;text;50;false;265
P61_Urinunitweightvalue;Удельный вес ;text;50;false;266
P63_Nitratvalue;Нитраты ;text;50;false;267
P15_Colorurinedesc;Цвет мочи - примечание;text;255;false;268
P17_Transparencydesc;Прозрачность - примечание;text;255;false;269
P19_Aciditydesc;Кислотность (рН) - примечание;text;255;false;270
P21_Proteindesc;Белок - примечание;text;255;false;271
P23_Glukozadesc;Глюкоза - примечание;text;255;false;272
P25_Ketonboddesc;Кетоновые тела - примечание;text;255;false;273
P27_Relativedensitydesc;Относительная плотность - примечание;text;255;false;274
P29_Bilirubindesc;Билирубин - примечание;text;255;false;275
P31_Hemegldesc;Гемоглобин - примечание;text;255;false;276
P33_Erythrocytdesc;Эритроциты - примечание;text;255;false;277
P35_Leucocytdesc;Лейкоциты - примечание;text;255;false;278
P37_Ploskiydesc;Эпителий - Плоский - примечание;text;255;false;279
P39_Perehoddesc;Эпителий - переходный - примечание;text;255;false;280
P41_Pochechndesc;Эпителий - почечный - примечание;text;255;false;281
P43_Hyalinedesc;Цилиндры гиалиновые - примечание;text;255;false;282
P45_Granulardesc;Цилиндры Зернистые - примечание;text;255;false;283
P47_Waxdesc;Цилиндры восковидные - примечание;text;255;false;284
P49_Lekocitdesc;Цилиндры лейкоцитарные - примечание;text;255;false;285
P51_Eritrocitdesc;Цилиндры эритроцитарные - примечание;text;255;false;286
P53_Epiteldesc;Цилиндры эпителиальные - примечание;text;255;false;287
P55_Cilinddesc;Цилиндры цилиндроиды - примечание;text;255;false;288
P57_Bacteriadesc;Бактерии - примечание;text;255;false;289
P59_Saltdesc;Соли - примечание;text;255;false;290
P62_Urinreactionvalue;Реакция ;text;255;false;291
P64_Urinorddepositionvalue;Организованный осадок;text;255;false;292
P65_Urindisorddepositionvalue;Неорганизованный осадок;text;255;false;293
P67_Urinconsistency;Консистенция;text;255;false;294
P0_Urinalysisnum;№ пробы;text;50;false;295
P32_Creatininemgvalue;креатинин (мг/дл) - результат исследований;text;15;false;296
P48_Kfkcreatinevalue;КФК креатинфосфокиназа - результат исследований;text;15;false;297
P44_Ldglactodvalue;ЛДГ лактадегидрогиназа - результат исследований;text;15;false;298
P92_Lipazavalue;липаза - результат исследований;text;15;false;299
P78_Magnesiummmvalue;магний (ммоль/л) - результат исследований;text;15;false;300
P80_Magnesiummecvalue;магний (мэкв/дл) - результат исследований;text;15;false;301
P104_Ketonebodiesvalue;Кетоновые тела;text;15;false;302
P26_Mochevinammvalue;мочевина (ммоль/л) - результат исследований;text;15;false;303
P28_Mochevinamgvalue;мочевина (мг/дл) - результат исследований;text;15;false;304
P105_Bikarbonatvalue;Бикарботаны;text;15;false;305
P88_Mochekislnmvalue;мочевая кислота (нмоль/л) - результат исследований;text;15;false;306
P90_Mochekislmgvalue;мочевая кислота (мг/дл) - результат исследований;text;15;false;307
P62_Natrmmvalue;натрий (ммоль/л) - результат исследований;text;15;false;308
P64_Natrmecvalue;натрий (мэкв/дл) - результат исследований;text;15;false;309
P82_Chloridemmvalue;хлорид (ммоль/л) - результат исследований;text;15;false;310
P84_Chloridemecvalue;хлорид (мэкв/дл) - результат исследований;text;15;false;311
P86_Acidityvalue;кислотность - результат исследований;text;15;false;312
P94_Totalproteinglvalue;Общий белок (г/л) - результат исследований;text;15;false;313
P96_Totalproteingdlvalue;Общий белок (г/дл) - результат исследований;text;15;false;314
P14_Totalbilirubinmcmvalue;билирубин общий (мкмоль/л) - результат исследований;text;15;false;315
P16_Totalbilirubinmgvalue;билирубин общий (мг/дл) - результат исследований;text;15;false;316
P18_Conjugatedbilirubinmcmvalue;билирубин прямой (мкмоль/л) - результат исследований;text;15;false;317
P20_Conjugatedbilirubinmgvalue;билирубин прямой (мг/дл) - результат исследований;text;15;false;318
P50_Holestermmvalue;Холестерол (ммоль/л) - результат исследований;text;15;false;319
P52_Holestermgvalue;Холестерол (мг/дл) - результат исследований;text;15;false;320
P54_Triglyceridsmmvalue;Триглицериды (ммоль/л) - результат исследований;text;15;false;321
P56_Triglyceridsmgvalue;Триглицериды (мг/дл) - результат исследований;text;15;false;322
P66_Phosphormmvalue;фосфор (ммоль/л) - результат исследований;text;15;false;323
P68_Phosphormgvalue;фосфор (мг/дл) - результат исследований;text;15;false;324
P34_Shelochfosfatvalue;Щелочная фосфатаза - результат исследований;text;15;false;325
P22_Altalanniamvalue;АЛТ аланинаминотрансфераза - результат исследований;text;15;false;326
P23_Altalanniamvadesc;АЛТ аланинаминотрансфераза - примечание;text;255;false;327
P36_Amilazavalue;?- амилаза - результат исследований;text;15;false;328
P37_Amilazadesc;?- амилаза - примечание;text;255;false;329
P38_Pancreatinevalue;Панкреатическая амилаза - результат исследований;text;15;false;330
P39_Pancreatinedesc;Панкреатическая амилаза - примечание;text;255;false;331
P24_Astaspartvalue;АСТ аспартатаминотрансфераза - результат исследований;text;15;false;332
P25_Astaspartdesc;АСТ аспартатаминотрансфераза - примечание;text;255;false;333
P100_Albumingdlvalue;Альбумины (г/дл) - результат исследований;text;15;false;334
P101_Albumingdldesc;Альбумины (г/дл) - примечание;text;255;false;335
P106_Proteinfractionsvalue;Белковые фракции;text;15;false;336
P98_Albuminglvalue;Альбумины (г/л) - результат исследований;text;15;false;337
P99_Albumingldesc;Альбумины (г/л) - примечание;text;255;false;338
P46_Lgtgammavalue;ГГТ гамма-глутамилтрансфераза - результат исследований;text;15;false;339
P47_Lgtgammadesc;ГГТ гамма-глутамилтрансфераза - примечание;text;255;false;340
P102_Hemoglobinvalue;Гемоглобин - результат исследований;text;15;false;341
P103_Hemoglobindesc;Гемоглобин - примечание;text;255;false;342
P40_Glukozamcmvalue;глюкоза (мкмоль/л) - результат исследований;text;15;false;343
P41_Glukozamcmdesc;глюкоза (мкмоль/л) - примечание;text;255;false;344
P42_Glukozamgvalue;глюкоза (мг/дл) - результат исследований;text;15;false;345
P43_Glukozamgdesc;глюкоза (мг/дл) - примечание;text;255;false;346
P74_Ironmcmvalue;железо (мкмоль/л) - результат исследований;text;15;false;347
P75_Ironmcmdesc;железо (мкмоль/л) - примечание;text;255;false;348
P76_Ironmcgvalue;железо (мкг/дл) - результат исследований;text;15;false;349
P77_Ironmcgdesc;железо (мкг/дл) - примечание;text;255;false;350
P58_Caliummmvalue;калий (ммоль/л) - результат исследований;text;15;false;351
P59_Caliummmdesc;калий (ммоль/л) - примечание;text;255;false;352
P60_Caliummecvalue;калий (мэкв/дл) - результат исследований;text;15;false;353
P61_Caliummecdesc;калий (мэкв/дл) - примечание;text;255;false;354
P70_Calciummmvalue;кальций (ммоль/л) - результат исследований;text;15;false;355
P71_Calciummmdesc;кальций (ммоль/л) - примечание;text;255;false;356
P72_Calciummcgvalue;кальций (мг/дл) - результат исследований;text;15;false;357
P73_Calciummcgdesc;кальций (мг/дл) - примечание;text;255;false;358
P30_Creatininemcmvalue;креатинин (мкмоль/л) - результат исследований;text;15;false;359
P31_Creatininemcmdesc;креатинин (мкмоль/л) - примечание;text;255;false;360
P33_Creatininemgdesc;креатинин (мг/дл) - примечание;text;255;false;361
P49_Kfkcreatinedesc;КФК креатинфосфокиназа - примечание;text;255;false;362
P45_Ldglactoddesc;ЛДГ лактадегидрогиназа - примечание;text;255;false;363
P93_Lipazadesc;липаза - примечание;text;255;false;364
P79_Magnesiummmdesc;магний (ммоль/л) - примечание;text;255;false;365
P81_Magnesiummecdesc;магний (мэкв/дл) - примечание;text;255;false;366
P27_Mochevinammdesc;мочевина (ммоль/л) - примечание;text;255;false;367
P29_Mochevinamgdesc;мочевина (мг/дл) - примечание;text;255;false;368
P89_Mochekislnmdesc;мочевая кислота (нмоль/л) - примечание;text;255;false;369
P91_Mochekislmgdesc;мочевая кислота (мг/дл) - примечание;text;255;false;370
P63_Natrmmdesc;натрий (ммоль/л) - примечание;text;255;false;371
P65_Natrmecdesc;натрий (мэкв/дл) - примечание;text;255;false;372
P83_Chloridemmdesc;хлорид (ммоль/л) - примечание;text;255;false;373
P85_Chloridemecdesc;хлорид (мэкв/дл) - примечание;text;255;false;374
P87_Aciditydesc;кислотность - примечание;text;255;false;375
P95_Totalproteingldesc;Общий белок (г/л) - примечание;text;255;false;376
P97_Totalproteingdldesc;Общий белок (г/дл) - примечание;text;255;false;377
P15_Totalbilirubinmcmdesc;билирубин общий (мкмоль/л) - примечание;text;255;false;378
P17_Totalbilirubinmgdesc;билирубин общий (мг/дл) - примечание;text;255;false;379
P19_Conjugatedbilirubinmcmdesc;билирубин прямой (мкмоль/л) - примечание;text;255;false;380
P21_Conjugatedbilirubinmgdesc;билирубин прямой (мг/дл) - примечание;text;255;false;381
P51_Holestermmdesc;Холестерол (ммоль/л) - примечание;text;255;false;382
P53_Holestermgdesc;Холестерол (мг/дл) - примечание;text;255;false;383
P55_Triglyceridsmmdesc;Триглицериды (ммоль/л) - примечание;text;255;false;384
P57_Triglyceridsmgdesc;Триглицериды (мг/дл) - примечание;text;255;false;385
P67_Phosphormmdesc;фосфор (ммоль/л) - примечание;text;255;false;386
P69_Phosphormgdesc;фосфор (мг/дл) - примечание;text;255;false;387
P35_Shelochfosfatdesc;Щелочная фосфатаза - примечание;text;255;false;388
P14_Wbcvalue;WBC (общее кол-во лейкоцитов) - результат исследования;text;15;false;389
P15_Wbcdesc;WBC (общее кол-во лейкоцитов) - примечание;text;255;false;390
P16_Lymvalue;LYM (лимфоциты) - результат исследования;text;15;false;391
P17_Lymdesc;LYM (лимфоциты) - примечание;text;255;false;392
P18_Monvalue;MON (моноциты) - результат исследования;text;15;false;393
P19_Mondesc;MON (моноциты) - примечание;text;255;false;394
P20_Gravalue;GRA (гранулоциты) - результат исследования;text;15;false;395
P21_Gradesc;GRA (гранулоциты) - примечание;text;255;false;396
P22_Rbcvalue;RBC (общее кол-во эритроцитов) - результат исследования;text;15;false;397
P23_Rbcdesc;RBC (общее кол-во эритроцитов) - примечание;text;255;false;398
P24_Hgbvalue;HGB (гемоглобин) - результат исследования;text;15;false;399
P25_Hgbdesc;HGB (гемоглобин) - примечание;text;255;false;400
P26_Hctvalue;HCT (гематокрит) - результат исследования;text;15;false;401
P27_Hctdesc;HCT (гематокрит) - примечание;text;255;false;402
P28_Mcvvalue;MCV (средний объем эритроцита) - результат исследования;text;15;false;403
P29_Mcvdesc;MCV (средний объем эритроцита) - примечание;text;255;false;404
P30_Mchvalue;MCH (сод. гемоглобина в 1 эритроците) - результат исследования;text;15;false;405
P31_Mchdesc;MCH (сод. гемоглобина в 1 эритроците) - примечание;text;255;false;406
P32_Mchcvalue;MCHC (конц. гемоглобина в 1 эритроците) - результат исследования;text;15;false;407
P33_Mchcdesc;MCHC (конц. гемоглобина в 1 эритроците) - примечание;text;255;false;408
P34_Rdwvalue;RDW (ширина распределения эритроцитов) - результат исследования;text;15;false;409
P35_Rdwdesc;RDW (ширина распределения эритроцитов) - примечание;text;255;false;410
P36_Pltvalue;PLT (тромбоциты) - результат исследования;text;15;false;411
P37_Pltdesc;PLT (тромбоциты) - примечание;text;255;false;412
P38_Mpvvalue;MPV (средний объем тромбоцита) - результат исследования;text;15;false;413
P39_Mpvdesc;MPV (средний объем тромбоцита) - примечание;text;255;false;414
P40_Pctvalue;PCT (тромбокрит) - результат исследования;text;15;false;415
P41_Pctdesc;PCT (тромбокрит) - примечание;text;255;false;416
P42_Pdwvalue;PDW (ширина распределения тромбоцитов) - результат исследования;text;15;false;417
P43_Pdwdesc;PDW (ширина распределения тромбоцитов) - примечание;text;255;false;418
P44_Soevalue;СОЭ - результат исследования;text;15;false;419
P45_Soedesc;СОЭ - примечание;text;255;false;420
P46_Youngvalue;Нейтрофилы - Юные - результат исследования;text;15;false;421
P47_Youngdesc;Нейтрофилы - Юные - примечание;text;255;false;422
P48_Palochkoyadervalue;Нейтрофилы - Палочкоядерные - результат исследования;text;15;false;423
P49_Palochkoyaderdesc;Нейтрофилы - Палочкоядерные - примечание;text;255;false;424
P50_Segmentvalue;Нейтрофилы - Сегментоядерные - результат исследования;text;15;false;425
P51_Segmentdesc;Нейтрофилы - Сегментоядерные - примечание;text;255;false;426
P52_Eosinophilsvalue;Эозинофилы - результат исследования;text;15;false;427
P53_Eosinophilsdesc;Эозинофилы - примечание;text;255;false;428
P54_Monocitvalue;Моноциты - результат исследования;text;15;false;429
P55_Monocitdesc;Моноциты - примечание;text;255;false;430
P56_Bazophilvalue;Базофилы - результат исследования;text;15;false;431
P57_Bazophildesc;Базофилы - примечание;text;255;false;432
P58_Limphocitvalue;Лимфоциты - результат исследования;text;15;false;433
P59_Limphocitdesc;Лимфоциты - примечание;text;255;false;434
P14_Bazalvalue;базальные;text;50;false;435
P15_Parabazalvalue;парабазальные;text;50;false;436
P16_Promezhutvalue;Промежуточные;text;50;false;437
P17_Poverkhvalue;поверхностные;text;50;false;438
P18_Leukocytesvalue;Лейкоциты;text;50;false;439
P19_Erythrocytesvalue;Эритроциты;text;50;false;440
P21_Fazatsiklavalue;Фаза цикла;text;50;false;441
P0_Petchpidentificationcode;№ чипа;text;50;true;442
P0_Petlabelidentificationcode;№ бирки;text;50;true;443
P0_Petstampidentificationcode;Клеймо;text;50;true;444
P8_Petsex;Пол животного;text;1;true;445
P0_Biopsycytologicsanalysisnum;№ пробы;text;50;false;446
P0_Cytologicsanalysisnum;№ мазка - отпечатка;text;50;false;447
P0_Diagnostictestinganalysisnum;№ пробы;text;50;false;448
P12_Analysisnum;Номер анализа;text;50;false;449
P12_Serviceresultvalue;Результат исследования;text;255;false;450
P13_Serviceresultdesc;Примечание;text;255;false;451
P13_Servicetext;Описание;text;255;false;452
P16_Odstructuraperedcamer;Структура передней камеры (od);text;100;false;453
P18_Odstructurahrust;Структура хрусталика (od);text;100;false;454
P19_Odcapsulahrust;Капсула хрусталика (od);text;100;false;455
P19_Qrsdesc;QRS - Примечание;text;50;false;456
P20_Odstructurasteklotelo;Структура стекловидного тела (od);text;100;false;457
P21_Rdesc;R - Примечание;text;50;false;458
P22_Odcontur;Задняя стенка глазного яблока (od) - Контуры;text;100;false;459
P22_Recomendvalue;Рекомендации;text;255;false;460
P23_Odstructura;Задняя стенка глазного яблока (od) - Структура;text;100;false;461
P23_Tdesc;T - Примечание;text;50;false;462
P24_Odstructuradiskazritnerva;Задняя стенка глазного яблока (od) - Структура диска зрительного нерва;text;100;false;463
P25_Odstructuraretrobulyar;Структура ретробульбарного пространства (od);text;100;false;464
P26_Eos;ЭОС;text;4;false;465
P28_Osstructuraperedcamer;Структура передней камеры (os);text;100;false;466
P30_Osstructurahrust;Структура хрусталика (os);text;100;false;467
P30_Serviceresult;Заключение;text;255;false;468
P31_Oscapsulahrust;Капсула хрусталика (os);text;100;false;469
P32_Osstructurasteklotelo;Структура стекловидного тела (os);text;100;false;470
P32_Abdomultmserviceresult;Заключение;text;255;false;471
P34_Oscontur;Задняя стенка глазного яблока (os) - Контуры;text;100;false;472
P35_Osstructura;Задняя стенка глазного яблока (os) - Структура;text;100;false;473
P36_Osstructuradiskazritnerva;Задняя стенка глазного яблока (os) - Структура диска зрительного нерва;text;100;false;474
P37_Osstructuraretrobulyar;Структура ретробульбарного пространства (os);text;100;false;475
P38_Serviceresult;Заключение;text;255;false;476
P46_Zheludkishechntrakt;Желудочно-кишечный тракт ;text;100;false;477
P48_Serviceresult;Заключение;text;255;false;478
P59_Serviceresult;Заключение;text;255;false;479
P27_Pravyaichnikkontur;Правый яичник - Контуры;text;50;false;480
P28_Pravyaichniknovoobrazov;Правый яичник - Новообразования;text;100;false;481
P30_Levyaichnikkontur;Левый яичник - Контуры;text;50;false;482
P31_Levyaichniknovoobrazov;Левый яичник - Новообразования;text;100;false;483
P32_Serviceresult;Заключение;text;255;false;484
P0_Juraddress;Адрес выезда;text;250;false;485
P0_Petregnum;Регистрационный № животного;text;50;true;486
P17_Petcolor;Окрас животного;text;255;false;487
P18_Petspecialtrait;Особые приметы животного;text;255;false;488
P33_SpecialistFIO;Специалист;text;250;true;489
P4_Ownername;Наименование организации/ ФИО владельца;text;150;true;490
P5_Owneraddres;Адрес владельца;text;250;true;491
P5_Ownercontact;Телефон владельца;text;50;true;492
P6_Speciesname;Вид животного;text;50;true;493
P7_Breedname;Порода животного;text;100;true;494
P9_Petname;Кличка животного;text;50;true;495
P0_Balanceinventorynumber;Серия вакцины;text;50;false;496
P0_Vaccinename;Наименование вакцины;text;50;false;497
P24_Anamnesis;Анамнез;text;255;false;498
P47_Serviceresult;Заключение;text;255;false;499
P1_Parentorgshortname;Наименование главной организации;text;100;true;500
P13_Orgshortname;Наименование организации;text;100;true;501
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        $not_found = 0;
        $not_changed = 0;
        $changed = 0;
        $updated = 0;

        foreach ($items as $item) {
            $tech_name = $item[0];
            $datatype = $item[2];
            $datatype_details = ($datatype == 'dict') ? strtolower($item[3]) : $item[3];
            $visit_flag = ($item[4] === 'true') ? true : false;

            $param = (new Query())
                ->from(Params::tableName())
                ->where(['tech_name' => $tech_name])
                ->one();

            if (empty($param)) {
                $not_found++;
                Console::output(Console::ansiFormat('Param not found [' . $tech_name . ']', [Console::FG_RED]));
                continue;
            }

            if ($param['datatype'] == $datatype && $param['datatype_details'] == $datatype_details && $param['visit_flag'] === $visit_flag) {
                $not_changed++;
                continue;
            }

            $changed++;

            $id_param = $param['id'];
            $old_columns = [];
            $columns = [];
            if ($param['datatype'] != $datatype) {
                $old_columns['datatype'] = $param['datatype'];
                $columns['datatype'] = $datatype;
            }
            if ($param['datatype_details'] != $datatype_details) {
                $old_columns['datatype_details'] = $param['datatype_details'];
                $columns['datatype_details'] = $datatype_details;
            }
            if ($param['visit_flag'] !== $visit_flag) {
                $old_columns['visit_flag'] = $param['visit_flag'];
                $columns['visit_flag'] = $visit_flag;
            }
            Console::output(Console::ansiFormat('Updating param id ' . $id_param . ' [' . $tech_name . ']: ' . PHP_EOL . VarDumper::dumpAsString($old_columns) . ' --> ' . PHP_EOL . VarDumper::dumpAsString($columns), [Console::FG_YELLOW]));

            $result = $this->db
                ->createCommand()
                ->update(Params::tableName(), $columns, ['id' => $id_param])
                ->execute();

            if ($result) {
                $updated++;
                Console::output(Console::ansiFormat('  - OK', [Console::FG_GREEN]));
                if ($old_columns['datatype'] == 'numeric' && $columns['datatype'] == 'text') {
                    $vals = $this->db
                        ->createCommand(
                            'update visit_service_param_values set char_value = num_value::text where id_param = :id_param',
                            ['id_param' => $id_param]
                        )->execute();
                    Console::output(Console::ansiFormat('  - updated ' . $vals . ' visit_service_param_values', [Console::FG_CYAN]));
                }
            }
        }

        Console::output(Console::ansiFormat('Total:' . count($items), [Console::FG_BLUE]));
        Console::output(Console::ansiFormat('Not found:' . $not_found, [Console::FG_RED]));
        Console::output(Console::ansiFormat('Not changed:' . $not_changed, [Console::FG_YELLOW]));
        Console::output(Console::ansiFormat('Changed:' . $changed, [Console::FG_YELLOW]));
        Console::output(Console::ansiFormat('Updated:' . $updated, [Console::FG_GREEN]));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190124_025358_update_params_datatype cannot be reverted.\n";

        return false;
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
}
