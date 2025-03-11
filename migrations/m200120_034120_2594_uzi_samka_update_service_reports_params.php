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
 * Class m200120_034120_2594_uzi_samka_update_service_reports_params
 */
class m200120_034120_2594_uzi_samka_update_service_reports_params extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');

        $id_report = 13;

        $serviceNames = [
            'Ультразвуковое исследование',
            'Повторное ультразвуковое исследование',
        ];

        $services = (new Query())
            ->from(GovServices::tableName())
            ->where(['in', 'name', $serviceNames])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        // добавить параметр P25_Matkanovoobrazov - Матка - Новообразования
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $tech_name = 'P25_Matkanovoobrazov';

        $param = new Params([
            'name' => 'Матка - Новообразования',
            'tech_name' => $tech_name,
            'datatype' => 'text',
            'datatype_details' => '255',
            'visit_flag' => false,
            'config' => [
                [
                    'report' => 'Ультразвуковое исследование репродуктивной системы самки',
                    'req_in' => [
                        'value' => 'репродуктивная система самки',
                        'tech_name' => 'P0_Organsystem'
                    ]
                ],
                [
                    'report' => 'Ультразвуковое исследование репродуктивной системы самки',
                    'req_in' => [
                        'value' => 'репродуктивная система самки',
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


        // переименовать параметр P15_Matkatolshinatela - 'Матка - Толщина стенки тела' в 'Матка - Толщина стенки'

        $this->update(Params::tableName(), ['name' => 'Матка - Толщина стенки', 'updated_at' => $now], ['tech_name' => 'P15_Matkatolshinatela']);

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


        // P27_Pravyaichnikkontur - Правый яичник - Контуры
        // P30_Levyaichnikkontur - Левый яичник - Контуры
        // было text 50
        // P28_Pravyaichniknovoobrazov - Правый яичник - Новообразования
        // P31_Levyaichniknovoobrazov - Левый яичник - Новообразования
        // было text 100
        // нужно text 'не менее 100 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 255

        $techNames = [
            'P27_Pravyaichnikkontur',
            'P30_Levyaichnikkontur',
            'P28_Pravyaichniknovoobrazov',
            'P31_Levyaichniknovoobrazov',
        ];

        $this->update(Params::tableName(), ['datatype_details' => '255', 'updated_at' => $now], ['in', 'tech_name', $techNames]);

        // P32_Serviceresult - Заключение
        // было text 255
        // нужно text 'не менее 2000 символов'
        // у нас значение в datatype_details используется как max, поэтому поставим 3000

        $this->update(Params::tableName(), ['datatype_details' => '3000', 'updated_at' => $now], ['tech_name' => 'P32_Serviceresult']);


        // для справочника alvusstructure:
        // - переименовать опции:
        // наличие гиперэхогенных образований подвижных -> наличие гиперэхогенных подвижных образований
        // наличие гиперэхогенных образований неподвижных -> наличие гиперэхогенных неподвижных образований
        // - добавить опции:
        // наличие гипоэхогенных подвижных образований
        // наличие гипоэхогенных неподвижных образований
        // наличие анэхогенных подвижных образований
        // наличие анэхогенных неподвижных образований
        // наличие изоэхогенных подвижных образований
        // наличие изоэхогенных неподвижных образований

        $dictType = 'alvusstructure';

        $renamedOptions = [
            'наличие гиперэхогенных образований подвижных' => 'наличие гиперэхогенных подвижных образований',
            'наличие гиперэхогенных образований неподвижных' => 'наличие гиперэхогенных неподвижных образований',
        ];

        foreach ($renamedOptions as $oldName => $newName) {
            $this->update(
                Dictionaries::tableName(),
                ['name' => $newName, 'updated_at' => $now],
                ['name' => $oldName, 'type' => $dictType]
            );
        }

        $values = [
            'наличие гипоэхогенных подвижных образований',
            'наличие гипоэхогенных неподвижных образований',
            'наличие анэхогенных подвижных образований',
            'наличие анэхогенных неподвижных образований',
            'наличие изоэхогенных подвижных образований',
            'наличие изоэхогенных неподвижных образований',
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200120_034120_2594_uzi_samka_update_service_reports_params cannot be reverted.\n";

        return false;
    }
}
