<?php

use app\commands\migrate\Migration;
use app\models\db\Params;

/**
 * Class m200122_084737_2594_fix_uzi_mvs_update_service_reports_params
 */
class m200122_084737_2594_fix_uzi_mvs_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // фикс - не изменил datatype когда менял numeric параметры

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

        $this->update(Params::tableName(), ['datatype' => 'text'], ['in', 'tech_name', $techNames]);

        // P26_Zhelchpuzyrtolshchinastenki - Желчный пузырь: Толщина стенки
        // было numeric 4,2
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P26_Zhelchpuzyrtolshchinastenki',
        ];

        $this->update(Params::tableName(), ['datatype' => 'text'], ['in', 'tech_name', $techNames]);


        // P14_Matkadiametrtela - Матка - Диаметр тела
        // P15_Matkatolshinatela - Матка - Толщина стенки тела
        // P18_Matkadiametrpravroga - Диаметр правого рога
        // P19_Matkatolshinapravroga - Толщина стенки правого рога
        // P22_Matkadiametrlevroga - Диаметр левого рога
        // P23_Matkatolshinalevroga - Толщина стенки левого рога
        // P26_Pravyaichnikrazmer - Правый яичник - Размеры
        // P29_Levyaichnikrazmer - Левый яичник - Размеры
        // было numeric 4,2
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P14_Matkadiametrtela',
            'P15_Matkatolshinatela',
            'P18_Matkadiametrpravroga',
            'P19_Matkatolshinapravroga',
            'P22_Matkadiametrlevroga',
            'P23_Matkatolshinalevroga',
            'P26_Pravyaichnikrazmer',
            'P29_Levyaichnikrazmer',
        ];

        $this->update(Params::tableName(), ['datatype' => 'text'], ['in', 'tech_name', $techNames]);

        // P14_Predstzhelezarazmer - Предстательная железа: Размеры
        // P15_Predstzhelezakontur - Предстательная железа: Контуры
        // P19_Pravsemkontur - Правый семенник: Контуры
        // P22_Pridatokpravsemgolovka - Придаток правого семенника: Головка
        // P23_Pridatokpravsemtelo - Придаток правого семенника: Тело
        // P25_Levsemrazmer - Левый семенник: Размеры
        // P26_Levsemkontur - Левый семенник: Контуры
        // P29_Pridatoklevsemgolovka - Придаток левого семенника: Головка
        // P30_Pridatoklevsemtelo - Придаток левого семенника: Тело
        // было numeric 4,2
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P14_Predstzhelezarazmer',
            'P15_Predstzhelezakontur',
            'P19_Pravsemkontur',
            'P22_Pridatokpravsemgolovka',
            'P23_Pridatokpravsemtelo',
            'P25_Levsemrazmer',
            'P26_Levsemkontur',
            'P29_Pridatoklevsemgolovka',
            'P30_Pridatoklevsemtelo',
        ];

        $this->update(Params::tableName(), ['datatype' => 'text'], ['in', 'tech_name', $techNames]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200122_084737_2594_fix_uzi_mvs_update_service_reports_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200122_084737_2594_fix_uzi_mvs_update_service_reports_params cannot be reverted.\n";

        return false;
    }
    */
}
