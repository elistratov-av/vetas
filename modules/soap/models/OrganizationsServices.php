<?php

namespace app\modules\soap\models;

/**
 * Class OrganizationsServices
 * @package app\modules\soap\models
 *
 * @property MosruOrganizations $org
 */
class OrganizationsServices extends \app\models\db\OrganizationsServices
{

    public function getOrg()
    {
        return $this->hasOne(MosruOrganizations::class, ['id' => 'id_organization']);
    }
}
