<?php

use app\commands\migrate\Migration;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\VarDumper;

/**
 * Class m181119_050846_update_params
 */
class m181119_050846_update_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = <<<CSV
"Ампутация рудиментарных фаланг у собак - до 2-х недельного возраста (с местным обезболиванием)";"P10_Petbirthday";true;false;1
"Ампутация рудиментарных фаланг у собак - от 2-х до 4-х недельного возраста";"P10_Petbirthday";true;false;1
"Ампутация рудиментарных фаланг у собак - свыше 4-х недельного возраста";"P10_Petbirthday";true;false;1
"Ампутация хвоста у собак - до 10-ти дневного возраста (с местным обезболиванием)";"P10_Petbirthday";true;false;1
"Ампутация хвоста у собак - от 10-ти дневного до 2-х месячного возраста";"P10_Petbirthday";true;false;1
"Ампутация хвоста у собак - свыше 2-х месячного возраста";"P10_Petbirthday";true;false;1
"Биохимические исследования крови - определение АЛТ (аланинаминотрансферазы)";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение амилазы";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение амилазы панкреатической";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение АСТ (аспартатаминотрансферазы)";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение белковых фракций";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение гаммаглутамилтрансферазы";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение гемоглобина";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение глюкозы";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение железа";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение калия";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение кальция";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение креатинина";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение креатинкиназы";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение лактатдегидрогеназы";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение липазы";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение магния";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение мочевины";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение мочевой кислоты";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение натрия";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение общего белка";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение общего билирубина";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение общего холестерина";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение триглицеридов";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение фосфора неорганического";"P0_Venousbloodanalysisnum";false;true;1
"Биохимические исследования крови - определение щелочной фосфатазы";"P0_Venousbloodanalysisnum";false;true;1
"Ветеринарное освидетельствование животных для оформления ветеринарных сопроводительных документов, включающая проведение клинического осмотра и изучение ветеринарных документов (паспорта на животное, результатов лабораторных исследований и др.) - с гельминтокопрологическим исследованием";"P0_Coproalysisnum";true;false;1
"Выезд ветврача";"P0_Juraddress";true;false;1
"Выезд для оказания ветеринарной помощи на дому";"P0_Juraddress";true;false;1
"Гельминтокопрологические исследования";"P0_Coproalysisnum";false;true;1
"Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) до 10 кг - длинношерстные (свыше 6 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) до 10 кг - короткошерстные (до 3 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) до 10 кг - среднешерстные (до 6 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) свыше 10 кг до 20 кг - длинношерстные (свыше 6 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) свыше 10 кг до 20 кг - короткошерстные (до 3см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) свыше 10 кг до 20 кг - среднешерстные (до 6 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) свыше 20 кг: - длинношерстные (свыше 6 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) свыше 20 кг: - короткошерстные (до 3 см)";"P0_Petwoollength";false;true;1
"Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см)";"P0_Petweight";false;true;1
"Груминг собак (комплекс) свыше 20 кг: - среднешерстные (до 6 см)";"P0_Petwoollength";false;true;1
"Исследование на кровепаразитарные болезни";"P0_Capillarybloodanalysisnum";false;true;1
"Исследование на кровепаразитарные болезни";"P14_Analysiscount";false;true;2
"Исследование на кровепаразитарные болезни";"P18_Analysisdate";false;true;5
"Кастрация, стерилизация (оперативное вмешательство) - кошки, самки декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)";"P8_Petsex";true;false;2
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг";"P0_Petweight";false;true;3
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг";"P10_Petbirthday";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: до 5 кг";"P8_Petsex";true;false;2
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг";"P0_Petweight";false;true;3
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг";"P10_Petbirthday";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 15 кг";"P8_Petsex";true;false;2
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг";"P0_Petweight";false;true;3
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг";"P10_Petbirthday";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - с/х животные от 2-х месяцев и кобели: свыше 5 кг до 15 кг";"P8_Petsex";true;false;2
"Кастрация, стерилизация (оперативное вмешательство) - с/х ж-ые до 2-х месяцев, коты, самцы декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)";"P10_Petbirthday";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - с/х ж-ые до 2-х месяцев, коты, самцы декоративных животных (хорьки, норки, морские свинки, лисы и другие животные)";"P8_Petsex";true;false;2
"Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг";"P0_Petweight";false;true;2
"Кастрация, стерилизация (оперативное вмешательство) - суки: до 5 кг";"P8_Petsex";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг";"P0_Petweight";false;true;2
"Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 15 кг до 25 кг";"P8_Petsex";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг";"P0_Petweight";false;true;2
"Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 25 кг";"P8_Petsex";true;false;1
"Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг";"P0_Petweight";false;true;2
"Кастрация, стерилизация (оперативное вмешательство) - суки: свыше 5 кг до 15 кг";"P8_Petsex";true;false;1
"Компьютерная томография без введения контрастного вещества - голова, отделы конечностей (сустав, регион конечности)";"P0_Investigationarea";true;false;1
"Компьютерная томография без введения контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы";"P0_Investigationarea";true;false;1
"Компьютерная томография с введением контрастного вещества - голова, отделы конечностей (сустав, регион конечности)";"P0_Investigationarea";true;false;1
"Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - голова, отделы конечностей (сустав, регион конечности)";"P0_Investigationarea";true;false;1
"Компьютерная томография с введением контрастного вещества - повторное сканирование с контрастированием - шейный, грудной, поясничный, крестцово-тазовый отделы";"P0_Investigationarea";true;false;1
"Компьютерная томография с введением контрастного вещества - шейный, грудной, поясничный, крестцово-тазовый отделы";"P0_Investigationarea";true;false;1
"Купирование ушных раковин у собак - до 10-ти дневного возраста (с местным обезболиванием)";"P10_Petbirthday";true;false;3
"Купирование ушных раковин у собак - от 10-ти дневного до 3-х месячного возраста";"P10_Petbirthday";true;false;2
"Купирование ушных раковин у собак - свыше 3-х месячного возраста";"P10_Petbirthday";true;false;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - до 5 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 10 до 20 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 20 до 30 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 30 до 40 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 40 до 50 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 5 до 10 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 50 до 60 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 60 до 70 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 70 до 80 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 80 до 90 кг";"P0_Petweight";false;true;1
"Медикаментозная эвтаназия животных с последующей утилизацией трупа - свыше 90 до 100 кг";"P0_Petweight";false;true;1
"Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты";"P12_Analysisnum";false;true;1
"Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты";"P14_Analysiscount";false;true;2
"Микроскопические исследования на дерматофиты, демодекоз и эктопаразиты";"P18_Analysisdate";false;true;5
"Наложение гипсовой повязки (без репозиции) - крупные породы собак";"P0_Petweight";false;false;1
"Наложение гипсовой повязки (без репозиции) - мелкие породы собак и кошки";"P0_Petweight";false;false;1
"Обрезка рогов с/х животного";"P10_Petbirthday";true;false;1
"Обрезка рогов с/х животного - обезроживание телят";"P10_Petbirthday";true;false;1
"Общий анализ кала";"P0_Coproalysisnum";false;true;1
"Общий анализ мочи";"P0_Urinalysisnum";false;true;1
"Общий клинический анализ крови - выведение лейкоцитарной формулы";"P0_Venousbloodanalysisnum";true;false;1
"Общий клинический анализ крови - определение гемоглобина";"P0_Venousbloodanalysisnum";true;false;1
"Общий клинический анализ крови - определение СОЭ";"P0_Venousbloodanalysisnum";true;false;1
"Общий клинический анализ крови - подсчет лейкоцитов";"P0_Venousbloodanalysisnum";true;false;1
"Общий клинический анализ крови - подсчет эритроцитов";"P0_Venousbloodanalysisnum";true;false;1
"Определение гормонов в сыворотке крови - кортизол";"P0_Venousbloodanalysisnum";false;true;1
"Определение гормонов в сыворотке крови - прогестерон";"P0_Venousbloodanalysisnum";false;true;1
"Определение гормонов в сыворотке крови - тестостерон";"P0_Venousbloodanalysisnum";false;true;1
"Определение гормонов в сыворотке крови - тироксин";"P0_Venousbloodanalysisnum";false;true;1
"Определение гормонов в сыворотке крови - трийодтиронин";"P0_Venousbloodanalysisnum";false;true;1
"Определение гормонов в сыворотке крови - эстрадиол";"P0_Venousbloodanalysisnum";false;true;1
"Повторное ультразвуковое исследование";"P0_Organsystem";true;false;1
"Санитарная помывка животных - крупные животные (свыше 15 кг)";"P0_Petweight";false;true;1
"Санитарная помывка животных - мелкие животные (до 5 кг)";"P0_Petweight";false;true;1
"Санитарная помывка животных - средние животные (свыше 5 кг до 15 кг)";"P0_Petweight";false;true;1
"Санитарная стрижка животных - крупные животные (свыше 15 кг)";"P0_Petweight";false;true;1
"Санитарная стрижка животных - мелкие животные (до 5 кг)";"P0_Petweight";false;true;1
"Санитарная стрижка животных - средние животные (свыше 5 кг до 15 кг)";"P0_Petweight";false;true;1
"Снятие гипсовой повязки - крупные породы собак";"P0_Petweight";false;true;1
"Снятие гипсовой повязки - мелкие породы собак и кошки";"P0_Petweight";false;true;1
"Содержание животных - кошки и собаки (до 5 кг)";"P0_Petweight";false;true;1
"Содержание животных - кошки и собаки (свыше 5 кг)";"P0_Petweight";false;true;1
"Ультразвуковое исследование";"P0_Organsystem";true;false;1
"Ультразвуковой скрининг органов брюшной полости";"P0_Organsystem";true;false;1
"Утилизация (сжигание) биологических отходов без транспортировки";"P0_Petweight";false;true;1
"Утилизация (сжигание) биологических отходов с транспортировкой";"P0_Petweight";false;true;1
"Цитологические исследования";"P0_Cytologicsanalysisnum";false;true;1
CSV;

        $params = (new Query())
            ->from(Params::tableName())
            ->orderBy(['tech_name' => SORT_ASC])
            ->all();

        $paramsMap = ArrayHelper::map($params, 'id', 'tech_name');

        $services = (new Query())
            ->from(GovServices::tableName())
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $servicesMap = ArrayHelper::map($services, 'id', 'name');

        $items = $this->parseCsv($csv, __FUNCTION__);

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $service_name = $item[0];
            $tech_name = $item[1];
            $req_in = ($item[2] === 'true') ? true : false;
            $req_out = ($item[3] === 'true') ? true : false;
            $sort_by = (int)$item[4];

            $id_param = array_search($tech_name, $paramsMap);

            if ($id_param === false) {
                Console::output(Console::ansiFormat('Param not found [' . $tech_name  . ']', [Console::FG_RED]));
                continue;
            }

            $id_service = array_search($service_name, $servicesMap);

            if ($id_service === false) {
                Console::output(Console::ansiFormat('Service not found [' . $service_name  . ']', [Console::FG_RED]));
                continue;
            }

            /* @var $record \app\models\db\GovServicesParams */
            $record = GovServicesParams::find()
                ->where([
                    'id_param' => $id_param,
                    'id_service' => $id_service,
                ])
                ->limit(1)
                ->one();

            if ($record === null) {
                Console::output(Console::ansiFormat('Creating relation  [' . $tech_name . ' | ' . $service_name  . ']', [Console::FG_GREEN]));
                $record = new GovServicesParams([
                    'id_param' => $id_param,
                    'id_service' => $id_service,
                    'req_in' => $req_in,
                    'req_out' => $req_out,
                    'sort_by' => $sort_by,
                ]);
                $record->save();
                $created++;
            } else {
                if ($record->req_in === $req_in && $record->req_out === $req_out && $record->sort_by === $sort_by) {
                    continue;
                }

                $record->load(compact('req_in', 'req_out', 'sort_by'), '');
                Console::output(Console::ansiFormat('Updating relation  [' . $tech_name . ' | ' . $service_name . ' | ' . VarDumper::export($record->dirtyAttributes)  . ']', [Console::FG_PURPLE]));
                $record->save();
                $updated++;
            }
        }

        Console::output('Created: ' . $created);
        Console::output('Updated: ' . $updated);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
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
