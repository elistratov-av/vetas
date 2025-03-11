<?php

namespace app\models\db;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Class ActiveRecord
 * @package app\models\db
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->hasAttribute('created_at') && $this->hasAttribute('updated_at')) {
            $this->attachBehavior(
                'timestamp',
                [
                    'class' => TimestampBehavior::class,
                    'value' => date('Y-m-d H:i:s'),
                ]
            );
        }

        if ($this->hasAttribute('created_by') && $this->hasAttribute('updated_by')) {
            $this->attachBehavior(
                'blameable',
                [
                    'class' => BlameableBehavior::class,
                ]
            );
        }

        parent::init();
    }

    public function isReadOnly()
    {
        return false;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this|void
     */
    public function setAttribute($name, $value)
    {
        parent::setAttribute($name, $value);

        return $this;
    }

    /**
     * @param array $values
     * @param bool $safeOnly
     * @return $this|void
     */
    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        return $this;
    }
}
