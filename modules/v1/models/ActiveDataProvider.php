<?php
/**
 * @author      Serge Postrash aka SDKiller <admin@yiisoft.ru>
 * @link        http://yiisoft.ru
 * @copyright   Copyright (c) 2018 YiiSoft.ru
 * @license     http://yiisoft.ru/licenses/commercial
 */

namespace app\modules\v1\models;

use yii\base\InvalidConfigException;
use yii\db\QueryInterface;

/**
 * Class ActiveDataProvider
 * @package app\modules\v1\models
 */
class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }

        if (empty($this->query->join) && empty($this->query->joinWith)) {
            return parent::prepareTotalCount();
        }

        /* @var $query \yii\db\ActiveQuery */
        $query = clone $this->query;
        $query->limit(-1)->offset(-1)->orderBy([])
            ->addGroupBy($query->modelClass::tableName() . '.id');

        return (int) $query->count('*', $this->db);
    }
}
