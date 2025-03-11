<?php

namespace app\modules\v1\models;

use tuyakhov\jsonapi\LinksInterface;
use tuyakhov\jsonapi\ResourceInterface;


class TmcResource extends BaseResource implements ResourceInterface, LinksInterface
{

    protected $alias = 'tmc';
    protected $excludedFields = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    protected $relationships = ['tmcType' => 'tmc_type'];

    public function getType()
    {
        return $this->tmcType->tmc_class;
    }

    public static function tableName()
    {
        return 'tmc';
    }

    public function rules()
    {
        return [
            [['name'], 'required', 'on' => 'insert'],
            [['name'], 'unique', 'on' => 'insert'],
            ['id_tmc_type', 'exist', 'targetRelation' => 'tmcType'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmcType()
    {
        return $this->hasOne( TmcTypeResource::class, ['id' => 'id_tmc_type']);
    }
}
