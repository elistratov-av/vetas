<?php

namespace app\modules\v1\models;

use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\db\Constraint;
use yii\db\Exception;
use yii\db\IntegrityException;
use yii\db\pgsql\Schema;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Link;

class BaseResource extends ActiveRecord
{
    public $additionalFields = [];

    public $filter;

    /**
     * @var array
     */
    protected $excludedFields = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'AdrID', 'OkrugID', 'RayonID', 'name_tsv'];
    /**
     * @var array
     */
    protected $relationships = [];

    public static function getExactlySearchFields()
    {
        return [];
    }

    protected $alias;

    public function getType() {}

    public function fields()
    {
        $fields = parent::fields();
        $attributes = array_diff($fields, $this->excludedFields);
        $attributes = array_merge($attributes, $this->additionalFields);
        return $attributes;

    }

    public function formName()
    {
        return ucfirst($this->getType());
    }

    /**
     * @param $name
     * @return array
     */
    public function getRelationshipLinks($name)
    {
        return [];
    }

    /**
     * @param array $linked
     * @return array|\tuyakhov\jsonapi\ResourceIdentifierInterface[]
     */
    public function getResourceRelationships(array $linked = [])
    {
        return $this->relationships;
    }

    /**
     * @param $name
     * @param $relationship
     */
    public function setResourceRelationship($name, $relationship)
    {
        $this->relationships[$name] = $relationship;
        return $this;
    }

    /**
     * The "id" member of a resource object.
     * @return string an ID that in pair with type uniquely identifies the resource.
     */
    public function getId()
    {
        return (string) $this->attributes['id'];
    }

    public function getResourceAttributes(array $fields = [])
    {
        $attributes = array_diff($this->fields(), $this->excludedFields);

        foreach ($attributes as $key => $attribute) {
            $attribute = Inflector::camel2id(Inflector::variablize($attribute), '_');

            if (!empty($fields) && !in_array($attribute, $fields, true)) {
                unset($attributes[$key]);
            } else {
                $attributes[$key] = $this->$attribute;
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        //$self_route = [$this->alias, 'id' => $this->getId()];

        return [
          Link::REL_SELF => Url::to(Url::base(true).'/v1/'.str_replace('_', '-', $this->alias).'/'.$this->getId())
        ];
    }

    public static function primaryKey($asArray = false)
    {
        return ['id'];
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return $this|bool
     * @throws IntegrityException
     * @throws \yii\base\NotSupportedException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        try {
            return parent::save($runValidation, $attributeNames);
        } catch (IntegrityException $e) {
            switch ($e->getCode()) {
                case 23505:
                    /** @var Schema $schema */
                    $schema = static::getDb()->getSchema();
                    /** @var Constraint[] $constaints */
                    $constaints = $schema->getTableUniques(static::tableName());

                    foreach ($constaints as $constaint) {
                        if (strpos($e->getMessage(), $constaint->name) !== false) {
                            $this->addError(implode('_and_', $constaint->columnNames),
                                'Ошибка уникальности: ' . implode(' и ', $constaint->columnNames));
                            break;
                        }
                    }

                    return $this;

                default:
                    throw $e;
            }
        }
    }
}
