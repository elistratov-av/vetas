<?php

namespace app\interfaces;

use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;

interface ActiveRecordInterface
{
    const SCENARIO_FILTER = 'filter';

    const MODEL_CLASS_QUERY = 'Query';
    const MODEL_CLASS_SEARCH_MODEL = 'SearchModel';
    const MODEL_CLASS_VIEW_MODEL = 'ViewModel';

    /**
     * Return named rules
     *
     * @param array $rules
     * @param bool $update
     * @return array
     */
    /*
    public function rules(
        array $rules = [],
        bool $update = false
    ): array;
    */

    /**
     * Helper to set ActiveRecord to state New
     */
    public function emptyPrimaryKey(): void;

    /**
     * @param bool $setAsNewRecord
     */
    /*
    public function setIsNewRecord(
        bool $setAsNewRecord = true
    ): void;
    */

    /**
     * Returns the list of all attribute names of the model.
     * The default implementation will return all column names of the table associated with this AR class.
     * @param array $only
     * @param array $except
     * @param bool $schemaOnly
     * @return array list of attribute names.
     * @throws \ReflectionException
     * @throws InvalidConfigException
     * return array
     */
    /*
    public function attributes(
        array $only = null,
        array $except = null,
        bool $schemaOnly = false
    ): array;
    */

    /**
     * Returns attribute values.
     * @param array $only list of attributes whose value needs to be returned.
     * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
     * If it is an array, only the attributes in the array will be returned.
     * @param array $except list of attributes whose value should NOT be returned.
     * @param bool $schemaOnly
     * @return array attribute values (name => value).
     * @throws InvalidConfigException
     * @throws \ReflectionException
     */
    public function getRawAttributes(
        array $only = null,
        array $except = [],
        bool $schemaOnly = false
    ): array;

    /**
     * @param array $attributes
     * @return array
     */
    public function readOnlyAttributes(
        array $attributes = []
    ): array;

    /**
     * @param string $name
     * @return bool
     */
    public function isReadOnlyAttribute(
        string $name
    ): bool;

    /**
     * @param string $name
     * @return void
     */
    public function resetAttribute(
        string $name
    ): void;

    /**
     * Return records as Array id => column (for dropdowns)
     *
     * @param array $condition
     * @param string|null $key
     * @param string|null $value
     * @param string|null $indexBy
     * @param string|null $orderBy
     * @param string|null $alias
     * @return ActiveQueryInterface
     */
    public static function getListQuery(
        array $condition = [],
        string $key = null,
        string $value = null,
        $indexBy = null,
        $orderBy = null,
        string $alias = null
    ): ActiveQueryInterface;


    /**
     * @param array $condition
     * @param string|null $key
     * @param string|null $value
     * @param string|null $indexBy
     * @param string|null $orderBy
     * @return array
     */
    public static function getList(
        array $condition = [],
        string $key = null,
        string $value = null,
        $indexBy = null,
        $orderBy = null
    ): array;

}
