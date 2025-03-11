<?php

namespace app\traits;

use app\interfaces\ActiveRecordInterface;
use Yii;
use app\models\BaseActiveQuery as ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\base\UnknownPropertyException;

trait ActiveRecordTrait
{
    use ObjectTrait, DBUtilsTrait;

    /** {@inheritDoc} */
    public function rules(
        array $rules = [],
        bool $update = false
    ): array {
        return $rules;
    }

    /** {@inheritDoc} */
    public function emptyPrimaryKey(): void
    {
        $this->setAttributes(array_fill_keys($this->primaryKey(), null), false);
    }

    /** {@inheritDoc} */
    public function setIsNewRecord(
        bool $setAsNewRecord = true
    ): void {
        $this->emptyPrimaryKey();
        parent::setIsNewRecord($setAsNewRecord);
    }

    /** {@inheritDoc} */
    public function attributes(
        array $only = null,
        array $except = null,
        bool $schemaOnly = false
    ): array {
        $names = array_keys(static::getTableSchema()->columns);

        if (!$schemaOnly) {

            $class = new \ReflectionClass($this);

            foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic()) {
                    $names[] = $property->getName();
                }
            }
        }

        $names = array_unique($names);

        if ($only) {
            $names = array_intersect($only, $names);
        }

        if ($except) {
            $names = array_diff($names, $except);
        }

        return $names;
    }

    /** {@inheritDoc} */
    public function setAttributes(
        $values,
        $safeOnly = true,
        $throwExceptionOnUnsafe = true
    ): void {
        try {
            parent::setAttributes($values, $safeOnly);
        } catch (UnknownPropertyException $e) {
            if ($throwExceptionOnUnsafe) {
                throw $e;
            }
        }
    }

    /** {@inheritDoc} */
    public function getRawAttributes(
        array $only = null,
        array $except = [],
        bool $schemaOnly = false
    ): array {

        $values = [];

        if ($only === null) {
            $only = $this->attributes($only, $except, $schemaOnly);
        }

        foreach ($only as $name) {
            $values[$name] = $this->getAttribute($name);
        }

        if ($except) {
            $values = array_diff_key($values, array_flip($except));
        }

        return $values;
    }

    /** {@inheritDoc} */
    public function fields($names = null, $except = [])
    {
        $fields = array_keys($this->getAttributes($names, $except)); // @TODO желательно поменять на getRawAttributes

        return array_combine($fields, $fields);
    }

    /** {@inheritDoc} */
    public function readOnlyAttributes(
        array $attributes = []
    ): array {
        return array_unique(array_merge($attributes, [
            'id',
        ]));
    }

    /** {@inheritDoc} */
    public function isReadOnlyAttribute(
        string $name
    ): bool {
        return in_array($name, $this->readOnlyAttributes());
    }

    /** {@inheritDoc} */
    public function resetAttribute(
        string $name
    ): void {
        if ($this->getOldAttribute($name) != $this->getAttribute($name)) {
            $this->setAttribute($name, $this->getOldAttribute($name));
        }
    }

    /** {@inheritDoc} */
    public static function getListQuery(
        array $condition = [],
        string $key = null,
        string $value = null,
        $indexBy = null,
        $orderBy = null,
        string $alias = null
    ): ActiveQueryInterface {

        if (!$alias && is_subclass_of(get_called_class(), ActiveRecord::class)) {
            $tableName = Yii::$app->db->schema->getRawTableName(static::tableName());
        } else {
            // @TODO дурно попахивает
            empty($alias)
                ? $tableName = ''
                : $tableName = $alias;
        }

        $key = !empty($key) ? $key : static::primaryKey()[0];
        $value = !empty($value) ? $value : $key;
        $condition = !empty($condition) ? $condition : [];

        $query = static::find()
            ->select([
                "$tableName.$value",
                "$tableName.$key",
            ])
            ->andWhere($condition);

        if ($orderBy === true) {
            $query->orderBy("$tableName.$value");
        } elseif (is_string($orderBy)) {
            $query->orderBy("$tableName.$orderBy");
        }

        if ($indexBy === true) { //
            $query->indexBy($key);
        } elseif (is_string($indexBy)) {
            $query->indexBy("$tableName.$indexBy");
        }

        return $query;
    }

    /** {@inheritDoc} */
    public static function getList(
        array $condition = [],
        string $key = null,
        string $value = null,
        $indexBy = null,
        $orderBy = null
    ): array {
        return static::_getList($condition, $key, $value, $indexBy, $orderBy);
    }

    protected static function _getList(
        array $condition = [],
        string $key = null,
        string $value = null,
        $indexBy = true,
        $orderBy = true
    ): array {

        $attributes = static::getTableSchema()->columns;

        if (!$value && isset($attributes['name'])) {
            $value = 'name';
        }

        if (!$value && isset($attributes['title'])) {
            $value = 'title';
        }

        return static::getListQuery($condition, $key, $value, $indexBy, $orderBy)->column();
    }

    /** {@inheritDoc} */
    // метод необходим для того, чтобы вместо стандартного ActiveQuery подсунуть  ModelQuery
    // внутрь которых я перенес все, что раньше было разнесено по непонятным файлам - смотреть в истории Git-а
    public static function find()
    {
        if ($class = static::getModelClass(ActiveRecordInterface::MODEL_CLASS_QUERY)) {
            return new $class(static::class);
        }

        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    /**
     * @param string $type
     * @param string|null $baseClass
     * @param bool $checkExists
     * @return string
     */
    public static function getModelClass(
        string $type,
        string $baseClass = null,
        bool $checkExists = true
    ): ?string {

        if ($baseClass === null) {
            $baseClass = static::class;
        }

        $suffix = implode('|', [
            ActiveRecordInterface::MODEL_CLASS_SEARCH_MODEL,
            ActiveRecordInterface::MODEL_CLASS_VIEW_MODEL,
            ActiveRecordInterface::MODEL_CLASS_QUERY,
            'Model'
        ]);

        $class = preg_replace('/(' . $suffix . ')$/', '', $baseClass) . $type;

        if ($checkExists && class_exists($class)) {
            return $class;
        }

        return null;
    }
}
