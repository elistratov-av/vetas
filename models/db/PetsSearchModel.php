<?php

namespace app\models\db;

use app\components\validators\Validator;
use app\traits\ModelSearchTrait;

class PetsSearchModel extends Pets
{
    use ModelSearchTrait {
        rules as protected _rulesTrait;
    }

    /** @inheritdoc */
    public function rules($rules = [], $update = false)
    {
        static $_rules;

        if (empty($_rules) || $update) {
            $_rules = Validator::merge([
                /*
                'string.break' => [['title',], 'filter', 'filter' => function($value) {
                    if(is_string($value) && strpos($value, ',') !== false){
                        $value = array_map('trim', explode(',', $value));
                    }
                    return $value;
                }],
                'string.each' => [
                    ['title',],
                    'each',
                    'rule' => ['integer'],
                    'when' => function ($model) {
                        return is_array($model->title);
                    }
                ],
                'string.max.255.when' => [
                    ['title',],
                    'string',
                    'max' => 255,
                    'when' => function ($model) {
                        return !is_array($model->title);
                    }
                ]
                */
            ], $this->_rulesTrait($rules));
        }

        return $_rules;
    }
}
