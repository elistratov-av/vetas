<?php
namespace app\migrations\traits;

use app\models\db\Users;

trait BlameableBehaviorTrait
{
    protected function addBlameableColumns($tablename, $refTablename = null)
    {
        $refTablename = $refTablename ?? Users::tableName();

        $this->addColumn($tablename, 'created_by', $this->integer()->notNull());
        $this->addColumn($tablename, 'updated_by', $this->integer()->notNull());

        $this->addForeignKey(
            "fk-$tablename-created_by",
            $tablename,
            'created_by',
            $refTablename,
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            "fk-$tablename-updated_by",
            $tablename,
            'updated_by',
            $refTablename,
            'id',
            'NO ACTION'
        );
    }

    protected function removeBlameableColumns($tablename)
    {
        $this->dropForeignKey("fk-$tablename-created_by", $tablename);
        $this->dropForeignKey("fk-$tablename-updated_by", $tablename);
        $this->dropColumn($tablename, 'created_by');
        $this->dropColumn($tablename, 'updated_by');
    }
}
