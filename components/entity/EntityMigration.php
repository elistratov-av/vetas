<?php

namespace app\common\components\entity;

class EntityMigration
{
    /** @var EntityManager $entityManager */
    public $entityManager;

    /** @var array */
    private $tableSchema;

    /** @var array */
    private $configSchema;

    protected $serviceColumns = [
        'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    /**
     * @param string $name
     * @return EntityInstance|null
     * @throws EntityException
     */
    protected function getEntity(string $name)
    {
        return $this->entityManager->getEntity($name);
    }

    /**
     * @param string $name
     * @return string
     * @throws EntityException
     */
    public function getCreateParams(string $name)
    {
        $entity = $this->getEntity($name);

        $fields = $this->makeFieldsParam($entity->getAttributes());
        $relations = $this->makeRelationsParam($entity->getRelationships());

        if (!empty($fields) || !empty($relations)) {
            return $fields = implode(',', array_merge($fields, $relations));
        } else {
            return '';
        }
    }

    /**
     * @param string $name
     * @return array
     * @throws EntityException
     * @throws \yii\base\NotSupportedException
     */
    public function diff(string $name)
    {
        $entity = $this->getEntity($name);

        $dbSchema = $this->getTableSchema($entity->getTableName());
        $entitySchema = $this->getConfigSchema($entity);

        $addColumns = array_diff(array_keys($entitySchema['columns']), array_keys($dbSchema->columns));
        $tableColumns = array_filter($dbSchema->getColumnNames(), function($column) {
            return !in_array($column, $this->serviceColumns);
        });
        $deleteColumns = array_diff($tableColumns, array_keys($entitySchema['columns']));

        return [
            'addColumns' => array_filter($entitySchema['columns'], function($column) use ($addColumns) {
                return in_array($column['name'], $addColumns);
            }),
            'deleteColumns' => array_filter($dbSchema->columns, function($column) use ($deleteColumns) {
                return in_array($column->name, $deleteColumns);
            })
        ];
    }

    /**
     * @param EntityInstance $entity
     * @return array
     */
    protected function getConfigSchema(EntityInstance $entity)
    {
        if (!isset($this->configSchema)) {
            $attributes = $entity->getAttributes();
            $relations = $entity->getRelationships();
            $columns = ['id' => [
                'name' => 'id',
                'type' => 'integer'
            ]];
            foreach ($attributes as $attribute) {
                $columns[$attribute['name']] = $attribute;
            }
            foreach ($relations as $relation) {
                $columns[$relation['property']] = [
                    'name' => $relation['property'],
                    'type' => 'integer'
                ];
            }
            $this->configSchema = [
                'attributes' => $attributes,
                'relations' => $relations,
                'columns' => $columns
            ];
        }

        return $this->configSchema;
    }

    /**
     * @param string $tableName
     * @return array|null|\yii\db\TableSchema
     * @throws \yii\base\NotSupportedException
     */
    protected function getTableSchema(string $tableName)
    {
        if (!isset($this->tableSchema)) {
            $this->tableSchema = \Yii::$app->db->getSchema()->getTableSchema($tableName);
        }

        return $this->tableSchema;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function makeFieldsParam(array $attributes) : array
    {
        $fields = [];
        foreach ($attributes as $attribute) {
            $str = "{$attribute['name']}:{$attribute['type']}";

            if (isset($attribute['unique']) && $attribute['unique']) {
                $str .= ":unique";
            }

            if (isset($attribute['required']) && $attribute['required']) {
                $str .= ":notNull";
            }

            $fields[] = $str;
        }

        return $fields;
    }

    /**
     * @param array $relationships
     * @return array
     * @throws EntityException
     */
    public function makeRelationsParam(array $relationships) : array
    {
        $relations = [];
        foreach ($relationships as $relation) {
            $foreignEntity = $this->getEntity($relation['link']);

            if ($foreignEntity) {
                $str = "{$relation['property']}:integer:foreignKey({$foreignEntity->getTableName()})";
                $relations[] = $str;
            }
        }

        return $relations;
    }

}
