<?php

namespace app\modules\v1\models;

use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;
use app\common\components\entity\EntityInstance;
use app\common\components\entity\EntityConfigManager;

class ActiveDataFilter extends \yii\data\ActiveDataFilter
{
    /**
     * @var array
     */
    protected $condition = [];
    /**
     * @var array
     */
    protected $relations = [];

    /**
     * {@inheritdoc}
     */
    public $filterControls = [
        'and' => 'AND',
        'or' => 'OR',
        'not' => 'NOT',
        'lt' => '<',
        'gt' => '>',
        'lte' => '<=',
        'gte' => '>=',
        'eq' => '=',
        'neq' => '!=',
        'in' => 'IN',
        'nin' => 'NOT IN',
        'like' => 'ILIKE',
        'like!' => 'LIKE',
        'is' => 'IS',
        'match' => 'MATCH',
    ];
    /**
     * {@inheritdoc}
     */
    public $conditionValidators = [
        'AND' => 'validateConjunctionCondition',
        'OR' => 'validateConjunctionCondition',
        'NOT' => 'validateBlockCondition',
        '<' => 'validateOperatorCondition',
        '>' => 'validateOperatorCondition',
        '<=' => 'validateOperatorCondition',
        '>=' => 'validateOperatorCondition',
        '=' => 'validateOperatorCondition',
        '!=' => 'validateOperatorCondition',
        'IN' => 'validateOperatorCondition',
        'NOT IN' => 'validateOperatorCondition',
        'LIKE' => 'validateOperatorCondition',
        'ILIKE' => 'validateOperatorCondition',
        'IS' => 'validateOperatorCondition',
        'MATCH' => 'validateOperatorCondition',
    ];
    /**
     * {@inheritdoc}
     */
    public $operatorTypes = [
        '<' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '>' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '<=' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '>=' => [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_DATETIME, self::TYPE_DATE, self::TYPE_TIME],
        '=' => '*',
        '!=' => '*',
        'IN' => '*',
        'NOT IN' => '*',
        'LIKE' => [self::TYPE_STRING],
        'ILIKE' => [self::TYPE_STRING],
        'IS' => '*',
        'MATCH' => [self::TYPE_STRING],
    ];
    /**
     * {@inheritdoc}
     */
    public $conditionBuilders = [
        'AND' => 'buildConjunctionCondition',
        'OR' => 'buildConjunctionCondition',
        'NOT' => 'buildBlockCondition',
        '<' => 'buildOperatorCondition',
        '>' => 'buildOperatorCondition',
        '<=' => 'buildOperatorCondition',
        '>=' => 'buildOperatorCondition',
        '=' => 'buildOperatorCondition',
        '!=' => 'buildOperatorCondition',
        'IN' => 'buildOperatorCondition',
        'NOT IN' => 'buildOperatorCondition',
        'LIKE' => 'buildOperatorCondition',
        'ILIKE' => 'buildOperatorCondition',
        'IS' => 'buildIsNullCondition',
        'MATCH' => 'buildFullTextCondition',
    ];

    /**
     * {@inheritdoc}
     */
    public function load($data, $formName = null)
    {
        parent::load($data, $formName);

        $this->filter = json_decode($this->filter, true);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildAttributeCondition($attribute, $condition)
    {
        if (is_array($condition)) {
            return parent::buildAttributeCondition($attribute, $condition);
        }

        return [$this->disambiguateColumnName($attribute) => $this->filterAttributeValue($attribute, $condition)];
    }

    /**
     * {@inheritdoc}
     */
    protected function buildOperatorCondition($operator, $condition, $attribute)
    {
        if (isset($this->queryOperatorMap[$operator])) {
            $operator = $this->queryOperatorMap[$operator];
        }

        return [$operator, $this->disambiguateColumnName($attribute), $this->filterAttributeValue($attribute, $condition)];
    }

    /**
     * @param string $operator
     * @param mixed $value
     * @param string $attribute
     * @return array
     */
    protected function buildIsNullCondition($operator, $value, $attribute)
    {
        if ($operator !== 'IS' || $value !== null) {
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('unsupportedOperatorType', ['attribute' => $attribute, 'operator' => $operator]));
            return;
        }

        return [$operator, $this->disambiguateColumnName($attribute), $value];
    }

    /**
     * @param string $operator
     * @param mixed $value
     * @param string $attribute
     * @return array
     */
    protected function buildFullTextCondition($operator, $value, $attribute)
    {
        if ($operator !== 'MATCH' || !is_string($value)) {
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('unsupportedOperatorType', ['attribute' => $attribute, 'operator' => $operator]));
            return;
        }

        $value = trim($value);

        if (empty($value)) {
            return ['=', $this->disambiguateColumnName($attribute), ''];
        }

        // в качестве naming convention примем суффикс '_tsv'
        $column = $this->disambiguateColumnName($attribute) . '_tsv';

        return new Expression($column . " @@ plainto_tsquery('russian', :searchtext)", [':searchtext' => $value]);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAttributeCondition($attribute, $condition)
    {
        if (($pos = strpos($attribute, '.', 1)) !== false) {
            $result = $this->validateRelatedAttribute($attribute);
            if ($result !== true) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('unknownAttribute', ['attribute' => $attribute . ' [1]']));
                return;
            }
        } else {
            $attributeTypes = $this->getSearchAttributeTypes();

            if (!isset($attributeTypes[$attribute])) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('unknownAttribute', ['attribute' => $attribute . ' [2]']));
                return;
            }
        }

        if (is_array($condition)) {
            $operatorCount = 0;
            foreach ($condition as $rawOperator => $value) {
                if (isset($this->filterControls[$rawOperator])) {
                    $operator = $this->filterControls[$rawOperator];
                    if (isset($this->operatorTypes[$operator])) {
                        $operatorCount++;
                        $this->validateOperatorCondition($rawOperator, $value, $attribute);
                    }
                }
            }

            if ($operatorCount > 0) {
                if ($operatorCount < count($condition)) {
                    $this->addError($this->filterAttributeName, $this->parseErrorMessage('invalidAttributeValueFormat', ['attribute' => $attribute]));
                }
            } else {
                // attribute may allow array value:
                $this->validateAttributeValue($attribute, $condition);
            }
        } else {
            $this->validateAttributeValue($attribute, $condition);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function detectSearchAttributeTypes()
    {
        $attributeTypes = parent::detectSearchAttributeTypes();
        $attributeTypes['id'] = self::TYPE_INTEGER;

        return $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAttributeValue($attribute, $value)
    {
        if (($pos = strpos($attribute, '.', 1)) !== false) {
            return;
        }

        if ($attribute == 'id') { // позволить фильтрацию по ID
            return true;
        }

        return parent::validateAttributeValue($attribute, $value);
    }

    /**
     * @param string $attribute
     * @return bool
     */
    protected function validateRelatedAttribute($attribute)
    {
        $attributeTypes = $this->getSearchAttributeTypes();

        if (isset($attributeTypes[$attribute])) {
            return true;
        }

        $pos = strpos($attribute, '.');
        $relname = substr($attribute, 0, $pos);

        /* @var $searchModel \yii\db\ActiveRecord|\app\modules\v1\models\BaseResource */
        $searchModel = $this->getSearchModel();

        if (!$searchModel instanceof \app\modules\v1\models\BaseResource) {
            /* @var $searchModel \yii\db\ActiveRecord */
            $relationQuery = $searchModel->getRelation($relname, false);
            if ($relationQuery === null) {
                return false;
            }

            $this->addAttributeAndRelation($attribute, $relname);

            return true;
        }

        /* @var $searchModel \app\modules\v1\models\BaseResource */
        $name = BaseInflector::camelize($relname);
        $method = 'get' . $name;
        if (method_exists($searchModel, $method)) {
            try {
                $relationQuery = call_user_func([$searchModel, $method]);
            } catch (\Throwable $e) {
                $relationQuery = null;
            }
            if ($relationQuery === null) {
                return false;
            }
            if (!$relationQuery instanceof \yii\db\ActiveQueryInterface) {
                return false;
            }
            /* @see \yii\db\BaseActiveRecord::getRelation */
            $this->addAttributeAndRelation($attribute, $relname, lcfirst($name));

            return true;
        }

        /* @var $searchModel \app\modules\v1\models\EntityResource */

        // check for single relations
        // first search by link
        $link = $this->resolveCollectionName($relname);
        $relation = $searchModel->entityInstance->getRelationByField($link);
        if ($relation !== null) {
            $this->addAttributeAndRelation($attribute, $relname, $relation);

            return true;
        }
        // preserve search by propname for BC
        $relation = $searchModel->entityInstance->getRelationByField($relname, 'propname');
        if ($relation !== null) {
            $this->addAttributeAndRelation($attribute, $relname, $relation);

            return true;
        }

        // check for multiple relations
        $collectionName = $this->resolveCollectionName($relname);
        $collection = $searchModel->entityInstance->getNestedCollection($collectionName);

        if ($collection === null) {
            return false;
        }

        if ($collection->isPlural()) {
            // one-to-many
            $config = [
                'plural' => true,
                'link' => $collectionName,
                'foreignKey' => $collection->getForeignKey(),
                'additionalFields' => $collection->getAdditionalFields(),
            ];
            $this->addAttributeAndRelation($attribute, $relname, $config);

            return true;
        } else {
            // many-to-many
            $config = [
                'via' => $collection->getJunctionTableName(),
                'link'  => $collectionName,
                'primaryKey' => $collection->getPrimaryKey(),
                'foreignKey' => $collection->getForeignKey(),
            ];
            $this->addAttributeAndRelation($attribute, $relname, $config);

            return true;
        }
    }

    /**
     * @param string $attr
     * @return string
     * @see \app\common\components\entity\EntityInstance::inflectTypeName()
     */
    private function resolveCollectionName($attr)
    {
        return str_replace('_', '-', EntityInstance::entityNameFromTypeName($attr));
    }

    /**
     * @param string $attribute
     * @param string $relname
     * @param array|string $config
     */
    protected function addAttributeAndRelation($attribute, $relname, $config = null)
    {
        $attributeTypes = $this->getSearchAttributeTypes();

        $pos = strpos($attribute, '.');
        $relatedAttribute = substr($attribute, $pos + 1);

        if ($relatedAttribute == 'id') {
            // consider PK to be of integer type
            $attributeTypes[$attribute] = self::TYPE_INTEGER;
        } else {
            // add dummy entry for validation as fallback
            $attributeTypes[$attribute] = self::TYPE_STRING;

            if (is_array($config)) {
                $relatedEntityConfig = ArrayHelper::getValue((new EntityConfigManager())->getConfig(), $config['link'], []);
                $attributes = ArrayHelper::map(ArrayHelper::getValue($relatedEntityConfig, 'attributes', []), 'name', function ($el) {
                    return ArrayHelper::getValue(self::typesMap(), $el['type'], $el['type']);
                });
                if (array_key_exists($relatedAttribute, $attributes)) {
                    // set attribute type from entity config
                    $attributeTypes[$attribute] = $attributes[$relatedAttribute];
                } else {
                    $relationsFKs = ArrayHelper::getColumn(ArrayHelper::getValue($relatedEntityConfig, 'relations', []), 'property');
                    if (in_array($relatedAttribute, $relationsFKs)) {
                        // consider FK to be of integer type
                        $attributeTypes[$attribute] = self::TYPE_INTEGER;
                    }
                }
            }
        }

        $this->setSearchAttributeTypes($attributeTypes);

        if (!array_key_exists($relname, $this->relations)) {
            $this->relations[$relname] = ($config === null) ? $relname : $config;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildInternal()
    {
        $filter = $this->normalize(false);
        if (empty($filter)) {
            return null;
        }

        $this->condition = $this->buildCondition($filter);

        return $this;
    }

    /**
     * @param \yii\db\ActiveQuery $query
     * @param array $filter
     */
    public function prepareQuery(&$query)
    {
        if (!empty($this->condition)) {
            $query->andWhere($this->condition);
            if (!empty($this->relations)) {
                $this->prepareJoins($query);
            }
        }
    }

    /**
     * @param \yii\db\ActiveQuery $query
     */
    protected function prepareJoins(\yii\db\ActiveQuery &$query)
    {
        $tableName = $query->modelClass::tableName();

        foreach ($this->relations as $relname => $relation) {
            if (is_array($relation)) {
                $relTable = EntityInstance::tableNameFromEntityName($relation['link']);
                $relAlias = EntityInstance::tableNameFromEntityName($relname);
                $params = [];
                if (isset($relation['plural'])) {
                    // one-to-many
                    $on = '[[' . $relAlias . ']].[[' . $relation['foreignKey'] . ']] = [[' . $tableName . ']].[[' . 'id' . ']]';
                    if (isset($relation['additionalFields'])) {
                        foreach ($relation['additionalFields'] as $field => $value) {
                            $paramName = ':' . $field;
                            $on .= ' AND ' . '[[' . $relAlias . ']].[[' . $field . ']] = ' . $paramName;
                            $params[$paramName] = $value;
                        }
                    }
                } elseif (isset($relation['via'])) {
                    // many-to-many
                    $junctionTable = EntityInstance::tableNameFromEntityName($relation['via']);
                    $junctionOn = '[[' . $junctionTable . ']].[[' . $relation['foreignKey'] . ']] = [[' . $tableName . ']].[[' . 'id' . ']]';
                    $query->leftJoin($junctionTable, $junctionOn);
                    $on = '[[' . $junctionTable . ']].[[' . $relation['primaryKey'] . ']] = [[' . $relAlias . ']].[[' . 'id' . ']]';
                } else {
                    // one-to-one
                    $on = '[[' . $tableName . ']].[[' . $relation['property'] . ']] = [[' . $relAlias . ']].[[' . 'id' . ']]';
                }
                $query->leftJoin($relTable . ' ' . $relAlias, $on, $params);
            } else {
                $relTable = EntityInstance::tableNameFromEntityName($relation);
                $relAlias = EntityInstance::tableNameFromEntityName($relname);
                $query->joinWith($relTable . ' ' . $relAlias);
            }
        }
    }

    /**
     * @param string $attribute
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function disambiguateColumnName($attribute)
    {
        $column = $attribute;
        if (strpos($attribute, '.') === false) {
            $tableName = $this->getSearchModel()::tableName();
            $column = $tableName . '.' . $attribute;
        }

        return $column;
    }

    /**
     * @return array
     */
    private static function typesMap()
    {
        return [
            'double' => self::TYPE_FLOAT,
            'text' => self::TYPE_STRING,
        ];
    }
}
