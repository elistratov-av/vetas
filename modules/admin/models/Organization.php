<?php

namespace app\modules\admin\models;

use app\models\db\Organizations;

/**
 * Class Organization
 * @package app\modules\admin\models
 */
class Organization extends Organizations
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecialists()
    {
        return $this->hasMany(Specialist::class, ['id_organization' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contacts::class, ['entity_id' => 'id'])
            ->andOnCondition(['entity_type' => 'organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentOrg()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id'])->from([self::tableName(). ' AS parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasOne(Addresses::class, ['id' => 'id_address']);
    }

    public function getVisits()
    {
        return $this->hasMany(Visits::class, ['id_organization' => 'id']);
    }

    public function getNestedOrganizations($with_managed = false)
    {
        $id_organization = $this->id;
        $tree_by_parent_id = Organization::getOrganizationTree($id_organization);
        $nested_children_from_tree_by_parent_id = Organization::getNestedChildren($tree_by_parent_id['children']);

        if ($with_managed) {
            $tree_by_managing_organization_id = Organization::getManagingOrganizationTree($id_organization);
            $nested_children_from_tree_by_managing_organization_id = Organization::getNestedChildren($tree_by_managing_organization_id['children']);
        }

        return $nested_children_from_tree_by_parent_id + ($nested_children_from_tree_by_managing_organization_id  ?? []);
    }

    public function getAllChildOrganisations()
    {
        $organisationIds = array();
        $organisations = $this->getNestedOrganizations(true);

        array_walk_recursive($organisations, function ($item, $key) use (&$organisationIds) {
            if (!is_object($item) && is_numeric($item))
                $organisationIds[] = $item;
        });

        return array_unique($organisationIds);
    }
}
