<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

/**
 * Class OdopmOrganizationsActions
 * @package app\models\db\odopm
 * @property int    $id
 * @property int    $id_organization
 * @property string $action
 * @property int    $entry_add_reason
 * @property string $entry_change_reason
 * @property int    $entry_deleted_reason
 * @property string $created_at
 * @property string $updated_at
 * @property int    $created_by
 * @property int    $updated_by
 */
class OdopmOrganizationsActions extends ActiveRecord
{
    const
        ACTION_ADD = 'added',
        ACTION_UPDATE = 'modified',
        ACTION_DELETE = 'deleted';

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'odopm.organizations_actions';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_organization', 'action'], 'required'],
            ['action', 'in', 'range' => [self::ACTION_ADD, self::ACTION_UPDATE, self::ACTION_DELETE]],
            [['id_organization', 'entry_add_reason', 'entry_deleted_reason'], 'integer'],
            ['entry_change_reason', 'string', 'max' => 500],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }
}
