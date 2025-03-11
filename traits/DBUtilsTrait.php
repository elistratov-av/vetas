<?php

namespace app\traits;

trait DBUtilsTrait
{
    public function getRawTableName($alias = null)
    {
        return \Yii::$app->db->schema->getRawTableName($alias ?? $this->_getTableNameAndAlias());
    }

    protected function _getTableNameAndAlias()
    {
        if (empty($this->from)) {
            $tableName = $this->getPrimaryTableName();
        } else {
            $tableName = '';
            foreach ($this->from as $alias => $tableName) {
                if (is_string($alias)) {
                    return [$tableName, $alias];
                }
                break;
            }
        }

        if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
            $alias = $matches[2];
        } else {
            $alias = $tableName;
        }

        return $alias;
    }

}
