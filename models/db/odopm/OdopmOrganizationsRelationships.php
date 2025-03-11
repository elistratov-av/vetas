<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

class OdopmOrganizationsRelationships extends ActiveRecord
{
    public static function tableName()
    {
        return 'odopm.organizations_relationship';
    }
}
