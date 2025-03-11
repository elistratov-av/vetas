<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;
use app\common\components\rbac\Role;

/**
 * Class AllOrgsCompositeRule1
 * @package app\common\components\rbac\rules
 *
 * Пользователям доступны данные, связанные с действующим местом работы пользователя, где ему
 * выдана соответствующая роль, и дочерними организациями действующего места работы
 * "Системному Администратору (гос)" доступны все данные
 */
class AllOrgsCompositeRule1 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'AllOrgsCompositeRule1';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserAllOrgsRule',
    ];

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            return true;
        }

        return parent::execute($user, $item, $params);
    }
}
