<?php

use app\commands\migrate\Migration;
use app\models\db\Dictionaries;
use app\models\db\GovServices;
use app\models\db\GovServicesParams;
use app\models\db\Params;
use app\models\db\ReportsParams;
use app\models\db\VisitServiceParamValues;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m200120_043943_2594_uzi_samez_update_service_reports_params
 */
class m200120_043943_2594_uzi_samez_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

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


        // P17_Predstzhelezaobyemnobrazov - Предстательная железа: Объемные образования
        // P18_Pravsemrazmer - Правый семенник: Размеры
        // P21_Pravsemobyemnobrazov - Правый семенник: Объемные образования
        // P24_Pridatokpravsemobyemnobrazov - Придаток правого семенника: Объемные образования
        // P28_Levsemobyemnobrazov - Левый семенник: Объемные образования
        // P31_Pridatoklevsemobyemnobrazov - Придаток левого семенника: Объемные образования
        // было text 100
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P17_Predstzhelezaobyemnobrazov',
            'P18_Pravsemrazmer',
            'P21_Pravsemobyemnobrazov',
            'P24_Pridatokpravsemobyemnobrazov',
            'P28_Levsemobyemnobrazov',
            'P31_Pridatoklevsemobyemnobrazov',
        ];

        $this->update(Params::tableName(), ['datatype_details' => '255', 'updated_at' => $now], ['in', 'tech_name', $techNames]);


        // P32_Abdomultmserviceresult - Заключение
        // было text 255
        // нужно text 'не менее 2000 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 3000

        $this->update(Params::tableName(), ['datatype_details' => '3000', 'updated_at' => $now], ['tech_name' => 'P32_Abdomultmserviceresult']);


        // fix (probably) typo
        $this->update(Dictionaries::tableName(), ['name' => 'анэхогенна', 'updated_at' => $now], ['type' => 'parenchymatous', 'name' => 'анэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'гипоэхогенна', 'updated_at' => $now], ['type' => 'parenchymatous', 'name' => 'гипоэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'гиперэхогенна', 'updated_at' => $now], ['type' => 'parenchymatous', 'name' => 'гиперэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'изоэхогенна', 'updated_at' => $now], ['type' => 'parenchymatous', 'name' => 'изоэхогенно']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200120_043943_2594_uzi_samez_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
