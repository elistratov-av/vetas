<?php

namespace app\common\components\entity;

interface EntityInterface
{
    public function getTableName() : string;
    public function getRelationships() : array;
    public function getTypeName() : string;
    public function getAliasName() : string;
    public function saveRelation($id, $field, $params, $attributes);

}