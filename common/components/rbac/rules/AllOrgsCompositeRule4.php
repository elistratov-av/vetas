<?php

namespace app\common\components\rbac\rules;

use app\common\components\rbac\CompositeRule;
use app\common\components\rbac\Role;

/**
 * Class AllOrgsCompositeRule4
 * @package app\common\components\rbac\rules
 *
 * Пользователям доступны данные, связанные с сетью организаций, к кторой относится организация пользователя.
 * "Системному Администратору (гос)" доступны все данные
 */
class AllOrgsCompositeRule4 extends CompositeRule
{
    /**
     * @var string
     */
    public $name = 'AllOrgsCompositeRule4';
    /**
     * @var array
     */
    public $rules = [
        'app\common\components\rbac\rules\UserOrgTreeRule',
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
