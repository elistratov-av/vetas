<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.05.19
 * Time: 15:05
 */

namespace app\modules\admin\models;


use app\models\db\ActiveRecord;

class OrganizationsTree extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.organizations_tree';
    }

}