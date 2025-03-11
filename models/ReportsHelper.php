<?php

namespace app\models;

class ReportsHelper
{
    const
        TYPE_URINE = 'urine',
        TYPE_BLOOD = 'blood',
        TYPE_BIOCHEMISTRY_BLOOD = 'biochemistry_blood',
        TYPE_MICROSCOPIC_BLOODPARASITES = 'microscopic_bloodparasites',
        TYPE_MICROSCOPIC_ECTOPARASITES = 'microscopic_ectoparasites',
        TYPE_HELMINT = 'helmint',
        TYPE_HORMONAL_BLOOD = 'hormonal_blood',
        TYPE_MICROSCOPIC_CYTOLOGY = 'microscopic_cytology',
        TYPE_BIOCHEMISTRY_FECES = 'biochemistry_feces'
    ;

    public static $types = [
        self::TYPE_URINE => ['attribute' => 'research_link_urine'],
        self::TYPE_BLOOD => ['attribute' => 'research_link_blood', 'name' => 'Общий клинический анализ крови'],
        self::TYPE_BIOCHEMISTRY_BLOOD => [
            'attribute' => 'research_link_biochemistry_blood', 'name' => 'Биохимические исследования крови'
        ],
        self::TYPE_HORMONAL_BLOOD => [
            'attribute' => 'research_link_hormonal_blood', 'name' => 'Определение гормонов в сыворотке крови'
        ],
        self::TYPE_MICROSCOPIC_BLOODPARASITES => ['attribute' => 'research_link_microscopic_bloodparasites'],
        self::TYPE_MICROSCOPIC_ECTOPARASITES => ['attribute' => 'research_link_microscopic_ectoparasites'],
        self::TYPE_HELMINT => ['attribute' => 'research_link_helmint'],
        self::TYPE_MICROSCOPIC_CYTOLOGY => ['attribute' => 'research_link_microscopic_cytology'],
        self::TYPE_BIOCHEMISTRY_FECES => ['attribute' => 'research_link_biochemistry_feces']
    ];

    /**
     * @param string $code
     * @return bool|mixed
     */
    public static function getAttribute(string $code)
    {
        $arr = [
            //'0277' => '',
            '0280' => self::$types[self::TYPE_URINE]['attribute'],
            '0284' => self::$types[self::TYPE_BLOOD]['attribute'],
            '0285' => self::$types[self::TYPE_BLOOD]['attribute'],
            '0286' => self::$types[self::TYPE_BLOOD]['attribute'],
            '0287' => self::$types[self::TYPE_BLOOD]['attribute'],
            '0288' => self::$types[self::TYPE_BLOOD]['attribute'],
            '0289' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0290' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0291' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0292' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0293' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0294' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0295' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0296' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0297' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0298' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0299' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0300' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0301' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0302' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0303' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0304' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0305' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0306' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0307' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0308' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0309' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0310' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0311' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0312' => self::$types[self::TYPE_MICROSCOPIC_BLOODPARASITES]['attribute'],
            '0313' => self::$types[self::TYPE_HELMINT]['attribute'],
            '0314' => self::$types[self::TYPE_MICROSCOPIC_ECTOPARASITES]['attribute'],
            '0348' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0351' => self::$types[self::TYPE_HORMONAL_BLOOD]['attribute'],
            '0352' => self::$types[self::TYPE_HORMONAL_BLOOD]['attribute'],
            '0353' => self::$types[self::TYPE_HORMONAL_BLOOD]['attribute'],
            '0354' => self::$types[self::TYPE_HORMONAL_BLOOD]['attribute'],
            '0355' => self::$types[self::TYPE_HORMONAL_BLOOD]['attribute'],
            '0356' => self::$types[self::TYPE_HORMONAL_BLOOD]['attribute'],
            '0427' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['attribute'],
            '0430' => self::$types[self::TYPE_MICROSCOPIC_CYTOLOGY]['attribute'],
            '0437' => self::$types[self::TYPE_BIOCHEMISTRY_FECES]['attribute']
        ];

        if (!isset($arr[$code])) {
            return false;
        }

        return $arr[$code];
    }

    /**
     * @param string $code
     * @return bool|mixed
     */
    public static function getName(string $code)
    {
        $arr = [
            '0284' => self::$types[self::TYPE_BLOOD]['name'],
            '0285' => self::$types[self::TYPE_BLOOD]['name'],
            '0286' => self::$types[self::TYPE_BLOOD]['name'],
            '0287' => self::$types[self::TYPE_BLOOD]['name'],
            '0288' => self::$types[self::TYPE_BLOOD]['name'],
            '0289' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0290' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0291' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0292' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0293' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0294' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0295' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0296' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0297' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0298' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0299' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0300' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0301' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0302' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0303' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0304' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0305' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0306' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0307' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0308' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0309' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0310' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0311' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0348' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
            '0351' => self::$types[self::TYPE_HORMONAL_BLOOD]['name'],
            '0352' => self::$types[self::TYPE_HORMONAL_BLOOD]['name'],
            '0353' => self::$types[self::TYPE_HORMONAL_BLOOD]['name'],
            '0354' => self::$types[self::TYPE_HORMONAL_BLOOD]['name'],
            '0355' => self::$types[self::TYPE_HORMONAL_BLOOD]['name'],
            '0356' => self::$types[self::TYPE_HORMONAL_BLOOD]['name'],
            '0427' => self::$types[self::TYPE_BIOCHEMISTRY_BLOOD]['name'],
        ];

        if (!isset($arr[$code])) {
            return false;
        }

        return $arr[$code];
    }
}
