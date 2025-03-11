<?php

namespace app\traits;

use app\components\validators\Validator;
use app\models\BaseActiveRecord;
use yii\data\ActiveDataProvider;
use app\interfaces\ActiveRecordInterface;
use yii\data\DataProviderInterface;

trait ModelSearchTrait
{
    protected $_filter;

    /** {@inheritdoc} */
    public function rules(
        $rules = [],
        $update = false
    ): array
    {
        static $_rules;

        if (!$_rules || $update) {

            // Order of rules is very important for filter controls. 'each' must be set before generic type
            $_rules = [
                'required' => [[], 'required', 'except' => ActiveRecordInterface::SCENARIO_FILTER],
                'filter.break' => [
                    ['id', 'title', 'name',],
                    'filter',
                    'filter' => function ($value) {
                        if (is_string($value) && strpos($value, ',') !== false) {
                            $value = array_map('trim', explode(',', $value));
                        }
                        return $value;
                    }
                ],
                'each.integer' => [
                    ['id',],
                    'each',
                    'rule' => ['integer'],
                    'when' => function ($model, $attribute) {
                        return is_array($model->$attribute);
                    }
                ],
                'integer' => [
                    ['id',],
                    'integer',
                    'when' => function ($model, $attribute) {
                        return !is_array($model->$attribute);
                    }
                ],
                'each.string.max.255' => [
                    ['title', 'name',],
                    'each',
                    'rule' => ['string', 'max' => 255,],
                    'when' => function ($model, $attribute) {
                        return is_array($model->$attribute);
                    }
                ],
                'string.max.255' => [
                    ['title', 'name',],
                    'string',
                    'max' => 255,
                    'when' => function ($model, $attribute) {
                        return !is_array($model->$attribute);
                    }
                ],
            ];

            if ($this instanceof ActiveRecordInterface) {
                $_rules = parent::rules(Validator::merge($_rules, $rules));
            }
        }

        // @TODO move rule to top of rules to avoid 'integer' rule first due order of parent rules always first
        $_rules = array_merge([
            'filter.break' => $_rules['filter.break'],
            'each.integer' => $_rules['each.integer'],
            'each.string.max.255' => $_rules['each.string.max.255'],
        ], $_rules);

        return $_rules;
    }

    /** {@inheritdoc} */
    public function scenarios(): array
    {
        $this->scenario = ActiveRecordInterface::SCENARIO_FILTER;
        return parent::scenarios();
    }

    /** {@inheritdoc} */
    public function search(
        array $params
    ): DataProviderInterface
    {
        $query = parent::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function __set($name, $value)
    {
        if ($name === '_filter') {
            $this->_filter = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if ($name === '_filter') {
            return $this->_filter;
        } else {
            return parent::__get($name);
        }
    }
}
