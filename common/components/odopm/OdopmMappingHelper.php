<?php

namespace app\common\components\odopm;

use app\models\db\odopm\OdopmAttributesSpecification;
use app\models\db\odopm\OdopmCatalogs;

/**
 * Class OdopmMappingHelper
 * @package app\common\helpers
 */
class OdopmMappingHelper
{
    /**
     * @var array
     */
    public static $attributeNamesMapping = [
        'id' =>  'system_object_id',
        'name' => 'FullName',
        'short_name' => 'ShortName',
        'inn' => 'INN',
        'kpp' => 'KPP',
        'ogrn' => 'OGRN',
        'id_area' => 'AdmArea',
        'id_district' => 'District',
        'comment' => 'Comments',
        'address' => 'Address',
        'clarification_schedule' => 'ClarificationOfWorkingHours',
        'id_code_odopm' => 'ID',
        'global_id' => 'global_id'
    ];
    /**
     * @var array
     */
    public static $odopmAttributeNamesMapping = [
        'global_id' => 'global_id',
        'system_object_id' => 'system_object_id',
        'id_code' => 'ID',
        'full_name' => 'FullName',
        'short_name' => 'ShortName',
        'chief_name' => 'ChiefName',
        'chief_position' => 'ChiefPosition',
        'responsible_department_id' => 'RespDepartment',
        'public_services_available' => 'PublicServicesAvailable',
        'inn' => 'INN',
        'kpp' => 'KPP',
        'ogrn' => 'OGRN',
        'is_capital_structure' => 'SignOfCapitalStructure',
        'bti_area_code' => 'AdmArea',
        'bti_district_code' => 'District',
        'address' => 'Address',
        'unom' => 'UNOM',
        'public_phone' => 'PublicPhone',
        'working_hours' => 'WorkingHours',
        'clarification_work_hours' => 'ClarificationOfWorkingHours',
        'comments' => 'Comments',
        'entry_state_id' => 'EntryState',
        'entry_add_reason_id' => 'EntryAddReason',
        'entry_change_reason_id' => 'EntryChangeReason',
        'entry_delete_reason_id' => 'EntryDeleteReason',
        'parent_entries' => 'ParentEntries',
        'child_entries' => 'ChildEntries',
        'geodata' => 'dict_geo',
        'signature' => 'signature',
    ];

    /**
     * Получаем маппниг атрибутов для каждого каталога
     * @param $idCatalog
     * @return array
     */
    public static function getMappingAttributesArray($idCatalog)
    {
        $mappingArray = [];
        $odopmSpecs = OdopmAttributesSpecification::find()
            ->alias('oas')
            ->leftJoin(OdopmCatalogs::tableName() . ' oc', 'oc.id = oas.id_catalog')
            ->select('tech_name, attribute_id')
            ->where(['oc.id_odopm' => $idCatalog])
            ->all();

        foreach ($odopmSpecs as $spec) {
            $attr = $spec->attributes;
            $mappingArray[$attr['tech_name']] = $attr['attribute_id'];
        }
        return $mappingArray;
    }
}
