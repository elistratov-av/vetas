<?php

namespace app\common\components\rbac;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Class CompositeRule
 * @package app\common\components\rbac
 *
 * Комбинированное правило, последовательно проверяющее выполнение нескольких правил.
 * Для самого простого применения комбинированного правила, следует располагать правила в массиве $rules
 * в такой последовательности, что невыполнение очередного правила делает ненужными последующие проверки.
 */
class CompositeRule extends Rule
{
    /**
     * @var array
     */
    public $rules;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->rules) || !is_array($this->rules)) {
            throw new InvalidConfigException('Для применения комбинированного правила должен быть задан массив rules');
        }
    }

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        foreach ($this->rules as $ruleName) {
            /* @var $rule \yii\rbac\Rule */
            $rule = Yii::createObject($ruleName);
            $result = $rule->execute($user, $item, $params);
            if ($result !== true) {
                // не имеет смысла проверять дальше
                return false;
            }
        }

        return true;
    }
}
