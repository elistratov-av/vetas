<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;
use app\common\components\rbac\Role;

/**
 * Class AllOrgsCompositeRule2
 * @package app\common\components\rbac\rules
 *
 * "Системному администратору (гос)" доступны данные, связанные с действующим местом работы
 * пользователя, где ему выдана соответствующая роль, и дочерними организациями
 * Пользователю с ролью отличной от "системный администратор (гос)" доступны данные, связанные с
 * действующим местом работы
 */
class AllOrgsCompositeRule2 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'AllOrgsCompositeRule2';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserAllOrgsRule',
        'app\common\components\rbac\rules\UserOrgRule',
    ];

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            $ruleName = $this->rules[0];
            /* @var $rule \yii\rbac\Rule */
            $rule = \Yii::createObject($ruleName);

            return $rule->execute($user, $item, $params);
        }

        $ruleName = $this->rules[1];
        /* @var $rule \yii\rbac\Rule */
        $rule = \Yii::createObject($ruleName);

        return $rule->execute($user, $item, $params);
    }
}
