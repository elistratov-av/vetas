<?php

namespace app\common\components\entity;


use app\common\validators\RelationExistValidator;
use yii\validators\DateValidator;

class EntityRules
{
    protected $allowed_types = [
        'integer', 'string', 'double', 'json', 'safe', 'boolean',
        DateValidator::TYPE_DATE, DateValidator::TYPE_DATETIME, DateValidator::TYPE_TIME
    ];
    protected $allowed_restricts = ['required', 'unique'];

    /**
     * @var EntityInstance
     */
    protected $entityInstance;

    protected $entityAttrs = [];
    protected $entityRels  = [];

    protected $rules;

    /**
     * EntityRules constructor.
     * @param EntityInstance $entityInstance
     */
    public function init(EntityInstance $entityInstance)
    {
        $this->entityInstance = $entityInstance;
        $this->entityAttrs = $entityInstance->getAttributes();
        $this->entityRels = $entityInstance->getRelationships();
    }

    public function getRules()
    {
        $this->composeRules();

        return $this->rules;
    }

    protected function composeRules()
    {
        foreach ($this->allowed_restricts as $restrict) {
            if ($restrictFields = $this->getFieldsByRestriction($restrict)) {
                $this->rules[] = [$restrictFields, $restrict, 'on' => ['insert', 'update']];
            }
        }

        foreach ($this->allowed_types as $type) {
            if ($fields = $this->getFieldsByType($type)) {
                // Пока нет поддержки валидации json, переопределяем тип как safe, чтобы поле в итоге записалось в БД
                if ($type == 'json') {
                    $this->rules[] = [$fields, 'safe'];
                } else {
                    $this->rules[] = [$fields, $type, 'on' => ['insert', 'update']];
                }
            }
        }

        foreach ($this->getRelationFields('link') as $field => $type) {
            $this->rules[] = [$field, 'integer'];
            $this->rules[] = [$field, RelationExistValidator::class, 'targetRelation' => $type, 'on' => ['insert', 'update']];
        }

        foreach ($this->getRelationFields('rules') as $field => $rules) {
            if (!$rules) {
                continue;
            }
            foreach ($rules as $key => $value) {
                if (is_array($value)) {
                    $rule = [$field, $key];
                    $this->rules[] = array_merge($rule, $value);
                } else {
                    $this->rules[] = [$field, $value];
                }
            }
        }

        foreach ($this->entityAttrs as $field) {
            if (!isset($field['rules'])) {
                continue;
            }
            foreach ($field['rules'] as $key => $value) {
                if (is_array($value)) {
                    $rule = [$field['name'], $key];
                    $this->rules[] = array_merge($rule, $value);
                } else {
                    $this->rules[] = [$field['name'], $value];
                }
            }
        }

        $this->rules[] = $this->getSafeFields();

        if($this->getFieldsByRestriction('composite_unique')){
            $this->rules[] = [
                $this->getFieldsByRestriction('composite_unique'),
                'unique',
                'on' => ['insert', 'update'],
                'targetAttribute' => $this->getFieldsByRestriction('composite_unique')
            ];
        }
    }

    protected function getFieldsByRestriction($restriction)
    {
        $fields = [];

        array_walk($this->entityAttrs, function($value) use (&$fields, $restriction) {
            if (!empty($value[$restriction])) {
                $fields[] = $value['name'];
            }
        });

        array_walk($this->entityRels, function($value) use (&$fields, $restriction) {
            if (!empty($value[$restriction])) {
                $fields[] = $value['property'];
            }
        });

        return count($fields) ? $fields : null;
    }

    protected function getSafeFields()
    {
        $fields = [];

        array_walk($this->entityAttrs, function($field) use (&$fields) {
            $fields[] = $field['name'];
        });

        return [$fields, 'safe'];
    }

    protected function getFieldsByType($type, $incl_max = false)
    {
        $fields = [];

        array_walk($this->entityAttrs, function($field) use (&$fields, $type, $incl_max) {
            if ($field['type'] == $type) {
                $max = $field['max'] ?? null;

                if ($incl_max) {
                    $max ? array_push($fields, [$field['name'] => $max]) : null;
                }
                else {
                    $fields[] = $field['name'];
                }
            }
        });

        if (!$incl_max && $type == 'integer') {
            $fields = array_merge($fields, array_keys($this->getRelationFields()));
        }

        return count($fields) ? $fields : null;
    }

    protected function getRelationFields($key = 'propname')
    {
        $fields = [];

        array_walk($this->entityRels, function($relation) use (&$fields, $key) {
            if (array_key_exists($key, $relation)) {
                $fields[$relation['property']] = $relation[$key];
            }
        });

        return $fields;
    }
}
