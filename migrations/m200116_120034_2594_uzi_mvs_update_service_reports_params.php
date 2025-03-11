<?php

use app\commands\migrate\Migration;
use app\models\db\Dictionaries;
use app\models\db\Params;
use app\models\db\VisitServiceParamValues;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class m200116_120034_2594_uzi_mvs_update_service_reports_params
 */
class m200116_120034_2594_uzi_mvs_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        // P15_Prvpochgranica - Правая почка: Границы
        // P16_Prvpochrazmer - Правая почка: Размеры
        // P23_Prvpochmedullyarsloykortmeddiffer - Правая почка: Медуллярный слой - Кортико-медуллярная дифференциация
        // P26_Prvpochpochsinuschetkostdifferents - Правая почка: Почечный синус - Четкость дифференциации
        // P27_Prvpochpochsinuspolostlokhanki - Правая почка: Почечный синус - Полость лоханки
        // P28_Prvpochpochsinusstepenlokhanki - Правая почка: Почечный синус - Стенки лоханки
        // P29_Prvpochsosudyparenkhimy - Правая почка: Сосуды паренхимы
        // P32_Prvpochkonkrementy - Правая почка: Конкременты
        // P33_Prvpochobyemnobrazov - Правая почка: Объемные образования
        // P35_Levpochgranica - Левая почка: Границы
        // P36_Levpochrazmer - Левая почка: Размеры
        // P43_Levpochmedullyarsloykortmeddiffer - Левая почка: Медуллярный слой - Кортико-медуллярная дифференциация
        // P46_Levpochpochsinuschetkostdifferents - Левая почка: Почечный синус - Четкость дифференциации
        // P47_Levpochpochsinuspolostlokhanki - Левая почка: Почечный синус - Полость лоханки
        // P48_Levpochpochsinusstenkilokhanki - Левая почка: Почечный синус - Стенки лоханки
        // P49_Levpochsosudyparenkhimy - Левая почка: Сосуды паренхимы
        // P52_Levpochkonkrementy - Левая почка: Конкременты
        // P53_Levpochobyemnobrazov - Левая почка: Объемные образования
        // P58_MochpuzObyemnobrazov - Объемные образования
        // было text 100
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P15_Prvpochgranica',
            'P16_Prvpochrazmer',
            'P23_Prvpochmedullyarsloykortmeddiffer',
            'P26_Prvpochpochsinuschetkostdifferents',
            'P27_Prvpochpochsinuspolostlokhanki',
            'P28_Prvpochpochsinusstepenlokhanki',
            'P29_Prvpochsosudyparenkhimy',
            'P32_Prvpochkonkrementy',
            'P33_Prvpochobyemnobrazov',
            'P35_Levpochgranica',
            'P36_Levpochrazmer',
            'P43_Levpochmedullyarsloykortmeddiffer',
            'P46_Levpochpochsinuschetkostdifferents',
            'P47_Levpochpochsinuspolostlokhanki',
            'P48_Levpochpochsinusstenkilokhanki',
            'P49_Levpochsosudyparenkhimy',
            'P52_Levpochkonkrementy',
            'P53_Levpochobyemnobrazov',
            'P58_MochpuzObyemnobrazov',
        ];

        $this->update(Params::tableName(), ['datatype_details' => '255', 'updated_at' => $now], ['in', 'tech_name', $techNames]);

        // P59_Serviceresult - Заключение
        // было text 255
        // нужно text 'не менее 2000 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 3000

        $this->update(Params::tableName(), ['datatype_details' => '3000', 'updated_at' => $now], ['tech_name' => 'P59_Serviceresult']);


        // P17_Prvpochkortiksloytolshina - Правая почка: кортикальный слой - Толщина
        // P20_Prvpochmedullyarsloytolshchina - Правая почка: Медуллярный слой - Толщина
        // P24_Prvpochpiyelicheskiyindeks - Правая почка: Паренхимо-пиелический индекс
        // P30_PrvpochIndeksrezistivnpochechnart - Правая почка: Индекс резистентности на участке почечной артерии
        // P31_PrvpochIndeksrezistivnmezhdolevoyart - Правая почка: Индекс резистентности на участке междолевой артерии
        // P37_Levpochkortiksloytolshina - Левая почка: кортикальный слой - Толщина
        // P40_Levpochmedullyarsloytolshchina - Левая почка: Медуллярный слой - Толщина
        // P44_Levpochpiyelicheskiyindeks - Левая почка: Паренхимо-пиелический индекс
        // P50_LevpochIndeksrezistivnpochechnart - Левая почка: Индекс резистентности на участке почечной артерии
        // P51_LevpochIndeksrezistivnmezhdolevoyart - Левая почка: Индекс резистентности на участке междолевой артерии
        // P55_Mochpuztolshchinastenki - Мочевой пузырь: Толщина стенки
        // P57_Mochpuzuretra - Уретра
        // было numeric 4,2
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P17_Prvpochkortiksloytolshina',
            'P20_Prvpochmedullyarsloytolshchina',
            'P24_Prvpochpiyelicheskiyindeks',
            'P30_PrvpochIndeksrezistivnpochechnart',
            'P31_PrvpochIndeksrezistivnmezhdolevoyart',
            'P37_Levpochkortiksloytolshina',
            'P40_Levpochmedullyarsloytolshchina',
            'P44_Levpochpiyelicheskiyindeks',
            'P50_LevpochIndeksrezistivnpochechnart',
            'P51_LevpochIndeksrezistivnmezhdolevoyart',
            'P55_Mochpuztolshchinastenki',
            'P57_Mochpuzuretra',
        ];

        $this->update(Params::tableName(), ['datatype_details' => '255', 'updated_at' => $now], ['in', 'tech_name', $techNames]);

        // продублируем значения из num_value char_value
        $this->update(
            VisitServiceParamValues::tableName(),
            ['char_value' => (new Expression('num_value::text')), 'updated_at' => $now],
            [
                'and',
                ['in', 'id_param', (new Query())->select('id')->from(Params::tableName())->where(['in', 'tech_name', $techNames])],
                ['not', ['num_value' => null]]
            ]
        );

        // fix (probably) typo
        $this->update(Params::tableName(), ['name' => 'Правая почка: Индекс резистентности на участке почечной артерии', 'updated_at' => $now], ['tech_name' => 'P30_PrvpochIndeksrezistivnpochechnart']);
        $this->update(Params::tableName(), ['name' => 'Правая почка: Индекс резистентности на участке междолевой артерии', 'updated_at' => $now], ['tech_name' => 'P31_PrvpochIndeksrezistivnmezhdolevoyart']);
        $this->update(Params::tableName(), ['name' => 'Левая почка: Индекс резистентности на участке почечной артерии', 'updated_at' => $now], ['tech_name' => 'P50_LevpochIndeksrezistivnpochechnart']);
        $this->update(Params::tableName(), ['name' => 'Левая почка: Индекс резистентности на участке междолевой артерии', 'updated_at' => $now], ['tech_name' => 'P51_LevpochIndeksrezistivnmezhdolevoyart']);


        // P18_Prvpochkortiksloyekhogennost - Правая почка: кортикальный слой - Эхогенность
        // P21_Prvpochmedullyarsloyekhogennost - Правая почка: Медуллярный слой - Эхогенность
        // P25_Prvpochpochsinusekhogennost - Правая почка: Почечный синус - Эхогенность
        // P38_Levpochkortiksloyekhogennost - Левая почка: кортикальный слой - Эхогенность
        // P41_Levpochmedullyarsloyekhogennost - Левая почка: Медуллярный слой - Эхогенность
        // P45_Levpochpochsinusekhogennost - Левая почка: Почечный синус - Эхогенность
        // было dict kidneyechogenicity
        // нужно 'с возможностью выбрать несколько вариантов'
        // ранее у нас не было мульти-селекта значений справочника!!!

        $techNames = [
            'P18_Prvpochkortiksloyekhogennost',
            'P21_Prvpochmedullyarsloyekhogennost',
            'P25_Prvpochpochsinusekhogennost',
            'P38_Levpochkortiksloyekhogennost',
            'P41_Levpochmedullyarsloyekhogennost',
            'P45_Levpochpochsinusekhogennost',
        ];

        // меняем тип с dict на complex

        $this->update(Params::tableName(), ['datatype' => 'complex', 'updated_at' => $now], ['in', 'tech_name', $techNames]);

        // продублируем visit_sevice_param_values из dict_value в complex_value
        $this->update(
            VisitServiceParamValues::tableName(),
            ['complex_value' => (new Expression('json_build_array(dict_value::text)')), 'updated_at' => $now],
            [
                'and',
                ['in', 'id_param', (new Query())->select('id')->from(Params::tableName())->where(['in', 'tech_name', $techNames])],
                ['not', ['dict_value' => null]]
            ]
        );

        // fix (probably) typo
        $this->update(Dictionaries::tableName(), ['name' => 'анэхогенна', 'updated_at' => $now], ['type' => 'kidneyechogenicity', 'name' => 'анэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'гипоэхогенна', 'updated_at' => $now], ['type' => 'kidneyechogenicity', 'name' => 'гипоэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'гиперэхогенна', 'updated_at' => $now], ['type' => 'kidneyechogenicity', 'name' => 'гиперэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'изоэхогенна', 'updated_at' => $now], ['type' => 'kidneyechogenicity', 'name' => 'изоэхогенно']);

        // P19_Prvpochkortiksloyekhostruktura - Правая почка: кортикальный слой - Эхоструктура
        // P22_Prvpochmedullyarsloyekhostruktura - Правая почка: Медуллярный слой - Эхоструктура
        // P39_Levpochkortiksloyekhostruktura - Левая почка: кортикальный слой - Эхоструктура
        // P42_Levpochmedullyarsloyekhostruktura - Левая почка: Медуллярный слой - Эхоструктура
        // было text 100
        // нужно справочник - 'однородная/не однородная'

        $techNames = [
            'P19_Prvpochkortiksloyekhostruktura',
            'P22_Prvpochmedullyarsloyekhostruktura',
            'P39_Levpochkortiksloyekhostruktura',
            'P42_Levpochmedullyarsloyekhostruktura',
        ];

        // новый справочник
        $dictType = 'kidneyechostruktura';

        $values = [
            'однородная',
            'не однородная',
        ];

        foreach ($values as $value) {
            $this->insert(
                Dictionaries::tableName(),
                [
                    'name' => $value,
                    'type' => $dictType,
                    'created_at' => $now,
                ]);
        }

        // только обновляем 'datatype', 'datatype_details'
        // конвертировать старые значения не представляется возможным, так как раньше был произвольный текст!!!
        $this->update(Params::tableName(), ['datatype' => 'dict', 'datatype_details' => $dictType, 'updated_at' => $now], ['in', 'tech_name', $techNames]);

        // P54_Mochpuzstepnapoln - Мочевой пузырь: Степень наполнения
        // было text 100
        // нужно справочник - 'пустой/слабо наполненный/умеренно наполненный/переполненный'

        // новый справочник
        $dictType = 'mochpuzstepnapoln';

        $values = [
            'пустой',
            'слабо наполненный',
            'умеренно наполненный',
            'переполненный',
        ];

        foreach ($values as $value) {
            $this->insert(
                Dictionaries::tableName(),
                [
                    'name' => $value,
                    'type' => $dictType,
                    'created_at' => $now,
                ]);
        }

        // только обновляем 'datatype', 'datatype_details'
        // конвертировать старые значения не представляется возможным, так как раньше был произвольный текст!!!
        $this->update(Params::tableName(), ['datatype' => 'dict', 'datatype_details' => $dictType, 'updated_at' => $now], ['tech_name' => 'P54_Mochpuzstepnapoln']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200116_120034_2594_uzi_mvs_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
