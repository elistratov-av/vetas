<?php

namespace app\models\db;

/**
 * Сводная таблица (представление) Organizations и OutOrganizations
 *
 * Class OrganizationDictionary
 * @package app\models\db
 */
class OrganizationDictionary extends Organizations
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'org_dictionary';
    }
}
