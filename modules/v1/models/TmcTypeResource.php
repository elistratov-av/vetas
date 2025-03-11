<?php

namespace app\modules\v1\models;

use tuyakhov\jsonapi\LinksInterface;
use tuyakhov\jsonapi\ResourceInterface;


class TmcTypeResource extends BaseResource implements ResourceInterface, LinksInterface
{
    const
        TYPE_DRUG = 'drug',
        TYPE_EQUIPMENT = 'equipment',
        TYPE_VACCINE = 'vaccine',
        TYPE_EXP_MATERIAL = 'exp_material'
    ;

    protected $alias = 'tmc-types';
    protected $excludedFields = ['id'];

    public function getType()
    {
        return 'tmcType';
    }

    public static function tableName()
    {
        return 'tmc_types';
    }

    public function rules()
    {
        return [
            [['name'], 'required', 'on' => 'insert'],
            [['name'], 'unique', 'on' => 'insert'],
            [['name', 'description', 'tmc_class'], 'string'],
            ['tmc_class', 'in', 'range' => [
                self::TYPE_DRUG, self::TYPE_EQUIPMENT, self::TYPE_VACCINE, self::TYPE_EXP_MATERIAL
            ]]
        ];
    }
}
