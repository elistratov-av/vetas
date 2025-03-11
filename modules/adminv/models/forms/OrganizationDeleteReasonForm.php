<?php

namespace app\modules\adminv\models\forms;

use app\modules\animalid\models\Model;

/**
 * Class OrganizationDeleteReasonForm
 * @package app\modules\adminv\models\forms
 */
class OrganizationDeleteReasonForm extends Model
{
    /**
     * @var string
     */
    public $reason;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['reason', 'required'],
            ['reason', 'integer'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'reason' => 'Причина удаления',
        ];
    }

    /**
     * @return array
     */
    public static function options()
    {
        return [
            5 => 'ошибочное добавление объекта',
            6 => 'ликвидация учреждения',
        ];
    }
}
