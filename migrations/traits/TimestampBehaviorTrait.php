<?php
namespace app\migrations\traits;

use yii\db\Expression;

trait TimestampBehaviorTrait
{
    protected function addTimestampColumns($tablename)
    {
        $this->addColumn($tablename,'created_at',
            $this->dateTime()->notNull()->defaultValue(new Expression('now()::timestamp without time zone')));
        $this->addColumn($tablename,'updated_at',
            $this->dateTime()->notNull()->defaultValue(new Expression('now()::timestamp without time zone')));

    }

    protected function removeTimestampColumns($tablename)
    {
        $this->dropColumn($tablename, 'created_at');
        $this->dropColumn($tablename, 'updated_at');
    }
}
