<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.05.19
 * Time: 15:05
 */

namespace app\models\db;

class OrganizationsTree extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.organizations_tree';
    }

}
