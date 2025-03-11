<?php

namespace app\models\db;

/**
 * Сводная таблица (представление) Organizations и OutOrganizations
 *
 * Class OrgDictionary
 * @package app\models\db
 */
class OrgDictionary extends Organizations
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'org_dictionary';
    }
}
