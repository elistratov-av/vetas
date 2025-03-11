<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

/**
 * Class OdopmOrganization
 * @package app\models\db\odopm
 *
 * @property int    $id
 * @property int    $global_id
 * @property int    $system_object_id
 * @property int    $id_code
 * @property string $full_name
 * @property string $short_name
 * @property string $chief_name
 * @property string $chief_position
 * @property int    $responsible_department_id
 * @property bool   $public_services_available
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property string $bti_area_code
 * @property string $bti_district_code
 * @property string $address
 * @property string $unom
 * @property string $public_phone
 * @property string $working_hours
 * @property string $clarification_work_hours
 * @property string $comments
 * @property int    $entry_state_id
 * @property int    $entry_add_reason_id
 * @property int    $entry_change_reason_id
 * @property int    $entry_delete_reason_id
 * @property string $parent_entries
 * @property string $child_entries
 * @property string $geodata
 * @property bool   $is_capital_structure
 * @property bool   $signature
 * @property int    $id_odopm_catalog
 * @property bool   $vet_organization
 * @property bool   $reseption_corpses
 * @property bool   $free_vaccination
 * @property bool   $pet_registration
 * @property string $created_at
 * @property string $updated_at
 */
class OdopmOrganization extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'odopm.odopm_organizations';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id', 'global_id', 'system_object_id', 'id_code', 'responsible_department_id', 'id_odopm_catalog'] , 'integer'],
            [['full_name', 'address', 'public_phone', 'working_hours', 'unom', 'parent_entries', 'child_entries'], 'string', 'max' => 255],
            [['short_name', 'chief_name', 'chief_position', 'bti_area_code', 'bti_district_code'], 'string', 'max' => 100],
            [['inn', 'kpp', 'ogrn'], 'string', 'max' => 32],
            [['comments', 'geodata'], 'string', 'max' => 3000],
            [['entry_state_id', 'entry_add_reason_id', 'entry_change_reason_id', 'entry_delete_reason_id'] , 'integer'],
            ['clarification_work_hours', 'string', 'max' => 3000],
            [['is_capital_structure', 'signature'], 'boolean'],
            [['public_services_available', 'vet_organization', 'reseption_corpses', 'free_vaccination', 'pet_registration'], 'boolean'],
            [['public_services_available', 'vet_organization', 'reseption_corpses', 'free_vaccination', 'pet_registration'], 'default', 'value' => false],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }
}
