<?php

use app\commands\migrate\Migration;
use app\models\db\Params;

/**
 * Class m181204_035748_update_params_datatype
 */
class m181204_035748_update_params_datatype extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
P14_Analysiscount;Количество проб;text;15;false
P14_Bazalvalue;базальные;text;50;false
P14_Cortisolbazalvalue;Кортизол базальный;text;15;false
P14_Totalbilirubinmcmvalue;билирубин общий (мкмоль/л) - результат исследований;text;15;false
P14_Wbcvalue;WBC (общее кол-во лейкоцитов) - результат исследования;text;15;false
P15_Cortisolactgvalue;Кортизол после стимуляции АКТГ;text;15;false
P15_Parabazalvalue;парабазальные;text;50;false
P16_Cortisoldexvalue;Кортизол 8ч после дексаметазоновой пробы (0,01-0,015 мг/кг);text;15;false
P16_Lymvalue;LYM (лимфоциты) - результат исследования;text;15;false
P16_Promezhutvalue;Промежуточные;text;50;false
P16_Totalbilirubinmgvalue;билирубин общий (мг/дл) - результат исследований;text;15;false
P17_Petcolor;Окрас животного;text;255;false
P17_Poverkhvalue;поверхностные;text;50;false
P17_Proganesvalue;Прогестерон - анэструс;text;15;false
P18_Acidityvalue;Кислотность (рН) - результат исследования;text;50;false
P18_Conjugatedbilirubinmcmvalue;билирубин прямой (мкмоль/л) - результат исследований;text;15;false
P18_Leukocytesvalue;Лейкоциты;text;50;false
P18_Monvalue;MON (моноциты) - результат исследования;text;15;false
P18_Progproenstvalue;Прогестерон - проэструс;text;15;false
P19_Animalcount;Количество животных в гурте, отаре, группе;text;15;false
P19_Erythrocytesvalue;Эритроциты;text;50;false
P19_Progestrusvalue;Прогестерон - эструс;text;15;false
P20_Acidityvalue;Кислотность - результат исследования;text;50;false
P20_Conjugatedbilirubinmgvalue;билирубин прямой (мг/дл) - результат исследований;text;15;false
P20_Gravalue;GRA (гранулоциты) - результат исследования;text;15;false
P20_Progmetestvalue;Прогестерон - метэструс;text;15;false
P20_Proteinvalue;Белок - результат исследования;text;50;false
P21_Estradanesvalue;Эстрадиол проэструс - анэструс;text;15;false
P21_Fazatsiklavalue;Фаза цикла;text;50;false
P22_Altalanniamvalue;АЛТ аланинаминотрансфераза - результат исследований;text;15;false
P22_Estradproenstvalue;Эстрадиол проэструс - проэструс;text;15;false
P22_Glukozavalue;Глюкоза - результат исследования;text;50;false
P22_Rbcvalue;RBC (общее кол-во эритроцитов) - результат исследования;text;15;false
P22_Stercobilinvalue;Стеркобилин - результат исследования;text;50;false
P23_Estradestrusvalue;Эстрадиол проэструс - эструс;text;15;false
P24_Astaspartvalue;АСТ аспартатаминотрансфераза - результат исследований;text;15;false
P24_Estradmetestvalue;Эстрадиол проэструс - метэструс;text;15;false
P24_Hgbvalue;HGB (гемоглобин) - результат исследования;text;15;false
P24_Ketonbodvalue;Кетоновые тела - результат исследования;text;50;false
P25_Estradmalevalue;Эстрадиол проэструс - самцы;text;15;false
P26_Hctvalue;HCT (гематокрит) - результат исследования;text;15;false
P26_Mochevinammvalue;мочевина (ммоль/л) - результат исследований;text;15;false
P26_Relativedensityvalue;Относительная плотность - результат исследования;text;50;false
P26_Testostervalue;Тестостерон (самцы);text;15;false
P27_Thyroxvalue;Тироксин (Т4);text;15;false
P28_Bilirubinvalue;Билирубин - результат исследования;text;50;false
P28_Mcvvalue;MCV (средний объем эритроцита) - результат исследования;text;15;false
P28_Mochevinamgvalue;мочевина (мг/дл) - результат исследований;text;15;false
P28_Triiodtirvalue;Трийодтиронин (Т3);text;15;false
P30_Creatininemcmvalue;креатинин (мкмоль/л) - результат исследований;text;15;false
P30_Hemeglvalue;Гемоглобин - результат исследования;text;50;false
P30_Mchvalue;MCH (сод. гемоглобина в 1 эритроците) - результат исследования;text;15;false
P32_Creatininemgvalue;креатинин (мг/дл) - результат исследований;text;15;false
P32_Erythrocytvalue;Эритроциты - результат исследования;text;50;false
P32_Mchcvalue;MCHC (конц. гемоглобина в 1 эритроците) - результат исследования;text;15;false
P34_Fattyacidsvalue;жирные кислоты - результат исследования;text;255;false
P34_Leucocytvalue;Лейкоциты - результат исследования;text;50;false
P34_Rdwvalue;RDW (ширина распределения эритроцитов) - результат исследования;text;15;false
P34_Shelochfosfatvalue;Щелочная фосфатаза - результат исследований;text;15;false
P36_Amilazavalue;?- амилаза - результат исследований;text;15;false
P36_Ploskiyvalue;Эпителий - Плоский - результат исследования;text;50;false
P36_Pltvalue;PLT (тромбоциты) - результат исследования;text;15;false
P38_Mpvvalue;MPV (средний объем тромбоцита) - результат исследования;text;15;false
P38_Pancreatinevalue;Панкреатическая амилаза - результат исследований;text;15;false
P38_Perehodvalue;Эпителий - переходный - результат исследования;text;50;false
P40_Glukozamcmvalue;глюкоза (мкмоль/л) - результат исследований;text;15;false
P40_Pctvalue;PCT (тромбокрит) - результат исследования;text;15;false
P40_Pochechnvalue;Эпителий - почечный - результат исследования;text;50;false
P42_Glukozamgvalue;глюкоза (мг/дл) - результат исследований;text;15;false
P42_Hyalinevalue;Цилиндры гиалиновые - результат исследования;text;50;false
P42_Pdwvalue;PDW (ширина распределения тромбоцитов) - результат исследования;text;15;false
P44_Granularvalue;Цилиндры Зернистые - результат исследования;text;50;false
P44_Ldglactodvalue;ЛДГ лактадегидрогиназа - результат исследований;text;15;false
P44_Soevalue;СОЭ - результат исследования;text;15;false
P46_Lgtgammavalue;ГГТ гамма-глутамилтрансфераза - результат исследований;text;15;false
P46_Waxvalue;Цилиндры восковидные - результат исследования;text;50;false
P46_Youngvalue;Нейтрофилы - Юные - результат исследования;text;15;false
P48_Kfkcreatinevalue;КФК креатинфосфокиназа - результат исследований;text;15;false
P48_Lekocitvalue;Цилиндры лейкоцитарные - результат исследования;text;50;false
P48_Palochkoyadervalue;Нейтрофилы - Палочкоядерные - результат исследования;text;15;false
P50_Eritrocitvalue;Цилиндры эритроцитарные - результат исследования;text;50;false
P50_Holestermmvalue;Холестерол (ммоль/л) - результат исследований;text;15;false
P50_Segmentvalue;Нейтрофилы - Сегментоядерные - результат исследования;text;15;false
P52_Eosinophilsvalue;Эозинофилы - результат исследования;text;15;false
P52_Epitelvalue;Цилиндры эпителиальные - результат исследования;text;50;false
P52_Holestermgvalue;Холестерол (мг/дл) - результат исследований;text;15;false
P54_Cilindvalue;Цилиндры цилиндроиды - результат исследования;text;50;false
P54_Monocitvalue;Моноциты - результат исследования;text;15;false
P54_Triglyceridsmmvalue;Триглицериды (ммоль/л) - результат исследований;text;15;false
P56_Bacteriavalue;Бактерии - результат исследования;text;50;false
P56_Bazophilvalue;Базофилы - результат исследования;text;15;false
P56_Triglyceridsmgvalue;Триглицериды (мг/дл) - результат исследований;text;15;false
P58_Caliummmvalue;калий (ммоль/л) - результат исследований;text;15;false
P58_Limphocitvalue;Лимфоциты - результат исследования;text;15;false
P58_Saltvalue;Соли - результат исследования;text;50;false
P60_Caliummecvalue;калий (мэкв/дл) - результат исследований;text;15;false
P61_Urinunitweightvalue;Удельный вес ;text;50;false
P62_Natrmmvalue;натрий (ммоль/л) - результат исследований;text;15;false
P63_Nitratvalue;Нитраты ;text;50;false
P64_Natrmecvalue;натрий (мэкв/дл) - результат исследований;text;15;false
P66_Phosphormmvalue;фосфор (ммоль/л) - результат исследований;text;15;false
P68_Phosphormgvalue;фосфор (мг/дл) - результат исследований;text;15;false
P70_Calciummmvalue;кальций (ммоль/л) - результат исследований;text;15;false
P72_Calciummcgvalue;кальций (мг/дл) - результат исследований;text;15;false
P74_Ironmcmvalue;железо (мкмоль/л) - результат исследований;text;15;false
P76_Ironmcgvalue;железо (мкг/дл) - результат исследований;text;15;false
P78_Magnesiummmvalue;магний (ммоль/л) - результат исследований;text;15;false
P80_Magnesiummecvalue;магний (мэкв/дл) - результат исследований;text;15;false
P82_Chloridemmvalue;хлорид (ммоль/л) - результат исследований;text;15;false
P84_Chloridemecvalue;хлорид (мэкв/дл) - результат исследований;text;15;false
P86_Acidityvalue;кислотность - результат исследований;text;15;false
P88_Mochekislnmvalue;мочевая кислота (нмоль/л) - результат исследований;text;15;false
P90_Mochekislmgvalue;мочевая кислота (мг/дл) - результат исследований;text;15;false
P92_Lipazavalue;липаза - результат исследований;text;15;false
P94_Totalproteinglvalue;Общий белок (г/л) - результат исследований;text;15;false
P96_Totalproteingdlvalue;Общий белок (г/дл) - результат исследований;text;15;false
P98_Albuminglvalue;Альбумины (г/л) - результат исследований;text;15;false
P100_Albumingdlvalue;Альбумины (г/дл) - результат исследований;text;15;false
P102_Hemoglobinvalue;Гемоглобин - результат исследований;text;15;false
P104_Ketonebodiesvalue;Кетоновые тела;text;15;false
P105_Bikarbonatvalue;Бикарботаны;text;15;false
P106_Proteinfractionsvalue;Белковые фракции;text;15;false
CSV;

        $items = $this->parseCsv($csv, __FUNCTION__);

        foreach ($items as $item) {
            $tech_name = $item[0];
            $datatype = $item[2];
            $datatype_details = $item[3];
            $this->db->createCommand()
                ->update(Params::tableName(), compact('datatype', 'datatype_details'), ['tech_name' => $tech_name])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181204_035748_update_params_datatype cannot be reverted.\n";

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
