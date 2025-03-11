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
 * Class m200117_070733_2594_uzi_gbs_update_service_reports_params
 */
class m200117_070733_2594_uzi_gbs_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        $id_report = 12;

        $serviceNames = [
            'Ультразвуковое исследование',
            'Повторное ультразвуковое исследование',
            'Ультразвуковой скрининг органов брюшной полости',
        ];

        $services = (new Query())
            ->from(GovServices::tableName())
            ->where(['in', 'name', $serviceNames])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        // добавить параметр P16_Capsula - Печень: Капсула
        // dict - утолщена/не утолщена

        // новый справочник
        $dictType = 'capsula';

        $values = [
            'утолщена',
            'не утолщена',
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

        $tech_name = 'P16_Capsula';

        $param = new Params([
            'name' => 'Печень: Капсула',
            'tech_name' => $tech_name,
            'datatype' => 'dict',
            'datatype_details' => $dictType,
            'visit_flag' => false,
            'config' => [
                [
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    'req_in' => [
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        'tech_name' => 'P0_Organsystem'
                    ]
                ],
                [
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    'req_in' => [
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        'tech_name' => 'P0_Abdomenorgansystem'
                    ]
                ]
            ]
        ]);

        if (!$param->save(false)) {
            Console::output('Error saving param ' . $tech_name);
            return false;
        }

        foreach ($services as $service) {
            $this->insert(
                GovServicesParams::tableName(),
                [
                    'id_param' => $param->id,
                    'id_service' => $service['id'],
                    'req_in' => false,
                    'req_out' => false,
                    'flag_in' => false,
                    'flag_out' => true,
                    'created_at' => $now,
                ]
            );
        }

        $this->insert(
            ReportsParams::tableName(),
            [
                'id_param' => $param->id,
                'id_report' => $id_report,
                'created_at' => $now,
            ]
        );

        // добавить параметр P24_Zhelchpuzyrraspoloshenie - Желчный пузырь: расположение
        // dict - анатомически правильное/анатомически не правильное
        // используем существующий справочник abdomendposition

        $dictType = 'abdomendposition';

        $tech_name = 'P24_Zhelchpuzyrraspoloshenie';

        $param = new Params([
            'name' => 'Желчный пузырь: расположение',
            'tech_name' => $tech_name,
            'datatype' => 'dict',
            'datatype_details' => $dictType,
            'visit_flag' => false,
            'config' => [
                [
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    'req_in' => [
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        'tech_name' => 'P0_Organsystem'
                    ]
                ],
                [
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    'req_in' => [
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        'tech_name' => 'P0_Abdomenorgansystem'
                    ]
                ]
            ]
        ]);

        if (!$param->save(false)) {
            Console::output('Error saving param ' . $tech_name);
            return false;
        }

        foreach ($services as $service) {
            $this->insert(
                GovServicesParams::tableName(),
                [
                    'id_param' => $param->id,
                    'id_service' => $service['id'],
                    'req_in' => false,
                    'req_out' => false,
                    'flag_in' => false,
                    'flag_out' => true,
                    'created_at' => $now,
                ]
            );
        }

        $this->insert(
            ReportsParams::tableName(),
            [
                'id_param' => $param->id,
                'id_report' => $id_report,
                'created_at' => $now,
            ]
        );

        // добавить параметр P42_Podzhelzhelezavisualization - Поджелудочная железа: Визуализация
        // dict - визуализируется /не визуализируется

        // новый справочник
        $dictType = 'visualization';

        $values = [
            'визуализируется',
            'не визуализируется',
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

        $tech_name = 'P42_Podzhelzhelezavisualization';

        $param = new Params([
            'name' => 'Поджелудочная железа: Визуализация',
            'tech_name' => $tech_name,
            'datatype' => 'dict',
            'datatype_details' => $dictType,
            'visit_flag' => false,
            'config' => [
                [
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    'req_in' => [
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        'tech_name' => 'P0_Organsystem'
                    ]
                ],
                [
                    'report' => 'Ультразвуковое исследование печени, желчного пузыря, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                    'req_in' => [
                        'value' => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
                        'tech_name' => 'P0_Abdomenorgansystem'
                    ]
                ]
            ]
        ]);

        if (!$param->save(false)) {
            Console::output('Error saving param ' . $tech_name);
            return false;
        }

        foreach ($services as $service) {
            $this->insert(
                GovServicesParams::tableName(),
                [
                    'id_param' => $param->id,
                    'id_service' => $service['id'],
                    'req_in' => false,
                    'req_out' => false,
                    'flag_in' => false,
                    'flag_out' => true,
                    'created_at' => $now,
                ]
            );
        }

        $this->insert(
            ReportsParams::tableName(),
            [
                'id_param' => $param->id,
                'id_report' => $id_report,
                'created_at' => $now,
            ]
        );


        // удалить параметр P16_Pechenrazmer - Печень: Размеры
        // удалить параметр P27_Zhelchpuzyrdeformatsiya - Желчный пузырь: Деформация
        // удалить параметр P42_Podzhelzhelezarazmer - Поджелудочная железа: Размеры
        // удалить параметр P44_Podzhelzhelezaekhogennost - Поджелудочная железа: Эхогенность
        // удалить параметр P45_Podzhelzhelezaobyemnobrazov - Поджелудочная железа: Объемные образования

        $techNames = [
            'P16_Pechenrazmer',
            'P27_Zhelchpuzyrdeformatsiya',
            'P42_Podzhelzhelezarazmer',
            'P44_Podzhelzhelezaekhogennost',
            'P45_Podzhelzhelezaobyemnobrazov',
        ];

        $ids = (new Query())
            ->select('id')
            ->from(Params::tableName())
            ->where(['in', 'tech_name', $techNames])
            ->column();

        $this->delete(
            GovServicesParams::tableName(),
            ['in', 'id_param', $ids]
        );

        $this->delete(
            ReportsParams::tableName(),
            [   'and',
                ['id_report' => $id_report],
                ['in', 'id_param', $ids]
            ]
        );

        // P15_Pechenkontur - Печень: Контуры
        // P20_Pechenportae - Печень: v. portae
        // P21_Pechenvhepatica - Печень: v. hepatica
        // P22_Pechenahepatica - Печень: a. hepatica
        // P23_Pechenobyemnobrazov - Печень: Объемные образования
        // P24_Zhelchpuzyrstepennapolneniya - Желчный пузырь: Степень наполнения
        // P32_Zhelchpuzyrobyemnobrazov - Желчный пузырь: Объемные образования
        // P34_Selezenkakontur - Селезенка: Контуры
        // P39_Selezenkaobyemnobrazov - Селезенка: Объемные образования
        // P41_Podzhelzhelezakontur - Поджелудочная железа: Контуры
        // было text 100
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P15_Pechenkontur',
            'P20_Pechenportae',
            'P21_Pechenvhepatica',
            'P22_Pechenahepatica',
            'P23_Pechenobyemnobrazov',
            'P24_Zhelchpuzyrstepennapolneniya',
            'P32_Zhelchpuzyrobyemnobrazov',
            'P34_Selezenkakontur',
            'P39_Selezenkaobyemnobrazov',
            'P41_Podzhelzhelezakontur',
        ];

        $this->update(Params::tableName(), ['datatype_details' => '255', 'updated_at' => $now], ['in', 'tech_name', $techNames]);


        // P46_Zheludkishechntrakt - Желудочно-кишечный тракт
        // P47_Svobodnzhidkost - Свободная жидкость в брюшной полости
        // было text 100
        // P48_Serviceresult - Заключение
        // было text 255
        // нужно text 'не менее 2000 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 3000

        $techNames = [
            'P46_Zheludkishechntrakt',
            'P47_Svobodnzhidkost',
            'P48_Serviceresult',
        ];

        $this->update(Params::tableName(), ['datatype_details' => '3000', 'updated_at' => $now], ['in', 'tech_name', $techNames]);


        // P26_Zhelchpuzyrtolshchinastenki - Желчный пузырь: Толщина стенки
        // было numeric 4,2
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P26_Zhelchpuzyrtolshchinastenki',
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


        // P28_Zhelchpuzyrstrukturazhelchi - Структура желчи
        // было dict patternbile (однородная/не однородная/наличие гиперэхогенных образований подвижных/наличие гиперэхогенных образований неподвижных)
        // нужно text 'не менее 100 символов'

        // P35_Selezenkarazmer - Селезенка: Размеры
        // было dict ultrasoundsystemsize (увеличена/не увеличена)
        // нужно text 'не менее 100 символов'

        // изменим datatype и продублируем конвертированные значения из dict_value в char_value

        $techNames = [
            'P28_Zhelchpuzyrstrukturazhelchi' => 'patternbile',
            'P35_Selezenkarazmer' => 'ultrasoundsystemsize',
        ];

        $this->update(Params::tableName(), ['datatype' => 'text', 'datatype_details' => '255', 'updated_at' => $now], ['in', 'tech_name', $techNames]);

        foreach ($techNames as $techName => $dictType) {
            $id_param = (new Query())
                ->select('id')
                ->from(Params::tableName())
                ->where(['tech_name' => $techName])
                ->scalar();
            if (!empty($id_param)) {
                $dictValues = (new Query())
                    ->from(Dictionaries::tableName())
                    ->where(['type' => $dictType])
                    ->all();
                if (!empty($dictValues)) {
                    foreach ($dictValues as $dictValue) {
                        $this->update(
                            VisitServiceParamValues::tableName(),
                            ['char_value' => $dictValue['name'], 'updated_at' => $now],
                            [
                                'id_param' => $id_param,
                                'dict_value' => $dictValue['id']
                            ]
                        );
                    }
                }
            }
        }


        // P17_Pechenekhostruktura - Печень: Эхоструктура
        // P36_Selezenkaekhostruktura - Селезенка: Эхоструктура
        // P43_Podzhelzhelezaekhostruktura - Поджелудочная железа: Эхоструктура
        // было text 100
        // нужно справочник - 'однородная/не однородная'

        $techNames = [
            'P17_Pechenekhostruktura',
            'P36_Selezenkaekhostruktura',
            'P43_Podzhelzhelezaekhostruktura',
        ];

        // используем существующий справочник
        $dictType = 'kidneyechostruktura';

        // только обновляем 'datatype', 'datatype_details'
        // конвертировать старые значения не представляется возможным, так как раньше был произвольный текст!!!
        $this->update(Params::tableName(), ['datatype' => 'dict', 'datatype_details' => $dictType, 'updated_at' => $now], ['in', 'tech_name', $techNames]);


        // P29_Zhelchpuzyrpuzyrprotok - Желчный пузырь: Пузырный проток
        // P30_Zhelchpuzyrobshzhelchprotok - Желчный пузырь: Общий желчный проток
        // было text 100
        // нужно справочник - 'расширен/ не расширен'

        $techNames = [
            'P29_Zhelchpuzyrpuzyrprotok',
            'P30_Zhelchpuzyrobshzhelchprotok',
        ];

        // новый справочник
        $dictType = 'zhelchpuzyrprotok';

        $values = [
            'расширен',
            'не расширен',
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


        // P31_Zhelchpuzyrpechenochnprotok - Желчный пузырь: Печеночные протоки
        // было text 100
        // нужно справочник - 'расширены/ не расширены'

        // новый справочник
        $dictType = 'zhelchpuzyrpechenochnprotoki';

        $values = [
            'расширены',
            'не расширены',
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
        $this->update(Params::tableName(), ['datatype' => 'dict', 'datatype_details' => $dictType, 'updated_at' => $now], ['tech_name' => 'P31_Zhelchpuzyrpechenochnprotok']);


        // P19_Pechenperifsosudrisunok - Периферический сосудистый рисунок
        // P38_Selezenkasosudrisunok - Селезенка: Сосудистый рисунок
        // добавить в справочник "сглажен", "сильно выражен"
        // !!! непонятно что делать с существующим "слабо выражен", оставляем

        $dictType = 'periphvascularpattern';

        $values = [
            'сглажен',
            'сильно выражен',
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


        // fix (probably) typo
        $this->update(Dictionaries::tableName(), ['name' => 'анэхогенна', 'updated_at' => $now], ['type' => 'abdomendechogenicity', 'name' => 'анэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'гипоэхогенна', 'updated_at' => $now], ['type' => 'abdomendechogenicity', 'name' => 'гипоэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'гиперэхогенна', 'updated_at' => $now], ['type' => 'abdomendechogenicity', 'name' => 'гиперэхогенно']);
        $this->update(Dictionaries::tableName(), ['name' => 'изоэхогенна', 'updated_at' => $now], ['type' => 'abdomendechogenicity', 'name' => 'изоэхогенно']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200117_070733_2594_uzi_gbs_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
